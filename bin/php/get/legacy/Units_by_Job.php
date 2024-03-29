<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
	//Connection
    $result = $database->query(
    	null,
    	"	SELECT 	*
			FROM   	Connection
			WHERE  		Connection.Connector = ?
			   		AND Connection.Hash = ?;", 
		array(
			$_SESSION[ 'User' ],
			$_SESSION[ 'Hash' ]
		)
	);
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result    = $database->query(
		null,
		"	SELECT 	Emp.*,
			   		Emp.fFirst AS First_Name,
			   		Emp.Last   AS Last_Name
			FROM   	Emp
			WHERE  	Emp.ID = ?;", 
		array(
			$_SESSION[ 'User' ]
		)
	);
	$User = sqlsrv_fetch_array( $result );
	$result = $database->query(
		null,
		"	SELECT 	Privilege.Access,
			   		Privilege.Owner,
			   		Privilege.Group,
			   		Privilege.Other
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$Privileges = array();
	$Privileged = False;
	if( result ){ while( $row = sqlsrv_fetch_array($result )){ $Privileges[ $row[ 'Access' ] ] = $row; } }
	if( 	isset( $Privileges[ 'Job' ] )
		&& 	$Privileges[ 'Job' ][ 'Owner' ] >= 4
		&& 	$Privileges[ 'Job' ][ 'Group' ] >= 4
	  	&& 	$Privileges[ 'Job' ][ 'Other' ] >= 4
	  	&& 	is_numeric( $_GET[ 'ID' ] )
	  ){	$Privileged = True; 
	} elseif( 	
			isset( $Privileges[ 'Job' ] )
		&& 	$Privileges[ 'Job' ][ 'Owner' ] >= 4
		&& 	$Privileges[ 'Job' ][ 'Group' ] >= 4 
		&& 	is_numeric( $_GET[ 'ID' ] )
	){		$r = $database->query(
				null,
				"	SELECT Job.Loc AS Location_ID
					FROM   Job
					WHERE  Job.ID = ?;", 
				array(
					$_GET[ 'ID' ]
				)
			);
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
			$result = $database->query(
				null,
				"	SELECT 	Tickets.ID
					FROM 	(
								(
									SELECT 	TicketO.ID,
											TicketO.fWork,
											TicketO.LID AS Location
									FROM   	TicketO
								) UNION ALL (
									SELECT 	TicketD.ID,
											TicketD.fWork,
											TicketD.Loc AS Location
									FROM   	TicketD
								)
							) AS Tickets
							LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
					WHERE  		Tickets.Location = ?
							AND Emp.ID 			 = ?;", 
				array(
					$Location_ID, 
					$_SESSION[ 'User' ]
				)
			);
			$Privileged = is_array( sqlsrv_fetch_array( $result ) );
	} elseif(	
			isset( $Privileges[ 'Job' ] )
		&& 	$Privileges[ 'Job' ][ 'Owner' ] >= 4
		&& 	is_numeric( $_GET[ 'ID' ] )
	){		$result = $database->query(
				null,
				"	SELECT 	Tickets.ID
					FROM  	(
								(
									SELECT 	TicketO.ID,
											TicketO.Job,
											TicketO.fWork
									FROM   	TicketO
								) UNION ALL (
									SELECT 	TicketD.ID,
											TicketD.Job,
											TicketD.fWork
									FROM   	TicketD
								)
							) AS Tickets
							LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
					WHERE 		Tickets.Job = ?
							AND Emp.ID      = ?;",
				array(
					$_GET['ID'], 
					$_SESSION[ 'User' ]
				)
			);
			$Privileged = is_array( sqlsrv_fetch_array( $result ) );
	}
    if(		!isset($Connection['ID'])  
    	|| 	!is_numeric($_GET['ID']) 
    	|| 	!$Privileged){
    		require('401.html');
   	} else {
    	$database->query(
    		null,
    		"	INSERT INTO Activity([User], [Date], [Page])
    			VALUES(?,?,?);",
    		array(
    			$_SESSION[ 'User' ],
    			date( 'Y-m-d H:i:s' ), 
    			'get/units_by_job.php?ID=' . $_GET['ID']
    		)
    	);
		$result = $database->query(
			null, 
			"	SELECT 	Units.Unit_ID AS ID,
				   		Elev.State    AS State,
				   		Elev.Unit     AS Unit,
				   		Elev.Type     AS Type,
				   		Elev.fDesc    AS fDesc,
					   	Elev.Status   AS Status,
					   	Loc.Tag       AS Location
				FROM 	(
							SELECT 		Job_Units.Unit AS ID
							FROM 		(
											(
												SELECT 		TicketO.LElev AS Unit,
															TicketO.Job
												FROM   		TicketO
												GROUP BY 	TicketO.LElev, 
															TicketO.Job
											) UNION ALL (
												SELECT 		TicketD.LElev AS Unit,
															TicketD.Job
												FROM   		TicketD
												GROUP BY 	TicketD.LElev, 
															TicketD.Job
											)
										) AS Job_Units 
							WHERE 		Job_Units.Unit IS NOT NULL
										AND Job_Units.Job = ?
							GROUP BY 	Job_Units.Unit
						) AS Units
						LEFT JOIN Elev ON Elev.ID  = Units.Unit_ID
						LEFT JOIN Loc  ON Elev.Loc = Loc.Loc;",
			array(
				$_GET['ID']
			)
		);
		$data = array();
		if( $result ){ while( $row = sqlsrv_fetch_array( $result )){ $data[ ] = $row; } }
		print json_encode(
			array(
				'data' => $data
			)
		);
    }
}?>