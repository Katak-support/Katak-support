<?php
if(!defined('KTKADMININC') || !@$thisuser->isStaff()) die(_('Access Denied'));

$qstr='&'; //Query string collector
if(isset($_REQUEST['status'])) { //Query string status has nothing to do with the real status used below; gets overloaded.
  $qstr.='status='.urlencode($_REQUEST['status']);}
else {
  $_REQUEST['status'] = 'open';
}

//See if this is a search
$search=$_REQUEST['a']=='search'?true:false;
$searchTerm='';
//make sure the search query is 3 chars min...defaults to no query with warning message
if($search) {
  $searchTerm=$_REQUEST['query'];
  if( ($_REQUEST['query'] && strlen($_REQUEST['query'])<3) || (!$_REQUEST['query'] && isset($_REQUEST['basic_search'])) ) {
      $search=false; //Instead of an error page...default back to regular query..with no search.
      $errors['err']=_('Search term must be more than 3 chars');
      $searchTerm='';
  }
}
$showoverdue=false;
$staffId=0; //Nothing for now...TODO: Allow admin and manager to limit tickets to single staff level.
//Get status we are actually going to use on the query...making sure it is clean!
$status=null;
switch(strtolower($_REQUEST['status'])){ //Status is overloaded
    case 'open':
        $status='open';
        break;
    case 'closed':
        $status='closed';
        break;
    case 'overdue':
        $status='open';
        $showoverdue=true;
        $results_type=_('Overdue Tickets');
        break;
    case 'assigned':
        $status='open';
        $staffId=$thisuser->getId();
        break;
    default:
        if(!$search)
            $status='open';
}

if($stats) {
    if(!$stats['open'] && (!$status || $status=='open')) {//no open tickets (+-queue?) - show closed tickets.
        $status='closed';
        $results_type=_('Closed Tickets');
    }
}

$qwhere ='';
/* DEPTS
   STRICT DEPARTMENTS BASED (a.k.a Categories) PERM. starts the where 
   if dept returns nothing...show only tickets without dept which could mean..none?
   Note that dept selected on search has nothing to do with departments allowed.
   User can also see tickets assigned to them regardless of the ticket's dept.
*/
$depts=$thisuser->getDeptsId(); //if dept returns nothing...show only tickets without dept which could mean..none...and display an error. ??
if(!$depts or !is_array($depts) or !count($depts)){
    //if dept returns nothing...show only orphaned tickets (without dept).
    $qwhere =' WHERE ticket.dept_id IN ( 0 ) ';
}elseif($thisuser->isadmin()){
    //administrators are allowed to access to all departments and all tickets.
    if($_REQUEST['dept']) { //department based search
        $qwhere = ' WHERE ticket.dept_id='.db_input($_REQUEST['dept']);
        $qstr .= '&dept='.urlencode($_REQUEST['dept']);
    }
    else {
        $qwhere = ' WHERE 1=1';
    }
    if($staffId) { //assigned tickets
        $results_type=_('Assigned Tickets');
        $qwhere .= ' AND ticket.staff_id='.db_input($staffId).' AND status=\'open\'';    
    }
    elseif($showoverdue) //overdue
        $qwhere .= ' AND isoverdue=1 AND status=\'open\'';
    elseif($status) {//open or closed
      $qwhere .= ' AND status='.db_input(strtolower($status));    
    }
}elseif($thisuser->isManager()){
    //Dept. managers are allowed to see all the tickets of their own dept.
    if($_REQUEST['dept'] && (in_array($_REQUEST['dept'],$thisuser->getDeptsId()))) {   //department based search
        $qwhere = ' WHERE ticket.dept_id='.db_input($_REQUEST['dept']);
        $qstr .= '&dept='.urlencode($_REQUEST['dept']);
    }
    else {
        $qwhere = ' WHERE (ticket.dept_id IN ('.implode(',',$depts).') OR ticket.staff_id='.$thisuser->getId().')';
    }
    if($staffId) { //assigned
        $results_type=_('Assigned Tickets');
        $qwhere .= ' AND ticket.staff_id='.db_input($staffId).' AND status=\'open\'';    
    }
    elseif($showoverdue) //overdue
        $qwhere .= ' AND isoverdue=1 AND status=\'open\'';
    elseif($status) { //open or closed
      $qwhere .= ' AND status='.db_input(strtolower($status)); 
    }   
}elseif(!$thisuser->canViewunassignedTickets()){
    //staff users limited to tickets assigned to them regardless of the dept.
    if($_REQUEST['dept'] && (in_array($_REQUEST['dept'],$thisuser->getDeptsId()))) {   //department based search
        $qwhere = ' WHERE (ticket.dept_id='.db_input($_REQUEST['dept']).' AND ticket.staff_id='.$thisuser->getId().')';
        $qstr .= '&dept='.urlencode($_REQUEST['dept']);
    }
    else {
        $qwhere = ' WHERE ticket.staff_id='.$thisuser->getId();
    } 
    if($staffId) { //assigned
        $results_type=_('Assigned Tickets');
        $qwhere .= ' AND status=\'open\'';  
    }  
    elseif($showoverdue) //overdue
        $qwhere .= ' AND isoverdue=1 AND status=\'open\'';
    elseif($status) { //open or closed
        $qwhere .= ' AND status='.db_input(strtolower($status)); 
    } 
}else{
    //staff users with access to unassigned ticket of their dept.
    if($_REQUEST['dept'] && (in_array($_REQUEST['dept'],$thisuser->getDeptsId()))) {   //department based search
        $qwhere = ' WHERE ticket.dept_id='.db_input($_REQUEST['dept']). ' AND (ticket.staff_id=0 OR ticket.staff_id='.$thisuser->getId().')';
        $qstr .= '&dept='.urlencode($_REQUEST['dept']);
    }
    else { // own dept. tickets or unassigned
        $qwhere = ' WHERE (ticket.staff_id='.$thisuser->getId().' OR (ticket.dept_id IN ('.implode(',',$depts).') AND ticket.staff_id=0))';
    }
    if($staffId) { //assigned
        $results_type=_('Assigned Tickets');
        $qwhere .= ' AND (ticket.staff_id='.db_input($staffId).' AND status=\'open\')';    
    }
    elseif($showoverdue) //overdue
        $qwhere .= ' AND isoverdue=1 AND status=\'open\'';
    elseif($status) { //open or closed
        $qwhere .= ' AND status='.db_input(strtolower($status));
    }   
}

