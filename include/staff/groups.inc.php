<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));

//List all groups.   
$sql='SELECT * ,count(client_id) as clients FROM '.GROUP_TABLE.' LEFT JOIN '.CLIENT_TABLE.' ON client_group_id=group_id GROUP BY group_id ORDER BY group_name';
$groups=db_query($sql);    
$showing=($num=db_num_rows($groups))?_('Client Groups'):'No groups?';
?>
<div class="msg"><?=$showing?></div>
<form action="admin.php?t=groups" method="POST" name="groups" onSubmit="return checkbox_checker(document.forms['groups'],1,0);">
  <input type=hidden name='a' value='update_groups'>
  <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
    <tr>
      <th width="7px">&nbsp;</th>
      <th width=200><?= _('Group Name') ?></th>
      <th width=100><?= _('Status') ?></th>
      <th width=10>&nbsp;<?= _('Members') ?></th>
      <th>&nbsp;<?= _('Created on') ?></th>
      <th><?= _('Last Updated') ?></th>
    </tr>
    <?php
    $class = 'row1';
    $total=0;
    $grps=($errors && is_array($_POST['grps']))?$_POST['grps']:null;
    if($groups && db_num_rows($groups)):
        while ($row = db_fetch_array($groups)) {
            $sel=false;
            if(($grps && in_array($row['group_id'],$grps)) || ($gID && $gID==$row['group_id']) ){
                $class="$class highlight";
                $sel=true;
            }
            ?>
            <tr class="<?=$class?>" id="<?=$row['role_id']?>">
                <td width=7px>
                  <input type="checkbox" name="groups[]" value="<?=$row['group_id']?>" <?=$sel?'checked':''?> onClick="highLight(this.value,this.checked);">
                </td>
                <td><a href="admin.php?t=groups&id=<?=$row['group_id']?>"><?=Format::htmlchars($row['group_name'])?></a></td>
                <td><b><?=$row['group_enabled']?_('Active'):_('Disabled')?></b></td>
                <td>&nbsp;&nbsp;
                    <?php if($row['clients']>0) { ?>
                        <a href="admin.php?t=clients&gid=<?=$row['group_id']?>"><b><?=$row['clients']?></b></a>
                    <?php }else{ ?> 0
                    <?php } ?>
                </td>
                <td><?=Format::db_date($row['group_created'])?></td>
                <td><?=Format::db_datetime($row['group_updated'])?></td>
            </tr>
            <?php
            $class = ($class =='row2') ?'row1':'row2';
        } //end of while.
    else: //not group found!! ?> 
        <tr class="<?=$class?>"><td colspan=6><b><?= _('Query returned 0 results') ?></b></td></tr>
    <?php
    endif; ?>
  </table>
  <?php
  if(db_num_rows($groups)>0): //Show options..
   ?>
      <div style="padding-left:20px;">
          <?= _('Select:') ?>&nbsp;
          [<a href="#" onclick="return select_all(document.forms['groups'],true)"><?= _('All') ?></a>]&nbsp;&nbsp;
          [<a href="#" onclick="return toogle_all(document.forms['groups'],true)"><?= _('Toggle') ?></a>]&nbsp;&nbsp;
          [<a href="#" onclick="return reset_all(document.forms['groups'])"><?= _('None') ?></a>]&nbsp;&nbsp;
      </div>
      <div class="centered">
          <input class="button" type="submit" name="activate_groups" value="<?= _('Enable') ?>"
              onClick=' return confirm("<?= _('Are you sure you want to ENABLE selected group(s)') ?>");'>
          <input class="button" type="submit" name="disable_groups" value="<?= _('Disable') ?>"
              onClick=' return confirm("<?= _('Are you sure you want to DISABLE selected group(s)') ?>");'>
          <input class="button" type="submit" name="delete_groups" value="<?= _('Delete') ?>"
              onClick=' return confirm("<?= _('Are you sure you want to DELETE selected group(s)') ?>");'>
      </div>

  <?php
  endif;
  ?>
</form>
