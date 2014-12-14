<?php
//Note that ticket is initiated in admin/tickets.php.
if(!defined('KTKADMININC') || !@$thisuser->isStaff() || !is_object($ticket) ) die(_('Invalid path'));
if(!$ticket->getId() or (!$thisuser->canAccessDept($ticket->getDeptId()) and $thisuser->getId()!=$ticket->getStaffId())) die(_('Access Denied'));

$info=($_POST && $errors)?Format::input($_POST):array(); //Re-use the post info on error...savekeyboards.org

//Auto-lock the ticket if locking is enabled..if locked already simply renew it.
if($cfg->getLockTime() && !$ticket->acquireLock())
    $warn.=_('Unable to obtain a lock on the ticket');

$dept  = $ticket->getDept();  //Dept
$staff = $ticket->getStaff(); //Assiged staff.
$lock  = $ticket->getLock();  //Ticket lock obj
$id    = $ticket->getId(); //Ticket ID.

if(!$errors['err'] && ($lock && $lock->getStaffId()!=$thisuser->getId()))
    $errors['err']=_('This ticket is currently locked by another staff member!');
if(!$errors['err'] && ($emailBanned=BanList::isbanned($ticket->getEmail())))
    $errors['err']=_('Email is in banlist! Must be removed before any reply/response');
?>

<div id="ticketnumber"><?= _('Ticket #') ?><?=$ticket->getExtId()?>&nbsp;<a href="tickets.php?id=<?=$id?>" title="<?= _('Reload') ?>"><span class="Icon refresh">&nbsp;</span></a></div>
<?php
if($ticket->isOverdue())
  echo '<div id="ticketstatus"><span class="Icon overdueTicket" title="Marked overdue!">&nbsp;</span></div>';
if($staff)
  echo '<div id="ticketstatus"><span class="Icon assignedTicket" title="Ticket assigned to '.$staff->getName().'">&nbsp;</span></div>';
?>
<br style="clear:both;" />
<table align="left" class="ticketinfo" cellspacing="1" cellpadding="3" width="50%" border=0>
	<tr>
		<th><?= _('Status:') ?></th>
    <td><?=$ticket->getStatus()?></td>
  </tr>
  <tr>
		<th><?= _('Priority:') ?></th>
		<td><?=$ticket->getPriority()?></td>
  </tr>
  <tr>
    <th><?= _('Department:') ?></th>
    <td><?=Format::htmlchars($ticket->getDeptName())?></td>
  </tr>
  <tr>
    <th><?= _('Created on:') ?></th>
    <td><?=Format::db_datetime($ticket->getCreateDate())?></td>
  </tr>
</table>
<table align="right" class="ticketinfo" cellspacing="1" cellpadding="3" width="50%" border=0>
  <tr>
    <th><?= _('Name:') ?></th>
    <td><?=Format::htmlchars($ticket->getName())?></td>
  </tr>
  <tr>
    <th><?= _('Email:') ?></th>
    <td><?php 
        echo $ticket->getEmail();
        if(($related=$ticket->getRelatedTicketsCount())) {
          echo sprintf('&nbsp;&nbsp;<a href="tickets.php?a=search&query=%s" title="'._('Related Tickets').'">(<b>%d</b>)</a>',
                       urlencode($ticket->getEmail()),$related);
        }
        ?>
    </td>
  </tr>
  <tr>
    <th><?= _('Phone:') ?></th>
    <td><?=Format::phone($ticket->getPhoneNumber())?></td>
  </tr>
  <tr>
    <th><?= _('Help Topic:') ?></th>
    <td><?php
        $ht=$ticket->getTopic();
        echo Format::htmlchars($ht?$ht:'N/A');
        ?>
    </td>
  </tr>
