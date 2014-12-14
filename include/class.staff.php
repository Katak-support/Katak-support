<?php
/*********************************************************************
    class.staff.php

    Everything about staff.

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicketv1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
class Staff {
    
    var $udata;
    var $role_id;
    var $dept_id;
    var $passwd;
    var $id;
    var $fullname;
    var $username;
    var $email;

    var $firstname;
    var $lastname;
    var $signature;

    var $dept;
    
    function Staff($var){
        $this->id = 0;
        return ($this->lookup($var));
    }

    function lookup($var){

        $sql=sprintf("SELECT staff.*, roles.*, dept.dept_name FROM ".STAFF_TABLE." staff LEFT JOIN ".GROUP_TABLE." roles USING(role_id) LEFT JOIN ".DEPT_TABLE." dept USING(dept_id) WHERE %s=%s ",
                        is_numeric($var)?'staff_id':'username',db_input($var));

        $res=db_query($sql);
        if(!$res || !db_num_rows($res))
            return NULL;

        $row=db_fetch_array($res);
        $this->udata=$row;
        $this->id         = $row['staff_id'];
        $this->role_id   = $row['role_id'];
        $this->dept_id    = $row['dept_id'];
        $this->firstname  = ucfirst($row['firstname']);
        $this->lastname  = ucfirst($row['lastname']);
        $this->fullname   = ucfirst($row['firstname'].' '.$row['lastname']);
        $this->passwd     = $row['passwd'];
        $this->username   = $row['username'];
        $this->email      = $row['email'];
        $this->signature  = $row['signature'];

        return($this->id);
    }

    function reload(){
        $this->lookup($this->id);
    }

    function getInfo() {
        return $this->udata;
    }
    
    // Compares staff password
    function check_passwd($password){
      $check = (strlen($this->passwd) && PhpassHashedPass::check($password, $this->passwd))?(TRUE):(FALSE);
      return $check;
    }

    function getTZoffset(){
        global $cfg;

        $offset=$this->udata['timezone_offset'];
        return $offset?$offset:$cfg->getTZoffset();
    }

    function observeDaylight() {
        return $this->udata['daylight_saving']?true:false;
    }

    function getRefreshRate(){
        return $this->udata['auto_refresh_rate'];
    }

    function getPageLimit() {
        global $cfg;
        $limit=$this->udata['max_page_size'];
        return $limit?$limit:$cfg->getPageSize();
    }

    function getData(){
        return($this->udata);
    }

    function getId(){
        return $this->id;
    }

    function getEmail(){
        return($this->email);
    }

    function getUserName(){
        return($this->username);
    }

    function getName(){
        return($this->fullname);
    }
        
    function getFirstName(){
        return $this->firstname;
    }
        
    function getLastName(){
        return $this->lastname;
    }
    
    function getDeptId(){
        return $this->dept_id;
    }   

    function getDeptName(){
        return $this->udata['dept_name'];
    }   

    function getRoleId(){
        return $this->role_id;
    }

    function getSignature(){
        return($this->signature);
    }

    function appendMySignature(){
        return $this->signature?true:false;
    }

    function forcePasswdChange(){
        return $this->udata['change_passwd']?true:false;        
    }

    function getDeptsId(){
        //Departments the user is allowed to access...based on the role they belong to + user's dept.
        //Administrators can access all!
      return array_filter(array_unique(array_merge(explode(',',$this->udata['dept_access']),array($this->dept_id)))); //Neptune help us
    }

    function getDeptsName(){
        //Departments the user is allowed to access...based on the role they belong to + user's dept.
        //Administrators can access all!
       if (!$this->isadmin())
         $sql='SELECT dept_id,dept_name FROM '.DEPT_TABLE.' WHERE dept_id IN ('.implode(',',$this->getDeptsId()).')';
       else
         $sql='SELECT dept_id,dept_name FROM '.DEPT_TABLE;
       
       $depts= db_query($sql);
       return ($depts);
    }

    function getDept(){

        if(!$this->dept && $this->dept_id)
            $this->dept= new Dept($this->dept_id);

        return $this->dept;
    }

    function isManager() {
        return (($dept=$this->getDept()) && $dept->getManagerId()==$this->getId())?true:false;
    }

    function isStaff(){
        return TRUE;
    }

    function isRoleActive() {
        return ($this->udata['role_enabled'])?true:false;
    }

    function isactive(){
        return ($this->udata['isactive'])?true:false;
    }

    function isVisible(){
         return ($this->udata['isvisible'])?true:false;
    }
        
    function onVacation(){
        return ($this->udata['onvacation'])?true:false;
    }

    function isAvailable() {
        return (!$this->isactive() || !$this->isRoleActive() || $this->onVacation())?false:true;
    }
   
    function isadmin(){
        return ($this->udata['isadmin'])?true:false;
    }
   
    function canAccessDept($deptid){
        return ($this->isadmin() ||in_array($deptid,$this->getDeptsId()))?true:false;
    }

    function canViewunassignedTickets(){
        return ($this->isadmin() || $this->udata['can_viewunassigned_tickets'])?true:false;
    }

    function canCreateTickets(){
        return ($this->isadmin() || $this->udata['can_create_tickets'])?true:false;
    }

    function canEditTickets(){
        return ($this->isadmin() || $this->udata['can_edit_tickets'])?true:false;
    }
    
    function canDeleteTickets(){
        return ($this->isadmin() || $this->udata['can_delete_tickets'])?true:false;
    }
   
    function canChangepriorityTickets(){
        return ($this->isadmin() || $this->udata['can_changepriority_tickets'])?true:false;
    }
   
    function canAssignTickets(){
        return ($this->isadmin() || $this->udata['can_assign_tickets'])?true:false;
    }
   
    function canCloseTickets(){
        return ($this->isadmin() || $this->udata['can_close_tickets'])?true:false;
    }

    function canTransferTickets() {
        return ($this->isadmin() || $this->udata['can_transfer_tickets'])?true:false;
    }

    function canManageBanList() {
        return ($this->isadmin() || $this->udata['can_ban_emails'])?true:false;
    }
  
    function canManageTickets() {
        return ($this->isadmin()
                || $this->isManager()
                || $this->canDeleteTickets()
                || $this->canChangepriorityTickets()
                || $this->canAssignTickets()
                || $this->canEditTickets()
                || $this->canManageBanList()
                || $this->canCloseTickets())?true:false;
    }

    function canManageStdr() { //Stdr = standard reply.
        return ($this->isadmin() || $this->udata['can_manage_stdr'])?true:false;
    }

    function update($vars,&$errors) {
        if($this->save($this->getId(),$vars,$errors)){
            $this->reload();
            return true;
        }
        return false;
    }

    // Update last login
    function update_lastlogin($id) {
      db_query('UPDATE ' . STAFF_TABLE . ' SET lastlogin=NOW() WHERE staff_id=' . db_input($id));
      return true;
    }
    
    static function create($vars,&$errors) {
        return Staff::save(0,$vars,$errors);
    }


    function save($id,$vars,&$errors) {
            
        if($id && $id!=$vars['staff_id'])
            $errors['err']=_('Internal Error');
            
        if(!$vars['firstname'] || !$vars['lastname'])
            $errors['name']=_('First and last name required');
            
        if(!$vars['username'] || strlen($vars['username'])<3)
            $errors['username']=_('Username required');
        else{
            //check if the username is already in-use.
            $sql='SELECT staff_id FROM '.STAFF_TABLE.' WHERE username='.db_input($vars['username']);
            if($id)
                $sql.=' AND staff_id!='.db_input($id);
            if(db_num_rows(db_query($sql)))
                $errors['username']=_('Username already in-use');
        }

        // Check email.
        if(!$vars['email'] || !Validator::is_email($vars['email']))
            $errors['email']=_('Valid email required');
        elseif(Email::getIdByEmail($vars['email']))
            $errors['email']=_('Already in-use system email');
        else{
            //check if the email is already in-use.
            $sql='SELECT staff_id FROM '.STAFF_TABLE.' WHERE email='.db_input($vars['email']);
            if($id)
                $sql.=' AND staff_id!='.db_input($id);
            if(db_num_rows(db_query($sql)))
                $errors['email']=_('Already in-use email');
        }
                
        if($vars['phone'] && !Validator::is_phone($vars['phone']))
            $errors['phone']=_('Valid number required');
        
        if($vars['mobile'] && !Validator::is_phone($vars['mobile']))
            $errors['mobile']=_('Valid number required');

        // Chek password
        if($vars['npassword'] || $vars['vpassword'] || !$id){
            if(!$vars['npassword'] && !$id)
                $errors['npassword']=_('Temp password required');
            elseif($vars['npassword'] && strcmp($vars['npassword'],$vars['vpassword']))
                $errors['vpassword']=_('Password(s) do not match');
            elseif($vars['npassword'] && strlen($vars['npassword'])<6)
                $errors['npassword']=_('Must be at least 6 characters');
            elseif($vars['npassword'] && strlen($vars['npassword'])>128)
                $errors['npassword']=_('Password too long');
        }

        // Check department
        if(!$vars['dept_id'])
          $errors['dept']=_('Department required');
        elseif($id && $this->getDeptId()!=$vars['dept_id']) {
            //check if the user is still dept. manager.
            $sql='SELECT dept_name FROM '.DEPT_TABLE.' WHERE dept_id='.db_input($this->getDeptId()).' AND manager_id='.db_input($id);
            if(db_num_rows(db_query($sql)))
                $errors['dept']=_('The user is currently manager of his/her department');
        }
          
        // Check if the role is select and that it remains at least one administrator
        if(!$vars['role_id'])
            $errors['role']=_('Role required');
        elseif($vars['role_id']=="1")
            $isadmin="1";
        elseif($id && (db_count('SELECT COUNT(*) FROM '.STAFF_TABLE.' WHERE staff_id = '.db_input($id).' AND isadmin = 1') == 1) && (db_count('SELECT COUNT(*) FROM '.STAFF_TABLE.' WHERE isadmin = 1') == 1))
            $errors['role']=_('At least an administrator must remain');
        else
            $isadmin="0";
        
        if(!$errors){
            
            $sql=' SET updated=NOW() '.
                 ',isadmin='.db_input($isadmin).
                 ',isactive='.db_input($vars['isactive']).
                 ',isvisible='.db_input(isset($vars['isvisible'])?1:0).
                 ',onvacation='.db_input(isset($vars['onvacation'])?1:0).
                 ',dept_id='.db_input($vars['dept_id']).
                 ',role_id='.db_input($vars['role_id']).
                 ',username='.db_input(Format::striptags($vars['username'])).
                 ',firstname='.db_input(Format::striptags($vars['firstname'])).
                 ',lastname='.db_input(Format::striptags($vars['lastname'])).
                 ',email='.db_input($vars['email']).
                 ',phone="'.db_input($vars['phone'],false).'"'.
                 ',mobile="'.db_input($vars['mobile'],false).'"'.
                 ',signature='.db_input(Format::striptags($vars['signature']));

            if($vars['npassword']) {
                $hash = PhpassHashedPass::hash($vars['npassword']);
                $sql.=',passwd='.db_input($hash);
            }
            
            if(isset($vars['resetpasswd']))
                $sql.=',change_passwd=1';

            if($id) {
                $sql='UPDATE '.STAFF_TABLE.' '.$sql.' WHERE staff_id='.db_input($id);
                if(!db_query($sql) || !db_affected_rows())
                    $errors['err']=_('Unable to update the user. Internal error occured');
            }else{
                $sql='INSERT INTO '.STAFF_TABLE.' '.$sql.',created=NOW()';
                if(db_query($sql) && ($uID=db_insert_id()))
                    return $uID;

                $errors['err']=_('Unable to create user. Internal error');
            }
        }

        return $errors?false:true;
    }
}
?>