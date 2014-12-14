<?php
/*********************************************************************
    admin.php

    Handles all admin related pages....everything admin!

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
require('staff.inc.php');
/**
 * Make sure the user is admin type!
 */
if (!$thisuser or !$thisuser->isadmin()) {
    header('Location: index.php');
    require('index.php'); // just in case!
    exit;
}


//Some security related warnings
if (defined('THIS_VERSION') && strcasecmp($cfg->getVersion(), substr(THIS_VERSION, 0, strripos((THIS_VERSION),'.')))) {
    $sysnotice = sprintf(_('The script is version %s while the database is version %s. '), substr(THIS_VERSION, 0, strripos((THIS_VERSION),'.')), $cfg->getVersion());
    if (file_exists('../setup/'))
        $sysnotice.=_('Possibly caused by incomplete upgrade.');
    $errors['err'] = $sysnotice;
}elseif (!$cfg->isHelpDeskOffline()) {

    if (file_exists('../setup/')) {
        $sysnotice = _('Please take a minute to delete <strong>setup/install</strong> directory for security reasons.');
    } else {

        if (CONFIG_FILE && file_exists(CONFIG_FILE) && is_writable(CONFIG_FILE)) {
            //Confirm for real that the file is writable by role or world.
            clearstatcache(); //clear the cache!
            $perms = @fileperms(CONFIG_FILE);
            if (($perms & 0x0002) || ($perms & 0x0010)) {
                $sysnotice = sprintf(_('Please change permission of config file (%s) to remove write access. e.g <i>chmod 644 %s</i>'),
                                basename(CONFIG_FILE), basename(CONFIG_FILE));
            }
        }
    }
    if (!$sysnotice && ini_get('register_globals'))
        $sysnotice = _('Please consider turning off register globals if possible');
}

//Access checked out OK...lets do the do 
define('KTKADMININC', TRUE); //checked by admin include files
define('ADMINPAGE', TRUE);   //Used by the header to swap menus.
//Files we might need.
//TODO: Do on-demand require...save some mem.
require_once(INCLUDE_DIR . 'class.ticket.php');
require_once(INCLUDE_DIR . 'class.dept.php');
require_once(INCLUDE_DIR . 'class.email.php');
require_once(INCLUDE_DIR . 'class.mailfetch.php');

