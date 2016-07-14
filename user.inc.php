<?php
/*********************************************************************
    user.inc.php

    File included on every external interface page.
    Includes everything you need for user pages.

    Copyright (c)  2012-2016 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']),basename(__FILE__))) die('Adiaux amikoj!');

if(!file_exists('main.inc.php')) die('Fatal error!');
require_once('main.inc.php');

// set user language and language domain
$lang = $cfg->getClientLanguage();
putenv('LC_MESSAGES=' . $lang);
setlocale(LC_ALL, $lang . '.UTF-8', $lang . '.UTF8', $lang . '.utf8', $lang . '.utf-8');
bindtextdomain('messages', './i18n');
bind_textdomain_codeset('messages','UTF-8');
textdomain("messages");

if(!defined('INCLUDE_DIR')) die(_('Fatal error!'));

/*Some more include defines specific to user only */
define('USERINC_DIR',INCLUDE_DIR.'user/');
define('KTKUSERINC',TRUE);

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

// include what is needed on user stuff
require_once(INCLUDE_DIR.'class.ticket.php');

// clear some vars
$errors=array();
$msg='';
$thisuser=null;

// Has got the user a session? Then make sure the user is valid...before doing anything else.
if(isset($_SESSION['_user']) && $_SESSION['_user']['userID'] && $_SESSION['_user']['key'])
  if(!$cfg->getUserLogRequired())
    $thisuser = new UserSession($_SESSION['_user']['userID'],$_SESSION['_user']['key']);
  else {
    $thisuser = new ClientSession($_SESSION['_user']['userID'],$_SESSION['_user']['key']);
    // Block blocked client
    if (!$thisuser->isactive()) {
      $errors['err'] = _('Access Disabled. Contact Admin');
      $_SESSION['_user']=array();
      session_unset();
      session_destroy();
    }
  }

// print_r($_SESSION);
    
// Is the user logged in?
if($thisuser && $thisuser->getId() && $thisuser->isValid()){
     $thisuser->refreshSession();
}

?>
