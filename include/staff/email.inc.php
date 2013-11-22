<?php
if(!defined('KTKADMININC') || basename($_SERVER['SCRIPT_NAME'])==basename(__FILE__)) die(_('Adiaux amikoj!')); //Say hi to our friend..
if(!$thisuser || !$thisuser->isadmin()) die(_('Access Denied'));

$info=($_POST && $errors)?$_POST:array(); //Re-use the post info on error...savekeyboards.org
if($email && $_REQUEST['a']!='new'){
    $title=_('Edit Email');
    $action='update';
    if(!$info) {
        $info=$email->getInfo();
        $info['userpass']=$info['userpass']?Misc::decrypt($info['userpass'],SECRET_SALT):'';
    }
    $qstr='?t=email&id='.$email->getId();
}else {
   $title=_('New Email');
   $action='create';
   $info['smtp_auth']=isset($info['smtp_auth'])?$info['smtp_auth']:1;
}

$info=Format::htmlchars($info);
//get the goodies.
$depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE);
$priorities= db_query('SELECT priority_id,priority_desc FROM '.PRIORITY_TABLE);
?>
<div class="msg"><?=$title?></div>
<form action="admin.php<?=$qstr?>" method="post">
  <input type="hidden" name="do" value="<?=$action?>">
  <input type="hidden" name="a" value="<?=Format::htmlchars($_REQUEST['a'])?>">
  <input type="hidden" name="t" value="email">
  <input type="hidden" name="email_id" value="<?=$info['email_id']?>">
  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header"><td colspan=2><?= _('Email Info') ?></td></tr>
      <tr class="subheader">
          <td colspan=2 ><?= _('Settings are mainly for emailed tickets. For online/web tickets see help topics.') ?></td>
      </tr>
      <tr><th><?= _('Email Address') ?></th>
          <td>
              <input type="text" name="email" size=30 value="<?=$info['email']?>">&nbsp;<font class="error">*&nbsp;<?=$errors['email']?></font>
          </td>
      </tr>
      <tr><th><?= _('Email Name:') ?></th>
          <td>
              <input type="text" name="name" size=30 value="<?=$info['name']?>">&nbsp;<font class="error">&nbsp;<?=$errors['name']?></font>
              &nbsp;&nbsp;(<i><?= _('Optional email\'s FROM name.') ?></i>)
          </td>
      </tr>
      <tr><th><?= _('New Ticket Priority') ?></th>
          <td>
              <select name="priority_id">
                  <option value=0><?= _('Select Priority') ?></option>
                  <?php
                  while (list($id,$name) = db_fetch_row($priorities)){
                      $selected = ($info['priority_id']==$id)?'selected':''; ?>
                      <option value="<?=$id?>"<?=$selected?>><?=$name?></option>
                  <?php
                  }?>
              </select>&nbsp;<font class="error">*&nbsp;<?=$errors['priority_id']?></font>
          </td>
      </tr>
      <tr><th><?= _('New Ticket Dept.') ?></th>
          <td>
              <select name="dept_id">
                  <option value=0><?= _('Select Department') ?></option>
                  <?php
                  while (list($id,$name) = db_fetch_row($depts)){
                      $selected = ($info['dept_id']==$id)?'selected':''; ?>
                  <option value="<?=$id?>"<?=$selected?>><?=$name?> <?= _('Dept') ?></option>
                  <?php
                  }?>
              </select>&nbsp;<font class="error">*&nbsp;<?=$errors['dept_id']?></font>&nbsp;
          </td>
      </tr>
      <tr><th><?= _('Auto Response') ?></th>
          <td>
              <input type="checkbox" name="noautoresp" value=1 <?=$info['noautoresp']? 'checked': ''?> ><?= _('<b>Disable</b> autoresponse for this email.') ?>
              &nbsp;&nbsp;(<i><?= _('Overwrite Dept setting') ?></i>)
          </td>
      </tr>
      <tr class="subheader">
          <td colspan=2 ><?= _('<b>Login info (optional)</b>: Required when IMAP/POP and/or SMTP are enabled.') ?></td>
      </tr>
      <tr><th><?= _('Username') ?></th>
          <td><input type="text" name="userid" size=35 value="<?=$info['userid']?>" autocomplete='off' >
              &nbsp;<font class="error">&nbsp;<?=$errors['userid']?></font>
          </td>
      </tr>
      <tr><th><?= _('Password') ?></th>
          <td>
             <input type="password" name="userpass" size=35 value="<?=$info['userpass']?>" autocomplete='off'>
              &nbsp;<font class="error">&nbsp;<?=$errors['userpass']?></font>
          </td>
      </tr>
      <tr class="header"><td colspan=2><b><?= _('Mail Account (Optional)') ?></b></td></tr>
      <tr class="subheader"><td colspan=2>
              <?= _('Setting for fetching incoming emails. Mail fetching must be enabled with autocron active or external cron setup.') ?><br>
              <b><?= _('Please be patient, the system will try to login to mail server to validate the entered login info.') ?></b>
          <font class="error">&nbsp;<?=$errors['mail']?></font></td></tr>
      <tr><th><?= _('Status') ?></th>
          <td>
              <label><input type="radio" name="mail_active"  value="1"   <?=$info['mail_active']?'checked':''?> /><?= _('Enable') ?></label>
              <label><input type="radio" name="mail_active"  value="0"   <?=!$info['mail_active']?'checked':''?> /><?= _('Disable') ?></label>
              &nbsp;<font class="error">&nbsp;<?=$errors['mail_active']?></font>
          </td>
      </tr>
      <tr><th><?= _('Host') ?></th>
          <td><input type="text" name="mail_host" size=35 value="<?=$info['mail_host']?>">
              &nbsp;<font class="error">&nbsp;<?=$errors['mail_host']?></font>
          </td>
      </tr>
      <tr><th><?= _('Port') ?></th>
          <td><input type="text" name="mail_port" size=6 value="<?=$info['mail_port']?$info['mail_port']:''?>">
              &nbsp;<font class="error">&nbsp;<?=$errors['mail_port']?></font>
          </td>
      </tr>
      <tr><th><?= _('Protocol') ?></th>
          <td>
              <select name="mail_protocol">
                  <option value='POP'><?= _('Select') ?></option>
                  <option value='POP' <?=($info['mail_protocol']=='POP')?'selected="selected"':''?> ><?= _('POP') ?></option>
                  <option value='IMAP' <?=($info['mail_protocol']=='IMAP')?'selected="selected"':''?> ><?= _('IMAP') ?></option>
              </select>
              <font class="error">&nbsp;<?=$errors['mail_protocol']?></font>
          </td>
      </tr>

      <tr><th><?= _('Encryption') ?></th>
          <td>
               <label><input type="radio" name="mail_encryption"  value="NONE"
                  <?=($info['mail_encryption']!='SSL')?'checked':''?> /><?= _('None') ?></label>
               <label><input type="radio" name="mail_encryption"  value="SSL"
                  <?=($info['mail_encryption']=='SSL')?'checked':''?> /><?= _('SSL') ?></label>
              <font class="error">&nbsp;<?=$errors['mail_encryption']?></font>
          </td>
      </tr>
      <tr><th><?= _('Fetch Frequency') ?></th>
          <td>
              <input type="text" name="mail_fetchfreq" size=4 value="<?=$info['mail_fetchfreq']?$info['mail_fetchfreq']:''?>"> <?= _('Delay intervals in minutes') ?>
              &nbsp;<font class="error">&nbsp;<?=$errors['mail_fetchfreq']?></font>
          </td>
      </tr>
      <tr><th><?= _('Maximum Emails Per Fetch') ?></th>
          <td>
              <input type="text" name="mail_fetchmax" size=4 value="<?=$info['mail_fetchmax']?$info['mail_fetchmax']:''?>"> <?= _('Maximum emails to process per fetch.') ?>
              &nbsp;<font class="error">&nbsp;<?=$errors['mail_fetchmax']?></font>
          </td>
      </tr>
      <tr><th><?= _('Delete Messages') ?></th>
          <td>
              <input type="checkbox" name="mail_delete" value=1 <?=$info['mail_delete']? 'checked': ''?> >
              <?= _('Delete fetched message(s) (<i>recommended when using POP</i>)') ?>
              &nbsp;<font class="error">&nbsp;<?=$errors['mail_delete']?></font>
          </td>
      </tr>
      <tr class="header"><td colspan=2><b><?= _('SMTP Settings (Optional)') ?></b></td></tr>
      <tr class="subheader"><td colspan=2>
              <?= _('When enabled the <b>email account</b> will use SMTP server instead of internal PHP mail() function for outgoing emails.<br>') ?>
              <b><?= _('Please be patient, the system will try to login to SMTP server to validate the entered login info.') ?></b>
              <font class="error">&nbsp;<?=$errors['smtp']?></font></td></tr>
      <tr><th><?= _('Status') ?></th>
          <td>
              <label><input type="radio" name="smtp_active"  value="1"   <?=$info['smtp_active']?'checked':''?> /><?= _('Enable') ?></label>
              <label><input type="radio" name="smtp_active"  value="0"   <?=!$info['smtp_active']?'checked':''?> /><?= _('Disable') ?></label>
              &nbsp;<font class="error">&nbsp;<?=$errors['smtp_active']?></font>
          </td>
      </tr>
      <tr><th><?= _('SMTP Host') ?></th>
          <td><input type="text" name="smtp_host" size=35 value="<?=$info['smtp_host']?>">
              &nbsp;<font class="error">&nbsp;<?=$errors['smtp_host']?></font>
          </td>
      </tr>
      <tr><th><?= _('SMTP Port') ?></th>
          <td><input type="text" name="smtp_port" size=6 value="<?=$info['smtp_port']?$info['smtp_port']:''?>">
              &nbsp;<font class="error">&nbsp;<?=$errors['smtp_port']?></font>
          </td>
      </tr>
      <tr><th><?= _('Authentication Required?') ?></th>
          <td>

               <label><input type="radio" name="smtp_auth"  value="1"
                  <?=$info['smtp_auth']?'checked':''?> /><?= _('Yes') ?></label>
               <label><input type="radio" name="smtp_auth"  value="0"
                  <?=!$info['smtp_auth']?'checked':''?> /><?= _('No') ?></label>
              <font class="error">&nbsp;<?=$errors['smtp_auth']?></font>
          </td>
      </tr>
      <tr><th><?= _('Encryption') ?></th>
          <td><?= _('Best available authentication method is auto-selected based on what the sever supports.') ?></td>
      </tr>
  </table>
  <div class="centered">
    <input class="button" type="submit" name="submit" value="<?= _('Submit') ?>">
    <input class="button" type="reset" name="reset" value="<?= _('Reset') ?>">
    <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="admin.php?t=email"'>
  </div>
</form>
