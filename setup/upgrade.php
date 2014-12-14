<?php
/*********************************************************************
    upgrade.php

    KataK upgrade script
    Install the system retrieving data from old KataK database.

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
if(!file_exists('../main.inc.php')) die('Fatal error...get tech support');
require_once('../main.inc.php'); 
require_once('setup.inc.php');


//Only admins can upgrade the system.
$thisuser = new StaffSession($_SESSION['_staff']['userID']); /*always reload???*/
if(!is_object($thisuser) || !$thisuser->getId() || !$thisuser->isValid() || !$thisuser->isadmin()){
    $_SESSION['_staff']['auth']['dest']=THISPAGE;
    $_SESSION['_staff']['auth']['msg']='Admin access level required.';
    session_write_close();
    session_regenerate_id();
    $adminloggedin = 0;
} else
    $adminloggedin = $thisuser->GetUserName();

//Let's roll.
$errors=array();
$fp=null;
define('VERSION','1.1'); //Current database version number
define('VERSION_VERBOSE','1.1.0'); //Script version (what the user sees during installation process).
define('CONFIGFILE','../include/ktk-config.php'); //Katak config file full path.
define('PREFIX',TABLE_PREFIX);

$install='<strong>Need help?</strong> &nbsp; <a href="http://www.katak-support.com/en/content/katak-pro" target="_blank">Professional Installation Available</a>';
$support='<strong>Need professional support?</strong> <a href="http://www.katak-support.com/en/content/katak-pro" target="_blank">Commercial Support Available</a>';
$info=$install;

//Basic checks 
$inc='upgrade.inc.php';
if(!strcasecmp($cfg->getVersion(),VERSION)) { // Check version
    $errors['err']=' Nothing to do! System already upgraded';
    $inc='upgradedone.inc.php';
}elseif($_SESSION['abort']){ // Check if already aborted
    die('Upgrade already aborted! Restore previous version and start all over again (logout required) or get help.');
}elseif((double)phpversion()<5.1){ // Too old PHP installation
    $errors['err']='PHP installation seriously out of date. PHP 5.2+ is required.';
    $wrninc='php.inc.php';
}elseif(!ini_get('short_open_tag') && (double)phpversion()<5.4) { // Check PHP version
    $errors['err']='Short open tag disabled! - with PHP version prior to 5.4 Katak Support requires it turned on.';
    $wrninc='shortopentag.inc.php';
}elseif($_POST && !$errors){
  if (($adminloggedin) || ($thisuser = new StaffSession($_POST['username'])) && $thisuser->getId() && $thisuser->check_passwd($_POST['password'])) {

    switch($cfg->getVersion()):
      case '0.9':  //upgrading from ver. 0.9.x.
          $schema='./inc/ktk-upgrade-0.9.sql';
          break;
      case '1.0':  //upgrading from ver. 1.0.x.
          $schema='./inc/ktk-upgrade-1.0.sql';
          break;
      default:
      		$schema=''; // This leads to an error in loading the schema
    endswitch;
    
    $vars=$errors=array();
                
    if(!load_sql_schema($schema,$errors) && !$errors['err'])
        $errors['err']='Error parsing SQL schema! Get help from developers';
    
    if(!$errors) {
        //update the version to the latest
        $sendnotices=$cfg->autoRespONNewTicket()?1:0;
        db_query('UPDATE '.CONFIG_TABLE.' SET ktsversion='.db_input(VERSION).',updated=NOW(), ticket_notice_active='.db_input($sendnotice));
    }

    if(!$errors) { //upgrade went smooth!
        //Log a message.
        $log=sprintf("Katak-support upgraded from version %s to version %s by %s \n\nThank you for choosing Katak-support!",$cfg->getVersion(),VERSION,$thisuser->getName());
        $sql='INSERT INTO '.PREFIX.'syslog SET created=NOW() '.
             ',title="Katak-support upgraded!",log_type="Debug" '.
             ',log='.db_input($log).
             ',logger='.$thisuser->getId().
             ',ip_address='.db_input($_SERVER['REMOTE_ADDR']);
        db_query($sql);

        //Report the good news.
        $inc='upgradedone.inc.php';
        $msg='Katak-support upgraded to version '.VERSION_VERBOSE;
    }else{ //errors....aborting.
        $inc='abortedupgrade.inc.php';
        $errors['err']=$errors['err']?$errors['err']:'Yikes! upgrade error(s) occured';
        $_SESSION['abort']=true;
    }
  }else {
    $errors['err']='Invalid login!';
  }
}
$title=sprintf('Katak-support version %s - Upgrade from KataK ver. %s', VERSION_VERBOSE, $cfg->getVersion());

$performing = 'upgrade';
require("./inc/header.inc.php");
if($wrninc!='' && file_exists("./inc/$wrninc"))
    require("./inc/$wrninc");
if(file_exists("./inc/$inc"))
    require("./inc/$inc");
else
    echo '<span class="error">Invalid path - get technical support</span>';

require("../include/staff/footer.inc.php");
?>
