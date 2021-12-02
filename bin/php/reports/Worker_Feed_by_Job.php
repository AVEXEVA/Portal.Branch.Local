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
	$My_User    = $database->query(null,"
		SELECT Emp.*, 
			   Emp.fFirst AS First_Name, 
			   Emp.Last   AS Last_Name 
		FROM   Emp
		WHERE  Emp.ID = ?
	;", array($_SESSION['User']));
	$My_User = sqlsrv_fetch_array($My_User); 
	$My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
	$r = $database->query(null,"
		SELECT Privilege.Access, 
			   Privilege.Owner, 
			   Privilege.Group, 
			   Privilege.Other
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
	$Privileged = False;
	if( isset($My_Privileges['Job']) 
	   	&& $My_Privileges['Job']['Other'] >= 4){
			$Privileged = True;} 
	elseif(isset($My_Privileges['Job']) 
		&& $My_Privileges['Job']['Group'] >= 4
		&& is_numeric($_GET['ID'])){
			$r = $database->query(null,"
				SELECT Job.Loc AS Location_ID
				FROM   Job
				WHERE  Job.ID = ?
			;", array($_GET['ID']));
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
			$r = $database->query(null,"
				SELECT Tickets.ID
				FROM 
				(
					(
						SELECT TicketO.ID
						FROM   TicketO
						WHERE  TicketO.LID       = ?
						       AND TicketO.fWork = ? 
					) 
					UNION ALL
					(
						SELECT TicketD.ID
						FROM   TicketD
						WHERE  TicketD.Loc       = ?
						       AND TicketD.fWork = ? 
					)
				) AS Tickets
			;", array($Location_ID, $My_User['fWork'],$Location_ID, $My_User['fWork'],$Location_ID, $My_User['fWork']));
			$Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
	elseif(isset($My_Privileges['Job'])
		&& $My_Privileges['Job']['Owner'] >= 4
		&& is_numeric($_GET['ID'])){
			$r = $database->query(null,"
				SELECT Tickets.ID
				FROM 
				(
					(
						SELECT TicketO.ID
						FROM   TicketO
						WHERE  TicketO.Job       = ?
						       AND TicketO.fWork = ? 
					) 
					UNION ALL
					(
						SELECT TicketD.ID
						FROM   TicketD
						WHERE  TicketD.Job       = ?
						       AND TicketD.fWork = ? 
					)
				) AS Tickets
			;", array($_GET['ID'], $My_User['fWork'],$_GET['ID'], $My_User['fWork'],$_GET['ID'], $My_User['fWork']));
			$Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
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
			WHERE  TicketO.Job           = ?
				   AND (TicketO.TimeComp = ''
				   	OR TicketO.TimeComp  = '1899-12-30 00:00:00.000')
		",array($_GET['ID']));
		if($r){while($array = sqlsrv_fetch_array($r)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   
	}
}?>