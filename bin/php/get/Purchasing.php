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
      " SELECT Privilege.Access,
               Privilege.Owner,
               Privilege.Group,
               Privilege.Other
        FROM   Privilege
        WHERE  Privilege.User_ID = ?;",
    array($_SESSION['User']));
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Procurement'])
        && (
			$Privileges['Procurement']['Owner'] >= 4
  &&  $Privileges['Procurement']['Group'] >= 4
  &&  $Privileges['Procurement']['Other'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        if($Privileges['Owner'] >= 4 && $Privileges['Group'] >= 4 && $Privileges['Other'] >= 4){
            $r = \singleton\database::getInstance( )->query(
                null,
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
