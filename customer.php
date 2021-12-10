<?php
// Session set for the root index page
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection for the user and the hash
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
  // This selects the User and Hash from the Dbo
    $Connection = sqlsrv_fetch_array($result);
    //Sets $result into $Connection
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
// gets Employee first/last/employee ID/ Title/Field and sets to $User
	//Privileges
	$Access = 0;
	$Hex = 0;
  // Defaults Privileges to Zero
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
// Selects $User Privilege and appends to $_SESSION user array
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
// Checks $User Privilege and appends to $_SESSION user array
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Customer' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Customer' ] )
    ){ ?><?php require('404.html');?><?php }
    //If privleges dont check, 404s out
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'customer.php'
        )
      );
  // If privleges check, Timestamp $_SESSION user and show customer.php
    	$ID = isset( $_GET[ 'ID' ] )
			? $_GET[ 'ID' ]
			: (
				isset( $_POST[ 'ID' ] )
					? $_POST[ 'ID' ]
					: null
			);
    		$Name = isset( $_GET[ 'Name' ] )
        		? $_GET[ 'Name' ]
        		: (
        			isset( $_POST[ 'Name' ] )
        				? $_POST[ 'Name' ]
        				: null
      	);
  // sets $ID, $Name Variable and Posts ID and Name into $result
        $result = \singleton\database::getInstance( )->query(
        	null,
            "	SELECT 	Top 1
            			Customer.*
            	FROM    (
            				SELECT 	Customer.ID        AS ID,
            						Customer.Type      AS Type,
            						Rolodex.ID 		   AS Rolodex,
                                    Rolodex.Name       AS Name,
                                    Rolodex.Phone      AS Phone,
                                    Rolodex.Email      AS Email,
                                    Rolodex.Contact    AS Contact,
                                    Rolodex.Address    AS Street,
                                    Rolodex.City       AS City,
                                    Rolodex.State      AS State,
                                    Rolodex.Zip        AS Zip,
                                    Rolodex.Latt 	   AS Latitude,
                                    Rolodex.fLong      AS Longitude,
                                    Customer.Status    AS Status,
          							Rolodex.Website    AS Website,
          							Customer.Internet  AS Internet,
          							Customer.fLogin    AS Login,
          							Customer.Password  AS Password,
          							Rolodex.Geolock    AS Geofence,
                                    CASE    WHEN Locations.Count IS NULL THEN 0 
                                            ELSE Locations.Count END AS Locations_Count,
                                    CASE    WHEN Locations.Maintained IS NULL THEN 0 
                                            ELSE Locations.Maintained END AS Locations_Maintained,
                                    CASE    WHEN Locations.Unmaintained IS NULL THEN 0 
                                            ELSE Locations.Unmaintained END AS Locations_Unmaintained,
                                    CASE    WHEN Units.Count IS NULL THEN 0
                                            ELSE Units.Count END AS Units_Count,
                                    CASE    WHEN Units.Elevators IS NULL THEN 0
                                            ELSE Units.Elevators END AS Units_Elevators,
                                    CASE    WHEN Units.Escalators IS NULL THEN 0
                                            ELSE Units.Escalators END AS Units_Escalators,
                                    CASE    WHEN Units.Other IS NULL THEN 0
                                            ELSE Units.Other END AS Units_Other,
                                    CASE    WHEN Jobs.[Open] IS NULL THEN 0
                                            ELSE Jobs.[Open] END AS Jobs_Open,
                                    CASE    WHEN Jobs.[On_Hold] IS NULL THEN 0
                                            ELSE Jobs.[On_Hold] END AS Jobs_On_Hold,
                                    CASE    WHEN Jobs.[Closed] IS NULL THEN 0
                                            ELSE Jobs.[Closed] END AS Jobs_Closed,
                                    CASE    WHEN Tickets.Unassigned IS NULL THEN 0
                                            ELSE Tickets.Unassigned END AS Tickets_Open,
                                    CASE    WHEN Tickets.Assigned IS NULL THEN 0
                                            ELSE Tickets.Assigned END AS Tickets_Assigned,
                                    CASE    WHEN Tickets.En_Route IS NULL THEN 0
                                            ELSE Tickets.En_Route END AS Tickets_En_Route,
                                    CASE    WHEN Tickets.On_Site IS NULL THEN 0
                                            ELSE Tickets.On_Site END AS Tickets_On_Site,
                                    CASE    WHEN Tickets.Reviewing IS NULL THEN 0
                                            ELSE Tickets.Reviewing END AS Tickets_Reviewing,
                                    CASE    WHEN Violations.Preliminary IS NULL THEN 0
                                            ELSE Violations.Preliminary END AS Violations_Preliminary_Report,
                                    CASE    WHEN Violations.Job_Created IS NULL THEN 0
                                            ELSE Violations.Job_Created END AS Violations_Job_Created
							 FROM   Owner AS Customer
									LEFT JOIN Rol AS Rolodex ON Customer.Rol = Rolodex.ID
                                    LEFT JOIN (
                                        SELECT      Owner.ID AS Customer,
                                                    Sum( Maintained.Count ) AS Maintained,
                                                    Sum( Unmaintained.Count ) AS Unmaintained,
                                                    Count( Location.Loc ) AS Count 
                                        FROM        Owner 
                                                    LEFT JOIN Loc AS Location ON Owner.ID = Location.Owner
                                                    LEFT JOIN (
                                                        SELECT      Location.Loc AS Location,
                                                                    Count( Location.Loc ) AS Count
                                                        FROM        Loc AS Location 
                                                        WHERE       Location.Maint = 1
                                                        GROUP BY    Location.Loc
                                                    ) AS Maintained ON Location.Loc = Maintained.Location
                                                    LEFT JOIN (
                                                        SELECT      Location.Loc AS Location,
                                                                    Count( Location.Loc ) AS Count
                                                        FROM        Loc AS Location 
                                                        WHERE       Location.Maint = 0
                                                        GROUP BY    Location.Loc
                                                    ) AS Unmaintained ON Location.Loc = Unmaintained.Location
                                        GROUP BY    Owner.ID
                                    ) AS Locations ON Locations.Customer = Customer.ID
                                    LEFT JOIN (
                                        SELECT      Owner.ID AS Customer,
                                                    Sum( Units.Count ) AS Count,
                                                    Sum( Elevators.Count) AS Elevators,
                                                    Sum( Escalators.Count ) AS Escalators,
                                                    Sum( Other.Count ) AS Other
                                        FROM        Owner 
                                                    LEFT JOIN Loc AS Location ON Owner.ID = Location.Owner 
                                                    LEFT JOIN (
                                                        SELECT      Unit.Loc AS Location,
                                                                    Count( Unit.ID ) AS Count
                                                        FROM        Elev AS Unit
                                                        GROUP BY    Unit.Loc
                                                    ) AS Units ON Units.Location = Location.Loc
                                                    LEFT JOIN (
                                                        SELECT      Unit.Loc AS Location,
                                                                    Count( Unit.ID ) AS Count
                                                        FROM        Elev AS Unit
                                                        WHERE       Unit.Type = 'Elevator'
                                                        GROUP BY    Unit.Loc
                                                    ) AS Elevators ON Elevators.Location = Location.Loc
                                                    LEFT JOIN (
                                                        SELECT      Unit.Loc AS Location,
                                                                    Count( Unit.ID ) AS Count
                                                        FROM        Elev AS Unit
                                                        WHERE       Unit.Type = 'Escalator'
                                                        GROUP BY    Unit.Loc
                                                    ) AS Escalators ON Escalators.Location = Location.Loc
                                                    LEFT JOIN (
                                                        SELECT      Unit.Loc AS Location,
                                                                    Count( Unit.ID ) AS Count
                                                        FROM        Elev AS Unit
                                                        WHERE       Unit.Type NOT IN ( 'Elevator', 'Escalator' )
                                                        GROUP BY    Unit.Loc
                                                    ) AS Other ON Other.Location = Location.Loc
                                        GROUP BY    Owner.ID
                                    ) AS Units ON Units.Customer = Customer.ID 
                                    LEFT JOIN (
                                        SELECT  Owner.ID AS Customer,
                                                [Open].Count AS [Open],
                                                [On_Hold].Count AS On_Hold,
                                                [Closed].Count AS Closed
                                        FROM    Owner
                                                LEFT JOIN (
                                                    SELECT      Job.Owner AS Customer,
                                                                Count( Job.ID ) AS Count 
                                                    FROM        Job 
                                                    WHERE       Job.Status = 0
                                                    GROUP BY    Job.Owner
                                                ) AS [Open] ON [Open].Customer = Owner.ID
                                                LEFT JOIN (
                                                    SELECT      Job.Owner AS Customer,
                                                                Count( Job.ID ) AS Count 
                                                    FROM        Job 
                                                    WHERE       Job.Status = 2
                                                    GROUP BY    Job.Owner
                                                ) AS [On_Hold] ON [On_Hold].Customer = Owner.ID
                                                LEFT JOIN (
                                                    SELECT      Job.Owner AS Customer,
                                                                Count( Job.ID ) AS Count 
                                                    FROM        Job 
                                                    WHERE       Job.Status = 1
                                                    GROUP BY    Job.Owner
                                                ) AS [Closed] ON [Closed].Customer = Owner.ID
                                    ) AS Jobs ON Jobs.Customer = Customer.ID
                                    LEFT JOIN (
                                        SELECT  Owner.ID AS Customer,
                                                Unassigned.Count AS Unassigned,
                                                Assigned.Count AS Assigned,
                                                En_Route.Count AS En_Route,
                                                On_Site.Count AS On_Site,
                                                Reviewing.Count AS Reviewing
                                        FROM    Owner 
                                                LEFT JOIN (
                                                  SELECT    Location.Owner AS Customer,
                                                            Count( TicketO.ID ) AS Count
                                                  FROM      TicketO
                                                            LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                                  WHERE     TicketO.Assigned = 0
                                                  GROUP BY  Location.Owner
                                                ) AS Unassigned ON Unassigned.Customer = Owner.ID
                                                LEFT JOIN (
                                                  SELECT    Location.Owner AS Customer,
                                                            Count( TicketO.ID ) AS Count
                                                  FROM      TicketO
                                                            LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                                  WHERE     TicketO.Assigned = 1
                                                  GROUP BY  Location.Owner
                                                ) AS Assigned ON Assigned.Customer = Owner.ID
                                                LEFT JOIN (
                                                  SELECT    Location.Owner AS Customer,
                                                            Count( TicketO.ID ) AS Count
                                                  FROM      TicketO
                                                            LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                                  WHERE     TicketO.Assigned = 2
                                                  GROUP BY  Location.Owner
                                                ) AS En_Route ON En_Route.Customer = Owner.ID
                                                LEFT JOIN (
                                                  SELECT    Location.Owner AS Customer,
                                                            Count( TicketO.ID ) AS Count
                                                  FROM      TicketO
                                                            LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                                  WHERE     TicketO.Assigned = 3
                                                  GROUP BY  Location.Owner
                                                ) AS On_Site ON On_Site.Customer = Owner.ID
                                                LEFT JOIN (
                                                  SELECT    Location.Owner AS Customer,
                                                            Count( TicketO.ID ) AS Count
                                                  FROM      TicketO
                                                            LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                                  WHERE     TicketO.Assigned = 6
                                                  GROUP BY  Location.Owner
                                                ) AS Reviewing ON Reviewing.Customer = Owner.ID
                                    ) AS Tickets ON Tickets.Customer = Customer.ID
                                    LEFT JOIN (
                                        SELECT  Owner.ID AS Customer,
                                                Preliminary.Count AS Preliminary,
                                                Job_Created.Count AS Job_Created
                                        FROM    Owner 
                                                LEFT JOIN (
                                                  SELECT    Location.Owner AS Customer,
                                                            Count( Violation.ID ) AS Count
                                                  FROM      Violation
                                                            LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                                  WHERE     Violation.Status = 'Preliminary Report'
                                                  GROUP BY  Location.Owner
                                                ) AS Preliminary ON Preliminary.Customer = Owner.ID
                                                LEFT JOIN (
                                                  SELECT    Location.Owner AS Customer,
                                                            Count( Violation.ID ) AS Count
                                                  FROM      Violation
                                                            LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                                  WHERE     Violation.Status = 'Job Created'
                                                  GROUP BY  Location.Owner
                                                ) AS Job_Created ON Job_Created.Customer = Owner.ID
                                    ) AS Violations ON Violations.Customer = Customer.ID
            		) AS Customer
            	WHERE   	Customer.ID = ?
            			OR 	Customer.Name = ?;",
            array(
            	$ID,
            	$Name
                  )
              );
        //var_dump( sqlsrv_errors( ) );
        $Customer = (  empty( $ID )
                       &&  !empty( $Name )
                       &&  !$result
                    )    || (empty( $ID )
                       &&  empty( $Name )
                    )    ? array(
        	'ID'        => null,
        	'Name'      => isset( $_GET [ 'Name' ] ) ? $_GET ['Name'] : null,
        	'Login'     => null,
        	'Password'  => null,
        	'Geofence'  => null,
        	'Type'      => null,
        	'Status'    => null,
        	'Website'   => null,
        	'Internet'  => null,
        	'Street'    => null,
        	'City'      => null,
        	'State'     => null,
        	'Zip'       => null,
        	'Latitude'  => isset( $_GET [ 'Latitude' ] ) ? $_GET ['Latitude'] : null,
        	'Longitude' => isset( $_GET [ 'Longitude' ] ) ? $_GET ['Longitude'] : null,
            'Phone'     =>  null,
            'Email'     =>  null,
            'Rolodex'   => isset( $_GET [ 'Rolodex' ] ) ? $_GET ['Rolodex'] : null,
            'Phone'     => null,
            'Email'     => null,
            'Contact'   => isset( $_GET [ 'Contact' ] ) ? $_GET [ 'Contact' ] : null
        ) : sqlsrv_fetch_array($result);
