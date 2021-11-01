<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT User_Privilege, Group_Privilege, Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE  User_ID = ? AND Access_Table='Location'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4 && $My_Privileges['Other_Privilege'] >= 4){
            $data = array();
			$r = sqlsrv_query($NEI,"
				SELECT Job.ID            AS ID,
					   Job.fDesc         AS Name,
					   Job.fDate         AS Date,
					   JobType.Type      AS Type,
					   Loc.Tag           AS Location,
					   Job.Remarks       AS Notes,
					   OwnerWithRol.Name AS Customer,
					   Rol.Contact       AS Contact,
					   Rol.Phone         AS Contact_Phone_Number,
					   Rol.EMail         AS Contact_Email,
					   Job_Status.Status AS Status
				FROM   nei.dbo.Job
					   LEFT JOIN JobType              ON Job.Type        = JobType.ID
					   LEFT JOIN nei.dbo.Loc          ON Job.Loc         = Loc.Loc
					   LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Job.Owner
					   LEFT JOIN nei.dbo.Terr         ON Terr.ID         = Loc.Terr
					   LEFT JOIN nei.dbo.Rol          ON Loc.Rol         = Rol.ID
					   LEFT JOIN nei.dbo.Job_Status   ON Job_Status.ID   = Job.Status
				WHERE  Job.Type <> 8
				       AND Job.Type <> 19
					   AND Job.Type <> 11
					   AND Job.Type <> 12
					   AND Job.Type <> 9
					   AND Job.Type <> 0
					   AND Job.Type <> 2
					   AND Job.fDesc NOT LIKE '%ECB%'
					   AND Job.fDesc NOT LIKE '%pvt%'
					   AND Job.fDesc NOT LIKE '%cat%'
					   AND Job.fDesc NOT LIKE '%def%'
					   AND Job.fDesc NOT LIKE '%annual%'
					   AND Job.fDesc NOT LIKE '%R*%'
					   AND Job.fDesc NOT LIKE '%*R%'
					   AND Job.fDesc NOT LIKE '%m/r%'
					   AND Job.fDesc NOT LIKE '%test'
					   AND Job.Status = 0
					   AND Job.Template <> 5
					   AND Job.Template <> 6
					   AND Job.Template <> 9
					   AND Job.Template <> 12
					   AND Job.Template <> 17
					   AND Job.Template <> 18
					   AND Job.Template <> 20
					   AND Job.Template <> 21
					   AND Job.Template <> 26
					   AND Job.Template <> 28
					   AND Job.Template <> 29
					   AND Job.Template <> 30
					   AND Job.fDesc NOT LIKE '%DO NOT USE%'
					   AND Job.fDesc NOT LIKE '%Extra Billing: X Call%'
				 	   AND Job.fDesc NOT LIKE '%2000%'
					   AND Job.fDesc NOT LIKE 'EB:'
					   AND Job.fDesc NOT LIKE 'S3365: One'
					   AND Job.fDesc NOT LIKE 'FOR CREDIT INPUT'
			;",array($_GET['ID']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
			if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
			$data = array();
			$row_count = sqlsrv_num_rows( $r );
			if($r){
				while($i < $row_count){
					$Job = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
					if(is_array($Job) && $Job != array()){
						$data[] = $Job;
					}
					$i++;
				}
			}
        }
        print json_encode(array('data'=>$data));  }
}