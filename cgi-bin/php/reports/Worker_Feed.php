<?php 
session_start();
require('index.php');
set_time_limit(120);
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE  User_ID='{$_SESSION['User']}'
    ;");
	$My_Privileges = array();
	while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
	$Privileged = FALSE;
	if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
	elseif($My_Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
		$r = sqlsrv_query(  $NEI,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.LID='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
		$r2 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Loc='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
		$r3 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Loc='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
		$r = sqlsrv_fetch_array($r);
		$r2 = sqlsrv_fetch_array($r2);
		$r3 = sqlsrv_fetch_array($r3);
		$Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
	}
    if(!isset($array['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        $r = sqlsrv_query($NEI,"
			SELECT TicketO.ID                  AS ID,
				   TicketO.fDesc               AS Description,
				   ''                          AS Resolution,
				   TicketO.CDate               AS Created,
				   TicketO.DDate               AS Dispatched,
				   TicketO.EDate               AS Worked,
				   TicketO.TimeSite            AS On_Site,
				   TicketO.TimeComp            AS Completed,
				   TicketO.Who 	               AS Caller,
				   TicketO.fBy                 AS Reciever,
				   TicketO.Level               AS Level,
				   TicketO.Cat                 AS Category,
				   TicketO.LID                 AS Location,
				   TicketO.Job                 AS Job,
				   TicketO.LElev               AS Unit,
				   TicketO.Owner               AS Owner,
				   TicketO.fWork               AS Mechanic,
				   TickOStatus.Type            AS Status,
				   0                           AS Total,
				   0                           AS Regular,
				   0                           AS Overtime,
				   0                           AS Doubletime,
				   Loc.ID                      AS Customer,
				   Loc.Tag                     AS Location,
				   Loc.Address                 AS Address,
				   Loc.Address                 AS Street,
				   Loc.City                    AS City,
				   Loc.State                   AS State,
				   Loc.Zip                     AS Zip,
				   Route.Name 		           AS Route,
				   Zone.Name 		           AS Division,
				   Loc.Maint 		           AS Maintenance,
				   Job.ID                      AS Job_ID,
				   Job.fDesc                   AS Job_Description,
				   OwnerWithRol.ID             AS Owner_ID,
				   OwnerWithRol.Name           AS Customer,
				   Elev.Unit                   AS Unit_Label,
				   Elev.State                  AS Unit_State,
				   Elev.fDesc				   AS Unit_Description,
				   Elev.Type 				   AS Unit_Type,
				   Emp.fFirst                  AS First_Name,
				   Emp.Last                    AS Last_Name,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   'Unknown'                   AS ClearPR,
				   JobType.Type                AS Job_Type
				   
			FROM   nei.dbo.TicketO
				   LEFT JOIN TickOStatus ON TicketO.Assigned          = TickOStatus.Ref
				   LEFT JOIN nei.dbo.Loc          ON TicketO.LID      = Loc.Loc
				   LEFT JOIN nei.dbo.Job          ON TicketO.Job      = Job.ID
				   LEFT JOIN nei.dbo.Elev         ON TicketO.Unit     = Elev.ID
				   LEFT JOIN nei.dbo.OwnerWithRol ON TicketO.Owner    = OwnerWithRol.ID
				   LEFT JOIN nei.dbo.Emp          ON TicketO.Mechanic = Emp.fWork
				   LEFT JOIN nei.dbo.JobType      ON Job.Type         = JobType.ID
				   LEFT JOIN nei.dbo.Zone 		  ON Zone.ID          = Loc.Zone
				   LEFT JOIN nei.dbo.Route		  ON Route.ID		  = Loc.Route
			WHERE 
				TicketO.LID = ?
				AND TicketO.Level = 1
		",array($_GET['ID']));
        print json_encode(array('data'=>$data));   
	}
}?>