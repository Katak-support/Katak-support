<?php
/*********************************************************************
    ktk-config.php

    Static Katak-support configuration file. Mainly useful for mysql login info
    and sysadmin email recording.
    Created during installation process and shouldn't change even on upgrades.
   
    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

#Disable direct access.
if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']),basename(__FILE__)) || !defined('ROOT_PATH')) die('Adiaux amikoj!');

#Install flag
define('KTSINSTALLED',FALSE);
if(KTSINSTALLED!=TRUE){
    if(!file_exists(ROOT_PATH.'setup/install.php')) die('Error: Contact system administrator.'); //Something is really wrong!
    //Invoke the installer.
    header('Location: '.ROOT_PATH.'setup/install.php');
    exit;
}

# Encrypt/Decrypt secret key - randomly generated during installation.
define('SECRET_SALT','%CONFIG-SIRI');

#System admin email. Used on db connection issues and ticket alerts.
define('ADMIN_EMAIL','%ADMIN-EMAIL');

#Mysql Login info
define('DBTYPE','mysql');
define('DBHOST','%CONFIG-DBHOST'); 
define('DBNAME','%CONFIG-DBNAME');
define('DBUSER','%CONFIG-DBUSER');
define('DBPASS','%CONFIG-DBPASS');

#Table prefix
define('TABLE_PREFIX','%CONFIG-PREFIX');
?>