<?php
/*********************************************************************
    index.php
    
    Support System landing page. Please customize it to fit your needs.
    
    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require('user.inc.php');

require(USERINC_DIR . 'header.inc.php');
?>

  <div id='landingpage'>
    <div id='title'><?= _('WELCOME TO THE KATAK-SUPPORT CENTER!') ?></div>
    <div id="subtitle">  
      <?= _('In order to better support you, we utilize a support ticket system. Every support request is assigned a unique ticket number which you can use to track the progress online. For your reference we provide complete history of all your support requests.') ?>
      <?= _('A valid email address is required to access the support system.') ?>
    </div>
  </div>

<?php require(USERINC_DIR . 'footer.inc.php'); ?>
