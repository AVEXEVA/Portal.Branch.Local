<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
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
  if( isset($Privileges['Route'])
      && $Privileges['Route']['Other_Privilege'] >= 4){$Privileged = True;}
  if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
  else {
      $r = \singleton\database::getInstance( )->query(
          null,
        " SELECT Route.ID   AS ID,
                 Route.Name AS Route,
                 Emp.fFirst AS First_Name,
                 Emp.Last	  AS Last_Name
          FROM   Route
                 LEFT JOIN Emp ON Route.Mech = Emp.fWork
          WHERE  Route.ID <> 76
	    ;");
      $data = array();
      if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
      print json_encode(array('data'=>$data));
  }
}?>