</table>
<br style="clear:both;" />
<table align="left" class="ticketinfo" cellspacing="1" cellpadding="3" width="50%" border=0>
    <tr>
        <th><?= _('Assigned Staff:') ?></th>
        <td><?=$staff?Format::htmlchars($staff->getName()):'- unassigned -'?></td>
    </tr>
    <tr>
        <th nowrap><?= _('Last Response:') ?></th>
        <td><?=Format::db_datetime($ticket->getLastResponseDate())?></td>
    </tr>
    <?php
    if($ticket->isOpen()){ ?>
    <tr>
        <th><?= _('Due Date:') ?></th>
        <td><?=Format::db_datetime($ticket->getDueDate())?></td>
    </tr>
    <?php
    }else { ?>
    <tr>
        <th><?= _('Close Date:') ?></th>
        <td><?=Format::db_datetime($ticket->getCloseDate())?></td>
    </tr>
    <?php
    }
    ?>
</table>
<table align="right" class="ticketinfo" cellspacing="1" cellpadding="3" width="50%" border=0>
    <tr>
        <th><?= _('Source:') ?></th>
        <td><?=$ticket->getSource()?></td>
    </tr>
    <tr>
        <th><?= _('IP Address:') ?></th>
        <td><?=$ticket->getIP()?></td>
    </tr>
    <tr><th nowrap><?= _('Last Message:') ?></th>
        <td><?=Format::db_datetime($ticket->getLastMessageDate())?></td>
    </tr>
</table>
<br style="clear:both;" />
<div class="messagelabel"><?= _('Subject:') ?> &nbsp; <?=Format::htmlchars($ticket->getSubject())?></div>
<?php
//get messages and shows the first message (ticket text)
$msgres=$ticket->getMessages();
$msg_row = db_fetch_array($msgres);
?>
<div class="msg"><?=_('Ticket text:') ?></div>
<div class="firstmessage">
  <?=Format::display($msg_row['message'])?>
</div>
<?php if($msg_row['attachments']>0){ ?>
  <div class="firstmessage">
  <?=$ticket->getAttachmentStr($msg_row['msg_id'],'M')?>
  </div>
<?php } ?>
<div>
    <?php if($errors['err'] && $_POST['a']=='process') { ?>
        <p align="center" id="errormessage"><?=$errors['err']?></p>
    <?php }elseif($msg && $_POST['a']=='process' || $_POST['a']=='update' ) { ?>
        <p align="center" id="infomessage"><?=$msg?></p>
    <?php }elseif($warn) { ?>
        <p id="warnmessage"><?=$warn?></p>
    <?php } ?>
