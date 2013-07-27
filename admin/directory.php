<?php
/*********************************************************************
    directory.php

    Help desk's directories.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

require('staff.inc.php');
$nav->setTabActive('directory');
$nav->addSubMenu(array('desc'=>_('STAFF MEMBERS'),'href'=>'directory.php','iconclass'=>'staff'));

$WHERE=' WHERE isvisible=1 ';
$sql=' SELECT staff.staff_id,staff.dept_id,firstname,lastname,email,phone,mobile,dept_name,isactive,onvacation,manager_id '.
     ' FROM '.STAFF_TABLE.' staff LEFT JOIN  '.DEPT_TABLE.' USING(dept_id)';
if($_POST && $_POST['a']=='search') {
    $searchTerm=$_POST['query']; 
    if($searchTerm){
        $query=db_real_escape($searchTerm,false); //escape the term ONLY...no quotes.
        if(is_numeric($searchTerm)){
            $WHERE.=" AND staff.phone LIKE '%$query%'";
        }elseif(strpos($searchTerm,'@') && Validator::is_email($searchTerm)){
            $WHERE.=" AND staff.email='$query'";
        }else{
            $WHERE.=" AND ( staff.email LIKE '%$query%'".
                         " OR staff.lastname LIKE '%$query%'".
                         " OR staff.firstname LIKE '%$query%'".
                        ' ) ';
        }
    }
    if($_POST['dept'] && is_numeric($_POST['dept'])) {
        $WHERE.=' AND staff.dept_id='.db_input($_POST['dept']);
    }
}

$users=db_query("$sql $WHERE ORDER BY lastname,firstname");

//Render the page.
require_once(STAFFINC_DIR.'header.inc.php');
?>
<div>
    <?php if($errors['err']) { ?>
        <p align="center" id="errormessage"><?=$errors['err']?></p>
    <?php }elseif($msg) { ?>
        <p align="center" id="infomessage"><?=$msg?></p>
    <?php }elseif($warn) { ?>
        <p id="warnmessage"><?=$warn?></p>
    <?php } ?>
</div>
<div align="left">
    <form action="directory.php" method="POST" >
    <input type='hidden' name='a' value='search'>
    <?= _('Search for')?> :&nbsp;<input type="text" name="query" value="<?=Format::htmlchars($_REQUEST['query'])?>">
    <?= _('Dept.') ?>
    <select name="dept">
            <option value=0><?= _('All Department') ?></option>
            <?php
            $depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE);
            while (list($deptId,$deptName) = db_fetch_row($depts)){
                $selected = ($_POST['dept']==$deptId)?'selected':''; ?>
                <option value="<?=$deptId?>"<?=$selected?>><?=$deptName?></option>
           <?php } ?>
    </select>
    &nbsp;
    <input type="submit" name="search" class="button" value="GO">
    </form>
</div>
<?php if($users && db_num_rows($users)):?>
<div class="msg"><?= _('Staff Members') ?></div>
<table border="0" cellspacing=0 cellpadding=2 class="dtable" width="100%">
    <tr>
        <th><?= _('Name') ?></th>
        <th><?= _('Dept') ?></th>
        <th><?= _('Status') ?></th>
        <th><?= _('Email') ?></th>
        <th><?= _('Phone') ?></th>
        <th><?= _('Mobile') ?></th>
    </tr>
    <?php
    $class = 'row1';
    while ($row = db_fetch_array($users)) {
        $name=ucfirst($row['firstname'].' '.$row['lastname']);
        ?>
        <tr class="<?=$class?>" id="<?=$row['staff_id']?>" onClick="highLightToggle(this.id);">
            <td><?=$name?>&nbsp;</td>
            <td><?=$row['dept_name']?>&nbsp;<?=$row['manager_id']==$row['staff_id']?'&nbsp;<i>('._('mng').')</i>':''?></td>
            <td><?=$row['isactive']?($row['onvacation']?_('Vacation'):_('Active')):_('Locked')?>&nbsp;</td>
            <td><?=$row['email']?>&nbsp;</td>
            <td><?=Format::phone($row['phone'])?>&nbsp;</td>
            <td><?=Format::phone($row['mobile'])?>&nbsp;</td>
        </tr>
        <?php
        $class = ($class =='row2') ?'row1':'row2';
    }
    ?>
</table>
<?php
else:
echo '<b>' . _("No staff person found with this name") . '</b>';
endif;
include_once(STAFFINC_DIR.'footer.inc.php');
?>