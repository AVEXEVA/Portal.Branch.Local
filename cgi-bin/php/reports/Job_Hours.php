<?php
session_start();
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
        " SELECT Tickets.Job                 AS Job_ID,
    			       Job.fDesc                   AS Job_Name,
    				   JobType.Type                AS Job_Type,
    				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
    				   SUM(Tickets.Total)          AS Job_Hours
    			FROM (
    					(
    						SELECT TicketD.Job   AS Job,
    							   TicketD.fWork AS Work_ID,
    							   TicketD.Total AS Total
    					    FROM   nei.dbo.TicketD
    						WHERE  TicketD.EDate >= '2018-01-01 00:00:00.000'
    							   AND TicketD.EDate < '2019-01-01 00:00:00.000'
    					)
    					UNION ALL
    					(
    						SELECT TicketDArchive.Job   AS Job,
    							   TicketDArchive.fWork AS Work_ID,
    							   TicketDArchive.Total AS Total
    						FROM   nei.dbo.TicketDArchive
    						WHERE  TicketDArchive.EDate >= '2018-01-01 00:00:00.000'
    							   AND TicketDArchive.EDate < '2019-01-01 00:00:00.000'
    					)
    				 ) AS Tickets
    				 LEFT JOIN nei.dbo.Job     ON Tickets.Job     = Job.ID
    				 LEFT JOIN nei.dbo.JobType ON Job.Type        = JobType.ID
    				 LEFT JOIN nei.dbo.Emp     ON Tickets.Work_ID = Emp.fWork
    			GROUP BY Tickets.Job, Job.fDesc, JobType.Type, Emp.fFirst + ' ' + Emp.Last
    		;");
        if($r){while($Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $Ticket;}}
        print json_encode(array('data'=>$data));
    }
}
sqlsrv_close($NEI);
?>
