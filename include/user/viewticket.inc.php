<?php
if(!defined('KTKUSERINC') || !is_object($thisuser) || !is_object($ticket)) die('Adiaux amikoj!'); //bye..see ya

// Double check access one last time...
// The user mail is identical of the ticket mail, OR
// the user is a logged-in client AND the ticket mail belong to the client group
if(strcasecmp($thisuser->getEmail(),$ticket->getEmail())) {
  if(!($cfg->getUserLogRequired() AND stristr($thisuser->getGroupMemebers(),$ticket->getEmail()) AND ($thisuser->group_id != 0))) {
    die(_('Access Denied'));
  }
}

$info=($_POST && $errors)?Format::input($_POST):array(); //Re-use the post info on error...savekeyboards.org

$dept = $ticket->getDept();
//Making sure we don't leak out internal dept names
$dept=($dept && $dept->isPublic())?$dept:$cfg->getDefaultDept();
//We roll like that...
?>
<div class="msg">
		<?php
		if($ticket->getStatus()=='closed')
          echo '<span class="Icon closedTicket">&nbsp;</span>';
		else
          echo '<span class="Icon openTicket">&nbsp;</span>';
        ?>
        Ticket #<?=$ticket->getExtId()?>
        &nbsp;<a href="tickets.php?id=<?=$ticket->getExtId()?>" title="<?=_('Reload') ?>"><span class="Icon refresh">&nbsp;</span></a>
</div>
<div class="msg" id="ticketstatus"><?=_('Ticket Status:').' '.$ticket->getStatus()?></div>
<br style="clear:both;" />
<div id="ticket">
  <div id="leftcolumn">
    <div>
      <span class="label"><?=_('Created on:') ?></span>
      <span><?=Format::db_datetime($ticket->getCreateDate())?></span>
    </div>
    <div>
      <span class="label"><?=_('Department:') ?></span>
      <span><?=Format::htmlchars($dept->getName())?></span>
    </div>
    <?php if (!$ticket->isOpen()) { ?>
    <div>
      <span class="label"><?=_('Closing date:') ?></span>
      <span><?=Format::db_datetime($ticket->getCloseDate())?></span>
    </div>
    <?php } ?>
  </div>
  <div id="rightcolumn">
    <div>
      <span class="label"><?=_('Name:') ?></span>
      <span><?=Format::htmlchars($ticket->getName())?></span>
    </div>
    <div>
      <span class="label"><?=_('Email:') ?></span>
      <span><?=$ticket->getEmail()?></span>
    </div>
    <div>
      <span class="label"><?=_('Phone:') ?></span>
      <span><?=Format::phone($ticket->getPhoneNumber())?></span>
    </div>
  </div>
  <br style="clear:both;" />
  <?php
  //get ticket messages and responses and shows the first message
  $msgres=$ticket->getMessages();
  $msg_row = db_fetch_array($msgres);
  ?>
  <div>
    <span class="label"><?=_('Subject:') ?></span>
    <span><?=Format::htmlchars($ticket->getSubject())?></span>
  </div>
  <span class="label"><?=_('Ticket text:') ?></span>
  <div class="firstmessage">
    <?=Format::display($msg_row['message'])?>
  </div>
  <?php if($msg_row['attachments']>0){ ?>
    <div class="firstmessage">
    <?=$ticket->getAttachmentStr($msg_row['msg_id'],'M')?>
    </div>
  <?php } ?>
</div>
<hr>

<span class="Icon thread"><?=_('Ticket Thread') ?></span>
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
</div>

<div>
    <div align="center">
        <?php if($_POST && $errors['err']) { ?>
            <p align="center" id="errormessage"><?=$errors['err']?></p>
        <?php }elseif($msg) { ?>
            <p align="center" id="infomessage"><?=$msg?></p>
        <?php } ?>
    </div>
    <?php 
    // Give the possibility to post a message and eventually reopen the ticket, if not blocked (reopen grace period overdue) and if the ticket belong to the user
    if(($ticket->isOpen() || (time()-strtotime($ticket->getCloseDate()))<=$cfg->getReopenGracePeriod()*24*3600) AND !strcasecmp($thisuser->getEmail(),$ticket->getEmail())) { ?>
    <div id="reply" style="padding:10px 0 20px 40px;">
        <?php if($ticket->isClosed()) { ?>
        <div class="msg"><?=_('Ticket will be reopened on message post') ?></div>
        <?php } ?>
        <form action="tickets.php?id=<?=$id?>#reply" name="reply" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?=$ticket->getExtId()?>">
            <input type="hidden" name="respid" value="<?=$respID?>">
            <input type="hidden" name="a" value="postmessage">
            <div align="left">
                <?=_('Enter new message') ?> <span class="error">*&nbsp;<?=$errors['message']?></span><br/>
                <textarea name="message" id="message" cols="60" rows="7" wrap="soft"><?=$info['message']?></textarea>
            </div>
            <?php if($cfg->allowOnlineAttachments()) {?>
              <div align="left">
                  <?=_('Attach File') ?>&nbsp;<span class="warning">(max <?=$cfg->getMaxFileSize()?> bytes)</span>
                      <br><input type="file" id="multiattach" name="attachment[]" size=30px value="<?=$info['attachment']?>" />
                      <span class="error">&nbsp;<?=$errors['attachment']?></span>
              </div>
              <div id="files_list"></div>
              <?php /*  Note: the script must be here, in order to be executed after the DOM is loaded */?>
              <script>
           	    // <!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
           	    var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 10 );
          	    //<!-- Pass in the file element -->
          	    multi_selector.addElement( document.getElementById( 'multiattach' ) );
              </script>
            <?php } ?>
            <div style="padding:14px 0px 0px 0px;">
                <input class="button" type='submit' value='<?=_('Post Reply') ?>' />
                <input class="button" type='button' value='<?=_('Cancel') ?>' onClick='window.location.href="tickets.php"' />
            </div>
        </form>
    </div>
    <?php } ?>
</div>
