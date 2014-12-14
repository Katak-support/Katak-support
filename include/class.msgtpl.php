<?php
/*********************************************************************
    class.msgtpl.php
    
    Messages templates.

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

class Template {

    var $id;
    var $name;

    var $info;
    
    function Template($id,$cId=0){
        $this->load($id,$cId);
    }
    
    function load($id,$cId=0) {

        if(!$id)
            return false;
        
        $sql='SELECT * FROM '.EMAIL_TEMPLATE_TABLE.' WHERE tpl_id='.db_input($id);
        if($cId && is_numeric($cId))
            $sql.=' AND cfg_id='.db_input($cId);

        if(($res=db_query($sql)) && db_num_rows($res)) {
            $info=db_fetch_array($res);
            $this->id=$info['tpl_id'];
            $this->cfgId=$info['cfg_id'];
            $this->name=$info['name'];
            $this->info=$info;
            return true;
        }
        $this->id=0;

        return false;
    }
  
    function reload() {
        return $this->load($this->getId(),$this->getCfgId());
    }
    
    function getId(){
        return $this->id;
    }

    function getCfgId(){
        return $this->cfgId;
    }
    
    function getName(){
        return $this->name;
    }

    function getInfo() {
        return $this->info;
    }

    function getCreateDate() {
        return $this->info['created'];
    }

    function getUpdateDate() {
        return $this->info['updated'];
    }


    function update($var,&$errors){


        $fields=array();
        $fields['id']  = array('type'=>'int',      'required'=>1, 'error'=>_('Internal Error'));
        $fields['name']     = array('type'=>'string',   'required'=>1, 'error'=>_('Name required'));
        //Notices sent to user
        $fields['ticket_autoresp_subj']  = array('type'=>'string',   'required'=>1, 'error'=>_('Subject required'));
        $fields['ticket_autoresp_body']  = array('type'=>'string',   'required'=>1, 'error'=>_('Template message required'));
        $fields['message_autoresp_subj'] = array('type'=>'string',   'required'=>1, 'error'=>_('Subject required'));
        $fields['message_autoresp_body'] = array('type'=>'string',   'required'=>1, 'error'=>_('Template message required'));
        $fields['ticket_notice_subj'] = array('type'=>'string',   'required'=>1, 'error'=>_('Subject required'));
        $fields['ticket_notice_body'] = array('type'=>'string',   'required'=>1, 'error'=>_('Template message required'));
        $fields['ticket_overlimit_subj'] = array('type'=>'string',   'required'=>1, 'error'=>_('Subject required'));
        $fields['ticket_overlimit_body'] = array('type'=>'string',   'required'=>1, 'error'=>_('Template message required'));
        $fields['ticket_reply_subj']     = array('type'=>'string',   'required'=>1, 'error'=>_('Subject required'));
        $fields['ticket_reply_body']     = array('type'=>'string',   'required'=>1, 'error'=>_('Template message required'));
        //Alerts sent to Staff
        $fields['ticket_alert_subj']    = array('type'=>'string',   'required'=>1, 'error'=>_('Subject required'));
        $fields['ticket_alert_body']    = array('type'=>'string',   'required'=>1, 'error'=>_('Template message required'));
        $fields['message_alert_subj']   = array('type'=>'string',   'required'=>1, 'error'=>_('Subject required'));
        $fields['message_alert_body']   = array('type'=>'string',   'required'=>1, 'error'=>_('Template message required'));
        $fields['note_alert_subj']      = array('type'=>'string',   'required'=>1, 'error'=>_('Subject required'));
        $fields['note_alert_body']      = array('type'=>'string',   'required'=>1, 'error'=>_('Template message required'));
        $fields['assigned_alert_subj']  = array('type'=>'string',   'required'=>1, 'error'=>_('Subject required'));
        $fields['assigned_alert_body']  = array('type'=>'string',   'required'=>1, 'error'=>_('Template message required'));
        $fields['ticket_overdue_subj']  = array('type'=>'string',   'required'=>1, 'error'=>_('Subject required'));
        $fields['ticket_overdue_body']  = array('type'=>'string',   'required'=>1, 'error'=>_('Template message required'));

        $validate = new Validator($fields);
        if(!$validate->validate($var)){
            $errors=array_merge($errors,$validate->errors());
        }

        if(!$errors && $var['id'] && $var['id']!=$this->getId())
            $errors['err']='Internal error. Try again';

        if(!$errors['name'] && ($tid=Template::getIdByName($var['name'])) && $tid!=$this->getId())
             $errors['name']='Name already in use';
                        
        if(!$errors) {
        
            $sql='UPDATE '.EMAIL_TEMPLATE_TABLE.' SET updated=NOW() '.
                 ',name='.db_input(Format::striptags($var['name'])).
                 ',notes='.db_input(Format::striptags($var['notes'])).
                 ',ticket_autoresp_subj='.db_input(Format::striptags($var['ticket_autoresp_subj'])).
                 ',ticket_autoresp_body='.db_input(Format::striptags($var['ticket_autoresp_body'])).
                 ',message_autoresp_subj='.db_input(Format::striptags($var['message_autoresp_subj'])).
                 ',message_autoresp_body='.db_input(Format::striptags($var['message_autoresp_body'])).
                 ',ticket_notice_subj='.db_input(Format::striptags($var['ticket_notice_subj'])).
                 ',ticket_notice_body='.db_input(Format::striptags($var['ticket_notice_body'])).
                 ',ticket_alert_subj='.db_input(Format::striptags($var['ticket_alert_subj'])).
                 ',ticket_alert_body='.db_input(Format::striptags($var['ticket_alert_body'])).
                 ',message_alert_subj='.db_input(Format::striptags($var['message_alert_subj'])).
                 ',message_alert_body='.db_input(Format::striptags($var['message_alert_body'])).
                 ',note_alert_subj='.db_input(Format::striptags($var['note_alert_subj'])).
                 ',note_alert_body='.db_input(Format::striptags($var['note_alert_body'])).
                 ',assigned_alert_subj='.db_input(Format::striptags($var['assigned_alert_subj'])).
                 ',assigned_alert_body='.db_input(Format::striptags($var['assigned_alert_body'])).
                 ',ticket_overdue_subj='.db_input(Format::striptags($var['ticket_overdue_subj'])).
                 ',ticket_overdue_body='.db_input(Format::striptags($var['ticket_overdue_body'])).
                 ',ticket_overlimit_subj='.db_input(Format::striptags($var['ticket_overlimit_subj'])).
                 ',ticket_overlimit_body='.db_input(Format::striptags($var['ticket_overlimit_body'])).
                 ',ticket_reply_subj='.db_input(Format::striptags($var['ticket_reply_subj'])).
                 ',ticket_reply_body='.db_input(Format::striptags($var['ticket_reply_body'])).
                 ' WHERE tpl_id='.db_input($this->getId());

            if(!db_query($sql) || !db_affected_rows())
                $errors['err']=_('Unable to update. Internal error occured');
                        
        }

        return $errors?false:true;

    }


    function getIdByName($name) {

        $id=0;
        $sql='SELECT tpl_id FROM '.EMAIL_TEMPLATE_TABLE.' WHERE name='.db_input($name);
        if(($resp=db_query($sql)) && db_num_rows($resp))
            list($id)=db_fetch_row($resp);

        return $id;
    }


    function create($var,&$errors){
        global $cfg;

        if(!$var['name'])
            $errors['name']='required';
        elseif(!$errors && Template::getIdByName($var['name'])) 
             $errors['name']=_('Name already in use');
              
        if(!$var['copy_template'])
            $errors['copy_template']='required';
        else if(!$errors){
            $template= new Template($var['copy_template'],$cfg->getId());
            if(!is_object($template) || !$template->getId())
                $errors['copy_template']=_('Unknown template');
        }

        $id=0;
        if(!$errors && ($info=$template->getInfo())) {

            $sql='INSERT INTO '.EMAIL_TEMPLATE_TABLE.' SET updated=NOW(), created=NOW() '.
                 ',cfg_id='.db_input($cfg->getId()).
                 ',name='.db_input(Format::striptags($var['name'])).
                 ',notes='.db_input('New template: copy of '.$info['name']).
                 ',ticket_autoresp_subj='.db_input(Format::striptags($info['ticket_autoresp_subj'])).
                 ',ticket_autoresp_body='.db_input(Format::striptags($info['ticket_autoresp_body'])).
                 ',message_autoresp_subj='.db_input(Format::striptags($info['message_autoresp_subj'])).
                 ',message_autoresp_body='.db_input(Format::striptags($info['message_autoresp_body'])).
                 ',ticket_notice_subj='.db_input(Format::striptags($info['ticket_notice_subj'])).
                 ',ticket_notice_body='.db_input(Format::striptags($info['ticket_notice_body'])).
                 ',ticket_alert_subj='.db_input(Format::striptags($info['ticket_alert_subj'])).
                 ',ticket_alert_body='.db_input(Format::striptags($info['ticket_alert_body'])).
                 ',message_alert_subj='.db_input(Format::striptags($info['message_alert_subj'])).
                 ',message_alert_body='.db_input(Format::striptags($info['message_alert_body'])).
                 ',note_alert_subj='.db_input(Format::striptags($info['note_alert_subj'])).
                 ',note_alert_body='.db_input(Format::striptags($info['note_alert_body'])).
                 ',assigned_alert_subj='.db_input(Format::striptags($info['assigned_alert_subj'])).
                 ',assigned_alert_body='.db_input(Format::striptags($info['assigned_alert_body'])).
                 ',ticket_overdue_subj='.db_input(Format::striptags($info['ticket_overdue_subj'])).
                 ',ticket_overdue_body='.db_input(Format::striptags($info['ticket_overdue_body'])).
                 ',ticket_overlimit_subj='.db_input(Format::striptags($info['ticket_overlimit_subj'])).
                 ',ticket_overlimit_body='.db_input(Format::striptags($info['ticket_overlimit_body'])).
                 ',ticket_reply_subj='.db_input(Format::striptags($info['ticket_reply_subj'])).
                 ',ticket_reply_body='.db_input(Format::striptags($info['ticket_reply_body']));
            //echo $sql;
            if(!db_query($sql) || !($id=db_insert_id()))
                $errors['err']=_('Unable to create the template. Internal error occured');
        }
        return $id;
    }

}
?>