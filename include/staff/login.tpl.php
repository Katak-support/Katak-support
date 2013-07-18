<?php defined('KTKADMININC') or die(_('Invalid path')); ?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <title>Katak-support:: Admin login</title>
  <link rel="stylesheet" href="css/login.css" type="text/css" />
  <meta name="robots" content="noindex" />
  <meta http-equiv="cache-control" content="no-cache" />
  <meta http-equiv="pragma" content="no-cache" />
</head>
<body>
<div id="loginBox">
  <h1 id="title"><?= _('Katak-support Staff Control Panel') ?></h1>
  <h1 id="logo"><a href="index.php">&nbsp;</a></h1>
	<h1><?=$msg?></h1>
	<br />
	<form action="login.php" method="post">
  	<input type="hidden" name=do value="adminlogin" />
    <span class="input"><?= _('Username') ?>: </span><span><input type="text" name="username" id="name" value="" /></span>
    <span class="input"><?= _('Password') ?>: </span><span><input type="password" name="passwd" id="pass" /></span>
    <div><input class="submit" type="submit" name="submit" value="<?= _('Login') ?>" /></div>
  </form>
</div>
<div id="copyRights">Copyright &copy; <a href="http://www.katak-support.com" target="_blank">Katak-support.com</a></div>
</body>
</html>
