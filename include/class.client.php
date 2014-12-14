<?php
/*********************************************************************
    class.client.php

    Handles everything about client.
    The client is a registered user.
    The administrator chooses whether to allow the creation of the tickets to all (users)
    or restrict it to registered visitors (client). 

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
class Client extends User {

    var $udata;
    var $firstname;
    var $lastname;
    var $passwd;


    function Client($var){
        $this->id =0;
        return ($this->lookup($var));
    }

    function lookup($var){

        $sql=sprintf("SELECT * FROM ".CLIENT_TABLE." WHERE %s=%s ",
                        is_numeric($var)?'client_id':'client_email',db_input($var));

        $res=db_query($sql);
        if(!$res || !db_num_rows($res))
            return NULL;

        $row=db_fetch_array($res);
        $this->udata=$row;
        $this->id         = $row['client_id'];
        $this->firstname  = ucfirst($row['client_firstname']);
        $this->lastname 	= ucfirst($row['client_lastname']);
        $this->fullname   = ucfirst($row['client_firstname'].' '.$row['client_lastname']);
        $this->passwd     = $row['client_password'];
        $this->username   = $row['client_email'];
        $this->email      = $row['client_email'];

        return($this->id);
    }

    function getInfo() {
        return $this->udata;
    }
    
    function isactive(){
        return ($this->udata['client_isactive'])?true:false;
    }
    
    // Compares client password
    function check_passwd($password){
      $check = (strlen($this->passwd) && PhpassHashedPass::check($password, $this->passwd))?(TRUE):(FALSE);
      return $check;
    }

    // Update last client login
    function update_lastlogin($id) {
      db_query('UPDATE ' . CLIENT_TABLE . ' SET client_lastlogin=NOW() WHERE client_id=' . db_input($id));
      return true;
    }

    static function create($vars,&$errors) {
        return Client::save(0,$vars,$errors);
    }

    function update($vars,&$errors) {
        if($this->save($this->getId(),$vars,$errors)){
            $this->reload();
            return true;
        }
        return false;
    }

    function save($id,$vars,&$errors) {
            
        if($id && $id!=$vars['client_id'])
            $errors['err']=_('Internal Error');
            
        // Check email.
        if(!$vars['client_email'] || !Validator::is_email($vars['client_email']))
            $errors['email']=_('Valid email required');
        elseif(Email::getIdByEmail($vars['client_email']))
            $errors['email']=_('Already in-use system email');
        else{
            //check if the email is already in-use.
            $sql='SELECT client_id FROM '.CLIENT_TABLE.' WHERE client_email='.db_input($vars['client_email']);
            if($id)
                $sql.=' AND client_id!='.db_input($id);
            if(db_num_rows(db_query($sql)))
                $errors['email']=_('Already in-use email');
        }
                
        if($vars['client_phone'] && !Validator::is_phone($vars['client_phone']))
            $errors['phone']=_('Valid number required');
        
        if($vars['client_mobile'] && !Validator::is_phone($vars['client_mobile']))
            $errors['mobile']=_('Valid number required');

        // Check passwords
        if($vars['npassword'] || $vars['vpassword'] || !$id){
            if(!$vars['npassword'] && !$id)
                $errors['npassword']=_('Password required');
            elseif($vars['npassword'] && strcmp($vars['npassword'],$vars['vpassword']))
                $errors['vpassword']=_('Password(s) do not match');
            elseif($vars['npassword'] && strlen($vars['npassword'])<6)
                $errors['npassword']=_('Must be at least 6 characters');
            elseif($vars['npassword'] && strlen($vars['npassword'])>128)
                $errors['npassword']=_('Password too long');
        }

        if(!$errors){
            $sql=' SET client_isactive='.db_input($vars['client_isactive']).
                 ',client_email='.db_input(Format::striptags($vars['client_email'])).
                 ',client_firstname='.db_input(Format::striptags($vars['client_firstname'])).
                 ',client_lastname='.db_input(Format::striptags($vars['client_lastname'])).
                 ',client_organization='.db_input(Format::striptags($vars['client_organization'])).
                 ',client_phone="'.db_input($vars['client_phone'],false).'"'.
                 ',client_mobile="'.db_input($vars['client_mobile'],false).'"';
            if($vars['npassword']) {
                $hash = PhpassHashedPass::hash($vars['npassword']);
                $sql.=',client_password='.db_input($hash);
            }
           
            if($id) {
                $sql='UPDATE '.CLIENT_TABLE.' '.$sql.' WHERE client_id='.db_input($id);
                if(!db_query($sql) || !db_affected_rows())
                  $errors['err']=_('Unable to update the user. Internal error occured');
                if($vars['old_client_email']!=$vars['client_email']) { // Email changed? Update the tickets!
                	$sql='UPDATE '.TICKET_TABLE.' SET email='.db_input(Format::striptags($vars['client_email'])).' WHERE email='.db_input($vars['old_client_email']);
                	if(!db_query($sql))
              		  $errors['err']=_('Unable to update the user. Internal error occured'); //TODO: reverse the previous db operation!
                }
            }else{
                $sql='INSERT INTO '.CLIENT_TABLE.' '.$sql.',client_created=NOW()';
                if(db_query($sql) && ($uID=db_insert_id()))
                    return $uID;

                $errors['err']=_('Unable to create user. Internal error');
            }
        }

        return $errors?false:true;
    }

    function reload(){
        $this->lookup($this->id);
    }

}
?>