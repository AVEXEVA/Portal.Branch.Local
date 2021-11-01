<?php
session_start();
set_time_limit (30);
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
  $array = sqlsrv_fetch_array($r);
  $Privileged = FALSE;
  $r = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE ID = ?",array($_SESSION['User']));
  $My_User = sqlsrv_fetch_array($r);
  $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
  $r = sqlsrv_query($NEI,"
      SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
      FROM   Privilege
      WHERE  User_ID = ?
  ;",array($_SESSION['User']));
  $My_Privileges = array();
  while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
  $Privileged = FALSE;
  if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 6){$Privileged = TRUE;}
  if(!$Privileged || count($_POST) == 0 || !isset($_POST['Latitude'],$_POST['Longitude'],$_POST['Time_Stamp']) || !is_numeric($_POST['Latitude']) || !is_numeric($_POST['Longitude'])){
    ?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
  else {
    $timestamp = floor($_POST['Time_Stamp'] / 1000); // Get seconds from milliseconds
    $datetime = new DateTime('@'.$timestamp);
    $_POST['Time_Stamp'] = $datetime->format('Y-m-d H:i:s');
    sqlsrv_query($NEI,"INSERT INTO GPS(Employee_ID, Latitude, Longitude, Altitude, Accuracy, Time_Stamp) VALUES(?, ?, ?, ?, ?, ?);", array($_SESSION['User'], $_POST['Latitude'], $_POST['Longitude'], 0, 0, $_POST['Time_Stamp']));
    sqlsrv_query($NEI,"DELETE FROM GPS WHERE GPS.Time_Stamp < ?;",array(date("Y-m-d H:i:s",strtotime("-3 days"))));
  }
}?>
