<?php
if(!defined('KTKADMININC') || !$thisuser->isadmin()) die(_('Access Denied'));

#required files
require_once(INCLUDE_DIR.'class.i18n.php');
//Get the config info.
$config=($errors && $_POST)?Format::input($_POST):Format::htmlchars($cfg->getConfig());
//Basic checks for warnings...
$warn=array();
if($config['allow_attachments'] && !$config['upload_dir']) {
    $errors['allow_attachments']=_('You need to setup upload dir.');
}else{
    if(!$config['allow_attachments'] && $config['allow_email_attachments'])
        $warn['allow_email_attachments']=_('*Attachments Disabled.');
    if(!$config['allow_attachments'] && ($config['allow_online_attachments'] or $config['allow_online_attachments_onlogin']))
        $warn['allow_online_attachments']=_('<br>*Attachments Disabled.');
}

if(!$errors['enable_captcha'] && $config['enable_captcha'] && !extension_loaded('gd'))
    $errors['enable_captcha']=_('GD required for captcha to work');
    

//Not showing err on post to avoid alarming the user...after an update.
if(!$errors['err'] &&!$msg && $warn )
    $errors['err']=_('Possible errors detected, please check the warnings below');
    
$gmtime=Misc::gmtime();
$depts= db_query('SELECT dept_id,dept_name FROM '.DEPT_TABLE.' WHERE ispublic=1');
$templates=db_query('SELECT tpl_id,name FROM '.EMAIL_TEMPLATE_TABLE.' WHERE cfg_id='.db_input($cfg->getId()));
?>
<div class="msg"><?= _('System Preferences and Settings') ?>&nbsp; &nbsp;(Katak-support ver. <?=THIS_VERSION?>)</div>
<form action="admin.php?t=pref" method="post">
  <input type="hidden" name="t" value="pref">
  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header" ><td colspan=2><?= _('General Settings') ?></td></tr>
      <tr class="subheader">
          <td colspan=2"><?= _('Offline mode will disable external interface and <b>only</b> allow <b>super admins</b> to login to Staff Control Panel') ?></td>
      </tr>
      <tr><th><b><?= _('Support System Status') ?></b></th>
          <td>
              <input type="radio" name="isonline"  value="1"   <?=$config['isonline']?'checked':''?> /><b><?= _('Online') ?></b> <?= _('(Active)') ?>
              <input type="radio" name="isonline"  value="0"   <?=!$config['isonline']?'checked':''?> /><b><?= _('Offline') ?></b> <?= _('(Inactive)') ?>
              &nbsp;<font class="warn">&nbsp;<?=$config['isoffline']?_('Katak system offline'):''?></font>
          </td>
      </tr>
      <tr><th><?= _('Support System URL:') ?></th>
          <td>
              <input type="text" size="40" name="helpdesk_url" value="<?=$config['helpdesk_url']?>"> 
              &nbsp;<font class="error">*&nbsp;<?=$errors['helpdesk_url']?></font></td>
      </tr>
      <tr><th><?= _('Support System Name/Title:') ?></th>
          <td><input type="text" size="40" name="helpdesk_title" value="<?=$config['helpdesk_title']?>"> </td>
      </tr>
      <tr><th><?= _('Default Email Templates:') ?></th>
          <td>
              <select name="default_template_id">
                  <option value=0><?= _('Select Default Template') ?></option>
                  <?php
                  while (list($id,$name) = db_fetch_row($templates)){
                      $selected = ($config['default_template_id']==$id)?'SELECTED':''; ?>
                      <option value="<?=$id?>"<?=$selected?>><?=$name?></option>
                  <?php
                  }?>
              </select>&nbsp;<font class="error">*&nbsp;<?=$errors['default_template_id']?></font>
          </td>
      </tr>
      <tr><th><?= _('Default Department:') ?></th>
          <td>
              <select name="default_dept_id">
                  <option value=0><?= _('Select Default Dept') ?></option>
                  <?php
                  while (list($id,$name) = db_fetch_row($depts)){
                  $selected = ($config['default_dept_id']==$id)?'SELECTED':''; ?>
                  <option value="<?=$id?>"<?=$selected?>><?=$name?> <?= _('Dept') ?></option>
                  <?php
                  }?>
              </select>&nbsp;<font class="error">*&nbsp;<?=$errors['default_dept_id']?></font>
          </td>
      </tr>
      <tr><th><?= _('Default Page Size:') ?></th>
          <td>
              <select name="max_page_size">
                  <?php
                   $pagelimit=$config['max_page_size'];
                  for ($i = 5; $i <= 50; $i += 5) {
                      ?>
                      <option <?=$config['max_page_size'] == $i ? 'SELECTED':''?> value="<?=$i?>"><?=$i?></option>
                      <?php
                  }?>
              </select>
              <i><?= _('Global setting which can be overwritten by single staff members.') ?></i>
          </td>
      </tr>
      <tr><th><?= _('System Log Level:') ?></th>
          <td>
              <select name="log_level">
                  <option value=0 <?=$config['log_level'] == 0 ? 'selected="selected"':''?>><?= _('None (Disable Logger)') ?></option>
                  <option value=3 <?=$config['log_level'] == 3 ? 'selected="selected"':''?>> <?= _('DEBUG') ?></option>
                  <option value=2 <?=$config['log_level'] == 2 ? 'selected="selected"':''?>> <?= _('WARN') ?></option>
                  <option value=1 <?=$config['log_level'] == 1 ? 'selected="selected"':''?>> <?= _('ERROR') ?></option>
              </select>
              &nbsp;<?= _('Purge logs after') ?>
              <select name="log_graceperiod">
                  <option value=0 selected> <?= _('None (Disable)') ?></option>
                  <?php
                  for ($i = 1; $i <=12; $i++) {
                      ?>
                      <option <?=$config['log_graceperiod'] == $i ? 'SELECTED':''?> value="<?=$i?>"><?=$i?>&nbsp;<?=($i>1)?_('Months'):_('Month')?></option>
                      <?php
                  }?>
              </select>
          </td>
      </tr>
      <tr><th><?= _('Staff Excessive Logins:') ?></th>
          <td>
              <select name="staff_max_logins">
                <?php
                  for ($i = 1; $i <= 10; $i++) {
                      echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['staff_max_logins']==$i)?'selected="selected"':''),$i);
                  }
                  ?>
              </select> <?= _('attempt(s) allowed') ?>
              &nbsp;<?= _('before a') ?>
              <select name="staff_login_timeout">
                <?php
                  for ($i = 1; $i <= 10; $i++) {
                      echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['staff_login_timeout']==$i)?'selected="selected"':''),$i);
                  }
                  ?>
              </select> <?= _('min. timeout (penalty in minutes)') ?>
          </td>
      </tr>
      <tr><th><?= _('Staff Session Timeout:') ?></th>
          <td>
            <input type="text" name="staff_session_timeout" size=6 value="<?=$config['staff_session_timeout']?>">
            (<i><?= _('Staff\'s max Idle time in minutes. Enter 0 to disable timeout') ?></i>)
          </td>
      </tr>
      <tr><th><?= _('Bind Staff Session to IP:') ?></th>
          <td>
            <input type="checkbox" name="staff_ip_binding" <?=$config['staff_ip_binding']?'checked':''?>>
            <?= _('Bind staff\'s session to login IP.') ?>
          </td>
      </tr>

      <tr><th><?= _('User log-in required:') ?></th>
          <td>
            <input type="checkbox" name="user_log_required" <?=$config['user_log_required']?'checked':''?>>
            <?= _('Require user to log-in and become "client" to post and view ticket.') ?>
          </td>
      </tr>

      <tr><th><?= _('User/client Excessive Logins:') ?></th>
          <td>
              <select name="client_max_logins">
                <?php
                  for ($i = 1; $i <= 10; $i++) {
                      echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['client_max_logins']==$i)?'selected="selected"':''),$i);
                  }

                  ?>
              </select> <?= _('attempt(s) allowed') ?>
              &nbsp;<?= _('before a') ?>
              <select name="client_login_timeout">
                <?php
                  for ($i = 1; $i <= 10; $i++) {
                      echo sprintf('<option value="%d" %s>%d</option>',$i,(($config['client_login_timeout']==$i)?'selected="selected"':''),$i);
                  }
                  ?>
              </select> <?= _('min. timeout (penalty in minutes)') ?>
          </td>
      </tr>

      <tr><th><?= _('User/client Session Timeout:') ?></th>
          <td>
            <input type="text" name="client_session_timeout" size=6 value="<?=$config['client_session_timeout']?>">
            (<i><?= _('User/client\'s max Idle time in minutes. Enter 0 to disable timeout') ?></i>)
          </td>
      </tr>
      <tr><th><?= _('Clickable URLs:') ?></th>
          <td>
            <input type="checkbox" name="clickable_urls" <?=$config['clickable_urls']?'checked':''?>>
            <?= _('Make URLs clickable') ?>
          </td>
      </tr>
      <tr><th><?= _('Enable Auto Cron:') ?></th>
          <td>
            <input type="checkbox" name="enable_auto_cron" <?=$config['enable_auto_cron']?'checked':''?>>
            <?= _('Enable cron call on staff\'s activity') ?>
          </td>
      </tr>
      <tr><th><?= _('Staff Language:') ?></th>
          <td>
          <select name="stafflanguage">
          <?php foreach (i18n::getLanguages() as $lang) { ?>
               <option value="<?=$lang->name ?>" <?=$lang->name==$config['staff_language']?'selected':'' ?>><?=$lang->description ?></option>
          <?php } ?>
          </select>
          (<i><?= _('Default language for the admin/staff control panel') ?></i>)
          </td>
      </tr>
      <tr><th><?= _('User/client Language:') ?></th>
          <td>
          <select name="userlanguage">
          <?php foreach (i18n::getLanguages() as $lang) { ?>
               <option value="<?=$lang->name ?>" <?=$lang->name==$config['user_language']?'selected':'' ?>><?=$lang->description ?></option>
          <?php } ?>
          </select>
          (<i><?= _('Default language for the external users\'s interface') ?></i>)
          </td>
      </tr>
  </table>
  
  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header"><td colspan=2><?= _('Date &amp; Time') ?></td></tr>
      <tr class="subheader">
          <td colspan=2><?= _('Please refer to <a href="http://php.net/date" target="_blank">PHP Manual</a> for supported parameters.') ?></td>
      </tr>
      <tr><th><?= _('Time Format:') ?></th>
          <td>
              <input type="text" name="time_format" value="<?=$config['time_format']?>">
                  &nbsp;<font class="error">*&nbsp;<?=$errors['time_format']?></font>
                  <i><?=Format::date($config['time_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving'])?></i></td>
      </tr>
      <tr><th><?= _('Date Format:') ?></th>
          <td><input type="text" name="date_format" value="<?=$config['date_format']?>">
                      &nbsp;<font class="error">*&nbsp;<?=$errors['date_format']?></font>
                      <i><?=Format::date($config['date_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving'])?></i>
          </td>
      </tr>
      <tr><th><?= _('Date &amp; Time Format:') ?></th>
          <td><input type="text" name="datetime_format" value="<?=$config['datetime_format']?>">
                      &nbsp;<font class="error">*&nbsp;<?=$errors['datetime_format']?></font>
                      <i><?=Format::date($config['datetime_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving'])?></i>
          </td>
      </tr>
      <tr><th><?= _('Day, Date &amp; Time Format:') ?></th>
          <td><input type="text" name="daydatetime_format" value="<?=$config['daydatetime_format']?>">
                      &nbsp;<font class="error">*&nbsp;<?=$errors['daydatetime_format']?></font>
                      <i><?=Format::date($config['daydatetime_format'],$gmtime,$config['timezone_offset'],$config['enable_daylight_saving'])?></i>
          </td>
      </tr>
      <tr><th><?= _('Default Timezone:') ?></th>
          <td>
              <select name="timezone_offset">
                  <?php
                  $gmoffset = date("Z") / 3600; //Server's offset.
                  echo"<option value=\"$gmoffset\">Server Time (GMT $gmoffset:00)</option>"; //Default if all fails.
                  $timezones= db_query('SELECT offset,timezone FROM '.TIMEZONE_TABLE);
                  while (list($offset,$tz) = db_fetch_row($timezones)){
                      $selected = ($config['timezone_offset'] ==$offset) ?'SELECTED':'';
                      $tag=($offset)?"GMT $offset ($tz)":" GMT ($tz)";
                      ?>
                      <option value="<?=$offset?>"<?=$selected?>><?=$tag?></option>
                      <?php
                  }?>
              </select>
              (<i><?= _('Default timezone for the external interface') ?></i>)
          </td>
      </tr>
      <tr>
          <th><?= _('Daylight Saving:') ?></th>
          <td>
              <input type="checkbox" name="enable_daylight_saving" <?=$config['enable_daylight_saving'] ? 'checked': ''?>><?= _('Observe daylight savings') ?>
          </td>
      </tr>
  </table>
 
  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header"><td colspan=2><?= _('Ticket Options &amp; Settings') ?></td></tr>
      <tr class="subheader"><td colspan=2><?= _('If enabled ticket lock get auto-renewed on form activity.') ?></td></tr>
      <tr><th><?= _('Ticket IDs:') ?></th>
          <td>
              <input type="radio" name="random_ticket_ids"  value="0"   <?=!$config['random_ticket_ids']?'checked':''?> /> <?= _('Sequential') ?>
              <input type="radio" name="random_ticket_ids"  value="1"   <?=$config['random_ticket_ids']?'checked':''?> /><?= _('Random  (recommended)') ?>
          </td>
      </tr>
      <tr><th><?= _('Ticket Priority:') ?></th>
          <td>
              <select name="default_priority_id">
                  <?php
                  $priorities= db_query('SELECT priority_id,priority_desc FROM '.PRIORITY_TABLE);
                  while (list($id,$tag) = db_fetch_row($priorities)){ ?>
                      <option value="<?=$id?>"<?=($config['default_priority_id']==$id)?'selected':''?>><?=$tag?></option>
                  <?php
                  }?>
              </select> &nbsp;<?= _('Default priority') ?><br/>
              <input type="checkbox" name="allow_priority_change" <?=$config['allow_priority_change'] ?'checked':''?>>
              <?= _('Allow user to overwrite/set priority (new web tickets)') ?><br/>
              <input type="checkbox" name="use_email_priority" <?=$config['use_email_priority'] ?'checked':''?> >
              <?= _('Use email priority when available (new emailed tickets)') ?>

          </td>
      </tr>
      <tr><th><?= _('Maximum <b>Open</b> Tickets:') ?></th>
          <td>
            <input type="text" name="max_open_tickets" size=4 value="<?=$config['max_open_tickets']?>"> 
            <?= _('per email. (<i>Helps with spam and flood control. Enter 0 for unlimited</i>)') ?>
          </td>
      </tr>
      <tr><th><?= _('Auto-Lock Time:') ?></th>
          <td>
            <input type="text" name="autolock_minutes" size=4 value="<?=$config['autolock_minutes']?>">
               <font class="error"><?=$errors['autolock_minutes']?></font>
               <?= _('(<i>Minutes to lock a ticket on activity. Enter 0 to disable locking</i>)') ?>
          </td>
      </tr>
      <tr><th><?= _('Ticket Grace Period:') ?></th>
          <td>
            <input type="text" name="overdue_grace_period" size=4 value="<?=$config['overdue_grace_period']?>">
            <?= _('(<i>Hours before ticket is marked overdue. Enter 0 to disable aging</i>)') ?>
          </td>
      </tr>
      <tr><th><?= _('Reopening Ticket by customer:') ?></th>
          <td>
            <input type="text" name="reopen_grace_period" size=4 value="<?=$config['reopen_grace_period']?>">
            <?= _('Days after which the ticket can no longer be re-opened. 0 means never reopen') ?> <br />
            <input type="checkbox" name="auto_assign_reopened_tickets" <?=$config['auto_assign_reopened_tickets'] ? 'checked': ''?>> 
            <?= _('Auto-assign reopened tickets to last respondent if still \'available\'.') ?>
          </td>
      </tr>
      <tr><th><?= _('Ticket Activity Log:') ?></th>
          <td>
            <input type="checkbox" name="log_ticket_activity" <?=$config['log_ticket_activity']?'checked':''?>>
            <?= _('Log ticket\'s activity as internal notes.') ?>
          </td>
      </tr>
      <tr><th><?= _('Staff Identity:') ?></th>
          <td>
            <input type="checkbox" name="hide_staff_name" <?=$config['hide_staff_name']?'checked':''?>>
            <?= _('Hide staff\'s name on responses.') ?>
          </td>
      </tr>
      <tr><th><?= _('Topic Choice:') ?></th>
          <td>
            <input type="checkbox" name="enable_topic" <?=$config['enable_topic']?'checked':''?>>
            <?= _('Enable topic choice on new web ticket.') ?>
          </td>
      </tr>
      <tr><th><?= _('Human Verification:') ?></th>
          <td>
              <?php
                 if($config['enable_captcha'] && !$errors['enable_captcha']) {?>
                      <img src="../captcha.php" border="0" align="left">&nbsp;
              <?php } ?>
            <input type="checkbox" name="enable_captcha" <?=$config['enable_captcha']?'checked':''?>>
            <?= _('Enable captcha on new web tickets.') ?>&nbsp;<font class="error">&nbsp;<?=$errors['enable_captcha']?></font><br/>
          </td>
      </tr>

  </table>
  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header"><td colspan=2 ><?= _('Email Settings') ?></td></tr>
      <tr class="subheader"><td colspan=2><?= _('Note that global settings can be disabled at dept/email level.') ?></td></tr>
      <tr><th><br><b><?= _('Incoming Emails') ?></b>:</th>
          <td><i><?= _('For mail fetcher (POP/IMAP) to work you must set a cron job or simply enable auto-cron') ?></i><br/>
              <input type="checkbox" name="enable_mail_fetch" value=1 <?=$config['enable_mail_fetch']? 'checked': ''?>  > <?= _('Enable POP/IMAP email fetch') ?>
              &nbsp;&nbsp;(<i><?= _('Global setting which can be disabled at email level') ?></i>) <br/>
              <input type="checkbox" name="enable_email_piping" value=1 <?=$config['enable_email_piping']? 'checked': ''?>  > <?= _('Enable email piping') ?>
              &nbsp;(<i><?= _('You pipe we accept policy') ?></i>)<br/>
              <input type="checkbox" name="strip_quoted_reply" <?=$config['strip_quoted_reply'] ? 'checked':''?>>
              <?= _('Strip quoted reply (<i>depends on the tag below</i>)') ?><br/>
              <input type="text" name="reply_separator" value="<?=$config['reply_separator']?>"> <?= _('Reply Separator Tag') ?>
              &nbsp;<font class="error">&nbsp;<?=$errors['reply_separator']?></font>
          </td>
      </tr>
      <tr><th><br><b><?= _('Outgoing Emails') ?></b>:</th>
          <td>
              <i><b><?= _('Default Email:') ?></b> <?= _('Only applies to outgoing emails with no SMTP settings.') ?></i><br/>
              <select name="default_smtp_id"
                  onChange="document.getElementById('overwrite').style.display=(this.options[this.selectedIndex].value>0)?'block':'none';">
                  <option value=0><?= _('Select One') ?></option>
                  <option value=0 selected="selected"><?= _('None: Use PHP mail function') ?></option>
                  <?php
                  $emails=db_query('SELECT email_id,email,name,smtp_host FROM '.EMAIL_TABLE.' WHERE smtp_active=1');
                  if($emails && db_num_rows($emails)) {
                      while (list($id,$email,$name,$host) = db_fetch_row($emails)){
                          $email=$name?"$name &lt;$email&gt;":$email;
                          $email=sprintf('%s (%s)',$email,$host);
                          ?>
                          <option value="<?=$id?>"<?=($config['default_smtp_id']==$id)?'selected="selected"':''?>><?=$email?></option>
                      <?php
                      }
                  }?>
              </select>
              &nbsp;&nbsp;<font class="error">&nbsp;<?=$errors['default_smtp_id']?></font><br/>
              <span id="overwrite" style="display:<?=($config['default_smtp_id']?'display':'none')?>">
                 <input type="checkbox" name="spoof_default_smtp" <?=$config['spoof_default_smtp'] ? 'checked':''?>>
                 <?= _('Allow spoofing (No Overwrite).') ?>&nbsp;<font class="error">&nbsp;<?=$errors['spoof_default_smtp']?></font><br/>
              </span>
           </td>
      </tr>
      <tr><th><?= _('Default System Email:') ?></th>
          <td>
              <select name="default_email_id">
                  <option value=0 disabled><?= _('Select One') ?></option>
                  <?php
                  $emails=db_query('SELECT email_id,email,name FROM '.EMAIL_TABLE);
                  while (list($id,$email,$name) = db_fetch_row($emails)){ 
                      $email=$name?"$name &lt;$email&gt;":$email;
                      ?>
                   <option value="<?=$id?>"<?=($config['default_email_id']==$id)?'selected':''?>><?=$email?></option>
                  <?php
                  }?>
               </select>
               &nbsp;<font class="error">*&nbsp;<?=$errors['default_email_id']?></font></td>
      </tr>
      <tr><th><?= _('Default Alert Email:') ?></th>
          <td>
              <select name="alert_email_id">
                  <option value=0 disabled><?= _('Select One') ?></option>
                  <option value=0 selected="selected"><?= _('Use Default System Email (above)') ?></option>
                  <?php
                  $emails=db_query('SELECT email_id,email,name FROM '.EMAIL_TABLE.' WHERE email_id != '.db_input($config['default_email_id']));
                  while (list($id,$email,$name) = db_fetch_row($emails)){
                      $email=$name?"$name &lt;$email&gt;":$email;
                      ?>
                   <option value="<?=$id?>"<?=($config['alert_email_id']==$id)?'selected':''?>><?=$email?></option>
                  <?php
                  }?>
               </select>
               &nbsp;<font class="error">*&nbsp;<?=$errors['alert_email_id']?></font>
               <br/><i><?= _('Used to send out alerts and notices to staff.') ?></i>
          </td>
      </tr>
  </table>

  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header"><td colspan=2><?= _('Autoresponders &nbsp;(Global Setting)') ?></td></tr>
      <tr class="subheader"><td colspan=2"><?= _('These global settings can be overridden at department level. Notices sent to user use \'Default System Email\' as FROM address if no Dept Email is available.') ?></td></tr>
      <tr><th><?= _('New Ticket:') ?></th>
          <td><i><?= _('Autoresponse includes the ticket ID required to check status of the ticket') ?></i><br>
              <input type="radio" name="ticket_autoresponder"  value="1"   <?=$config['ticket_autoresponder']?'checked':''?> /><?= _('Enable') ?>
              <input type="radio" name="ticket_autoresponder"  value="0"   <?=!$config['ticket_autoresponder']?'checked':''?> /><?= _('Disable') ?>
          </td>
      </tr>
      <tr><th><?= _('New Ticket by Staff:') ?></th>
          <td><i><?= _('Notice sent when staff creates a ticket on behalf of the user (Staff can disable)') ?></i><br>
              <input type="radio" name="ticket_notice_active"  value="1"   <?=$config['ticket_notice_active']?'checked':''?> /><?= _('Enable') ?>
              <input type="radio" name="ticket_notice_active"  value="0"   <?=!$config['ticket_notice_active']?'checked':''?> /><?= _('Disable') ?>
          </td>
      </tr>
      <tr><th><?= _('New Message:') ?></th>
          <td><i><?= _('Message appended to an existing ticket confirmation') ?></i><br>
              <input type="radio" name="message_autoresponder"  value="1"   <?=$config['message_autoresponder']?'checked':''?> /><?= _('Enable') ?>
              <input type="radio" name="message_autoresponder"  value="0"   <?=!$config['message_autoresponder']?'checked':''?> /><?= _('Disable') ?>
          </td>
      </tr>
      <tr><th><?= _('New Response from staff:') ?></th>
          <td><i><?= _('Notice sent when staff respond to an existing ticket') ?></i><br>
              <input type="radio" name="response_notice_active"  value="1"   <?=$config['response_notice_active']?'checked':''?> /><?= _('Enable') ?>
              <input type="radio" name="response_notice_active"  value="0"   <?=!$config['response_notice_active']?'checked':''?> /><?= _('Disable') ?>
          </td>
      </tr>
      <tr><th><?= _('Overlimit notice:') ?></th>
          <td><i><?= _('Ticket denied notice sent <b>only once</b> on limit violation to the user.') ?></i><br/>
              <input type="radio" name="overlimit_notice_active"  value="1"   <?=$config['overlimit_notice_active']?'checked':''?> /><?= _('Enable') ?>
              <input type="radio" name="overlimit_notice_active"  value="0"   <?=!$config['overlimit_notice_active']?'checked':''?> /><?= _('Disable') ?>
              <br><i><?= _('<b>Note:</b> SysAdmin gets alerts on ALL denials by default') ?></i><br>
          </td>
      </tr>
  </table>
  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="tform">
      <tr class="header"><td colspan=2>&nbsp;<?= _('Alerts &amp; notices to staff') ?></td></tr>
      <tr class="subheader"><td colspan=2>
              <?= _('Alerts sent to staff use \'Default Alert Email\' set above as FROM address.') ?></td>
      </tr>
      <tr><th><?= _('New Ticket Alert:') ?></th>
          <td>
            <input type="radio" name="ticket_alert_active"  value="1"   <?=$config['ticket_alert_active']?'checked':''?> /><?= _('Enable') ?>
            <input type="radio" name="ticket_alert_active"  value="0"   <?=!$config['ticket_alert_active']?'checked':''?> /><?= _('Disable') ?>
            <br><i><?= _('Select recipients') ?></i>&nbsp;<font class="error">&nbsp;<?=$errors['ticket_alert_active']?></font><br>
            <input type="checkbox" name="ticket_alert_admin" <?=$config['ticket_alert_admin']?'checked':''?>> <?= _('Administrators') ?>
            <input type="checkbox" name="ticket_alert_dept_manager" <?=$config['ticket_alert_dept_manager']?'checked':''?>> <?= _('Department Manager') ?>
            <input type="checkbox" name="ticket_alert_dept_members" <?=$config['ticket_alert_dept_members']?'checked':''?>> <?= _('Department Members (spammy)') ?>
          </td>
      </tr>
      <tr><th><?= _('New Message Alert:') ?></th>
          <td>
            <input type="radio" name="message_alert_active"  value="1"   <?=$config['message_alert_active']?'checked':''?> /><?= _('Enable') ?>
            <input type="radio" name="message_alert_active"  value="0"   <?=!$config['message_alert_active']?'checked':''?> /><?= _('Disable') ?>
            <br><i><?= _('Select recipients') ?></i>&nbsp;<font class="error">&nbsp;<?=$errors['message_alert_active']?></font><br>
            <input type="checkbox" name="message_alert_laststaff" <?=$config['message_alert_laststaff']?'checked':''?>> <?= _('Last Respondent') ?>
            <input type="checkbox" name="message_alert_assigned" <?=$config['message_alert_assigned']?'checked':''?>> <?= _('Assigned Staff') ?>
            <input type="checkbox" name="message_alert_dept_manager" <?=$config['message_alert_dept_manager']?'checked':''?>> <?= _('Department Manager') ?>
          </td>
      </tr>
      <tr><th><?= _('Ticket assignment:') ?></th>
          <td>
            <i><?= _('Send alert to assigned staff') ?></i>&nbsp;<font class="error">&nbsp;<?=$errors['assignment_alert_active']?></font><br />
            <input type="radio" name="assignment_alert_active"  value="1"   <?=$config['assignment_alert_active']?'checked':''?> /><?= _('Enable') ?>
            <input type="radio" name="assignment_alert_active"  value="0"   <?=!$config['assignment_alert_active']?'checked':''?> /><?= _('Disable') ?>
          </td>
      </tr>
      <tr><th><?= _('New Internal Note Alert:') ?></th>
          <td>
            <input type="radio" name="note_alert_active"  value="1"   <?=$config['note_alert_active']?'checked':''?> /><?= _('Enable') ?>
            <input type="radio" name="note_alert_active"  value="0"   <?=!$config['note_alert_active']?'checked':''?> /><?= _('Disable') ?>
            <br><i><?= _('Select recipients') ?></i>&nbsp;<font class="error">&nbsp;<?=$errors['note_alert_active']?></font><br>
            <input type="checkbox" name="note_alert_laststaff" <?=$config['note_alert_laststaff']?'checked':''?>> <?= _('Last Respondent') ?>
            <input type="checkbox" name="note_alert_assigned" <?=$config['note_alert_assigned']?'checked':''?>> <?= _('Assigned Staff') ?>
            <input type="checkbox" name="note_alert_dept_manager" <?=$config['note_alert_dept_manager']?'checked':''?>> <?= _('Department Manager') ?>
          </td>
      </tr>
      <tr><th><?= _('Overdue Ticket Alert:') ?></th>
          <td>
            <input type="radio" name="overdue_alert_active"  value="1"   <?=$config['overdue_alert_active']?'checked':''?> /><?= _('Enable') ?>
            <input type="radio" name="overdue_alert_active"  value="0"   <?=!$config['overdue_alert_active']?'checked':''?> /><?= _('Disable') ?>
            <br><i><?= _('The system administrator gets an alert by default. Select additional recipients below') ?></i>&nbsp;<font class="error">&nbsp;<?=$errors['overdue_alert_active']?></font><br>
            <input type="checkbox" name="overdue_alert_assigned" <?=$config['overdue_alert_assigned']?'checked':''?>> <?= _('Assigned Staff') ?>
            <input type="checkbox" name="overdue_alert_dept_manager" <?=$config['overdue_alert_dept_manager']?'checked':''?>> <?= _('Department Manager') ?>
            <input type="checkbox" name="overdue_alert_dept_members" <?=$config['overdue_alert_dept_members']?'checked':''?>> <?= _('Department Members (spammy)') ?>
          </td>
      </tr>
      <tr><th><?= _('System Errors:') ?></th>
          <td><i><?= _('Enabled errors are sent to the system administrator') ?></i><br>
            <input type="checkbox" name="send_sys_errors" <?=$config['send_sys_errors']?'checked':'checked'?> disabled><?= _('System Errors') ?>
            <input type="checkbox" name="send_sql_errors" <?=$config['send_sql_errors']?'checked':''?>><?= _('SQL errors') ?>
            <input type="checkbox" name="send_login_errors" <?=$config['send_login_errors']?'checked':''?>><?= _('Excessive Login attempts') ?>
          </td>
      </tr> 
  </table>
  <div class="centered">
    <input class="button" type="submit" name="submit" value="<?= _('Save Changes') ?>">
    <input class="button" type="reset" name="reset" value="<?= _('Reset Changes') ?>">
  </div>
</form>