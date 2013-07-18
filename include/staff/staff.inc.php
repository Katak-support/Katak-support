<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));

$rep=null;
$newuser=true;
if($staff && $_REQUEST['a']!='new'){
    $rep=$staff->getInfo();
    $title=sprintf(_('Update: %s %s'), $rep['firstname'], $rep['lastname']);
    $action='update';
    $pwdinfo=_('To reset the password enter a new one below (min. 6 chars.)');
    $newuser=false;
    ($staff->isadmin() && !strcasecmp(ADMIN_EMAIL,$staff->getEmail()))?$sysadmin=1:$sysadmin=0;
}else {
    $title=_('New Staff Member');
    $pwdinfo=_('Temp password required (min. 6 chars.)');
    $action='create';
    $rep['resetpasswd']=isset($rep['resetpasswd'])?$rep['resetpasswd']:1;
    $rep['isactive']=isset($rep['isactive'])?$rep['isactive']:1;
    $rep['dept_id']=$rep['dept_id']?$rep['dept_id']:$_GET['dept'];
    $rep['isvisible']=isset($rep['isvisible'])?$rep['isvisible']:1;
    $sysadmin=0;
}
$rep=($errors && $_POST)?Format::input($_POST):Format::htmlchars($rep);

//get the goodies.
$roles = db_query('SELECT role_id,role_name FROM '.GROUP_TABLE);
$depts = db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE);

?>
<div class="msg"><?=$title?></div>
<form action="admin.php" method="post">
  <input type="hidden" name="do" value="<?=$action?>">
  <input type="hidden" name="a" value="<?=Format::htmlchars($_REQUEST['a'])?>">
  <input type="hidden" name="t" value="staff">
  <input type="hidden" name="staff_id" value="<?=$rep['staff_id']?>">
  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header"><td colspan=2><?= _('User Account') ?></td></tr>
      <tr class="subheader"><td colspan=2><?= _('Account information') ?></td></tr>
      <tr>
          <th><?= _('Username:') ?></th>
          <td><input type="text" name="username" value="<?=$rep['username']?>">
              &nbsp;<font class="error">*&nbsp;<?=$errors['username']?></font></td>
      </tr>
      <tr>
          <th><?= _('Department:') ?></th>
          <td>
              <select name="dept_id">
                  <option value=0><?= _('Select Department') ?></option>
                  <?php
                  while (list($id,$name) = db_fetch_row($depts)){
                      $selected = ($rep['dept_id']==$id)?'selected':''; ?>
                  <option value="<?=$id?>" <?=$selected?>><?=$name?> </option>
                  <?php
                  }?>
              </select>&nbsp;<font class="error">*&nbsp;<?=$errors['dept']?></font>
          </td>
      </tr>
      <tr>
          <th><?= _('Staff Role:') ?></th>
          <td>
              <select name="role_id">
                  <option value=0><?= _('Select Role') ?></option>
                  <?php
                  while (list($id,$name) = db_fetch_row($roles)){
                      $selected = ($rep['role_id']==$id)?'selected':''; 
                      if($sysadmin ){
                        if ($selected=='selected') {?>
                          <option value="<?=$id?>"<?=$selected?>><?=$name?></option>
                      <?php }
                      } else {?>
                        <option value="<?=$id?>"<?=$selected?>><?=$name?></option>
                  <?php
                  }}?>
              </select>&nbsp;<font class="error">*&nbsp;<?=$errors['role']?></font>
          </td>
      </tr>
      <tr>
          <th><?= _('Name (First, Last):') ?></th>
          <td>
              <input type="text" name="firstname" size=30 value="<?=$rep['firstname']?>">&nbsp;<font class="error">*</font>
              &nbsp;&nbsp;&nbsp;<input type="text" name="lastname" size=30 value="<?=$rep['lastname']?>">
              &nbsp;<font class="error">*&nbsp;<?=$errors['name']?></font></td>
      </tr>
      <tr>
          <th><?= _('Email Address:') ?></th>
          <td><input type="text" name="email" size=30 value="<?=$rep['email']?>" <?=$sysadmin?'readonly':''?> >
              &nbsp;<font class="error">*&nbsp;<?=$errors['email']?></font></td>
      </tr>
      <tr>
          <th><?= _('Office Phone:') ?></th>
          <td>
              <input type="text" name="phone" size=30 value="<?=$rep['phone']?>" >
                  &nbsp;<font class="error">&nbsp;<?=$errors['phone']?></font></td>
      </tr>
      <tr>
          <th><?= _('Mobile Phone:') ?></th>
          <td>
              <input type="text" name="mobile" size=30 value="<?=$rep['mobile']?>" >
                  &nbsp;<font class="error">&nbsp;<?=$errors['mobile']?></font></td>
      </tr>
      <tr>
          <th><?= _('Signature:') ?></th>
          <td><textarea name="signature" cols="21" rows="5" style="width: 60%;"><?=$rep['signature']?></textarea></td>
      </tr>
      <tr>
          <th><?= _('Password:') ?></th>
          <td><i><?=$pwdinfo?></i><br/>
              <input type="password" name="npassword" AUTOCOMPLETE=OFF >
                  &nbsp;<font class="error">*&nbsp;<?=$errors['npassword']?></font></td>
      </tr>
      <tr>
          <th><?= _('Confirm Password:') ?></th>
          <td class="mainTableAlt"><input type="password" name="vpassword" AUTOCOMPLETE=OFF >
              &nbsp;<font class="error">*&nbsp;<?=$errors['vpassword']?></font></td>
      </tr>
      <tr>
          <th><?= _('Forced Password Change:') ?></th>
          <td>
              <input type="checkbox" name="resetpasswd" <?=$rep['resetpasswd'] ? 'checked': ''?>><?= _('Require a change of password in the next login') ?></td>
      </tr>
      <tr class="subheader"><td colspan=2><?= _('Account Permission, Status &amp; Settings') ?></td></tr>
      <tr><th><b><?= _('Account Status') ?></b></th>
          <td>
              <input type="radio" name="isactive"  value="1" <?=$rep['isactive']?'checked':''?> /><b><?= _('Active') ?></b>
              <input type="radio" name="isactive"  value="0" <?=!$rep['isactive']?'checked':''?> <?=$sysadmin?'disabled':''?> /><b><?= _('Locked') ?></b>
          </td>
      </tr>
      <tr><th><?= _('Directory Listing') ?></th>
          <td>
              <input type="checkbox" name="isvisible" <?=$rep['isvisible'] ? 'checked': ''?>><?= _('Show the user on staff\'s directory') ?>
          </td>
      </tr>
      <tr><th><?= _('Vacation Mode') ?></th>
          <td class="mainTableAlt">
           <input type="checkbox" name="onvacation" <?=$rep['onvacation'] ? 'checked': ''?>>
           <?= _('Staff on vacation mode.') ?> (<i><?= _('No ticket assignment or Alerts') ?></i>)
              &nbsp;<font class="error">&nbsp;<?=$errors['vacation']?></font>
          </td>
      </tr>
  </table>
  <div class="centered">
      <input class="button" type="submit" name="submit" value="<?= _('Submit') ?>">
      <input class="button" type="reset" name="reset" value="<?= _('Reset') ?>">
      <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="admin.php?t=staff"'>
  </div>
</form>