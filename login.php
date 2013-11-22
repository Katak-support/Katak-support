<?php
/*********************************************************************
    login.php

    Client Login 

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require_once('main.inc.php');
if(!defined('INCLUDE_DIR')) die(_('Fatal error!'));

define('CLIENTINC_DIR',INCLUDE_DIR.'client/');
define('KTKCLIENTINC',TRUE); //make includes happy

require_once(INCLUDE_DIR.'class.client.php');
require_once(INCLUDE_DIR.'class.ticket.php');
//We are ready
$loginmsg=_('Authentication Required');
if($_POST && (!empty($_POST['lemail']) && !empty($_POST['lticket']))):
    $loginmsg=_('Authentication Required');
    $email=trim($_POST['lemail']);
    $ticketID=trim($_POST['lticket']);
    //$_SESSION['_client']=array(); #Uncomment to disable login strikes.
    
    //Check time for last max failed login attempt strike.
    $loginmsg=_('Invalid login');
    if($_SESSION['_client']['laststrike']) {
        if((time()-$_SESSION['_client']['laststrike'])<$cfg->getClientLoginTimeout()) {
            $loginmsg=_('Excessive failed login attempts');
            $errors['err']=_('You\'ve reached maximum failed login attempts allowed. Try again later or <a href="open.php">open a new ticket</a>');
        }else{ //Timeout is over.
            //Reset the counter for next round of attempts after the timeout.
            $_SESSION['_client']['laststrike']=null;
            $_SESSION['_client']['strikes']=0;
        }
    }
    //See if we can fetch local ticket id associated with the ID given
    if(!$errors && is_numeric($ticketID) && Validator::is_email($email) && ($tid=Ticket::getIdByExtId($ticketID))) {
        //At this point we know the ticket is valid.
        $ticket= new Ticket($tid);
        //TODO: 1) Check how old the ticket is...3 months max?? 2) Must be the latest 5 tickets?? 
        //Check the email given.
        if($ticket->getId() && strcasecmp($ticket->getEMail(),$email)==0){
            //valid match...create session goodies for the client.
            $user = new ClientSession($email,$ticket->getId());
            $_SESSION['_client']=array(); //clear.
            $_SESSION['_client']['userID']   =$ticket->getEmail(); //Email
            $_SESSION['_client']['key']      =$ticket->getExtId(); //Ticket ID --acts as password when used with email. See above.
            $_SESSION['_client']['token']    =$user->getSessionToken();
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
    $_SESSION['_client']['strikes']+=1;
    if(!$errors && $_SESSION['_client']['strikes']>$cfg->getClientMaxLogins()) {
        $loginmsg=('Access Denied');
        $errors['err']=_('Forgot your login info? Please <a href="open.php">open a new ticket</a>.');
        $_SESSION['_client']['laststrike']=time();
        $alert=_('Excessive login attempts by a client')."\n\n".
                _('Email') . ': '.$_POST['lemail'] . "\n" . _('Ticket No.') . ': ' . $_POST['lticket']."\n".
                _('IP') . ': ' . $_SERVER['REMOTE_ADDR'] . "\n" . _('Time') . ": " . date('M j, Y, g:i a T') . "\n".
                _('Attempts No.') . ' '.$_SESSION['_client']['strikes'];
        Sys::log(LOG_ALERT,'Excessive login attempts (client)',$alert,$_POST['lemail'],($cfg->alertONLoginError()));
    }elseif($_SESSION['_client']['strikes']%2==0){ //Log every other failed login attempt as a warning.
        $alert=_('Failed login attempts by a client') . "\n\n" . 
               _('Email').': '.$_POST['lemail'] . "\n" . 'Ticket No.' . ' ' . $_POST['lticket'] . "\n" . _('Attempts No.') . ' ' . $_SESSION['_client']['strikes'];
        Sys::log(LOG_WARNING,'Failed login attempt (client)',$alert,$_POST['lemail']);
    }
endif;
require(CLIENTINC_DIR.'header.inc.php');
require(CLIENTINC_DIR.'login.inc.php');
require(CLIENTINC_DIR.'footer.inc.php');
?>
