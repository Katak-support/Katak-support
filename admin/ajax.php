<?php
/*********************************************************************
    ajax.php

    Ajax utils interface.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require('staff.inc.php');

//Clean house...don't let the world see your crap.
ini_set('display_errors', '0'); //Disable error display
ini_set('display_startup_errors', '0');

//TODO: disable direct access via the browser? i,e All request must have REFER? 

if (!defined('INCLUDE_DIR'))
    Http::response(500, _('config error'));

if (!$thisuser || !$thisuser->isValid()) {
    Http::response(401, sprintf(_('Access Denied. IP %s'), $_SERVER['REMOTE_ADDR']));
    exit;
}

//---------check required global vars --------//
if (!$_REQUEST['api'] || !$_REQUEST['f']) {
    Http::response(416, _('Invalid params'));
    exit;
}
//------Do the AJAX Dance ----------------//
define('OSTAJAXINC', TRUE);
$file = 'ajax.' . Format::file_name(strtolower($_REQUEST['api'])) . '.php';
if (!file_exists(INCLUDE_DIR . $file)) {
    Http::response(405, _('invalid method'));
    exit;
}

$class = ucfirst(strtolower($_REQUEST['api'])) . 'AjaxAPI';
$func = $_REQUEST['f'];

if (is_callable($func)) { //if the function is callable B4 we include the source file..play with the user...
    Http::response(500, sprintf(_('This is secure ajax assjax %s'), $_SERVER['REMOTE_ADDR']));
    exit;
}
require(INCLUDE_DIR . $file);

if (!is_callable(array($class, $func))) {
    Http::response(416, sprintf(_('invalid method/call %s'), Format::htmlchars($func)));
    exit;
}

$response = @call_user_func(array($class, $func), $_REQUEST);
Http::response(200, $response);
exit;
?>