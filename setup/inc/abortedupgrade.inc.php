<h1>Upgrade Aborted</h1>
<p>Upgrade aborted due to errors. Please note the error(s) below and <a href="http://katak-support.com/contact.php" target="_blank">contact us</a> for help.</p>
<ul class="error">   
<?php
foreach($errors as $k=>$error) {
    if($k!='err') echo sprintf('<li>%s</li>',$error);    
}?>
</ul>
<br /><br /><br />