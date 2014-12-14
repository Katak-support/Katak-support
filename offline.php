<?php
/*********************************************************************
    offline.php

    Offline page...modify to fit your needs.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require_once('user.inc.php');
if($cfg && !$cfg->isHelpDeskOffline()) { 
    @header('Location: index.php'); //Redirect if the system is online.
    include('index.php');
    exit;
}

require(USERINC_DIR . 'header.inc.php'); 
?>

<h2><?= _('Support Ticket System Offline') ?></h2>
<p>
  <?= _('Thank you for your interest in contacting us.') ?><br />
  <?= _('Our Support System is offline at the moment, please check back at a later time.') ?>
</p>

<?php require(USERINC_DIR . 'footer.inc.php'); ?>
