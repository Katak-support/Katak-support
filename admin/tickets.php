<?php
/*********************************************************************
    tickets.php
    
    Handles all tickets related actions for staff.
    
    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require_once('staff.inc.php');
require_once(INCLUDE_DIR . 'class.ticket.php');
require_once(INCLUDE_DIR . 'class.dept.php');
require_once(INCLUDE_DIR . 'class.banlist.php');


$page = '';
$ticket = null; //clean start.
//LOCKDOWN...See if the id provided is actually valid and if the user has access.
if (!$errors && ($id = $_REQUEST['id'] ? $_REQUEST['id'] : $_POST['ticket_id']) && is_numeric($id)) {
    $deptID = 0;
    $ticket = new Ticket($id);
    if (!$ticket or !$ticket->getDeptId())
        $errors['err'] = _('Unknown ticket ID#') . $id; //Sucker...invalid id
    elseif (!$thisuser->isAdmin() && (!$thisuser->canAccessDept($ticket->getDeptId()) && $thisuser->getId() != $ticket->getStaffId()))
        $errors['err'] = _('Access denied. Contact admin if you believe this is in error.');
    if (!$errors && $ticket->getId() == $id)
        $page = 'viewticket.inc.php'; //Default - view
}
elseif (isset($_REQUEST['a']) && $_REQUEST['a'] == 'open') {
    //TODO: Check perm here..
    $page = 'newticket.inc.php';
}
//At this stage we know the access status. we can process the post.
if ($_POST && !$errors):

    if ($ticket && $ticket->getId()) {
        $errors = array();
        $lock = $ticket->getLock(); //Ticket lock if any
        $statusKeys = array('open' => 'Open', 'Reopen' => 'Open', 'Close' => 'Closed');
        switch (strtolower($_POST['a'])):
            case 'reply':
              $fields = array();
                $fields['response'] = array('type' => 'text', 'required' => 1, 'error' => _('Response message required'));
                $params = new Validator($fields);
                if (!$params->validate($_POST)) {
                   $errors = array_merge($errors, $params->errors());
                }
                //Use locks to avoid double replies
                if ($lock && $lock->getStaffId() != $thisuser->getId())
                    $errors['err'] = _('Action Denied. Ticket is locked by someone else!');

                //Check attachments restrictions.
                $i = 0;
                while($_FILES['attachment']['name'][$i]) {
                  if(!$cfg->allowOnlineAttachments()) //Something wrong with the form...user shouldn't have an option to attach
                      $errors['attachment']='File [ '.$_FILES['attachment']['name'].' ] rejected: no upload permission.';
                  elseif (!$cfg->canUploadFiles()) //TODO: saved vs emailed attachments...admin config??
                      $errors['attachment'] = _('Upload dir invalid. Contact admin.');
                  elseif(!$cfg->canUploadFileType($_FILES['attachment']['name'][$i]))
                      $errors['attachment']=_('Invalid file type').' [ '.Format::htmlchars($_FILES['attachment']['name'][$i]).' ]';
                  elseif($_FILES['attachment']['size'][$i]>$cfg->getMaxFileSize())
                      $errors['attachment']=_('File is too big').': '.$_FILES['attachment']['size'][$i].' bytes';
                  $i++;
                }
                
                //Make sure the email is not banned
                if (!$errors && BanList::isbanned($ticket->getEmail()))
                    $errors['err'] = _('Email is in banlist. Must be removed to reply');

                //If no error...do the do.
                if (!$errors && ($respId = $ticket->postResponse($_POST['response'], $_POST['signature'], $_FILES['attachment']))) {
                    $msg = _('Response Posted Successfully');
                    //Set status if any.
                    $wasOpen = $ticket->isOpen();
                    if (isset($_POST['ticket_status']) && $_POST['ticket_status']) {
                        if ($ticket->setStatus($_POST['ticket_status']) && $ticket->reload()) {
                            $note = sprintf(_('%s %s the ticket on reply'), $thisuser->getName(), $ticket->isOpen() ? _('reopened') : _('closed'));
                            $ticket->logActivity(sprintf(_('Ticket status changed to %s'), ($ticket->isOpen() ? _('Open') : _('Closed'))), $note);
                        }
                    }
                    //Finally upload attachment if any
                    if ($_FILES['attachment'] && $_FILES['attachment']['size']) {
                        $ticket->uploadAttachment($_FILES['attachment'], $respId, 'R');
                    }
                    $ticket->reload();
                    //Mark the ticket answered if OPEN.
                    if ($ticket->isopen()) {
                        $ticket->markAnswered();
                    } elseif ($wasOpen) { //Closed on response???
                        $page = $ticket = null; //Going back to main listing.
                    }
                } elseif (!$errors['err']) {
                    $errors['err'] = _('Unable to post the response.');
                }
                break;
            case 'transfer':
                $fields = array();
                $fields['dept_id'] = array('type' => 'int', 'required' => 1, 'error' => _('Select Department'));
                $fields['message'] = array('type' => 'text', 'required' => 1, 'error' => _('Note/Message required'));
                $params = new Validator($fields);
                if (!$params->validate($_POST)) {
                    $errors = array_merge($errors, $params->errors());
                }

                if (!$errors && ($_POST['dept_id'] == $ticket->getDeptId()))
                    $errors['dept_id'] = _('Ticket already in the Dept.');

                if (!$errors && !$thisuser->canTransferTickets())
                    $errors['err'] = _('Action Denied. You are not allowed to transfer tickets.');

                if (!$errors && $ticket->transfer($_POST['dept_id'])) {
                    $olddept = $ticket->getDeptName();
                    $ticket->reload(); //dept manager changed!
                    //Send out alerts?? - for now yes....part of internal note!
                    $title = sprintf(_('Dept. Transfer from %s to %s'), $olddept, $ticket->getDeptName());
                    $ticket->postNote($title, $_POST['message']);
                    $msg = sprintf(_('Ticket transfered sucessfully to %s Dept.'), $ticket->getDeptName());
                    if (!$thisuser->canAccessDept($_POST['dept_id']) && $ticket->getStaffId() != $thisuser->getId()) { //Check access.
                        //Staff doesn't have access to the new department.
                        $page = 'tickets.inc.php';
                        $ticket = null;
                    }
                } elseif (!$errors['err']) {
                    $errors['err'] = _('Unable to complete the transfer');
                }
                break;
            case 'assign':
                $fields = array();
                $fields['staffId'] = array('type' => 'int', 'required' => 1, 'error' => _('Select assignee'));
                $fields['assign_message'] = array('type' => 'text', 'required' => 1, 'error' => _('Message required'));
                $params = new Validator($fields);
                if (!$params->validate($_POST)) {
                    $errors = array_merge($errors, $params->errors());
                }
                if (!$errors && $ticket->isAssigned()) {
                    if ($_POST['staffId'] == $ticket->getStaffId())
                        $errors['staffId'] = _('Ticket already assigned to the staff.');
                }
                //if already assigned.
                if (!$errors && $ticket->isAssigned()) { //Re assigning.
                    //Already assigned to the user?
                    if ($_POST['staffId'] == $ticket->getStaffId())
                        $errors['staffId'] = _('Ticket already assigned to the staff.');
                    //Admin, Dept manager (any) or current assigneee ONLY can reassign
                    if (!$thisuser->isadmin() && !$thisuser->isManager() && $thisuser->getId() != $ticket->getStaffId())
                        $errors['err'] = _('Ticket already assigned. You do not have permission to re-assign assigned tickets');
                }
                if (!$errors && $ticket->assignStaff($_POST['staffId'], $thisuser->getId(), $_POST['assign_message'], TRUE)) {
                    $staff = $ticket->getStaff();
                    $msg = _('Ticket Assigned to') . ' ' . ($staff ? $staff->getName() : _('staff'));
                    //Remove all the locks and go back to index page.
                    TicketLock::removeStaffLocks($thisuser->getId(), $ticket->getId());
                    $page = 'tickets.inc.php';
                    $ticket = null;
                } elseif (!$errors['err']) {
                    $errors['err'] = _('Unable to assign the ticket');
                }
                break;
            case 'postnote':
                $fields = array();
                $fields['title'] = array('type' => 'string', 'required' => 1, 'error' => _('Title required'));
                $fields['note'] = array('type' => 'string', 'required' => 1, 'error' => _('Note message required'));
                $params = new Validator($fields);
                if (!$params->validate($_POST))
                    $errors = array_merge($errors, $params->errors());

                if (!$errors && $ticket->postNote($_POST['title'], $_POST['note'])) {
                    $msg = _('Internal note posted');
                    if (isset($_POST['ticket_status']) && $_POST['ticket_status']) {
                        if ($ticket->setStatus($_POST['ticket_status']) && $ticket->reload()) {
                            $msg.=' ' . _('and status set to') . ' ' . ($ticket->isClosed() ? _('closed') : _('open'));
                            if ($ticket->isClosed())
                                $page = $ticket = null; //Going back to main listing.

                        }
                    }
                }elseif (!$errors['err']) {
                    $errors['err'] = _('Error(s) occured. Unable to post the note.');
                }
                break;
            case 'update':
                $page = 'editticket.inc.php';
                if (!$ticket || !$thisuser->canEditTickets())
                    $errors['err'] = _('Perm. Denied. You are not allowed to edit tickets');
                elseif ($ticket->update($_POST, $errors)) {
                    $msg = _('Ticket updated successfully');
                    $page = 'viewticket.inc.php';
                } elseif (!$errors['err']) {
                    $errors['err'] = _('Error(s) occured! Try again.');
                }
                break;
            case 'process':
                $isdeptmanager = ($ticket->getDeptId() == $thisuser->getDeptId()) ? true : false;
                switch (strtolower($_POST['do'])):
                    case 'edit':
                        if ($thisuser->canEditTickets() || ($thisuser->isManager() && $ticket->getDeptId() == $thisuser->getDeptId()))
                            $page = 'editticket.inc.php';
                        else
                            $errors['err'] = _('Access denied. You are not allowed to edit this ticket. Contact admin if you believe this is in error');
                        break;
                    case 'change_priority':
                        if (!$thisuser->canManageTickets() && !$thisuser->isManager()) {
                            $errors['err'] = _('Perm. Denied. You are not allowed to change ticket\'s priority');
                        } elseif (!$_POST['ticket_priority'] or !is_numeric($_POST['ticket_priority'])) {
                            $errors['err'] = _('You must select priority');
                        }
                        if (!$errors) {
                            if ($ticket->setPriority($_POST['ticket_priority'])) {
                                $msg = _('Priority Changed Successfully');
                                $ticket->reload();
                                $note = sprintf(_('Ticket priority set to "%s" by %s'), $ticket->getPriority(), $thisuser->getName());
                                $ticket->logActivity(_('Priority Changed'), $note);
                            } else {
                                $errors['err'] = _('Problems changing priority. Try again');
                            }
                        }
                        break;
                    case 'close':
                        if (!$thisuser->isadmin() && !$thisuser->canCloseTickets()) {
                            $errors['err'] = _('Perm. Denied. You are not allowed to close tickets.');
                        } else {
                            if ($ticket->close()) {
                                $msg = sprintf(_('Ticket #%s status set to CLOSED'), $ticket->getExtId());
                                $note = sprintf(_('Ticket closed without response by %s'), $thisuser->getName());
                                $ticket->logActivity(_('Ticket Closed'), $note);
                                $page = $ticket = null; //Going back to main listing.
                            } else {
                                $errors['err'] = _('Problems closing the ticket. Try again');
                            }
                        }
                        break;
                    case 'reopen':
                        //if they can close...then assume they can reopen.
                        if (!$thisuser->isadmin() && !$thisuser->canCloseTickets()) {
                            $errors['err'] = _('Perm. Denied. You are not allowed to reopen tickets.');
                        } else {
                            if ($ticket->reopen()) {
                                $msg = _('Ticket status set to OPEN');
                                $note = _('Ticket reopened (without comments)');
                                if ($_POST['ticket_priority']) {
                                    $ticket->setPriority($_POST['ticket_priority']);
                                    $ticket->reload();
                                    $note.=' ' . _('and status set to') . ' ' . $ticket->getPriority();
                                }
                                $note.=' by ' . $thisuser->getName();
                                $ticket->logActivity(_('Ticket Reopened'), $note);
                            } else {
                                $errors['err'] = _('Problems reopening the ticket. Try again');
                            }
                        }
                        break;
                    case 'release':
                        if (!($staff = $ticket->getStaff()))
                            $errors['err'] = _('Ticket is not assigned!');
                        elseif ($ticket->release()) {
                            $msg = sprintf(_('Ticket released (unassigned) from %s by %s'), $staff->getName(), $thisuser->getName());
                            ;
                            $ticket->logActivity(_('Ticket unassigned'), $msg);
                        }else
                            $errors['err'] = _('Problems releasing the ticket. Try again');
                        break;
                    case 'overdue':
                        //Mark the ticket as overdue
                        if (!$thisuser->isadmin() && !$thisuser->isManager()) {
                            $errors['err'] = _('Perm. Denied. You are not allowed to flag tickets overdue');
                        } else {
                            if ($ticket->markOverdue()) {
                                $msg = _('Ticket flagged as overdue');
                                $note = $msg;
                                if ($_POST['ticket_priority']) {
                                    $ticket->setPriority($_POST['ticket_priority']);
                                    $ticket->reload();
                                    $note.=' ' . _('and status set to') . ' ' . $ticket->getPriority();
                                }
                                $note.=' by ' . $thisuser->getName();
                                $ticket->logActivity(_('Ticket marked Overdue'), $note);
                            } else {
                                $errors['err'] = _('Problems marking the the ticket overdue. Try again');
                            }
                        }
                        break;
                    case 'banemail':
                        if (!$thisuser->isadmin() && !$thisuser->canManageBanList()) {
                            $errors['err'] = _('Perm. Denied. You are not allowed to ban emails');
                        } elseif (Banlist::add($ticket->getEmail(), $thisuser->getName())) {
                            $msg = sprintf(_('Email (%s) added to banlist'), $ticket->getEmail());
                            if ($ticket->isOpen() && $ticket->close()) {
                                $msg.= ' ' . _('& ticket status set to closed');
                                $ticket->logActivity(_('Ticket Closed'), $msg);
                                $page = $ticket = null; //Going back to main listing.
                            }
                        } else {
                            $errors['err'] = _('Unable to add the email to banlist');
                        }
                        break;
                    case 'unbanemail':
                        if (!$thisuser->isadmin() && !$thisuser->canManageBanList()) {
                            $errors['err'] = _('Perm. Denied. You are not allowed to remove emails from banlist.');
                        } elseif (Banlist::remove($ticket->getEmail())) {
                            $msg = _('Email removed from banlist');
                        } else {
                            $errors['err'] = _('Unable to remove the email from banlist. Try again.');
                        }
                        break;
                    case 'delete': // Dude what are you trying to hide? bad customer support??
                        if (!$thisuser->isadmin() && !$thisuser->canDeleteTickets()) {
                            $errors['err'] = _('Perm. Denied. You are not allowed to DELETE tickets!!');
                        } else {
                            if ($ticket->delete()) {
                                $page = 'tickets.inc.php'; //ticket is gone...go back to the listing.
                                $msg = _('Ticket Deleted Forever');
                                $ticket = null; //clear the object.
                            } else {
                                $errors['err'] = _('Problems deleting the ticket. Try again');
                            }
                        }
                        break;
                    default:
                        $errors['err'] = _('You must select action to perform');
                endswitch;
                break;
            default:
                $errors['err'] = _('Unknown action');
        endswitch;
        if ($ticket && is_object($ticket))
            $ticket->reload(); //Reload ticket info following post processing

    }elseif ($_POST['a']) {
        switch ($_POST['a']) {
            case 'mass_process':
                if (!$thisuser->canManageTickets())
                    $errors['err'] = _('You do not have permission to mass manage tickets. Contact admin for such access');
                elseif (!$_POST['tids'] || !is_array($_POST['tids']))
                    $errors['err'] = _('No tickets selected. You must select at least one ticket.');
                elseif (($_POST['reopen'] || $_POST['close']) && !$thisuser->canCloseTickets())
                    $errors['err'] = _('You do not have permission to close/reopen tickets');
                elseif ($_POST['delete'] && !$thisuser->canDeleteTickets())
                    $errors['err'] = _('You do not have permission to delete tickets');
                elseif (!$_POST['tids'] || !is_array($_POST['tids']))
                    $errors['err'] = _('You must select at least one ticket');

                if (!$errors) {
                    $count = count($_POST['tids']);
                    if (isset($_POST['reopen'])) {
                        $i = 0;
                        $note = _('Ticket reopened by') . ' ' . $thisuser->getName();
                        foreach ($_POST['tids'] as $k => $v) {
                            $t = new Ticket($v);
                            if ($t && @$t->reopen()) {
                                $i++;
                                $t->logActivity(_('Ticket Reopened'), $note, false, 'System');
                            }
                        }
                        $msg = "$i " . _("of") . " $count " . _("selected tickets reopened");
                    } elseif (isset($_POST['close'])) {
                        $i = 0;
                        $note = _('Ticket closed without response by') . ' ' . $thisuser->getName();
                        foreach ($_POST['tids'] as $k => $v) {
                            $t = new Ticket($v);
                            if ($t && @$t->close()) {
                                $i++;
                                $t->logActivity(_('Ticket Closed'), $note, false, 'System');
                            }
                        }
                        $msg = "$i " . _("of") . " $count " . _("selected tickets closed");
                    } elseif (isset($_POST['overdue'])) {
                        $i = 0;
                        $note = _('Ticket flagged as overdue by') . ' ' . $thisuser->getName();
                        foreach ($_POST['tids'] as $k => $v) {
                            $t = new Ticket($v);
                            if ($t && !$t->isoverdue())
                                if ($t->markOverdue()) {
                                    $i++;
                                    $t->logActivity(_('Ticket Marked Overdue'), $note, false, 'System');
                                }
                        }
                        $msg = "$i " . _("of") . " $count " . _("selected tickets marked overdue");
                    } elseif (isset($_POST['delete'])) {
                        $i = 0;
                        foreach ($_POST['tids'] as $k => $v) {
                            $t = new Ticket($v);
                            if ($t && @$t->delete())
                                $i++;
                        }
                        $msg = "$i " . _("of") . " $count " . _("selected tickets deleted");
                    }
                }
                break;
            case 'open':
                $ticket = null;
                //TODO: check if the user is allowed to create a ticket.
                if (($ticket = Ticket::create_by_staff($_POST, $errors))) {
                    $ticket->reload();
                    $msg = _('Ticket created successfully');
                    if ($thisuser->canAccessDept($ticket->getDeptId()) || $ticket->getStaffId() == $thisuser->getId()) {
                        //View the sucker
                        $page = 'viewticket.inc.php';
                    } else {
                        //Staff doesn't have access to the newly created ticket's department.
                        $page = 'tickets.inc.php';
                        $ticket = null;
                    }
                } elseif (!$errors['err']) {
                    $errors['err'] = _('Unable to create the ticket. Correct the error(s) and try again');
                }
                break;
        }
    }
    $crap = '';
endif;
//Navigation 
$submenu = array();
/* quick stats... */
$sql = 'SELECT count(open.ticket_id) as open, count(overdue.ticket_id) as overdue, count(assigned.ticket_id) as assigned ' .
        ' FROM ' . TICKET_TABLE . ' ticket ' .
        'LEFT JOIN ' . TICKET_TABLE . ' open ON open.ticket_id=ticket.ticket_id AND open.status=\'open\' ' .
        'LEFT JOIN ' . TICKET_TABLE . ' overdue ON overdue.ticket_id=ticket.ticket_id AND overdue.status=\'open\' AND overdue.isoverdue=1 ' .
        'LEFT JOIN ' . TICKET_TABLE . ' assigned ON assigned.ticket_id=ticket.ticket_id AND assigned.staff_id=' . db_input($thisuser->getId()) . ' AND assigned.status=\'open\' ';
