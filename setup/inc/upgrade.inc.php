<?php
if(!defined('SETUPINC')) die('Adiaux amikoj!');

?>
<h1>Getting ready</h1>
<p>We recommend that you take the system offline during the upgrade process, if you haven't done so already. While we try to ensure that the upgrade process is straightforward and painless, we can't guarantee it will be the case for every user. 
<br /><b>Did you remember to do a full backup of the old site and database before proceeding?</b></p>
<p>
If the thought of upgrading your installation gives you the shake then feel free to <a href="http://katak-support.com/contact/" target="_blank">contact us </a> for help.
</p>

<form action="upgrade.php" method="post" name="setup" id="setup">
<?php if(!$adminloggedin) {?>
	<table width="100%" cellspacing="0" cellpadding="2" class="setup">
	    <tr class="title"><td colspan=2>System Administrator</td></tr>
	    <tr class="subtitle"><td colspan=2>Only the system administrator can upgrade the system.</td></tr>
	    <tr>
	        <td style="text-align:right">Username: </td>
	        <td><input type="text" name="username" size=20 value="<?php echo $setup['username']?>"></td>
	    </tr>
	    <tr>
	    		<td style="text-align:right">Password:</td>
	        <td><input type="password" name="password" size=20 value="<?php echo $setup['password']?>">
	    </tr>
	</table>
<?php } else {
	echo '<input type="hidden" name="adminloggedin" value=1>';
	echo 'You are logged-in as '.$adminloggedin.'<br>';
} ?>
<p>Please click Upgrade to complete the upgrade process ... be patient it might take a couple of seconds.</p>
<div align="center">
    <input class="button" type="submit" value="Upgrade">
</div>
</form>
