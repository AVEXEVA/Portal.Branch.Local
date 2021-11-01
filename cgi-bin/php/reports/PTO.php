<?php
session_start();
require('../index.php');
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
	 if( isset($My_Privileges['Ticket'])
        && (
				  $My_Privileges['Ticket']['User_Privilege'] >= 4
			||	$My_Privileges['Ticket']['Group_Privilege'] >= 4
			||	$My_Privileges['Ticket']['Other_Privilege'] >= 4)){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
	else {
    sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "pto.php"));
    $Vacation_Start = date("Y-m-d H:i:s") >= date("Y-06-01 00:00:00.000") ? date("Y-06-01 00:00:00.000") : date("Y-06-01 00:00:00.000",strtotime("-1 year"));
    $Medical_Start = date("Y-m-d H:i:s") >= date("Y-03-01 00:00:00.000") ? date("Y-03-01 00:00:00.000") : date("Y-03-01 00:00:00.000",strtotime("-1 year"));
    $Lieu_Start = date("Y-m-d H:i:s") >= date("Y-01-01 00:00:00.000") ? date("Y-01-01 00:00:00.000") : date("Y-01-01 00:00:00.000",strtotime("-1 year"));
    $resource = sqlsrv_query($NEI,
      " SELECT  TicketD.*
        FROM    nei.dbo.TicketD
                LEFT JOIN nei.dbo.Emp ON TicketD.fWork = Emp.fWork
        WHERE   Emp.ID = ?
                AND
                (
                  (
                      TicketD.Job = 100749
                      AND TicketD.EDate >= ?
                  )
                  OR
                  (
                      (
                          TicketD.Job = 102567
                          OR
                          TicketD.Job = 100751
                      )
                      AND TicketD.EDate >= ?
                  )
                  OR
                  (
                      TicketD.Job = 102467
                      AND TicketD.EDate >= ?
                  )
                )
        ORDER BY TicketD.EDate DESC
      ;",array($_SESSION['User'], $Vacation_Start, $Medical_Start, $Lieu_Start));
    $Jobs = array(
      102567 => 'Medical',
      100749 => 'Vacation',
      100751 => 'Sick',
      102467 => 'En Lieu'
    );
    $data = array();
    if($resource){while($row = sqlsrv_fetch_array($resource)){
      $row['Type'] = $Jobs[$row['Job']];
      $row['Date'] = date("m/d/Y",strtotime($row['EDate']));
      $data[] = $row;
    }}
    print json_encode(array('data'=>$data));
  }
}?>