//Search
$deep_search=false;
if($search):
    $qstr.='&a='.urlencode($_REQUEST['a']);
    $qstr.='&t='.urlencode($_REQUEST['t']);
    if(isset($_REQUEST['advance_search'])){ //advance search box!
        $qstr.='&advance_search=Search';
    }

    //query
    if($searchTerm){
        $qstr.='&query='.urlencode($searchTerm);
        $queryterm=db_real_escape($searchTerm,false); //escape the term ONLY...no quotes.
        if(is_numeric($searchTerm)){
            $qwhere.=" AND ticket.ticketID LIKE '$queryterm%'";
        }elseif(strpos($searchTerm,'@') && Validator::is_email($searchTerm)){ //pulling all tricks!
            $qwhere.=" AND ticket.email='$queryterm'";
        }else{//Deep search!
            //This sucks..mass scan! search anything that moves! 
            
            $deep_search=true;
            if($_REQUEST['stype'] && $_REQUEST['stype']=='FT') { //Using full text on big fields.
                $qwhere.=" AND ( ticket.email LIKE '%$queryterm%'".
                            " OR ticket.name LIKE '%$queryterm%'".
                            " OR ticket.subject LIKE '%$queryterm%'".
                            " OR note.title LIKE '%$queryterm%'".
                            " OR MATCH(message.message) AGAINST('$queryterm')".
                            " OR MATCH(note.note) AGAINST('$queryterm')".
                            ' ) ';
            }else{
                $qwhere.=" AND ( ticket.email LIKE '%$queryterm%'".
                            " OR ticket.name LIKE '%$queryterm%'".
                            " OR ticket.subject LIKE '%$queryterm%'".
                            " OR message.message LIKE '%$queryterm%'".
                            " OR note.note LIKE '%$queryterm%'".
                            " OR note.title LIKE '%$queryterm%'".
                            ' ) ';
            }
        }
    }
    //dates
    $startTime  =($_REQUEST['startDate'] && (strlen($_REQUEST['startDate'])>=8))?strtotime($_REQUEST['startDate']):0;
    $endTime    =($_REQUEST['endDate'] && (strlen($_REQUEST['endDate'])>=8))?strtotime($_REQUEST['endDate']):0;
    if( ($startTime && $startTime>time()) or ($startTime>$endTime && $endTime>0)){
        $errors['err']='Entered date span is invalid. Selection ignored.';
        $startTime=$endTime=0;
    }else{
        //Have fun with dates.
        if($startTime){
            $qwhere.=' AND ticket.created>=FROM_UNIXTIME('.$startTime.')';
            $qstr.='&startDate='.urlencode($_REQUEST['startDate']);
                        
        }
        if($endTime){
            $qwhere.=' AND ticket.created<=FROM_UNIXTIME('.$endTime.')';
            $qstr.='&endDate='.urlencode($_REQUEST['endDate']);
        }
}

