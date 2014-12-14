<?php
/*************************************************************************
    class.sys.php

    System core helper.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

require_once(INCLUDE_DIR.'class.config.php'); //Config helper

define('LOG_WARN',LOG_WARNING);

class Sys {

    var $loglevel=array(1=>'Error','Warning','Debug');


    //Load configuration info.
    static function getConfig() {
        $cfg= new Config(1);
        return ($cfg && $cfg->getId())?$cfg:null;
    }

    // Send email alert to the system administrator
    static function alertAdmin($subject,$message) {
        global $cfg;

        //Set sysadmin's email address
        $to=ADMIN_EMAIL;

        //Try getting the alert email.
        $email=null;
        if($cfg && !($email=$cfg->getAlertEmail())) 
            $email=$cfg->getDefaultEmail(); //will take the default email.

        if($email) {
            $email->send($to,$subject,$message);
        }else {//no luck - try the system mail.
            Email::sendmail($to,$subject,$message,sprintf('"Katak-support Alerts"<%s>',$to));
        }
    }

    // Save a log message in the database
    static function log($priority,$title,$message,$logger,$alert=true) {
        global $cfg;
        global $dblink;
        
        switch($priority){ //We are providing only 3 levels of logs. Windows style.
            case LOG_EMERG:
            case LOG_ALERT: 
            case LOG_CRIT: 
            case LOG_ERR:       
                $level=1; // Error level
                if($alert) {
                    Sys::alertAdmin($title,$message);
                }
                break;
            case LOG_WARN:
            case LOG_WARNING:
                $level=2; // Warning level
                break;
            case LOG_NOTICE:
            case LOG_INFO:
            case LOG_DEBUG:
            default:
                $level=3; // Debug level
        }
        //Save log based on system log level settings.
        if($cfg && $cfg->getLogLevel()>=$level){
            $loglevel=array(1=>'Error','Warning','Debug');
            $sql='INSERT INTO '.SYSLOG_TABLE.' SET created=NOW() '.
                 ',title='.db_input($title).
                 ',log_type='.db_input($loglevel[$level]).
                 ',log='.db_input($message).
                 ',logger='.db_input($logger).
                 ',ip_address='.db_input($_SERVER['REMOTE_ADDR']);
            //echo $sql;
            if (class_exists('mysqli'))
              $dblink->query($sql); //don't use db_query to avoid possible loop.
            else
              mysql_query($sql); //don't use db_query to avoid possible loop.
        }
    }

    // Delete old log messages
    static function purgeLogs(){
        global $cfg;

        if($cfg && ($gp=$cfg->getLogGraceperiod()) && is_numeric($gp)) {
            $sql='DELETE  FROM '.SYSLOG_TABLE.' WHERE DATE_ADD(created, INTERVAL '.$gp.' MONTH)<=NOW()';
            db_query($sql);
        }

    }
}

?>
