<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));

$info=($errors && $_POST)?Format::input($_POST):Format::htmlchars($role);
if($role && $_REQUEST['a']!='new'){
    $title=sprintf(_('Edit Role: %s'), ($info['dept_access']=='SADMIN')?_('Administrator'):$role['role_name']);
    $action='update';
}else {
    $title=_('Add New Role');
    $action='create';
    $info['role_enabled']=isset($info['role_enabled'])?$info['role_enabled']:1; //Default to active 
}

?>

<form action="admin.php" method="POST" name="role">
  <input type="hidden" name="do" value="<?=$action?>">
  <input type="hidden" name="a" value="<?=Format::htmlchars($_REQUEST['a'])?>">
  <input type="hidden" name="t" value="roles">
  <input type="hidden" name="role_id" value="<?=$info['role_id']?>">
  <input type="hidden" name="old_name" value="<?=$info['role_name']?>">

  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header"><td colspan=2><?=Format::htmlchars($title)?></td></tr>
      <tr class="subheader"><td colspan=2>
          <?= _('Role permissions set below applies cross all role members, but don\'t apply to Dept. Managers in some cases.') ?>
      </td></tr>
      <tr><th><?= _('Role Name:') ?></th>
          <td><input type="text" name="role_name" size=25 value="<?=$info['role_name']?>">
              &nbsp;<font class="error">*&nbsp;<?=$errors['role_name']?></font>
                  
          </td>
      </tr>
      <tr>
          <th><?= _('Role Status:') ?></th>
          <td>
              <input type="radio" name="role_enabled"  value="1"   <?=$info['role_enabled']?'checked':''?> /> <?= _('Enabled') ?>
              <input type="radio" name="role_enabled"  value="0"   <?=!$info['role_enabled']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('Disabled') ?>
              &nbsp;<font class="error">&nbsp;<?=$errors['role_enabled']?></font>
          </td>
      </tr>
      <tr><th><br><?= _('Dept Access') ?></th>
          <td class="mainTableAlt"><i><?= _('Select departments role members are allowed to access in addition to their own department.') ?></i>
              &nbsp;<font class="error">&nbsp;<?=$errors['depts']?></font><br/>
              <?php
              //Try to save the state on error...
              $access=($_POST['depts'] && $errors)?$_POST['depts']:explode(',',$info['dept_access']);
              $depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name');
              while (list($id,$name) = db_fetch_row($depts)){
                  $ck=(($access && in_array($id,$access)) || $info['dept_access']=='SADMIN')?'checked':''; ?>
                  <input type="checkbox" name="depts[]" value="<?=$id?>" <?=$ck?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> > <?=$name?><br/>
              <?php
              }
              if($info['dept_access']=='SADMIN'){ ?>
                <input type="hidden" name="depts[]" value="SADMIN">;
              <?php } ?>
                  <a href="#" onclick="return select_all(document.forms['role'])"><?= _('Select All') ?></a>&nbsp;&nbsp;
                  <a href="#" onclick="return reset_all(document.forms['role'])"><?= _('Select None') ?></a>&nbsp;&nbsp;
          </td>
      </tr>
      <tr><th><?= _('Can <b>View Unassigned</b> Tickets') ?></th>
          <td>
              <input type="radio" name="can_viewunassigned_tickets"  value="1"   <?=$info['can_viewunassigned_tickets']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="can_viewunassigned_tickets"  value="0"   <?=!$info['can_viewunassigned_tickets']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('Ability to view unassigned tickets. (Dept Manager are always allowed).') ?></i>
          </td>
      </tr>
      <tr><th><?= _('Can <b>Create</b> Tickets') ?></th>
          <td>
              <input type="radio" name="can_create_tickets"  value="1"   <?=$info['can_create_tickets']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="can_create_tickets"  value="0"   <?=!$info['can_create_tickets']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('Ability to open tickets on behalf of users.') ?></i>
          </td>
      </tr>
      <tr><th><?= _('Can <b>Edit</b> Tickets') ?></th>
          <td>
              <input type="radio" name="can_edit_tickets"  value="1"   <?=$info['can_edit_tickets']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="can_edit_tickets"  value="0"   <?=!$info['can_edit_tickets']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('Ability to change the contents and the data of the tickets.') ?></i>
          </td>
      </tr>
      <tr><th><?= _('Can <b>Close</b> Tickets') ?></th>
          <td>
              <input type="radio" name="can_close_tickets"  value="1" <?=$info['can_close_tickets']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="can_close_tickets"  value="0" <?=!$info['can_close_tickets']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('<b>Mass Close Only:</b> Staff can still close one ticket at a time when set to No.') ?></i>
          </td>
      </tr>
      <tr><th><?= _('Can <b>Change</b> Tickets Priority') ?></th>
          <td>
              <input type="radio" name="can_changepriority_tickets"  value="1" <?=$info['can_changepriority_tickets']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="can_changepriority_tickets"  value="0" <?=!$info['can_changepriority_tickets']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('Ability to change the priority (IE: Low, Normal, High, ...) of the tickets.') ?></i>
          </td>
      </tr>
      <tr><th><?= _('Can <b>Transfer</b> Tickets') ?></th>
          <td>
              <input type="radio" name="can_transfer_tickets"  value="1" <?=$info['can_transfer_tickets']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="can_transfer_tickets"  value="0" <?=!$info['can_transfer_tickets']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('Ability to transfer tickets from one dept to another.') ?></i>
          </td>
      </tr>
      <tr><th><?= _('Can <b>Assign</b> Tickets') ?></th>
          <td>
              <input type="radio" name="can_assign_tickets"  value="1" <?=$info['can_assign_tickets']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="can_assign_tickets"  value="0" <?=!$info['can_assign_tickets']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('Ability to assign tickets to a staff member.') ?></i>
          </td>
      </tr>
      <tr><th><?= _('Can <b>Delete</b> Tickets') ?></th>
          <td>
              <input type="radio" name="can_delete_tickets"  value="1"   <?=$info['can_delete_tickets']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="can_delete_tickets"  value="0"   <?=!$info['can_delete_tickets']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('<b>Note</b>: Deleted tickets can\'t be recovered.') ?></i>
          </td>
      </tr>
      <tr><th><?= _('Can <b>Ban</b> Emails') ?></th>
          <td>
              <input type="radio" name="can_ban_emails"  value="1" <?=$info['can_ban_emails']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="can_ban_emails"  value="0" <?=!$info['can_ban_emails']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('Ability to add/remove emails from banlist via tickets interface.') ?></i>
          </td>
      </tr>
      <tr><th><?= _('Can <b>Manage</b> Std. Replies') ?></th>
          <td>
              <input type="radio" name="can_manage_stdr"  value="1" <?=$info['can_manage_stdr']?'checked':''?> /><?= _('Yes') ?>
              <input type="radio" name="can_manage_stdr"  value="0" <?=!$info['can_manage_stdr']?'checked':''?> <?=($info['dept_access']=='SADMIN')?'disabled':''?> /><?= _('No') ?>
              &nbsp;&nbsp;<i><?= _('Ability to add/update/disable/delete standard responses.') ?></i>
          </td>
      </tr>
  </table>
  <br />
  <div class="centered">
    <input class="button" type="submit" name="submit" value="<?= _('Submit') ?>">
    <input class="button" type="reset" name="reset" value="<?= _('Reset') ?>">
    <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="admin.php?t=roles"'>
  </div>
</form>