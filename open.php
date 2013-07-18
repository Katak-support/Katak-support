<?php
/*********************************************************************
    open.php

    New tickets handle.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require('client.inc.php');
define('SOURCE','Web'); //Ticket source.
$inc='open.inc.php';    //default include.
$errors=array();
if($_POST):
    $_POST['deptId']=$_POST['emailId']=0; //Just Making sure we don't accept crap...only topicId is expected.
    if(!$thisclient && $cfg->enableCaptcha()){
        if(!$_POST['captcha'])
            $errors['captcha']=_('Enter text shown on the image');
        elseif(strcmp($_SESSION['captcha'],md5($_POST['captcha'])))
            $errors['captcha']=_('Invalid - try again!');
    }
    //Ticket::create...checks for errors..
    if(($ticket=Ticket::create($_POST,$errors,SOURCE))){
        $msg=_('Support ticket request created');
        if($thisclient && $thisclient->isValid()) //Logged in...simply view the newly created ticket.
            @header('Location: tickets.php?id='.$ticket->getExtId());
        //Thank the user and promise speedy resolution!
        $inc='thankyou.inc.php';
    }else{
        // Impossible to create the ticket: display error message
        $errors['err']=$errors['err']?$errors['err']:_('Unable to create a ticket. The system administrator has been notified. Please try later!');
    }
endif;

// TODO: Check if the attachment size exceed the post_max_size directive

//page
require(CLIENTINC_DIR.'header.inc.php');
require(CLIENTINC_DIR.$inc);
require(CLIENTINC_DIR.'footer.inc.php');
?>
