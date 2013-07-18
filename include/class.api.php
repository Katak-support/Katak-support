<?php
/*********************************************************************
    class.api.php

    Api related functions...

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
class Api {
  

    function add($ip,&$errors) {
        global $cfg;

        $passphrase=$cfg->getAPIPassphrase();

        if(!$passphrase)
            $errors['err']=_('API passphrase missing.');

        if(!$ip || !Validator::is_ip($ip))
            $errors['ip']=_('Valid IP required');
        elseif(Api::getKey($ip))
            $errors['ip']=_('API key for the IP already exists');

        $id=0;
        if(!$errors) {
            $sql='INSERT INTO '.API_KEY_TABLE.' SET created=NOW(), updated=NOW(), isactive=1'.
                 ',ipaddr='.db_input($ip).
                 ',apikey='.db_input(strtoupper(md5($ip.md5($passphrase)))); //Security of the apikey is not as critical at the moment 

            if(db_query($sql))
                $id=db_insert_id();

        }

        return $id;
    }

    function setPassphrase($phrase,&$errors) {
        global $cfg;

        if(!$phrase)
            $errors['phrase']=_('Required');
        elseif(str_word_count($_POST['phrase'])<3)
            $errors['phrase']=_('Must be at least 3 words long.');
        elseif(!strcmp($cfg->getAPIPassphrase(),$phrase))
            $errors['phrase']=_('Already set');
        else{
            $sql='UPDATE '.CONFIG_TABLE.' SET updated=NOW(), api_passphrase='.db_input($phrase).
                ' WHERE id='.db_input($cfg->getId());
            if(db_query($sql) && db_affected_rows()){
                $cfg->reload();
                return true;
            }

        }

        return false;
    }


    function getKey($ip) {

        $key=null;
        $resp=db_query('SELECT apikey FROM '.API_KEY_TABLE.' WHERE ipaddr='.db_input($ip));
        if($resp && db_num_rows($resp))
            list($key)=db_fetch_row($resp);

        return $key;
    }


    function validate($key,$ip) {

        $resp=db_query('SELECT id FROM '.API_KEY_TABLE.' WHERE ipaddr='.db_input($ip).' AND apikey='.db_input($key));
        return ($resp && db_num_rows($resp))?true:false;

    }
   
}
?>
