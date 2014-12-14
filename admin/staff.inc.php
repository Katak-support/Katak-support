<?php
/*********************************************************************
    staff.inc.php

    File included on every staff page.
    Handles logins (security), file path issues and language.

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
if (basename($_SERVER['SCRIPT_NAME']) == basename(__FILE__))
    die('Adiaux amikoj!'); //Say hi to our friend..
if (!file_exists('../main.inc.php'))
    die('Fatal error!');
define('ROOT_PATH', '../'); //Path to the root dir.
require_once('../main.inc.php');

// set staff/admin language and language domain
$lang = $cfg->getStaffLanguage();
putenv('LC_MESSAGES=' . $lang);
setlocale(LC_MESSAGES, $lang . '.UTF-8', $lang . '.UTF8', $lang . '.utf8', $lang . '.utf-8');
bindtextdomain('messages', '../i18n');
bind_textdomain_codeset('messages','UTF-8');
textdomain("messages");

if (!defined('INCLUDE_DIR'))
    die(_('Fatal error!'));

/* Some more include defines specific to staff only */
define('STAFFINC_DIR', INCLUDE_DIR . 'staff/');
define('ADMIN_DIR', str_replace('//', '/', dirname(__FILE__) . '/'));

/* Define tag that included files can check */
define('KTKADMININC', TRUE);
define('KTKSTAFFINC', TRUE);

/* Tables used by staff only */
define('STD_REPLY_TABLE', TABLE_PREFIX . 'std_reply');


/* include what is needed on staff control panel */
require_once(INCLUDE_DIR . 'class.staff.php');
require_once(INCLUDE_DIR . 'class.nav.php');


/* First order of the day is see if the user is logged in and with a valid session.
  User must be valid beyond this point
  ONLY super admins can access the helpdesk on offline state.
*/

function staffLoginPage($msg) {
    $_SESSION['_staff']['auth']['dest'] = THISPAGE;
    $_SESSION['_staff']['auth']['msg'] = $msg;
    require(ADMIN_DIR . 'login.php');
    exit;
}

$thisuser = new StaffSession($_SESSION['_staff']['userID']); /* always reload??? */

//1) is the user Logged in for real && is staff.
if (!is_object($thisuser) || !$thisuser->getId() || !$thisuser->isValid()) {
    $msg = (!$thisuser || !$thisuser->isValid()) ? _('Authentication Required') : _('Session timed out due to inactivity');
    staffLoginPage($msg);
    exit;
}

//2) if not super admin..check system and role status
if (!$thisuser->isadmin()) {
    if ($cfg->isHelpDeskOffline()) {
        staffLoginPage(_('System Offline'));
        exit;
    }

    if (!$thisuser->isactive() || !$thisuser->isRoleActive()) {
        staffLoginPage(_('Access Disabled. Contact Admin'));
        exit;
    }
}

//Keep the session activity alive
$thisuser->refreshSession();
//Set staff's timezone offset.
$_SESSION['TZ_OFFSET'] = $thisuser->getTZoffset();
$_SESSION['daylight'] = $thisuser->observeDaylight();

define('AUTO_REFRESH_RATE', $thisuser->getRefreshRate() * 60);

//Clear some vars. we use in all pages.
$errors = array();
$msg = $warn = $sysnotice = '';
$tabs = array();
$submenu = array();

if (defined('THIS_VERSION') && strcasecmp($cfg->getVersion(), substr(THIS_VERSION, 0, strripos((THIS_VERSION),'.')))) {
    $errors['err'] = $sysnotice = sprintf(_('The script is version %s while the database is version %s'), substr(THIS_VERSION, 0, strripos((THIS_VERSION),'.')), $cfg->getVersion());
} elseif ($cfg->isHelpDeskOffline()) {
    $sysnotice ="<strong>". _('System is set to offline mode')."</strong> - "._('External interface is disabled and ONLY admins can access staff control panel.');
    $sysnotice.=' <a href="admin.php?t=pref">' . _('Enable') . '</a>.';
}

$nav = new StaffNav(strcasecmp(basename($_SERVER['SCRIPT_NAME']), 'admin.php') ? 'staff' : 'admin');
//Check for forced password change.
if ($thisuser->forcePasswdChange()) {
    require('profile.php'); //profile.php must request this file as require_once to avoid problems.
    exit;
}

?>
