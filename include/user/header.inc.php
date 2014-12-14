<?php
$title=($cfg && is_object($cfg))?$cfg->getTitle():_('KataK Support - Ticket System');
header("Content-Type: text/html; charset=UTF-8\r\n");
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8">
  <title><?=Format::htmlchars($title)?></title>
  
  <link rel="stylesheet" href="./styles/user.css" media="screen, print">
  <link rel="stylesheet" href="./styles/print.css" media="print">
  <link rel="stylesheet" href="./styles/colors.css" media="screen, print">
  
  <script src="./js/multifile.js" type="text/javascript"></script>
</head>
<body>
<div id="container">
  <div id="left-top-block">
      <a id="logo" href="index.php" title="<?=_('Katak Support Center')?>"><img src="./images/logo.jpg" alt="<?=_('KataK Support Center')?>"></a>
      <?php                    
      if($thisuser && is_object($thisuser) && $thisuser->isValid()) {?>
        <span id="info"><?= _('Logged in as') ?>: <b><i><?=$thisuser->getUserName()?></i></b></span>
      <?php } ?>
  </div>
  <div id="right-top-block">
       <?php                    
       if($thisuser && is_object($thisuser) && $thisuser->isValid()) {?>
         <a class="log_out" href="logout.php"><span><?=_('Log Out')?></span></a>
         <a class="my_tickets" href="tickets.php"><span><?=_('My Tickets')?></span></a>
         <a class="new_ticket" href="open.php"><span><?=_('New Ticket')?></span></a>
       <?php } elseif(!$cfg->getUserLogRequired()) { ?>
         <a class="ticket_status" href="tickets.php"><span><?=_('Ticket Status')?></span></a>
         <a class="new_ticket" href="open.php"><span><?=_('New Ticket')?></span></a>
       <?php } else { ?>
         <a class="user_login" href="tickets.php"><span><?=_('Log-in')?></span></a>
       <?php } ?>
       <a class="home" href="index.php"><span><?=_('Home')?></span></a>
  </div>
  <div id="content">
