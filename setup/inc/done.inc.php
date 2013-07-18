<h1>Important!</h1>
<div>Your new support ticket system is installed. Your next step is to fully configure your new support ticket system for use, but before you get to it please take a minute to cleanup.
<ul>
    <li><strong>Change permission of ktk-config.php to remove write access</strong><br>
        chmod 644 include/ktk-config.php</li>
    <li><strong>Delete setup directory</strong><br>
     After verifying that your installation completed correctly please delete setup folder.</li>
    <?php
     if(ini_get('register_globals')) { ?>
     <li><strong>Disable register globals (optional)</strong><br>
     If you don't have a good reason why register globals is enabled then please disable it.
     </li> 
    <?php
     }

     if(!function_exists('mcrypt_encrypt') || !function_exists('mcrypt_decrypt')) {?>
     <li><strong>Install/Enable cryptography extension mcrypt (optional)</strong><br>
       Cryptography extension mcrypt is not enabled or installed. IMAP/POP passwords will be stored as plain text in database.
     </li>
     <?php
     }?>
</ul>
</div>
<h3>Post-Install Setup</h3>
<p>You can now log in to <a href="../admin/admin.php">Admin Panel</a> with the username and password you created during the install process. After a successful log in, you can proceed with post-install setup. For complete and up to date guide see <a href="http://www.katak-support.com/en/content/installation-guide" target="_blank">Katak-support Documentation</a></p>
<p>Please note that the system is set to offline by default - as admin you can still login to the admin panel.</p>

<h3>Commercial Support Available</h3>
<p>Don't let technical problems impact your customer support experience. Get guaranteed response times and access to the core development team.
We can also customize the system to meet your unique needs. For more information or to discuss your needs, please <a href="http://katak-support.com/contact/" target="_blank">contact us</a>.</p>
<br/>
<div align="center"><b>
    <a href="../admin/admin.php">Admin Panel</a>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <a href="http://www.katak-support.com/en/content/installation-guide" target="_blank">Documentation</a>
    </b>
</div>
<br/>
<p>Thank you for choosing Katak-support and best of luck! The Katak-support Team</p>
