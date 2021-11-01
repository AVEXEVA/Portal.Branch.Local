<?php
session_start();
require('index.php');
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
                SELECT Job.ID            AS  ID,
                       Job.fDesc         AS  Name,
                       Job.Custom1       AS  Supervisor,
                       JobType.Type      AS  Type,
                       Job.fDate         AS  Finished_Date,
                       Job_Status.Status AS  Status,
                       Job.Custom20      AS Project_Manager,
                       Loc.Tag           AS  Location,
                       (SELECT Sum(TicketD.Total) FROM nei.dbo.TicketD WHERE TicketD.Job = Job.ID AND TicketD.EDate >= (SELECT DATEADD(wk, DATEDIFF(wk,18,GETDATE()), 3))    AND TicketD.EDate < (SELECT DATEADD(wk, DATEDIFF(wk, 6, GETDATE()), 3))) AS Last_Week,
                       (SELECT Sum(TicketD.Total) FROM nei.dbo.TicketD WHERE TicketD.Job = Job.ID AND TicketD.EDate >= (SELECT DATEADD(wk, DATEDIFF(wk,6,GETDATE()), 3))    AND TicketD.EDate < (SELECT DATEADD(wk, DATEDIFF(wk, 0, GETDATE()), 3))) AS This_Week,
                       (SELECT Sum(TicketD.Total) FROM nei.dbo.TicketD WHERE TicketD.Job = Job.ID) AS Total_Hours,
                       Job.BHour               AS  Budgeted_Hours,
                       (Job.BHour - (SELECT Sum(TicketD.Total) FROM nei.dbo.TicketD WHERE TicketD.Job = Job.ID))
                                               AS  Balance,
						(Select Sum(TicketD.OT) + Sum(TicketD.DT) FROM nei.dbo.TicketD WHERE TicketD.Job = Job.ID) AS OT_DT
                FROM   nei.dbo.Job
                       LEFT JOIN nei.dbo.JobType    ON  Job.Type = JobType.ID
                       LEFT JOIN nei.dbo.Loc        ON  Job.Loc = Loc.Loc
                       LEFT JOIN nei.dbo.Job_Status ON  Job.Status = Job_Status.ID
                WHERE  Job.Status   = 0
                       AND Job.Type = 2
            ;");
            if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        }
        print json_encode(array('data'=>$data));
    }
}
