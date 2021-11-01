<?php
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
			SELECT
				   Tickets.Job                 AS Job_ID,
			       Job.fDesc                   AS Job_Name,
				   JobType.Type                AS Job_Type,
				   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
				   Emp.Ref                     AS Employee_ID,
				   PRWage.Reg 				   AS Wage_Regular,
            	   PRWage.OT1                  AS Wage_Overtime,
            	   PRWage.OT2                  AS Wage_Double_Time,
				   SUM(Tickets.Reg)            AS Job_Regular_Hours,
				   SUM(Tickets.OT)             AS Job_Overtime_Hours,
				   SUM(Tickets.DT)             AS Job_Double_Time_Hours,
				   SUM(Tickets.TT)             AS Job_Travel_Time_Hours,
				   SUM(Tickets.Total)          AS Job_Total_Hours,
				   CAST(Tickets.Work_Date AS DATE)    	   AS Work_Date

			FROM (
					(
						SELECT TicketD.Job   AS Job,
							   TicketD.fWork AS Work_ID,
							   TicketD.EDate AS Work_Date,
							   TicketD.Reg   AS Reg,
							   TicketD.OT    AS OT,
							   TicketD.DT    AS DT,
							   TicketD.TT    AS TT,
							   TicketD.Total AS Total
					    FROM   nei.dbo.TicketD
						WHERE  TicketD.EDate >= '2018-01-01 00:00:00.000'
							   AND TicketD.EDate < '2019-01-01 00:00:00.000'
					)
					UNION ALL
					(
						SELECT TicketDArchive.Job   AS Job,
							   TicketDArchive.fWork AS Work_ID,
							   TicketDArchive.EDate AS Work_Date,
							   TicketDArchive.Reg   AS Reg,
							   TicketDArchive.OT    AS OT,
							   TicketDArchive.DT    AS DT,
							   TicketDArchive.TT    AS TT,
							   TicketDArchive.Total AS Total
						FROM   nei.dbo.TicketDArchive
						WHERE  TicketDArchive.EDate >= '2017-01-01 00:00:00.000'
							   AND TicketDArchive.EDate < '2018-01-01 00:00:00.000'
					)
				 ) AS Tickets
				 LEFT JOIN nei.dbo.Job     ON Tickets.Job     = Job.ID
				 LEFT JOIN nei.dbo.JobType ON Job.Type        = JobType.ID
				 LEFT JOIN nei.dbo.Emp     ON Tickets.Work_ID = Emp.fWork
				 LEFT JOIN nei.dbo.PRWage  ON Emp.WageCat     = PRWage.ID
			WHERE Tickets.Job > 0
			GROUP BY Tickets.Job, Job.fDesc, JobType.Type, Emp.fFirst + ' ' + Emp.Last, Emp.Ref, PRWage.Reg, PRWage.OT1, PRWage.OT2, CAST(Tickets.Work_Date AS DATE)
		;");
		if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
        if($r){while($Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
			/*$Week_Number = intval(date('W',strtotime($Ticket['Work_Date'])));
			$dayOfWeek = date("l",$Ticket['Work_Date']);*/
			/*$Week_Ending_On = date("Y-m-d H:i:s",strtotime('next Wednesday',$Ticket['Work_Date']));
			$Ticket['Week_Ending_On'] = date("Y-m-d H:i:s",strtotime('next Wednesday',$Ticket['Work_Date']));*/
			if(date("l",strtotime($Ticket['Work_Date'])) != "Wednesday"){
				$Ticket['Week_Ending_On'] = date("Y-m-d",strtotime("next Wednesday {$Ticket['Work_Date']}"));
			} else {
				$Ticket['Week_Ending_On'] = date("Y-m-d",strtotime($Ticket['Work_Date']));
			}
			$Ticket['Work_Date'] = date('Y-m-d',strtotime($Ticket['Work_Date']));
			$data[] = $Ticket;
		}}
        print json_encode(array('data'=>$data));
    }
}
sqlsrv_close($NEI);
?>
