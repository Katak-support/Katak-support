<?php
if(!defined('KTKADMININC') || !is_object($thisuser) || !$thisuser->isStaff()) die(_('Access Denied'));
$info=($_POST && $errors)?Format::input($_POST):array(); //on error...use the post data
?>
<div>
    <?php if($errors['err']) { ?>
        <p align="center" id="errormessage"><?=$errors['err']?></p>
    <?php }elseif($msg) { ?>
        <p align="center" class="infomessage"><?=$msg?></p>
    <?php }elseif($warn) { ?>
        <p class="warnmessage"><?=$warn?></p>
    <?php } ?>
</div>
<form action="tickets.php" method="post" enctype="multipart/form-data">
  <input type='hidden' name='a' value='open'>
  <table width="80%" border="0" cellspacing=1 cellpadding=2>
    <tr><td align="left" colspan=2><?= _('Please fill in the form below to open a new ticket.') ?></td></tr>
    <tr>
        <td align="left" nowrap width="20%"><b><?= _('Email Address:') ?></b></td>
        <td>
            <input type="text" id="email" name="email" size="30" value="<?=$info['email']?>">
            &nbsp;<font class="error"><b>*</b>&nbsp;<?=$errors['email']?></font>
            <?php if($cfg->notifyONNewStaffTicket()) {?>
               &nbsp;&nbsp;&nbsp;
               <input type="checkbox" name="alertuser" <?=(!$errors || $info['alertuser'])? 'checked': ''?>><?= _('Send alert to user.') ?>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <td align="left" ><b><?= _('Full Name:') ?></b></td>
        <td>
            <input type="text" id="name" name="name" size="30" value="<?=$info['name']?>">
            &nbsp;<font class="error"><b>*</b>&nbsp;<?=$errors['name']?></font>
        </td>
    </tr>
    <tr>
        <td align="left"><?= _('Telephone:') ?></td>
        <td><input type="text" name="phone" size="30" value="<?=$info['phone']?>">
            <font class="error">&nbsp;<?=$errors['phone']?></font></td>
    </tr>
    <tr height=2px><td align="left" colspan=2 >&nbsp;</td></tr>
    <tr>
        <td align="left"><b><?= _('Ticket Source:') ?></b></td>
        <td>
            <select name="source">
                <option value="" selected ><?= _('Select Source') ?></option>
                <option value="Phone" <?=($info['source']=='Phone')?'selected':''?>><?= _('Phone') ?></option>
                <option value="Email" <?=($info['source']=='Email')?'selected':''?>><?= _('Email') ?></option>
                <option value="Other" <?=($info['source']=='Other')?'selected':''?>><?= _('Other') ?></option>
            </select>
            &nbsp;<font class="error"><b>*</b>&nbsp;<?=$errors['source']?></font>
        </td>
    </tr>
    <tr>
        <td align="left"><b><?= _('Department:') ?></b></td>
        <td>
            <select name="deptId">
                <option value="" selected ><?= _('Select Department') ?></option>
                <?php
                 $services= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name');
                 while (list($deptId,$dept) = db_fetch_row($services)){
                    $selected = ($info['deptId']==$deptId)?'selected':''; ?>
                    <option value="<?=$deptId?>"<?=$selected?>><?=$dept?></option>
                <?php
                 }?>
            </select>
            &nbsp;<font class="error"><b>*</b>&nbsp;<?=$errors['deptId']?></font>
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
        <td><br /><b><?= _('Issue Summary:') ?></b></td>
        <td>
            <i><?= _('Visible to user/client as answer to the ticket.') ?></i><font class="error"><b>*&nbsp;<?=$errors['issue']?></b></font><br/>
            <?php
            $sql='SELECT stdreply_id,title FROM '.STD_REPLY_TABLE.' WHERE isenabled=1';
            $canned=db_query($sql);
            if($canned && db_num_rows($canned)) {
            ?>
            <?= _('Std. reply:') ?>&nbsp;
              <select id="canned" name="canned"
                onChange="getCannedResponse(this.options[this.selectedIndex].value,this.form,'issue');this.selectedIndex='0';" >
                  <option value="0" selected="selected"><?= _('Select a standard reply/issue') ?></option>
                <?php while(list($cannedId,$title)=db_fetch_row($canned)) { ?>
                <option value="<?=$cannedId?>" ><?=Format::htmlchars($title)?></option>
                <?php } ?>
              </select>&nbsp;&nbsp;&nbsp;<label><input type='checkbox' value='1' name='append' checked /><?= _('Append') ?></label>
            <?php } ?>
            <textarea name="issue" cols="55" rows="8" wrap="soft"><?=$info['issue']?></textarea></td>
    </tr>
    <?php if($cfg->canUploadFiles()) { ?>
    <tr>
        <td><?= _('Attachment:') ?></td>
        <td>
            <input type="file" name="attachment[]" multiple />&nbsp;<span class="warning">(max <?=$cfg->getMaxFileSize()?> bytes)</span><font class="error">&nbsp;<?=$errors['attachment']?></font>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <td><br /><?= _('Internal Note:') ?></td>
        <td>
            <i><?= _('Optional Internal note(s).') ?></i><font class="error"><b>&nbsp;<?=$errors['note']?></b></font><br/>
            <textarea name="note" cols="55" rows="5" wrap="soft"><?=$info['note']?></textarea></td>
    </tr>

    <tr>
        <td><br /><?= _('Due Date:') ?></td>
        <td>
            <i><?= sprintf(_('Time is based on your time zone (GMT + %s)'), $thisuser->getTZoffset()) ?></i>&nbsp;<font class="error">&nbsp;<?=$errors['time']?></font><br>
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
                $info['pri']=$info['pri']?$info['pri']:$cfg->getDefaultPriorityId();
                while($row=db_fetch_array($priorities)){ ?>
                    <option value="<?=$row['priority_id']?>" <?=$info['pri']==$row['priority_id']?'selected':''?> ><?=$row['priority_desc']?></option>
              <?php } ?>
            </select>
        </td>
       </tr>
    <?php }?>
    <?php
    $services= db_query('SELECT topic_id,topic FROM '.TOPIC_TABLE.' WHERE isactive=1 ORDER BY topic');
    if($services && db_num_rows($services)){ ?>
    <tr>
        <td><?= _('Help Topic:') ?></td>
        <td>
            <select name="topicId">
                <option value="" selected ><?= _('Select One') ?></option>
                <?php
                 while (list($topicId,$topic) = db_fetch_row($services)){
                    $selected = ($info['topicId']==$topicId)?'selected':''; ?>
                    <option value="<?=$topicId?>"<?=$selected?>><?=$topic?></option>
                <?php
                 }?>
            </select>
            &nbsp;<font class="error">&nbsp;<?=$errors['topicId']?></font>
        </td>
    </tr>
    <?php
    }?>
    <tr>
        <td><?= _('Assign To:') ?></td>
        <td>
            <select id="staffId" name="staffId">
                <option value="0" selected="selected"><?= _('-Assign To Staff-') ?></option>
                <?php
                    //TODO: make sure the user's role is also active....DO a join.
                    $sql=' SELECT staff_id,CONCAT_WS(", ",lastname,firstname) as name FROM '.STAFF_TABLE.' WHERE isactive=1 AND onvacation=0 ';
                    $depts= db_query($sql.' ORDER BY lastname,firstname ');
                    while (list($staffId,$staffName) = db_fetch_row($depts)){
                        $selected = ($info['staffId']==$staffId)?'selected':''; ?>
                        <option value="<?=$staffId?>"<?=$selected?>><?=$staffName?></option>
                    <?php
                    }?>
            </select><font class='error'>&nbsp;<?=$errors['staffId']?></font>
                &nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="alertstaff" <?=(!$errors || $info['alertstaff'])? 'checked': ''?>><?= _('Send alert to assigned staff.') ?>
        </td>
    </tr>
    <tr>
        <td><?= _('Signature:') ?></td>
        <td> <?php
            $appendStaffSig=$thisuser->appendMySignature();
            $info['signature']=!$info['signature']?_('none'):$info['signature']; //change 'none' to 'mine' to default to staff signature.
            ?>
            <div style="margin-top: 2px;">
                <label><input type="radio" name="signature" value="none" checked > <?= _('None') ?></label>
                <?php if($appendStaffSig) { ?>
                <label> <input type="radio" name="signature" value="mine" <?=$info['signature']=='mine'?'checked':''?> > <?= _('My signature') ?></label>
                 <?php } ?>
                <label><input type="radio" name="signature" value="dept" <?=$info['signature']=='dept'?'checked':''?> > <?= _('Dept Signature (if any)') ?></label>
            </div>
        </td>
    </tr>
    <tr height=2px><td align="left" colspan=2 >&nbsp;</td></tr>
    <tr>
        <td></td>
        <td>
            <input class="button" type="submit" name="submit_x" value="<?= _('Submit Ticket') ?>">
            <input class="button" type="reset" value="<?= _('Reset') ?>">
            <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="tickets.php"'>
        </td>
    </tr>
  </table>
</form>
<script type="text/javascript">
    
    var options = {
        script:"ajax.php?api=tickets&f=searchbyemail&limit=10&",
        varname:"input",
        json: true,
        shownoresults:false,
        maxresults:10,
        callback: function (obj) { document.getElementById('email').value = obj.id; document.getElementById('name').value = obj.info; return false;}
    };
    var autosug = new bsn.AutoSuggest('email', options);
</script>
