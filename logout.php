<?php
/*********************************************************************
    logout.php

    Destroy users session.

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

require_once('user.inc.php');

//Log logout info...
$msg=sprintf("%s/%s " . _("logged out"),$_SESSION['_user']['userID'],$_SESSION['_user']['key']);
Sys::log(LOG_DEBUG,'User logout',$msg,$_SESSION['_user']['userID']);

//We are checking to make sure the user is logged in before a logout to avoid session reset tricks on excess logins
$_SESSION['_user']=array();
session_unset();
session_destroy();
header('Location: index.php');
require('index.php');
?>
