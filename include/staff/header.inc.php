<?php if(!defined('KTKADMININC') || !is_object($thisuser) || !$thisuser->isStaff() || !is_object($nav)) die(_('Access Denied')); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd"> 
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  <?php
  if(defined('AUTO_REFRESH') && is_numeric(AUTO_REFRESH_RATE) && AUTO_REFRESH_RATE>0){ //Refresh rate
  echo '<meta http-equiv="refresh" content="'.AUTO_REFRESH_RATE.'" />';
  }
  ?>
  <title>Katak-support :: Staff Control Panel</title>
  <link rel="stylesheet" href="css/main.css" media="screen, print">
  <link rel="stylesheet" href="css/style.css" media="screen, print">
  <link rel="stylesheet" href="css/print.css" media="print">
  <link rel="stylesheet" href="css/tabs.css" type="text/css">
  <link rel="stylesheet" href="css/autosuggest_inquisitor.css" type="text/css" media="screen" charset="utf-8">
  <script type="text/javascript" src="js/ajax.js"></script>
  <script type="text/javascript" src="js/admin.js"></script>
  <script type="text/javascript" src="js/tabber.js"></script>
  <script type="text/javascript" src="js/calendar.js"></script>
  <script type="text/javascript" src="js/bsn.AutoSuggest_2.1.3.js" charset="utf-8"></script>
  <?php
  if($cfg && $cfg->getLockTime()) { //autoLocking enabled.?>
    <script type="text/javascript" src="js/autolock.js" charset="utf-8"></script>
  <?php } ?>
</head>
<body>
<?php
if($sysnotice){?>
  <div id="system_notice"><?php echo $sysnotice; ?></div>
<?php 
}?>
<div id="container">
    <div id="header">
        <a id="logo" href="index.php" title="Katak-support"><img src="images/kataklogo.gif" width="188" height="72" alt="Katak-support"></a>
        <span id="info"><i><?= _('Logged in as') ?>: <?=$thisuser->getUsername()?></i>
           <?php
            if($thisuser->isAdmin()) {
              if(!defined('ADMINPAGE')) {?>
                | <a href="admin.php"><?= _('Admin Panel') ?></a>
            <?php }else{ ?>
              | <a href="index.php"><?= _('Staff Panel') ?></a>
            <?php }} ?>
              | <a href="logout.php"><?= _('Log Out') ?></a>
        </span>
    </div>
    <div id="nav">
        <ul id="main_nav" <?=!defined('ADMINPAGE')?'class="dist"':''?>>
            <?php
            if(($tabs=$nav->getTabs()) && is_array($tabs)){
             foreach($tabs as $tab) { ?>
                <li><a <?=$tab['active']?'class="active"':''?> href="<?=$tab['href']?>" title="<?=$tab['title']?>"><?=$tab['desc']?></a></li>
            <?php }
            }else{ //?? ?>
                <li><a href="profile.php" title="<?= _('My Preference') ?>"><?= _('My Account') ?></a></li>
            <?php } ?>
        </ul>
        <ul id="sub_nav">
            <?php
            if(($subnav=$nav->getSubMenu()) && is_array($subnav)){
              foreach($subnav as $item) { ?>
                <li><a class="<?=$item['iconclass']?>" href="<?=$item['href']?>" title="<?=$item['title']?>"><?=$item['desc']?></a></li>
              <?php }
            }?>
        </ul>
    </div>
    <div class="clear"></div>
    <div id="content">
