<?php
session_start();
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
        SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = sqlsrv_query($NEI,"
        SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Unit'])
        && (
				$My_Privileges['Unit']['Other_Privilege'] >= 4
			&&	$My_Privileges['Unit']['Group_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
    $data = array();
    if(isset($_GET['ID'])){
      $r = sqlsrv_query($NEI,"SELECT Elev.ID FROM nei.dbo.Elev LEFT JOIN nei.dbo.Loc ON Elev.Loc = Loc.Loc WHERE Loc.Route = ?;",array($_GET['ID']));
      if($r){
        $Units = array();
        while($row = sqlsrv_fetch_array($r)){$Units[] = $row['ID'];}
        if(count($Units) > 0){
          $Units = "WHERE (CM_Unit.Elev_ID = " . implode(" OR CM_Unit.Elev_ID = ",$Units) . ")";
          $r = sqlsrv_query($database_Device,"SELECT CM_Fault.* FROM Device.dbo.CM_Fault LEFT JOIN Device.dbo.CM_Unit ON CM_Fault.Location = CM_Unit.Location AND CM_Fault.Unit = CM_Unit.Unit {$Units}");
          if($r){while($row = sqlsrV_fetch_array($r)){
            $row['DateTime'] = date("m/d/Y h:i A",strtotime($row['Date']));
            $data[] = $row;
          }}
        }
      }
    }
    print json_encode(array('data'=>$data));
	}
}?>
