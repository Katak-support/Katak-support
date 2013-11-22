<?php
if (!defined('KTKADMININC') or !is_object($thisuser) or !$thisuser->canManageStdr())
    die(_('Access Denied'));

//List standard replies.
$select = 'SELECT stdreply.*,dept_name ';
$from = 'FROM ' . STD_REPLY_TABLE . ' stdreply LEFT JOIN ' . DEPT_TABLE . ' USING(dept_id) ';

//make sure the search query is 3 chars min...defaults to no query with warning message
if ($_REQUEST['a'] == 'search') {
    if (!$_REQUEST['query'] || strlen($_REQUEST['query']) < 3) {
        $errors['err'] = _('Search term must be more than 3 chars');
    } else {
        //fulltext search.
        $search = true;
        $qstr.='&a=' . urlencode($_REQUEST['a']);
        $qstr.='&query=' . urlencode($_REQUEST['query']);
        $where = ' WHERE MATCH(title,answer) AGAINST (' . db_input($_REQUEST['query']) . ')';
        if ($_REQUEST['dept'])
            $where.=' AND dept_id=' . db_input($_REQUEST['dept']);
    }
}

//I admit this crap sucks...but who cares??
$sortOptions = array('createdate' => 'stdreply.created', 'updatedate' => 'stdreply.updated', 'title' => 'stdreply.title');
$orderWays = array('DESC' => 'DESC', 'ASC' => 'ASC');
//Sorting options...
if ($_REQUEST['sort']) {
    $order_column = $sortOptions[$_REQUEST['sort']];
}

if ($_REQUEST['order']) {
    $order = $orderWays[$_REQUEST['order']];
}


$order_column = $order_column ? $order_column : 'stdreply.title';
$order = $order ? $order : 'DESC';

$order_by = $search ? '' : " ORDER BY $order_column $order ";


$total = db_count('SELECT count(*) ' . $from . ' ' . $where);
$pagelimit = $thisuser->getPageLimit();
$pagelimit = $pagelimit ? $pagelimit : PAGE_LIMIT; //true default...if all fails.
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$pageNav = new PageNate($total, $page, $pagelimit);
$pageNav->setURL('stdreply.php', $qstr . '&sort=' . urlencode($_REQUEST['sort']) . '&order=' . urlencode($_REQUEST['order']));
//Ok..lets roll...create the actual query
$query = "$select $from $where $order_by LIMIT " . $pageNav->getStart() . "," . $pageNav->getLimit();
//echo $query;
$replies = db_query($query);
$showing = db_num_rows($replies) ? $pageNav->showing() : '';
$results_type = ($search) ? _('Search Results') : _('Standard/Canned Replies');
$negorder = $order == 'DESC' ? 'ASC' : 'DESC'; //Negate the sorting..
?>
<div>
    <?php if ($errors['err']) {
    ?>
        <p align="center" id="errormessage"><?= $errors['err'] ?></p>
    <?php } elseif ($msg) {
 ?>
        <p align="center" id="infomessage"><?= $msg ?></p>
<?php } elseif ($warn) { ?>
        <p id="warnmessage"><?= $warn ?></p>
<?php } ?>
</div>
<div align="left">
  <form action="stdreply.php" method="GET" >
    <input type='hidden' name='a' value='search'>
    <?= _('Search for') ?>:&nbsp;<input type="text" name="query" value="<?= Format::htmlchars($_REQUEST['query']) ?>">
    <?= _('category') ?>
    <select name="dept">
        <option value=0><?= _('All Departments') ?></option>
        <?php
        $depts = db_query('SELECT dept_id,dept_name FROM ' . DEPT_TABLE . ' WHERE dept_id!=' . db_input($ticket['dept_id']));
        while (list($deptId, $deptName) = db_fetch_row($depts)) {
            $selected = ($_GET['dept'] == $deptId) ? 'selected' : '';
        ?>
            <option value="<?= $deptId ?>"<?= $selected ?>>&nbsp;&nbsp;<?= $deptName ?></option>
        <?php } ?>
    </select>
    &nbsp;
    <input type="submit" name="search" class="button" value="<?= _('GO') ?>">
  </form>
