<?php
/*********************************************************************
    tickets.php

    Main client/user interface.
    Note that we are using external ID. The real (local) ids are hidden from user.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require_once('secure.inc.php');
if(!is_object($thisuser) || !$thisuser->isValid()) die(_('Access Denied')); //Double check again.

require_once(INCLUDE_DIR.'class.ticket.php');
$ticket=null;
$inc='tickets.inc.php'; //Default page...show all tickets.
//Check if any id is given...
if(($id=$_REQUEST['id']?$_REQUEST['id']:$_POST['ticket_id']) && is_numeric($id)) {
    //id given fetch the ticket info and check perm.
    $ticket= new Ticket(Ticket::getIdByExtId((int)$id));
    if(!$ticket or !$ticket->getEmail()) {
        $ticket=null; //clear.
        $errors['err']=_('Access Denied. Possibly invalid ticket ID');
    }elseif(strcasecmp($thisuser->getEmail(),$ticket->getEmail())){
        $errors['err']=_('Security violation. Repeated violations will result in your account being locked.');
        $ticket=null; //clear.
    }else{
        //Everything checked out.
        $inc='viewticket.inc.php';
    }
}
//Process post...depends on $ticket object above.
if($_POST && is_object($ticket) && $ticket->getId()):
    $errors=array();
    switch(strtolower($_POST['a'])){
    case 'postmessage':
        if(strcasecmp($thisuser->getEmail(),$ticket->getEmail())) { //double check perm again!
            $errors['err']=_('Access Denied. Possibly invalid ticket ID');
            $inc='tickets.inc.php'; //Show the tickets.               
        }

        if(!$_POST['message'])
            $errors['message']= _('Message required');
        //check attachment..if any is set
        $i = 0;
        while($_FILES['attachment']['name'][$i] && !$errors) {
          if(!$cfg->allowOnlineAttachments()) //Something wrong with the form...user shouldn't have an option to attach
              $errors['attachment']='File [ '.$_FILES['attachment']['name'].' ] rejected: no upload permission.';
          elseif(!$cfg->canUploadFileType($_FILES['attachment']['name'][$i]))
              $errors['attachment']=_('Invalid file type').' [ '.Format::htmlchars($_FILES['attachment']['name'][$i]).' ]';
           elseif($_FILES['attachment']['size'][$i]>$cfg->getMaxFileSize())
              $errors['attachment']=_('File is too big').': '.$_FILES['attachment']['size'][$i].' bytes';
          $i++;
        }
            
        if(!$errors){
            //Everything checked out...do the magic.
            if(($msgid=$ticket->postMessage($_POST['message'],'Web'))) {
                if($_FILES['attachment']['name'] && $cfg->canUploadFiles() && $cfg->allowOnlineAttachments())
                    $ticket->uploadAttachment($_FILES['attachment'],$msgid,'M');
                    
                $msg=_('Message Posted Successfully');
            }else{
                $errors['err']=_('Unable to post the message. Try again');
            }
        }else{
            $errors['err']=$errors['err']?$errors['err']:_('Error(s) occured. Please try again');
        }
        break;
    default:
        $errors['err']=_('Uknown action');
    }
    $ticket->reload();
endif;
include(USERINC_DIR.'header.inc.php');
include(USERINC_DIR.$inc);
include(USERINC_DIR.'footer.inc.php');
?>
