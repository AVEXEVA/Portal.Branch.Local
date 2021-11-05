<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
        SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = $database->query(null,"
        SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query($Portal,"
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
    if( isset($My_Privileges['Requisition'])
        && (
			$My_Privileges['Requisition']['User_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
      if($My_Privileges['Requisition']['Other_Privilege'] >= 4){
        $r = $database->query(null,
        " SELECT  Requisition.ID,
                  Emp.fFirst + ' ' + Emp.Last AS [User],
                  Requisition.[Date],
                  Requisition.[Required],
                  Loc.Tag AS Location,
                  DropOff.Tag AS DropOff,
                  Elev.State AS Unit,
                  Job.fDesc AS Job
          FROM    Portal.dbo.Requisition
                  LEFT JOIN nei.dbo.Loc ON Requisition.Location = Loc.Loc
                  LEFT JOIN nei.dbo.Loc AS DropOff ON Requisition.DropOff = DropOff.Loc
                  LEFT JOIN nei.dbo.Elev ON Requisition.Unit = Elev.ID
                  LEFT JOIN nei.dbo.Job ON Requisition.Job = Job.ID
                  LEFT JOIN Emp ON Emp.ID = Requisition.[User]
        ;",array($_SESSION['User']));
      } else {
        $r = $database->query(null,
        " SELECT  Requisition.ID,
                  Emp.fFirst + ' ' + Emp.Last AS [User],
                  Requisition.[Date],
                  Requisition.[Required],
                  Loc.Tag AS Location,
                  DropOff.Tag AS DropOff,
                  Elev.State AS Unit,
                  Job.fDesc AS Job
          FROM    Portal.dbo.Requisition
                  LEFT JOIN nei.dbo.Loc ON Requisition.Location = Loc.Loc
                  LEFT JOIN nei.dbo.Loc AS DropOff ON Requisition.DropOff = DropOff.Loc
                  LEFT JOIN nei.dbo.Elev ON Requisition.Unit = Elev.ID
                  LEFT JOIN nei.dbo.Job ON Requisition.Job = Job.ID
                  LEFT JOIN Emp ON Emp.ID = Requisition.[User]
          WHERE   Emp.ID = ?
        ;",array($_SESSION['User']));
      }
      $data = array();
      if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
        $array['Date'] = date("m/d/Y h:i A",strtotime($array['Date']));
        $array['Required'] = date("m/d/Y",strtotime($array['Required']));
        $data[] = $array;
      }}
      print json_encode(array('data'=>$data));
    }
}?>
