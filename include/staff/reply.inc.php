<?php
if(!defined('KTKADMININC') or !$thisuser->canManageStdr()) die(_('Access Denied'));
$info=($errors && $_POST)?Format::input($_POST):Format::htmlchars($answer);
if($answer && $_REQUEST['a']!='add'){
    $title=_('Edit Standard Reply');
    $action='update';
}else {
    $title=_('Add New Standard Reply');
    $action='add';
    $info['isenabled']=1;
}
?>
<div>
    <?php if($errors['err']) { ?>
        <p align="center" id="errormessage"><?=$errors['err']?></p>
    <?php }elseif($msg) { ?>
        <p align="center" id="infomessage"><?=$msg?></p>
    <?php }elseif($warn) { ?>
        <p id="warnmessage"><?=$warn?></p>
    <?php } ?>
</div>
<div class="msg"><?=$title?></div>
<form action="stdreply.php" method="POST" name="role">
  <input type="hidden" name="a" value="<?=$action?>">
  <input type="hidden" name="id" value="<?=$info['stdreply_id']?>">
  <table width="100%" border="0" cellspacing=1 cellpadding=2>  
  <tr><td width=80px>Title:</td>
      <td><input type="text" size=45 name="title" value="<?=$info['title']?>">
          &nbsp;<font class="error">*&nbsp;<?=$errors['title']?></font>
      </td>
  </tr>
  <tr>
      <td><?= _('Status:') ?></td>
      <td>
          <input type="radio" name="isenabled"  value="1"   <?=$info['isenabled']?'checked':''?> /> <?= _('Active') ?>
          <input type="radio" name="isenabled"  value="0"   <?=!$info['isenabled']?'checked':''?> /><?= _('Inactive') ?>
          &nbsp;<font class="error">&nbsp;<?=$errors['isenabled']?></font>
      </td>
  </tr>
  <tr><td><?= _('Category:') ?></td>
      <td><?= _('Department under which the \'answer\' will be made available.') ?>&nbsp;<font class="error">&nbsp;<?=$errors['depts']?></font><br/>
          <select name=dept_id>
              <option value=0 selected><?= _('All Departments') ?></option>
              <?php
              $depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name');
              while (list($id,$name) = db_fetch_row($depts)){
                  $ck=($info['dept_id']==$id)?'selected':''; ?>
                  <option value="<?=$id?>" <?=$ck?>><?=$name?></option>
              <?php
              }?>
          </select>
      </td>
  </tr>
  <tr><td><?= _('Answer:') ?></td>
      <td><?= _('Standard Reply - Ticket\'s base variables are supported.') ?>&nbsp;<font class="error">*&nbsp;<?=$errors['answer']?></font><br/>
          <textarea name="answer" id="answer" cols="90" rows="9" wrap="soft" style="width:80%"><?=$info['answer']?></textarea>
      </td>
  </tr>
  </table><br />
  <div class="centered">
      <input class="button" type="submit" name="submit" value="<?= _('Submit') ?>">
      <input class="button" type="reset" name="reset" value="<?= _('Reset') ?>">
      <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="stdreply.php"'>
  </div>
</form>