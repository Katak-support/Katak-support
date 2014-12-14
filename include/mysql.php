<?php
/*********************************************************************
    mysql.php

    Collection of MySQL helper interface functions. 
    Mostly wrappers with error checking.

    Copyright (c)  2012-2014 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket v1.6 by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/

require_once(INCLUDE_DIR.'class.sys.php');

//The MySQLI support is enabled
if (class_exists('mysqli')) {
  function db_connect($dbhost, $dbuser, $dbpass, $dbname) {
      
    if(!strlen($dbuser) || !strlen($dbpass) || !strlen($dbhost) || !strlen($dbname))
  	  $connection = FALSE;
    else {
      global $dblink;
      $dblink = @new mysqli($dbhost, $dbuser, $dbpass, $dbname);
      //set desired encoding just in case mysql charset is not UTF-8 - Thanks to FreshMedia
      if ($dblink->connect_errno) 
        $connection = FALSE;
      else {
        $connection = TRUE;
        $dblink->query('SET NAMES "UTF8"');
        $dblink->query('SET COLLATION_CONNECTION=utf8_general_ci');
      }
    }
    return $connection;	
  }

  function db_close(){
      global $dblink;
      return $dblink->close();
  }

  function db_version(){
    $result = db_query('SELECT VERSION()');
    $row = $result->fetch_row();
    preg_match('/(\d{1,2}\.\d{1,2}\.\d{1,2})/', $row['0'], $matches);
    return $matches[1];
  }
       
	// execute sql query
  function db_query($query){
    global $cfg;
    global $dblink;

    $response=$dblink->query($query);
    //TODO: recover from "Lost connection to MySQL server during query" error??
    
    if(!$response) { //error reporting
        $alert='['.$query.']'."\n\n".db_error();
        Sys::log(LOG_ALERT,_('DB Error No.').' '.db_errno(),$alert,($cfg && $cfg->alertONSQLError()));
        //echo $msg; #uncomment during debuging or dev.
    }
    return $response;
	}

	function db_count($query){		
		list($count)=db_fetch_row(db_query($query));
		return $count;
	}

	function db_fetch_array($result,$mode=FALSE) {
   	    return ($result)?db_output($result->fetch_array(($mode)?$mode:MYSQLI_ASSOC)):NULL;
  	}

    function db_fetch_row($result) {
        return ($result)?db_output($result->fetch_row()):NULL;
    }

    function db_fetch_fields($result) {
        return $result->fetch_field();
    }   

    function db_assoc_array($result,$mode=FALSE){
	    if($result && db_num_rows($result)){
      	    while ($row=db_fetch_array($result,$mode))
         	    $results[]=$row;
        }
        return $results;
    }

    function db_num_rows($result) {
      return ($result)?$result->num_rows:0;
    }

	  function db_affected_rows() {
      global $dblink;
      return $dblink->affected_rows;
    }

  	function db_insert_id() {
  	  global $dblink;
   	  return $dblink->insert_id;
  	}

	function db_free_result($result) {
   	    return $result->close();
  	}
  
	function db_output($param) {

        if(!function_exists('get_magic_quotes_runtime') || !get_magic_quotes_runtime()) //Sucker is NOT on - thanks.
            return $param;

        if (is_array($param)) {
      	    reset($param);
      	    while(list($key, $value) = each($param)) {
        	    $param[$key] = db_output($value);
      	    }
      	    return $param;
    	}elseif(!is_numeric($param)) {
            $param=trim(stripslashes($param));
        }

        return $param;
  	}

    //Do not call this function directly...use db_input
    function db_real_escape($val, $quote=false){
        global $dblink;

        //Magic quotes crap is taken care of in main.inc.php
        $val = $dblink->real_escape_string($val);

        return ($quote)?"'$val'":$val;
    }

    function db_input($param,$quote=true) {

        //is_numeric doesn't work all the time...9e8 is considered numeric..which is correct...but not expected.
        if($param && preg_match("/^\d+(\.\d+)?$/",$param))
            return $param;

        if($param && is_array($param)){
            reset($param);
            while (list($key, $value) = each($s)) {
                $param[$key] = db_input($value,$quote);
            }
            return $param;
        }
        return db_real_escape($param,$quote);
    }

	  function db_error(){
      global $dblink;
	    return $dblink->error;   
      }
   
    function db_errno(){
      global $dblink;
      return $dblink->errno;
    }
}


//The MySQLI support is NOT enabled. To be delete when finally all the servers have it enabled!
else {
  function db_connect($dbhost,$dbuser, $dbpass,$dbname = "") {
      
      if(!strlen($dbuser) || !strlen($dbpass) || !strlen($dbhost))
          return NULL;

      @$$dblink = mysql_connect($dbhost, $dbuser, $dbpass);
      if($$dblink && $dbname)
          @mysql_select_db($dbname);
      //set desired encoding just in case mysql charset is not UTF-8 - Thanks to FreshMedia
      if($$dblink) {
          @mysql_query('SET NAMES "UTF8"');
          @mysql_query('SET COLLATION_CONNECTION=utf8_general_ci');
      }
      return $$dblink;  
  }

  function db_close(){
      global $$dblink;
      return @mysql_close($$dblink);
  }

  function db_select_database($dbname) {
       return @mysql_select_db($dbname);
  }

  function db_version(){
      preg_match('/(\d{1,2}\.\d{1,2}\.\d{1,2})/', mysql_result(db_query('SELECT VERSION()'),0,0),$matches);
      return $matches[1];
  }
       
  // execute sql query
  function db_query($query, $database="",$conn=""){
    global $cfg;
       
    if($conn){ /* connection is provided*/
      $response=($database)?mysql_db_query($database,$query,$conn):mysql_query($query,$conn);
    }else{
      $response=($database)?mysql_db_query($database,$query):mysql_query($query);
    }
         
    if(!$response) { //error reporting
      $alert='['.$query.']'."\n\n".db_error();
      Sys::log(LOG_ALERT,_('DB Error No.').' '.db_errno(),$alert,($cfg && $cfg->alertONSQLError()));
      //echo $msg; #uncomment during debuging or dev.
    }
    return $response;
  }

  function db_count($query){    
    list($count)=db_fetch_row(db_query($query));
    return $count;
  }

  function db_fetch_array($result,$mode=false) {
        return ($result)?db_output(mysql_fetch_array($result,($mode)?$mode:MYSQL_ASSOC)):null;
    }

    function db_fetch_row($result) {
        return ($result)?db_output(mysql_fetch_row($result)):NULL;
    }

    function db_fetch_fields($result) {
        return mysql_fetch_field($result);
    }   

    function db_assoc_array($result,$mode=false){
      if($result && db_num_rows($result)){
            while ($row=db_fetch_array($result,$mode))
              $results[]=$row;
        }
        return $results;
    }

    function db_num_rows($result) {
        return ($result)?mysql_num_rows($result):0;
    }

  function db_affected_rows() {
      return mysql_affected_rows();
    }

    function db_insert_id() {
        return mysql_insert_id();
    }

  function db_free_result($result) {
        return mysql_free_result($result);
    }
  
  function db_output($param) {

        if(!function_exists('get_magic_quotes_runtime') || !get_magic_quotes_runtime()) //Sucker is NOT on - thanks.
            return $param;

        if (is_array($param)) {
            reset($param);
            while(list($key, $value) = each($param)) {
              $param[$key] = db_output($value);
            }
            return $param;
      }elseif(!is_numeric($param)) {
            $param=trim(stripslashes($param));
        }

        return $param;
  }

  //Do not call this function directly...use db_input
  function db_real_escape($val,$quote=false){
      global $$dblink;

      //Magic quotes crap is taken care of in main.inc.php
      $val=mysql_real_escape_string($val);

      return ($quote)?"'$val'":$val;
  }

  function db_input($param,$quote=true) {

      //is_numeric doesn't work all the time...9e8 is considered numeric..which is correct...but not expected.
      if($param && preg_match("/^\d+(\.\d+)?$/",$param))
          return $param;

      if($param && is_array($param)){
          reset($param);
          while (list($key, $value) = each($s)) {
              $param[$key] = db_input($value,$quote);
          }
          return $param;
      }
      return db_real_escape($param,$quote);
  }

  function db_error(){
    return mysql_error();   
  }
   
  function db_errno(){
    return mysql_errno();
  }
}
?>
