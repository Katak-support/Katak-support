<?php
/*********************************************************************
    stdreplay.php

    Standard Replies handle.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

require('staff.inc.php');
if (!$thisuser->canManageStdr() && !$thisuser->isadmin())
    die(_('Access Denied'));

$page = '';
$answer = null; //clean start.
if (($id = $_REQUEST['id'] ? $_REQUEST['id'] : $_POST['id']) && is_numeric($id)) {
    $replyID = 0;
    $resp = db_query('SELECT * FROM ' . STD_REPLY_TABLE . ' WHERE stdreply_id=' . db_input($id));
    if ($resp && db_num_rows($resp))
        $answer = db_fetch_array($resp);
    else
        $errors['err'] = _('Unknown ID#') . $id; //Sucker...invalid id

        if (!$errors && $answer['stdreply_id'] == $id)
        $page = 'reply.inc.php';
}

if ($_POST):
    $errors = array();
    switch (strtolower($_POST['a'])):
        case 'update':
        case 'add':
            if (!$_POST['id'] && $_POST['a'] == 'update')
                $errors['err'] = _('Missing or invalid role ID');

            if (!$_POST['title'])
                $errors['title'] = _('Title/subject required');

            if (!$_POST['answer'])
                $errors['answer'] = _('Reply required');

            if (!$errors) {
                $sql = ' SET updated=NOW(),isenabled=' . db_input($_POST['isenabled']) .
                        ', dept_id=' . db_input($_POST['dept_id']) .
                        ', title=' . db_input(Format::striptags($_POST['title'])) .
                        ', answer=' . db_input(Format::striptags($_POST['answer']));
                if ($_POST['a'] == 'add') { //create
                    $res = db_query('INSERT INTO ' . STD_REPLY_TABLE . ' ' . $sql . ',created=NOW()');
                    if (!$res or !($replyID = db_insert_id()))
                        $errors['err'] = _('Unable to create the reply. Internal error'.$sql);
                    else
                        $msg='Standard reply created';
                }elseif ($_POST['a'] == 'update') { //update
                    $res = db_query('UPDATE ' . STD_REPLY_TABLE . ' ' . $sql . ' WHERE stdreply_id=' . db_input($_POST['id']));
                    if ($res && db_affected_rows()) {
                        $msg = _('Standard reply updated');
                        $answer = db_fetch_array(db_query('SELECT * FROM ' . STD_REPLY_TABLE . ' WHERE stdreply_id=' . db_input($id)));
                    }
                    else
                        $errors['err'] = _('Internal update error occured. Try again');
                }
                if ($errors['err'] && db_errno() == 1062)
                    $errors['title'] = _('Title already exists!');
            }else {
                $errors['err'] = $errors['err'] ? $errors['err'] : _('Error(s) occured. Try again');
            }
            break;
        case 'process':
            if (!$_POST['canned'] || !is_array($_POST['canned']))
                $errors['err'] = _('You must select at least one item');
            else {
                $msg = '';
                $ids = implode(',', $_POST['canned']);
                $selected = count($_POST['canned']);
                if (isset($_POST['enable'])) {
                    if (db_query('UPDATE ' . STD_REPLY_TABLE . ' SET isenabled=1,updated=NOW() WHERE isenabled=0 AND stdreply_id IN(' . $ids . ')'))
                        $msg = db_affected_rows() . " of  $selected selected replies enabled";
                }elseif (isset($_POST['disable'])) {
                    if (db_query('UPDATE ' . STD_REPLY_TABLE . ' SET isenabled=0, updated=NOW() WHERE isenabled=1 AND stdreply_id IN(' . $ids . ')'))
                        $msg = db_affected_rows() . " of  $selected selected replies disabled";
                }elseif (isset($_POST['delete'])) {
                    if (db_query('DELETE FROM ' . STD_REPLY_TABLE . ' WHERE stdreply_id IN(' . $ids . ')'))
                        $msg = db_affected_rows() . " of  $selected selected replies deleted";
                }

                if (!$msg)
                    $errors['err'] = _('Error occured. Try again');
            }
            break;
        default:
            $errors['err'] = _('Unknown action');
    endswitch;
endif;
//new reply??
if (!$page && $_REQUEST['a'] == 'add' && !$replyID)
    $page = 'reply.inc.php';

$inc = $page ? $page : 'stdreply.inc.php';

$nav->setTabActive('stdreply');
$nav->addSubMenu(array('desc' => _('STANDARD REPLIES'), 'href' => 'stdreply.php', 'iconclass' => 'stdreply'));
$nav->addSubMenu(array('desc' => _('NEW STANDARD REPLY'), 'href' => 'stdreply.php?a=add', 'iconclass' => 'newStdreply'));
require_once(STAFFINC_DIR . 'header.inc.php');
require_once(STAFFINC_DIR . $inc);
require_once(STAFFINC_DIR . 'footer.inc.php');
?>