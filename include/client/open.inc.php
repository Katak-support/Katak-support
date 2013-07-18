<?php
if(!defined('KTKCLIENTINC')) die('Adiaux amikoj!'); //Say bye to our friends.

$info=($_POST && $errors)?Format::input($_POST):array(); //on error...use the post data
?>
<div>
    <?php if($errors['err']) { ?>
        <p align="center" id="errormessage"><?=$errors['err']?></p>
    <?php }elseif($msg) { ?>
        <p align="center" id="infomessage"><?=$msg?></p>
    <?php }elseif($warn) { ?>
        <p id="warnmessage"><?=$warn?></p>
    <?php }?>
</div>
<div><?= _('Please fill in the form below to open a new ticket and provide as much detail as possible, so we can best assist you.')?><br />
     <?= _('To update a previously submitted ticket, please go to ')?><a href="tickets.php"><?= _('Ticket Status')?></a>.
</div><br />
<form action="open.php" method="POST" enctype="multipart/form-data">

    <div class="input">
        <span class="label"><?= _('Full Name:')?></span>
        <span>
            <?php if ($thisclient && ($name=$thisclient->getName())) {
                ?>
                <input type="hidden" name="name" value="<?=$name?>"><?=$name?>
            <?php }else { ?>
                <input type="text" name="name" size="30" value="<?=$info['name']?>">
	        <?php } ?>
            &nbsp;<span class="error">*&nbsp;<?=$errors['name']?></span>
        </span>
    </div>
    <div class="input">
        <span class="label"><?= _('Email Address:')?></span>
        <span>
            <?php if ($thisclient && ($email=$thisclient->getEmail())) {
                ?>
                <input type="hidden" name="email" size="30" value="<?=$email?>"><?=$email?>
            <?php }else { ?>             
                <input type="text" name="email" size="30" value="<?=$info['email']?>">
            <?php } ?>
            &nbsp;<span class="error">*&nbsp;<?=$errors['email']?></span>
        </span>
    </div>
    <div class="input">
        <span class="label-optional"><?= _('Telephone:')?></span>
        <span><input type="text" name="phone" size="30" value="<?=$info['phone']?>">
            &nbsp;<span class="error">&nbsp;<?=$errors['phone']?></span>
        </span>
    </div>
    <div><span>&nbsp;</span></div>

    <?php
    // Present the topic selection menu if enabled
    if($cfg && $cfg->enableTopic()) {?>
    <div class="input">
        <span class="label"><?= _('Help Topic:')?></span>
        <span>
            <select name="topicId">
                <option value="" selected><?= _('Select One')?></option>
                <?php
                 $services= db_query('SELECT topic_id,topic FROM '.TOPIC_TABLE.' WHERE isactive=1 ORDER BY topic');
                 if($services && db_num_rows($services)) {
                     while (list($topicId,$topic) = db_fetch_row($services)){
                        $selected = ($info['topicId']==$topicId)?'selected':''; ?>
                        <option value="<?=$topicId?>"<?=$selected?>><?=$topic?></option>
                        <?php
                     }
                 }else{?>
                    <option value="0" ><?= _('General Inquiry')?></option>
                <?php } ?>
            </select>
            &nbsp;<span class="error">*&nbsp;<?=$errors['topicId']?></span>
        </span>
    </div>
    <?php }
    else {?>
    <input type="hidden" name="topicId" value="0">
    <?php } ?>    
    
    <div class="input">
        <span class="label"><?= _('Subject:')?></span>
        <span>
            <input type="text" name="subject" size="38" value="<?=$info['subject']?>">
            &nbsp;<span class="error">*&nbsp;<?=$errors['subject']?></span>
        </span>
    </div>
    <div class="input">
        <span class="label"><?= _('Message:')?>
            <?php if($errors['message']) {?> <br /><span class="error">*&nbsp;<?=$errors['message']?></span><?php } ?>
    		</span>
        <span>
            <textarea name="message" cols="40" rows="8" style="width:80%"><?=$info['message']?></textarea>
        </span>
    </div>
    <?php
    if($cfg->allowPriorityChange() ) {
      $sql='SELECT priority_id,priority_desc FROM '.PRIORITY_TABLE.' WHERE ispublic=1 ORDER BY priority_urgency DESC';
      if(($priorities=db_query($sql)) && db_num_rows($priorities)){ ?>
      <div>
        <span class="label-optional"><?= _('Priority:')?></span>
        <span>
            <select name="pri">
              <?php
                $info['pri']=$info['pri']?$info['pri']:$cfg->getDefaultPriorityId(); //use system's default priority.
                while($row=db_fetch_array($priorities)){ ?>
                    <option value="<?=$row['priority_id']?>" <?=$info['pri']==$row['priority_id']?'selected':''?> ><?=$row['priority_desc']?></option>
              <?php } ?>
            </select>
        </span>
       </div>
    <?php }
    }?>

    <?php if(($cfg->allowOnlineAttachments() && !$cfg->allowAttachmentsOnlogin())  
                || ($cfg->allowAttachmentsOnlogin() && ($thisclient && $thisclient->isValid()))){
                  ?>
    <div class="input">
        <span class="label-optional"><?= _('Attachment:')?></span>
        <span>
    	  <input type="file" id="multiattach" name="attachment[]" />&nbsp;<span class="warning">(max <?=$cfg->getMaxFileSize()?> bytes)</span><span class="error"> &nbsp;<?=$errors['attachment']?></span>
        </span>
          <div id="files_list" class="files_list"></div>
          <?php // sorry but the script must be here, in order to be executed after the DOM is loaded ?>
          <script>
          	//<!-- Create an instance of the multiSelector class, pass it the output target and the max number of files -->
          	var multi_selector = new MultiSelector( document.getElementById( 'files_list' ), 10 );
          	//<!-- Pass in the file element -->
          	multi_selector.addElement( document.getElementById( 'multiattach' ) );
          </script>
    </div>
    <?php } ?>
    <?php //test if captcha is enabled and the client is not yet log-in
    if($cfg && $cfg->enableCaptcha() && (!$thisclient || !$thisclient->isValid())) {
        if($_POST && $errors && !$errors['captcha'])
            $errors['captcha']='Please re-enter the text again';
    ?>
    <div class="input">
        <span class="label"><?= _('Captcha Text:')?></span>
        <span><img src="captcha.php" border="0"></span>
        <span style="vertical-align:top">
            &nbsp;&nbsp;<input type="text" name="captcha" size="7" value="">&nbsp;<i><?= _('Enter the text shown on the image.')?></i>
            <span class="error">*&nbsp;<?=$errors['captcha']?></span>
        </span>
    </div>
    <?php } ?>
    <div align="right">
      <input class="button" type="submit" name="submit_x" value="<?= _('Submit Ticket')?>">
      <input class="button" type="button" name="cancel" value="<?= _('Cancel')?>" onClick='window.location.href="index.php"'>
    </div>
</form>

