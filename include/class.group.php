<?php
/*********************************************************************
    class.group.php

    Client groups

    Copyright (c) 1016 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

class Group {
  
    static function update($id,$vars,&$errors) {
        if($id && Group::save($id,$vars,$errors)){
            return true;
        }
        return false;
    }

    static function create($vars,&$errors) { 
        return Group::save(0,$vars,$errors);
    }

    static function save($id,$vars,&$errors) {

        if($id && !$vars['group_id'])
            $errors['err']=_('Missing or invalid group ID');
            
        if(!$vars['group_name']) {
            $errors['group_name']=_('Group name required');
        }elseif(strlen($vars['group_name'])<5) {
            $errors['group_name']=_('Group name must be at least 5 chars.');
        }else {
            $sql='SELECT group_id FROM '.GROUP_TABLE.' WHERE group_name='.db_input($vars['group_name']);
            if($id)
                $sql.=' AND group_id!='.db_input($id);

            if(db_num_rows(db_query($sql)))
                $errors['group_name']=_('Group name already exists');
        }
        // The group_can_edit_tickets choice is actually disabled, therefore:
        if(!$vars['group_can_edit_tickets'])
          $vars['group_can_edit_tickets'] = 0;
        
        if(!$errors){
        
            $sql=' SET group_updated=NOW(), group_name='.db_input(Format::striptags($vars['group_name'])).
                 ', group_enabled='.db_input($vars['group_enabled']).
                 ', group_can_edit_tickets='.db_input($vars['group_can_edit_tickets']);
            //echo $sql;
            if($id) {
                $res=db_query('UPDATE '.GROUP_TABLE.' '.$sql.' WHERE group_id='.db_input($id));
                if(!$res || !db_affected_rows())
                    $errors['err']=_('Internal error occured');
            }else{
                $res=db_query('INSERT INTO '.GROUP_TABLE.' '.$sql.',group_created=NOW()');
                if($res && ($gID=db_insert_id()))
                    return $gID;
                
                $errors['err']=_('Unable to create the group. Internal error');
            }
        }

        return $errors?false:true;
    }
}
?>