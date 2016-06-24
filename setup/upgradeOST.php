<?php
/*********************************************************************
    upgradeOST.php

    Katak-support upgrader script from osTicket.
    Install the system retrieving data from old osTicket v1.6 ST database.

    Copyright (c)  2012-2016 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
#inits
error_reporting(E_ERROR); //turn on fatal errors reporting
ini_set('magic_quotes_gpc', 0);
ini_set('session.use_trans_sid', 0);
ini_set('session.cache_limiter', 'nocache');
ini_set('display_errors',1); //We want the user to see errors during install process.
ini_set('display_startup_errors',1);

#start session
session_start();
require('setup.inc.php');

$errors=array();
$fp=null;
$_SESSION['abort']=false;
define('VERSION','1.2'); //Current database version number
define('VERSION_VERBOSE','1.2.0'); //Script version (what the user sees during installation process).
define('CONFIGFILE','../include/ktk-config.php'); //Katak config file full path.
define('SCHEMAFILE','./inc/ktk-upgrade-ost16ST.sql'); //Katak upgrade from osTicket SQL schema.
define('URL',rtrim('http'.(($_SERVER['HTTPS']=='on')?'s':'').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']),'setup'));

$install='<strong>Need help?</strong> &nbsp; <a href="http://www.katak-support.com/en/content/katak-pro" target="_blank">Professional Installation Available</a>';
$support='<strong>Need professional support?</strong> <a href="http://www.katak-support.com/en/content/katak-pro" target="_blank">Commercial Support Available</a>';

//Basic checks 
$inc='upgradeOST.inc.php';
$wrninc='';
$info=$install;

if((double)phpversion()<5.1){ // Too old PHP version
    $errors['err']='PHP installation seriously out of date. PHP 5.2+ is required.';
    $wrninc='php.inc.php';
}elseif(!ini_get('short_open_tag') && (double)phpversion()<5.4) {
    $errors['err']='Short open tag disabled! - with PHP version prior to 5.4 Katak Support requires it turned on.';
    $wrninc='shortopentag.inc.php';
}elseif(!file_exists(CONFIGFILE)) { 
    $errors['err']=sprintf('Configuration file (%s) missing!',basename(CONFIGFILE));
    $wrninc='missing.inc.php';
}elseif(($cFile=file_get_contents(CONFIGFILE)) && preg_match("/define\('KTSINSTALLED',TRUE\)\;/i",$cFile)){
    $errors['err']='Configuration file already modified!';
    $wrninc='unclean.inc.php';
}elseif(!file_exists(CONFIGFILE) || !is_writable(CONFIGFILE)) { //writable config file??
    clearstatcache();
    $errors['err']='Configuration file not writable';
    $wrninc='chmod.inc.php';
}else {
    $configfile=file_get_contents(CONFIGFILE); //Get the goodies...peek and tell.
    //Make SURE this is a new installation. 
    if(preg_match("/define\('KTSINSTALLED',TRUE\)\;/i",$configfile) || !strpos($configfile,'%CONFIG-DBHOST')){
        $errors['err']='Configuration file already modified!';
        $inc='unclean.inc.php';
    }elseif($_POST){
        $f=array();
        $f['username']  = array('type'=>'username', 'required'=>1, 'error'=>'Username required');
        $f['password']  = array('type'=>'password', 'required'=>1, 'error'=>'Password required');
        $f['dbhost']    = array('type'=>'string', 'required'=>1, 'error'=>'Hostname required');
        $f['dbname']    = array('type'=>'string', 'required'=>1, 'error'=>'Database name required');
        $f['dbuser']    = array('type'=>'string', 'required'=>1, 'error'=>'Username required');
        $f['dbpass']    = array('type'=>'string', 'required'=>1, 'error'=>'password required');
        $f['prefix']    = array('type'=>'string', 'required'=>1, 'error'=>'Table prefix required');
        
        $validate = new Validator($f);
        if(!$validate->validate($_POST)){
            $errors=array_merge($errors,$validate->errors());
        }
        //Check table prefix underscore required at the end!
        if($_POST['prefix'] && substr($_POST['prefix'], -1)!='_')
            $errors['prefix']='Bad prefix. Must have underscore (_) at the end. e.g \'ktk_\'';
       
        //Connect to the DB
        if(!$errors && !db_connect($_POST['dbhost'],$_POST['dbuser'],$_POST['dbpass'],$_POST['dbname']))
            $errors['mysql']='Unable to connect to MySQL server. Possibly invalid login info. <br />'; 
        //check mysql version
        if(!$errors && (db_version()<'4.4'))
            $errors['mysql']='Katak Support requires MySQL 4.4 or better! Please upgrade';
        
        //Check if it is an osTicket v1.6 ST database
        if(!$errors) {
          $sql='SHOW TABLES FROM '.$_POST['dbname'];
          if(db_query($sql) != '') {
            $sql='SELECT ostversion FROM '.$_POST['prefix'].'config';
            $result=db_fetch_array(db_query($sql));
            if(trim($result['ostversion']) != '1.6 ST') {
             $errors['err']='The database '.$_POST['dbname'].' does not seem to be a osTicket 1.6 ST database!';
                            $errors['mysql']='The database does not seem to be a osTicket 1.6 ST database!';
            }    
          }else {
             $errors['err']='The database '.$_POST['dbname'].' does not seem to be a osTicket 1.6 ST database!';
             $errors['mysql']='The database does not seem to be a osTicket 1.6 ST database!';
          }    
        }
        
        //Check admin's account data and email and retrive admin group
        if(!$errors) {
          $sql='SELECT email,group_id FROM '.$_POST['prefix'].'staff WHERE 
                 isadmin=1
                 AND username='.db_input($_POST['username']). 
                'AND passwd='.db_input(MD5($_POST['password']));
          $result=db_fetch_array(db_query($sql));
          if(!$result['email']) 
            $errors['err']='Bad admin username or password!';
          else {
            $adminemail = $result['email'];
            $admingroup = $result['group_id'];
            $sql = 'SELECT admin_email FROM '.$_POST['prefix'].'config'; // Retrive sysadmin email
            $result = db_fetch_array(db_query($sql));
            if(!strcasecmp($adminemail,$result['admin_email']))
              define('ADMIN_EMAIL',$result['admin_email']); //Needed to report SQL errors during install.
            else
              $errors['err']='The admin\'s email address does not match that in the configuration table';
          }
        }
        
        //Get database schema
        if(!$errors && !file_exists(SCHEMAFILE)) {
            $errors['err']='Internal error. Please make sure your download is the latest';
            $errors['mysql']='Missing SQL schema file';
        }
        
        //Open the configuration file for writing.
        if(!$errors && !($fp = @fopen(CONFIGFILE,'r+'))){
            $errors['err']='Unable to open config file for writing. Permission denied!';
        }

        //If no errors..Do the install/upgrade.
        if(!$errors && $fp) {
            define('PREFIX',$_POST['prefix']); //Table prefix
                     
            $debug=FALSE; //Change it to true to show failed query
            if(!load_sql_schema(SCHEMAFILE,$errors,$debug) && !$errors['err'])
                $errors['err']='Error parsing SQL schema! Get help from developers';
                
            // Mark with "F" the original text message                
            $sql = 'SELECT DISTINCT ticket_id FROM '.PREFIX.'ticket_message';
            $result = db_query($sql);
            while($message = db_fetch_array($result)) {
              $query=('UPDATE '.PREFIX.'ticket_message SET msg_type="F" 
                      WHERE ticket_id='.$message['ticket_id'].'
                      ORDER BY ticket_id
                      LIMIT 1');
              db_query($query);
            }

            //Mark the Administrator role
            $query=('UPDATE '.PREFIX.'roles SET dept_access="SADMIN" WHERE role_id='.$admingroup);
            db_query($query);
            
            
            // transfer responses data and delete table
            $sql = 'SELECT * FROM '.PREFIX.'ticket_response';
            $result = db_query($sql);
            while($responses = db_fetch_array($result)) {
              $query = ('INSERT INTO '.PREFIX.'ticket_message (ticket_id, messageId, msg_type, message, staff_id, staff_name, ip_address, created)
                          VALUES ('.$responses["ticket_id"].', '.$responses["msg_id"].', "R", "'.addslashes($responses["response"]).'", '.$responses["staff_id"].', "'.$responses["staff_name"].'", "'.$responses["ip_address"].'", "'.$responses["created"].'")');
              db_query($query);
            }            
            db_query('DROP TABLE IF EXISTS '.PREFIX.'ticket_response');
            
            
            if(!$errors) {
                $info=$support;

                //Rewrite the config file.
                $configfile= str_replace("define('KTSINSTALLED',FALSE);","define('KTSINSTALLED',TRUE);",$configfile);
                $configfile= str_replace('%ADMIN-EMAIL',ADMIN_EMAIL,$configfile);
                $configfile= str_replace('%CONFIG-DBHOST',$_POST['dbhost'],$configfile);
                $configfile= str_replace('%CONFIG-DBNAME',$_POST['dbname'],$configfile);
                $configfile= str_replace('%CONFIG-DBUSER',$_POST['dbuser'],$configfile);
                $configfile= str_replace('%CONFIG-DBPASS',$_POST['dbpass'],$configfile);
                $configfile= str_replace('%CONFIG-PREFIX',$_POST['prefix'],$configfile);
                $configfile= str_replace('%CONFIG-SIRI',Misc::randcode(32),$configfile);

                if(ftruncate($fp,0) && fwrite($fp,$configfile)){
                    // Update config info 
                    $sql='UPDATE '.PREFIX.'config SET updated=NOW() '.
                         ', ktsversion='.db_input(VERSION).'
                         , helpdesk_url="'.URL.'"';
                    db_query($sql);

                    $msg='Congratulations: Katak-support basic installation completed!';
                    $inc='done.inc.php';
                }else{
                    $errors['err']='Unable to write to config file!';
                }
            }
            @fclose($fp);
            
            //Log a message.
            $sql='INSERT INTO '.PREFIX.'syslog SET created=NOW() '.
                 ',title="Katak-support upgraded",log_type="Debug" '.
                 ',log='.db_input("osTicket succesfully upgraded to Katak-support version ".VERSION."\n\nThank you for choosing Katak-support!").
                 ',ip_address='.db_input($_SERVER['REMOTE_ADDR']);
            db_query($sql);
        }else{            
            $errors['err']=$errors['err']?$errors['err']:'Error(s) occured. Please correct them and try again';
        }
    }
}
$title=sprintf('Katak-support version %s - Installation with data retrive from osTicket 1.6 ST', VERSION_VERBOSE);

$performing = 'upgradeOST';
require("./inc/header.inc.php");
if($wrninc!='' && file_exists("./inc/$wrninc"))
    require("./inc/$wrninc");
if(file_exists("./inc/$inc"))
    require("./inc/$inc");
else
    echo '<span class="error">Invalid path - get technical support</span>';

require("../include/staff/footer.inc.php");
?>