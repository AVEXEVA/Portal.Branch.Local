<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID'],$_GET['ID']) 
	   || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
			SELECT Tickets.*,
				   Loc.ID            AS Account,
				   Loc.Tag           AS Tag,
				   Loc.Tag           AS Location,
				   Loc.Address       AS Address,
				   Loc.Address       AS Street,
				   Loc.City          AS City,
				   Loc.State         AS State,
				   Loc.Zip           AS Zip,
				   Job.ID            AS Job_ID,
				   Job.fDesc         AS Job_Description,
				   OwnerWithRol.ID   AS Owner_ID,
				   OwnerWithRol.Name AS Customer,
				   Elev.Unit         AS Unit_Label,
				   Elev.State        AS Unit_State,
				   Emp.fFirst        AS Worker_First_Name,
				   Emp.Last          AS Worker_Last_Name,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   'Unknown'         AS ClearPR,
				   JobType.Type      AS Job_Type,
				   Tickets.Date      AS Worked,
				   Route.Name        AS Route,
				   Zone.Name         AS Division
			FROM (
					(SELECT TicketO.ID       AS ID,
							TicketO.fDesc    AS Description,
							''               AS Resolution,
							TicketO.CDate    AS Created,
							TicketO.DDate    AS Dispatched,
							TicketO.EDate    AS Date,
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
							0                AS Doubletime,
							TicketO.fBy      AS Taken_By
					 FROM   nei.dbo.TicketO
							LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
					 WHERE  TicketO.LElev = ?
					)
					UNION ALL
					(SELECT TicketD.ID       AS ID,
							TicketD.fDesc    AS Description,
							TicketD.DescRes  AS Resolution,
							TicketD.CDate    AS Created,
							TicketD.DDate    AS Dispatched,
							TicketD.EDate    AS Date,
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
							TicketD.DT       AS Doubletime,
							TicketD.fBy      AS Taken_By
					 FROM   nei.dbo.TicketD
							LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
					 WHERE  TicketD.Elev = ?
					)
					UNION ALL
					(SELECT TicketDArchive.ID       AS ID,
							TicketDArchive.fDesc    AS Description,
							TicketDArchive.DescRes  AS Resolution,
							TicketDArchive.CDate    AS Created,
							TicketDArchive.DDate    AS Dispatched,
							TicketDArchive.EDate    AS Date,
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
							TicketDArchive.DT       AS Doubletime,
							TicketDArchive.fBy      AS Taken_By
					 FROM   nei.dbo.TicketDArchive
							LEFT JOIN nei.dbo.Loc ON TicketDArchive.Loc = Loc.Loc
					 WHERE  TicketDArchive.Elev = ?
					)
				) AS Tickets
				LEFT JOIN nei.dbo.Loc          ON Tickets.Location = Loc.Loc
				LEFT JOIN nei.dbo.Job          ON Tickets.Job      = Job.ID
				LEFT JOIN nei.dbo.Elev         ON Tickets.Unit     = Elev.ID
				LEFT JOIN nei.dbo.OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
				LEFT JOIN nei.dbo.Emp          ON Tickets.Mechanic = Emp.fWork
				LEFT JOIN nei.dbo.JobType      ON Job.Type         = JobType.ID
				LEFT JOIN nei.dbo.Route        ON Loc.Route        = Route.ID
				LEFT JOIN nei.dbo.Zone         ON Loc.Zone         = Zone.ID
			WHERE	JobType.Type = 'Maintenance'
		;",array($_GET['ID'],$_GET['ID'],$_GET['ID']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
		$data = array();
		$row_count = sqlsrv_num_rows( $r );
		if($r){
			while($i < $row_count){
				$Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
				if(is_array($Ticket) && $Ticket != array()){
					//Tags
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
					
					//On Site / Completed Time
					if($Ticket['On_Site'] == NULL || $Ticket['On_Site'] == ''){
						$Ticket['On_Site'] = 'None';
					} else {
						$Ticket['On_Site'] = date("H:i:s",strtotime($Ticket['On_Site']));
					}
					if($Ticket['Completed'] == NULL || $Ticket['Completed'] == ''){
						$Ticket['Completed'] = 'None';
					} else {
						$Ticket['Completed'] = date("H:i:s",strtotime($Ticket['Completed']));
					}
					if($Ticket['Created'] == NULL || $Ticket['Created'] == ''){
						$Ticket['Created'] = 'None';
					} else {
						$Ticket['Created'] = date("m/d/Y H:i:s",strtotime($Ticket['Created']));
					}
					if($Ticket['Dispatched'] == NULL || $Ticket['Dispatched'] == ''){
						$Ticket['Dispatched'] = 'None';
					} else {
						$Ticket['Dispatched'] = date("m/d/Y H:i:s",strtotime($Ticket['Dispatched']));
					}
					$data[] = $Ticket;
				}
				$i++;
			}
		}
		print json_encode(array('data'=>$data));  
    }
}
sqlsrv_close($NEI);
?>