//Binds $ID, $Name, $Customer and query values into the $result variable

        if( isset( $_POST ) && count( $_POST ) > 0 ){
          // if the $_Post is set and the count is null, select if available
        	$Customer[ 'Name' ] 		= isset( $_POST[ 'Name' ] ) 	 ? $_POST[ 'Name' ] 	 : $Customer[ 'Name' ];
          $Customer[ 'Contact' ] 	    = isset( $_POST[ 'Contact' ] ) ? $_POST[ 'Contact' ]     : $Customer[ 'Contact' ];
        	$Customer[ 'Phone' ] 		= isset( $_POST[ 'Phone' ] ) 	 ? $_POST[ 'Phone' ] 	 : $Customer[ 'Phone' ];
        	$Customer[ 'Email' ] 		= isset( $_POST[ 'Email' ] ) 	 ? $_POST[ 'Email' ] 	 : $Customer[ 'Email' ];
        	$Customer[ 'Login' ] 		= isset( $_POST[ 'Login' ] ) 	 ? $_POST[ 'Login' ] 	 : $Customer[ 'Login' ];
        	$Customer[ 'Password' ]     = isset( $_POST[ 'Password' ] )  ? $_POST[ 'Password' ]  : $Customer[ 'Password' ];
        	$Customer[ 'Geofence' ]     = isset( $_POST[ 'Geofence' ] )  ? $_POST[ 'Geofence' ]  : $Customer[ 'Geofence' ];
        	$Customer[ 'Type' ]         = isset( $_POST[ 'Type' ] ) 	 ? $_POST[ 'Type' ] 	 : $Customer[ 'Type' ];
        	$Customer[ 'Status' ] 	    = isset( $_POST[ 'Status' ] ) 	 ? $_POST[ 'Status' ] 	 : $Customer[ 'Status' ];
        	$Customer[ 'Website' ] 	    = isset( $_POST[ 'Website' ] ) 	 ? $_POST[ 'Website' ] 	 : $Customer[ 'Website' ]; 
        	$Customer[ 'Internet' ]     = isset( $_POST[ 'Internet' ] )  ? $_POST[ 'Internet' ]  : $Customer[ 'Internet' ];
        	$Customer[ 'Street' ] 	    = isset( $_POST[ 'Street' ] ) 	 ? $_POST[ 'Street' ] 	 : $Customer[ 'Street' ];
        	$Customer[ 'City' ] 		= isset( $_POST[ 'City' ] ) 	 ? $_POST[ 'City' ] 	 : $Customer[ 'City' ];
        	$Customer[ 'State' ] 		= isset( $_POST[ 'State' ] ) 	 ? $_POST[ 'State' ] 	 : $Customer[ 'State' ];
        	$Customer[ 'Zip' ] 			= isset( $_POST[ 'Zip' ] ) 		 ? $_POST[ 'Zip' ] 		 : $Customer[ 'Zip' ];
        	$Customer[ 'Latitude' ] 	= isset( $_POST[ 'Latitude' ] )  ? $_POST[ 'Latitude' ]  : $Customer[ 'Latitude' ];
        	$Customer[ 'Longitude' ] 	= isset( $_POST[ 'Longitude' ] ) ? $_POST[ 'Longitude' ] : $Customer[ 'Longitude' ];

        	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
        		$result = \singleton\database::getInstance( )->query(
        			null,
        			"	DECLARE @MAXID INT;
        				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Rol ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Rol ) END ;
        				INSERT INTO Rol(
    						  ID,
        					Type,
        					Name,
        					Website,
        					Address,
        					City,
        					State,
        					Zip,
        					Latt,
        					fLong,
        					Geolock
        				)
        				VALUES( @MAXID + 1 , 0, ?, ?, ?, ?, ?, ?, ?, ?, ? );
        				SELECT @MAXID + 1;",
        			array(
        				$Customer[ 'Name' ],
        				$Customer[ 'Website' ],
        				$Customer[ 'Street' ],
        				$Customer[ 'City' ],
        				$Customer[ 'State' ],
        				$Customer[ 'Zip' ],
        				$Customer[ 'Latitude' ],
        				$Customer[ 'Longitude' ],
        				isset( $Customer[ 'Geofence' ] ) ? $Customer[ 'Geofence' ] : 0
        			)
        		);
        		sqlsrv_next_result( $result );
    //Update query to fill values for $Customer and appends to $result for any updated colums
        		$Customer[ 'Rolodex' ] = sqlsrv_fetch_array( $result )[ 0 ];