if (!$thisuser->isAdmin()) {
  if($thisuser->isManager())
    $sql.=' WHERE ticket.dept_id IN(' . implode(',', $thisuser->getDeptsId()) . ') OR ticket.staff_id=' . db_input($thisuser->getId());
  elseif(!$thisuser->canViewunassignedTickets())
    $sql.=' WHERE (ticket.dept_id IN(' . implode(',', $thisuser->getDeptsId()) . ') AND ticket.staff_id=' . db_input($thisuser->getId()).') OR ticket.staff_id=' . db_input($thisuser->getId());
  else
    $sql.=' WHERE (ticket.dept_id IN(' . implode(',', $thisuser->getDeptsId()) . ') AND (ticket.staff_id=0 OR ticket.staff_id='. db_input($thisuser->getId()).')) OR ticket.staff_id=' . db_input($thisuser->getId());
  }
// echo $sql;

$stats = db_fetch_array(db_query($sql));
//print_r($stats);
$nav->setTabActive('tickets');

if($stats['open']){
  $nav->addSubMenu(array('desc' => sprintf(_('OPEN TICKETS (%s)'), $stats['open']), 'title' => _('Open Tickets'), 'href' => 'tickets.php', 'iconclass' => 'Ticket'));
}

if ($stats['assigned'] && $thisuser->canViewunassignedTickets()) {
    if (!$sysnotice && $stats['assigned'] > 10)
        $sysnotice = $stats['assigned'] . ' ' . _('assigned to you!');

    $nav->addSubMenu(array('desc' => sprintf(_('MY TICKETS (%s)'), $stats['assigned']), 'title' => _('Assigned Tickets'),
        'href' => 'tickets.php?status=assigned', 'iconclass' => 'assignedTickets'));
}

