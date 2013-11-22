<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));

//List all staff members...not pagenating...
$sql='SELECT staff.staff_id, staff.role_id, staff.dept_id, firstname, lastname, username, email'.
     ',isactive, onvacation, isadmin, role_name, role_enabled, dept_name, manager_id, DATE(staff.created) as created, lastlogin, staff.updated '.
     ' FROM '.STAFF_TABLE.' staff '.
     ' LEFT JOIN '.GROUP_TABLE.' roles ON staff.role_id=roles.role_id'.
     ' LEFT JOIN '.DEPT_TABLE.' dept ON staff.dept_id=dept.dept_id';
    
if($_REQUEST['dept'] && is_numeric($_REQUEST['dept'])){
    $id=$_REQUEST['dept'];
    $sql.=' WHERE staff.dept_id='.db_input($_REQUEST['dept']);
}
$users=db_query($sql.' ORDER BY lastname,firstname');
$showing=($num=db_num_rows($users))?_("Staff Members"):sprintf(_("No staff found. <a href='admin.php?t=staff&a=new&dept=%s'>Add New Staff</a>."), $id);
?>
<div class="msg">&nbsp;<?=$showing?>&nbsp;</div>
<form action="admin.php?t=staff" method="POST" name="staff" onSubmit="return checkbox_checker(document.forms['staff'],1,0);">
  <input type=hidden name='a' value='staff'>
  <input type=hidden name='do' value='mass_process'>
   <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
      <tr>
        <th width="7px">&nbsp;</th>
        <th><?= _('Full Name') ?></th>
        <th><?= _('Username') ?></th>
        <th><?= _('Status') ?></th>
        <th><?= _('Role') ?></th>
        <th><?= _('Dept') ?></th>
        <th><?= _('Created on') ?></th>
        <th><?= _('Last Login') ?></th>
      </tr>
      <?php
      $class = 'row1';
      $total=0;
      $uids=($errors && is_array($_POST['uids']))?$_POST['uids']:null;
      if($users && db_num_rows($users)):
          while ($row = db_fetch_array($users)) {
            ($row['isadmin'] && !strcasecmp(ADMIN_EMAIL,$row['email']))?$sysadmin=1:$sysadmin=0; // Is System Admin?
            $sel=false;
            if(($uids && in_array($row['staff_id'],$uids)) or ($uID && $uID==$row['staff_id'])){
                $class="$class highlight";
                $sel=true;
            }
            $name=ucfirst($row['firstname'].' '.$row['lastname']);
            ?>
            <tr class="<?=$class?>" id="<?=$row['staff_id']?>">
                <td width=7px> <?php // Disable the first admin account: it can't be deleted! ?>
                  <input type="checkbox" name="uids[]" value="<?=$row['staff_id']?>" <?=$sel?'checked':''?> <?=$sysadmin?'disabled':''?> 
                      onClick="highLight(this.value,this.checked);">
                </td>
                <td><a href="admin.php?t=staff&id=<?=$row['staff_id']?>"><?=Format::htmlchars($name)?></a>&nbsp;</td>
                <td><?=$row['username']?></td>
                <td><?=$row['isactive']?_('Active'):'<b>'._('Locked').'</b>'?><?=$row['onvacation']?'&nbsp;(<i>'._('Vacation').'</i>)':''?></td>
                <td><a href="admin.php?t=grp&id=<?=$row['role_id']?>"><?=Format::htmlchars($row['role_name'])?></a><?=$sysadmin?'*':''?><?=$row['role_enabled']?'':' (<i>'._('Disabled').'</i>)'?></td>
                <td><a href="admin.php?t=dept&id=<?=$row['dept_id']?>"><?=Format::htmlchars($row['dept_name'])?></a><?=$row['manager_id']==$row['staff_id']?'&nbsp;<i>('._('mng').')</i>':''?></td>
                <td><?=Format::db_date($row['created'])?></td>
                <td><?=Format::db_datetime($row['lastlogin'])?>&nbsp;</td>
            </tr>
            <?php
            $class = ($class =='row2') ?'row1':'row2';
          } //end of while.
      else: ?> 
          <tr class="<?=$class?>"><td colspan=8><b><?= _('Query returned 0 results') ?></b></td></tr>
      <?php
      endif; ?>
   </table>
  <?php
  if(db_num_rows($users)>0): //Show options..
  ?>
    <div style="margin-left:20px;">
        <?= _('Select:') ?>&nbsp;
        [<a href="#" onclick="return select_all(document.forms['staff'],true)"><?= _('All') ?></a>]&nbsp;&nbsp;
        [<a href="#" onclick="return toogle_all(document.forms['staff'],true)"><?= _('Toggle') ?></a>]&nbsp;&nbsp;
        [<a href="#" onclick="return reset_all(document.forms['staff'])"><?= _('None') ?></a>]&nbsp;&nbsp;
    </div>
    <div class="centered">
            <input class="button" type="submit" name="enable" value="<?= _('Enable') ?>"
            onClick=' return confirm("<?= _('Are you sure you want to ENABLE selected user(s)?') ?>");'>
            <input class="button" type="submit" name="disable" value="<?= _('Lock') ?>"
            onClick=' return confirm("<?= _('Are you sure you want to LOCK selected user(s)?') ?>");'>
            <input class="button" type="submit" name="delete" value="<?= _('Delete') ?>"
            onClick=' return confirm("<?= _('Are you sure you want to DELETE selected user(s)?') ?>");'>
    </div>
  <?php
  endif;
  ?>
</form>
