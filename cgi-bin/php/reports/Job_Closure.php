<?php 
session_start();
require('index.php');
set_time_limit ( 600 );
ini_set('memory_limit','1024M');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($Portal,"
        SELECT User_Privilege, Group_Privilege, Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE User_ID = ? AND Access_Table='Job'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID']) || !is_array($My_Privileges)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4 && $My_Privileges['Other_Privilege'] >= 4){
			$r = sqlsrv_query($NEI,"
				SELECT Job.ID             AS ID,
                       Job.fDesc          AS Name,
                       JobType.Type       AS Type,
                       Job.fDate          AS Date,
                       Job_Status.Status  AS Status,
					   OwnerWithRol.Name  AS Customer,
					   OwnerWithRol.ID    AS Customer_ID,
                       Loc.Tag            AS Location,
					   Loc.Loc            AS Location_ID,
					   Estimate.Price     AS Proposal_Amount,
					   CASE WHEN (SELECT Sum(JobTItem.Budget) FROM nei.dbo.JobTItem WHERE JobTItem.Job = Job.ID) = Estimate.Price * 2 THEN Estimate.Price ELSE (SELECT Sum(JobTItem.Budget) FROM nei.dbo.JobTItem WHERE JobTItem.Job = Job.ID) END AS Budget_Amount,
					   Sum(Invoice.Total) AS Billed_Amount
                FROM   Job
                       LEFT JOIN nei.dbo.JobType       ON Job.Type       = JobType.ID
                       LEFT JOIN nei.dbo.Loc           ON Job.Loc        = Loc.Loc
                       LEFT JOIN nei.dbo.Job_Status    ON Job.Status + 1 = Job_Status.ID
					   LEFT JOIN nei.dbo.OwnerWithRol  ON Job.Owner      = OwnerWithRol.ID
					   LEFT JOIN nei.dbo.Estimate      ON Estimate.Job   = Job.ID
					   LEFT JOIN nei.dbo.Invoice       ON Invoice.Job    = Job.ID
                WHERE  Job.Type <> 9 
                       AND Job.Type <> 12
					   AND Job.Type <> 0
					   AND Job.Type <> 2
					   AND Job.fDesc NOT LIKE '%DO NOT USE%'
					   AND Job.Status <>       1
					   AND Job.Status <>       2
					   AND Job.Status <>       3
					   AND Job.ID <> 2
					   AND Job.ID <> 24966
				GROUP BY Job.ID, 
						 Job.fDesc, 
						 Job.fDate, 
						 Job_Status.Status, 
						 OwnerWithRol.Name, 
						 Loc.Tag, 
						 Estimate.Price, 
						 JobType.Type, 
						 OwnerWithRol.ID, 
						 Loc.Loc
				HAVING Count(Invoice.Ref) > 0 
				       AND (Sum(Invoice.Total) >= CASE WHEN Estimate.Price IS NULL THEN 0 ELSE Estimate.Price END AND (CASE WHEN (SELECT Sum(JobTItem.Budget) FROM nei.dbo.JobTItem WHERE JobTItem.Job = Job.ID) = Estimate.Price * 2 THEN Estimate.Price ELSE (SELECT Sum(JobTItem.Budget) FROM nei.dbo.JobTItem WHERE JobTItem.Job = Job.ID) END <= Sum(Invoice.Total)))
			;");
			$ID = 0;
			$Job = 0;
			$stmt = sqlsrv_prepare($NEI,"
				SELECT Top 1 
					   Tickets.*,
					   Loc.Tag                     AS Location,
					   Loc.Loc                     AS Location_ID,
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
					   Elev.ID                     AS Unit_ID,
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
							(SELECT TicketO.ID       AS Ticket_ID,
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
									TicketO.fWork    AS Mechanic,
									TickOStatus.Type AS Ticket_Status,
									0                AS Total,
									0                AS Regular,
									0                AS Overtime,
									0                AS Doubletime
							 FROM   nei.dbo.TicketO
									LEFT JOIN nei.dbo.TickOStatus ON TicketO.Assigned = TickOStatus.Ref
							 WHERE  TicketO.Job = ?
							)
							UNION ALL
							(SELECT TicketD.ID       AS Ticket_ID,
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
									TicketD.fWork    AS Mechanic,
									'Completed'      AS Ticket_Status,
									TicketD.Total    AS Total,
									TicketD.Reg      AS Regular,
									TicketD.OT       AS Overtime,
									TicketD.DT       AS Doubletime
							 FROM   nei.dbo.TicketD
							 WHERE  TicketD.Job = ?
							)
						) AS Tickets
						LEFT JOIN nei.dbo.Loc          ON Tickets.Location = Loc.Loc
						LEFT JOIN nei.dbo.Job          ON Tickets.Job      = Job.ID
						LEFT JOIN nei.dbo.Elev         ON Tickets.Unit     = Elev.ID
						LEFT JOIN nei.dbo.Emp          ON Tickets.Mechanic = Emp.fWork
						LEFT JOIN nei.dbo.JobType      ON Job.Type         = JobType.ID
						LEFT JOIN nei.dbo.Zone 		   ON Zone.ID          = Loc.Zone
						LEFT JOIN nei.dbo.Route		   ON Route.ID		   = Loc.Route
					WHERE Tickets.Ticket_Status <> 'Open'
						  AND Tickets.Ticket_Status <> 'Assigned'
					ORDER BY Tickets.Worked DESC
			;",array(&$ID,&$ID));
			if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
				$ID = $array['ID'];
				if(sqlsrv_execute($stmt)){
					$array2 = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
					if($array2['Ticket_ID'] == NULL || $array2['Ticket_ID'] == ''){continue;}
					$array = array_merge($array2,$array);
					$data[] = $array;
				}
			}}
        }
        print json_encode(array('data'=>$data));	
    }
}