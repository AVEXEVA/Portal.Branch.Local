<?php
function to_array(&$object=''){
  // IF OBJECT, MAKE ARRAY
  if(is_object($object)){$object = (array)$object;}

  // IF NOT ARRAY OR EMPTY ARRAY, RETURN = LEAVES SCALARS
  if(!is_array($object)||empty($object)){return;}

  // FOR EACH ITEM, RECURSE VALUE
  foreach($object as &$Value){to_array($Value);}
}
session_start();
require('../../../cgi-bin/php/index.php');
if(isset($_GET['Token']) && $_GET['Token'] == 'DF58BF2A46732848E8784426E2EB4D9DBDA030D7A80509F6B192AD4FCDC23555E81D7A67F9C4C712BE09D38601559FA23B175BEB7B802F9B8A3CA41917F46428'){
  $_GET['Phone'] = json_decode($_GET['Phone']);
  to_array($_GET['Phone']);
  if(isset($_GET['Phone']['imei'])){
    sqlsrv_query($Portal,"INSERT INTO Portal.dbo.Phone_GPS(Phone, Latitude, Longitude, Altitude, [TimeStamp]) VALUES(?, ?, ?, ?, ?);",array($_GET['Phone']['imei'],$_GET['Latitude'],$_GET['Longitude'],$_GET['Sea_Level'], date("Y-m-d H:i:s")));
  }
}?>
