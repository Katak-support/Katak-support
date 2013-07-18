<?php
/*********************************************************************
    secure.inc.php

    File included on every client's "secure" pages

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']),basename(__FILE__))) die('Adiaux amikoj!');
if(!file_exists('client.inc.php')) die(_('Fatal error!'));
require_once('client.inc.php');
//User must be logged in!
if(!$thisclient || !$thisclient->getId() || !$thisclient->isValid()){
    require('./login.php');
    exit;
}
$thisclient->refreshSession();
?>
