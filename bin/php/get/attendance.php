<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
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
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Time'])
        && (
				$Privileges['Time']['Group_Privilege'] >= 4
			||	$Privileges['Time']['User_Privilege'] >= 4
		)
	){$Privileged = True;}
	if(!isset($Connection['ID'])  ||  !$Privileged){print json_encode(array('data'=>array()));}
  else {
    if((isset($_GET['User']) && $Privileges['Time']['Other_Privilege'] >= 4) || (isset($_GET['User']) && $_GET['User'] == $_SESSION['User'])){
      $r = $database->query($Portal,"
        SELECT Attendance.*
        FROM   Portal.dbo.Attendance
        WHERE  Attendance.[User] = ?
      ;",array($_GET['User']));
      $data = array();
      if($r){while($row = sqlsrv_fetch_array($r)){
        $row['Total'] = (strtotime($row['End']) - strtotime($row['Start'])) / (60 * 60) > 0 ? (strtotime($row['End']) - strtotime($row['Start'])) / (60 * 60) : '0';
        $row['Start'] = $row['Start'] == '1899-12-30 00:00:00.000' || is_null($row['Start'])  ? '' : date("m/d/Y H:i A",strtotime($row['Start']));
        $row['End'] = $row['End'] == '1899-12-30 00:00:00.000' || is_null($row['End']) ? '' : date("m/d/Y H:i A",strtotime($row['End']));

        $row['Total'] = round($row['Total'],2);
        $data[] = $row;
      }}
    } elseif(isset($_GET['Supervisor'])  && strlen($_GET['Supervisor']) > 0 && $Privileges['Time']['Other_Privilege'] >= 4) {
      //$_GET['Start'] = date('Y-m-d H:i:s',strtotime($_GET['Start']));
      //$_GET['End'] = date('Y-m-d H:i:s',strtotime($_GET['End']));
      $r = $database->query($Portal,"
        SELECT Emp.ID AS ID,
               Emp.fFirst,
               Emp.Last
        FROM   Emp
               LEFT JOIN nei.dbo.tblWork 		ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
        WHERE  Emp.Field = 1
               AND tblWork.Super = ?
        ORDER BY Emp.Last ASC
      ;",array($_GET['Supervisor']));
      $data = array();
      $sQuery = "SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE Attendance.[User] = ? ORDER BY Attendance.[ID] DESC;";
      if($r){while($row = sqlsrv_fetch_array($r)){
        $r2 = $database->query($Portal, $sQuery, array($row['ID']));
        if($r2){
          $row2 = sqlsrv_fetch_array($r2);
          $row2 = is_array($row2) ? $row2 : array('Start'=>'1899-12-30 00:00:00.000', 'End'=>'1899-12-30 00:00:00.000');
        }
        $row['Start'] = $row2['Start'] == '1899-12-30 00:00:00.000' ? '' : date("m/d/Y H:i A",strtotime($row2['Start']));
        $row['End'] = $row2['End'] == '1899-12-30 00:00:00.000' ? '' : date("m/d/Y H:i A",strtotime($row2['End']));
        $data[] = $row;
      }}
    } elseif($Privileges['Time']['Other_Privilege'] >= 4) {
      $_GET['Start'] = date('Y-m-d H:i:s',strtotime($_GET['Start']));
      $_GET['End'] = date('Y-m-d H:i:s',strtotime($_GET['End']));
      $r = $database->query($Portal,"
        SELECT Emp.ID AS ID,
               Emp.fFirst,
               Emp.Last
        FROM   Emp
        WHERE  Emp.Field = 1
        ORDER BY Emp.Last ASC
      ;",array());
      $data = array();
      $sQuery = "SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE Attendance.[User] = ? ORDER BY Attendance.[ID] DESC;";
      if($r){while($row = sqlsrv_fetch_array($r)){
        $r2 = $database->query($Portal, $sQuery, array($row['ID']));
        if($r2){
          $row2 = sqlsrv_fetch_array($r2);
          $row2 = is_array($row2) ? $row2 : array('Start'=>'1899-12-30 00:00:00.000', 'End'=>'1899-12-30 00:00:00.000');
        }
        $row['Start'] = $row2['Start'] == '1899-12-30 00:00:00.000' ? '' : date("m/d/Y H:i A",strtotime($row2['Start']));
        $row['End'] = $row2['End'] == '1899-12-30 00:00:00.000' ? '' : date("m/d/Y H:i A",strtotime($row2['End']));
        $data[] = $row;
      }}
    }

    print json_encode(array("data"=>$data));
  }
}?>