endif;

//Sorting options list
$sortOptions=array('date'=>'ticket.created','ID'=>'ticketID','pri'=>'priority_urgency','dept'=>'dept_name');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');

//Sorting options...
if($_REQUEST['sort']) {
    $order_by =$sortOptions[$_REQUEST['sort']];
}
if($_REQUEST['order']) {
    $order=$orderWays[$_REQUEST['order']];
}
if($_GET['limit']){
    $qstr.='&limit='.urlencode($_GET['limit']);
}
if(!$order_by && !strcasecmp($status,'closed')){
    $order_by='ticket.closed DESC, ticket.created'; //No priority sorting for closed tickets.
}elseif(!$order_by) {
    $order_by='ticket.lastresponse DESC, ticket.created';
}


$order_by =$order_by?$order_by:'priority_urgency,effective_date DESC ,ticket.created';
$order=$order?$order:'DESC';
$pagelimit=$_GET['limit']?$_GET['limit']:$thisuser->getPageLimit();
$pagelimit=$pagelimit?$pagelimit:PAGE_LIMIT; //true default...if all fails.
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;


$qselect = 'SELECT DISTINCT ticket.ticket_id,lock_id,ticketID,ticket.dept_id,ticket.staff_id,subject,ticket.name,ticket.email,dept_name,lastresponse '.
           ',ticket.status,ticket.source,isoverdue,isanswered,ticket.created,pri.* ,count(attach.attach_id) as attachments '.
           ',staff.firstname,staff.lastname';
$qfrom=' FROM '.TICKET_TABLE.' ticket '.
       ' LEFT JOIN '.DEPT_TABLE.' dept ON ticket.dept_id=dept.dept_id ';

if($search && $deep_search) {
    $qfrom.=' LEFT JOIN '.TICKET_MESSAGE_TABLE.' message ON (ticket.ticket_id=message.ticket_id )';
    $qfrom.=' LEFT JOIN '.TICKET_EVENTS_TABLE.' note ON (ticket.ticket_id=note.ticket_id )';
}

