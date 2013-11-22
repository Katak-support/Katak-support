<?php
if(!defined('KTKADMININC') || !is_object($ticket) || !is_object($thisuser) || !$thisuser->isStaff()) die(_('Access Denied'));

if(!($thisuser->canEditTickets() || ($thisuser->isManager() && $ticket->getDeptId()==$thisuser->getDeptId()))) die(_('Access Denied. Perm error.'));

if($_POST && $errors){
    $info=Format::input($_POST);
}else{
    $info=array('email'=>$ticket->getEmail(),
                'name' =>$ticket->getName(),
                'phone'=>$ticket->getPhone(),
                'pri'=>$ticket->getPriorityId(),
                'topicId'=>$ticket->getTopicId(),
                'topic'=>$ticket->getTopic(),
                'subject' =>$ticket->getSubject(),
                'duedate' =>$ticket->getDueDate()?(Format::userdate('m/d/Y',Misc::db2gmtime($ticket->getDueDate()))):'',
                'time'=>$ticket->getDueDate()?(Format::userdate('G:i',Misc::db2gmtime($ticket->getDueDate()))):'',
                );
}

?>
<div width="100%">
    <?php if($errors['err']) { ?>
        <p align="center" id="errormessage"><?=$errors['err']?></p>
    <?php }elseif($msg) { ?>
        <p align="center" class="infomessage"><?=$msg?></p>
    <?php }elseif($warn) { ?>
        <p class="warnmessage"><?=$warn?></p>
    <?php } ?>
</div>
<form action="tickets.php?id=<?=$ticket->getId()?>" method="post">
  <table width="100%" border="0" cellspacing=1 cellpadding=2>
    <input type='hidden' name='id' value='<?=$ticket->getId()?>'>
    <input type='hidden' name='a' value='update'>
    <tr><td align="left" colspan=2 class="msg">
            <?= _('Update Ticket #') ?><?=$ticket->getExtId()?>&nbsp;&nbsp;[<a href="tickets.php?id=<?=$ticket->getId()?>"><?= _('Back to Ticket') ?></a>]
        </td>
    </tr>
    <tr>
        <td align="left" nowrap width="120"><b><?= _('Email Address:') ?></b></td>
        <td>
            <input type="text" id="email" name="email" size="25" value="<?=$info['email']?>">
            &nbsp;<font class="error"><b>*</b>&nbsp;<?=$errors['email']?></font>
        </td>
    </tr>
    <tr>
        <td align="left" ><b><?= _('Full Name:') ?></b></td>
        <td>
            <input type="text" id="name" name="name" size="25" value="<?=$info['name']?>">
            &nbsp;<font class="error"><b>*</b>&nbsp;<?=$errors['name']?></font>
        </td>
    </tr>
    <tr>
        <td align="left"><b><?= _('Subject:') ?></b></td>
        <td>
            <input type="text" name="subject" size="35" value="<?=$info['subject']?>">
            &nbsp;<font class="error">*&nbsp;<?=$errors['subject']?></font>
        </td>
    </tr>
    <tr>
        <td align="left"><?= _('Telephone:') ?></td>
        <td><input type="text" name="phone" size="25" value="<?=$info['phone']?>">
            &nbsp;<font class="error">&nbsp;<?=$errors['phone']?></font></td>
    </tr>
    <tr height=1px><td align="left" colspan=2 >&nbsp;</td></tr>
    <tr>
        <td><br /><?= _('Due Date:') ?></td>
        <td>
            <i><?= sprintf(_('Time is based on your time zone GM %s'), $thisuser->getTZoffset()) ?></i>&nbsp;<font class="error">&nbsp;<?=$errors['time']?></font><br>
            <input id="duedate" name="duedate" value="<?=Format::htmlchars($info['duedate'])?>"
                onclick="event.cancelBubble=true;calendar(this);" autocomplete=OFF>
            <a href="#" onclick="event.cancelBubble=true;calendar(getObj('duedate')); return false;"><img src='images/cal.png'border=0 alt=""></a>
            &nbsp;&nbsp;
            <?php
             $min=$hr=null;
             if($info['time'])
                list($hr,$min)=explode(':',$info['time']);
                echo Misc::timeDropdown($hr,$min,'time');
            ?>
            &nbsp;<font class="error">&nbsp;<?=$errors['duedate']?></font>
        </td>
    </tr>
    <?php
      $sql='SELECT priority_id,priority_desc FROM '.PRIORITY_TABLE.' ORDER BY priority_urgency DESC';
      if(($priorities=db_query($sql)) && db_num_rows($priorities)){ ?>
      <tr>
          <td align="left"><?= _('Priority:') ?></td>
        <td>
            <select name="pri">
              <?php
                while($row=db_fetch_array($priorities)){ ?>
                    <option value="<?=$row['priority_id']?>" <?=$info['pri']==$row['priority_id']?'selected':''?> ><?=$row['priority_desc']?></option>
              <?php } ?>
            </select>
        </td>
       </tr>
    <?php }?>

    <?php
    $services= db_query('SELECT topic_id,topic,isactive FROM '.TOPIC_TABLE.' ORDER BY topic');
    if($services && db_num_rows($services)){ ?>
    <tr>
        <td><?= _('Help Topic:') ?></td>
        <td>
            <select name="topicId">    
                <option value="0" selected ><?= _('None') ?></option>
                <?php if(!$info['topicId'] && $info['topic']){ ?>
                  <option value="0" selected ><?=$info['topic']?> <?= _('(deleted)') ?></option>
                <?php
                }
                 while (list($topicId,$topic,$active) = db_fetch_row($services)){
                    $selected = ($info['topicId']==$topicId)?'selected':'';
                    $status=$active?'Active':'Inactive';
                    ?>
                    <option value="<?=$topicId?>"<?=$selected?>><?=$topic?>&nbsp;&nbsp;&nbsp;(<?=$status?>)</option>
                <?php
                 }?>
            </select>
            &nbsp;<?= _('(optional)') ?><font class="error">&nbsp;<?=$errors['topicId']?></font>
        </td>
    </tr>
    <?php
    }?>
    <tr>
        <th><br /><?= _('Internal Note:') ?></th>
        <td>
            <i><?= _('Reasons for the edit.') ?></i><font class="error"><b>*&nbsp;<?=$errors['note']?></b></font><br/>
            <textarea name="note" cols="45" rows="5" wrap="soft"><?=$info['note']?></textarea></td>
    </tr>
    <tr height=2px><td align="left" colspan=2 >&nbsp;</td></tr>
    <tr>
        <td></td>
        <td>
            <input class="button" type="submit" name="submit_x" value="<?= _('Update Ticket') ?>">
            <input class="button" type="reset" value="<?= _('Reset') ?>">
            <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="tickets.php?id=<?=$ticket->getId()?>"'>
        </td>
    </tr>
  </table>
</form>

