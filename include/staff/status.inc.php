<?php
if(!defined('KTKADMININC') || !@$thisuser->isStaff()) die(_('Access Denied'));

?>
<br/>
<div> Katak-support version: <?= THIS_VERSION ?></div>
<br/>
<div> Katak database version: <?= $cfg->getVersion() ?></div>
<br/>
<div> Last system update: <?= $cfg->getLastUpdate() ?></div>
<br/>
<div> Current PHP version:  <?= phpversion() ?></div>
<br/>
<div> MySQL server version: <?= mysqli_get_server_info($dblink) ?></div>
<br/>
<div> Free disk space: 
<?php
    $bytes = disk_free_space(".");
    $si_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'EB', 'ZB', 'YB' );
    $base = 1024;
    $class = min((int)log($bytes , $base) , count($si_prefix) - 1);
    echo sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $si_prefix[$class] . '<br />';
?>
</div>