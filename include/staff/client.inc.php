<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));

$rep=null;
$newuser=true;
if($client && $_REQUEST['a']!='new'){
    $rep=$client->getInfo();
    $title=sprintf(_('Update: %s'), $rep['client_email']);
    $action='update';
    $pwdinfo=_('To reset the password enter a new one below (min. 6 chars.)');
    $newuser=false;
}else {
    $title=_('New Client');
    $pwdinfo=_('Password required (min. 6 chars.)');
    $action='create';
    $rep['client_isactive']=isset($rep['client_isactive'])?$rep['client_isactive']:1;
}
$rep=($errors && $_POST)?Format::input($_POST):Format::htmlchars($rep);
$groups = db_query('SELECT group_id,group_name FROM '.GROUP_TABLE.' WHERE group_enabled = 1 ORDER BY group_name');


//get the goodies.
?>
<div class="msg"><?=$title?></div>
<form action="admin.php" method="post">
  <input type="hidden" name="do" value="<?=$action?>">
  <input type="hidden" name="a" value="<?=Format::htmlchars($_REQUEST['a'])?>">
  <input type="hidden" name="t" value="clients">
  <input type="hidden" name="client_id" value="<?=$rep['client_id']?>">
  <input type="hidden" name="old_client_email" value="<?=$rep['client_email']?>">
  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header"><td colspan=2><?= _('Client Account') ?></td></tr>
      <tr class="subheader"><td colspan=2><?= _('Account information') ?></td></tr>
      <tr>
          <th><?= _('E-mail (Username):') ?></th>
          <td><input type="text" name="client_email" value="<?=$rep['client_email']?>">
              &nbsp;<font class="error">*&nbsp;<?=$errors['email']?></font></td>
      <tr>
          <th><?= _('Client Group:') ?></th>
          <td>
              <select name="group_id">
                  <option value=0><?= _('Select Group') ?></option>
                  <?php
                  while (list($id,$name) = db_fetch_row($groups)){
                      $selected = ($rep['client_group_id']==$id)?'selected':''; 
                  ?>
                      <option value="<?=$id?>"<?=$selected?>><?=$name?></option>
                  <?php
                  }?>
              </select>&nbsp;<font class="error">*&nbsp;<?=$errors['group']?></font>
          </td>
      </tr>
      <tr>
          <th><?= _('Name (First, Last):') ?></th>
          <td>
              <input type="text" name="client_firstname" size=30 value="<?=$rep['client_firstname']?>">&nbsp;
              &nbsp;&nbsp;&nbsp;<input type="text" name="client_lastname" size=30 value="<?=$rep['client_lastname']?>">
              &nbsp;<font class="error">&nbsp;<?=$errors['name']?></font></td>
      </tr>
      <tr>
          <th><?= _('Organization:') ?></th>
          <td>
              <input type="text" name="client_organization" size=66 value="<?=$rep['client_organization']?>" >
                  &nbsp;<font class="error">&nbsp;<?=$errors['organization']?></font></td>
      </tr>
      <tr>
          <th><?= _('Office Phone:') ?></th>
          <td>
              <input type="text" name="client_phone" size=30 value="<?=$rep['client_phone']?>" >
                  &nbsp;<font class="error">&nbsp;<?=$errors['phone']?></font></td>
      </tr>
      <tr>
          <th><?= _('Mobile Phone:') ?></th>
          <td>
              <input type="text" name="client_mobile" size=30 value="<?=$rep['client_mobile']?>" >
                  &nbsp;<font class="error">&nbsp;<?=$errors['mobile']?></font></td>
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
      <tr class="subheader"><td colspan=2><?= _('Account Permission, Status &amp; Settings') ?></td></tr>
      <tr><th><b><?= _('Account Status') ?></b></th>
          <td>
              <input type="radio" name="client_isactive"  value="1" <?=$rep['client_isactive']?'checked':''?> /><b><?= _('Active') ?></b>
              <input type="radio" name="client_isactive"  value="0" <?=!$rep['client_isactive']?'checked':''?> /><b><?= _('Locked') ?></b>
          </td>
      </tr>
  </table>
  <div class="centered">
      <input class="button" type="submit" name="submit" value="<?= _('Submit') ?>">
      <input class="button" type="reset" name="reset" value="<?= _('Reset') ?>">
      <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="admin.php?t=clients"'>
  </div>
</form>