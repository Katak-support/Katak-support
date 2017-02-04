<?php
/*********************************************************************
    class.nav.php

    Navigation helper class. Defines the menus and helps keep navigation
    clean and free from errors.

    Copyright (c)  2012-2017 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
class StaffNav {
    var $tabs=array();
    var $submenu=array();

    var $activetab;
    var $ptype;
    
    // Create the main menu
    function StaffNav($pagetype='staff'){
        global $thisuser;

        $this->ptype=$pagetype;
        $tabs=array();
        if($thisuser->isAdmin() && strcasecmp($pagetype,'admin')==0) {
            $tabs['dashboard']=array('desc'=>_('Dashboard'),'href'=>'admin.php?t=dashboard','title'=>_('Admin Dashboard'));
            $tabs['settings']=array('desc'=>_('Settings'),'href'=>'admin.php?t=settings','title'=>_('System Settings'));
            $tabs['emails']=array('desc'=>_('Emails'),'href'=>'admin.php?t=email','title'=>_('Email Settings'));
            $tabs['topics']=array('desc'=>_('Help Topics'),'href'=>'admin.php?t=topics','title'=>_('Help Topics'));
            $tabs['staff']=array('desc'=>_('Staff'),'href'=>'admin.php?t=staff','title'=>_('Staff Members'));
            $tabs['depts']=array('desc'=>_('Departments'),'href'=>'admin.php?t=depts','title'=>_('Departments'));
            $tabs['clients']=array('desc'=>_('Clients'),'href'=>'admin.php?t=clients','title'=>_('Clients'));
        }else {
            $tabs['tickets']=array('desc'=>_('Tickets'),'href'=>'tickets.php','title'=>_('Ticket Queue'));
            if($thisuser && $thisuser->canManageStdr()){
              $tabs['stdreply']=array('desc'=>_('Standard Replies'),'href'=>'stdreply.php','title'=>_('Standard Replies'));
            }
            $tabs['directory']=array('desc'=>_('Directory'),'href'=>'directory.php','title'=>_('Staff Directory'));
            $tabs['profile']=array('desc'=>_('My Account'),'href'=>'profile.php','title'=>_('Personal data of my account'));
        }
        $this->tabs=$tabs;    
    }

		// Set the admin's submenus
    function setSubMenu($tab){
    	switch ($tab) {
    		case 'dashboard':
    			$this->addSubMenu(array('desc' => _('REPORTS'), 'href' => 'admin.php?t=reports', 'iconclass' => 'reports'));
    			$this->addSubMenu(array('desc' => _('SYSTEM LOGS'), 'href' => 'admin.php?t=syslog', 'iconclass' => 'syslogs'));
    			$this->addSubMenu(array('desc' => _('STATUS'), 'href' => 'admin.php?t=status', 'iconclass' => 'status'));
    			break;
    		case 'settings':
    			$this->addSubMenu(array('desc' => _('PREFERENCES'), 'href' => 'admin.php?t=pref', 'iconclass' => 'preferences'));
    			$this->addSubMenu(array('desc' => _('ATTACHMENTS'), 'href' => 'admin.php?t=attach', 'iconclass' => 'attachment'));
    			$this->addSubMenu(array('desc' => _('API'), 'href' => 'admin.php?t=api', 'iconclass' => 'api'));
    			break;
    		case 'emails':
    			$this->addSubMenu(array('desc' => _('EMAIL ADDRESSES'), 'href' => 'admin.php?t=email', 'iconclass' => 'emailSettings'));
    			$this->addSubMenu(array('desc' => _('ADD NEW EMAIL'), 'href' => 'admin.php?t=email&a=new', 'iconclass' => 'newEmail'));
    			$this->addSubMenu(array('desc' => _('TEMPLATES'), 'href' => 'admin.php?t=templates', 'title' => _('Email Templates'), 'iconclass' => 'emailTemplates'));
    			$this->addSubMenu(array('desc' => _('BANLIST'), 'href' => 'admin.php?t=banlist', 'title' => _('Banned Email'), 'iconclass' => 'banList'));
    			break;
    		case 'topics':
    			$this->addSubMenu(array('desc' => _('HELP TOPICS'), 'href' => 'admin.php?t=topics', 'iconclass' => 'helpTopics'));
    			$this->addSubMenu(array('desc' => _('ADD NEW TOPIC'), 'href' => 'admin.php?t=topics&a=new', 'iconclass' => 'newHelpTopic'));
    			break;
    		case 'staff':
    			$this->addSubMenu(array('desc' => _('STAFF MEMBERS'), 'href' => 'admin.php?t=staff', 'iconclass' => 'users'));
    			$this->addSubMenu(array('desc' => _('ADD NEW STAFF'), 'href' => 'admin.php?t=staff&a=new', 'iconclass' => 'newuser'));
    			$this->addSubMenu(array('desc' => _('STAFF ROLES'), 'href' => 'admin.php?t=roles', 'iconclass' => 'roles'));
    			$this->addSubMenu(array('desc' => _('ADD NEW ROLE'), 'href' => 'admin.php?t=roles&a=new', 'iconclass' => 'newrole'));
    			break;
    		case 'clients':
    			$this->addSubMenu(array('desc' => _('CLIENT LIST'), 'href' => 'admin.php?t=clients', 'iconclass' => 'user'));
    			$this->addSubMenu(array('desc' => _('ADD NEW CLIENT'), 'href' => 'admin.php?t=clients&a=new', 'iconclass' => 'newuser'));
    			$this->addSubMenu(array('desc' => _('CLIENT GROUPS'), 'href' => 'admin.php?t=groups', 'iconclass' => 'groups'));
    			$this->addSubMenu(array('desc' => _('ADD NEW GROUP'), 'href' => 'admin.php?t=groups&a=new', 'iconclass' => 'newgroup'));
    			break;
    		case 'depts':
    			$this->addSubMenu(array('desc' => _('DEPARTMENTS'), 'href' => 'admin.php?t=depts', 'iconclass' => 'departments'));
    			$this->addSubMenu(array('desc' => _('ADD NEW DEPT.'), 'href' => 'admin.php?t=depts&a=new', 'iconclass' => 'newDepartment'));
    			break;
    	}
    }
    
		// Set the active main menu tab and calls the submenus
    function setTabActive($tab){
    
        if($this->tabs[$tab]){
            $this->tabs[$tab]['active']=true;
            if($this->activetab && $this->activetab!=$tab && $this->tabs[$this->activetab])
                 $this->tabs[$this->activetab]['active']=false;
            $this->activetab=$tab;
            $this->setSubMenu($tab); // Calls the submenus
            return true;
        }
        return false;
    }
    
    function addSubMenu($item,$tab=null) {
      
        $tab=$tab?$tab:$this->activetab;
        $this->submenu[$tab][]=$item;
    }
      
    function getActiveTab(){
        return $this->activetab;
    }        

    function getTabs(){
        return $this->tabs;
    }

    function getSubMenu($tab=null){
      
        $tab=$tab?$tab:$this->activetab;  
        return $this->submenu[$tab];
    }
    
}
?>