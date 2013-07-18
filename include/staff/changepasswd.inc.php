<?php
if(!defined('KTKADMININC') || !is_object($thisuser)) die('Adiaux amikoj!');
$rep=Format::htmlchars($rep);
?>
<div class="msg"><?= _('Change Password') ?></div>
<form action="profile.php" method="post">
  <input type="hidden" name="t" value="passwd">
  <input type="hidden" name="id" value="<?=$thisuser->getId()?>">
  <table width="100%" border="0" cellspacing=0 cellpadding=2>
    <tr>
        <td width="120"><?= _('Current Password:') ?></td>
        <td>
            <input type="password" name="password" AUTOCOMPLETE=OFF value="<?=$rep['password']?>">
            &nbsp;<font class="error">*&nbsp;<?=$errors['password']?></font></td>
    </tr>
    <tr>
        <td><?= _('New Password:') ?></td>
        <td>
            <input type="password" name="npassword" AUTOCOMPLETE=OFF value="<?=$rep['npassword']?>">
            &nbsp;<font class="error">*&nbsp;<?=$errors['npassword']?></font></td>
    </tr>
    <tr>
        <td><?= _('Confirm Password:') ?></td>
        <td>
            <input type="password" name="vpassword" AUTOCOMPLETE=OFF value="<?=$rep['vpassword']?>">
            &nbsp;<font class="error">*&nbsp;<?=$errors['vpassword']?></font></td>
    </tr>
    <tr><td >&nbsp;</td>
         <td><br/>
             <input class="button" type="submit" name="submit" value="<?= _('Submit') ?>">
             <input class="button" type="reset" name="reset" value="<?= _('Reset') ?>">
             <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="profile.php"'>
        </td>
    </tr>
  </table>
</form>
 
