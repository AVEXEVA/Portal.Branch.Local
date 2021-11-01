<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID'],$_GET['ID']) || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
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
					 FROM   nei.dbo.TicketO
							LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
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
					 FROM   nei.dbo.TicketD
							LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
					)
					UNION ALL
					(SELECT TicketDArchive.ID       AS ID,
							TicketDArchive.fDesc    AS Description,
							TicketDArchive.DescRes  AS Resolution,
							TicketDArchive.CDate    AS Created,
							TicketDArchive.DDate    AS Dispatched,
							TicketDArchive.EDate    AS Worked,
							TicketDArchive.TimeSite AS On_Site,
							TicketDArchive.TimeComp AS Completed,
							TicketDArchive.Who 	    AS Caller,
							TicketDArchive.fBy      AS Reciever,
							TicketDArchive.Level    AS Level,
							TicketDArchive.Cat      AS Category,
							TicketDArchive.Loc      AS Location,
							TicketDArchive.Job      AS Job,
							TicketDArchive.Elev     AS Unit,
							Loc.Owner               AS Owner,
							TicketDArchive.fWork    AS Mechanic,
							'Completed'             AS Status,
							TicketDArchive.Total    AS Total,
							TicketDArchive.Reg      AS Regular,
							TicketDArchive.OT       AS Overtime,
							TicketDArchive.DT       AS Doubletime
					 FROM   nei.dbo.TicketDArchive
							LEFT JOIN nei.dbo.Loc ON TicketDArchive.Loc = Loc.Loc
					)
				) AS Tickets
				LEFT JOIN nei.dbo.Loc          ON Tickets.Location = Loc.Loc
				LEFT JOIN nei.dbo.Job          ON Tickets.Job      = Job.ID
				LEFT JOIN nei.dbo.Elev         ON Tickets.Unit     = Elev.ID
				LEFT JOIN nei.dbo.OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
				LEFT JOIN nei.dbo.Emp          ON Tickets.Mechanic = Emp.fWork
				LEFT JOIN nei.dbo.JobType      ON Job.Type         = JobType.ID
				LEFT JOIN nei.dbo.Zone 		   ON Zone.ID          = Loc.Zone
				LEFT JOIN nei.dbo.Route		   ON Route.ID		   = Loc.Route
			WHERE JobType.Type = 'Maintenance'
			      AND (
				  	(Tickets.Description    LIKE '%Edge%'
						AND Tickets.Description NOT LIKE '%Preventative%')
				  	OR (Tickets.Description LIKE '%Water%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Fire%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Door%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Motor%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Generator%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Controller%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Cable%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Traveler%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Hoist%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%CPU%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Board%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Sheave%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Description LIKE '%Edge%'
						AND Tickets.Description NOT LIKE '%Preventative%')
				  	OR (Tickets.Resolution  LIKE '%Water%'
						AND Tickets.Description NOT LIKE '%Water%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%Fire%'
						AND Tickets.Description NOT LIKE '%Fire%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%Door%'
						AND Tickets.Description NOT LIKE '%Door%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%Motor%'
						AND Tickets.Description NOT LIKE '%Motor%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%Generator%'
						AND Tickets.Description NOT LIKE '%Generator%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%Controller%'
						AND Tickets.Description NOT LIKE '%Controller%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%Cable%'
						AND Tickets.Description NOT LIKE '%Cable%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%Traveler%'
						AND Tickets.Description NOT LIKE '%Traveler%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%Hoist%'
						AND Tickets.Description NOT LIKE '%Hoist%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%CPU%'
						AND Tickets.Description NOT LIKE '%CPU%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%Board%'
						AND Tickets.Description NOT LIKE '%Board%'
						AND Tickets.Description NOT LIKE '%Preventative%')
					OR (Tickets.Resolution  LIKE '%Sheave%'
						AND Tickets.Description NOT LIKE '%Sheave%'
						AND Tickets.Description NOT LIKE '%Preventative%')
				  )
				  AND Tickets.Worked >= ?
			ORDER BY Tickets.ID DESC
		",array(date("2018-03-01 00:00:00.000")),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
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
}?>