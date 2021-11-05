<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
	//Connection
    $result = sqlsrv_query(
    	$NEI, 
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
	$result = sqlsrv_query(
		$NEI,
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
	$result = sqlsrv_query($NEI,
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
    ){ print json_encode( array( 'data' => array( ) ) ); }
    else {
        $data = array();
        $r = sqlsrv_query($NEI,"
			SELECT Workers.ID,
				   Workers.First_Name,
				   Workers.Last_Name,
				   Sum(Workers.Regular) AS Regular,
				   Sum(Workers.Overtime)      AS Overtime,
				   Sum(Workers.Doubletime)      AS Doubletime,
				   Sum(Workers.Total)   AS Total
			FROM 
			(
				(
					SELECT   Emp.ID             AS ID,
							 Emp.fFirst         AS First_Name,
							 Emp.Last           AS Last_Name,
							 0   AS Regular,
							 0    AS Overtime,
							 0    AS Doubletime,
							 0 AS Total 
					FROM     TicketO
							 LEFT JOIN Loc          ON  TicketO.LID      = Loc.Loc
							 LEFT JOIN Job          ON  TicketO.Job      = Job.ID
							 LEFT JOIN OwnerWithRol ON  TicketO.Owner    = OwnerWithRol.ID
							 LEFT JOIN JobType      ON  Job.Type         = JobType.ID
							 LEFT JOIN Elev         ON  TicketO.LElev    = Elev.ID
							 LEFT JOIN TickOStatus  ON  TicketO.Assigned = TickOStatus.Ref
							 LEFT JOIN Emp          ON  TicketO.fWork    = Emp.fWork
					WHERE    Job.Owner = ?
					GROUP BY Emp.ID, 
							 Emp.fFirst, 
							 Emp.Last
				)
				UNION ALL
				(
					SELECT   Emp.ID             AS ID,
							 Emp.fFirst         AS First_Name,
							 Emp.Last           AS Last_Name,
							 Sum(TicketD.Reg)   AS Regular,
							 Sum(TicketD.OT)    AS Overtime,
							 Sum(TicketD.DT)    AS Doubletime,
							 Sum(TicketD.Total) AS Total
					FROM     TicketD
							 LEFT JOIN Loc          ON TicketD.Loc   = Loc.Loc
							 LEFT JOIN Job          ON TicketD.Job   = Job.ID 
							 LEFT JOIN OwnerWithRol ON Loc.Owner     = OwnerWithRol.ID
							 LEFT JOIN JobType      ON Job.Type      = JobType.ID
							 LEFT JOIN Elev         ON TicketD.Elev  = Elev.ID
							 LEFT JOIN Emp          ON TicketD.fWork = Emp.fWork
					WHERE    Job.Owner = ?
							 AND NOT (TicketD.DescRes LIKE '%Voided%')
					GROUP BY Emp.ID, 
							 Emp.fFirst, 
							 Emp.Last
				)
			) AS Workers
			GROUP BY Workers.ID,
					 Workers.First_Name,
					 Workers.Last_Name
        ;",array($_GET['ID'], $_GET['ID'], $_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
	}
}?>