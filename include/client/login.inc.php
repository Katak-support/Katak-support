<?php
if(!defined('KTKCLIENTINC')) die('Adiaux amikoj!');

$e=Format::input($_POST['lemail']?$_POST['lemail']:$_GET['e']);
$t=Format::input($_POST['lticket']?$_POST['lticket']:$_GET['t']);
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
        <?=_("To view the status of a ticket, provide us with your login data below.<br/>If this is your first time contacting us or you've lost the ticket ID, please <a href='open.php'>click here</a> to open a new ticket.")?>
    </p>
    <div>
        <span class="label"></span>
        <span class="error"><?=Format::htmlchars($loginmsg)?></span>
    </div>
    <form action="tickets.php" method="post">
      <div class="input">
          <span class="label"><?=_('E-Mail:') ?></span>
          <span><input type="text" name="lemail" size="25" value="<?=$e?>"></span>
      </div>
      <div class="input">
          <span class="label"><?=_('Ticket ID:') ?></span>
          <span><input type="text" name="lticket" size="12" value="<?=$t?>"></span>
      </div>
      <div>
          <span class="label"></span>
          <span><input class="button" type="submit" value="<?=_('View Status') ?>"></span>
      </div>
    </form>
</div>

