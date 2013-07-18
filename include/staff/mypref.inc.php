<?php
if(!defined('KTKADMININC') || !is_object($thisuser) || !$rep) die('Adiaux amikoj!');
?>
<div class="msg">&nbsp;<?= _('My Preferences') ?></div>
<form action="profile.php" method="post">
  <input type="hidden" name="t" value="pref">
  <input type="hidden" name="id" value="<?=$thisuser->getId()?>">
  <table width="100%" border="0" cellspacing=2 cellpadding=3>
    <tr>
        <td width="145" nowrap><?= _('Maximum Page size:') ?></td>
        <td>
            <select name="max_page_size">
                <?php
                $pagelimit=$rep['max_page_size']?$rep['max_page_size']:$cfg->getPageSize();
                for ($i = 5; $i <= 50; $i += 5) {?>
                    <option <?=$pagelimit== $i ? 'selected':''?>><?=$i?></option>
                <?php } ?>
            </select> <?= _('Tickets/items per page.') ?>
        </td>
    </tr>
    <tr>
        <td nowrap><?= _('Auto Refresh Rate:') ?></td>
        <td>
            <input type="text" size=3 name="auto_refresh_rate" value="<?=$rep['auto_refresh_rate']?>">
            <?= _('in Mins. (<i>Tickets page refresh rate in minutes. Enter 0 to disable</i>)') ?>
        </td>
    </tr>
    <tr>
        <td nowrap><?= _('Preferred Timezone:') ?></td>
        <td>
            <select name="timezone_offset">
                <?php
                $gmoffset  = date("Z") / 3600; //Server's offset.
                $currentoffset = ($rep['timezone_offset']==NULL)?$cfg->getTZOffset():$rep['timezone_offset'];
                echo"<option value=\"$gmoffset\">Server Time (GMT $gmoffset:00)</option>"; //Default if all fails.
                $timezones= db_query('SELECT offset,timezone FROM '.TIMEZONE_TABLE);
                while (list($offset,$tz) = db_fetch_row($timezones)){
                    $selected = ($currentoffset==$offset) ?'SELECTED':'';
                    $tag=($offset)?"GMT $offset ($tz)":" GMT ($tz)"; ?>
                    <option value="<?=$offset?>"<?=$selected?>><?=$tag?></option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr>
        <td><?= _('Daylight Savings:') ?></td>
        <td>
            <input type="checkbox" name="daylight_saving" <?=$rep['daylight_saving'] ? 'checked': ''?>><?= _('Observe daylight saving') ?>
        </td>
    </tr>
    <tr><td><?= _('Current Time:') ?></td>
        <td><b><i><?=Format::date($cfg->getDateTimeFormat(),Misc::gmtime(),$rep['timezone_offset'],$rep['daylight_saving'])?></i></b></td>
    </tr>  
    <tr>
        <td>&nbsp;</td>
        <td><br>
            <input class="button" type="submit" name="submit" value="<?= _('Submit') ?>">
            <input class="button" type="reset" name="reset" value="<?= _('Reset') ?>">
            <input class="button" type="button" name="cancel" value="<?= _('Cancel') ?>" onClick='window.location.href="profile.php"'>
        </td>
    </tr>
  </table>
</form>

