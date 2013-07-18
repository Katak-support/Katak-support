<?php
/*********************************************************************
    profile.php

    Staff's profile handle

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

require_once('staff.inc.php');
$msg='';
if($_POST && $_POST['id']!=$thisuser->getId()) { //Check dummy ID used on the form.
    $errors['err']=_('Internal Error. Action Denied');
}

if(!$errors && $_POST) { //Handle post
    switch(strtolower($_REQUEST['t'])):
        case 'pref':
            if(!is_numeric($_POST['auto_refresh_rate']))
                $errors['err']=_('Invalid auto refresh value.');

            if(!$errors) {

                $sql='UPDATE '.STAFF_TABLE.' SET updated=NOW() '.
                        ',daylight_saving='.db_input(isset($_POST['daylight_saving'])?1:0).
                        ',max_page_size='.db_input($_POST['max_page_size']).
                        ',auto_refresh_rate='.db_input($_POST['auto_refresh_rate']).
                        ',timezone_offset='.db_input($_POST['timezone_offset']).
                        ' WHERE staff_id='.db_input($thisuser->getId());

                if(db_query($sql) && db_affected_rows()) {
                    $thisuser->reload();
                    $_SESSION['TZ_OFFSET']=$thisuser->getTZoffset();
                    $_SESSION['daylight']=$thisuser->observeDaylight();
                    $msg=_('Preference Updated Successfully');
                }else {
                    $errors['err']=_('Preference update error.');
                }
            }
            break;
        case 'passwd':
            if(!$_POST['password'])
                $errors['password']=_('Current password required');
            if(!$_POST['npassword'])
                $errors['npassword']=_('New password required');
            elseif(strlen($_POST['npassword'])<6)
                $errors['npassword']=_('Must be at least 6 characters');
            if(!$_POST['vpassword'])
                $errors['vpassword']=_('Confirm new password');
            if(!$errors) {
                if(!$thisuser->check_passwd($_POST['password'])) {
                    $errors['password']=_('Valid password required');
                }elseif(strcmp($_POST['npassword'],$_POST['vpassword'])) {
                    $errors['npassword']=$errors['vpassword']=_('New password(s) don\'t match');
                }elseif(!strcasecmp($_POST['password'],$_POST['npassword'])) {
                    $errors['npassword']=_('New password is same as old password');
                }
            }
            if(!$errors) {
                $sql='UPDATE '.STAFF_TABLE.' SET updated=NOW() '.
                    ',change_passwd=0, passwd='.db_input(PhpassHashedPass::hash($_POST['npassword'])).
                        ' WHERE staff_id='.db_input($thisuser->getId());
                if(db_query($sql) && db_affected_rows()) {
                    $msg=_('Password Changed Successfully');
                }else {
                    $errors['err']=_('Unable to complete password change. Internal error.');
                }
            }
            break;
        case 'info':
        //Update profile info
            if(!$_POST['firstname']) {
                $errors['firstname']=_('First name required');
            }
            if(!$_POST['lastname']) {
                $errors['lastname']=_('Last name required');
            }
            if(!$_POST['email'] || !Validator::is_email($_POST['email'])) {
                $errors['email']=_('Valid email required');
            }
            if($_POST['phone'] && !Validator::is_phone($_POST['phone'])) {
                $errors['phone']=_('Enter a valid number');
            }
            if($_POST['mobile'] && !Validator::is_phone($_POST['mobile'])) {
                $errors['mobile']=_('Enter a valid number');
            }

            if(!$errors) {

                $sql='UPDATE '.STAFF_TABLE.' SET updated=NOW() '.
                        ',firstname='.db_input(Format::striptags($_POST['firstname'])).
                        ',lastname='.db_input(Format::striptags($_POST['lastname'])).
                        ',email='.db_input($_POST['email']).
                        ',phone="'.db_input($_POST['phone'],false).'"'.
                        ',mobile="'.db_input($_POST['mobile'],false).'"'.
                        ',signature='.db_input(Format::striptags($_POST['signature'])).
                        ' WHERE staff_id='.db_input($thisuser->getId());
                if(db_query($sql) && db_affected_rows()) {
                    $msg=_('Profile Updated Successfully');
                }else {
                    $errors['err']=_('Error(s) occured. Profile NOT updated');
                }
            }else {
                $errors['err']=_('Error(s) below occured. Try again');
            }
            break;
        default:
            $errors['err']=_('Uknown action');
        endswitch;
    //Reload user info if no errors.
    if(!$errors) {
        $thisuser->reload();
        $_SESSION['TZ_OFFSET']=$thisuser->getTZoffset();
        $_SESSION['daylight']=$thisuser->observeDaylight();
    }
}

//Tab and Nav options.
$nav->setTabActive('profile');
$nav->addSubMenu(array('desc'=>_('MY PROFILE'),'href'=>'profile.php','iconclass'=>'user'));
$nav->addSubMenu(array('desc'=>_('PREFERENCES'),'href'=>'profile.php?t=pref','iconclass'=>'userPref'));
$nav->addSubMenu(array('desc'=>_('CHANGE PASSWORD'),'href'=>'profile.php?t=passwd','iconclass'=>'userPasswd'));
//Warnings if any.
if($thisuser->onVacation()) {
    $warn.=_('Welcome back! You are listed as \'on vacation\' Please let admin or your manager know that you are back.');
}

$rep=($errors && $_POST)?Format::input($_POST):Format::htmlchars($thisuser->getData());

// page logic
$inc='myprofile.inc.php';
switch(strtolower($_REQUEST['t'])) {
    case 'pref':
        $inc='mypref.inc.php';
        break;
    case 'passwd':
        $inc='changepasswd.inc.php';
        break;
    case 'info':
    default:
        $inc='myprofile.inc.php';
}
//Forced password Change.
if($thisuser->forcePasswdChange()) {
    $errors['err']=_('You must change your password to continue.');
    $inc='changepasswd.inc.php';
}

//Render the page.
require_once(STAFFINC_DIR.'header.inc.php');
?>
<div>
    <?php if($errors['err']) { ?>
    <p align="center" id="errormessage"><?=$errors['err']?></p>
        <?php }elseif($msg) { ?>
    <p align="center" id="infomessage"><?=$msg?></p>
        <?php }elseif($warn) { ?>
    <p align="center" id="warnmessage"><?=$warn?></p>
        <?php } ?>
</div>
<div>
    <?php require(STAFFINC_DIR.$inc);  ?>
</div>
<?php
require_once(STAFFINC_DIR.'footer.inc.php');
?>