</div><br />
<?php
//Ticket adminstrative options. Staff with manage perm allowed, managers and admins are always allowed.
if($thisuser->canManageTickets()){ ?> 
  <form name='action' action='tickets.php?id=<?=$id?>' method='post'>
  <table cellpadding="0" cellspacing="2" border="0" width="100%" class="ticketoptions">
      <tr><td>
              <input type='hidden' name='ticket_id' value="<?=$id?>"/>
              <input type='hidden' name='a' value="process"/>
              <span for="do"> &nbsp;<b><?= _('Action:') ?></b></span>
              <select id="do" name="do" 
                onChange="this.form.ticket_priority.disabled=strcmp(this.options[this.selectedIndex].value,'change_priority','reopen','overdue')?false:true;">
                  <option value=""><?= _('Select Action') ?></option>
                  <?php if($thisuser->canEditTickets()) { ?>
                    <option value="edit" <?=$info['do']=='edit'?'selected':''?> ><?= _('Edit Ticket') ?></option>
                  <?php } ?>  
                  <?php if($thisuser->canChangepriorityTickets()) { ?>
                    <option value="change_priority" <?=$info['do']=='change_priority'?'selected':''?> ><?= _('Change Priority') ?></option>
                  <?php } ?>  
                  <?php if(!$ticket->isoverdue()){ ?>
                    <option value="overdue" <?=$info['do']=='overdue'?'selected':''?> ><?= _('Mark Overdue') ?></option>
                  <?php } ?>
                  <?php if($ticket->isAssigned() && $thisuser->canAssignTickets()){ ?>
                    <option value="release" <?=$info['do']=='release'?'selected':''?> ><?= _('Release (unassign)') ?></option>
                  <?php }
                  // staff members can always close a ticket and reopening it if closed.
                  if($ticket->isOpen()){?>
                    <option value="close" <?=$info['do']=='close'?'selected':''?> ><?= _('Close Ticket') ?></option>
                  <?php }else{ ?>
                    <option value="reopen" <?=$info['do']=='reopen'?'selected':''?> ><?= _('Reopen Ticket') ?></option>
                  <?php } ?>
                  <?php
                  if($thisuser->canManageBanList()) {
                     if(!$emailBanned) {?>    
                         <option value="banemail" ><?= _('Ban Email') ?> <?=$ticket->isOpen()?'&amp; '._('Close'):''?></option>
                     <?php }else{ ?>
                         <option value="unbanemail"><?= _('Un-Ban Email') ?></option>
                     <?php }
                  }?>
                  
                  <?php if($thisuser->canDeleteTickets()){ //oooh...fear the deleters! ?>
                          <option value="delete" ><?= _('Delete Ticket') ?></option>
                  <?php } ?>
              </select>
              <span for="ticket_priority"><?= _('Priority:') ?></span>
              <select id="ticket_priority" name="ticket_priority" <?=!$info['do']?'disabled':''?> >
                  <option value="0" selected="selected"><?= _('-Unchanged-') ?></option>
                  <?php
                  $priorityId=$ticket->getPriorityId();
                  $resp=db_query('SELECT priority_id,priority_desc FROM '.PRIORITY_TABLE);
                  while($row=db_fetch_array($resp)){ ?>
                      <option value="<?=$row['priority_id']?>" <?=$priorityId==$row['priority_id']?'disabled':''?> ><?=$row['priority_desc']?></option>
                  <?php } ?>
              </select>
                  &nbsp; &nbsp;
                  <input class="button" type="submit" value="<?= _('GO') ?>">
      </td></tr>
  </table>
  </form><br />
<?php }

//Internal notes and events
$resp=$ticket->getNotes();
if($notes=db_num_rows($resp)){
  $display=($notes>5)?'none':'block'; //Collapse internal notes if more than 5.
  ?>
  <a class="Icon note" href="#" onClick="toggleLayer('ticketnotes'); return false;"><?= _('Internal Notes') ?> (<?=$notes?>)</a><br />
  <div id='ticketnotes' style="display:<?=$display?>;"> 
      <?php
      while($row=db_fetch_array($resp)) {?>
          <div class="notelabel">&nbsp; <?=Format::db_daydatetime($row['created'])?>&nbsp;-&nbsp; <?= _('posted by') ?> <?=$row['source']?></div>
          <?php if($row['title']) {?>
          <div class="note_title"><?=Format::display($row['title'])?></div>
          <?php } ?>
          <div class="message"><?=Format::display($row['note'])?></div>
   <?php } ?>
  </div> <!-- ticketnotes -->
  <br />
<?php }

// Messages and responses
?>
<a class="Icon thread" href="#" onClick="toggleLayer('ticketthread'); return false;"><?= _('Ticket Thread') ?></a>
<div id="ticketthread">
  <?php
  // shows the other messages
  while ($msg_row = db_fetch_array($msgres)):
    if ($msg_row['msg_type'] == 'M') {?>
      <div class="messagelabel">
          <span class="Icon outMessage">&nbsp;<?=Format::db_daydatetime($msg_row['created'])?></span>
      </div>
      <div class="message">
          <?=Format::display($msg_row['message'])?>
      </div>
      <div class="attachment">
          <?php if($msg_row['attachments']>0){ ?>
              <?=$ticket->getAttachmentStr($msg_row['msg_id'],'M')?>
          <?php } ?>
      </div>
     <?php }
     elseif ($msg_row['msg_type'] == 'R') {
        //get answers for messages
            $respID=$resp_row['response_id'];
            $name=$cfg->hideStaffName()?'staff':Format::htmlchars($msg_row['staff_name']);
            ?>
        <div class="responselabel">
            <span class="Icon inMessage">&nbsp;<?=Format::db_daydatetime($msg_row['created'])?>&nbsp;-&nbsp;<?=$name?></span>
            </div>
            <div class="message">
                  <?=Format::display($msg_row['message'])?>
        </div>
            <div class="attachment">
                <?php if($msg_row['attachments']>0){ ?>
                  <?=$ticket->getAttachmentStr($msg_row['msg_id'],'R')?>
                <?php } ?>
        </div>
    <?php }
    $msgid =$msg_row['msg_id'];
  endwhile; //message loop.
  ?>