//Handle a POST.
if ($_POST && $_REQUEST['t'] && !$errors):
    //print_r($_POST);

    $errors = array(); //do it anyways.

    switch (strtolower($_REQUEST['t'])):
        case 'pref':  //set general preferences
            if ($cfg->updateGeneralPref($_POST, $errors)) {
                $msg = _('Preferences Updated Successfully');
                $cfg->reload();
            } else {
                $errors['err'] = $errors['err'] ? $errors['err'] : _('Internal error');
            }
            break;
        case 'attach':  //set mail and attachment preferences
            if ($cfg->updateMailPref($_POST, $errors)) {
                $msg = _('Attachments settings updated');
                $cfg->reload();
            } else {
                $errors['err'] = $errors['err'] ? $errors['err'] : _('Internal Error');
            }
            break;
        case 'api':
            include_once(INCLUDE_DIR . 'class.api.php');
            switch (strtolower($_POST['do'])) {
                case 'add':
                    if (Api::add(trim($_POST['ip']), $errors))
                        $msg = sprintf(_('Key created successfully for %s'), Format::htmlchars($_POST['ip']));
                    elseif (!$errors['err'])
                        $errors['err'] = _('Error adding the IP. Try again');
                    break;
                case 'update_phrase':
                    if (Api::setPassphrase(trim($_POST['phrase']), $errors))
                        $msg = _('API passphrase updated successfully');
                    elseif (!$errors['err'])
                        $errors['err'] = _('Error updating passphrase. Try again');
                    break;
                case 'mass_process':
                    if (!$_POST['ids'] || !is_array($_POST['ids'])) {
                        $errors['err'] = _('You must select at least one entry to process');
                    } else {
                        $count = count($_POST['ids']);
                        $ids = implode(',', $_POST['ids']);
                        if ($_POST['enable'] || $_POST['disable']) {
                            $resp = db_query('UPDATE ' . API_KEY_TABLE . ' SET isactive=' . db_input($_POST['enable'] ? 1 : 0) . ' WHERE id IN (' . $ids . ')');

                            if ($resp && ($i = db_affected_rows())) {
                                $msg = sprintf(_("%s of %s selected key(s) updated"), $i, $count);
                            } else {
                                $errors['err'] = _('Unable to delete selected keys.');
                            }
                        } elseif ($_POST['delete']) {
                            $resp = db_query('DELETE FROM ' . API_KEY_TABLE . '  WHERE id IN (' . $ids . ')');
                            if ($resp && ($i = db_affected_rows())) {
                                $msg = sprintf(_("%s of %s selected key(s) deleted"), $i, $count);
                            } else {
                                $errors['err'] = _('Unable to delete selected key(s). Try again');
                            }
                        } else {
                            $errors['err'] = _('Unknown command');
                        }
                    }
                    break;
                default:
                    $errors['err'] = sprintf(_('Unknown action %s'), $_POST['do']);
            }
            break;
        case 'banlist': //BanList.
            require_once(INCLUDE_DIR . 'class.banlist.php');
            switch (strtolower($_POST['a'])) {
                case 'add':
                    if (!$_POST['email'] || !Validator::is_email($_POST['email']))
                        $errors['err'] = _('Please enter a valid email.');
                    elseif (BanList::isbanned($_POST['email']))
                        $errors['err'] = _('Email already banned');
                    else {
                        if (BanList::add($_POST['email'], $thisuser->getName()))
                            $msg = _('Email added to banlist');
                        else
                            $errors['err'] = _('Unable to add email to banlist. Try again');
                    }
                    break;
                case 'remove':
                    if (!$_POST['ids'] || !is_array($_POST['ids'])) {
                        $errors['err'] = _('You must select at least one email');
                    } else {
                        //TODO: move mass remove to Banlist class when needed elsewhere...at the moment this is the only place.
                        $sql = 'DELETE FROM ' . BANLIST_TABLE . ' WHERE id IN (' . implode(',', $_POST['ids']) . ')';
                        if (db_query($sql) && ($num = db_affected_rows()))
                            $msg = sprintf(_("%s of %s selected emails removed from banlist"), $num, $count);
                        else
                            $errors['err'] = _('Unable to make remove selected emails. Try again.');
                    }
                    break;
                default:
                    $errors['err'] = _('Uknown banlist command!');
            }
            break;
        case 'email':
            require_once(INCLUDE_DIR . 'class.email.php');
            $do = strtolower($_POST['do']);
            switch ($do) {
                case 'update':
                    $email = new Email($_POST['email_id']);
                    if ($email && $email->getId()) {
                        if ($email->update($_POST, $errors))
                            $msg = _('Email updated successfully');
                        elseif (!$errors['err'])
                            $errors['err'] = _('Error updating email');
                    }else {
                        $errors['err'] = _('Internal error');
                    }
                    break;
                case 'create':
                    if (Email::create($_POST, $errors))
                        $msg = _('Email added successfully');
                    elseif (!$errors['err'])
                        $errors['err'] = _('Unable to add email. Internal error');
                    break;
                case 'mass_process':
                    if (!$_POST['ids'] || !is_array($_POST['ids'])) {
                        $errors['err'] = _('You must select at least one email to process');
                    } else {
                        $count = count($_POST['ids']);
                        $ids = implode(',', $_POST['ids']);
                        $sql = 'SELECT count(dept_id) FROM ' . DEPT_TABLE . ' WHERE email_id IN (' . $ids . ') OR autoresp_email_id IN (' . $ids . ')';
                        list($depts) = db_fetch_row(db_query($sql));
                        if ($depts > 0) {
                            $errors['err'] = _('One or more of the selected emails is being used by a Dept. Remove association first.');
                        } elseif ($_POST['delete']) {
                            $i = 0;
                            foreach ($_POST['ids'] as $k => $v) {
                                if (Email::deleteEmail($v))
                                    $i++;
                            }
                            if ($i > 0) {
                                $msg = sprintf(_("%s of %s selected email(s) deleted"), $i, $count);
                            } else {
                                $errors['err'] = _('Unable to delete selected email(s).');
                            }
                        } else {
                            $errors['err'] = _('Unknown command');
                        }
                    }
                    break;
                default:
                    $errors['err'] = _('Unknown topic action!');
            }
            break;
        case 'templates':
            include_once(INCLUDE_DIR . 'class.msgtpl.php');
            $do = strtolower($_POST['do']);
            switch ($do) {
                case 'add':
                case 'create':
                    if (($tid = Template::create($_POST, $errors))) {
                        $msg = _('Template created successfully');
                    } elseif (!$errors['err']) {
                        $errors['err'] = _('Error creating the template - try again');
                    }
                    break;
                case 'update':
                    $template = null;
                    if ($_POST['id'] && is_numeric($_POST['id'])) {
                        $template = new Template($_POST['id']);
                        if (!$template || !$template->getId()) {
                            $template = null;
                            $errors['err'] = _('Unknown template') . $id;
                        } elseif ($template->update($_POST, $errors)) {
                            $msg = _('Template updated successfully');
                        } elseif (!$errors['err']) {
                            $errors['err'] = _('Error updating the template. Try again');
                        }
                    }
                    break;
                case 'mass_process':
                    if (!$_POST['ids'] || !is_array($_POST['ids'])) {
                        $errors['err'] = _('You must select at least one template');
                    } elseif (in_array($cfg->getDefaultTemplateId(), $_POST['ids'])) {
                        $errors['err'] = _('You can not delete default template');
                    } else {
                        $count = count($_POST['ids']);
                        $ids = implode(',', $_POST['ids']);
                        $sql = 'SELECT count(dept_id) FROM ' . DEPT_TABLE . ' WHERE tpl_id IN (' . $ids . ')';
                        list($tpl) = db_fetch_row(db_query($sql));
                        if ($tpl > 0) {
                            $errors['err'] = _('One or more of the selected templates is being used by a Dept. Remove association first.');
                        } elseif ($_POST['delete']) {
                            $sql = 'DELETE FROM ' . EMAIL_TEMPLATE_TABLE . ' WHERE tpl_id IN (' . $ids . ') AND tpl_id!=' . db_input($cfg->getDefaultTemplateId());
                            if (($result = db_query($sql)) && ($i = db_affected_rows()))
                                $msg = sprintf(_("%s of %s selected templates(s) deleted"), $i, $count);
                            else
                                $errors['err'] = _('Unable to delete selected templates(s).');
                        }else {
                            $errors['err'] = _('Unknown command');
                        }
                    }
                    break;
                default:
                    $errors['err'] = _('Unknown action');
                //print_r($_POST);
            }
            break;
        case 'topics':
            require_once(INCLUDE_DIR . 'class.topic.php');
            $do = strtolower($_POST['do']);
            switch ($do) {
                case 'update':
                    $topic = new Topic($_POST['topic_id']);
                    if ($topic && $topic->getId()) {
                        if ($topic->update($_POST, $errors))
                            $msg = _('Topic updated successfully');
                        elseif (!$errors['err'])
                            $errors['err'] = _('Error updating the topic');
                    }else {
                        $errors['err'] = _('Internal error');
                    }
                    break;
                case 'create':
                    if (Topic::create($_POST, $errors))
                        $msg = _('Help topic created successfully');
                    elseif (!$errors['err'])
                        $errors['err'] = _('Unable to create the topic. Internal error');
                    break;
                case 'mass_process':
                    if (!$_POST['tids'] || !is_array($_POST['tids'])) {
                        $errors['err'] = _('You must select at least one topic');
                    } else {
                        $count = count($_POST['tids']);
                        $ids = implode(',', $_POST['tids']);
                        if ($_POST['enable']) {
                            $sql = 'UPDATE ' . TOPIC_TABLE . ' SET isactive=1, updated=NOW() WHERE topic_id IN (' . $ids . ') AND isactive=0 ';
                            if (db_query($sql) && ($num = db_affected_rows()))
                                $msg = sprintf(_("%s of %s selected services enabled"), $num, $count);
                            else
                                $errors['err'] = _('Unable to complete the action.');
                        }elseif ($_POST['disable']) {
                            $sql = 'UPDATE ' . TOPIC_TABLE . ' SET isactive=0, updated=NOW() WHERE topic_id IN (' . $ids . ') AND isactive=1 ';
                            if (db_query($sql) && ($num = db_affected_rows()))
                                $msg = sprintf(_("%s of %s selected topics disabled"), $num, $count);
                            else
                                $errors['err'] = _('Unable to disable selected topics');
                        }elseif ($_POST['delete']) {
                            $sql = 'DELETE FROM ' . TOPIC_TABLE . ' WHERE topic_id IN (' . $ids . ')';
                            if (db_query($sql) && ($num = db_affected_rows()))
                                $msg = sprintf(_("%s of %s selected topics deleted!"), $num, $count);
                            else
                                $errors['err'] = _('Unable to delete selected topics');
                        }
                    }
                    break;
                default:
                    $errors['err'] = _('Unknown topic action!');
            }
            break;
        case 'roles':
            include_once(INCLUDE_DIR . 'class.role.php');
            $do = strtolower($_POST['do']);
            switch ($do) {
                case 'update':
                    if (Role::update($_POST['role_id'], $_POST, $errors)) {
                        $msg = sprintf(_('Role %s updated successfully'), Format::htmlchars($_POST['role_name']));
                    } elseif (!$errors['err']) {
                        $errors['err'] = _('Error(s) occured. Try again.');
                    }
                    break;
                case 'create':
                    if (($gID = Role::create($_POST, $errors))) {
                        $msg = sprintf(_('Role %s created successfully'), Format::htmlchars($_POST['role_name']));
                    } elseif (!$errors['err']) {
                        $errors['err'] = _('Error(s) occured. Try again.');
                    }
                    break;
                default:
                    //ok..at this point..look WMA.
                    if ($_POST['grps'] && is_array($_POST['grps'])) {
                        $ids = implode(',', $_POST['grps']);
                        $selected = count($_POST['grps']);
                        if (isset($_POST['activate_grps'])) {
                            $sql = 'UPDATE ' . GROUP_TABLE . ' SET role_enabled=1,updated=NOW() WHERE role_enabled=0 AND role_id IN(' . $ids . ')';
                            db_query($sql);
                            $msg = sprintf(_("%s of  $selected selected roles Enabled"), db_affected_rows());
                        } elseif (in_array($thisuser->getDeptId(), $_POST['grps'])) {
                            $errors['err'] = "Trying to 'Disable' or 'Delete' your role? Doesn't make any sense!";
                        } elseif (isset($_POST['disable_grps'])) {
                            $sql = 'UPDATE ' . GROUP_TABLE . ' SET role_enabled=0, updated=NOW() WHERE role_enabled=1 AND role_id IN(' . $ids . ')';
                            db_query($sql);
                            $msg = sprintf(_("%s of  $selected selected roles Disabled"), db_affected_rows());
                        } elseif (isset($_POST['delete_grps'])) {
                            $res = db_query('SELECT staff_id FROM ' . STAFF_TABLE . ' WHERE role_id IN(' . $ids . ')');
                            if (!$res || db_num_rows($res)) { //fail if any of the selected roles has users.
                                $errors['err'] = _('One or more of the selected roles have users. Only empty roles can be deleted.');
                            } else {
                                db_query('DELETE FROM ' . GROUP_TABLE . ' WHERE role_id IN(' . $ids . ')');
                                $msg = sprintf(_("%s of %s selected roles Deleted"), db_affected_rows(), $selected);
                            }
                        } else {
                            $errors['err'] = _('Uknown command!');
                        }
                    } else {
                        $errors['err'] = _('No roles selected.');
                    }
            }
            break;
        case 'client':
            include_once(INCLUDE_DIR . 'class.client.php');
            $do = strtolower($_POST['do']);
            switch ($do) {
                case 'update':
                    $client = new Client($_POST['client_id']);
                    if ($client && $client->getId()) {
                        if ($client->update($_POST, $errors))
                            $msg = _('Client profile updated successfully');
                        elseif (!$errors['err'])
                            $errors['err'] = _('Error updating the user');
                    }else {
                        $errors['err'] = _('Internal error');
                    }
                    break;
                case 'create':
                    if (($uID = Client::create($_POST, $errors)))
                      $msg = sprintf(_('%s added successfully'), Format::htmlchars($_POST['client_firstname'].' '.$_POST['client_lastname']));
                    elseif (!$errors['err'])
                        $errors['err'] = _('Unable to add the user. Internal error');
                    break;
                case 'mass_process':
                    //ok..at this point..look WMA.
                    if ($_POST['uids'] && is_array($_POST['uids'])) {
                        $ids = implode(',', $_POST['uids']);
                        $selected = count($_POST['uids']);
                        if (isset($_POST['enable'])) {
                            $sql = 'UPDATE ' . CLIENT_TABLE . ' SET client_isactive=1 WHERE client_isactive=0 AND client_id IN(' . $ids . ')';
                            db_query($sql);
                            $msg = sprintf(_("%s of  %s selected users enabled"), db_affected_rows(), $selected);
                        } elseif (isset($_POST['disable'])) {
                            $sql = 'UPDATE ' . CLIENT_TABLE . ' SET client_isactive=0 WHERE client_isactive=1 AND client_id IN(' . $ids . ')';
                            db_query($sql);
                            $msg = sprintf(_("%s of %s selected users locked"), db_affected_rows(), $selected);
                        } elseif (isset($_POST['delete'])) {
                            db_query('DELETE FROM ' . CLIENT_TABLE . ' WHERE client_id IN(' . $ids . ')');
                            $msg = sprintf(_("%s of %s selected users deleted"), db_affected_rows(), $selected);
                        } else {
                            $errors['err'] = _('Uknown command!');
                        }
                    } else {
                        $errors['err'] = _('No users selected.');
                    }
                    break;
                default:
                    $errors['err'] = _('Uknown command!');
            }
            break;
        case 'staff':
            include_once(INCLUDE_DIR . 'class.staff.php');
            $do = strtolower($_POST['do']);
            switch ($do) {
                case 'update':
                    $staff = new Staff($_POST['staff_id']);
                    if ($staff && $staff->getId()) {
                        if ($staff->update($_POST, $errors))
                            $msg = _('Staff profile updated successfully');
                        elseif (!$errors['err'])
                            $errors['err'] = _('Error updating the user');
                    }else {
                        $errors['err'] = _('Internal error');
                    }
                    break;
                case 'create':
                    if (($uID = Staff::create($_POST, $errors)))
                      $msg = sprintf(_('%s added successfully'), Format::htmlchars($_POST['firstname'].' '.$_POST['lastname']));
                    elseif (!$errors['err'])
                        $errors['err'] = _('Unable to add the user. Internal error');
                    break;
                case 'mass_process':
                    //ok..at this point..look WMA.
                    if ($_POST['uids'] && is_array($_POST['uids'])) {
                        $ids = implode(',', $_POST['uids']);
                        $selected = count($_POST['uids']);
                        if (isset($_POST['enable'])) {
                            $sql = 'UPDATE ' . STAFF_TABLE . ' SET isactive=1,updated=NOW() WHERE isactive=0 AND staff_id IN(' . $ids . ')';
                            db_query($sql);
                            $msg = sprintf(_("%s of  %s selected users enabled"), db_affected_rows(), $selected);
                        } elseif (in_array($thisuser->getId(), $_POST['uids'])) {
                            //sucker...watch what you are doing...why don't you just DROP the DB?
                            $errors['err'] = _('You can not lock or delete yourself!');
                        } elseif (isset($_POST['disable'])) {
                            $sql = 'UPDATE ' . STAFF_TABLE . ' SET isactive=0, updated=NOW() ' .
                                    ' WHERE isactive=1 AND staff_id IN(' . $ids . ') AND staff_id!=' . $thisuser->getId();
                            db_query($sql);
                            $msg = sprintf(_("%s of %s selected users locked"), db_affected_rows(), $selected);
                            //Release tickets assigned to the user?? NO? could be a temp thing
                            // May be auto-release if not logged in for X days?
                        } elseif (isset($_POST['delete'])) {
                            db_query('DELETE FROM ' . STAFF_TABLE . ' WHERE staff_id IN(' . $ids . ') AND staff_id!=' . $thisuser->getId());
                            $msg = sprintf(_("%s of %s selected users deleted"), db_affected_rows(), $selected);
                            //Demote the user
                            db_query('UPDATE ' . DEPT_TABLE . ' SET manager_id=0 WHERE manager_id IN(' . $ids . ') ');
                            db_query('UPDATE ' . TICKET_TABLE . ' SET staff_id=0 WHERE staff_id IN(' . $ids . ') ');
                        } else {
                            $errors['err'] = _('Uknown command!');
                        }
                    } else {
                        $errors['err'] = _('No users selected.');
                    }
                    break;
                default:
                    $errors['err'] = _('Uknown command!');
            }
            break;
        case 'dept':
            include_once(INCLUDE_DIR . 'class.dept.php');
            $do = strtolower($_POST['do']);
            switch ($do) {
                case 'update':
                    $dept = new Dept($_POST['dept_id']);
                    if ($dept && $dept->getId()) {
                        if ($dept->update($_POST, $errors))
                            $msg = _('Dept updated successfully');
                        elseif (!$errors['err'])
                            $errors['err'] = _('Error updating the department');
                    }else {
                        $errors['err'] = _('Internal error');
                    }
                    break;
                case 'create':
                    if (($deptID = Dept::create($_POST, $errors)))
                        $msg = sprintf('%s added successfully', Format::htmlchars($_POST['dept_name']));
                    elseif (!$errors['err'])
                        $errors['err'] = _('Unable to add department. Internal error');
                    break;
                case 'mass_process':
                    if (!$_POST['ids'] || !is_array($_POST['ids'])) {
                        $errors['err'] = _('You must select at least one department');
                    } elseif (!$_POST['public'] && in_array($cfg->getDefaultDeptId(), $_POST['ids'])) {
                        $errors['err'] = _('You can not disable/delete a default department. Remove default Dept and try again.');
                    } else {
                        $count = count($_POST['ids']);
                        $ids = implode(',', $_POST['ids']);
                        if ($_POST['public']) {
                            $sql = 'UPDATE ' . DEPT_TABLE . ' SET ispublic=1 WHERE dept_id IN (' . $ids . ')';
                            if (db_query($sql) && ($num = db_affected_rows()))
                                $warn = sprintf(_("%s of %s selected departments made public"), $num, $count);
                            else
                                $errors['err'] = _('Unable to make depts public.');
                        }elseif ($_POST['private']) {
                            $sql = 'UPDATE ' . DEPT_TABLE . ' SET ispublic=0 WHERE dept_id IN (' . $ids . ') AND dept_id!=' . db_input($cfg->getDefaultDeptId());
                            if (db_query($sql) && ($num = db_affected_rows())) {
                                $warn = sprintf(_("%s of %s selected departments made private"), $num, $count);
                            }else
                                $errors['err'] = _('Unable to make selected department(s) private. Possibly already private!');
                        }elseif ($_POST['delete']) {
                            //Deny all deletes if one of the selections has members in it.
                            $sql = 'SELECT count(staff_id) FROM ' . STAFF_TABLE . ' WHERE dept_id IN (' . $ids . ')';
                            list($members) = db_fetch_row(db_query($sql));
                            $sql = 'SELECT count(topic_id) FROM ' . TOPIC_TABLE . ' WHERE dept_id IN (' . $ids . ')';
                            list($topics) = db_fetch_row(db_query($sql));
                            if ($members) {
                                $errors['err'] = _('Can not delete Dept. with members. Move staff first.');
                            } elseif ($topic) {
                                $errors['err'] = _('Can not delete Dept. associated with a help topics. Remove association first.');
                            } else {
                                //We have to deal with individual selection because of associated tickets and users.
                                $i = 0;
                                foreach ($_POST['ids'] as $k => $v) {
                                    if ($v == $cfg->getDefaultDeptId())
                                        continue; //Don't delete default dept. Triple checking!!!!!
                                    if (Dept::delete($v))
                                        $i++;
                                }
                                if ($i > 0) {
                                    $warn = sprintf(_("%s of %s selected departments deleted"), $i, $count);
                                } else {
                                    $errors['err'] = _('Unable to delete selected departments.');
                                }
                            }
                        }
                    }
                    break;
                default:
                    $errors['err'] = _('Unknown Dept action');
            }
            break;
        default:
            $errors['err'] = _('Uknown command!');
    endswitch;
