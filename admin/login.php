<?php
/*********************************************************************
    login.php

    Handles staff authentication/logins.

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require_once('../main.inc.php');
if (!defined('INCLUDE_DIR'))
    die('Fatal error!');
    
// TODO: the following lines were introduced to have the traslation during staff login. Find a better solution!
// set staff/admin language and language domain
$cfig = Sys::getConfig();
$lang = $cfig->getStaffLanguage();
putenv('LC_MESSAGES=' . $lang);
setlocale(LC_MESSAGES, $lang);
bindtextdomain('messages', '../i18n');
bind_textdomain_codeset('messages','UTF-8');
textdomain("messages");
  
$msg = $_SESSION['_staff']['auth']['msg'];
$msg = isset($msg) ? $msg : _('Authentication Required');

if ($_POST && (!empty($_POST['username']) && !empty($_POST['passwd']))) {

    //$_SESSION['_staff']=array(); #Uncomment to disable login strikes.
    $msg = _('Invalid login');
    if ($_SESSION['_staff']['laststrike']) {
        if ((time() - $_SESSION['_staff']['laststrike']) < $cfg->getStaffLoginTimeout()) {
            $msg = _('Excessive failed login attempts');
            $errors['err'] = _('You\'ve reached maximum failed login attempts allowed.');
        } else { //Timeout is over.
            //Reset the counter for next round of attempts after the timeout.
            $_SESSION['_staff']['laststrike'] = null;
            $_SESSION['_staff']['strikes'] = 0;
        }
    }
    if (!$errors && ($user = new StaffSession($_POST['username'])) && $user->getId() && $user->check_passwd($_POST['passwd'])) {
        //update last login.
        $user->update_lastlogin($user->getId());
        //Figure out where the user is headed - destination!
        $dest = $_SESSION['_staff']['auth']['dest'];
        //Now set session crap and lets roll baby!
        $_SESSION['_staff'] = array(); //clear.
        $_SESSION['_staff']['userID'] = $_POST['username'];
        $user->refreshSession(); //set the hash.
        $_SESSION['TZ_OFFSET'] = $user->getTZoffset();
        $_SESSION['daylight'] = $user->observeDaylight();
        Sys::log(LOG_DEBUG, 'Staff login',sprintf('%s ' . _('logged in'),$user->getUserName()),$user->getUserName()); //Debug
        //Redirect to the original destination. (make sure it is not redirecting to login page.)
        $dest = ($dest && (!strstr($dest, 'login.php') && !strstr($dest, 'ajax.php'))) ? $dest : 'index.php';
        session_write_close();
        session_regenerate_id();
        @header("Location: $dest");
        require_once('index.php'); //Just incase header is messed up.
        exit;
    }
    //If we get to this point we know the login failed.
    $_SESSION['_staff']['strikes']+=1;
    if (!$errors && $_SESSION['_staff']['strikes'] > $cfg->getStaffMaxLogins()) {
        $msg = _('Access Denied');
        $errors['err'] = _('Forgot your login info? Contact IT Dept.');
        $_SESSION['_staff']['laststrike'] = time();
        $alert = _('Excessive login attempts by a staff member') . "\n\n" .
                 _('Username') . ": " . $_POST['username'] . "\n" .
                 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\n" .
                 _('Time') . ': ' . date('M j, Y, g:i a T') . "\n\n" .
                 _('Attempts No.') . ' ' . $_SESSION['_staff']['strikes'] . "\n" . 'Timeout: ' . ($cfg->getStaffLoginTimeout() / 60) . " " . _("minutes") . " \n";
        Sys::log(LOG_ALERT, 'Excessive login attempts (staff)', $alert, $_POST['username'], ($cfg->alertONLoginError()));
    } elseif ($_SESSION['_staff']['strikes'] % 2 == 0) { //Log every other failed login attempt as a warning.
        $alert = _('Failed login attempts by a staff member') . "\n\n" .
                 _('Username') . ": " . $_POST['username'] . "\n" .
                 'IP: ' . $_SERVER['REMOTE_ADDR'] . "\n" .
                 _('Time') . ": " . date('M j, Y, g:i a T') . "\n" .
                 _('Attempts No.') . ' ' . $_SESSION['_staff']['strikes'];
        Sys::log(LOG_WARNING, 'Failed login attempt (staff)', $alert, $_POST['username']);
    }
}
define("KTKADMININC", TRUE); //Make includes happy!
$login_err = ($_POST) ? true : false; //error displayed only on post
include_once(INCLUDE_DIR . 'staff/login.tpl.php');
?>