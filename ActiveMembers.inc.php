<?php

require_once('profile.inc.php');
require_once(dirname(__FILE__) . '../../../../wp-config.php');
require_once(dirname(__FILE__) . '../../../../wp-includes/wp-db.php');
require_once(dirname(__FILE__) . '../../../../wp-load.php');

global $dm_configs;
if(!isset($dm_configs))
{
  if(defined('WP_CONTENT_DIR'))
  {
    $dm_config_file = WP_CONTENT_DIR . '/digitalmeasures/config.inc.php';
  }
  if(file_exists($dm_config_file))
  {
    require_once($dm_config_file);
  }
  else
  {
    require_once('config.inc.php');
  }
}

$datauser;
foreach($dm_configs as $conf) {
  $datauser = $conf['username'];
}
$password;
foreach($dm_configs as $conf) {
  $password = $conf['password'];
}
$key;
foreach($dm_configs as $conf) {
  $key = $conf['key'];
}
$configs = array("username"=>$datauser, "password"=>$password, "key"=>$key);
//URL for all member xml data sheet
$ch = curl_init('https://digitalmeasures.com/login/service/v4/SchemaData/INDIVIDUAL-ACTIVITIES-Business/PCI,ADMIN');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
curl_setopt($ch, CURLOPT_USERPWD, "$datauser:$password");
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
$ret = curl_exec($ch);
curl_close($ch);

// $datauser = '';
// $password = '';
//
//Make parsing easier by removing dmd namespace
$ret = str_replace('dmd:', '', $ret);
// echo $ret;
$dom = new DOMDocument;
$good = $dom->loadXML($ret);
if(!$good) {
  echo $ret;
  exit;
}
$xml = simplexml_import_dom($dom);
//makes an array of active Users
global $wpdb;
$prefix = $wpdb->prefix;
$digital_measures_table = $prefix . "digital_measures";
// checks if digital measures table already exists
if($wpdb->get_var("SHOW TABLES LIKE '$digital_measures_table'")!=$digital_measures_table) {
  // Table doesn't exists, create one
  $charset_collate = $wpdb->get_charset_collate();
  $sql = "CREATE TABLE $digital_measures_table (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    username varchar(15) NOT NULL,
    first_name varchar(15),
    middle_name varchar(15),
    last_name varchar(15),
    UNIQUE KEY id (id)
  ) $charset_collate;";
  require_once( ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta( $sql );
}
// else
// {
// $wpdb->delete( $digital_measures_table, array( 'ID'< 0 )); // im not sure if this works but should delete all of the rows before repopulating them
// }
// find all of the active members
foreach($xml->Record as $record){
  // seeing if the record is an active user
  $adminXML = $record->ADMIN[0]; // selecting the first admin element
  $activeYearStr = substr($adminXML->AC_YEAR,-4);
  $isActive = false; // default $isActive for a user is set to false
  if($activeYearStr == date("Y")) {
    $isActive = true;
  }
  else {
    $isActive = false;
  }
  if($isActive) {
    // user is active and should be added to the WP database
    $username = $record['username'];
    $firstName = $record->PCI->FNAME;
    $middleName = $record->PCI->MNAME;
    $lastName = $record->PCI->LNAME;

    // inserting into the WordPress Database
    $wpdb->insert(
      $digital_measures_table,
      array(
        'username' => $username,
        'first_name' => $firstName,
        'middle_name' => $middleName,
        'last_name' => $lastName
      ),
      array(
        '%s',
        '%s',
        '%s',
        '%s'
      )
    );
  }
}

echo "Active members data were updated successfully";


?>
