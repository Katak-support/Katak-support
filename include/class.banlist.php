<?php
/*********************************************************************
    class.banlist.php

    Banned emails handle.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

class Banlist {
    
    static function add($email,$submitter='') {
        $sql='INSERT IGNORE INTO '.BANLIST_TABLE.' SET added=NOW(),email='.db_input($email).',submitter='.db_input($submitter);
        return (db_query($sql) && ($id=db_insert_id()))?$id:0;
    }
    
    static function remove($email) {
        $sql='DELETE FROM '.BANLIST_TABLE.' WHERE email='.db_input($email);
        return (db_query($sql) && db_affected_rows())?true:false;
    }
    
    static function isbanned($email) {
        return db_num_rows(db_query('SELECT id FROM '.BANLIST_TABLE.' WHERE email='.db_input($email)))?true:false;
    }
}