</div>
<div class="msg"><?= $result_type ?>&nbsp;<?= $showing ?></div>
<form action="stdreply.php" method="POST" name="stdreply" onSubmit="return checkbox_checker(document.forms['stdreply'],1,0);">
  <input type=hidden name='a' value='process'>
  <table border="0" cellspacing=0 cellpadding=2 class="dtable" align="center" width="100%">
      <tr>
          <th width="7px">&nbsp;</th>
          <th><a href="stdreply.php?sort=title&order=<?= $negorder ?><?= $qstr ?>" title="<?= _('Sort By Title') ?> <?= $negorder ?>"><?= _('Reply Title') ?></a></th>
          <th width=50><?= _('Status') ?></th>
          <th width=200><?= _('Category/Dept') ?></th>
          <th width=150 nowrap><a href="stdreply.php?sort=updatedate&order=<?= $negorder ?><?= $qstr ?>" title="<?= _('Sort By Update Date') ?> <?= $negorder ?>"><?= _('Last Updated') ?></a></th>
      </tr>
      <?php
      $class = 'row1';
      $total = 0;
      $grps = ($errors && is_array($_POST['grps'])) ? $_POST['grps'] : null;
      if ($replies && db_num_rows($replies)):
          while ($row = db_fetch_array($replies)) {
              $sel = false;
              if ($canned && in_array($row['stdreply_id'], $canned)) {
                  $class = "$class highlight";
                  $sel = true;
              } elseif ($replyID && $replyID == $row['stdreply_id']) {
                  $class = "$class highlight";
              }
      ?>
      <tr class="<?= $class ?>" id="<?= $row['stdreply_id'] ?>">
          <td width=7px>
              <input type="checkbox" name="canned[]" value="<?= $row['stdreply_id'] ?>" <?= $sel ? 'checked' : '' ?>
                     onClick="highLight(this.value,this.checked);">
          </td>
          <td><a href="stdreply.php?id=<?= $row['stdreply_id'] ?>"><?= Format::htmlchars(Format::truncate($row['title'], 60)) ?></a></td>
          <td><b><?= $row['isenabled'] ? _('Active') : _('Disabled') ?></b></td>
          <td><?= $row['dept_name'] ? Format::htmlchars($row['dept_name']) : 'All Departments' ?></td>
          <td><?= Format::db_datetime($row['updated']) ?></td>
      </tr>
      <?php
              $class = ($class == 'row2') ? 'row1' : 'row2';
          } //end of while.
      else: //nothin' found!!?>
      <tr class="<?= $class ?>">
        <td colspan=6><b><?= _('Query returned 0 results') ?></b></td>
      </tr>
      <?php endif; ?>
  </table>
                  
  <?php
  if (db_num_rows($replies) > 0): //Show options..
  ?>
    <div style="margin-left:20px;">                      
      <?= _('Select:') ?>&nbsp;
      [<a href="#" onclick="return select_all(document.forms['stdreply'],true)"><?= _('All') ?></a>]&nbsp;
      [<a href="#" onclick="return toogle_all(document.forms['stdreply'],true)"><?= _('Toggle') ?></a>]&nbsp;
      [<a href="#" onclick="return reset_all(document.forms['stdreply'])"><?= _('None') ?></a>]&nbsp;
    </div>
    <div class="centered">                     
      <input class="button" type="submit" name="enable" value="<?= _('Enable') ?>"
             onClick='return confirm("<?= _('Are you sure you want to ENABLE selected entries?') ?>");'>
      <input class="button" type="submit" name="disable" value="<?= _('Disable') ?>"
             onClick='return confirm("<?= _('Are you sure you want to DISABLE selected entries?') ?>");'>
      <input class="button" type="submit" name="delete" value="<?= _('Delete') ?>"
             onClick='return confirm("<?= _('Are you sure you want to DELETE selected entries?') ?>");'>
    </div>
    <span style="float:right; padding-right:4px;">
      &nbsp;<?= _('page:') ?><?= $pageNav->getPageLinks() ?>&nbsp;
    </span>
  <?php
  endif;
  ?>
</form>