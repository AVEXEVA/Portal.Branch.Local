<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
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
	 if( isset($My_Privileges['Ticket'],$My_Privileges['Job']) 
        && $My_Privileges['Ticket']['Other'] >= 4
	  	&& $My_Privileges['Job']['Other'] >= 4){
            $Privileged = True;}
	elseif(isset($My_Privileges['Ticket'],$My_Privileges['Job'])
		&& $My_Privileges['Ticket']['Group'] >= 4
		&& $My_Privileges['Job']['Group'] >= 4){
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
		&& $My_Privileges['Ticket']['Owner'] >= 4
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
	}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
	else {
        $r = $database->query(null,"
			SELECT Tickets.*,
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
			FROM (
					(SELECT TicketO.ID       AS ID,
							TicketO.fDesc    AS Description,
							''               AS Resolution,
							TicketO.CDate    AS Created,
							TicketO.DDate    AS Dispatched,
							TicketO.EDate    AS Worked,
							TicketO.TimeSite AS On_Site,
							TicketO.TimeComp AS Completed,
							TicketO.Who 	 AS Caller,
							TicketO.fBy      AS Reciever,
							TicketO.Level    AS Level,
							TicketO.Cat      AS Category,
							TicketO.LID      AS Location,
							TicketO.Job      AS Job,
							TicketO.LElev    AS Unit,
							TicketO.Owner    AS Owner,
							TicketO.fWork    AS Mechanic,
							TickOStatus.Type AS Status,
							0                AS Total,
							0                AS Regular,
							0                AS Overtime,
							0                AS Doubletime
					 FROM   TicketO
							LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
					 WHERE  TicketO.Job = ?
					)
					UNION ALL
					(SELECT TicketD.ID       AS ID,
							TicketD.fDesc    AS Description,
							TicketD.DescRes  AS Resolution,
							TicketD.CDate    AS Created,
							TicketD.DDate    AS Dispatched,
							TicketD.EDate    AS Worked,
							TicketD.TimeSite AS On_Site,
							TicketD.TimeComp AS Completed,
							TicketD.Who 	 AS Caller,
							TicketD.fBy      AS Reciever,
							TicketD.Level    AS Level,
							TicketD.Cat      AS Category,
							TicketD.Loc      AS Location,
							TicketD.Job      AS Job,
							TicketD.Elev     AS Unit,
							Loc.Owner        AS Owner,
							TicketD.fWork    AS Mechanic,
							'Completed'      AS Status,
							TicketD.Total    AS Total,
							TicketD.Reg      AS Regular,
							TicketD.OT       AS Overtime,
							TicketD.DT       AS Doubletime
					 FROM   TicketD
							LEFT JOIN Loc ON TicketD.Loc = Loc.Loc
					 WHERE  TicketD.Job = ?
					)
				) AS Tickets
				LEFT JOIN Loc          ON Tickets.Location = Loc.Loc
				LEFT JOIN Job          ON Tickets.Job      = Job.ID
				LEFT JOIN Elev         ON Tickets.Unit     = Elev.ID
				LEFT JOIN OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
				LEFT JOIN Emp          ON Tickets.Mechanic = Emp.fWork
				LEFT JOIN JobType      ON Job.Type         = JobType.ID
				LEFT JOIN Zone 		   ON Zone.ID          = Loc.Zone
				LEFT JOIN Route		   ON Route.ID		   = Loc.Route
			WHERE Tickets.Job = ?
			ORDER BY Tickets.ID DESC
		",array($_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
		$data = array();
		$row_count = sqlsrv_num_rows( $r );
		if($r){
			$i = 0;
			while($i < $row_count){
				$Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
				if(is_array($Ticket) && $Ticket != array()){
					$Tags = array();
					if(strpos($Ticket['Description'],"s/d") || strpos($Ticket['Description'],"S/D") || strpos($Ticket['Description'],"shutdown")){
						$Tags[] = "Shutdown";
					}
					if($Ticket['Level'] == 10){
						$Tags[] = "Maintenance";
					}
					if($Ticket['Level'] == 1){
						$Tags[] = "Service Call";
					}
					$Ticket['Tags'] = implode(", ",$Tags);
					$data[] = $Ticket;
				}
				$i++;
			}
		}
        print json_encode(array('data'=>$data));   
	}
?>