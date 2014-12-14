<?php
/*********************************************************************
    login.php

    User and client Login 

    Copyright (c) 2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require_once('main.inc.php');
if(!defined('INCLUDE_DIR')) die(_('Fatal error!'));

define('USERINC_DIR',INCLUDE_DIR.'user/');
define('KTKUSERINC',TRUE); //make includes happy

if(!$cfg->getUserLogRequired())
  $inc = 'login.inc.php';
else
  $inc = 'clientlogin.inc.php';

$loginmsg=_('Authentication Required');
// User login
if($_POST && (!empty($_POST['lemail']) && !empty($_POST['lticket']))):
//    $loginmsg=_('Authentication Required');
    $email=trim($_POST['lemail']);
    $ticketID=trim($_POST['lticket']);
    //$_SESSION['_user']=array(); #Uncomment to disable login strikes.
    
    //Check time for last max failed login attempt strike.
    $loginmsg=_('Invalid login');
    if($_SESSION['_user']['laststrike']) {
        if((time() - $_SESSION['_user']['laststrike']) < $cfg->getClientLoginTimeout()) {
            $loginmsg=_('Excessive failed login attempts');
            $errors['err']=_('You\'ve reached maximum failed login attempts allowed. Try again later or <a href="open.php">open a new ticket</a>');
        }else{ //Timeout is over.
            //Reset the counter for next round of attempts after the timeout.
            $_SESSION['_user']['laststrike']=null;
            $_SESSION['_user']['strikes']=0;
        }
    }
    //See if we can fetch local ticket id associated with the ID given
    if(!$errors && is_numeric($ticketID) && Validator::is_email($email) && ($tid=Ticket::getIdByExtId($ticketID))) {
        //At this point we know that a ticket with the given number exists.
        $ticket= new Ticket($tid);
        //TODO: 1) Check how old the ticket is...3 months max?? 2) Must be the latest 5 tickets?? 
        //Check the email given.
        if($ticket->getId() && strcasecmp($ticket->getEMail(),$email)==0){
            //valid email match...create session goodies for the user.
            $user = new UserSession($email,$ticket->getId());
            $_SESSION['_user']=array(); //clear.
            $_SESSION['_user']['userID']   =$ticket->getEmail(); //Email
            $_SESSION['_user']['key']      =$ticket->getExtId(); //Ticket ID --acts as password when used with email. See above.
            $_SESSION['_user']['token']    =$user->getSessionToken();
            $_SESSION['TZ_OFFSET']=$cfg->getTZoffset();
            $_SESSION['daylight']=$cfg->observeDaylightSaving();
            //Log login info...
            $msg=sprintf("%s/%s " . _("logged in"),$ticket->getEmail(),$ticket->getExtId());
            Sys::log(LOG_DEBUG,'User login',$msg,$ticket->getEmail());
            //Redirect tickets.php
            session_write_close();
            session_regenerate_id();
            @header("Location: tickets.php");
            require_once('tickets.php'); //Just incase. of header already sent error.
            exit;
        }
    }
    //If we get to this point we know the login failed.
    $_SESSION['_user']['strikes']+=1;
    if(!$errors && $_SESSION['_user']['strikes']>$cfg->getClientMaxLogins()) {
        $loginmsg=('Access Denied');
        $errors['err']=_('Forgot your login info? Please <a href="open.php">open a new ticket</a>.');
        $_SESSION['_user']['laststrike']=time();
        $alert= _('Excessive login attempts by a user')."\n\n".
                _('Email') . ': '. $email . "\n" .
                _('Ticket No.') . ': ' . $_POST['lticket']."\n".
                'IP: ' . $_SERVER['REMOTE_ADDR'] . "\n" .
                _('Time') . ": " . date('M j, Y, g:i a T') . "\n".
                _('Attempts No.') . ' '.$_SESSION['_user']['strikes'];
        Sys::log(LOG_ALERT,'Excessive login attempts (user)',$alert,$email,($cfg->alertONLoginError()));
    }elseif($_SESSION['_user']['strikes']%2==0){ //Log every other failed login attempt as a warning.
        $alert= _('Failed login attempts by a user') . "\n\n" . 
                _('Email').': ' . $email . "\n" .
                _('Ticket No.') . ' ' . $_POST['lticket'] . "\n" .
                _('Attempts No.') . ' ' . $_SESSION['_user']['strikes'];
        Sys::log(LOG_WARNING,'Failed login attempt (user)',$alert,$email);
    }
endif;

// Client login
if($_POST && (!empty($_POST['username']) && !empty($_POST['passwd']))):
//    $loginmsg=_('Authentication Required');
    $email=trim($_POST['username']);
    //$_SESSION['_user']=array(); #Uncomment to disable login strikes.
    
    //Check time for last max failed login attempt strike.
    $loginmsg=_('Invalid login');
    if($_SESSION['_user']['laststrike']) {
        if((time() - $_SESSION['_user']['laststrike']) < $cfg->getClientLoginTimeout()) {
            $loginmsg=_('Excessive failed login attempts');
            $errors['err']=_('You\'ve reached maximum failed login attempts allowed. Try again later.');
        }else{ //Timeout is over.
            //Reset the counter for next round of attempts after the timeout.
            $_SESSION['_user']['laststrike']=null;
            $_SESSION['_user']['strikes']=0;
        }
    }
    // Check password
    if (!$errors && ($thisuser = new ClientSession($_POST['username'])) && $thisuser->check_passwd($_POST['passwd'])) {
          $_SESSION['_user']=array(); //clear.
          $_SESSION['_user']['userID']   =$thisuser->getEmail(); //Email
          $_SESSION['_user']['key']      =$thisuser->getId(); //Ticket ID --acts as password when used with email. See above.
          $_SESSION['_user']['token']    =$thisuser->getSessionToken();
          $_SESSION['TZ_OFFSET']=$cfg->getTZoffset();
          $_SESSION['daylight']=$cfg->observeDaylightSaving();
          // Update last login
          $thisuser->update_lastlogin($thisuser->getId());
          //Log login info...
          $msg=sprintf("%s/%s " . _("logged in"),$thisuser->getEmail(),$thisuser->getId());
          Sys::log(LOG_DEBUG,'Client login',$msg,$thisuser->getEmail());
          //Redirect tickets.php
          session_write_close();
          session_regenerate_id();
          @header("Location: tickets.php");
          require_once('tickets.php'); //Just incase. of header already sent error.
          exit;
    }

    //If we get to this point we know the login failed.
    $_SESSION['_user']['strikes'] += 1;
    if(!$errors && $_SESSION['_user']['strikes']>$cfg->getClientMaxLogins()) {
        $loginmsg=_('Access Denied');
        $errors['err']=_('Forgot your login info? Please ask at the customer service.');
        $_SESSION['_user']['laststrike']=time();
        $alert= _('Excessive login attempts by a client')."\n\n".
                _('Email') . ': ' . $email . "\n" .
                _('Password') . ': ' . $_POST['passwd']."\n".
                'IP: ' . $_SERVER['REMOTE_ADDR'] . "\n" .
                _('Time') . ": " . date('M j, Y, g:i a T') . "\n".
                _('Attempts No.') . ' '.$_SESSION['_user']['strikes'];
        Sys::log(LOG_ALERT,'Excessive login attempts (client)',$alert,$email,($cfg->alertONLoginError()));
    }elseif($_SESSION['_user']['strikes']%2==0){ //Log every other failed login attempt as a warning.
        $alert=_('Failed login attempts by a client') . "\n\n" . 
               _('Email'). ': ' . $email . "\n" .
               _('Password') . ' ' . $_POST['passwd'] . "\n" .
               _('Attempts No.') . ' ' . $_SESSION['_user']['strikes'];
        Sys::log(LOG_WARNING,'Failed login attempt (client)',$alert,$email);
    }
endif;
require(USERINC_DIR.'header.inc.php');
require(USERINC_DIR.$inc);
require(USERINC_DIR.'footer.inc.php');
?>
