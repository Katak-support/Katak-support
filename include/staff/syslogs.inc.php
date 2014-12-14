<?php
if(!defined('KTKADMININC') || !@$thisuser->isStaff()) die(_('Access Denied'));

$qstr='&t=syslog'; //Query string collector
if($_REQUEST['type']) { 
    $qstr.='&amp;type='.urlencode($_REQUEST['type']);
}

$type=null;

switch(strtolower($_REQUEST['type'])){
    case 'error':
        $title=_('Errors');
        $type=$_REQUEST['type'];
        break;
    case 'warning':
        $title=_('Warnings');
        $type=$_REQUEST['type'];
        break;
    case 'debug':
        $title=_('Debug logs');
        $type=$_REQUEST['type'];
        break;
    default:
        $type=null;
        $title=_('All logs');
}

$qwhere =' WHERE 1';

//Type
if($type){
    $qwhere.=' AND log_type='.db_input($type);    
}

//dates
$startTime  =($_REQUEST['startDate'] && (strlen($_REQUEST['startDate'])>=8))?strtotime($_REQUEST['startDate']):0;
$endTime    =($_REQUEST['endDate'] && (strlen($_REQUEST['endDate'])>=8))?strtotime($_REQUEST['endDate']):0;
if( ($startTime && $startTime>time()) or ($startTime>$endTime && $endTime>0)){
    $errors['err']=_('Entered date span is invalid. Selection ignored.');
    $startTime=$endTime=0;    
}else{
    
    //Have fun with dates.
    
    if($startTime){
    
        $qwhere.=' AND created>=FROM_UNIXTIME('.$startTime.')';
        $qstr.='&startDate='.urlencode($_REQUEST['startDate']);
        
    }
    
    if($endTime){
    
        $qwhere.=' AND created<=FROM_UNIXTIME('.$endTime.')';
        $qstr.='&endDate='.urlencode($_REQUEST['endDate']);

    }
}

$qselect = 'SELECT log.* ';
$qfrom=' FROM '.SYSLOG_TABLE.' log ';
//get log count based on the query so far..
$total=db_count("SELECT count(*) $qfrom $qwhere");
$pagelimit=30;
$page = ($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;  
//pagenate
$pageNav=new PageNate($total,$page,$pagelimit);
$pageNav->setURL('admin.php',$qstr);
$query="$qselect $qfrom $qwhere ORDER BY log.created DESC LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
$result = db_query($query);
$showing=db_num_rows($resp)?$pageNav->showing():"";
?>
<div class="msg"><?= _('System Logs') ?></div>
<div id='filter'  style="margin-bottom:8px;">
  <form action="admin.php?t=syslog" method="get">
    <input type="hidden" name="t" value="syslog" />
    <div style="padding-left:15px;">
      <?= _('Date Span:') ?>
      &nbsp;<?= _('From') ?>&nbsp;<input id="sd" size=15 name="startDate" value="<?=Format::htmlchars($_REQUEST['startDate'])?>"
              onclick="event.cancelBubble=true;calendar(this);" autocomplete=OFF>
          <a href="#" onclick="event.cancelBubble=true;calendar(getObj('sd')); return false;"><img src='images/cal.png'border=0 alt=""></a>
          &nbsp;&nbsp;<?= _('to') ?>
          <input id="ed" size=15 name="endDate" value="<?=Format::htmlchars($_REQUEST['endDate'])?>" 
              onclick="event.cancelBubble=true;calendar(this);" autocomplete=OFF >
              <a href="#" onclick="event.cancelBubble=true;calendar(getObj('ed')); return false;"><img src='images/cal.png'border=0 alt=""></a>
          &nbsp;&nbsp;
          &nbsp;<?= _('Type:') ?>
          <select name='type'>
              <option value="" selected><?= _('All') ?></option>
              <option value="Error" <?=($type=='Error')?'selected="selected"':''?>><?= _('Errors') ?></option>
              <option value="Warning" <?=($type=='Warning')?'selected="selected"':''?>><?= _('Warnings') ?></option>
              <option value="Debug" <?=($type=='Debug')?'selected="selected"':''?>><?= _('Debug') ?></option>
          </select>
          &nbsp;&nbsp;
          <input class="button" type="submit" Value="<?= _('GO') ?>" />
    </div>
  </form>
</div>

<div style="margin-bottom:10px; padding-top:0px;">
  <form action="tickets.php" method="POST" name='tickets' onSubmit="return checkbox_checker(this,1,0);">
    <input type="hidden" name="a" value="mass_process">
    <input type="hidden" name="status" value="<?=$status?>" >
    <table width="100%" border="0" cellspacing=0 cellpadding=2 class="logs" align="center">
      <tr><th><?=$title?></th></tr>
      <?php
      $class = "row1";
      $total=0;
      if($result && ($num=db_num_rows($result))):
        $icons=array('Debug'=>'debugLog','Warning'=>'alertLog','Error'=>'errorLog');
        while ($row = db_fetch_array($result)) {
          $icon=isset($icons[$row['log_type']])?$icons[$row['log_type']]:'debugLog';
          ?>
          <tr class="<?=$class?> " id="<?=$row['log_id']?>">
            <td>
              <a href="javascript:toggleMessage('<?=$row['log_id']?>');">
                <img border="0" align="left" id="img_<?=$row['log_id']?>" src="images/plus.gif">
                <span style="color:000; float: left; width:190px;"><?=Format::db_daydatetime($row['created'])?></span>
                &nbsp;&nbsp;
                <span class="Icon <?=$icon?>"><?=Format::htmlchars($row['title'])?></span>
                <span style="text-align:right;float:right;"><i><?=Format::htmlchars($row['logger'])?>&nbsp; - &nbsp;<?=Format::htmlchars($row['ip_address'])?>&nbsp;&nbsp;</i></span>
              </a>
              <div id="msg_<?=$row['log_id']?>" class="hide">
                <hr>
                <?=Format::display($row['log'])?>
              </div>     
            </td>
          </tr>
          <?php
          $class = ($class =='row2') ?'row1':'row2';
        } //end of while.
      else: //not tickets found!! ?> 
        <tr class="<?=$class?>"><td><b><?= _('Query returned 0 results.') ?></b></td></tr>
      <?php
      endif; ?>
    </table>
    
    <?php
    if($num>0){ ?>
    <?= _('page:') ?><?=$pageNav->getPageLinks()?>
    <?php } ?>
  </form>
</div>