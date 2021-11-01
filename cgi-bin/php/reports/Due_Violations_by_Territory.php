<?php 
session_start();
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
        SELECT * 
        FROM   nei.dbo.Connection 
        WHERE  Connection.Connector = ? 
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = sqlsrv_query($NEI,"
        SELECT Emp.*, 
               Emp.fFirst AS First_Name, 
               Emp.Last   AS Last_Name 
        FROM   nei.dbo.Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Privilege.Access_Table, 
               Privilege.User_Privilege, 
               Privilege.Group_Privilege, 
               Privilege.Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Territory']) 
        && $My_Privileges['Territory']['Other_Privilege'] >= 4
	  	&& $My_Privileges['Violation']['Other_Privilege'] >= 4){
            $Privileged = True;} 
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
		$r = sqlsrv_query($NEI,"
			SELECT *
			FROM
				((SELECT 0					 	   AS ID,
					   Job.fDesc	               AS Name,
					   ''						   AS fDate,
					   'Job Created'   			   AS Status,
					   Loc.Tag                     AS Location,
					   Elev.State                  AS Unit,
					   Job.Custom1                 AS Division,
					   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
					   Job.ID 			           AS Job,
					   Job.Custom16 			   AS Due_Date,
					   '' 						   AS Remarks
				FROM   nei.dbo.Job 	
					   LEFT JOIN nei.dbo.Elev  ON Job.Elev       = Elev.ID
					   LEFT JOIN nei.dbo.Loc   ON Job.Loc        = Loc.Loc
					   LEFT JOIN nei.dbo.Zone  ON Loc.Zone       = Zone.ID
					   LEFT JOIN nei.dbo.Route ON Loc.Route      = Route.ID
					   LEFT JOIN nei.dbo.Emp   ON Route.Mech     = Emp.fWork
				WHERE  ((Job.fDesc LIKE '%CAT%' AND Job.fDesc LIKE '%DEF%')
					   OR Job.fDesc LIKE '%PVT%')
					   AND Job.Status = 0
					   AND Loc.Terr = ?)
				UNION ALL
				(SELECT Violation.ID               AS ID,
					   Violation.Name              AS Name,
					   Violation.fdate             AS fDate,
					   Violation.Status            AS Status,
					   Loc.Tag                     AS Location,
					   Elev.State                  AS Unit,
					   Zone.Name                   AS Division,
					   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
					   Violation.Job 			   AS Job,
					   SUBSTRING(Violation.Remarks,CHARINDEX('DUE: ',Violation.Remarks)+5,8) AS Due_Date,
					   '' 						   AS Remarks
				FROM   nei.dbo.Violation
					   LEFT JOIN nei.dbo.Elev  ON Violation.Elev = Elev.ID
					   LEFT JOIN nei.dbo.Loc   ON Violation.Loc  = Loc.Loc
					   LEFT JOIN nei.dbo.Zone  ON Loc.Zone       = Zone.ID
					   LEFT JOIN nei.dbo.Route ON Loc.Route      = Route.ID
					   LEFT JOIN nei.dbo.Emp   ON Route.Mech     = Emp.fWork
					   LEFT JOIN nei.dbo.Job   ON Violation.Job  = Job.ID
				WHERE  Violation.Remarks LIKE '%DUE: [0123456789][0123456789]/[0123456789][0123456789]/[0123456789][0123456789]%'
					   AND Violation.Status <> 'Dismissed'
					   AND Violation.ID     <> 0
					   AND Loc.Terr = ?
					   AND (Violation.Job = 0
							OR 
							(Violation.Job > 0
							AND Job.Status = 0)))) AS Violations
		;",array($_GET['ID'],$_GET['ID']));
		if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
		if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
			if($array['Due_Date'] == '' || is_null($array['Due_Date'])){
				$array['Due_Date'] = '01/01/99';
			} else {
				if($array['ID'] > 0){
				} else {
					$array['Due_Date'] = substr($array['Due_Date'],0,5) . "." .substr($array['Due_Date'],8,2);
				}
				$array['Due_Date'] = str_replace(".","-",$array['Due_Date']);
			}
			unset($array['Remarks']);
			$data[] = $array;
		}}
		$data2 = array();
		if(count($data) > 0){
			foreach($data as $array){
				$data2[$array['Job']] = $array;
			}
			$data = array();
			foreach($data2 as $Job=>$array){
				$data[] = $array;
			}
		}
	    print json_encode(array('data'=>$data));
    }
}?>
