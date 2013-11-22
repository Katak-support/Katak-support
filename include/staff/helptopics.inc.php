<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));

//List all help topics
$sql='SELECT topic_id,isactive,topic.noautoresp,topic.dept_id,topic,dept_name,priority_desc,topic.created,topic.updated FROM '.TOPIC_TABLE.' topic '.
     ' LEFT JOIN '.DEPT_TABLE.' dept ON dept.dept_id=topic.dept_id '.
     ' LEFT JOIN '.PRIORITY_TABLE.' pri ON pri.priority_id=topic.priority_id ';
$services=db_query($sql.' ORDER BY topic'); 
?>
<div class="msg"><?= _('Help Topics') ?></div>
<form action="admin.php?t=settings" method="POST" name="topic" onSubmit="return checkbox_checker(document.forms['topic'],1,0);">
  <input type='hidden' name='t' value='topics'>
  <input type=hidden name='do' value='mass_process'>
  <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
      <tr>
        <th width="7px">&nbsp;</th>
              <th><?= _('Help Topic') ?></th>
              <th><?= _('Status') ?></th>
              <th><?= _('AutoResp.') ?></th>
              <th><?= _('Department') ?></th>
              <th><?= _('Priority') ?></th>
              <th><?= _('Last Updated') ?></th>
      </tr>
      <?php
      $class = 'row1';
      $total=0;
      $ids=($errors && is_array($_POST['tids']))?$_POST['tids']:null;
      if($services && db_num_rows($services)):
          while ($row = db_fetch_array($services)) {
              $sel=false;
              if(($ids && in_array($row['topic_id'],$ids)) or ($row['topic_id']==$topicID)){
                  $class="$class highlight";
                  $sel=true;
              }
              ?>
          <tr class="<?=$class?>" id="<?=$row['topic_id']?>">
              <td width=7px>
               <input type="checkbox" name="tids[]" value="<?=$row['topic_id']?>" <?=$sel?'checked':''?>  onClick="highLight(this.value,this.checked);">
              </td>
              <td><a href="admin.php?t=topics&id=<?=$row['topic_id']?>"><?=Format::htmlchars(Format::truncate($row['topic'],30))?></a></td>
              <td><?=$row['isactive']?_('Active'):_('<b>Disabled</b>')?></td>
              <td>&nbsp;&nbsp;<?=$row['noautoresp']?_('No'):_('<b>Yes</b>')?></td>
              <td><a href="admin.php?t=dept&id=<?=$row['dept_id']?>"><?=$row['dept_name']?></a></td>
              <td><?=$row['priority_desc']?></td>
              <td><?=Format::db_datetime($row['updated'])?></td>
          </tr>
          <?php
          $class = ($class =='row2') ?'row1':'row2';
          } //end of while.
      else: //notthing! ?> 
          <tr class="<?=$class?>"><td colspan=8><b><?= _('Query returned 0 results') ?></b></td></tr>
      <?php
      endif; ?>
  </table>
  <?php
  if(db_num_rows($services)>0): //Show options..
   ?>
      <div style="padding-left:20px">
          <?= _('Select:') ?>&nbsp;
          [<a href="#" onclick="return select_all(document.forms['topic'],true)"><?= _('All') ?></a>]&nbsp;&nbsp;
          [<a href="#" onclick="return reset_all(document.forms['topic'])"><?= _('None') ?></a>]&nbsp;&nbsp;
          [<a href="#" onclick="return toogle_all(document.forms['topic'],true)"><?= _('Toggle') ?></a>]&nbsp;&nbsp;
      </div>
      <div class="centered">
          <input class="button" type="submit" name="enable" value="<?= _('Enable') ?>"
              onClick=' return confirm("<?= _('Are you sure you want to make selected services active?') ?>");'>
          <input class="button" type="submit" name="disable" value="<?= _('Disable') ?>"
              onClick=' return confirm("<?= _('Are you sure you want to DISABLE selected services?') ?>");'>
          <input class="button" type="submit" name="delete" value="<?= _('Delete') ?>"
              onClick=' return confirm("<?= _('Are you sure you want to DELETE selected services?') ?>");'>
      </div>
  <?php
  endif;
  ?>
</form>
