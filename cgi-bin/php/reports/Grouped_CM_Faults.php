<?php
session_start();
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
        SELECT *
        FROM   nei.dbo.Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = sqlsrv_query($NEI,"
        SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   nei.dbo.Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Time'])
        && (
				$My_Privileges['Time']['Other_Privilege'] >= 4
			&&	$My_Privileges['Time']['Group_Privilege'] >= 4
			&&  $My_Privileges['Time']['User_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
      $r = sqlsrv_query($database_Device,
        " SELECT  CM_Fault.Location AS Location,
                  CM_Fault.Unit AS Unit,
                  Count(CM_Fault.ID) AS Count_of_Fault,
                  CAST(CM_Fault.Fault as varchar(max)) AS Fault
          FROM    Device.dbo.CM_Fault
          WHERE   CM_Fault.Date >= '2018-11-13 00:00:00.000'
          GROUP BY CM_Fault.Unit, CAST(CM_Fault.Fault as varchar(max)), CM_Fault.Location
        ;");
        if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
      $data = array();
      if($r){while($row = sqlsrv_fetch_array($r)){
        #$row['DateTime'] = date("m/d/Y h:i A",strtotime($row['DateTime']));
        $data[] = $row;
      }}
      print json_encode(array('data'=>$data));
	}
}?>
