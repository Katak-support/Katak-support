<?php
/*********************************************************************
    setup.inc.php

    Master include file for setup/install scripts.

    Copyright (c)  2012-2013 Katak Support
    http://www.katak-support.com/
    
    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    Derived from osTicket by Peter Rotich.
    See LICENSE.TXT for details.

    $Id: $
**********************************************************************/
#define paths
define('SETUPINC',true);

if(!defined('INCLUDE_DIR')):
define('ROOT_PATH','../');
define('ROOT_DIR','../');
define('INCLUDE_DIR',ROOT_DIR.'include/');
endif;

#required files
require_once(INCLUDE_DIR.'mysql.php');
require_once(INCLUDE_DIR.'class.i18n.php');
require_once(INCLUDE_DIR.'class.validator.php');
require_once(INCLUDE_DIR.'class.format.php');
require_once(INCLUDE_DIR.'class.misc.php');

#Table Prefix: TABLE_PREFIX must be defined by the caller 
function replace_table_prefix($query) {
    return str_replace('%TABLE_PREFIX%',PREFIX, $query);
}


function load_sql_schema($schema,&$errors,$debug=false){

    global $dblink;
    //Get database schema
    if(!file_exists($schema) || !($schema=file_get_contents($schema))) {
        $errors['err']='Internal error. Please make sure your download is the latest';
        $errors[]='Error accessing SQL schema';
    }else{
        //Loadup SQL schema.
        $queries =array_map('replace_table_prefix',array_filter(array_map('trim',explode(';',$schema)))); //Don't fail me bro!
        if($queries && count($queries)) {
            @db_query('SET SESSION SQL_MODE =""');
            foreach($queries as $k=>$sql) {
                if(!db_query($sql)){
                    if($debug) echo $sql;
                    //Aborting on error.
                    $errors['err']='Invalid SQL schema. Get help from developers';
                    $errors['sql']="[$sql] - ".$dblink->error;
                    break;
                }
            }
        }else{
            $errors['err']='Error parsing SQL schema! Get help from developers';
        }
    }

    return $errors?false:true;
}


#Some messages....

ob_start();
echo "
Thank you for choosing Katak-support.

Please make sure you join the Katak-support forums at http://www.katak-support.org/forums to stay up to date on the latest news, security alerts and updates. The Katak-support forums are also a great place to get assistance, guidance, tips, and help from other Katak-support users. We welcome your contributions to the Katak-support community.

If you are looking for a greater level of support, we provide professional services and commercial support with guaranteed response times, and access to the core development team. We can also help customize Katak-support or even add new features to the system to meet your unique needs.

For more information or to discuss your needs, please contact us today at http://www.katak-support.com/contact/. Your feedback is greatly appreciated!

- The Katak-support Team";
$msg1 = ob_get_contents();
ob_end_clean();
define('KATAK_INSTALLED',trim($msg1));

$msg='';
$errors=array();
?>