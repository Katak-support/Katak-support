<h1>Upgrade completed!</h1>
<P>Congratulations your new support ticket system is now upgraded. Please refer to Release Notes for more information about changes and/or new features.</p>
<h3>Post-upgrade setup</h3>
Please take a minute to cleanup!
<ul>
    <li><strong>Delete setup directory</strong><br>
     After verifying that the upgrade completed correctly please delete setup folder.</li>
    <?if(ini_get('register_globals')) { ?>
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
    <li><strong>Enable the helpdesk</strong><br>
     As admin you can enable it in <a href="../admin/admin.php">Admin Panel</a> - preference section.
</ul>
<h3>Commercial Support Available</h3>
<p>Don't let technical problems impact your customer support experience. Get guaranteed response times and access to the core development team.
We can also customize the system to meet your unique needs. For more information or to discuss your needs, please <a href="http://katak-support.com/contact/" target="_blank">contact us today</a></p>

<p>Thank you for choosing KataK and best of luck.<br><br>Katak-support Team.</p>
