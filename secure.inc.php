<?php
/*********************************************************************
    secure.inc.php

    File included on every user's "secure" pages

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']),basename(__FILE__))) die('Adiaux amikoj!');
if(!file_exists('user.inc.php')) die(_('Fatal error!'));
require_once('user.inc.php');
//User must be logged in!
if(!$thisuser || !$thisuser->getId() || !$thisuser->isValid()){
    require('./login.php');
    exit;
}
$thisuser->refreshSession();
?>
