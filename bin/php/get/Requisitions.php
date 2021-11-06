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
    $User    = $database->query(null,"
        SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = $database->query($Portal,"
        SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Requisition'])
        && (
			$Privileges['Requisition']['User_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
      if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
  			$parameters[] = $_GET['ID'];
  			$conditions[] = "Requisition.ID LIKE '%' + ? + '%'";
  		}
  		
      if( isset($_GET[ 'Full_Name' ] ) && !in_array( $_GET[ 'Full_Name' ], array( '', ' ', null ) ) ){
  			$parameters[] = $_GET['Full_Name'];
  			$conditions[] = "Employee.Emp.fFirst + ' ' + Emp.Last LIKE '%' + ? + '%'";
  		}
		  



      if($Privileges['Requisition']['Other_Privilege'] >= 4){
        $r = $database->query(null,
        " SELECT  Requisition.ID,
                  Emp.fFirst + ' ' + Emp.Last AS [User],
                  Requisition.[Date],
                  Requisition.[Required],
                  Loc.Tag AS Location,
                  DropOff.Tag AS DropOff,
                  Elev.State AS Unit,
                  Job.fDesc AS Job
          FROM    Requisition
                  LEFT JOIN Loc ON Requisition.Location = Loc.Loc
                  LEFT JOIN Loc AS DropOff ON Requisition.DropOff = DropOff.Loc
                  LEFT JOIN Elev ON Requisition.Unit = Elev.ID
                  LEFT JOIN Job ON Requisition.Job = Job.ID
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
          FROM    Requisition
                  LEFT JOIN Loc ON Requisition.Location = Loc.Loc
                  LEFT JOIN Loc AS DropOff ON Requisition.DropOff = DropOff.Loc
                  LEFT JOIN Elev ON Requisition.Unit = Elev.ID
                  LEFT JOIN Job ON Requisition.Job = Job.ID
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
