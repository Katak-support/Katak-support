<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));

//List all clients...not pagenating...
$sql='SELECT * FROM '.CLIENT_TABLE.' LEFT JOIN '.GROUP_TABLE.' ON client_group_id=group_id';
if($_REQUEST['gid'] && is_numeric($_REQUEST['gid'])){
  $sql.=' WHERE client_group_id='.db_input($_REQUEST['gid']);
  $groupName=db_fetch_array(db_query('SELECT group_name FROM '.GROUP_TABLE.' WHERE group_id='.db_input($_REQUEST['gid'])));
}

$clients=db_query($sql.' ORDER BY client_lastname,client_firstname');
$showing=($_REQUEST['gid'])?_("Clients in the group: ").$groupName['group_name']:_("Client Members");
$showing .= $cfg->getUserLogRequired()?"":" &nbsp (" . _("Note: Client log-in disabled") . ")";
?>
<div class="msg">&nbsp;<?=$showing?>&nbsp;</div>
<form action="admin.php?t=clients" method="POST" name="client" onSubmit="return checkbox_checker(document.forms['client'],1,0);">
  <input type=hidden name='a' value='client'>
  <input type=hidden name='do' value='mass_process'>
   <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
      <tr>
        <th width="7px">&nbsp;</th>
        <th><?= _('Email (Username)') ?></th>
        <th><?= _('Full Name') ?></th>
        <th><?= _('Organization') ?></th>
        <th><?= _('Status') ?></th>
        <th><?= _('Group') ?></th>
        <th><?= _('Created on') ?></th>
        <th><?= _('Last Login') ?></th>
      </tr>
      <?php
      $class = 'row1';
      $total=0;
      $uids=($errors && is_array($_POST['uids']))?$_POST['uids']:null;
      if($clients && db_num_rows($clients)):
          while ($row = db_fetch_array($clients)) {
            ($row['isadmin'] && !strcasecmp(ADMIN_EMAIL,$row['email']))?$sysadmin=1:$sysadmin=0; // Is System Admin?
            $sel=false;
            if(($uids && in_array($row['client_id'],$uids)) or ($uID && $uID==$row['client_id'])){
                $class="$class highlight";
                $sel=true;
            }
            $name=ucfirst($row['client_firstname'].' '.$row['client_lastname']);
            ?>
            <tr class="<?=$class?>" id="<?=$row['client_id']?>">
                <td width=7px>
                  <input type="checkbox" name="uids[]" value="<?=$row['client_id']?>" <?=$sel?'checked':''?> 
                      onClick="highLight(this.value,this.checked);">
                </td>
                <td><a href="admin.php?t=clients&id=<?=$row['client_id']?>"><?=$row['client_email']?></a>&nbsp;</td>
                <td><?=Format::htmlchars($name)?>&nbsp;</td>
                <td><?=$row['client_organization']?></td>
                <td><?=$row['client_isactive']?_('Active'):'<b>'._('Locked').'</b>'?></td>
                <td><?=$row['group_name']?></td>
                <td><?=Format::db_date($row['client_created'])?></td>
                <td><?=Format::db_datetime($row['client_lastlogin'])?>&nbsp;</td>
            </tr>
            <?php
            $class = ($class =='row2') ?'row1':'row2';
          } //end of while.
      else: ?> 
          <tr class="<?=$class?>"><td colspan=7><b><?= _('Query returned 0 results') ?></b></td></tr>
      <?php
      endif; ?>
   </table>
  <?php
  if(db_num_rows($clients)>0): //Show options..
  ?>
    <div style="margin-left:20px;">
        <?= _('Select:') ?>&nbsp;
        [<a href="#" onclick="return select_all(document.forms['client'],true)"><?= _('All') ?></a>]&nbsp;&nbsp;
        [<a href="#" onclick="return toogle_all(document.forms['client'],true)"><?= _('Toggle') ?></a>]&nbsp;&nbsp;
        [<a href="#" onclick="return reset_all(document.forms['client'])"><?= _('None') ?></a>]&nbsp;&nbsp;
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
