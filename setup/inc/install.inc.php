<?php
if(!defined('SETUPINC')) die('Adiaux amikoj!');

$info=($errors && $_POST)?Format::input($_POST):array(); //use post data.
   
if(!isset($info['title'])) {
    $info['title']='KataK Support - Ticket System';
}
if(!isset($info['dbhost'])) {
    $info['dbhost']='localhost';
}
if(!isset($info['prefix'])) {
    $info['prefix']='ktk_';
}


?>
&nbsp;All fields are required.
<form action="install.php" method="post" name="setup" id="setup">
<table width="100%" cellspacing="0" cellpadding="2" class="setup">
    <tr class="title"><td colspan=2>Katak-support general information</td></tr>
    <tr class="subtitle"><td colspan=2>Url to Katak-support installation on your server, title and default language. You can change everything later.</td></tr>
    <tr><td width=150>Support System URL:</td><td><b><?=URL?></b></td></tr>
    <tr>
        <td>Support System Title:</td><td><input type=text name=title size=40 value="<?=$info['title']?>">
            &nbsp;<span class="error"><?=$errors['title']?></span></td>
    </tr>
    <tr><td><?= _('System Language:') ?></td><td>
            <select name="language">
            <?php foreach (i18n::getLanguages() as $lang) { ?>
                <option value="<?=$lang->name ?>"><?=$lang->description ?></option>
            <?php } ?>
            </select>
            &nbsp;<span class="error"><?=$errors['language']?></span></td>
    </tr>
    <tr class="title"><td colspan=2>System email</td></tr>
    <tr class="subtitle"><td colspan=2>Default system email (e.g support@yourdomain.com). You can change or add more emails later.</td></tr>
    <tr><td>Default Email:</td><td><input type=text name=sysemail size=40 value="<?=$info['sysemail']?>">
            &nbsp;<span class="error"><?=$errors['sysemail']?></span></td>
    </tr>
    <tr class="title"><td colspan=2>System Administrator</td></tr>
    <tr class="subtitle"><td colspan=2>Min. of six chars for the password. You can change (but not the email) and add more users later.</td></tr>
    <tr>
        <td colspan=2>
         <table border=0 cellspacing=0 cellpadding=2 class="clean">
            <tr><td width=150>Username:</td>
                <td><input type=text name=username size=20 value="<?=$info['username']?>">
                    &nbsp;<span class="error"><?=$errors['username']?></span></td></tr>
            <tr><td>Password:</td>
                <td><input type=password name=password size=20 value="<?=$info['password']?>">
                    &nbsp;<span class="error"><?=$errors['password']?></span></td></tr>
            <tr><td>Password (again):</td>
                <td><input type=password name=password2 size=20 value="<?=$info['password2']?>">
                    &nbsp;<span class="error"><?=$errors['password2']?></span></td>
            </tr>
            <tr><td>Email:</td><td><input type=text name=email size=40 value="<?=$info['email']?>">
                    &nbsp;<span class="error"><?=$errors['email']?></span></td></tr>
         </table>
        </td>
    </tr>
    <tr class="title"><td colspan=2>Database</td></tr>
    <tr class="subtitle"><td colspan=2>MySQL (version 4.4+) is the only database supported at the moment.</td></tr>
    <tr>
        <td colspan=2><span class="error"><b><?=$errors['mysql']?></b></span>
         <table cellspacing=1 cellpadding=2 border=0>
            <tr><td width=150>MySQL Table Prefix:</td><td><input type=text name=prefix size=20 value="<?=$info['prefix']?>" >
                    <span class="error"><?=$errors['prefix']?></span></td></tr>
            <tr><td>MySQL Hostname:</td><td><input type=text name=dbhost size=20 value="<?=$info['dbhost']?>" >
                    <span class="error"><?=$errors['dbhost']?></span></td></tr>
            <tr><td>MySQL Database:</td><td><input type=text name=dbname size=20 value="<?=$info['dbname']?>">
                    <span class="error"><?=$errors['dbname']?></span></td></tr>
            <tr><td>MySQL Username:</td><td><input type=text name=dbuser size=20 value="<?=$info['dbuser']?>">
                    <span class="error"><?=$errors['dbuser']?></span></td></tr>
            <tr><td>MySQL Password:</td><td><input type=password name=dbpass size=20 value="<?=$info['dbpass']?>">
                    <span class="error"><?=$errors['dbpass']?></span></td></tr>
         </table>
        </td>
    </tr>
</table>
<div align="center">
    <input class="button" type="submit" value="Install">
    <input class="button" type="reset" name="reset" value="Reset">
</div>
</form>