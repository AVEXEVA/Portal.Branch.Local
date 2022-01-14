<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
    $result = \singleton\database::getInstance( )->query(
        null,
        " SELECT  Emp.fFirst  AS First_Name,
                  Emp.Last    AS Last_Name,
                  Emp.fFirst + ' ' + Emp.Last AS Name,
                  Emp.Title AS Title,
                  Emp.Field   AS Field
          FROM  Emp
          WHERE   Emp.ID = ?;",
        array(
            $_SESSION[ 'Connection' ][ 'User' ]
        )
    );
    $User   = sqlsrv_fetch_array( $result );
    //Privileges
    $Access = 0;
    $Hex = 0;
    $result = \singleton\database::getInstance( )->query(
        'Portal',
        "   SELECT  [Privilege].[Access],
                    [Privilege].[Owner], 
                    [Privilege].[Group], 
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
          FROM      dbo.[Privilege]
          WHERE     Privilege.[User] = ?;",
        array(
            $_SESSION[ 'Connection' ][ 'User' ],
        )
    );
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){
        
        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
            dechex( $Privilege[ 'Owner' ] ),
            dechex( $Privilege[ 'Group' ] ),
            dechex( $Privilege[ 'Department' ] ),
            dechex( $Privilege[ 'Database' ] ),
            dechex( $Privilege[ 'Server' ] ),
            dechex( $Privilege[ 'Other' ] ), 
            dechex( $Privilege[ 'Token' ] ),
            dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if(     !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Customer' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Customer' ] )
    ){ ?><?php print json_encode( array( 'data' => array( ) ) ); ?><?php }
    else {
		$output = array(
	        'sEcho'         		=> isset( $_GET[ 'draw' ] ) ? intval( $_GET[ 'draw' ] ) : 1,
	        'iTotalRecords'     	=>  0,
	        'iTotalDisplayRecords'  =>  0,
	        'aaData'        		=>  array(),
	        'options' 				=> array( )
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
	    if( isset( $_GET[ 'Employee_ID' ] ) && !in_array( $_GET[ 'Employee_ID' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Employee_ID'];
	      $conditions[] = "Employee.ID = ?";
	    }
	    if( isset( $_GET[ 'Employee_Name' ] ) && !in_array( $_GET[ 'Employee_Name' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Employee_Name'];
	      $conditions[] = "Employee.fFirst + ' ' + Employee.Last LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Division_ID' ] ) && !in_array( $_GET[ 'Division_ID' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Division_ID'];
	      $conditions[] = "Division.ID LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Division_Name' ] ) && !in_array( $_GET[ 'Division_Name' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Division_Name'];
	      $conditions[] = "Division.Name LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Customer_ID' ] ) && !in_array( $_GET[ 'Customer_ID' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Customer_ID'];
	      $conditions[] = "Customer.ID LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Customer_Name' ] ) && !in_array( $_GET[ 'Customer_Name' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Customer_Name'];
	      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Location_ID' ] ) && !in_array( $_GET[ 'Location_ID' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Location_ID'];
	      $conditions[] = "Location.Loc LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Location_Name' ] ) && !in_array( $_GET[ 'Location_Name' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Location_Name'];
	      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Unit_ID' ] ) && !in_array( $_GET[ 'Unit_ID' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Unit_ID'];
	      $conditions[] = "Unit.ID = ?";
	    }
	    if( isset( $_GET[ 'Unit_Name' ] ) && !in_array( $_GET[ 'Unit_Name' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Unit_Name'];
	      $conditions[] = "Unit.State LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Job_ID' ] ) && !in_array( $_GET[ 'Job_ID' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Job_ID'];
	      $conditions[] = "Job.ID = ?"; 
	    }
	    if( isset( $_GET[ 'Job_Name' ] ) && !in_array( $_GET[ 'Job_Name' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Job_Name'];
	      $conditions[] = "Job.fDesc LIKE '%' + ? + '%'"; 
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
	      $conditions[] = "Ticket.Status = ?";
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
	    if( isset( $_GET[ 'RowGroup1' ] ) && !in_array(  $_GET[ 'RowGroup1' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET[ 'RowGroup1' ];
	      $conditions[] = "Ticket.RowGroup1 LIKE '%' + ? + '%'";
	    }
		/*Search Filters*/
		//if( isset( $_GET[ 'search' ] ) ){ }
		

		/*Concatenate Filters*/
		$conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    	$search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

		/*ROW NUMBER*/
		$parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] - 25 : 0;
		$parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 9999;

		/*Order && Direction*/
		//update columns from bin/js/tickets/table.js
		$Columns = array(
			0 =>  "CASE 	WHEN Ticket.Level = 1  THEN 'Service Call'  WHEN Ticket.Level = 10 THEN 'Maintenance' WHEN Ticket.Level = 3  THEN 'Modernization' ELSE 'Other' END",
			1 =>  "CASE 	WHEN Ticket.Level = 1  THEN Employee.fFirst + ' ' + Employee.Last  WHEN Ticket.Level = 10 THEN Route.Name WHEN Ticket.Level = 3  THEN Job.fDesc ELSE 'Other' END",
			2 =>  'Ticket.ID',
			3 =>  "Employee.fFirst + ' ' + Employee.Last",
			4 =>  'Customer.Name',
			5 =>  'Location.Tag',
			6 =>  "Unit.State + ' - ' + Unit.Unit",
			7 =>  'Job.ID',
			8 =>  "JobType.Type + ' ' + Ticket.Level",
			9 =>  'Ticket.Date',
			10 =>  'Ticket.Time_Route',
			11 =>  'Ticket.Time_Site',
			12 =>  'Ticket.Time_Completed'
	    );
	    $Order = $Columns[ 0 ];
	    $Direction = 'ASC';

	    $Order2 = $Columns[ 1 ];
	    $Direction2 = 'ASC';

		/*Perform Query*/
		$Query = "
			SELECT 	*
			FROM 	(
				SELECT 	Ticket.RowGroup2 AS RowGroup2,
						Ticket.RowLink,
						Count( Ticket.ID ) AS Count
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
										TicketO.fDesc 		AS Description,
				           	     		0                   AS Payroll,
				           	     		TicketO.TimeRoute 	AS Time_Route,
				           	     		TicketO.TimeSite    AS Time_Site,
				           	     		TicketO.TimeComp    AS Time_Completed,
				           	     		'' 					AS Resolution,
				           	     		CASE 	WHEN TicketO.Level = 1   THEN 'Service Call'  
				           	     				WHEN TicketO.Level = 10  THEN 'Maintenance' 
				           	     				WHEN TicketO.Level = 3   THEN 'Modernization' 
				           	     				ELSE 'Other' 
				           	     		END AS RowGroup1,
				           	     		CASE 	WHEN TicketO.Level = 1  THEN Employee.fFirst + ' ' + Employee.Last 
												WHEN TicketO.Level = 10 THEN 'Route #' + Route.Name
												WHEN TicketO.Level = 3  THEN Job.fDesc
												ELSE 'Other'
										END AS RowGroup2,
										CASE 	WHEN TicketO.Level = 1  THEN 'tickets.php?Open=1&Employee_ID=' + cast( Employee.ID AS VARCHAR( 255 ) ) + '&Employee_Name=' + Employee.Name
												WHEN TicketO.Level = 10 THEN 'tickets.php?Open=1&Route_ID=' + cast( Route.ID AS varchar( 255 ) ) + '&Route_Name=' + Route.Name
												WHEN TicketO.Level = 3  THEN 'tickets.php?Open=1&Job_ID' + cast( Job.ID AS varchar( 255 ) ) + '&Job_Name=' + Job.fDesc
												ELSE 'Other'
										END AS RowLink
						 		FROM   	TicketO
						        		LEFT JOIN TickOStatus 	ON TicketO.Assigned = TickOStatus.Ref
	                					LEFT JOIN TicketDPDA 	ON TicketDPDA.ID 	= TicketO.ID
	                					LEFT JOIN Emp 		   AS Employee ON TicketO.fWork    = Employee.fWork
										LEFT JOIN Loc          AS Location ON TicketO.LID = Location.Loc
										LEFT JOIN Zone 		   AS Division ON Location.Zone   = Division.ID
										LEFT JOIN Job          AS Job      ON TicketO.Job      = Job.ID
										LEFT JOIN JobType 	   AS JobType  ON Job.Type 		  = JobType.ID
										LEFT JOIN Route 	   AS Route    ON Location.Route  = Route.ID
										LEFT JOIN (
				                            SELECT  Owner.ID,
				                                    Rol.Name 
				                            FROM    Owner 
				                                    LEFT JOIN Rol ON Rol.ID = Owner.Rol
				                        ) AS Customer ON Job.Owner = Customer.ID
										LEFT JOIN Elev AS Unit ON TicketO.LElev = Unit.ID

							)
						) AS Ticket
				WHERE 	({$conditions}) AND ({$search})
				GROUP BY 	Ticket.RowGroup2,
							Ticket.RowLink
			) AS Tbl;";

		$rResult = \singleton\database::getInstance( )->query(
			null,
			$Query,
			$parameters
		) or die(print_r(sqlsrv_errors()));

		while ( $Ticket = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
	      $output[ 'aaData' ][]   		= $Ticket;
	    }
		$sQueryRow = "
	        SELECT 		Ticket.RowGroup1,
				        Count( Ticket.ID ) AS Count
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
				    	       	     	'' 					AS Resolution,
				    	       	     	CASE 	WHEN TicketO.Level = 1   THEN 'Service Call'  
				           	     				WHEN TicketO.Level = 10  THEN 'Maintenance' 
				           	     				WHEN TicketO.Level = 3   THEN 'Modernization' 
				           	     				ELSE 'Other' 
				           	     		END AS RowGroup1,
				           	     		CASE 	WHEN TicketO.Level = 1  THEN Employee.fFirst + ' ' + Employee.Last 
												WHEN TicketO.Level = 10 THEN 'Route #' + Route.Name
												WHEN TicketO.Level = 3  THEN Job.fDesc
												ELSE 'Other'
										END AS RowGroup2
						 		FROM   	TicketO
						        		LEFT JOIN TickOStatus 	ON TicketO.Assigned = TickOStatus.Ref
	                					LEFT JOIN TicketDPDA 	ON TicketDPDA.ID 	= TicketO.ID
	                					LEFT JOIN Emp 		   AS Employee ON TicketO.fWork    = Employee.fWork
										LEFT JOIN Loc          AS Location ON TicketO.LID = Location.Loc
										LEFT JOIN Zone 		   AS Division ON Location.Zone   = Division.ID
										LEFT JOIN Job          AS Job      ON TicketO.Job      = Job.ID
										LEFT JOIN JobType 	   AS JobType  ON Job.Type 		  = JobType.ID
										LEFT JOIN Route 	   AS Route    ON Location.Route  = Route.ID
										LEFT JOIN (
				                            SELECT  Owner.ID,
				                                    Rol.Name 
				                            FROM    Owner 
				                                    LEFT JOIN Rol ON Rol.ID = Owner.Rol
				                        ) AS Customer ON Job.Owner = Customer.ID
										LEFT JOIN Elev AS Unit ON TicketO.LElev = Unit.ID
							)
						) AS Ticket
						
			WHERE 		({$conditions}) AND ({$search})
			GROUP BY 	Ticket.RowGroup1;";
		//echo $sQueryRow;
	    $stmt = \singleton\database::getInstance( )->query( 
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
	                	)
	            	) AS Ticket;";
	    $rResultTotal = \singleton\database::getInstance( )->query(
	    	null,  
	    	$sQuery, 
	    	array( )
	    ) or die(print_r(sqlsrv_errors()));
	    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
	    $iTotal = $aResultTotal[0];

	    $Types = array( );
	    $result = \singleton\database::getInstance( )->query(
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
