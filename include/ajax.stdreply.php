<?php
/*********************************************************************
    ajax.stdreply.php

    AJAX interface for standard replies related...allowed methods.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

if(!defined('OSTAJAXINC') || !defined('INCLUDE_DIR')) die('!');
	    
class StdreplyAjaxAPI{
    
    function cannedResp($params) {
       
	    $sql='SELECT answer FROM '.STD_REPLY_TABLE.' WHERE isenabled=1 AND stdreply_id='.db_input($params['id']);
	    if(($res=db_query($sql)) && db_num_rows($res))
		    list($response)=db_fetch_row($res);

        if($response && $params['tid'] && strpos($response,'%')!==false) {
            include_once(INCLUDE_DIR.'class.ticket.php');

            $ticket = new Ticket($params['tid']);
            if($ticket && $ticket->getId()){
                $response=$ticket->replaceTemplateVars($response);
            }
        }

        return $response;
	}
}
?>
