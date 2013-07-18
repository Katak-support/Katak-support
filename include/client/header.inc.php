<?php
$title=($cfg && is_object($cfg))?$cfg->getTitle():_('KataK Support - Ticket System');
header("Content-Type: text/html; charset=UTF-8\r\n");
?>
<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title><?=Format::htmlchars($title)?></title>

    <link rel="stylesheet" href="./styles/client.css" media="screen">
    <link rel="stylesheet" href="./styles/colors.css" media="screen">

  	<script src="./js/multifile.js" type="text/javascript"></script>
</head>
<body>
<div id="container">
    <div id="left-top-block">
        <a id="logo" href="index.php" title="<?=_('Katak Support Center')?>"><img src="./images/logo2.jpg" alt="<?=_('KataK Support Center')?>"></a>
        <?php                    
        if($thisclient && is_object($thisclient) && $thisclient->isValid()) {?>
            <span id="info"><?= _('Logged in as') ?>: <i><?=$thisclient->getUserName()?></i></span>
        <?php } ?>
    </div>
    <div id="right-top-block">
         <?php                    
         if($thisclient && is_object($thisclient) && $thisclient->isValid()) {?>
           <a class="log_out" href="logout.php"><span><?=_('Log Out')?></span></a>
           <a class="my_tickets" href="tickets.php"><span><?=_('My Tickets')?></span></a>
         <?php }else { ?>
           <a class="ticket_status" href="tickets.php"><span><?=_('Ticket Status')?></span></a>
         <?php } ?>
         <a class="new_ticket" href="open.php"><span><?=_('New Ticket')?></span></a>
         <a class="home" href="index.php"><span><?=_('Home')?></span></a>
    </div>
    <div id="content">
