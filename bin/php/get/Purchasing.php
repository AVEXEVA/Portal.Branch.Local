<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,
      " SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $User    = $database->query(null,
      " SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = $database->query($Portal,
      " SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?;",
    array($_SESSION['User']));
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Procurement'])
        && (
			$Privileges['Procurement']['User_Privilege'] >= 4
  &&  $Privileges['Procurement']['Group_Privilege'] >= 4
  &&  $Privileges['Procurement']['Other_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        if($Privileges['User_Privilege'] >= 4 && $Privileges['Group_Privilege'] >= 4 && $Privileges['Other_Privilege'] >= 4){
            $r = $database->query(null,
              " SELECT Mod_Equipment.*,
                       Loc.Tag AS Location
                FROM   Mod_Equipment
                       LEFT JOIN Modernization ON Mod_Equipment.Modernization = Modernization.ID
                       LEFT JOIN Job              ON Job.ID                      = Modernization.Job
                       LEFT JOIN Loc              ON Loc.Loc                     = Job.Loc

            ;");
            if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
        }
        print json_encode(array('data'=>$data));
    }
}?>
