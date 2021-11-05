<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [
		'read_and_close' => true
	] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $Connection = $database->query(
    	null,
    	"	SELECT 	Top 1 
    				*
			FROM   	Connection
			WHERE  		Connection.Connector 	= ?
				   	AND Connection.Hash 		= ?;", 
		array(
			$_SESSION['User'],
			$_SESSION['Hash']
		)
	);
    $Connection = sqlsrv_fetch_array($Connection);
	$User    = $database->query(
		null,
		"	SELECT 	Top 1 
					Emp.*,
				   	Emp.fFirst AS First_Name,
			   		Emp.Last   AS Last_Name
			FROM   	Emp
			WHERE  	Emp.ID = ?;", 
		array(
			$_SESSION['User']
		)
	);
	$User = sqlsrv_fetch_array( $User );
	$r = $database->query(
		null,
		"	SELECT 	Privilege.Access_Table,
				   	Privilege.User_Privilege,
			   		Privilege.Group_Privilege,
			   		Privilege.Other_Privilege
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;",
		array(
			$_SESSION['User']
		)
	);
	$Privileges = array();
	while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
	$Privileged = False;
	if( isset($Privileges['Ticket'])
        && (
				$Privileges['Ticket']['User_Privilege'] >= 4
			||	$Privileges['Ticket']['Group_Privilege'] >= 4
			||	$Privileges['Ticket']['Other_Privilege'] >= 4)){
            	$Privileged = True;
    }
    if( !isset( $Connection[ 'ID' ] ) || !$Privileged ){ print json_encode( array( 'data' => array( ) ) );}
	else {
		$output = array(
	        'sEcho'         => isset( $_GET[ 'draw' ] ) ? intval( $_GET[ 'draw' ] ) : 1,
	        'iTotalRecords'     =>  0,
	        'iTotalDisplayRecords'  =>  0,
	        'aaData'        =>  array(),
	        'options' => array( )
	    );

	    $Statuses = array(
			0 => 'Unassigned',
			1 => 'Assigned',
			2 => 'En Route',
			3 => 'On Site',
			4 => 'Completed',
			5 => 'On Hold',
			6 => 'Reviewing'
		);
	    $Levels = array(
			0  => '',
			1  => 'Service Call',
			2  => 'Trucking',
			3  => 'Modernization',
			4  => 'Violations',
			5  => 'Level 5',
			6  => 'Repair',
			7  => 'Annual',
			8  => 'Escalator',
			9  => 'Email',
			10 => 'Maintenance',
			11 => 'Survey',
			12 => 'Engineering',
			13 => 'Support',
			14 => "M/R"
		);

		/*Parse GET*/
		$_GET[ 'Start_Date' ]	 	= isset( $_GET[ 'Start_Date' ] )  		&& !in_array( $_GET[ 'Start_Date' ], array( '', ' ', null ) ) 		? DateTime::createFromFormat( 'm/d/Y', $_GET['Start_Date'] )->format( 'Y-m-d 00:00:00.000' ) 		: null;
		$_GET[ 'End_Date' ] 		= isset( $_GET[ 'End_Date' ] )   		&& !in_array( $_GET[ 'End_Date' ], array( '', ' ', null ) ) 		? DateTime::createFromFormat( 'm/d/Y', $_GET['End_Date'] )->format( 'Y-m-d 00:00:00.000' ) 			: null;
		$_GET[ 'Time_Route_Start' ] = isset( $_GET[ 'Time_Route_Start' ] )  && !in_array( $_GET[ 'Time_Route_Start' ], array( '', ' ', null ) ) ? DateTime::createFromFormat( 'h:i A', $_GET['Time_Route_Start'] )->format( '1899-12-30 H:i:s' ) 		: null;
		$_GET[ 'Time_Route_End' ] 	= isset( $_GET[ 'Time_Route_End' ] )    && !in_array( $_GET[ 'Time_Route_End' ], array( '', ' ', null ) ) 	? DateTime::createFromFormat( 'h:i A', $_GET['Time_Route_End'] )->format( '1899-12-30 H:i:s' ) 		: null;
		$_GET[ 'Time_Site_Start' ] = isset( $_GET[ 'Time_Site_Start' ] )  && !in_array( $_GET[ 'Time_Site_Start' ], array( '', ' ', null ) ) ? DateTime::createFromFormat( 'h:i A', $_GET['Time_Site_Start'] )->format( '1899-12-30 H:i:s' ) 		: null;
		$_GET[ 'Time_Site_End' ] 	= isset( $_GET[ 'Time_Site_End' ] )    && !in_array( $_GET[ 'Time_Site_End' ], array( '', ' ', null ) ) 	? DateTime::createFromFormat( 'h:i A', $_GET['Time_Site_End'] )->format( '1899-12-30 H:i:s' ) 		: null;
		$_GET[ 'Time_Completed_Start' ] = isset( $_GET[ 'Time_Completed_Start' ] )  && !in_array( $_GET[ 'Time_Completed_Start' ], array( '', ' ', null ) ) ? DateTime::createFromFormat( 'h:i A', $_GET['Time_Completed_Start'] )->format( '1899-12-30 H:i:s' ) 		: null;
		$_GET[ 'Time_Completed_End' ] 	= isset( $_GET[ 'Time_Completed_End' ] )    && !in_array( $_GET[ 'Time_Completed_End' ], array( '', ' ', null ) ) 	? DateTime::createFromFormat( 'h:i A', $_GET['Time_Completed_End'] )->format( '1899-12-30 H:i:s' ) 		: null;
		//$_GET[ 'Time_Site' ] 		= isset( $_GET[ 'Time_Site' ] )   		&& !in_array( $_GET[ 'Time_Site' ], array( '', ' ', null ) ) 		? DateTime::createFromFormat( 'h:ia', $_GET['Time_Site'] )->format( 'Y-m-d 00:00:00.000' ) 			: null;
		//$_GET[ 'Time_Completed' ] 	= isset( $_GET[ 'Time_Completed' ] )    && !in_array( $_GET[ 'Time_Completed' ], array( '', ' ', null ) ) 	? DateTime::createFromFormat( 'h:ia', $_GET['Time_Completed'] )->format( 'Y-m-d 00:00:00.000' ) 	: null;
		//var_dump( $_GET[ 'Time_Route_Start' ] );
		$conditions = array( );
		$search 	= array( );
		/*Default Filters*/
		/*$conditions[] = "Employee.ID = ?";
		$parameters[] = $User[ 'ID' ];*/

		/*$conditions[] = "Ticket.Date >= ?";
		$conditions[] = "Ticket.Date <= ?";
		$parameters[] = $_GET[ 'Start_Date' ];
		$parameters[] = $_GET[ 'End_Date' ];*/


	    if( isset( $_GET[ 'ID' ] ) && !in_array(  $_GET[ 'ID' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['ID'];
	      $conditions[] = "Ticket.ID LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Person' ] ) && !in_array( $_GET[ 'Person' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Person'];
	      $conditions[] = "Employee.fFirst + ' ' + Employee.Last LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Customer'];
	      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Location'];
	      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Unit' ] ) && !in_array( $_GET[ 'Unit' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Unit'];
	      $conditions[] = "Unit.State + ' ' + Unit.Unit LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Job' ] ) && !in_array( $_GET[ 'Job' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Job'];
	      $conditions[] = "CAST(Job.ID as VARCHAR( 25 ) ) + ' ' + Job.fDesc LIKE '%' + ? + '%'"; 
	    }
	    if( isset( $_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Type'];
	      $conditions[] = "Job.Type = ?";
	    }
	    if( isset( $_GET[ 'Level' ] ) && !in_array( $_GET[ 'Level' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Level'];
	      $conditions[] = "Ticket.Level = ?";
	    }
	    if( isset( $_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Status'];
	      $conditions[] = "Ticket.Status LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Start_Date' ] ) && !in_array( $_GET[ 'Start_Date' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Start_Date'];
	      $conditions[] = "Ticket.Date >= ?";
	    }
	    if( isset( $_GET[ 'End_Date' ] ) && !in_array( $_GET[ 'End_Date' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['End_Date'];
	      $conditions[] = "Ticket.Date <= ?";
	    }
	    if( isset( $_GET[ 'Time_Route_Start' ] ) && !in_array( $_GET[ 'Time_Route_Start' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Time_Route_Start'];
	      $conditions[] = "Ticket.Time_Route >= ?";
	    }
	    if( isset( $_GET[ 'Time_Route_End' ] ) && !in_array( $_GET[ 'Time_Route_End' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Time_Route_End'];
	      $conditions[] = "Ticket.Time_Route <= ?";
	    }
	    if( isset( $_GET[ 'Time_Site_Start' ] ) && !in_array( $_GET[ 'Time_Site_Start' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Time_Site_Start'];
	      $conditions[] = "Ticket.Time_Site >= ?";
	    }
	    if( isset( $_GET[ 'Time_Site_End' ] ) && !in_array( $_GET[ 'Time_Site_End' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Time_Site_End'];
	      $conditions[] = "Ticket.Time_Site <= ?";
	    }
	    if( isset( $_GET[ 'Time_Completed_Start' ] ) && !in_array( $_GET[ 'Time_Completed_Start' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Time_Completed_Start'];
	      $conditions[] = "Ticket.Time_Completed >= ?";
	    }
	    if( isset( $_GET[ 'Time_Completed_End' ] ) && !in_array( $_GET[ 'Time_Completed_End' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Time_Completed_End'];
	      $conditions[] = "Ticket.Time_Completed <= ?";
	    }
	    if( isset( $_GET[ 'LSD' ] ) && !in_array( $_GET[ 'LSD' ], array( '', ' ', null ) ) ){
	      switch( $_GET[ 'LSD'] ){
	      	case 0:	$conditions[ ] = "Ticket.Resolution NOT LIKE '%LSD%'";break;
	      	case 1:	$conditions[ ] = "Ticket.Resolution LIKE '%LSD%'";break;
	      	default : break;
	      }
	    }
	    
		/*Search Filters*/
		//if( isset( $_GET[ 'search' ] ) ){ }
		

		/*Concatenate Filters*/
		$conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    	$search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

		/*ROW NUMBER*/
		$parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
		$parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

		/*Order && Direction*/
		//update columns from bin/js/tickets/table.js
		$Columns = array(
			0 =>  'Ticket.ID',
			1 =>  'Location.Tag',
			2 =>  "Unit.State + ' - ' + Unit.Unit",
			3 =>  'Ticket.Status',
			4 =>  'Ticket.Date',
			5 =>  'Ticket.Hours',
			6 =>  'Ticket.Payroll'
	    );
	    $Order = isset( $Columns[ $_GET['order']['column'] ] )
	        ? $Columns[ $_GET['order']['column'] ]
	        : "Ticket.ID";
	    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
	      ? $_GET['order']['dir']
	      : 'ASC';

		/*Perform Query*/
		$Query = "
			SELECT 	*
			FROM 	(
				SELECT 	ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
						Ticket.ID 						AS ID,
						Ticket.Date 					AS Date,
						Customer.ID  					AS Customer_ID,
						Customer.Name 					AS Customer_Name,
						Location.Loc 					AS Location_ID,
						Location.Tag 					AS Location_Tag,
						Job.ID  						AS Job_ID,
						Job.fDesc 						AS Job_Name,
						JobType.Type 					AS Job_Type,
						Ticket.Level 					AS Level,
						Unit.ID 					 	AS Unit_ID,
						Unit.State 						AS Unit_City_ID,
						Unit.Unit 						AS Unit_Building_ID,
						Ticket.Hours 					AS Hours,
						Ticket.Status 	 				AS Status,
						Ticket.Payroll  				AS Payroll,
						Employee.fFirst + ' ' + Employee.Last AS Person,
						Employee.ID 					AS Employee_ID,
						CASE 	WHEN Ticket.Time_Route = '1899-12-30 00:00:00.000' THEN null 
								ELSE Ticket.Time_Route 
						END AS Time_Site,
						CASE 	WHEN Ticket.Time_Site = '1899-12-30 00:00:00.000' THEN null 
								ELSE Ticket.Time_Site 
						END AS Time_Route,
						CASE 	WHEN Ticket.Time_Completed = '1899-12-30 00:00:00.000' THEN null 
								ELSE Ticket.Time_Completed 
						END AS Time_Completed,
						CASE 	WHEN Ticket.Resolution LIKE '%LSD%' THEN 1
								ELSE 0 
						END AS LSD
				FROM 	(
							(
								SELECT 	TicketO.ID       	AS ID,
										TicketO.EDate       AS Date,
										TicketO.fWork 		AS Field,
										TicketO.LID         AS Location,
										TicketO.Job         AS Job,
										TicketO.LElev       AS Unit,
										TicketDPDA.Total    AS Hours,
										TicketO.Level       AS Level,
										TicketO.Assigned    AS Status,
				           	     		0                   AS Payroll,
				           	     		TicketO.TimeRoute 	AS Time_Route,
				           	     		TicketO.TimeSite    AS Time_Site,
				           	     		TicketO.TimeComp    AS Time_Completed,
				           	     		'' 					AS Resolution
						 		FROM   	TicketO
						        		LEFT JOIN TickOStatus 	ON TicketO.Assigned = TickOStatus.Ref
	                					LEFT JOIN TicketDPDA 	ON TicketDPDA.ID 	= TicketO.ID
							) UNION ALL (
								SELECT 	TicketD.ID      	AS ID,
										TicketD.EDate   	AS Date,
										TicketD.fWork 		AS Field,
										TicketD.Loc 		AS Location,
										TicketD.Job 		AS Job,
										TicketD.Elev		AS Unit,
										TicketD.Total 		AS Hours,
										TicketD.Level 		AS Level,
										4					AS Status,
										TicketD.ClearPR 	AS Payroll,
										TicketD.TimeRoute 	AS Time_Route,
				           	     		TicketD.TimeSite    AS Time_Site,
				           	     		TicketD.TimeComp    AS Time_Completed,
				           	     		TicketD.DescRes 	AS Resolution
							 	FROM   	TicketD
							)
						) AS Ticket
						LEFT JOIN Emp 		   AS Employee ON Ticket.Field    = Employee.fWork
						LEFT JOIN Loc          AS Location ON Ticket.Location = Location.Loc
						LEFT JOIN Job          AS Job      ON Ticket.Job      = Job.ID
						LEFT JOIN JobType 	   AS JobType  ON Job.Type 		  = JobType.ID
						LEFT JOIN (
                            SELECT  Owner.ID,
                                    Rol.Name 
                            FROM    Owner 
                                    LEFT JOIN Rol ON Rol.ID = Owner.Rol
                        ) AS Customer ON Job.Owner = Customer.ID
						LEFT JOIN Elev         AS Unit     ON Ticket.Unit     = Unit.ID
				WHERE 	({$conditions}) AND ({$search})
			) AS Tbl 
			WHERE 		Tbl.ROW_COUNT >= ?
					AND Tbl.ROW_COUNT <= ?;";
		$rResult = $database->query(
			null,
			$Query,
			$parameters
		) or die(print_r(sqlsrv_errors()));

		while ( $Ticket = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
	      $Ticket[ 'Status' ]    		= $Statuses[ $Ticket[ 'Status' ] ];
	      $Ticket[ 'Level' ]      		= $Levels[ $Ticket[ 'Level' ] ];
          $Ticket[ 'Date' ]       		= date( 'm/d/Y', strtotime( $Ticket[ 'Date' ] ) );
          $Ticket[ 'Time_Route' ] 		= date( 'h:i A', strtotime( $Ticket['Time_Route' ] ) );
          $Ticket[ 'Time_Site' ]  		= date( 'h:i A', strtotime( $Ticket['Time_Site' ] ) );
          $Ticket[ 'Time_Completed' ]  	= date( 'h:i A', strtotime( $Ticket['Time_Completed' ] ) );
	      $output[ 'aaData' ][]   		= $Ticket;
	    }

		$sQueryRow = "
	        SELECT 		Count( Ticket.ID ) AS Count
			FROM 		(
							(
								SELECT 	TicketO.ID       	AS ID,
										TicketO.EDate       AS Date,
										TicketO.fWork 		AS Field,
										TicketO.LID         AS Location,
										TicketO.Job         AS Job,
										TicketO.LElev       AS Unit,
										TicketDPDA.Total    AS Hours,
										TicketO.Level       AS Level,
										TicketO.Assigned    AS Status,
			        	   	     		0                   AS Payroll,
			        	   	     		TicketO.TimeRoute 	AS Time_Route,
			        	   	     		TicketO.TimeSite    AS Time_Site,
			        	   	     		TicketO.TimeComp    AS Time_Completed,
				    	       	     		'' 					AS Resolution
						 		FROM   	TicketO
						        		LEFT JOIN TickOStatus 	ON TicketO.Assigned = TickOStatus.Ref
                						LEFT JOIN TicketDPDA 	ON TicketDPDA.ID 	= TicketO.ID
							) UNION ALL (
								SELECT 	TicketD.ID      AS ID,
										TicketD.EDate   AS Date,
										TicketD.fWork 	AS Field,
										TicketD.Loc 	AS Location,
										TicketD.Job 	AS Job,
										TicketD.Elev	AS Unit,
										TicketD.Total 	AS Hours,
										TicketD.Level 	AS Level,
										4				AS Status,
										TicketD.ClearPR AS Payroll,
										TicketD.TimeRoute 	AS Time_Route,
			        	   	     		TicketD.TimeSite    AS Time_Site,
			        	   	     		TicketD.TimeComp    AS Time_Completed,
				    	       	     	TicketD.DescRes 	AS Resolution
							 	FROM   	TicketD
							)
						) AS Ticket
						LEFT JOIN Emp 		   AS Employee ON Ticket.Field    = Employee.fWork
						LEFT JOIN Loc          AS Location ON Ticket.Location = Location.Loc
						LEFT JOIN Job          AS Job      ON Ticket.Job      = Job.ID
						LEFT JOIN (
                    	    SELECT  Owner.ID,
                    	            Rol.Name 
                    	    FROM    Owner 
                    	            LEFT JOIN Rol ON Rol.ID = Owner.Rol
                    	) AS Customer ON Job.Owner = Customer.ID
						LEFT JOIN Elev         AS Unit     ON Ticket.Unit     = Unit.ID
			WHERE 		({$conditions}) AND ({$search});";

	    $stmt = $database->query( 
	    	null, 
	    	$sQueryRow, 
	    	$parameters
	    ) or die(print_r(sqlsrv_errors()));

	    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];
	    sqlsrv_cancel( $stmt );

	    $sQuery = " SELECT  COUNT(Ticket.ID)
	                FROM    (
	                	(
	                		SELECT 	TicketO.ID
	                		FROM 	TicketO
	                	) UNION ALL (
	                		SELECT 	TicketD.ID
	                		FROM 	TicketD
	                	)
	            	) AS Ticket;";
	    $rResultTotal = $database->query(
	    	null,  
	    	$sQuery, 
	    	array( $User[ 'ID' ] )
	    ) or die(print_r(sqlsrv_errors()));
	    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
	    $iTotal = $aResultTotal[0];

	    $Types = array( );
	    $result = $database->query(
	    	null,
	    	"	SELECT 	JobType.ID,
	    				JobType.Type
	    		FROM 	JobType;",
	    );
	    if( $result ) { while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){
	    	$Types[ $row[ 'ID' ] ] = $row[ 'Type' ];
	    }}
	    $output[ 'iTotalRecords' ] = $iTotal;
	    $output[ 'iTotalDisplayRecords' ] = $iFilteredTotal;
	    $output[ 'options' ][ 'Status' ] = $Statuses;
	    $output[ 'options' ][ 'Level' ] = $Levels ;
	    $output[ 'options' ][ 'Type' ] = $Types ;
	    echo json_encode( $output );
  	}
}?>
