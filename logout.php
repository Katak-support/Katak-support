<?php
/*********************************************************************
    logout.php

    Destroy clients session.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

require('client.inc.php');

//Log logout info...
$msg=sprintf("%s/%s " . _("logged out"),$_SESSION['_client']['userID'],$_SESSION['_client']['key']);
Sys::log(LOG_DEBUG,'User logout',$msg,$_SESSION['_client']['userID']);

//We are checking to make sure the user is logged in before a logout to avoid session reset tricks on excess logins
$_SESSION['_client']=array();
session_unset();
session_destroy();
header('Location: index.php');
require('index.php');
?>
