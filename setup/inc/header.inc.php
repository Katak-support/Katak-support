<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Katak-support Installer</title>
<link rel="stylesheet" href="style.css" media="screen">
</head>
<body>
<div id="container">
    <div id="header">
        <a id="logo" href="#" title="Katak-support"><img src="images/kataklogo.gif" width="188" height="72" alt="Katak-support Installer"></a>
        <div id="info"><?=$info?></div>
    </div>
    <div id="nav">
        <ul id="main_nav">
            <li><a <?=$performing=='install'?'class="active"':''?> href="install.php">Install</a></li>
            <li><a <?=$performing=='upgrade'?'class="active"':''?> href="upgrade.php">Upgrade</a></li>
        </ul>
        <ul id="sub_nav">
            <li><?=$title?></li>
        </ul>
    </div>
    <div class="clear"></div>
    <div id="content">
       <div>
            <?php if($errors['err']) { ?>
                <p align="center" id="errormessage"><?=$errors['err']?></p>
            <?php }elseif($msg) { ?>
                <p align="center" id="infomessage"><?=$msg?></p>
            <?php }elseif($warn) { ?>
                <p align="center" id="warnmessage"><?=$warn?></p>
            <?php } ?>
        </div>    