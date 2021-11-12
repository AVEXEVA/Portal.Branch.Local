<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
		'read_and_close' => true
	] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $Connection = $database->query(
    	null,
    	"	SELECT 	*
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
		"	SELECT 	Emp.*,
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
		/*Parse GET*/
		$_GET[ 'Start_Date' ]	 	= isset( $_GET[ 'Start_Date' ] )  		&& !in_array( $_GET[ 'Start_Date' ], array( '', ' ', null ) ) 		? DateTime::createFromFormat( 'm/d/Y', $_GET['Start_Date'] )->format( 'Y-m-d 00:00:00.000' ) 		: null;
		$_GET[ 'End_Date' ] 		= isset( $_GET[ 'End_Date' ] )   		&& !in_array( $_GET[ 'End_Date' ], array( '', ' ', null ) ) 		? DateTime::createFromFormat( 'm/d/Y', $_GET['End_Date'] )->format( 'Y-m-d 00:00:00.000' ) 			: null;
		$_GET[ 'Time_Route_Start' ] = isset( $_GET[ 'Time_Route_Start' ] )  && !in_array( $_GET[ 'Time_Route_Start' ], array( '', ' ', null ) ) ? DateTime::createFromFormat( 'h:i A', $_GET['Time_Route_Start'] )->format( '1899-12-30 H:i:s' ) 		: null;
		$_GET[ 'Time_Route_End' ] 	= isset( $_GET[ 'Time_Route_End' ] )    && !in_array( $_GET[ 'Time_Route_End' ], array( '', ' ', null ) ) 	? DateTime::createFromFormat( 'h:i A', $_GET['Time_Route_End'] )->format( '1899-12-30 H:i:s' ) 		: null;
		$_GET[ 'Time_Site_Start' ] = isset( $_GET[ 'Time_Site_Start' ] )  && !in_array( $_GET[ 'Time_Site_Start' ], array( '', ' ', null ) ) ? DateTime::createFromFormat( 'h:i A', $_GET['Time_Site_Start'] )->format( '1899-12-30 H:i:s' ) 		: null;
		$_GET[ 'Time_Site_End' ] 	= isset( $_GET[ 'Time_Site_End' ] )    && !in_array( $_GET[ 'Time_Site_End' ], array( '', ' ', null ) ) 	? DateTime::createFromFormat( 'h:i A', $_GET['Time_Site_End'] )->format( '1899-12-30 H:i:s' ) 		: null;
		$_GET[ 'Time_Completed_Start' ] = isset( $_GET[ 'Time_Completed_Start' ] )  && !in_array( $_GET[ 'Time_Completed_Start' ], array( '', ' ', null ) ) ? DateTime::createFromFormat( 'h:i A', $_GET['Time_Completed_Start'] )->format( '1899-12-30 H:i:s' ) 		: null;
		$_GET[ 'Time_Completed_End' ] 	= isset( $_GET[ 'Time_Completed_End' ] )    && !in_array( $_GET[ 'Time_Completed_End' ], array( '', ' ', null ) ) 	? DateTime::createFromFormat( 'h:i A', $_GET['Time_Completed_End'] )->format( '1899-12-30 H:i:s' ) 		: null;

		$conditions = array( );
		$search 	= array( );


	    if( isset( $_GET[ 'ID' ] ) && !in_array(  $_GET[ 'ID' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['ID'];
	      $conditions[] = "CAST( Ticket.ID AS VARCHAR( 32 ) ) LIKE '%' + ? + '%'";
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
	      //$parameters[] = $_GET['LSD'];
	      switch( $_GET[ 'LSD'] ){
	      	case 0:	$conditions[ ] = "Ticket.Resolution NOT LIKE '%LSD%'";break;
	      	case 1:	$conditions[ ] = "Ticket.Resolution LIKE '%LSD%'";break;
	      	default : break;
	      }
	    }

	    if( isset( $_GET[ 'search' ] ) && !in_array(  $_GET[ 'search' ], array( '', ' ', null ) ) ){

			$search[] = "CAST( Ticket.ID AS VARCHAR( 32 ) ) LIKE '%' + ? + '%'";
			$parameters[] = $_GET[ 'search' ];

			$search[] = " Customer.Name LIKE '%' + ? + '%'";
			$parameters[] = $_GET[ 'search' ];

			$search[] = " Location.Tag LIKE '%' + ? + '%'";
			$parameters[] = $_GET[ 'search' ];
			

			$search[] = " Unit.State + ' - ' + Unit.Unit LIKE '%' + ? + '%'";
			$parameters[] = $_GET[ 'search' ];

			$search[] = " CAST( Job.ID AS VARCHAR( 32 ) ) + ' - ' + Job.fDesc LIKE '%' + ? + '%'";
			$parameters[] = $_GET[ 'search' ];

			$search[] = " JobType.Type LIKE '%' + ? + '%'";
			$parameters[] = $_GET[ 'search' ];

			$search[] = " Employee.fFirst + ' ' + Employee.Last LIKE '%' + ? + '%'";
			$parameters[] = $_GET[ 'search' ];

		}

		/*Concatenate Filters*/
		$conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    	$search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

		/*Order && Direction*/
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

	    $parameters[ ] = $_GET[ 'search' ];

		/*Perform Query*/
		$Query = " SELECT 		Top 10
  						tbl.FieldName,
  						tbl.FieldValue
  			FROM 		(

							SELECT
							    attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
							    attr.insRow.value('.', 'nvarchar(max)') as FieldValue
							FROM ( Select
							          convert(xml, (select i.* for xml raw)) as insRowCol
							       FROM (

								   (
											SELECT 	Top 100
													Ticket.ID 						AS ID,
													Customer.ID  					AS Customer_ID,
													Customer.Name 					AS Customer_Name,
													Location.Tag 					AS Location_Tag,
													Job.fDesc 						AS Job_Name,
													JobType.Type 					AS Job_Type,
													Ticket.Level 					AS Level,
													Unit.State 						AS Unit_City_ID,
													Unit.Unit 						AS Unit_Building_ID,
													Ticket.Status 	 				AS Status,
													Employee.fFirst + ' ' + Employee.Last AS Person
											FROM 	(
														(
															SELECT 	TicketO.ID       	AS ID,
																	TicketO.EDate       AS Date,
																	TicketO.fWork 		AS Field,
																	TicketO.LID         AS Location,
																	TicketO.Job         AS Job,
																	TicketO.LElev       AS Unit,
																	TicketO.Level       AS Level,
																	TicketO.Assigned    AS Status
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
																	TicketD.Level 		AS Level,
																	4					AS Status
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
								   ) ) as i
							     ) as insRowTbl
							CROSS APPLY insRowTbl.insRowCol.nodes('/row/@*') as attr(insRow)
						) AS tbl
			WHERE 		tbl.FieldValue LIKE '%' + ? + '%'
			GROUP BY 	tbl.FieldName, tbl.FieldValue;";
		$rResult = $database->query(
			null,
			$Query,
			$parameters
		) or die(print_r(sqlsrv_errors()));

		$output = array( );
	    while ( $Row = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
	      $output[]   		= $Row;
	    }
	    echo json_encode( $output );
  	}
}?>
