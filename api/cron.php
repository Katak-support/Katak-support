<?php
/*********************************************************************
    cron.php

    File to handle cron job calls (local and remote).

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
@chdir(realpath(dirname(__FILE__)).'/'); //Change dir.
require('api.inc.php');
require_once(INCLUDE_DIR.'class.cron.php');
Cron::run();
Sys::log(LOG_DEBUG,'Cron Job','External cron job executed ['.$_SERVER['REMOTE_ADDR'].']');
?>
