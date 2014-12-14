<?php
/*********************************************************************
    chart.php

    Configuring and creating report chart. Use the PHPGraphLib library.

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
include("../include/lib/phpgraphlib.php");

// receive data
$data = unserialize(urldecode(stripslashes($_GET['mydata'])));

// instantiate the graph
$graph = new PHPGraphLib(570,380);
  
//configure graph
$graph->addData($data);
$graph->setTitle("Tickets per month");
$graph->setGradient("black", "lime");
$graph->setBarOutlineColor("black");
$graph->setDataValues(TRUE);
$graph->setYValues(false);
$graph->createGraph();
?>