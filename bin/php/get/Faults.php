<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $User    = \singleton\database::getInstance( )->query(
        null,
      " SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Unit'])
        && (
				  $Privileges['Unit']['User_Privilege'] >= 4
			&&	$Privileges['Unit']['Group_Privilege'] >= 4
      && $Privileges['Unit']['Other_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
      if(isset($_GET['ID'])){
        $r = \singleton\database::getInstance( )->query(
            null,
          " SELECT  CM_Fault.ID AS ID,
                    CM_Fault.Location AS Location,
                    CM_Fault.Unit AS Unit,
                    CM_Fault.Date AS DateTime,
                    CM_Fault.Fault AS Fault,
                    CM_Fault.Status AS Status
            FROM    Device.dbo.CM_Fault
                    LEFT JOIN Device.dbo.CM_Unit ON CM_Fault.[Location] = CM_Unit.[Location] AND CM_Fault.[Unit] = CM_Unit.[Unit]
            WHERE   CM_Unit.Elev_ID = ?
            ORDER BY CM_Fault.Date DESC
          ;",array($_GET['ID']));
      } else {
        $r = \singleton\database::getInstance( )->query(
            null,
          " SELECT  CM_Fault.ID AS ID,
                    CM_Fault.Location AS Location,
                    CM_Fault.Unit AS Unit,
                    CM_Fault.Date AS DateTime,
                    CM_Fault.Fault AS Fault,
                    CM_Fault.Status AS Status
            FROM    Device.dbo.CM_Fault
            ORDER BY CM_Fault.Date DESC
          ;");
      }
      $data = array();
      if($r){while($row = sqlsrv_fetch_array($r)){
        $row['DateTime'] = date("m/d/Y h:i A",strtotime($row['DateTime']));
        $data[] = $row;
      }}
      print json_encode(array('data'=>$data));
	}
}?>
