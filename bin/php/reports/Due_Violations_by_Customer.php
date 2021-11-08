<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
	//Connection
    $result = $database->query(
    	null,
    	"	SELECT 	* 
    		FROM 	Connection 
    		WHERE 		Connector = ? 
    				AND Hash = ?;",
    	array(
    		$_SESSION['User'],
    		$_SESSION['Hash']
    	)
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = $database->query(
		null,
		"	SELECT 	*, 
					fFirst AS First_Name, 
					Last as Last_Name 
			FROM 	Emp 
			WHERE 	ID= ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$result = $database->query(null,
		" 	SELECT 	Privilege.*
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$Privileges = array();
	$Privileged = false;
	while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
	if(		isset($Privileges['Customer']) 
		&& 	$Privileges[ 'Customer' ][ 'User_Privilege' ]  >= 4 
		&& 	$Privileges[ 'Customer' ][ 'Group_Privilege' ] >= 4 
		&& 	$Privileges[ 'Customer' ][ 'Other_Privilege' ] >= 4){
				$Privileged = true;}
    if(		!isset($Connection['ID'])  
    	|| 	!is_numeric($_GET['ID']) 
    	|| !$Privileged 
    ){ ?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $data = array();
            $r = $database->query(null,"
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
					FROM   Job 	
						   LEFT JOIN Elev  ON Job.Elev       = Elev.ID
						   LEFT JOIN Loc   ON Job.Loc        = Loc.Loc
						   LEFT JOIN Zone  ON Loc.Zone       = Zone.ID
						   LEFT JOIN Route ON Loc.Route      = Route.ID
						   LEFT JOIN Emp   ON Route.Mech     = Emp.fWork
					WHERE  ((Job.fDesc LIKE '%CAT%' AND Job.fDesc LIKE '%DEF%')
						   OR Job.fDesc LIKE '%PVT%')
						   AND Job.Status = 0
						   AND Job.Owner = ?)
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
					FROM   Violation
						   LEFT JOIN Elev  ON Violation.Elev = Elev.ID
						   LEFT JOIN Loc   ON Violation.Loc  = Loc.Loc
						   LEFT JOIN Zone  ON Loc.Zone       = Zone.ID
						   LEFT JOIN Route ON Loc.Route      = Route.ID
						   LEFT JOIN Emp   ON Route.Mech     = Emp.fWork
						   LEFT JOIN Job   ON Violation.Job  = Job.ID
					WHERE  Violation.Remarks LIKE '%DUE: [0123456789][0123456789]/[0123456789][0123456789]/[0123456789][0123456789]%'
						   AND Violation.Status <> 'Dismissed'
						   AND Violation.ID     <> 0
						   AND Loc.Owner = ?
						   AND (Violation.Job = 0
								OR 
								(Violation.Job > 0
								AND Job.Status = 0)))) AS Violations
            ;",array($_GET['ID'],$_GET['ID']));
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
