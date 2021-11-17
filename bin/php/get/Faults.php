<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $result = \singleton\database::getInstance( )->query(
        null,
      " SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($result);
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
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      "   SELECT  [Privilege].[Access],
                  [Privilege].[Owner],
                  [Privilege].[Group],
                  [Privilege].[Other]
        FROM      dbo.[Privilege]
        WHERE     Privilege.[User] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ]
      )
    );
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($result)){$Privileges[$array2['Access_Table']] = $array2;}
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
        $result = \singleton\database::getInstance( )->query(
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
        $result = \singleton\database::getInstance( )->query(
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
      if($result){while($row = sqlsrv_fetch_array($result)){
        $row['DateTime'] = date("m/d/Y h:i A",strtotime($row['DateTime']));
        $data[] = $row;
      }}
      print json_encode(array('data'=>$data));
	}
}?>
