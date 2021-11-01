<?php 
session_start();
require('index.php');
set_time_limit ( 120 );
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
				SELECT Job.ID                  AS ID,
                       Job.fDesc               AS Name,
                       JobType.Type            AS Type,
                       Job.fDate               AS Date,
                       Job_Status.Status       AS Status,
					   OwnerWithRol.Name       AS Customer,
                       Loc.Tag                 AS Location
                FROM   Job
                       LEFT JOIN nei.dbo.JobType       ON Job.Type       = JobType.ID
                       LEFT JOIN nei.dbo.Loc           ON Job.Loc        = Loc.Loc
                       LEFT JOIN nei.dbo.Job_Status    ON Job.Status + 1 = Job_Status.ID
					   LEFT JOIN nei.dbo.OwnerWithRol  ON Job.Owner = OwnerWithRol.ID
                WHERE  Job.Type <> 9 
                       AND Job.Type <> 12
					   AND Job.fDate > '2017-01-01 00:00:00.000'
			;");
			$ID = 0;
			$stmt = sqlsrv_prepare($NEI,"
				SELECT Top 1 Tickets.*
					FROM (
							(SELECT TicketO.ID       AS ID,
									TicketO.fDesc    AS Description,
									''               AS Resolution,
									TicketO.EDate    AS Worked,
									TickOStatus.Type AS Status
							 FROM   nei.dbo.TicketO
									LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
							 WHERE  TicketO.Job = ?
							)
							UNION ALL
							(SELECT TicketD.ID       AS ID,
									TicketD.fDesc    AS Description,
									TicketD.DescRes  AS Resolution,
									TicketD.EDate    AS Worked,
									'Completed'      AS Status
							 FROM   nei.dbo.TicketD
							 WHERE  TicketD.Job = ?
							)
						) AS Tickets
					WHERE Tickets.Status <> 'Open'
						  AND Tickets.Status <> 'Assigned'
					ORDER BY Tickets.Worked DESC
			;",array(&$ID,&$ID));
			if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
				$ID = $array['ID'];
				if(sqlsrv_execute($stmt)){
					$array2 = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
					if($array2['ID'] == NULL || $array2['ID'] == '' || $array2['Description'] == ''){continue;}
					$array['Ticket_ID'] = $array2['ID'];
					$array['Ticket_Description'] = $array2['Description'];
					$array['Ticket_Resolution'] = $array2['Resolution'];
					$array['Ticket_Date'] = $array2['Worked'];
					$data[] = $array;
				} else {
					continue;
				}
			}}
        }
        print json_encode(array('data'=>$data));	
    }
}