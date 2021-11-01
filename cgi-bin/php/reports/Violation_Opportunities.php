<?php
session_start();
require('../../../cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT *
		FROM   nei.dbo.Connection
		WHERE  Connection.Connector = ?
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   nei.dbo.Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Portal.dbo.Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID'])
	   	|| !isset($My_Privileges['Map'])
	  		|| $My_Privileges['Map']['User_Privilege']  < 4
	  		|| $My_Privileges['Map']['Group_Privilege'] < 4
	  	    || $My_Privileges['Map']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
      $r = sqlsrv_query($NEI,
        " SELECT  Loc.Tag AS Location_Tag,
                  Count(Violation.ID) AS Violation_Count,
                  Emp.fFirst + ' ' + Emp.Last AS Employee_Name,
                  Zone.Name AS Division,
                  tblWork.Super AS Supervisor,
                  TicketO.Level AS Level
          FROM    nei.dbo.TicketO
                  LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
                  LEFT JOIN nei.dbo.Emp ON Emp.fWork = TicketO.fWork
                  LEFT JOIN nei.dbo.Violation ON Violation.Loc = Loc.Loc
                  LEFT JOIN nei.dbo.Job ON Violation.Job = Job.ID
                  LEFT JOIN nei.dbo.Zone ON Zone.ID = Loc.Zone
                  LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
          WHERE   Violation.Status = 'Job Created'
                  AND Job.Status = 0
                  AND TicketO.Assigned = 3
                  AND tblWork.Super LIKE '%division%'
          GROUP BY Loc.Tag, Zone.Name, Emp.fFirst + ' ' + Emp.Last, Zone.Name, tblWork.Super, TicketO.Level
          ORDER BY Zone.Name ASC
        ;",array());
      $rows = array();
      if($r){while($row = sqlsrv_fetch_array($r)){
          $rows[] = $row;
      }}
      print json_encode(array("data"=>$rows));
  }
}?>
