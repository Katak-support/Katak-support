<?php
/*********************************************************************
    client.inc.php

    File included on every client page. Includes everything you need for client pages.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']),basename(__FILE__))) die('Adiaux amikoj!');

if(!file_exists('main.inc.php')) die('Fatal error!');
require_once('main.inc.php');

// set client language and language domain
$lang = $cfg->getClientLanguage();
putenv('LC_ALL=' . $lang);
setlocale(LC_ALL, $lang . '.UTF-8');
bindtextdomain('messages', './i18n');
textdomain("messages");

if(!defined('INCLUDE_DIR')) die(_('Fatal error!'));

/*Some more include defines specific to client only */
define('CLIENTINC_DIR',INCLUDE_DIR.'client/');
define('KTKCLIENTINC',TRUE);

//Check the status of the Support System.
if(!is_object($cfg) || !$cfg->getId() || $cfg->isHelpDeskOffline()) {
    include('./offline.php');
    exit;
}

//Forced upgrade? Version mismatch.
if(defined('THIS_VERSION') && strcasecmp($cfg->getVersion(), substr(THIS_VERSION, 0, strripos((THIS_VERSION),'.')))) {
    die(_('System is offline for an upgrade.'));
    exit;
}

/* include what is needed on client stuff */
require_once(INCLUDE_DIR.'class.client.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');

//clear some vars
$errors=array();
$msg='';
$thisclient=null;
//Make sure the user is valid..before doing anything else.
if($_SESSION['_client']['userID'] && $_SESSION['_client']['key'])
    $thisclient = new ClientSession($_SESSION['_client']['userID'],$_SESSION['_client']['key']);

//print_r($_SESSION);
//is the user logged in?
if($thisclient && $thisclient->getId() && $thisclient->isValid()){
     $thisclient->refreshSession();
}

?>
