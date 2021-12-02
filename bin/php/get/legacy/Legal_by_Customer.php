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
    if( isset($My_Privileges['Legal'], $My_Privileges['Customer']) 
        && (
				$My_Privileges['Legal']['Other'] >= 4
			||	$My_Privileges['Customer']['Other'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        $r = $database->query(null,"
            SELECT 
                Job.ID            AS  ID,
                Job.fDesc         AS  Name,
                JobType.Type      AS  Type,
                Job.fDate         AS  Finished_Date,
                Job_Status.Status AS  Status,
                Loc.Tag           AS  Location
            FROM 
                nei.dbo.Job
                LEFT JOIN nei.dbo.JobType    ON  Job.Type       = JobType.ID
                LEFT JOIN nei.dbo.Loc        ON  Job.Loc        = Loc.Loc
                LEFT JOIN nei.dbo.Job_Status ON  Job.Status + 1 = Job_Status.ID
            WHERE 
                Job.Type 	= 9 
                OR Job.Type = 12
        ;");
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}?>
        $r = $database->query(null,"
            SELECT 
                Job.ID            AS  ID,
                Job.fDesc         AS  Name,
                JobType.Type      AS  Type,
                Job.fDate         AS  Finished_Date,
                Job_Status.Status AS  Status,
                Loc.Tag           AS  Location
            FROM 
                Job
                LEFT JOIN nei.dbo.JobType    ON Job.Type       = JobType.ID
                LEFT JOIN nei.dbo.Loc        ON Job.Loc        = Loc.Loc
                LEFT JOIN nei.dbo.Job_Status ON Job.Status + 1 = Job_Status.ID
            WHERE 
                Loc.Owner     = ?
                AND (Job.Type = 9
                  OR Job.Type  = 12)
        ;",array($_GET['ID']));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}?>