// finds any result with the value of 0/ null
        		$result = \singleton\database::getInstance( )->query(
        			null,
        			"	DECLARE @MAXID INT;
        				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Owner ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Owner ) END ;
        				INSERT INTO Owner(
        					ID,
                  Status,
        					Locs,
        					Elevs,
        					Balance,
        					Type,
        					Billing,
        					Central,
        					Rol,
        					Internet,
        					TicketO,
        					TicketD,
        					Ledger,
        					Request,
        					Password,
        					fLogin,
        					Statement,
        					Approve,
        					InvoiceO,
        					Quote,
        					QuoteX,
        					Dispatch,
        					Service,
        					Pay,
        					Safety,
        					TicketEmail,
        					TFMID,
        					TFMSource,
        					QuoteEmail
        				)
        				VALUES ( @MAXID + 1, ?, 0, 0, 0, ?, 0, null, ?, ?, 0, 0, 0, 0, null, null, 0, 0, 0, 0, 0, 0, 0, 0, 0, null, '', '', null );
        				SELECT @MAXID + 1;",
        			array(
        				$Customer[ 'Status' ],
        				$Customer[ 'Type' ],
        				$Customer[ 'Rolodex' ],
        				$Customer[ 'Internet' ]
        			)
        		);
// query that inserts values into the $Customer [rolodex] variable datatable and appends it to the $result variable
        		sqlsrv_next_result( $result );
        		$Customer[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
// Checks the $Customer[ID] for any fields that are null, if none exit,
        		header( 'Location: customer.php?ID=' . $Customer[ 'ID' ] );
        		exit;
        	} else {
        		\singleton\database::getInstance( )->query(
	        		null,
	        		"	UPDATE 	Owner
	        			SET Owner.Status = ?,
	        					Owner.Internet = ?,
	        					Owner.fLogin = ?,
	        					Owner.Password = ?,
	        					Owner.Type = ?
	        			WHERE 	Owner.ID = ?;",
	        		array(
	        			$Customer[ 'Status' ],
	        			$Customer[ 'Internet' ],
	        			$Customer[ 'Login' ],
	        			$Customer[ 'Password' ],
	        			$Customer[ 'Type' ],
	        			$Customer[ 'ID' ]
	        		)
	        	);
	        	\singleton\database::getInstance( )->query(
	        		null,
	        		"	UPDATE 	Rol
	        			SET 	Rol.Name = ?,
	        					Rol.Website = ?,
	        					Rol.Address = ?,
	        					Rol.City = ?,
	        					Rol.State = ?,
	        					Rol.Zip = ?,
	        					Rol.Latt = ?,
	        					Rol.fLong = ?,
                    Rol.Phone = ?,
                    Rol.EMail = ?

	        			WHERE 	Rol.ID = ?;",
	        		array(
	        			$Customer[ 'Name' ],
	        			$Customer[ 'Website' ],
	        			$Customer[ 'Street' ],
	        			$Customer[ 'City' ],
	        			$Customer[ 'State' ],
	        			$Customer[ 'Zip' ],
	        			$Customer[ 'Latitude' ],
	        			$Customer[ 'Longitude' ],
                $Customer[ 'Phone' ],
                $Customer[ 'Email' ],
	        			$Customer[ 'Rolodex' ]
	        		)
	        	);
        	}
        }
    // if any fields are 0/null, attempt to update said colums from owner/rol ID
