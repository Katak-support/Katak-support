<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));
//List all Depts
$sql='SELECT dept.dept_id,dept_name,email.email_id,email.email,email.name as email_name,ispublic,count(staff.staff_id) as users '.
     ',CONCAT_WS(" ",mgr.firstname,mgr.lastname) as manager,mgr.staff_id as manager_id,dept.created,dept.updated  FROM '.DEPT_TABLE.' dept '.
     ' LEFT JOIN '.STAFF_TABLE.' mgr ON dept.manager_id=mgr.staff_id '.
     ' LEFT JOIN '.EMAIL_TABLE.' email ON dept.email_id=email.email_id '.
     ' LEFT JOIN '.STAFF_TABLE.' staff ON dept.dept_id=staff.dept_id ';
$depts=db_query($sql.' GROUP BY dept.dept_id ORDER BY dept_name');    
?>
<div class="msg"><?= _('Departments') ?></div>
<form action="admin.php?t=dept" method="POST" name="depts" onSubmit="return checkbox_checker(document.forms['depts'],1,0);">
<input type=hidden name='do' value='mass_process'>
  <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
      <tr>
        <th width="7px">&nbsp;</th>
              <th><?= _('Dept. Name') ?></th>
              <th><?= _('Type') ?></th>
              <th width=10><?= _('Users') ?></th>
              <th><?= _('Primary Outgoing Email') ?></th>
              <th><?= _('Manager') ?></th>
      </tr>
      <?php
      $class = 'row1';
      $total=0;
      $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
      if($depts && db_num_rows($depts)):
          $defaultId=$cfg->getDefaultDeptId();
          while ($row = db_fetch_array($depts)) {
              $sel=false;
              if(($ids && in_array($row['dept_id'],$ids)) && ($deptID && $deptID==$row['dept_id'])){
                  $class="$class highlight";
                  $sel=true;
              }
              $row['email']=$row['email_name']?($row['email_name'].' &lt;'.$row['email'].'&gt;'):$row['email'];
              $default=($defaultId==$row['dept_id'])?'(Default)':'';
              ?>
              <tr class="<?=$class?>" id="<?=$row['dept_id']?>">
                  <td width=7px>
                    <input type="checkbox" name="ids[]" value="<?=$row['dept_id']?>" <?=$sel?'checked':''?>  <?=$default?'disabled':''?>
                              onClick="highLight(this.value,this.checked);"> </td>
                  <td><a href="admin.php?t=dept&id=<?=$row['dept_id']?>"><?=$row['dept_name']?></a>&nbsp;<?=$default?></td>
                  <td><?=$row['ispublic']?'Public':'<b>Private</b>'?></td>
                  <td>&nbsp;&nbsp;
                      <b>
                      <?php if($row['users']>0) { ?>
                          <a href="admin.php?t=staff&dept=<?=$row['dept_id']?>"><?=$row['users']?></a>
                      <?php }else{ ?> 0
                      <?php } ?>
                      </b>
                  </td>
                  <td><a href="admin.php?t=email&id=<?=$row['email_id']?>"><?=$row['email']?></a></td>
                  <td><a href="admin.php?t=staff&id=<?=$row['manager_id']?>"><?=$row['manager']?>&nbsp;</a></td>
              </tr>
              <?php
              $class = ($class =='row2') ?'row1':'row2';
            } //end of while.
      else: //not tickets found!! ?> 
          <tr class="<?=$class?>"><td colspan=6><b><?= _('Query returned 0 results') ?></b></td></tr>
      <?php
      endif; ?>
  </table>
  <?php
  if($depts && db_num_rows($depts)): //Show options..
   ?>
      <div>
          <?= _('Select:') ?>&nbsp;
          [<a href="#" onclick="return select_all(document.forms['depts'],true)"><?= _('All') ?></a>]&nbsp;&nbsp;
          [<a href="#" onclick="return reset_all(document.forms['depts'])"><?= _('None') ?></a>]&nbsp;&nbsp;
          [<a href="#" onclick="return toogle_all(document.forms['depts'],true)"><?= _('Toggle') ?></a>]&nbsp;&nbsp;
      </div>
      <div class="centered">
          <input class="button" type="submit" name="public" value="<?= _('Make Public') ?>"
              onClick=' return confirm("<?= _('Are you sure you want to make selected depts(s) public?') ?>");'>
          <input class="button" type="submit" name="private" value="<?= _('Make Private') ?>"
              onClick=' return confirm("<?= _('Are you sure you want to make selected depts(s) private?') ?>");'>
          <input class="button" type="submit" name="delete" value="<?= _('Delete Dept(s)') ?>"
              onClick=' return confirm("<?= _('Are you sure you want to DELETE selected depts(s)?') ?>");'>
      </div>
  <?php
  endif;
  ?>  
</form>
