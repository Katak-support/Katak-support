<?php
if(!defined('KTKADMININC') || !@$thisuser->isStaff()) die(_('Access Denied'));

?>
<br/>
<div class="msg">Katak-support version: <?= THIS_VERSION ?></div>
<br/>
<div class="msg">Katak database version: <?= $cfg->getVersion() ?></div>
<br/>
<div class="msg">Last system update: <?= $cfg->getLastUpdate() ?></div>
