<?php
if(!defined('KTKADMININC') || !is_object($thisuser)) die('Adiaux amikoj!');

?>
<div class="msg"><?= _('My Profile Info') ?></div>
<form action="profile.php" method="post">
  <input type="hidden" name="t" value="info">
  <input type="hidden" name="id" value="<?=$thisuser->getId()?>">
  <table width="100%" border="0" cellspacing=0 cellpadding=2>
    <tr>
        <th width="110"><?= _('Username:') ?></th>
        <td>&nbsp;<?=$thisuser->getUserName()?></td>
    </tr>
    <tr>
        <td width="110"><?= _('Department:') ?></td>
        <td>&nbsp;<?=$thisuser->getDeptName()?></td>
    </tr>
    <tr>
        <td><?= _('First Name:') ?></td>
        <td><input type="text" name="firstname" size=30 value="<?=$rep['firstname']?>">
            &nbsp;<font class="error">*&nbsp;<?=$errors['firstname']?></font></td>
    </tr>
    <tr>
        <td><?= _('Last Name:') ?></td>
        <td><input type="text" name="lastname" size=30 value="<?=$rep['lastname']?>">
            &nbsp;<font class="error">*&nbsp;<?=$errors['lastname']?></font></td>
    </tr>
    <tr>
        <td><?= _('Email Address:') ?></td>
        <td><input type="text" name="email" size=30 value="<?=$rep['email']?>">
            &nbsp;<font class="error">*&nbsp;<?=$errors['email']?></font></td>
    </tr>
    <tr>
        <td><?= _('Office Phone:') ?></td>
        <td>
            <input type="text" name="phone" size=30 value="<?=$rep['phone']?>" ><font class="error">&nbsp;<?=$errors['phone']?></font>&nbsp;
            <font class="error">&nbsp;<?=$errors['phone']?></font>
        </td>
    </tr>
    <tr>
        <td><?= _('Cell Phone:') ?></td>
        <td><input type="text" name="mobile" size=30 value="<?=$rep['mobile']?>" >
            &nbsp;<font class="error">&nbsp;<?=$errors['mobile']?></font></td>
    </tr>
    <tr>
        <td><?= _('Signature:') ?></td>
        <td><textarea name="signature" cols="21" rows="5" style="width: 60%;"><?=$rep['signature']?></textarea></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td><br/>
            <input class="button" type="submit" name="submit" value="<?= _('Save') ?>">
            <input class="button" type="reset" name="reset" value="<?= _('Reset') ?>">
            <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="index.php"'>
        </td>
    </tr>
  </table>
</form>
 
