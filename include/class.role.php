<?php
/*********************************************************************
    class.role.php

    Staff roles

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

class Role {

    static function update($id,$vars,&$errors) {
        if($id && Role::save($id,$vars,$errors)){
            return true;
        }
        return false;
    }

    static function create($vars,&$errors) { 
        return Role::save(0,$vars,$errors);
    }

    static function save($id,$vars,&$errors) {

        if($id && !$vars['role_id'])
            $errors['err']=_('Missing or invalid role ID');
            
        if(!$vars['role_name']) {
            $errors['role_name']=_('Role name required');
        }elseif(strlen($vars['role_name'])<5) {
            $errors['role_name']=_('Role name must be at least 5 chars.');
        }else {
            $sql='SELECT role_id FROM '.GROUP_TABLE.' WHERE role_name='.db_input($vars['role_name']);
            if($id)
                $sql.=' AND role_id!='.db_input($id);

            if(db_num_rows(db_query($sql)))
                $errors['role_name']=_('Role name already exists');
        }
        
        if(!$errors){
        
            $sql=' SET updated=NOW(), role_name='.db_input(Format::striptags($vars['role_name'])).
                 ', role_enabled='.db_input($vars['role_enabled']).
                 ', dept_access='.db_input($vars['depts']?implode(',',$vars['depts']):'').
                 ', can_viewunassigned_tickets='.db_input($vars['can_viewunassigned_tickets']).
                 ', can_create_tickets='.db_input($vars['can_create_tickets']).
                 ', can_delete_tickets='.db_input($vars['can_delete_tickets']).
                 ', can_edit_tickets='.db_input($vars['can_edit_tickets']).
                 ', can_changepriority_tickets='.db_input($vars['can_changepriority_tickets']).
                 ', can_assign_tickets='.db_input($vars['can_assign_tickets']).
                 ', can_close_tickets='.db_input($vars['can_close_tickets']).
                 ', can_transfer_tickets='.db_input($vars['can_edit_tickets']).
                 ', can_ban_emails='.db_input($vars['can_ban_emails']).
                 ', can_manage_stdr='.db_input($vars['can_manage_stdr']);
            //echo $sql;
            if($id) {
                $res=db_query('UPDATE '.GROUP_TABLE.' '.$sql.' WHERE role_id='.db_input($id));
                if(!$res || !db_affected_rows())
                    $errors['err']=_('Internal error occured');
            }else{
                $res=db_query('INSERT INTO '.GROUP_TABLE.' '.$sql.',created=NOW()');
                if($res && ($gID=db_insert_id()))
                    return $gID;
                
                $errors['err']=_('Unable to create the role. Internal error');
            }
        }

        return $errors?false:true;
    }
}
?>