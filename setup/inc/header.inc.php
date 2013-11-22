<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<title>Katak-support Installer</title>
<link rel="stylesheet" href="style.css" media="all">
</head>
<body>
<div id="container">
    <div id="header">
        <a id="logo" href="#" title="Katak-support"><img src="images/kataklogo.gif" width="188" height="72" alt="Katak-support Installer"></a>
        <div id="info"><?php echo $info?></div>
    </div>
    <div id="nav">
        <ul id="main_nav">
            <li><a <?php echo $performing=='install'?'class="active"':''?> href="install.php">Install</a></li>
            <?php // Upgrade only if the system is already installed!
            if(file_exists(CONFIGFILE) && ($cFile=file_get_contents(CONFIGFILE)) && preg_match("/define\('KTSINSTALLED',TRUE\)\;/i",$cFile)){ ?>
              <li><a <?php echo $performing=='upgrade'?'class="active"':''?> href="upgrade.php">Upgrade</a></li>
            <?php } ?>
            <li><a <?php echo $performing=='upgradeOST'?'class="active"':''?> href="upgradeOST.php">Upgrade from OST</a></li>
        </ul>
        <ul id="sub_nav">
            <li><?php echo $title?></li>
        </ul>
    </div>
    <div class="clear"></div>
    <div id="content">
       <div>
            <?php if($errors['err']) { ?>
                <p align="center" id="errormessage"><?php echo $errors['err']?></p>
            <?php }elseif($msg) { ?>
                <p align="center" id="infomessage"><?php echo $msg?></p>
            <?php }elseif($warn) { ?>
                <p align="center" id="warnmessage"><?php echo $warn?></p>
            <?php } ?>
        </div>    