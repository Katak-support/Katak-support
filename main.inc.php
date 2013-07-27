<?php
/*********************************************************************
    main.inc.php

    Master include file which must be included at the start of every file.
    The brain of the whole sytem. Don't monkey with it.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/    

#Disable direct access.
if(!strcasecmp(basename($_SERVER['SCRIPT_NAME']),basename(__FILE__))) die('Adiaux amikoj!');

#Disable Globals if enabled....before loading config info
if(ini_get('register_globals')) {
    ini_set('register_globals',0);
    foreach($_REQUEST as $key=>$val)
        if(isset($$key))
            unset($$key);
}

#Disable url fopen && url include
ini_set('allow_url_fopen', 0);
ini_set('allow_url_include', 0);

#Disable session ids on url.
ini_set('session.use_trans_sid', 0);
#No cache
ini_set('session.cache_limiter', 'nocache');
#Cookies
//ini_set('session.cookie_path','/katak-support/');

#Error reporting...Good idea to ENABLE error reporting to a file. i.e display_errors should be set to false
#Don't display errors in productions.
error_reporting(E_STRICT | E_ERROR);
//error_reporting(E_ERROR); 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 0);

//set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/include/pear/');


//Start the session BUT ONLY if a web request (http) host exists
if ($_SERVER['HTTP_HOST']) {
  session_start();
}

// Set Dir constants
if(!defined('ROOT_PATH')) define('ROOT_PATH','./'); //root path. Damn directories
define('ROOT_DIR',str_replace('\\\\', '/', realpath(dirname(__FILE__))).'/'); #Get real path for root dir ---linux and windows
define('INCLUDE_DIR',ROOT_DIR.'include/'); //Change this if include is moved outside the web path.
define('PEAR_DIR',INCLUDE_DIR.'pear/');
define('SETUP_DIR',INCLUDE_DIR.'setup/');

/*############## Do NOT monkey with anything else beyond this point UNLESS you really know what you are doing ##############*/

/* 
 * Current version.
 * The first two digits indicate the database's version.
 * The third digit represents minor changes that do not affect the database.
*/ 
define('THIS_VERSION','0.9.2'); //Changes from version to version.

// Check if config file exists and load config info
$configfile='';
if(file_exists(INCLUDE_DIR.'ktk-config.php'))
    $configfile=INCLUDE_DIR.'ktk-config.php';
elseif(file_exists(ROOT_DIR.'include/'))
    header('Location: '.ROOT_PATH.'setup/'); // Go to new installation

if(!$configfile || !file_exists($configfile)) die('<b>' . _('Error loading settings. Contact admin.') . '</b>');

require($configfile); // Load configuration file
define('CONFIG_FILE',$configfile); //used in admin.php to check perm.

//Path separator
if(!defined('PATH_SEPARATOR')) {
    if(strpos($_ENV['OS'],'Win')!==false || !strcasecmp(substr(PHP_OS, 0, 3),'WIN'))
        define('PATH_SEPARATOR', ';' ); //Windows
    else
        define('PATH_SEPARATOR',':'); //Linux
}

//Set include paths. Overwrite the default paths.
ini_set('include_path', './'.PATH_SEPARATOR.INCLUDE_DIR.PATH_SEPARATOR.PEAR_DIR);

#include required files
require(INCLUDE_DIR.'class.usersession.php');
require(INCLUDE_DIR.'class.pagenate.php'); //Pagenate helper!
require(INCLUDE_DIR.'class.sys.php'); //system loader & config & logger.
require(INCLUDE_DIR.'class.misc.php');
require(INCLUDE_DIR.'class.http.php');
require(INCLUDE_DIR.'class.format.php'); //format helpers
require(INCLUDE_DIR.'class.validator.php'); //Class to help with basic form input validation...please help improve it.
require(INCLUDE_DIR.'mysql.php');

#CURRENT EXECUTING SCRIPT.
define('THISPAGE',Misc::currentURL());

#pagenation default
define('PAGE_LIMIT',20);

# This is to support old installations. with no secret salt.
if(!defined('SECRET_SALT')) define('SECRET_SALT',md5(TABLE_PREFIX.ADMIN_EMAIL));

#Session related
define('SESSION_SECRET', MD5(SECRET_SALT)); //Not that useful anymore...
define('SESSION_TTL', 86400); // Default 24 hours

define('DEFAULT_PRIORITY_ID',1);
define('EXT_TICKET_ID_LEN',6); //Ticket create. when you start getting collisions. Applies only on random ticket ids.

#Tables being used sytem wide
define('CONFIG_TABLE',TABLE_PREFIX.'config');
define('SYSLOG_TABLE',TABLE_PREFIX.'syslog');

define('STAFF_TABLE',TABLE_PREFIX.'staff');
define('DEPT_TABLE',TABLE_PREFIX.'department');
define('TOPIC_TABLE',TABLE_PREFIX.'help_topic');
define('GROUP_TABLE',TABLE_PREFIX.'roles');

define('TICKET_TABLE',TABLE_PREFIX.'ticket');
define('TICKET_EVENTS_TABLE',TABLE_PREFIX.'ticket_events');
define('TICKET_MESSAGE_TABLE',TABLE_PREFIX.'ticket_message');
define('TICKET_ATTACHMENT_TABLE',TABLE_PREFIX.'ticket_attachment');
define('PRIORITY_TABLE',TABLE_PREFIX.'priority');
define('TICKET_LOCK_TABLE',TABLE_PREFIX.'ticket_lock');

define('EMAIL_TABLE',TABLE_PREFIX.'email');
define('EMAIL_TEMPLATE_TABLE',TABLE_PREFIX.'email_template');
define('BANLIST_TABLE',TABLE_PREFIX.'email_banlist');
define('API_KEY_TABLE',TABLE_PREFIX.'api_key');
define('TIMEZONE_TABLE',TABLE_PREFIX.'timezone'); 

#Connect to the DB && get configuration from database
$ferror=null;
if (!db_connect(DBHOST,DBUSER,DBPASS,DBNAME)) {
    $ferror='Unable to connect to the database';
}elseif(!($cfg=Sys::getConfig())) {
    $ferror='Unable to load config info from DB.';
}elseif(!ini_get('short_open_tag') && (double)phpversion()<5.4) { // PHP ver. < 5.4 requires short_open_tag enabled
    $ferror='Short open tag disabled! - Katak-support requires it is turned ON.';
}

if($ferror) { //Fatal error
    Sys::alertAdmin('Katak-support fatal error: ',$ferror); //try alerting sysadmin.
    die("<br /><b>Fatal error!</b> Contact system adminstrator."); //Generic error message.
    exit;
}
//Init
$cfg->init();
//Set default timezone...staff will overwrite it.
$_SESSION['TZ_OFFSET']=$cfg->getTZoffset();
$_SESSION['daylight']=$cfg->observeDaylightSaving();

#Cleanup magic quotes crap.
if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
    $_POST=Format::strip_slashes($_POST);
    $_GET=Format::strip_slashes($_GET);
    $_REQUEST=Format::strip_slashes($_REQUEST);
}
?>
