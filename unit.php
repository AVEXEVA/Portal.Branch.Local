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
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Unit' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Unit' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        $ID = isset( $_GET[ 'ID' ] )
            ? $_GET[ 'ID' ]
            : (
            isset( $_POST[ 'ID' ] )
                ? $_POST[ 'ID' ]
                : null
            );
        $City_ID = isset( $_GET[ 'City_ID' ] )
            ? $_GET[ 'City_ID' ]
            : (
            isset( $_POST[ 'City_ID' ] )
                ? $_POST[ 'City_ID' ]
                : null
            );
        $squery="SELECT  TOP 1
	                    Unit.ID,
	                    CASE 	WHEN Unit.State IS NULL AND Unit.Unit IS NULL THEN ''
	                    		WHEN Unit.State IS NULL THEN Unit.Unit
	                    		WHEN Unit.Unit  IS NULL THEN Unit.State
	                    		ELSE Unit.State + ' - ' + Unit.Unit
	                    END AS Name,
	                    Unit.Unit        		AS Building_ID,
	                    Unit.State              AS City_ID,
	                    Customer.ID             AS Customer_ID,
	                    Customer.Name           AS Customer_Name,
	                    Location.ID             AS Location_ID,
	                    Location.Tag  			AS Location_Name,
	                    Location.Latt 			AS Location_Latitude,
	                    Location.fLong 			AS Location_Longitude,
	                    Unit.fDesc              AS Description,
	                    Unit.fGroup             AS Bank,
	                    Unit.Remarks            AS Note,
	                    Unit.Type               AS Type,
	                    Unit.Cat                AS Category,
	                    Unit.Building           AS Environment,
	                    Unit.Manuf              AS Manufacturer,
	                    Unit.Install            AS Installation,
	                    Unit.InstallBy          AS Installer,
	                    Unit.Since              AS Created,
	                    Unit.Last               AS Maintained,
	                    Unit.Price              AS Price,
	                    Unit.Serial             AS Serial,
	                    Unit.Template           AS Template,
	                    Unit.Status             AS Status,
	                    Unit.TFMID              AS TFMID,
	                    Unit.TFMSource          AS TFMSource,
	                    CASE    WHEN Invoices.[Open] IS NULL THEN 0
                                ELSE Invoices.[Open] END AS Invoices_Open,
                        CASE    WHEN Invoices.[Closed] IS NULL THEN 0
                                ELSE Invoices.[Closed] END AS Invoices_Closed,
                       	Tickets.Unassigned AS Tickets_Open,
	                    Tickets.Assigned AS Tickets_Assigned,
	                    Tickets.En_Route AS Tickets_En_Route,
	                    Tickets.On_Site AS Tickets_On_Site,
	                    Tickets.Reviewing AS Tickets_Reviewing,
	                    Violations.Preliminary AS Violations_Preliminary_Report,
	                    Violations.Job_Created AS Violations_Job_Created
	            FROM    Elev AS Unit
	                    LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
	                    LEFT JOIN (
	                       	SELECT  Owner.ID,
	                               	Rol.Name
	                       	FROM    Owner
	                               LEFT JOIN Rol ON Rol.ID = Owner.Rol
	                   	) AS Customer ON Unit.Owner = Customer.ID
	                   	LEFT JOIN (
	                        SELECT  Unit.ID AS Unit,
	                                Unassigned.Count AS Unassigned,
	                                Assigned.Count AS Assigned,
	                                En_Route.Count AS En_Route,
	                                On_Site.Count AS On_Site,
	                                Reviewing.Count AS Reviewing
	                        FROM    Elev AS Unit
	                                LEFT JOIN (
	                                  SELECT    TicketO.LElev AS Unit,
	                                            Count( TicketO.ID ) AS Count
	                                  FROM      TicketO
	                                  WHERE     TicketO.Assigned = 0
	                                  GROUP BY  TicketO.LElev
	                                ) AS Unassigned ON Unassigned.Unit = Unit.ID
	                                LEFT JOIN (
	                                  SELECT    TicketO.LElev AS Unit,
	                                            Count( TicketO.ID ) AS Count
	                                  FROM      TicketO
	                                  WHERE     TicketO.Assigned = 1
	                                  GROUP BY  TicketO.LElev
	                                ) AS Assigned ON Assigned.Unit = Unit.ID
	                                LEFT JOIN (
	                                  SELECT    TicketO.LElev AS Unit,
	                                            Count( TicketO.ID ) AS Count
	                                  FROM      TicketO
	                                  WHERE     TicketO.Assigned = 2
	                                  GROUP BY  TicketO.LElev
	                                ) AS En_Route ON En_Route.Unit = Unit.ID
	                                LEFT JOIN (
	                                  SELECT    TicketO.LElev AS Unit,
	                                            Count( TicketO.ID ) AS Count
	                                  FROM      TicketO
	                                  WHERE     TicketO.Assigned = 3
	                                  GROUP BY  TicketO.LElev
	                                ) AS On_Site ON On_Site.Unit = Unit.ID
	                                LEFT JOIN (
	                                  SELECT    TicketO.LElev AS Unit,
	                                            Count( TicketO.ID ) AS Count
	                                  FROM      TicketO
	                                  WHERE     TicketO.Assigned = 6
	                                  GROUP BY  TicketO.LElev
	                                ) AS Reviewing ON Reviewing.Unit = Unit.ID
	                    ) AS Tickets ON Tickets.Unit = Unit.ID
	                    LEFT JOIN (
							SELECT  Unit.ID AS Unit,
									Preliminary.Count AS Preliminary,
									Job_Created.Count AS Job_Created
							FROM    Elev AS Unit
									LEFT JOIN (
										SELECT    	Violation.Elev AS Unit,
													Count( Violation.ID ) AS Count
										FROM      	Violation
										WHERE     	Violation.Status = 'Preliminary Report'
										GROUP BY  	Violation.Elev
									) AS Preliminary ON Preliminary.Unit = Unit.ID
									LEFT JOIN (
										SELECT    	Violation.Elev AS Unit,
													Count( Violation.ID ) AS Count
										FROM      	Violation
										WHERE     	Violation.Status = 'Job Created'
										GROUP BY  	Violation.Elev
									) AS Job_Created ON Job_Created.Unit = Unit.ID
						) AS Violations ON Violations.Unit = Unit.ID
						LEFT JOIN (
                            SELECT      Job.Elev                AS Unit,
                                        Sum( [Open].Count )     AS [Open],
                                        Sum( [Closed].Count )   AS Closed
                            FROM        Job AS Job
                                        LEFT JOIN (
                                          SELECT    Invoice.Job AS Job,
                                                    Count( Invoice.Ref ) AS Count
                                          FROM      Invoice
                                          WHERE     Invoice.Ref IN ( SELECT Ref FROM OpenAR )
                                          GROUP BY  Invoice.Job
                                        ) AS [Open] ON Job.ID = [Open].Job
                                        LEFT JOIN (
                                          SELECT    Invoice.Job AS Job,
                                                    Count( Invoice.Ref ) AS Count
                                          FROM      Invoice
                                          WHERE     Invoice.Ref NOT IN ( SELECT Ref FROM OpenAR )
                                          GROUP BY  Invoice.Job
                                        ) AS [Closed] ON Job.ID = [Closed].Job
                            GROUP BY    Job.Elev
                        ) AS Invoices ON Invoices.Unit = Unit.ID
	            WHERE      Unit.ID = ?
	                    OR Unit.State = ?;";
		$result = \singleton\database::getInstance( )->query(
		    null,
		    $squery,
            array(
                $ID,
                $City_ID
            )
		);
		$Unit = (
						empty( $ID )
		    		&&  !empty( $City_ID )
		    		&&  !$result
				) || (
						empty( $ID )
		    		&&  empty( $City_ID )
				)
					? array(
					    'ID' => null,
					    'Name' => null,
					    'Customer_ID' => null,
					    'Customer_Name' => null,
					    'Location_ID' => null,
					    'Location_Name' => null,
					    'Location_Latitude' => null,
						'Location_Longitude' => null,
					    'Building_ID' => null,
					    'City_ID' => null,
					    'Description' => '',
					    'Bank' => null,
					    'Note' => '',
					    'Type' => null,
					    'Category' => null,
					    'Environment' => '',
					    'Manufacturer' => '',
					    'Installation' => '',
					    'Installer'   =>  null,
					    'Created' => '',
					    'Maintained'   =>  '',
					    'Price' => null,
					    'Serial' => null,
					    'Template' => '',
					    'Status' => null,
					    'TFMID' => '',
					    'TFMSource' => '',
					    //Totals
					    'Tickets_Open' => null,
					    'Tickets_Assigned' => null,
					    'Tickets_En_Route' => null,
					    'Tickets_On_Site' => null,
					    'Tickets_Reviewing' => null,
					    'Violations_Preliminary_Report' => null,
					    'Violations_Job_Created' => '',
					    'Invoices_Open' => null,
					    'Invoices_Closed' => null
					)
					: sqlsrv_fetch_array($result);

		if( isset( $_POST ) && count( $_POST ) > 0 ){
			//Foreign Keys
  			$Unit[ 'Customer_ID' ] 		= isset( $_POST[ 'Customer_ID' ] ) 	? $_POST[ 'Customer_ID' ] 	: $Unit[ 'Customer_ID' ];
  			$Unit[ 'Customer_Name' ] 	= isset( $_POST[ 'Customer_Name' ] )? $_POST[ 'Customer_Name' ] : $Unit[ 'Customer_Name' ];
  			$Unit[ 'Location_ID' ] 		= isset( $_POST[ 'Location_ID' ] ) 	? $_POST[ 'Location_ID' ] 	: $Unit[ 'Location_ID' ];
  			$Unit[ 'Location_Name' ] 	= isset( $_POST[ 'Location' ] ) 		? $_POST[ 'Location' ] 	 	  : $Unit[ 'Location_Name' ];
			//Other
		  	$Unit[ 'Building_ID' ] 		= isset( $_POST[ 'Building_ID' ] ) 	? $_POST[ 'Building_ID' ] 	: $Unit[ 'Building_ID' ];
		  	$Unit[ 'City_ID' ] 		  	= isset( $_POST[ 'City_ID' ] )			? $_POST[ 'City_ID' ] 	 	  : $Unit[ 'City_ID' ];
  			$Unit[ 'Description' ] 		= isset( $_POST[ 'Description' ] ) 	? $_POST[ 'Description' ] 	: $Unit[ 'Description' ];
  			$Unit[ 'Bank' ] 		     	= isset( $_POST[ 'Bank' ] ) 	 		  ? $_POST[ 'Bank' ] 	 		    : $Unit[ 'Bank' ];
  			$Unit[ 'Note' ] 		     	=  isset( $_POST[ 'Note' ] ) 			  ? $_POST[ 'Note' ] 			    : $Unit[ 'Note' ];
  			$Unit[ 'Type' ] 			    = isset( $_POST[ 'Type' ] ) 	 		  ? $_POST[ 'Type' ] 	 		    : $Unit[ 'Type' ];
  			$Unit[ 'Category' ] 		  = isset( $_POST[ 'Category' ] ) 	 	? $_POST[ 'Category' ] 	 	  : $Unit[ 'Category' ];
		  	$Unit[ 'Environment' ] 		= isset( $_POST[ 'Environment' ] ) 	? $_POST[ 'Environment' ] 	: $Unit[ 'Environment' ];
		  	$Unit[ 'Manufacturer' ] 	= isset( $_POST[ 'Manufacturer' ] ) ? $_POST[ 'Manufacturer' ] 	: $Unit[ 'Manufacturer' ];
		  	$Unit[ 'Installation' ] 	= isset( $_POST[ 'Installation' ] ) ? $_POST[ 'Installation' ] 	: $Unit[ 'Installation' ];
		  	$Unit[ 'Installer' ] 		  = isset( $_POST[ 'Installer' ] ) 	 	? $_POST[ 'Installer' ] 	  : $Unit[ 'Installer' ];
		  	$Unit[ 'Maintained' ] 		= isset( $_POST[ 'Maintained' ] ) 	? $_POST[ 'Maintained' ]  	: $Unit[ 'Maintained' ];
		  	$Unit[ 'Price' ] 			    = isset( $_POST[ 'Price' ] ) 	 		  ? $_POST[ 'Price' ] 	 	    : $Unit[ 'Price' ];
		  	$Unit[ 'Serial' ] 			  = isset( $_POST[ 'Serial' ] ) 	 		? $_POST[ 'Serial' ] 	 	    : $Unit[ 'Serial' ];
		  	$Unit[ 'Template' ]  		  = isset( $_POST[ 'Template' ] ) 	 	? $_POST[ 'Template' ] 	 	  : $Unit[ 'Template' ];

		  	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){


		      	$result = \singleton\database::getInstance( )->query(
            		null,
		            "	DECLARE @MAXID INT;
		            	SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Elev ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Elev ) END ;
INSERT INTO Elev(ID,Owner,Loc,Unit,State,fDesc,fGroup,Remarks,Type,Cat,Building,Manuf,Install,
	InstallBy,Since,Last,Price,Serial,Template,Status,TFMID,TFMSource)
VALUES( @MAXID + 1 ,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?,
		            		?
		            	);
		            	SELECT @MAXID + 1;",
		            	array(
		                $Unit[ 'Customer_ID' ],
		                $Unit[ 'Location_ID' ],
		                $Unit[ 'Building_ID' ],
		                $Unit[ 'City_ID' ],
		                $Unit[ 'Description' ],
		                $Unit[ 'Bank' ],
		                $Unit[ 'Note' ],
		                $Unit[ 'Type' ],
		                $Unit[ 'Category' ],
		                !empty( $Unit[ 'Environment' ] ) ? $Unit[ 'Environment' ]: '',
		                !empty( $Unit[ 'Manufacturer' ] ) ? $Unit[ 'Manufacturer' ]: '',
		                !empty( $Unit[ 'Installation' ] ) ? $Unit[ 'Installation' ]: '',
		                !empty( $Unit[ 'Installer' ] ) ? $Unit[ 'Installer' ]: '',

		                 !empty( $Unit[ 'Created' ] ) ? $Unit[ 'Created' ]: date( 'Y-m-d h:i:s' ),
		               !empty( $Unit[ 'Maintained' ] ) ? $Unit[ 'Maintained' ]: date( 'Y-m-d h:i:s' ),

		                !empty( $Unit[ 'Price' ] ) ? $Unit[ 'Price' ]: 0,
		                !empty( $Unit[ 'Serial' ] ) ? $Unit[ 'Serial' ]: '',
		                !empty( $Unit[ 'Template' ] ) ? $Unit[ 'Template' ]: '',
		                !empty( $Unit[ 'Status' ] ) ? $Unit[ 'Status' ]: '',
		               !empty( $Unit[ 'TFMID' ] ) ? $Unit[ 'TFMID' ]: '',
		               !empty( $Unit[ 'TFMSource' ] ) ? $Unit[ 'TFMSource' ]: ''
		            )

			    )or die(print_r(sqlsrv_errors()));;
	        	sqlsrv_next_result( $result );
	        	$Unit[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
	        	header( 'Location: unit.php?ID=' . $Unit[ 'ID' ] );
	        	exit;
		    } else{
		    	$parameters=array(
            			$Unit[ 'Customer_ID' ],
            			$Unit[ 'Location_ID' ],
						$Unit[ 'Building_ID' ],
						$Unit[ 'City_ID' ],
						$Unit[ 'Description' ],
						$Unit[ 'Bank' ],
						$Unit[ 'Note' ],
						$Unit[ 'Type' ],
						$Unit[ 'Category' ],
						  !empty( $Unit[ 'Environment' ] ) ? $Unit[ 'Environment' ]: '',
		                !empty( $Unit[ 'Manufacturer' ] ) ? $Unit[ 'Manufacturer' ]: '',
		                !empty( $Unit[ 'Installation' ] ) ? $Unit[ 'Installation' ]: '',
		                !empty( $Unit[ 'Installer' ] ) ? $Unit[ 'Installer' ]: '',
		                !empty( $Unit[ 'Created' ] ) ? date('Y-m-d h:i:s',strtotime($Unit[ 'Created' ])): date( 'Y-m-d h:i:s' ),
		               !empty( $Unit[ 'Maintained' ] ) ? date('Y-m-d h:i:s',strtotime($Unit[ 'Maintained' ])): date( 'Y-m-d h:i:s' ),

						!empty( $Unit[ 'Price' ] ) ? $Unit[ 'Price' ]: 0,
						$Unit[ 'Serial' ],
						$Unit[ 'Template' ],
						$Unit[ 'Status' ],
						$Unit[ 'TFMID' ],
						$Unit[ 'TFMSource' ],
						$Unit[ 'ID' ]
            		);
		    	print_r($parameters);
	        	\singleton\database::getInstance( )->query(
            		null,
            		"	UPDATE 	Elev
    				    SET 	Elev.Owner = ?,
    				    		Elev.Loc = ?,
    				    		Elev.Unit = ?,
							    Elev.State = ?,
    						    Elev.fDesc  = ?,
    						    Elev.fGroup  = ?,
							    Elev.Remarks = ?,
							    Elev.Type = ?,
							    Elev.Cat = ?,
							    Elev.Building = ?,
							    Elev.Manuf = ?,
							    Elev.Install = ?,
							    Elev.InstallBy = ?,
							    Elev.Since = ?,
							    Elev.Last = ?,
							    Elev.Price = ?,
							    Elev.Serial = ?,
							    Elev.Template = ?,
							    Elev.Status = ?,
							    Elev.TFMID = ?,
							    Elev.TFMSource  = ?
    				    WHERE 	Elev.ID = ?;",
            		$parameters
	        	)or die(print_r(sqlsrv_errors()));;
	        	header( 'Location: unit.php?ID=' . $Unit[ 'ID' ] );
		    }
		}
