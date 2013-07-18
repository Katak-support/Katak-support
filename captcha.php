<?php
/*********************************************************************
    captcha.php

    Simply returns captcha image.
    
    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require_once('main.inc.php');
require(INCLUDE_DIR.'class.captcha.php');

$captcha = new Captcha(5,12,ROOT_DIR.'images/captcha/');
echo $captcha->getImage();
?>
