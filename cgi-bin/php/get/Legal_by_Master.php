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
    if( isset($My_Privileges['Legal'], $My_Privileges['Customer']) 
        && (
				$My_Privileges['Legal']['Other_Privilege'] >= 4
			||	$My_Privileges['Customer']['Other_Privilege'] >= 4
		)
	 ){	$Privileged = True;	}
    if(!isset($Connection['ID']) || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $r = sqlsrv_query($NEI,"
            SELECT 
                Job.ID            AS  ID,
                Job.fDesc         AS  Name,
                JobType.Type      AS  Type,
                Job.fDate         AS  Finished_Date,
                Job_Status.Status AS  Status,
                Loc.Tag           AS  Location
            FROM 
                nei.dbo.Job
                LEFT JOIN nei.dbo.JobType       	ON  Job.Type               = JobType.ID
                LEFT JOIN nei.dbo.Loc           	ON  Job.Loc                = Loc.Loc
                LEFT JOIN nei.dbo.Job_Status    	ON  Job.Status + 1         = Job_Status.ID
                LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
            WHERE 
                Master_Account.Master = ?
                AND (	Job.Type = 9
                	OR 	Job.Type = 12)
        ;",array($_GET['ID']));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}?>