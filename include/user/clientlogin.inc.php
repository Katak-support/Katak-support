<?php
if(!defined('KTKUSERINC')) die('Adiaux amikoj!');

$e=Format::input($_POST['username']?$_POST['username']:$_GET['e']);

?>
<div>
    <?php if($errors['err']) { ?>
        <p id="errormessage"><?=$errors['err']?></p>
    <?php }elseif($warn) { ?>
        <p class="warnmessage"><?=$warn?></p>
    <?php }?>
</div>
<div style="margin:5px 0px 100px 0; width:100%;">
    <p>
        <?=_("To insert or view the status of a ticket, provide us with your login data below.<br/>If you don't have username and password, please ask at the customer service.")?>
    </p>
    <div>
        <span class="label"></span>
        <span class="error"><?=Format::htmlchars($loginmsg)?></span>
    </div>
    <form action="tickets.php" method="post">
      <div class="input">
          <span class="label"><?=_('User name:') ?></span>
          <span><input type="text" name="username" size="25" value="<?=$e?>"></span>
      </div>
      <div class="input">
          <span class="label"><?=_('Password') ?>: </span>
          <span><input type="password" name="passwd" size="25"></span>
      </div>
      <div>
          <span class="label"></span>
          <span><input class="button" type="submit" value="<?=_('GO') ?>"></span>
      </div>
    </form>
</div>

