<?php
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $Connection = sqlsrv_query(
    	$NEI,
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
	$User    = sqlsrv_query(
		$NEI,
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
	$r = sqlsrv_query(
		$NEI,
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
		$_GET[ 'Start_Date' ] 	= isset( $_GET[ 'Start_Date' ] ) 	? DateTime::createFromFormat( 'm/d/Y', $_GET['Start_Date'] )->format( 'Y-m-d 00:00:00.000' ) 	: date('Y-m-d 00:00:00.000', strtotime( '-7 days' ) );
		$_GET[ 'End_Date' ] 	= isset( $_GET[ 'End_Date' ] ) 		? DateTime::createFromFormat( 'm/d/Y', $_GET['End_Date'] )->format( 'Y-m-d 23:59:59.999' ) 		: date('Y-m-d 00:00:00.000', strtotime( 'now' ) );

		/*Default Filters*/
		$Conditions[] = "Employee.ID = ?";
		$Parameters[] = $User[ 'ID' ];

		$Conditions[] = "Ticket.Date >= ?";
		$Conditions[] = "Ticket.Date <= ?";
		$Parameters[] = $_GET[ 'Start_Date' ];
		$Parameters[] = $_GET[ 'End_Date' ];

		/*Search Filters*/
		if( isset( $_GET[ 'search' ] ) ){
			/*Ticket ID*/
			$Search[] = " Ticket.ID LIKE '%' + ? + '%'";
			$Parameters[] = $_GET[ 'search' ];
			/*Ticket ID*/
			$Search[] = " Location.Tag LIKE '%' + ? + '%'";
			$Parameters[] = $_GET[ 'search' ];

			$Search[] = " Unit.State + ' - ' + Unit.Unit LIKE '%' + ? + '%'";
			$Parameters[] = $_GET[ 'search' ];
		}
		

		/*Concatenate Filters*/
		$Conditions = implode( ' AND ', $Conditions );
		$Search     = implode( ' OR ', $Search );

		/*ROW NUMBER*/
		$Parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
		$Parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] : 25;

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

		/*Perform Query*/
		$Query = "
			SELECT 	*
			FROM 	(
				SELECT 	ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
						Ticket.ID 						AS ID,
						Ticket.Date 					AS Date,
						Customer.Name 					AS Customer,
						Location.Tag 					AS Location,
						Job.ID  						AS Job,
						Unit.State + ' - ' + Unit.Unit 	AS Unit,
						Ticket.Hours 					AS Hours,
						Ticket.Level 					AS Level,
						Ticket.Status 	 				AS Status,
						Ticket.Payroll  				AS Payroll
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
				           	     		0                   AS Payroll
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
										TicketD.ClearPR AS Payroll
							 	FROM   	TicketD
							)
						) AS Ticket
						LEFT JOIN Emp 		   AS Employee ON Ticket.Field    = Employee.fWork
						LEFT JOIN Loc          AS Location ON Ticket.Location = Location.Loc
						LEFT JOIN Job          AS Job      ON Ticket.Job      = Job.ID
						LEFT JOIN OwnerWithRol AS Customer ON Job.Owner    	  = Customer.ID
						LEFT JOIN Elev         AS Unit     ON Ticket.Unit     = Unit.ID
				WHERE 	({$Conditions}) AND ({$Search})
			) AS Tbl 
			WHERE 		Tbl.ROW_COUNT >= ?
					AND Tbl.ROW_COUNT <= ?;";
		$rResult = sqlsrv_query(
			$NEI,
			$Query,
			$Parameters,
			array( 'Scrollable' => SQLSRV_CURSOR_KEYSET )
		) or die(print_r(sqlsrv_errors()));

		$sQueryRow = "
	        SELECT 	Ticket.ID AS ID
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
			           	     		0                   AS Payroll
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
									TicketD.ClearPR AS Payroll
						 	FROM   	TicketD
						)
					) AS Ticket
					LEFT JOIN Emp 		   AS Employee ON Ticket.Field    = Employee.fWork
					LEFT JOIN Loc          AS Location ON Ticket.Location = Location.Loc
					LEFT JOIN Job          AS Job      ON Ticket.Job      = Job.ID
					LEFT JOIN OwnerWithRol AS Customer ON Job.Owner    	  = Customer.ID
					LEFT JOIN Elev         AS Unit     ON Ticket.Unit     = Unit.ID
			WHERE 	({$Conditions}) AND ({$Search})";

	    $Options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
	    $stmt = sqlsrv_query( 
	    	$NEI, 
	    	$sQueryRow, 
	    	$Parameters, 
	    	$Options 
	    ) or die(print_r(sqlsrv_errors()));

	    $iFilteredTotal = sqlsrv_num_rows( $stmt );

	    $sQuery = " SELECT  COUNT(Ticket.ID)
	                FROM    (
	                	(
	                		SELECT 	TicketO.ID,
	                				TicketO.fWork
	                		FROM 	TicketO
	                	) UNION ALL (
	                		SELECT 	TicketD.ID,
	                				TicketD.fWork 
	                		FROM 	TicketD
	                	)
	            	) AS Ticket
	            	WHERE 	Ticket.fWork = ?;";
	    $rResultTotal = sqlsrv_query(
	    	$NEI,  
	    	$sQuery, 
	    	array( $User[ 'ID' ] )
	    ) or die(print_r(sqlsrv_errors()));
	    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
	    $iTotal = $aResultTotal[0];

	    $output = array(
	        'sEcho'         =>  intval($_GET['sEcho']),
	        'iTotalRecords'     =>  $iTotal,
	        'iTotalDisplayRecords'  =>  $iFilteredTotal,
	        'aaData'        =>  array()
	    );
	 
	    $Statuses = array(
			0 => 'Open',
			1 => 'Assigned',
			2 => 'En Route',
			3 => 'On Site',
			4 => 'Completed',
			5 => 'On Hold',
			6 => 'Reviewing'
		);

	    while ( $Ticket = sqlsrv_fetch_array( $rResult ) ){
	      $Ticket[ 'Status' ]     = $Statuses[ $Ticket[ 'Status' ] ];
          $Ticket[ 'Date' ]       = date( 'm/d/Y h:i A', strtotime( $Ticket[ 'Date' ] ) );
	      $output[ 'aaData' ][]   = $Ticket;
	    }
	    echo json_encode( $output );
  	}
}?>
