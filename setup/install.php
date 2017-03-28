<?php
/*********************************************************************
    install.php

    Katak-support installer.

    Copyright (c)  2012-2017 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
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
if(!file_exists('setup.inc.php')) die('Fatal error...get tech support');
require_once('setup.inc.php');

$errors=array();
$fp=null;
$_SESSION['abort']=false;
define('VERSION','1.2'); //Current database version number
define('VERSION_VERBOSE','1.2.1'); //Script version (what the user sees during installation process).
define('CONFIGFILE','../include/ktk-config.php'); //Katak config file full path.
define('SCHEMAFILE','./inc/katak-v1.2.sql'); //Katak SQL schema.
define('URL',rtrim('http'.(($_SERVER['HTTPS']=='on')?'s':'').'://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']),'setup'));

$install='<strong>Need help?</strong> &nbsp; <a href="http://www.katak-support.com/en/content/katak-pro" target="_blank">Professional Installation Available</a>';
$support='<strong>Need professional support?</strong> <a href="http://www.katak-support.com/en/content/katak-pro" target="_blank">Commercial Support Available</a>';

//Basic checks 
$inc='install.inc.php';
$wrninc='';
$info=$install;

if((double)phpversion()<5.1){ // Too old PHP version
    $errors['err']='PHP installation seriously out of date. PHP 5.2+ is required.';
    $wrninc='php.inc.php';
}elseif(!ini_get('short_open_tag') && (double)phpversion()<5.4) {
    $errors['err']='Short open tag disabled! - with PHP version prior to 5.4 Katak Support requires it turned on.';
    $wrninc='shortopentag.inc.php';
}elseif(!function_exists('gettext')) { // Check if GETTEXT is installed
    $errors['err']='GETTEXT not installed!';
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
        $f['title']     = array('type'=>'string', 'required'=>1, 'error'=>'Title required');
        $f['sysemail']  = array('type'=>'email',  'required'=>1, 'error'=>'Valid email required');
        $f['username']  = array('type'=>'username', 'required'=>1, 'error'=>'Username required');
        $f['password']  = array('type'=>'password', 'required'=>1, 'error'=>'Password required');
        $f['password2'] = array('type'=>'password', 'required'=>1, 'error'=>'Confirm password');
        $f['email']     = array('type'=>'email',  'required'=>1, 'error'=>'Valid email required');
        $f['dbhost']    = array('type'=>'string', 'required'=>1, 'error'=>'Hostname required');
        $f['dbname']    = array('type'=>'string', 'required'=>1, 'error'=>'Database name required');
        $f['dbuser']    = array('type'=>'string', 'required'=>1, 'error'=>'Username required');
        $f['dbpass']    = array('type'=>'string', 'required'=>1, 'error'=>'password required');
        $f['prefix']    = array('type'=>'string', 'required'=>1, 'error'=>'Table prefix required');
        $f['language']  = array('type'=>'string', 'required'=>1, 'error'=>'Language required');
        
        $validate = new Validator($f);
        if(!$validate->validate($_POST)){
            $errors=array_merge($errors,$validate->errors());
        }
        if($_POST['sysemail'] && $_POST['email'] && !strcasecmp($_POST['sysemail'],$_POST['email']))
            $errors['email']='Conflicts with system email above';
        if(!$errors && strcasecmp($_POST['password'],$_POST['password2']))
            $errors['password2']='passwords to not match!';
        //Check table prefix underscore required at the end!
        if($_POST['prefix'] && substr($_POST['prefix'], -1)!='_')
            $errors['prefix']='Bad prefix. Must have underscore (_) at the end. e.g \'ktk_\'';
       
        //Connect to the DB
        if(!$errors && !db_connect($_POST['dbhost'],$_POST['dbuser'],$_POST['dbpass'],$_POST['dbname']))
            $errors['mysql']='Unable to connect to MySQL server. Possibly invalid login info. <br />'; 
        //check mysql version
        if(!$errors && (db_version()<'4.4'))
            $errors['mysql']='Katak Support requires MySQL 4.4 or better! Please upgrade';
        
        //Get database schema
        if(!$errors && !file_exists(SCHEMAFILE)) {
            $errors['err']='Internal error. Please make sure your download is the latest';
            $errors['mysql']='Missing SQL schema file';
        }
        
        //Open the configuration file for writing.
        if(!$errors && !($fp = @fopen(CONFIGFILE,'r+'))){
            $errors['err']='Unable to open config file for writing. Permission denied!';
        }

        //IF no errors..Do the install.
        if(!$errors && $fp) {  //Install a new system
            define('ADMIN_EMAIL',$_POST['email']); //Needed to report SQL errors during install.
            define('PREFIX',$_POST['prefix']); //Table prefix
            
            $debug=false; //Change it to true to show failed query
            if(!load_sql_schema(SCHEMAFILE,$errors,$debug) && !$errors['err'])
                $errors['err']='Error parsing SQL schema! Get help from developers';

            if(!$errors) {
                $info=$support;

                //Rewrite the config file.
                $configfile= str_replace("define('KTSINSTALLED',FALSE);","define('KTSINSTALLED',TRUE);",$configfile);
                $configfile= str_replace('%ADMIN-EMAIL',$_POST['email'],$configfile);
                $configfile= str_replace('%CONFIG-DBHOST',$_POST['dbhost'],$configfile);
                $configfile= str_replace('%CONFIG-DBNAME',$_POST['dbname'],$configfile);
                $configfile= str_replace('%CONFIG-DBUSER',$_POST['dbuser'],$configfile);
                $configfile= str_replace('%CONFIG-DBPASS',$_POST['dbpass'],$configfile);
                $configfile= str_replace('%CONFIG-PREFIX',$_POST['prefix'],$configfile);
                $configfile= str_replace('%CONFIG-SIRI',Misc::randcode(32),$configfile);

                if(ftruncate($fp,0) && fwrite($fp,$configfile)){
                    //Some more configurations.
                    $tzoffset= date("Z")/3600; //Server's offset.
                    //Create admin user. Dummy first and last name.
                    $sql='INSERT INTO '.PREFIX.'staff SET created=NOW(), isadmin=1,change_passwd=0,role_id=1,dept_id=1 '.
                        ',email='.db_input($_POST['email']).',firstname='.db_input('System').',lastname='.db_input('Administrator').
                        ',username='.db_input($_POST['username']).',passwd='.db_input(PhpassHashedPass::hash($_POST['password'])).
                        ',timezone_offset='.db_input($tzoffset);
                    db_query($sql);
                    //Add emails - hopefully the domain is actually valid
                    list($uname,$domain)=explode('@',$_POST['sysemail']);
                    //1 - main support email
                    $sql='INSERT INTO '.PREFIX.'email SET created=NOW(),updated=NOW(),priority_id=2,dept_id=1'.
                         ',name='.db_input('Katak-support').',email='.db_input($_POST['sysemail']);
                    db_query($sql);
                    //2 - alert email
                    $sql='INSERT INTO '.PREFIX.'email SET created=NOW(),updated=NOW(),priority_id=1,dept_id=1'.
                         ',name='.db_input('Katak-support Alerts').',email='.db_input('alerts@'.$domain);
                    db_query($sql);
                    //3 - noreply email
                    $sql='INSERT INTO '.PREFIX.'email SET created=NOW(),updated=NOW(),priority_id=1,dept_id=1'.
                         ',name='.db_input('').',email='.db_input('noreply@'.$domain);
                    db_query($sql);
                    //config info 
                    $sql='INSERT INTO '.PREFIX.'config SET updated=NOW() '.
                         ',isonline=0,default_email_id=1,alert_email_id=2,default_dept_id=1,default_template_id=1'.
                         ',staff_language='.db_input($_POST['language']).
                         ',user_language='.db_input($_POST['language']).
                         ',timezone_offset='.db_input($tzoffset).
                         ',ktsversion='.db_input(VERSION).
                         ',helpdesk_url='.db_input(URL).
                         ',helpdesk_title='.db_input($_POST['title']);
                    db_query($sql);
                    //Create a first ticket as welcome and example.
                    $sql='INSERT INTO '.PREFIX.'ticket SET created=NOW(),ticketID='.db_input(Misc::randNumber(6)).
                        ',priority_id=2,topic_id=1,dept_id=1,email="'.$_POST['sysemail'].'",name="Katak-support" '.
                        ',subject="Katak-support installed!",status="open",source="Web"';
                    if(db_query($sql) && ($id=db_insert_id())){
                        db_query('INSERT INTO '.PREFIX.'ticket_message SET ticket_id=1,msg_type="F",message="'.db_input(KATAK_INSTALLED).'",source="web",created=NOW()');
                    }
                    //Log a message.
                    $sql='INSERT INTO '.PREFIX.'syslog SET created=NOW() '.
                         ',title="Katak-support installed",log_type="Debug" '.
                         ',log='.db_input("Katak-support ".VERSION." basic installation completed\n\nThank you for choosing Katak-support!").
                         ',ip_address='.db_input($_SERVER['REMOTE_ADDR']);
                    db_query($sql);
                    $msg='Congratulations: Katak-support basic installation completed!';
                    $inc='done.inc.php';
                }else{
                    $errors['err']='Unable to write to config file!';
                }
            }
            @fclose($fp);
        }else{            
            $errors['err']=$errors['err']?$errors['err']:'Error(s) occured. Please correct them and try again';
        }
    }
}
$title=sprintf('Katak-support version %s - Basic installation',VERSION_VERBOSE);

$performing = 'install';
require("./inc/header.inc.php");
if($wrninc!='' && file_exists("./inc/$wrninc"))
    require("./inc/$wrninc");
if(file_exists("./inc/$inc"))
    require("./inc/$inc");
else
    echo '<span class="error">Invalid path - get technical support</span>';

require("../include/staff/footer.inc.php");
?>