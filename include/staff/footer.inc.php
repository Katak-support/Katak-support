    </div> <!-- Content -->
    <?php if(is_object($thisuser) && $thisuser->isStaff()) { ?>
  <div>
      <!-- Do not remove <img src="autocron.php" alt="" width="1" height="1" border="0" /> or your auto cron will cease to function -->
      <img src="autocron.php" alt="" width="1" height="1" border="0" />
      <!-- Do not remove <img src="autocron.php" alt="" width="1" height="1" border="0" /> or your auto cron will cease to function -->
  </div>
<?php } ?>
<div id="footer"><?= sprintf(_('Copyright &copy; 2011-%s &nbsp;Katak-support.com. &nbsp;All Rights Reserved.'), date('Y')) ?></div>
</div> <!-- Container -->
</body>
</html>