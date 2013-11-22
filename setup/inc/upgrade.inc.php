<?php
if(!defined('SETUPINC')) die('Adiaux amikoj!');

$info=($errors && $_POST)?Format::input($_POST):array(); //use post data.
   
if(!isset($info['title'])) {
    $info['title']='KataK Support - Ticket System';
}


?>
<h1>Getting ready</h1>
<p>We recommend that you take the system offline during the upgrade process, if you haven't done so already. While we try to ensure that the upgrade process is straightforward and painless, we can't guarantee it will be the case for every user. 
<br /><b>Did you remember to backup the database?</b></p>
<p>
If the thought of upgrading your installation gives you the shake then feel free to <a href="http://katak-support.com/contact/" target="_blank">contact us </a> for help.
</p>
<p>Please click continue to complete the upgrade process ... be patient it might take a couple of seconds.</p>
<div style="padding:20px 20px 10px 200px;">
<form method=post action='upgrade.php'>
    <input type=hidden name=step value=1 />
    <input class="button" type="submit" name=submit value="Continue &raquo;&raquo;" />
</form>
</div>