$qgroup=' GROUP BY ticket.ticket_id';
//get ticket count based on the query so far..
$total=db_count("SELECT count(DISTINCT ticket.ticket_id) $qfrom $qwhere");
//pagenate
$pageNav=new PageNate($total,$page,$pagelimit);
$pageNav->setURL('tickets.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));

//Ok..lets roll...create the actual query
//ADD attachment,priorities and lock crap
$qselect.=' , IF(ticket.reopened is NULL,ticket.created,ticket.reopened) as effective_date';
$qfrom.=' LEFT JOIN '.PRIORITY_TABLE.' pri ON ticket.priority_id=pri.priority_id '.
        ' LEFT JOIN '.TICKET_LOCK_TABLE.' tlock ON ticket.ticket_id=tlock.ticket_id AND tlock.expire>NOW() '.
        ' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON  ticket.ticket_id=attach.ticket_id '.
        ' LEFT JOIN '.STAFF_TABLE.' staff ON  ticket.staff_id=staff.staff_id ';

$query="$qselect $qfrom $qwhere $qgroup ORDER BY $order_by $order LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
// echo $query;
$tickets_res = db_query($query);
$showing=db_num_rows($tickets_res)?$pageNav->showing():"";
if(!$results_type) {
    $results_type=($search)?'Search Results':ucfirst($status).' Tickets';
}
$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting..

//Permission  setting we are going to reuse.
$canDelete=$canClose=false;
$canDelete=$thisuser->canDeleteTickets();
$canClose=$thisuser->canCloseTickets();
$basic_display=!isset($_REQUEST['advance_search'])?true:false;

//YOU BREAK IT YOU FIX IT.
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

<!-- SEARCH FORM: START -->
<div id="basic" style="display:<?=$basic_display?'block':'none'?>; float:right">
    <form action="tickets.php" method="get">
      <input type="hidden" name="a" value="search">
      <div>
            <span><?= _('Query:') ?> </span>
            <span><input type="text" class="query" name="query" size=30 value="<?=Format::htmlchars($_REQUEST['query'])?>"></span>
            <span><input type="submit" name="basic_search" class="button" value="<?= _('Search') ?>">
                &nbsp;[<a href="#" onClick="showHide('basic','advance'); return false;"><?= _('Advanced') ?></a> ] </span>
      </div>
    </form>
</div>
<div id="advance" style="display:<?=$basic_display?'none':'block'?>; float:right">
  <form action="tickets.php" method="get">
  <input type="hidden" name="a" value="search">
  <table>
    <tr>
      <td><?= _('Query:') ?> </td><td><input type="text" size="30" class="query" name="query" value="<?=Format::htmlchars($_REQUEST['query'])?>"></td>
      <td><?= _('Dept:') ?></td>
      <td><select name="dept"><option value=0><?= _('All Departments') ?></option>
          <?php
          //Showing only departments the user has access to...
          $dp = $thisuser->getDeptsName();
          while (list($deptId,$deptName) = db_fetch_row($dp)){
            $selected = ($_GET['dept']==$deptId)?'selected':''; ?>
          <option value="<?=$deptId?>"<?=$selected?>><?=$deptName?></option>
          <?php
          }?>
          </select>
      </td>
      <td><?= _('Status is:') ?></td>
      <td>
        <select name="status">
            <option value='any' selected ><?= _('Any status') ?></option>
            <option value="open" <?=!strcasecmp($_REQUEST['status'],'Open')?'selected':''?>><?= _('Open') ?></option>
            <option value="overdue" <?=!strcasecmp($_REQUEST['status'],'overdue')?'selected':''?>><?= _('Overdue') ?></option>
            <option value="closed" <?=!strcasecmp($_REQUEST['status'],'Closed')?'selected':''?>><?= _('Closed') ?></option>
        </select>
      </td>
    </tr>
  </table>
  <div>
    <?= _('Date Span:') ?>
    &nbsp;<?= _('From') ?>&nbsp;<input id="sd" name="startDate" value="<?=Format::htmlchars($_REQUEST['startDate'])?>"
            onclick="event.cancelBubble=true;calendar(this);" autocomplete=OFF>
        <a href="#" onclick="event.cancelBubble=true;calendar(getObj('sd')); return false;"><img src='images/cal.png'border=0 alt=""></a>
        &nbsp;&nbsp; <?= _('to') ?> &nbsp;&nbsp;
        <input id="ed" name="endDate" value="<?=Format::htmlchars($_REQUEST['endDate'])?>" 
            onclick="event.cancelBubble=true;calendar(this);" autocomplete=OFF >
            <a href="#" onclick="event.cancelBubble=true;calendar(getObj('ed')); return false;"><img src='images/cal.png'border=0 alt=""></a>
        &nbsp;&nbsp;
  </div>
  <table>
    <tr>
      <td><?= _('Type:') ?></td>
      <td>       
        <select name="stype">
            <option value="LIKE" <?=(!$_REQUEST['stype'] || $_REQUEST['stype'] == 'LIKE') ?'selected':''?>><?= _('Scan') ?> (%)</option>
            <option value="FT"<?= $_REQUEST['stype'] == 'FT'?'selected':''?>><?= _('Fulltext') ?></option>
        </select>   
      </td>
      <td><?= _('Sort by:') ?></td><td>
          <?php 
           $sort=$_GET['sort']?$_GET['sort']:'date';
          ?>
          <select name="sort">
              <option value="ID" <?= $sort== 'ID' ?'selected':''?>><?= _('Ticket #') ?></option>
              <option value="pri" <?= $sort == 'pri' ?'selected':''?>><?= _('Priority') ?></option>
              <option value="date" <?= $sort == 'date' ?'selected':''?>><?= _('Date') ?></option>
              <option value="dept" <?= $sort == 'dept' ?'selected':''?>><?= _('Dept.') ?></option>
          </select>
          <select name="order">
              <option value="DESC"<?= $_REQUEST['order'] == 'DESC' ?'selected':''?>><?= _('Descending') ?></option>
              <option value="ASC"<?= $_REQUEST['order'] == 'ASC'?'selected':''?>><?= _('Ascending') ?></option>
          </select>
      </td>
      <td><?= _('Results Per Page:') ?></td><td>
          <select name="limit">
          <?php
           $sel=$_REQUEST['limit']?$_REQUEST['limit']:15;
           for ($x = 5; $x <= 25; $x += 5) {?>
              <option  value="<?=$x?>" <?=($sel==$x )?'selected':''?>><?=$x?></option>
          <?php } ?>
          </select>
      </td>
      <td>
         <input type="submit" name="advance_search" class="button" value="<?= _('Search') ?>">
         &nbsp;[ <a href="#" onClick="showHide('advance','basic'); return false;" ><?= _('Basic') ?></a> ]
      </td>
    </tr>
  </table>
  </form>
</div>

<script type="text/javascript">
    var options = {
        script:"ajax.php?api=tickets&f=search&limit=10&",
        varname:"input",
        shownoresults:false,
        maxresults:10,
        callback: function (obj) { document.getElementById('query').value = obj.id; document.forms[0].submit();}
    };
    var autosug = new bsn.AutoSuggest('query', options);
</script>

<br style="clear:both;" />
<!-- SEARCH FORM: END -->

<div class="msg" style="padding-left:12px">
  <a href="" class="Icon refresh"></a>
  <?=$showing?>&nbsp;&nbsp;&nbsp;<?=$results_type?>
</div>
<form action="tickets.php" method="POST" name='tickets' onSubmit="return checkbox_checker(this,1,0);">
  <input type="hidden" name="a" value="mass_process" >
  <input type="hidden" name="status" value="<?=$statusss?>" >
  <table width="100%" border="0" cellspacing=0 cellpadding=2 class="dtable" align="center">
    <tr>
      <?php if($canDelete || $canClose) { ?>
        <th class="box" width="8px">&nbsp;</th>
      <?php } ?>
      <th width="68" nowrap>&nbsp;<?= _('Status') ?></th>
      <th width="54"><a href="tickets.php?sort=ID&order=<?=$negorder?><?=$qstr?>" title="<?= _('Sort By Ticket ID') ?> <?=$negorder?>">&nbsp;<?= _('Ticket') ?></a></th>
      <th width="72"><a href="tickets.php?sort=date&order=<?=$negorder?><?=$qstr?>" title="<?= _('Sort By Date') ?> <?=$negorder?>"><?= _('Date') ?></a></th>
      <th width="270"><?= _('Subject') ?></th>
      <th width="170"><?= _('From') ?></th>
      <th width="124"><a href="tickets.php?sort=dept&order=<?=$negorder?><?=$qstr?>" title="<?= _('Sort By Category') ?> <?=$negorder?>"><?= _('Department') ?></a></th>
      <th width="58"><a href="tickets.php?sort=pri&order=<?=$negorder?><?=$qstr?>" title="<?= _('Sort By Priority') ?> <?=$negorder?>"><?= _('Priority') ?></a></th>
    </tr>
    <?php
    $class = "row1";
    $total=0;
    if($tickets_res && ($num=db_num_rows($tickets_res))):
        while ($row = db_fetch_array($tickets_res)) {
            $tag=$row['staff_id']?'assigned':'openticket';
    
            $tid=$row['ticketID'];
            $subject = Format::truncate($row['subject'],40);
            if(!strcasecmp($row['status'],'open') && !$row['isanswered'] && !$row['lock_id']) {
                $tid=sprintf('<b>%s</b>',$tid);
                //$subject=sprintf('<b>%s</b>',Format::truncate($row['subject'],40)); // Making the subject bold is too much for the eye
            }
            ?>
        <tr class="<?=$class?>" id="<?=$row['ticket_id']?>">
            <?php if($canDelete || $canClose) { ?>
            <td class="box" align="center" class="nohover">
                <input type="checkbox" name="tids[]" value="<?=$row['ticket_id']?>" onClick="highLight(this.value,this.checked);">
            </td>
            <?php } ?>
            <td><?php $row['isoverdue']? print('<img src="images/icons/overdue_ticket.gif" title="'._('Overdue').'">'):print('<img src="images/icons/void8.gif" title="" >')?>
                <?php $row['isanswered']? print('<img src="images/icons/answered_tickets.gif" title="'._('Answered on').' '.Format::db_date($row['lastresponse']).'">'):print('<img src="images/icons/void12.gif" title="" >')?>
                <?php $row['staff_id']? print('<img src="images/icons/assigned_ticket.gif" title="'._('Assigned to').' '.$row['firstname'].' '.$row['lastname'].'">'):print('<img src="images/icons/void.gif" title="" >')?>
                <img src="images/icons/ticket_source_<?=strtolower($row['source'])?>.gif" title="<?=$row['source']?> ticket"></td>
            <td align="center" nowrap><a title="<?=$row['source']?> <?= _('Ticket from:') ?> <?=$row['email']?>"
                href="tickets.php?id=<?=$row['ticket_id']?>"><?=$tid?></a></td>
            <td align="center" nowrap><?=Format::db_date($row['created'])?></td>
            <td><a <?php if($row['lock_id']) { ?> class="Icon lockedTicket"  title="<?=_('Locked Ticket')?>" <?php } ?>
                href="tickets.php?id=<?=$row['ticket_id']?>"><?=$subject?></a>
                &nbsp;<?=$row['attachments']?"<span class='Icon file'>&nbsp;</span>":''?></td>
            <td nowrap><?=Format::truncate($row['name'],22,strpos($row['name'],'@'))?>&nbsp;</td>
            <td nowrap><?=Format::truncate($row['dept_name'],30)?></td>
            <td class="nohover" align="center" style="color:<?=$row['priority_color']?>;"><?=$row['priority_desc']?></td>
        </tr>
        <?php
        $class = ($class =='row2') ?'row1':'row2';
        } //end of while.
    else: //not tickets found!! ?> 
        <tr class="<?=$class?>"><td colspan=8><b><?= _('Query returned 0 results.') ?></b></td></tr>
    <?php
    endif; ?>
  </table>
  <?php
  if($num>0){ //if we actually had any tickets returned.
    if($canDelete || $canClose) { ?>
    <span id="togglebox">
      <?= _('Select:')." &nbsp; " ?>
      [<a href="#" onclick="return select_all(document.forms['tickets'],true)"><?= _('All') ?></a>]&nbsp;
      [<a href="#" onclick="return reset_all(document.forms['tickets'])"><?= _('None') ?></a>]&nbsp;
      [<a href="#" onclick="return toogle_all(document.forms['tickets'],true)"><?= _('Toggle') ?></a>]&nbsp;
    </span>
    <?php } ?>
    <span style="float:right; padding-right:4px;"><?= _('page:') ?><?=$pageNav->getPageLinks()?></span>
    <?php if($canClose or $canDelete) { ?>
    <div id="buttonsline" style="text-align:center">
      <?php
      $status=$_REQUEST['status']?$_REQUEST['status']:$status;

      //If the user can close the ticket...mass reopen is allowed.
      //If they can delete tickets...they are allowed to close--reopen..etc.
      switch (strtolower($status)) {
          case 'closed': ?>
            <input class="button" type="submit" name="reopen" value="<?= _('Reopen') ?>"
                 onClick=' return confirm("<?= _('Are you sure you want to reopen selected tickets?') ?>");'>
              <?php
              break;
          case 'open':
          case 'assigned':
              ?>
            <input class="button" type="submit" name="overdue" value="<?= _('Overdue') ?>"
                  onClick=' return confirm("<?= _('Are you sure you want to mark selected tickets overdue/stale?') ?>");'>
            <input class="button" type="submit" name="close" value="<?= _('Close') ?>"
                  onClick=' return confirm("<?= _('Are you sure you want to close selected tickets?') ?>");'>
              <?php
              break;
          default: //search??
              ?>
            <input class="button" type="submit" name="close" value="<?= _('Close') ?>"
                  onClick=' return confirm("<?= _('Are you sure you want to close selected tickets?') ?>");'>
            <input class="button" type="submit" name="reopen" value="<?= _('Reopen') ?>"
                  onClick=' return confirm("<?= _('Are you sure you want to reopen selected tickets?') ?>");'>
      <?php
      }
      if($canDelete) {?>
          <input class="button" type="submit" name="delete" value="<?= _('Delete') ?>"
              onClick=' return confirm("<?= _('Are you sure you want to DELETE selected tickets?') ?>");'>
      <?php } ?>
    </div>
    <?php }
  } ?>
</form>