?><!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<!-- required files from other locations, such as css, js, bootstrap and, Entity files  -->
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php'); ?>
        <div id="page-wrapper" class='content'>
        	<div class='card card-primary'><form action='customer.php?ID=<?php echo $Customer[ 'ID' ];?>' method='POST'>
                <input type='hidden' name='ID' value='<?php echo $Customer[ 'ID' ];?>' />
        		<?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Customer', 'Customers', $Customer[ 'ID' ] );?>
        		<div class='card-body bg-dark text-white'>
					<div class='row g-0' data-masonry='{"percentPosition": true }'>
						<?php if( !in_array( $Customer[ 'Latitude' ], array( null, 0 ) ) && !in_array( $Customer['Longitude' ], array( null, 0 ) ) ){ ?><div class='card card-primary my-3 col-12 col-lg-3'>
                            <?php \singleton\bootstrap::getInstance( )->card_header( 'Map' );?>
							<div id='customer_map' class='card-body p-0 bg-dark position-relative overflow-hidden' style='width:100%;height:350px;z-index:0;<?php echo isset( $_SESSION[ 'Cards' ][ 'Map' ] ) && $_SESSION[ 'Cards' ][ 'Map' ] == 0 ? 'display:none;' : null;?>'></div>
							<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB05GymhObM_JJaRCC3F4WeFn3KxIOdwEU"></script>
								<script type="text/javascript">
					                var map;
					                function initialize() {
					                     map = new google.maps.Map(
					                        document.getElementById( 'customer_map' ),
					                        {
					                          zoom: 10,
					                          center: new google.maps.LatLng( <?php echo $Customer[ 'Latitude' ];?>, <?php echo $Customer[ 'Longitude' ];?> ),
					                          mapTypeId: google.maps.MapTypeId.ROADMAP
					                        }
					                    );
					                    var markers = [];
					                    markers[0] = new google.maps.Marker({
					                        position: {
					                            lat:<?php echo $Customer['Latitude'];?>,
					                            lng:<?php echo $Customer['Longitude'];?>
					                        },
					                        map: map,
					                        title: '<?php echo $Customer[ 'Name' ];?>'
					                    });
					                }
					                $(document).ready(function(){ initialize(); });
					            </script>
						</div><?php }?>
						<div class='card card-primary my-3 col-12 col-lg-3'>
							<?php \singleton\bootstrap::getInstance( )->card_header( 'Information' ); ?>
						 	<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                                <?php 
                                    \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', $Customer[ 'Name' ] );
                                    \singleton\bootstrap::getInstance( )->card_row_form_select( 'Type', $Customer[ 'Type' ], array(
                                        'General' => 'General',
                                        'Bank' => 'Bank',
                                        'Churches' => 'Churches',
                                        'Hospitals' => 'Hospitals',
                                        'Property Manage' => 'Property Manage',
                                        'Restaraunts' => 'Restaraunts',
                                        'Schools' => 'Schools'
                                    ) );
                                    \singleton\bootstrap::getInstance( )->card_row_form_select( 'Status', $Customer[ 'Status' ], array(
                                        0 => 'Disabled',
                                        1 => 'Enabled'
                                    ) );
								    \singleton\bootstrap::getInstance( )->card_row_form_input_url( 'Website', $Customer[ 'Website' ] );
								    \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Address', 'https://maps.google.com/?q=' . $Customer['Street'].' '.$Customer['City'].' '.$Customer[ 'State' ].' '.$Customer[ 'Zip' ] );
                                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Street', $Customer[ 'Street' ] );
                                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'City', $Customer[ 'City' ] );
                                    \singleton\bootstrap::getInstance( )->card_row_form_select_sub( 'State', $Customer[ 'State' ],  array( 'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming' ) );
                                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Zip', $Customer[ 'Zip' ] );
                                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub_number( 'Latitude',  $Customer[ 'Latitude' ] );
                                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub_number( 'Longitude',  $Customer[ 'Longitude' ] );
                                ?>
							</div>
		      </div>
          <!-- End of customer inforation card, ending with card-footer div class with a button for save  -->
            <div class='card card-primary my-3 col-12 col-lg-3'>
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  	<div class='col-8'><h5><?php \singleton\fontawesome::getInstance( )->Users( 1 );?><span>Contacts</span></h5></div>
					<div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='contact.php?Name=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Add( 1 );?></button></div>
					<div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='contacts.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
              </div>
              <!-- Card hedding, that holds customer contacts, with a post call that gets customer contact information based on $Customer ID  -->
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Contacts' ] ) && $_SESSION[ 'Cards' ][ 'Contacts' ] == 0 ? "style='display:none;'" : null;?>>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_url( 'Contact', $Customer[ 'Contact' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_tel( 'Phone', $Customer[ 'Phone' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_email( 'Email', $Customer[ 'Email' ] );?>
              </div>
            </div>
        	<!-- End of customer contact information card, ending with customer card-footer and a submit button-->
        	<div class='card card-primary my-3 col-12 col-lg-3'>
				<?php \singleton\bootstrap::getInstance( )->card_header( 'Portal' );?>
  				<!-- Start of a new card Using a post method to fill data based on $Customer ID -->
				<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Portal' ] ) && $_SESSION[ 'Cards' ][ 'Portal' ] == 0 ? "style='display:none;'" : null;?>>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Login' );?>
					<div class='row g-0'>
						<div class='col-1'>&nbsp;</div>
			 			<div class='col-3'>Portal:</div>
			 			<div class='col-8'><select
                            <?php echo check( privilege_execute, level_server, isset( $Privileges[ 'Customer' ] ) ? $Privileges[ 'Customer' ] : 0 ) ? null : 'disabled';?>
                            class='form-control edit'
                            name='Internet' >
    			 				<option value=''>Select</option>
    			 				<option value='0' <?php echo $Customer[ 'Internet' ] == 0 ? 'selected' : null;?>>Disabled</option>
    			 				<option value='1' <?php echo $Customer[ 'Internet' ] == 1 ? 'selected' : null;?>>Enabled</option>
			 			</select></div>
			 		</div>
					<div class='row g-0' <?php echo $Customer[ 'Internet' ] == 0 ? "style='display:none;'" : null;?>>
						<div class='col-1'>&nbsp;</div>
			 			<div class='col-3'>Username:</div>
			 			<div class='col-8'><input type='text' class='form-control edit' name='Login' value='<?php echo $Customer[ 'Login' ];?>' /></div>
			 		</div>
			 		<div class='row g-0' <?php echo $Customer[ 'Internet' ] == 0 ? "style='display:none;'" : null;?>>
			 			<div class='col-1'>&nbsp;</div>
			 			<div class='col-3'>Password:</div>
			 			<div class='col-8'><input type='password' class='form-control edit' name='Login' value='<?php echo $Customer[ 'Login' ];?>' name='Password' value='<?php echo $Customer[ 'Password' ];?>' /></div>
			 		</div>
			 		<div class='row g-0'>
			 			<div class='col-1'>&nbsp;</div>
			 			<div class='col-3'>Geofence:</div>
			 			<div class='col-8'><select
                            <?php echo check( privilege_execute, level_server, isset( $Privileges[ 'Customer' ] ) ? $Privileges[ 'Customer' ] : 0 ) ? null : 'disabled';?>
                            class='form-control edit'
                            name='Geofence' >
    			 				<option value=''>Select</option>
    			 				<option value='0' <?php echo $Customer[ 'Geofence' ] == 0 ? 'selected' : null;?>>Disabled</option>
    			 				<option value='1' <?php echo $Customer[ 'Geofence' ] == 1 ? 'selected' : null;?>>Enabled</option>
			 			</select></div>
			 		</div>
				</div>
			</div>
			<div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Locations', 'Location', 'Locations', 'Customer', $Customer[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Locations' ] ) && $_SESSION[ 'Cards' ][ 'Locations' ] == 0 ? "style='display:none;'" : null;?>>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Locations', 'locations.php?Customer=' . $Customer[ 'ID' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Maintain', $Customer[ 'Locations_Maintained' ], true, true, 'locations.php?Customer=' . $Customer[ 'ID' ] . '&Maintained=1');?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Customer[ 'Locations_Unmaintained' ], true, true, 'locations.php?Customer=' . $Customer[ 'ID' ] ) . '&Maintained=0';?>
                </div>
            </div>
            <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Units', 'Unit', 'Units', 'Customer', $Customer[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'units.php?Customer=' . $Customer[ 'ID' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Elevators', $Customer[ 'Units_Elevators' ], true, true, 'units.php?Customer=' . $Customer[ 'ID' ] . '&Type=Elevator');?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Escalators', $Customer[ 'Units_Escalators' ], true, true, 'units.php?Customer=' . $Customer[ 'ID' ] ) . '&Type=Escalator';?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Escalators', $Customer[ 'Units_Other' ], true, true, 'units.php?Customer=' . $Customer[ 'ID' ] ) . '&Type=Other';?>
                </div>
            </div>
            <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Jobs', 'Job', 'Jobs', 'Customer', $Customer[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Jobs' ] ) && $_SESSION[ 'Cards' ][ 'Jobs' ] == 0 ? "style='display:none;'" : null;?>>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'jobs.php?Customer=' . $Customer[ 'ID' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Customer[ 'Jobs_Open' ], true, true, 'jobs.php?Customer=' . $Customer[ 'ID' ] . '&Status=0');?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Hold', $Customer[ 'Jobs_On_Hold' ], true, true, 'jobs.php?Customer=' . $Customer[ 'ID' ] ) . '&Status=2';?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Customer[ 'Jobs_Closed' ], true, true, 'jobs.php?Customer=' . $Customer[ 'ID' ] ) . '&Status=1';?>
                </div>
            </div>
            <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Customer', $Customer[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Customer=' . $Customer[ 'ID' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Customer[ 'Tickets_Open' ], true, true, 'tickets.php?Customer=' . $Customer[ 'ID' ] . '&Status=0');?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Customer[ 'Tickets_Assigned' ], true, true, 'tickets.php?Customer=' . $Customer[ 'ID' ] ) . '&Status=1';?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En Route', $Customer[ 'Tickets_En_Route' ], true, true, 'tickets.php?Customer=' . $Customer[ 'ID' ] ) . '&Status=2';?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Site', $Customer[ 'Tickets_On_Site' ], true, true, 'tickets.php?Customer=' . $Customer[ 'ID' ] ) . '&Status=3';?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Customer[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Customer=' . $Customer[ 'ID' ] ) . '&Status=6';?>
                </div>
            </div>
            <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Violations', 'Violations', 'Violations', 'Customer', $Customer[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Violations' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'violations.php?Customer=' . $Customer[ 'ID' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Preliminary', $Customer[ 'Violations_Preliminary_Report' ], true, true, 'violations.php?Customer=' . $Customer[ 'ID' ] . '&Status=Preliminary Report');?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Ongoing', $Customer[ 'Violations_Job_Created' ], true, true, 'violations.php?Customer=' . $Customer[ 'ID' ] ) . '&Status=Job Created';?>
                </div>
            </div>
			<div class='card card-primary my-3 col-12 col-lg-3'>
				<?php \singleton\bootstrap::getInstance( )->card_header( 'Proposals', 'Proposal', 'Proposals', 'Customer', $Customer[ 'ID' ] );?>
				<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
					<?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'proposals.php?Customer=' . $Customer[ 'ID' ] );?>
					<div class='row g-0'>
						<div class='col-1'>&nbsp;</div>
					    <div class='col-3 border-bottom border-white my-auto'>Open</div>
					    <div class='col-6'><input class='form-control' type='text' readonly name='Proposals' value='<?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT 	Count(Estimate.ID) AS Proposals
								FROM   	Estimate
									   	LEFT JOIN Loc AS Location ON Estimate.LocID = Location.Loc
								WHERE  		Location.Owner = ?
										AND Estimate.Status = 0
							;",array($Customer[ 'ID' ]));
							echo $r ? sqlsrv_fetch_array($r)['Proposals'] : 0;
						?>' /></div>
						<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
					</div>
					<div class='row g-0'>
						<div class='col-1'>&nbsp;</div>
					    <div class='col-3 border-bottom border-white my-auto'>Awarded</div>
					    <div class='col-6'><input class='form-control' type='text' readonly name='Proposals' value='<?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT 	Count(Estimate.ID) AS Proposals
								FROM   	Estimate
									   	LEFT JOIN Loc AS Location ON Estimate.LocID = Location.Loc
								WHERE  		Location.Owner = ?
										AND Estimate.Status = 4
							;",array($Customer[ 'ID' ]));
							echo $r ? sqlsrv_fetch_array($r)['Proposals'] : 0;
						?>' /></div>
						<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
					</div>
				</div>
			</div>
			<div class='card card-primary my-3 col-12 col-lg-3'>
				<?php \singleton\bootstrap::getInstance( )->card_header( 'Invoices', 'Invoice', 'Invoices', 'Customer', $Customer[ 'ID' ] );?>
				<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
					<?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'invoices.php?Customer=' . $Customer[ 'ID' ] );?>
					<?php if(isset($Privileges['Invoice']) ) {?>
					<div class='row g-0'>
						<div class='col-1'>&nbsp;</div>
					    <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Open</div>
					    <div class='col-6'><input class='form-control' type='text' readonly name='Collections' value='<?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT Count( OpenAR.Ref ) AS Count
								FROM   OpenAR
									   LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
								WHERE  Location.Owner = ?
							;",array($Customer[ 'ID' ]));
							$Count = $r ? sqlsrv_fetch_array($r)['Count'] : 0;
							echo $Count
						?>' /></div>
						<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
					</div>
					<?php }?>
					<?php if(isset($Privileges['Invoice']) ) {?>
					<div class='row g-0'>
						<div class='col-1'>&nbsp;</div>
					    <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Closed</div>
					    <div class='col-6'><input class='form-control' type='text' readonly name='Collections' value='<?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT 	Count( Invoice.Ref ) AS Count
								FROM   	Invoice
									   	LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
								WHERE  		Location.Owner = ?
										AND Invoice.Ref NOT IN ( SELECT Ref FROM OpenAR )

							;",array($Customer[ 'ID' ]));
							$Count = $r ? sqlsrv_fetch_array($r)['Count'] : 0;
							echo $Count
						?>' /></div>
						<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
					</div>
					<?php }?>
				</div>
			</div>
		</div>
  	</div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($Customer[ 'ID' ]) || !is_numeric($Customer[ 'ID' ])) ? "s.php" : ".php?ID={$Customer[ 'ID' ]}";?>";</script></head></html><?php }?>
