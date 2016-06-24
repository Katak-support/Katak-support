<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));

$info=($errors && $_POST)?Format::input($_POST):Format::htmlchars($group);
if($group && $_REQUEST['a']!='new'){
    $title=sprintf(_('Edit Group: %s'), ($info['dept_access']=='SADMIN')?_('Administrator'):$group['group_name']);
    $action='update';
}else {
    $title=_('Add New Group');
    $action='create';
    $info['group_enabled']=isset($info['group_enabled'])?$info['group_enabled']:1; //Default to active 
}

?>

<form action="admin.php" method="POST" name="groups">
  <input type="hidden" name="do" value="<?=$action?>">
  <input type="hidden" name="a" value="<?=Format::htmlchars($_REQUEST['a'])?>">
  <input type="hidden" name="t" value="groups">
  <input type="hidden" name="group_id" value="<?=$info['group_id']?>">
  <input type="hidden" name="old_name" value="<?=$info['group_name']?>">

  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header"><td colspan=2><?=Format::htmlchars($title)?></td></tr>
      <tr class="subheader"><td colspan=2>
          <?= _('Group permissions set below applies cross all groups members.') ?>
      </td></tr>
      <tr><th><?= _('Group Name:') ?></th>
          <td><input type="text" name="group_name" size=25 value="<?=$info['group_name']?>">
              &nbsp;<font class="error">*&nbsp;<?=$errors['group_name']?></font>
                  
          </td>
      </tr>
      <tr>
          <th><?= _('Group Status:') ?></th>
          <td>
              <input type="radio" name="group_enabled"  value="1"   <?=$info['group_enabled']?'checked':''?> /> <?= _('Enabled') ?>
              <input type="radio" name="group_enabled"  value="0"   <?=!$info['group_enabled']?'checked':''?> /><?= _('Disabled') ?>
              &nbsp;<font class="error">&nbsp;<?=$errors['group_enabled']?></font>
          </td>
      </tr>
<!--  
      <tr><th><?= _('Can <b>Edit</b> Tickets') ?></th>
          <td>
              <input type="radio" name="group_can_edit_tickets"  value="1"   <?=$info['group_can_edit_tickets']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="group_can_edit_tickets"  value="0"   <?=!$info['group_can_edit_tickets']?'checked':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('Ability to change the contents and the data of the tickets.') ?></i>
          </td>
      </tr>
-->
  </table>
  <br />
  <div class="centered">
    <input class="button" type="submit" name="submit" value="<?= _('Submit') ?>">
    <input class="button" type="reset" name="reset" value="<?= _('Reset') ?>">
    <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="admin.php?t=groups"'>
  </div>
</form>