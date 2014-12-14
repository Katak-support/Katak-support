<?php
/*********************************************************************
    class.visitorsession.php

    Client, user and staff sessions handle.

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

include_once(INCLUDE_DIR.'class.user.php');
include_once(INCLUDE_DIR.'class.client.php');
include_once(INCLUDE_DIR.'class.staff.php');


/**
 * Base class for user, client and staff sessions.
 * Never directly instantiated.
 */
class VisitorSession {

   var $session_id = '';
   var $userID='';
   var $browser = '';
   var $ip = '';
   var $validated=FALSE;

   function VisitorSession($userid){

      $this->browser=(!empty($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : $_ENV['HTTP_USER_AGENT'];
      $this->ip=(!empty($_SERVER['REMOTE_ADDR'])) ? $_SERVER['REMOTE_ADDR'] : getenv('REMOTE_ADDR');
      $this->session_id=session_id();
      $this->userID=$userid;
   }

   function isStaff(){
       return FALSE;
   }

   function isUser() {
       return FALSE;
   }

   function getSessionId(){
       return $this->session_id;
   }

   function getIP(){
        return  $this->ip;
   }

   function getBrowser(){
       return $this->browser;
   }
   function refreshSession(){
       //nothing to do...users need to worry about it.
   }

   function sessionToken(){

      $time  = time();
      $hash  = md5($time.SESSION_SECRET.$this->userID);
      $token = "$hash:$time:".MD5($this->ip);

      return($token);
   }

   function isvalidSession($htoken,$maxidletime=0,$checkip=false){
        global $cfg;
       
        $token = rawurldecode($htoken);
        
        #check if we got what we expected....
        if($token && !strstr($token,":"))
            return FALSE;
        
        #get the goodies
        list($hash,$expire,$ip)=explode(":",$token);
        
        #Make sure the session hash is valid
        if((md5($expire . SESSION_SECRET . $this->userID)!=$hash)){
            return FALSE;
        }
        #is it expired??
        
        
        if($maxidletime && ((time()-$expire)>$maxidletime)){
            return FALSE;
        }
        #Make sure IP is still same ( proxy access??????)
        if($checkip && strcmp($ip, MD5($this->ip)))
            return FALSE;

        $this->validated=TRUE;

        return TRUE;
   }

   function isValid() {
        return FALSE;
   }

}

class UserSession extends User {
    
    var $session;

    function UserSession($email,$id){
        parent::User($email,$id);
        $this->session= new VisitorSession($email);
    }

    function isValid(){
        global $_SESSION,$cfg;

        if(!$this->getId() || $this->session->getSessionId()!=session_id())
            return false;
        
        return $this->session->isvalidSession($_SESSION['_user']['token'],$cfg->getClientTimeout(),false)?true:false;
    }

    function refreshSession(){
        global $_SESSION;
        $_SESSION['_user']['token']=$this->getSessionToken();
        //TODO: separate expire time from hash??
    }

    function getSession() {
        return $this->session;
    }

    function getSessionToken() {
        return $this->session->sessionToken();
    }
    
    function getIP(){
        return $this->session->getIP();
    }    
}


class ClientSession extends Client {
    
    var $session;

    function ClientSession($var){
        parent::Client($var);
        $this->session= new VisitorSession($var);
    }

    function isValid(){
        global $_SESSION,$cfg;

        if(!$this->getId() || $this->session->getSessionId()!=session_id())
            return false;
        
        return $this->session->isvalidSession($_SESSION['_user']['token'],$cfg->getClientTimeout(),false)?true:false;
    }

    function refreshSession(){
        global $_SESSION;
        $_SESSION['_user']['token']=$this->getSessionToken();
        //TODO: separate expire time from hash??
    }

    function getSession() {
        return $this->session;
    }

    function getSessionToken() {
        return $this->session->sessionToken();
    }
    
    function getIP(){
        return $this->session->getIP();
    }    
}


class StaffSession extends Staff {
    
    var $session;
    
    function StaffSession($var){
        parent::Staff($var);
        $this->session= new VisitorSession($var);
    }

    function isValid(){
        global $_SESSION,$cfg;

        if(!$this->getId() || $this->session->getSessionId()!=session_id())
            return false;
        
        return $this->session->isvalidSession($_SESSION['_staff']['token'],$cfg->getStaffTimeout(),$cfg->enableStaffIPBinding())?true:false;
    }

    function refreshSession(){
        global $_SESSION;
        $_SESSION['_staff']['token']=$this->getSessionToken();
    }
    
    function getSession() {
        return $this->session;
    }

    function getSessionToken() {
        return $this->session->sessionToken();
    }
    
    function getIP(){
        return $this->session->getIP();
    }
    
}

?>