?><!DOCTYPE html>
<html lang="en">
<head>
  	<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php  $_GET[ 'Entity_CSS' ] = 1;?>
    <?php	require( bin_meta . 'index.php');?>
    <?php	require( bin_css  . 'index.php');?>
    <?php  require( bin_js   . 'index.php');?>
</head>
<body>
	<div id="wrapper">
		<?php require( bin_php . 'element/navigation.php'); ?>
		<div id="page-wrapper" class='content'>
			<div class="card card-primary">
				<form action='unit.php?ID=<?php echo $Unit[ 'ID' ];?>' method='POST'>
					<input type='hidden' name='ID' value='<?php echo $Unit[ 'ID' ];?>' />
					<?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Unit', 'Units', $Unit[ 'ID' ] );?>
					<div class="card-body bg-dark text-white">
						<div class='row g-0' data-masonry='{"percentPosition": true }'>
							<?php \singleton\bootstrap::getInstance( )->card_map( 'unit_map', $Unit[ 'Name' ], $Unit[ 'Location_Latitude'], $Unit[ 'Location_Longitude' ] );?>
							<div class='card card-primary my-3 col-12 col-lg-3'>
								<?php \singleton\bootstrap::getInstance( )->card_header( 'Information' );?>
								<div class='card-body bg-dark text-white'>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'City_ID', $Unit[ 'City_ID' ] );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Building_ID', $Unit[ 'Building_ID' ] );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Customer', 'Customers', $Unit[ 'Customer_ID' ], $Unit[ 'Customer_Name' ] );?>
              						<?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Location', 'Locations', $Unit[ 'Location_ID' ], $Unit[ 'Location_Name' ] );?>
              						<?php \singleton\bootstrap::getInstance( )->card_row_form_select( 'Status', $Unit[ 'Status' ], array( 0 => 'Disabled', 1 => 'Enabled' ) );?>
              						<?php \singleton\bootstrap::getInstance( )->card_row_form_textarea( 'Description', $Unit[ 'Description' ] );?>
              						<?php \singleton\bootstrap::getInstance( )->card_row_form_textarea( 'Note', $Unit[ 'Note' ] );?>
              					</div>
              				</div>
              				<div class='card card-primary my-3 col-12 col-lg-3'>
              					<?php \singleton\bootstrap::getInstance( )->card_header( 'Engineering' );?>
              					<div class='card-body bg-dark text-white'>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Manufacturer', $Unit[ 'Manufacturer' ] );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Installation', $Unit[ 'Installation' ] );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Installer', $Unit[ 'Installer' ] );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Maintained', $Unit[ 'Maintained' ] );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Serial', $Unit[ 'Serial' ] );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_select( 'Status', $Unit[ 'Status' ], array( 'Elevator' => 'Elevator', 'Escalator' => 'Escalator', 'Moving Walk' => 'Moving Walk', 'Dumbwaiter' => 'Dumbwaiter' ) );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Created', $Unit[ 'Created' ] );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_input_currency( 'Price', $Unit[ 'Price' ] );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Bank', $Unit[ 'Bank' ] );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_select( 'Template', $Unit[ 'Template' ], array( ) );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_select( 'Category', $Unit[ 'Category' ], array( 'Consultant' => 'Consultant', 'Other' => 'Other', 'Public' => 'Public', 'N/A' => 'N/A', 'Service' => 'Service', 'Private' => 'Private' ) );?>
									<?php \singleton\bootstrap::getInstance( )->card_row_form_select( 'Environment', $Unit[ 'Environment' ], array( 'Government' => 'Government',  'Hospital' => 'Hospital',  'School' => 'School',  'Commercial' => 'Commercial',  'Residence' => 'Residence', 'Funeral Homes' => 'Funeral Homes', 'Utility-Powerplants' => 'Utility-Powerplants', 'Other' => 'Other', 'Catering Hall' => 'Catering Hall', 'Apartment / Residence' => 'Apartment / Residence', 'Office / Commercial' => 'Office / Commercial', 'Warehouse' => 'Warehouse', 'Store / Retail' => 'Store / Retail', 'Bank' => 'Bank', 'Parking Structure' => 'Parking Structure', 'Nursing Home' => 'Nursing Home', 'Airport' => 'Airport', 'Church' => 'Church', 'Hotel' => 'Hotel', 'Post Office' => 'Post Office', 'Mission' => 'Mission' ) );?>
              					</div>
							</div>
							<div class='card card-primary my-3 col-12 col-lg-3'>
		                        <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Unit', $Unit[ 'ID' ] );?>
		                        <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
		                            <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Unit=' . $Unit[ 'ID' ] );?>
		                            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Unit[ 'Tickets_Open' ], true, true, 'tickets.php?Unit=' . $Unit[ 'ID' ] . '&Status=0');?>
		                            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Unit[ 'Tickets_Assigned' ], true, true, 'tickets.php?Unit=' . $Unit[ 'ID' ] ) . '&Status=1';?>
		                            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En Route', $Unit[ 'Tickets_En_Route' ], true, true, 'tickets.php?Unit=' . $Unit[ 'ID' ] ) . '&Status=2';?>
		                            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Site', $Unit[ 'Tickets_On_Site' ], true, true, 'tickets.php?Unit=' . $Unit[ 'ID' ] ) . '&Status=3';?>
		                            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Unit[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Unit=' . $Unit[ 'ID' ] ) . '&Status=6';?>
		                        </div>
		                    </div>
		                    <div class='card card-primary my-3 col-12 col-lg-3'>
				              	<?php \singleton\bootstrap::getInstance( )->card_header( 'Violations', 'Violations', 'Violations', 'Unit', $Unit[ 'ID' ] );?>
				              	<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Violations' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
				              	  	<?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'violations.php?Unit=' . $Unit[ 'ID' ] );?>
				              	  	<?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Preliminary', $Unit[ 'Violations_Preliminary_Report' ], true, true, 'violations.php?Unit=' . $Unit[ 'ID' ] . '&Status=Preliminary Report');?>
				              	  	<?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Ongoing', $Unit[ 'Violations_Job_Created' ], true, true, 'violations.php?Unit=' . $Unit[ 'ID' ] ) . '&Status=Job Created';?>
				              	</div>
				            </div>
				            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Invoices', 'Invoice', 'Invoices', 'Unit', $Unit[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'invoices.php?Unit=' . $Unit[ 'ID' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Unit[ 'Invoices_Open' ], true, true, 'invoices.php?Unit=' . $Unit[ 'ID' ] . '&Status=0');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Unit[ 'Invoices_Closed' ], true, true, 'invoices.php?Unit=' . $Unit[ 'ID' ] ) . '&Status=1';?>
                                </div>
                            </div>
				        </div>
				    </div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>
<?php
	}
} else {?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