</div> <!-- ticketthread -->

<div class="ticketprocess">
  <?php if($_POST['a']!='process') {
         if($errors['err']) { ?>
        <p align="center" id="errormessage"><?=$errors['err']?></p>
    <?php }elseif($msg) { ?>
        <p align="center" id="infomessage"><?=$msg?></p>
    <?php }
        } ?>
    <div class="tabber">
        <div id="reply" class="tabbertab" align="left">
            <h2><?= _('Post Reply') ?></h2>
                <form action="tickets.php?id=<?=$id?>#reply" name="reply" id="replyform" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="ticket_id" value="<?=$id?>">
                    <input type="hidden" name="a" value="reply">
                    <div class="input">
                       <?php
                         $sql='SELECT stdreply_id,title FROM '.STD_REPLY_TABLE.' WHERE isenabled=1 '.
                            ' AND (dept_id=0 OR dept_id='.db_input($ticket->getDeptId()).')';
                        $canned=db_query($sql);
                        if($canned && db_num_rows($canned)) {
                         ?>
                        <?= _('Canned Response:') ?>&nbsp;
                           <select id="canned" name="canned"
                             onChange="getCannedResponse(this.options[this.selectedIndex].value,this.form,'response');this.selectedIndex='0';" >
                             <option value="0" selected="selected"><?= _('Select a standard reply') ?></option>
                             <?php while(list($cannedId,$title)=db_fetch_row($canned)) { ?>
                             <option value="<?=$cannedId?>" ><?=Format::htmlchars($title)?></option>
                            <?php } ?>
                           </select>&nbsp;&nbsp;&nbsp;<label><input type='checkbox' value='1' name=append checked="true" /><?= _('Append') ?></label>
                        <?php } ?>
                    </div>
                    <div><font class="error">&nbsp;<?=$errors['response']?></font></div>
                    <div>
                        <textarea name="response" id="response" cols="90" rows="9" wrap="soft" style="width:100%"><?=$info['response']?></textarea>
                    </div>
                    <?php if($cfg->canUploadFiles()){ //TODO: may be allow anyways and simply email out attachment?? ?>
                    <div style="margin-top: 3px;">
                        <label for="attachment" ><?= _('Attach File:') ?></label>
                        <input type="file" name="attachment[]" size=30px value="<?=$info['attachment']?>" multiple /> 
                        &nbsp;<span class="warning">(max <?=$cfg->getMaxFileSize()?> bytes)</span>
                        <font class="error">&nbsp;<?=$errors['attachment']?></font>
                    </div>
                    <?php }?>
                    <?php
                    if($cfg->notifyONNewResponse()) { // A notice will be sent to the user/client? If no, hide the signature.
                     $appendStaffSig=$thisuser->appendMySignature();
                     $appendDeptSig=$dept->canAppendSignature();
                     $info['signature']=!$info['signature']?'none':$info['signature']; //change 'none' to 'mine' to default to staff signature.
                     if($appendStaffSig || $appendDeptSig) { ?>
                      <div style="margin-top: 10px;">
                          <label for="signature" nowrap><?= _('Append signature to e-mail:') ?></label>
                          <label><input type="radio" name="signature" value="none" checked > <?= _('None') ?></label>
                            <?php if($appendStaffSig) { ?>
                          <label> <input type="radio" name="signature" value="mine" <?=$info['signature']=='mine'?'checked':''?> > <?= _('My signature') ?></label>
                            <?php } ?>
                            <?php if($appendDeptSig) { ?>
                          <label><input type="radio" name="signature" value="dept" <?=$info['signature']=='dept'?'checked':''?> > <?= _('Dept Signature') ?></label>
                            <?php } ?>
                       </div>
                      <?php }
                    } ?>
                    <div style="margin-top: 3px;">
                        <b><?= _('Ticket Status:') ?></b>
                        <?php
                        $checked=isset($info['ticket_status'])?'checked':''; //Staff must explicitly check the box to change status..
                        if($ticket->isOpen()){?>
                        <label><input type="checkbox" name="ticket_status" id="l_ticket_status" value="Close" <?=$checked?> > <?= _('Close on Reply') ?></label>
                        <?php }else{ ?>
                        <label><input type="checkbox" name="ticket_status" id="l_ticket_status" value="Reopen" <?=$checked?> > <?= _('Reopen on Reply') ?></label>
                        <?php } ?>
                    </div>
                    <div  style="margin-left:50px; margin-top:20px; margin-bottom:10px; border:0px;">
                        <input class="button" type='submit' value='<?= _('Post Reply') ?>' />
                        <input class="button" type='reset' value='<?= _('Reset') ?>' />
                        <input class="button" type='button' value='<?= _('Cancel') ?>' onClick="history.go(-1)" />
                    </div>
                </form>                
         </div>
            <div id="notes" class="tabbertab"  align="left">
            <h2><?= _('Post Internal Note') ?></h2>
            <form action="tickets.php?id=<?=$id?>#notes" name="notes" class="inline" method="post" enctype="multipart/form-data">
                <input type="hidden" name="ticket_id" value="<?=$id?>">
                <input type="hidden" name="a" value="postnote">
                <div class="input">
                    <label for="title"><?= _('Note Title:') ?></label>
                    <input type="text" name="title" id="title" value="<?=$info['title']?>" size=30px />
                    </select><font class="error">*&nbsp;<?=$errors['title']?></font>
                </div>
                <div style="margin-top: 3px;">
                    <label for="note"><?= _('Enter note content.') ?>
                        <font class="error">*&nbsp;<?=$errors['note']?></font></label><br/>
                    <textarea name="note" id="note" cols="80" rows="7" wrap="soft" style="width:100%"><?=$info['note']?></textarea>
                </div>
                <?php
                 //When the ticket is assigned Allow assignee, admin or ANY dept manager to close it
                if(!$ticket->isAssigned() || $thisuser->isadmin()  || $thisuser->isManager() || $thisuser->getId()==$ticket->getStaffId()) {
                 ?>
                  <div style="margin-top: 3px;">
                      <b><?= _('Ticket Status:') ?></b>
                      <?php
                      $checked=($info && isset($info['ticket_status']))?'checked':''; //not selected by default.
                      if($ticket->isOpen()){?>
                        <label><input type="checkbox" name="ticket_status" id="ticket_status" value="Close" <?=$checked?> > <?= _('Close Ticket') ?></label>
                      <?php }else{ ?>
                        <label><input type="checkbox" name="ticket_status" id="ticket_status" value="Reopen" <?=$checked?> > <?= _('Reopen Ticket') ?></label>
                      <?php } ?>
                  </div>
                <?php } ?>
                <div  align="left" style="margin-left:50px; margin-top:20px; margin-bottom:10px; border: 0px;">
                    <input class="button" type='submit' value='<?= _('Submit') ?>' />
                    <input class="button" type='reset' value='<?= _('Reset') ?>' />
                    <input class="button" type='button' value='<?= _('Cancel') ?>' onClick="history.go(-1)" />
                </div>
            </form>
        </div>
        <?php
        if($thisuser->canTransferTickets()) { 
        ?>
        <div id="transfer" class="tabbertab"  align="left">
            <h2><?= _('Dept. Transfer') ?></h2>
            <form action="tickets.php?id=<?=$id?>#transfer" name="notes" method="post" enctype="multipart/form-data">
                <input type="hidden" name="ticket_id" value="<?=$id?>">
                <input type="hidden" name="a" value="transfer">
                <div class="input">
                    <span for="dept_id"><?= _('Department:') ?></span>
                    <select id="dept_id" name="dept_id">
                        <option value="" selected="selected"><?= _('-Select Target Dept.-') ?></option>
                        <?php
                        $depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE.' WHERE dept_id!='.db_input($ticket->getDeptId()));
                        while (list($deptId,$deptName) = db_fetch_row($depts)){
                            $selected = ($info['dept_id']==$deptId)?'selected':''; ?>
                            <option value="<?=$deptId?>"<?=$selected?>><?=$deptName?> Department </option>
                        <?php
                        }?>
                    </select><font class='error'>&nbsp;*<?=$errors['dept_id']?></font>
                </div>
                <div>
                    <span ><?= _('Comments/Reasons for the transfer')?> <i><?= _('(Internal note).') ?></i>
                        <font class='error'>&nbsp;*<?=$errors['message']?></font></span>
                    <textarea name="message" id="message" cols="80" rows="7" wrap="soft" style="width:100%;"><?=$info['message']?></textarea>
                </div>
                <div  style="margin-left:50px; margin-top:8px; margin-bottom:10px; border:0px;" align="left">
                    <input class="button" type='submit' value='<?= _('Transfer') ?>' />
                    <input class="button" type='reset' value='<?= _('Reset') ?>' />
                    <input class="button" type='button' value='<?= _('Cancel') ?>' onClick="history.go(-1)" />
                </div>
            </form>
        </div>
        <?php }

         //When the ticket is assigned Allow staff with permission, admin or ANY dept manager to reassign the ticket.
        if(!$ticket->isAssigned() || $thisuser->isadmin()  || $thisuser->isManager() || $thisuser->canAssignTickets()) {
        ?>
        <div id="assign" class="tabbertab"  align="left">
            <h2><?=$staff?_('Re Assign Ticket'):_('Assign to Staff') ?></h2>
            <form action="tickets.php?id=<?=$id?>#assign" name="notes" method="post" enctype="multipart/form-data">
                <input type="hidden" name="ticket_id" value="<?=$id?>">
                <input type="hidden" name="a" value="assign">
                <div class="input">
                    <span for="staffId"><?= _('Staff Member:') ?></span>
                    <select id="staffId" name="staffId">
                        <option value="0" selected="selected"><?= _('-Select Staff Member.-') ?></option>
                        <?php
                        //TODO: make sure the user's role is also active....DO a join.
                        $sql=' SELECT dept_name,staff_id,CONCAT_WS(", ",lastname,firstname) as name FROM '.STAFF_TABLE.' LEFT JOIN '.DEPT_TABLE.' USING (dept_id) '.
                             ' WHERE isactive=1 AND onvacation=0 ';
                        if($ticket->isAssigned()) 
                            $sql.=' AND staff_id!='.db_input($ticket->getStaffId());
                        $depts= db_query($sql.' ORDER BY lastname,firstname ');
                        while (list($deptName,$staffId,$staffName) = db_fetch_row($depts)){
                            
                            $selected = ($info['staffId']==$staffId)?'selected':''; ?>
                            <option value="<?=$staffId?>"<?=$selected?>><?=$staffName?> &nbsp; (<?=$deptName?>)</option>
                        <?php
                        }?>
                    </select><font class='error'>&nbsp;*<?=$errors['staffId']?></font>
                </div>
                <div>
                    <span><?= _('Comments/message for assignee')?> <i><?= _('(Internal note).') ?></i>
                        <font class='error'>&nbsp;*<?=$errors['assign_message']?></font></span>
                    <textarea name="assign_message" id="assign_message" cols="80" rows="7" 
                        wrap="soft" style="width:100%;"><?=$info['assign_message']?></textarea>
                </div>
                <div  style="margin-left:50px; margin-top:8px; margin-bottom:10px; border:0px;" align="left">
                    <input class="button" type='submit' value='<?= _('Assign') ?>' />
                    <input class="button" type='reset' value='<?= _('Reset') ?>' />
                    <input class="button" type='button' value='<?= _('Cancel') ?>' onClick="history.go(-1)" />
                </div>
            </form>
        </div>
        <?php } ?>
    </div>
</div> <!-- ticketprocess -->
