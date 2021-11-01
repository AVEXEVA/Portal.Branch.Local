<?php 
session_start();
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
        SELECT * 
        FROM   Connection 
        WHERE  Connection.Connector = ? 
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = sqlsrv_query($NEI,"
        SELECT Emp.*, 
               Emp.fFirst AS First_Name, 
               Emp.Last   AS Last_Name 
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Privilege.Access_Table, 
               Privilege.User_Privilege, 
               Privilege.Group_Privilege, 
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Job']) 
        && (
				$My_Privileges['Job']['Other_Privilege'] >= 4
			||	$My_Privileges['Job']['Group_Privlege'] >= 4
			||  $My_Privileges['Job']['User_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4 && $My_Privileges['Other_Privilege'] >= 4){
			$r = sqlsrv_query($NEI,"
				SELECT Job.ID                  AS  ID,
                       Job.fDesc               AS  Name,
                       JobType.Type            AS  Type,
                       Job.fDate               AS  Finished_Date,
                       Job_Status.Status       AS  Status,
                       Loc.Tag                 AS  Location,
					   Job.Custom1             AS  Supervisor
                FROM   Job
                       LEFT JOIN nei.dbo.JobType       ON  Job.Type       = JobType.ID
                       LEFT JOIN nei.dbo.Loc           ON  Job.Loc        = Loc.Loc
                       LEFT JOIN nei.dbo.Job_Status    ON  Job.Status + 1 = Job_Status.ID
                WHERE  Job.Type <> 9 
                       AND Job.Type <> 12
					   AND Job.Template <> 1
					   AND Job.Template <> 16
					   AND Job.Template <> 13
					   AND Job.Template <> 31
					   AND Job.Template <> 32
					   AND (Job.Custom1  = ''
					   OR Job.Custom1  = ' ')
					   AND Job.Type <> 0 
					   AND Job.Type <> 20
					   AND Job.Status = 0
			;");
			if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        } else {
            $SQL_Jobs = array();
            if($My_Privileges['Group_Privilege'] >= 4){
                $r = sqlsrv_query($NEI,"
                    SELECT Job AS Job
                    FROM   TicketO
                           LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Jobs[] = "Job.ID='{$array['Job']}'";}
                $r = sqlsrv_query($NEI,"
                    SELECT Job AS Job
                    FROM   TicketD
                           LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){
                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Jobs[] = "Job.ID='{$array['Job']}'";}
                }
				$r = sqlsrv_query($NEI,"
                    SELECT Job AS Job
                    FROM   TicketDArchive
                           LEFT JOIN Emp ON TicketDArchive.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){
                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Jobs[] = "Job.ID='{$array['Job']}'";}
                }

            }
            if($My_Privileges['User_Privilege'] >= 4){
                $r = sqlsrv_query($NEI,"
                    SELECT Job.ID AS Job
                    FROM Job
                         LEFT JOIN nei.dbo.Loc   ON Elev.Loc = Loc.Loc
                         LEFT JOIN nei.dbo.Route ON Loc.Route = Route.ID
                         LEFT JOIN Emp   ON Route.Mech = Emp.fWork
                    WHERE Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){
                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Jobs[] = "Job.ID='{$array['Job']}'";}
                }
            }
            $SQL_Jobs = array_unique($SQL_Jobs);
            if(count($SQL_Jobs) > 0){
                $SQL_Jobs = implode(' OR ',$SQL_Jobs);
                $r = sqlsrv_query($NEI,"
                    SELECT Job.ID            AS  ID,
                           Job.fDesc         AS  Name,
                           JobType.Type      AS  Type,
                           Job.fDate         AS  Finished_Date,
                           Job_Status.Status AS  Status,
                           Loc.Tag           AS  Location
                    FROM   Job
                           LEFT JOIN nei.dbo.JobType    ON Job.Type       = JobType.ID
                           LEFT JOIN nei.dbo.Loc        ON Job.Loc        = Loc.Loc
                           LEFT JOIN nei.dbo.Job_Status ON Job.Status + 1 = Job_Status.ID
                    WHERE  ({$SQL_Jobs})    
					       AND Job_Status.Status = 'Open'
                ;");
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
            }
        }
        print json_encode(array('data'=>$data));	
    }
}