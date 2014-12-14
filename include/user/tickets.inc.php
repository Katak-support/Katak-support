<?php
if(!defined('KTKUSERINC') || !is_object($thisuser) || !$thisuser->isValid()) die('Adiaux amikoj!');

$qstr='&'; //Query string collector

//Restrict based on email of the user...STRICT!
$qwhere =' WHERE email='.db_input($thisuser->getEmail());

//Translate the order requests to db-fields
$sortOptions=array('date'=>'ticket.created','ID'=>'ticketID','dept'=>'dept_name','status'=>'ticket.status');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');

//Sorting and limit options...
if($_REQUEST['sort']) {
  $order_by =$sortOptions[$_REQUEST['sort']];
}
if(($_REQUEST['sort'] == 'status') OR ($_REQUEST['sort'] == 'dept'))
  $order2=", ticket.created DESC ";
else
  $order2="";

if($_REQUEST['order']) {
  $order=$orderWays[$_REQUEST['order']];
}
if($_GET['limit']){
  $qstr.='&limit='.urlencode($_GET['limit']);
}

$order_by =$order_by?$order_by:'ticket.created';
$order=$order?$order:'DESC';
$pagelimit=$_GET['limit']?$_GET['limit']:PAGE_LIMIT;
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;

$qselect = 'SELECT ticket.ticket_id,ticket.ticketID,ticket.dept_id,isanswered,ispublic,subject,name,closed '.
           ',dept_name,status,ticket.created,lastresponse ';
$qfrom=' FROM '.TICKET_TABLE.' ticket LEFT JOIN '.DEPT_TABLE.' dept ON ticket.dept_id=dept.dept_id ';
//Pagenation stuff....wish MYSQL could auto pagenate (something better than limit)
$total=db_count('SELECT count(*) '.$qfrom.' '.$qwhere);
$pageNav=new PageNate($total,$page,$pagelimit);
$pageNav->setURL('tickets.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));

//Ok..lets roll...create the actual query
$qselect.=' ,count(attach_id) as attachments ';
$qfrom.=' LEFT JOIN '.TICKET_ATTACHMENT_TABLE.' attach ON  ticket.ticket_id=attach.ticket_id ';
$qgroup=' GROUP BY ticket.ticket_id';
$query="$qselect $qfrom $qwhere $qgroup ORDER BY $order_by $order $order2 LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
$tickets_res = db_query($query);
$showing=db_num_rows($tickets_res)?$pageNav->showing():"";
$negorder=$order=='DESC'?'ASC':'DESC'; //Negate the sorting..
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
<div class='msg'>
  <?=$showing?>&nbsp;&nbsp;<?=_('tickets')?> &nbsp; &nbsp;
  <a href="" title="<?=_('Reload') ?>"><span class="Icon refresh">&nbsp;</span></a>
</div>
<table width="100%" cellspacing=0 cellpadding=3 class="tgrid" align="center">
  <tr>
    <th align="center" nowrap><a href="tickets.php?sort=ID&order=<?=$negorder?><?=$qstr?>" title="<?= _('Sort by Ticket Number')?> <?=$negorder?>"><?= _('Ticket #')?></a></th>
    <th align="center"><a href="tickets.php?sort=date&order=<?=$negorder?><?=$qstr?>" title="<?= _('Sort by Date of Creation')?> <?=$negorder?>"><?= _('Create Date')?></a></th>
    <th align="center"><a href="tickets.php?sort=status&order=<?=$negorder?><?=$qstr?>" title="<?= _('Sort by Status')?> <?=$negorder?>"><?= _('Status')?></a></th>
    <th>&nbsp;<a href="tickets.php?sort=dept&order=<?=$negorder?><?=$qstr?>" title="<?= _('Sort by Department')?> <?=$negorder?>"><?= _('Department')?></a></th>
    <th>&nbsp;<?= _('Subject')?></th>
  </tr>
  <?php
  $total=0;
  if($tickets_res && ($num=db_num_rows($tickets_res))):
    $defaultDept=Dept::getDefaultDeptName();
    // display the tickets
    while ($row = db_fetch_array($tickets_res)) {
      $dept=$row['ispublic']?$row['dept_name']:$defaultDept; //Don't show hidden/non-public depts.
      $subject=Format::htmlchars(Format::truncate($row['subject'],48));
      $ticketID=$row['ticketID'];
      if($row['isanswered'] && !strcasecmp($row['status'],'open')) {
        $subject="<b>$subject</b>";
        $ticketID="<b>$ticketID</b>";
      }
      ?>
      <tr class="row" id="<?=$row['ticketID']?>">
        <td align="center"><a href="tickets.php?id=<?=$row['ticketID']?>"><?=$ticketID?></a></td>
        <td align="center" nowrap><?=Format::db_date($row['created'])?></td>
              
        <td align="center">
          <?php
          if($row['status']=='closed')
            echo "<span class='Icon closedTicket' title='"._('Closed on ').Format::db_date($row['closed'])."'>&nbsp;</span>";
          elseif ($row['isanswered'] && !strcasecmp($row['status'],'open'))
            echo "<span class='Icon answeredTicket' title='"._('Open - Answered on ').Format::db_date($row['lastresponse'])."'>&nbsp;</span>";
          else
            echo "<span class='Icon openTicket' title="._('Open').">&nbsp;</span>";
          ?>
        </td>
        <td nowrap>&nbsp;<?=Format::truncate($dept,30)?></td>
        <td>
          &nbsp;<a href="tickets.php?id=<?=$row['ticketID']?>"><?=$subject?></a>
          &nbsp;<?=$row['attachments']?"<span class='Icon file'>&nbsp;</span>":''?>
        </td>
      </tr>
      <?php
    } //end of while.
    else: //not tickets found!! ?> 
      <tr class="<?=$class?>"><td colspan=7><b><?= _('NO tickets found.')?></b></td></tr>
    <?php
    endif; ?>
</table>
<?php
if($num>0 && $pageNav->getNumPages()>1){ //if we actually had any tickets returned ?>
  <div style="text-align:left;padding-left:20px">page:<?=$pageNav->getPageLinks()?>&nbsp;</div>
<?php } ?>
