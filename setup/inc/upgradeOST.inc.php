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
    $info['prefix']='ost_';
}


?>
&nbsp;All fields are required.
<form action="upgradeOST.php" method="post" name="setup" id="setup">
<table width="100%" cellspacing="0" cellpadding="2" class="setup">
    <tr class="title"><td colspan=2>Admin user</td></tr>
    <tr class="subtitle"><td colspan=2>Autentication data administrator of the old osTicket system.</td></tr>
    <tr>
        <td colspan=2>
         <table border=0 cellspacing=0 cellpadding=2 class="clean">
            <tr><td width=150>Username:</td>
                <td><input type=text name=username size=20 value="<?php echo $info['username']?>">
                    &nbsp;<span class="error"><?=$errors['username']?></span></td></tr>
            <tr><td>Password:</td>
                <td><input type=password name=password size=20 value="<?php echo $info['password']?>">
                    &nbsp;<span class="error"><?=$errors['password']?></span></td></tr>
         </table>
        </td>
    </tr>
    <tr class="title"><td colspan=2>Database</td></tr>
    <tr class="subtitle"><td colspan=2>Database connection data of the old osTicket system.</td></tr>
    <tr>
        <td colspan=2><span class="error"><b><?=$errors['mysql']?></b></span>
         <table cellspacing=1 cellpadding=2 border=0>
            <tr><td width=150>MySQL Table Prefix:</td><td><input type=text name=prefix size=20 value="<?php echo $info['prefix']?>" >
                    <span class="error"><?=$errors['prefix']?></span></td></tr>
            <tr><td>MySQL Hostname:</td><td><input type=text name=dbhost size=20 value="<?php echo $info['dbhost']?>" >
                    <span class="error"><?=$errors['dbhost']?></span></td></tr>
            <tr><td>MySQL Database:</td><td><input type=text name=dbname size=20 value="<?php echo $info['dbname']?>">
                    <span class="error"><?=$errors['dbname']?></span></td></tr>
            <tr><td>MySQL Username:</td><td><input type=text name=dbuser size=20 value="<?php echo $info['dbuser']?>">
                    <span class="error"><?=$errors['dbuser']?></span></td></tr>
            <tr><td>MySQL Password:</td><td><input type=password name=dbpass size=20 value="<?php echo $info['dbpass']?>">
                    <span class="error"><?=$errors['dbpass']?></span></td></tr>
         </table>
        </td>
    </tr>
</table>
&nbsp;Please read the upgrade instructions and remember to do a full backup of the old site and database before proceeding!<br /><br />
<div align="center">
    <input class="button" type="submit" value="Install">
    <input class="button" type="reset" name="reset" value="Reset">
</div>
</form>