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
    $r = $database->query($Portal,"
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
        $Keyword = $_GET['Keyword']
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4 && $My_Privileges['Other_Privilege'] >= 4){
            $r = $database->query(null,"
                SELECT Job.ID            AS  ID,
                       Job.fDesc         AS  Name,
                       JobType.Type      AS  Type,
                       Job.fDate         AS  Finished_Date,
                       Job_Status.Status AS  Status,
                       Loc.Tag           AS  Location
                FROM   nei.dbo.Job
                       LEFT JOIN nei.dbo.JobType      ON  Job.Type        = JobType.ID
                       LEFT JOIN nei.dbo.Loc          ON  Job.Loc         = Loc.Loc
                       LEFT JOIN nei.dbo.Job_Status   ON  Job.Status + 1  = Job_Status.ID
                       LEFT JOIN nei.dbo.OwnerWithRol ON  OwnerWithRol.ID = Loc.OwnerWithRol
                       LEFT JOIN nei.dbo.Elev         ON  Elev.Loc        = Loc.Loc
                WHERE  Job.ID               LIKE '%{$Keyword}%'
                       OR Job.fDesc         LIKE '%{$Keyword}%'
                       OR JobType.Type      LIKE '%{$Keyword}%'
                       OR Job.fDate         LIKE '%{$Keyword}%'
                       OR Job_Status.Status LIKE '%{$Keyword}%'
                       OR Loc.Tag           LIKE '%{$Keyword}%'
                       OR Loc.Loc           LIKE '%{$Keyword}%'
                       OR Elev.State        LIKE '%{$Keyword}%'
                       OR Elev.Unit         LIKE '%{$Keyword}%'
                       OR OwnerWithRol.Name LIKE '%{$Keyword}%'
            ;");
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}
            }
        } else {
            $SQL_Jobs = array();
            if($My_Privileges['Group_Privilege'] >= 4){
                $r = $database->query(null,"
                    SELECT Job AS Job
                    FROM   nei.dbo.TicketO
                           LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']);
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Jobs[] = "Job.ID='{$array['Job']}'";}
                $r = $database->query(null,"
                    SELECT Job AS Job
                    FROM   nei.dbo.TicketD
                           LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']);
                if($r){
                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Jobs[] = "Job.ID='{$array['Job']}'";}
                }

            }
            if($My_Privileges['User_Privilege'] >= 4){
                $r = $database->query(null,"
                    SELECT Job.ID          AS Job
                    FROM   nei.dbo.Job
                           LEFT JOIN nei.dbo.Loc   ON Elev.Loc   = Loc.Loc
                           LEFT JOIN nei.dbo.Route ON Loc.Route  = Route.ID
                           LEFT JOIN Emp   ON Route.Mech = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']);
                if($r){
                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Jobs[] = "Job.ID='{$array['Job']}'";}
                }
            }
            $SQL_Jobs = array_unique($SQL_Jobs);
            if(count($SQL_Jobs) > 0){
                $SQL_Jobs = implode(' OR ',$SQL_Jobs);
                $r = $database->query(null,"
                    SELECT 
                        Job.ID            AS ID,
                        Job.fDesc         AS Name,
                        JobType.Type      AS Type,
                        Job.fDate         AS Finished_Date,
                        Job_Status.Status AS Status,
                        Loc.Tag           AS Location
                    FROM 
                        nei.dbo.Job
                        LEFT JOIN nei.dbo.JobType      ON Job.Type           = JobType.ID
                        LEFT JOIN nei.dbo.Loc          ON Job.Loc            = Loc.Loc
                        LEFT JOIN nei.dbo.Job_Status   ON Job.Status + 1     = Job_Status.ID
                        LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.Owner = Loc.Owner
                        LEFT JOIN nei.dbo.Elev         ON Elev.Loc           = Loc.Loc
                    WHERE 
                        Loc.Maint = 1
                        AND (Job.ID             LIKE '%{$Keyword}%'
                        OR Job.fDesc            LIKE '%{$Keyword}%'
                        OR JobType.Type         LIKE '%{$Keyword}%'
                        OR Job.fDate            LIKE '%{$Keyword}%'
                        OR Job_Status.Status    LIKE '%{$Keyword}%'
                        OR Loc.Tag              LIKE '%{$Keyword}%'
                        OR Loc.Loc              LIKE '%{$Keyword}%'
                        OR Elev.State           LIKE '%{$Keyword}%'
                        OR Elev.Unit            LIKE '%{$Keyword}%'
                        OR OwnerWithRol.Name    LIKE '%{$Keyword}%')
                ;");
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
            }
        }
        print json_encode(array('data'=>utf8ize($data)));	
    }
}