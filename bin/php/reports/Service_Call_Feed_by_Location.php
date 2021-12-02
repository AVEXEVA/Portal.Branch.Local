<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
set_time_limit(120);
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query(null,"
        SELECT Access, Owner, Group, Other
        FROM   Privilege
        WHERE  User_ID='{$_SESSION['User']}'
    ;");
	$My_Privileges = array();
	while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
	$Privileged = FALSE;
	if(isset($My_Privileges['Location']) && $My_Privileges['Location']['Owner'] >= 4 && $My_Privileges['Location']['Group'] >= 4 && $My_Privileges['Location']['Other'] >= 4){$Privileged = TRUE;}
	elseif($My_Privileges['Location']['Owner'] >= 4 && is_numeric($_GET['ID'])){
		$r  = $database->query( null,"SELECT * FROM TicketO        WHERE TicketO.LID        = ? AND fWork=?;",array($_GET['ID'],$My_User['fWork']));
		$r2 = $database->query( null,"SELECT * FROM TicketD        WHERE TicketD.Loc        = ? AND fWork=?;",array($_GET['ID'],$My_User['fWork']));
		$r3 = $database->query( null,"SELECT * FROM TicketDArchive WHERE TicketDArchive.Loc = ? AND fWork=?;",array($_GET['ID'],$My_User['fWork']));
		$r  = sqlsrv_fetch_array($r);
		$r2 = sqlsrv_fetch_array($r2);
		$r3 = sqlsrv_fetch_array($r3);
		$Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
	}
    if(!isset($array['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        $r = $database->query(null,"
			SELECT TicketO.ID                  AS ID,
				   TicketO.ID                  AS Ticket_ID,
				   TicketO.fDesc               AS Description,
				   ''                          AS Resolution,
				   TicketO.CDate               AS Created,
				   TicketO.DDate               AS Dispatched,
				   TicketO.EDate               AS Worked,
				   TicketO.EDate               AS Scheduled,
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
				   Elev.ID 					   AS Unit_ID,
				   Elev.Unit                   AS Unit_Label,
				   Elev.State                  AS Unit_State,
				   Elev.fDesc				   AS Unit_Description,
				   Elev.Type 				   AS Unit_Type,
				   Emp.fFirst                  AS First_Name,
				   Emp.Last                    AS Last_Name,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   'Unknown'                   AS ClearPR,
				   JobType.Type                AS Job_Type
				   
			FROM   TicketO
				   LEFT JOIN TickOStatus  ON TicketO.Assigned = TickOStatus.Ref
				   LEFT JOIN Loc          ON TicketO.LID      = Loc.Loc
				   LEFT JOIN Job          ON TicketO.Job      = Job.ID
				   LEFT JOIN Elev         ON TicketO.LElev    = Elev.ID
				   LEFT JOIN OwnerWithRol ON TicketO.Owner    = OwnerWithRol.ID
				   LEFT JOIN Emp          ON TicketO.fWork    = Emp.fWork
				   LEFT JOIN JobType      ON Job.Type         = JobType.ID
				   LEFT JOIN Zone 		  ON Zone.ID          = Loc.Zone
				   LEFT JOIN Route		  ON Route.ID		  = Loc.Route
			WHERE 
				TicketO.LID = ?
				AND TicketO.Level = 1
		",array($_GET['ID']));
		if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   
	}
}?>