endif;

//================ADMIN MAIN PAGE LOGIC==========================
//Process requested tab.
$thistab = strtolower($_REQUEST['t'] ? $_REQUEST['t'] : 'dashboard');
$inc = $page = ''; //No outside crap please!
$submenu = array();
switch ($thistab) {
    //Preferences & settings
    case 'settings':
    case 'pref':
    case 'attach':
    case 'api':
        $nav->setTabActive('settings');
        $nav->addSubMenu(array('desc' => _('PREFERENCES'), 'href' => 'admin.php?t=pref', 'iconclass' => 'preferences'));
        $nav->addSubMenu(array('desc' => _('ATTACHMENTS'), 'href' => 'admin.php?t=attach', 'iconclass' => 'attachment'));
        $nav->addSubMenu(array('desc' => _('API'), 'href' => 'admin.php?t=api', 'iconclass' => 'api'));
        switch ($thistab){
            case 'settings':
            case 'pref':
                $page = 'preference.inc.php';
                break;
            case 'attach':
                $page = 'attachment.inc.php';
                break;
            case 'api':
                $page = 'api.inc.php';
        }
        break;
    case 'dashboard':
    case 'syslog':
    case 'reports':
      $nav->setTabActive('dashboard');
      $nav->addSubMenu(array('desc' => _('REPORTS'), 'href' => 'admin.php?t=reports', 'iconclass' => 'reports'));
      $nav->addSubMenu(array('desc' => _('SYSTEM LOGS'), 'href' => 'admin.php?t=syslog', 'iconclass' => 'syslogs'));
      switch ($thistab) {
          case 'dashboard':
          case 'reports':
            $page = 'reports.inc.php';
            break;
          case 'syslog':
            $page = 'syslogs.inc.php';
      }
      break;
    case 'email':
    case 'templates':
    case 'banlist':
        $nav->setTabActive('emails');
        $nav->addSubMenu(array('desc' => _('EMAIL ADDRESSES'), 'href' => 'admin.php?t=email', 'iconclass' => 'emailSettings'));
        $nav->addSubMenu(array('desc' => _('ADD NEW EMAIL'), 'href' => 'admin.php?t=email&a=new', 'iconclass' => 'newEmail'));
        $nav->addSubMenu(array('desc' => _('TEMPLATES'), 'href' => 'admin.php?t=templates', 'title' => _('Email Templates'), 'iconclass' => 'emailTemplates'));
        $nav->addSubMenu(array('desc' => _('BANLIST'), 'href' => 'admin.php?t=banlist', 'title' => _('Banned Email'), 'iconclass' => 'banList'));
        switch ($thistab) {
            case 'templates':
                $page = 'templates.inc.php';
                $template = null;
                if (($id = $_REQUEST['id'] ? $_REQUEST['id'] : $_POST['email_id']) && is_numeric($id)) {
                    include_once(INCLUDE_DIR . 'class.msgtpl.php');
                    $template = new Template($id);
                    if (!$template || !$template->getId()) {
                        $template = null;
                        $errors['err'] = sprintf(_('Unable to fetch info on template ID#'), $id);
                    } else {
                        $page = 'template.inc.php';
                    }
                }
                break;
            case 'banlist':
                $page = 'banlist.inc.php';
                break;
            case 'email':
            default:
                include_once(INCLUDE_DIR . 'class.email.php');
                $email = null;
                if (($id = $_REQUEST['id'] ? $_REQUEST['id'] : $_POST['email_id']) && is_numeric($id)) {
                    $email = new Email($id, false);
                    if (!$email->load()) {
                        $email = null;
                        $errors['err'] = sprintf(_('Unable to fetch info on email ID#%s'), $id);
                    }
                }
                $page = ($email or ($_REQUEST['a'] == 'new' && !$emailID)) ? 'email.inc.php' : 'emails.inc.php';
        }
        break;
    case 'topics':
        require_once(INCLUDE_DIR . 'class.topic.php');
        $topic = null;
        $nav->setTabActive('topics');
        $nav->addSubMenu(array('desc' => _('HELP TOPICS'), 'href' => 'admin.php?t=topics', 'iconclass' => 'helpTopics'));
        $nav->addSubMenu(array('desc' => _('ADD NEW TOPIC'), 'href' => 'admin.php?t=topics&a=new', 'iconclass' => 'newHelpTopic'));
        if (($id = $_REQUEST['id'] ? $_REQUEST['id'] : $_POST['topic_id']) && is_numeric($id)) {
            $topic = new Topic($id);
            if (!$topic->load() && $topic->getId() == $id) {
                $topic = null;
                $errors['err'] = sprintf(_('Unable to fetch info on topic #%s'), $id);
            }
        }
        $page = ($topic or ($_REQUEST['a'] == 'new' && !$topicID)) ? 'topic.inc.php' : 'helptopics.inc.php';
        break;
    //Staff (members, clients and roles)
    case 'grp':
    case 'roles':
    case 'client':
    case 'staff':
        $role = null;
        //Tab and Nav options.
        $nav->setTabActive('staff');
        $nav->addSubMenu(array('desc' => _('STAFF MEMBERS'), 'href' => 'admin.php?t=staff', 'iconclass' => 'users'));
        $nav->addSubMenu(array('desc' => _('ADD NEW STAFF'), 'href' => 'admin.php?t=staff&a=new', 'iconclass' => 'newuser'));
        $nav->addSubMenu(array('desc' => _('STAFF ROLES'), 'href' => 'admin.php?t=roles', 'iconclass' => 'roles'));
        $nav->addSubMenu(array('desc' => _('ADD NEW ROLE'), 'href' => 'admin.php?t=roles&a=new', 'iconclass' => 'newrole'));
       	$nav->addSubMenu(array('desc' => _('CLIENT LIST'), 'href' => 'admin.php?t=client', 'iconclass' => 'user'));
       	$nav->addSubMenu(array('desc' => _('ADD NEW CLIENT'), 'href' => 'admin.php?t=client&a=new', 'iconclass' => 'newuser'));
        $page = '';
        switch ($thistab) {
            case 'grp':
            case 'roles':
                if (($id = $_REQUEST['id'] ? $_REQUEST['id'] : $_POST['role_id']) && is_numeric($id)) {
                    $res = db_query('SELECT * FROM ' . GROUP_TABLE . ' WHERE role_id=' . db_input($id));
                    if (!$res or !db_num_rows($res) or !($role = db_fetch_array($res)))
                        $errors['err'] = sprintf(_('Unable to fetch info on role ID#%s'), $id);
                }
                $page = ($role or ($_REQUEST['a'] == 'new' && !$gID)) ? 'role.inc.php' : 'roles.inc.php';
                break;
            case 'client':
                $page = 'clientmembers.inc.php';
                if (($id = $_REQUEST['id'] ? $_REQUEST['id'] : $_POST['client_id']) && is_numeric($id)) {
                    $client = new Client($id);
                    if (!$client || !is_object($client) || $client->getId() != $id) {
                        $client = null;
                        $errors['err'] = sprintf(_('Unable to fetch info on client ID#%s'), $id);
                    }
                }
                $page = ($client or ($_REQUEST['a'] == 'new' && !$uID)) ? 'client.inc.php' : 'clientmembers.inc.php';
                break;
            case 'staff':
                $page = 'staffmembers.inc.php';
                if (($id = $_REQUEST['id'] ? $_REQUEST['id'] : $_POST['staff_id']) && is_numeric($id)) {
                    $staff = new Staff($id);
                    if (!$staff || !is_object($staff) || $staff->getId() != $id) {
                        $staff = null;
                        $errors['err'] = sprintf(_('Unable to fetch info on staff ID#%s'), $id);
                    }
                }
                $page = ($staff or ($_REQUEST['a'] == 'new' && !$uID)) ? 'staff.inc.php' : 'staffmembers.inc.php';
                break;
            default:
                $page = 'staffmembers.inc.php';
        }
        break;
    //Departments
    case 'dept': //lazy
    case 'depts':
        $dept = null;
        if (($id = $_REQUEST['id'] ? $_REQUEST['id'] : $_POST['dept_id']) && is_numeric($id)) {
            $dept = new Dept($id);
            if (!$dept || !$dept->getId()) {
                $dept = null;
                $errors['err'] = sprintf(_('Unable to fetch info on Dept ID#%s'), $id);
            }
        }
        $page = ($dept or ($_REQUEST['a'] == 'new' && !$deptID)) ? 'dept.inc.php' : 'depts.inc.php';
        $nav->setTabActive('depts');
        $nav->addSubMenu(array('desc' => _('DEPARTMENTS'), 'href' => 'admin.php?t=depts', 'iconclass' => 'departments'));
        $nav->addSubMenu(array('desc' => _('ADD NEW DEPT.'), 'href' => 'admin.php?t=depts&a=new', 'iconclass' => 'newDepartment'));
        break;
    // (default)
    default:
        $page = 'pref.inc.php';
}
//========================= END ADMIN PAGE LOGIC ==============================//

$inc = ($page) ? STAFFINC_DIR . $page : '';
//Now lets render the page... First the header
require(STAFFINC_DIR . 'header.inc.php');

// Insert possible error messages
?>
<div>
<?php if ($errors['err']) { ?>
        <p align="center" id="errormessage"><?= $errors['err'] ?></p>
<?php } elseif ($msg) { ?>
        <p align="center" id="infomessage"><?= $msg ?></p>
<?php } elseif ($warn) {?>
        <p align="center" id="warnmessage"><?= $warn ?></p>
<?php } ?>
</div>

<?php
if ($inc && file_exists($inc)) {
    require($inc);
} else {
?>
    <p align="center">
        <span class="error"><?= _('Problems loading requested admin page.') ?> (<?= Format::htmlchars($thistab) ?>)</span>
        <br /><?= _('Possibly access denied, if you believe this is in error please get technical support.') ?>
    </p>
<?php }
// Eventually the footer
include_once(STAFFINC_DIR . 'footer.inc.php');
?>