if ($stats['overdue']) {
    $nav->addSubMenu(array('desc' => sprintf(_('OVERDUE (%s)'), $stats['overdue']), 'title' => _('Stale Tickets'),
        'href' => 'tickets.php?status=overdue', 'iconclass' => 'overdueTickets'));

    if (!$sysnotice && $stats['overdue'] > 10)
        $sysnotice = $stats['overdue'] . ' ' . _('overdue tickets!');
}

$nav->addSubMenu(array('desc' => _('CLOSED TICKETS'), 'title' => _('Closed Tickets'), 'href' => 'tickets.php?status=closed', 'iconclass' => 'closedTickets'));


if ($thisuser->canCreateTickets()) {
    $nav->addSubMenu(array('desc' => _('NEW TICKET'), 'href' => 'tickets.php?a=open', 'iconclass' => 'newTicket'));
}

//Render the page...
$inc = $page ? $page : 'tickets.inc.php';

//If we're on tickets page...set refresh rate if the user has it configured. No refresh on search and POST to avoid repost.
if (!$_POST && $_REQUEST['a'] != 'search' && !strcmp($inc, 'tickets.inc.php') && ($min = $thisuser->getRefreshRate())) {
    define('AUTO_REFRESH', 1);
}

require_once(STAFFINC_DIR . 'header.inc.php');
require_once(STAFFINC_DIR . $inc);
require_once(STAFFINC_DIR . 'footer.inc.php');
?>