<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));
//Get the config info.
$config=($errors && $_POST)?Format::input($_POST):$cfg->getConfig();
?>
<form action="admin.php?t=attach" method="post">
  <input type="hidden" name="t" value="attach">

  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
    <tr class="header">
        <td colspan=2>&nbsp;<?= _('Attachments Settings') ?></td>
    </tr>
    <tr class="subheader">
      <td colspan=2">
          <?= _('Before enabling attachments make sure you understand the security settings and issues related to file uploads.') ?></td>
    </tr>
    <tr>
        <th width="165"><?= _('Allow Attachments') ?>:</th>
      <td>
          <input type="checkbox" name="allow_attachments" <?=$config['allow_attachments'] ?'checked':''?>><b><?= _('Allow Attachments') ?></b>
          &nbsp; (<i><?= _('Global Setting') ?></i>)
          &nbsp;<font class="error">&nbsp;<?=$errors['allow_attachments']?></font>
      </td>
    </tr>
    <tr>
        <th><?= _('Emailed Attachments:') ?></th>
        <td>
          <input type="checkbox" name="allow_email_attachments" <?=$config['allow_email_attachments'] ? 'checked':''?> > <?= _('Accept emailed files') ?>
              &nbsp;<font class="warn">&nbsp;<?=$warn['allow_email_attachments']?></font>
      </td>
    </tr>
   <tr>
       <th><?= _('Online Attachments:') ?></th>
      <td>
          <input type="checkbox" name="allow_online_attachments" <?=$config['allow_online_attachments'] ?'checked':''?> >
          <?= _('Allow online attachments upload') ?><br/>&nbsp;&nbsp;&nbsp;&nbsp;
          <input type="checkbox" name="allow_online_attachments_onlogin" <?=$config['allow_online_attachments_onlogin'] ?'checked':''?> >
          <?= _('Authenticated users Only.') ?> (<i><?= _('User must be logged in to upload files') ?> </i>)
              <font class="warn">&nbsp;<?=$warn['allow_online_attachments']?></font>
      </td>
    </tr>
    <tr>
      <th><?= _('Staff Response Files:') ?></th>
      <td>
          <input type="checkbox" name="email_attachments" <?=$config['email_attachments']?'checked':''?> ><?= _('Email attachments to the user') ?>
      </td>
    </tr>
    <tr>
        <th nowrap><?= _('Maximum File Size:') ?></th>
      <td>
          <input type="text" name="max_file_size" value="<?=$config['max_file_size']?>"> <i> bytes
          <br /><?= _('Note: UPLOAD_MAX_FILESIZE in php.ini is set to')?> <?=ini_get('upload_max_filesize')?> ,&nbsp; <?=_('POST_MAX_SIZE to')?> <?=ini_get('post_max_size') ?>bytes</i>
          <font class="error">&nbsp;<?=$errors['max_file_size']?></font>
      </td>
    </tr>
    <tr>
        <th><?= _('Attachment Folder:') ?></th>
      <td>
          <?= _('Web user (e.g apache) must have write access to the folder.') ?> &nbsp;<font class="error">&nbsp;<?=$errors['upload_dir']?></font><br>
        <input type="text" size=60 name="upload_dir" value="<?=$config['upload_dir']?>"> 
        <font color=red>
        <?=$attwarn?>
        </font>
      </td>
    </tr>
    <tr>
        <th><br /><?= _('Accepted File Types:') ?></th>
      <td>
          <?= _('Enter file extensions allowed separated by a comma. e.g <i>.doc, .pdf, </i>') ?> <br>
          <?= _('To accept all files enter wildcard <b><i>.*</i></b>&nbsp;&nbsp;i.e dotStar (NOT recommended).') ?>
          <textarea name="allowed_filetypes" cols="21" rows="4" style="width: 65%;" wrap=HARD ><?=$config['allowed_filetypes']?></textarea>
      </td>
    </tr>
  </table>

  <div class="centered">
     <input class="button" type="submit" name="submit" value="<?= _('Save Changes') ?>">
     <input class="button" type="reset" name="reset" value="<?= _('Reset Changes') ?>">
  </div>
</form>
