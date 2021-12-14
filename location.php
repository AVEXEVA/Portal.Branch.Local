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
        $_SESSION[ 'Connection' ][ 'User' ]
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
  if(   !isset( $Connection[ 'ID' ] )
      ||  !isset( $Privileges[ 'Location' ] )
      ||  !check( privilege_read, level_group, $Privileges[ 'Location' ] )
  ){ ?><?php require('404.html');?><?php }
  else {
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
  $Territory = isset( $_GET[ 'Territory' ] )
    ? $_GET[ 'Territory' ]
    : (
      isset( $_POST[ 'Territory' ] )
        ? $_POST[ 'Territory' ]
        : null
    );
    $result = \singleton\database::getInstance( )->query(
    	null,
    	"	SELECT TOP 1
                    Location.Loc         AS ID,
                    Location.Tag         AS Name,
                 	  Location.Status      AS Status,
                    Location.Address     AS Street,
                    Location.City        AS City,
                    Location.State       AS State,
                    Location.Zip         AS Zip,
                    Location.fLong 		   AS Longitude,
                    Location.Latt 		   AS Latitude,
                    Location.Balance     AS Balance,
                    Location.Custom8 	   AS Resident_Mechanic,
                    Location.Maint 		   AS Maintenance,
                    Location.Geolock 	   AS Geofence,
                    Location.STax 		   AS Sales_Tax,
                    Location.InUse 		   AS In_Use,
                    Customer.ID 		     AS Customer_ID,
                    Customer.Name        AS Customer_Name,
                    Location.Route       AS Route_ID,
                    Route.Name 			     AS Route_Name,
                    Employee.ID          AS Route_Mechanic_ID,
                    Employee.fFirst      AS Route_Mechanic_First_Name,
                    Employee.Last        AS Route_Mechanic_Last_Name,
                    Location.Owner 	     AS Customer_ID,
                    Customer.Name    	   AS Customer_Name,
                    Territory.ID 		     AS Territory_ID,
                    Territory.Name       AS Territory_Name,
                    Division.ID 		     AS Division_ID,
                    Division.Name 		    AS Division_Name,
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
                            ELSE Violations.Job_Created END AS Violations_Job_Created,
                    CASE    WHEN Invoices.[Open] IS NULL THEN 0
                            ELSE Invoices.[Open] END AS Invoices_Open,
                    CASE    WHEN Invoices.[Closed] IS NULL THEN 0
                            ELSE Invoices.[Closed] END AS Invoices_Closed,
                    CASE    WHEN Proposals.[Open] IS NULL THEN 0
                            ELSE Proposals.[Open] END AS Proposals_Open,
                    CASE    WHEN Proposals.[Closed] IS NULL THEN 0
                            ELSE Proposals.[Closed] END AS Proposals_Closed
            FROM    Loc AS Location
                    LEFT JOIN Zone  AS Division  ON Location.Zone   = Division.ID
                    LEFT JOIN Terr  AS Territory ON Territory.ID    = Location.Terr
                    LEFT JOIN Route AS Route     ON Location.Route  = Route.ID
                    LEFT JOIN Emp   AS Employee  ON Route.Mech      = Employee.fWork
                    LEFT JOIN (
                			SELECT 	Owner.ID    	AS ID,
    		                    	Rol.Name    	AS Name,
    		                    	Rol.Address 	AS Street,
    				                  Rol.City    	AS City,
    				                  Rol.State   	AS State,
    				                  Rol.Zip     	AS Zip,
    				                  Owner.Status  	AS Status,
                              Rol.Website 	AS Website
                      FROM    Owner
                              LEFT JOIN Rol ON Owner.Rol 			= Rol.ID
                    ) AS Customer ON Location.Owner = Customer.ID
                    LEFT JOIN (
                      SELECT      Location.Loc AS Location,
                                  Sum( Units.Count ) AS Count,
                                  Sum( Elevators.Count) AS Elevators,
                                  Sum( Escalators.Count ) AS Escalators,
                                  Sum( Other.Count ) AS Other
                      FROM        Loc AS Location
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
                      GROUP BY    Location.Loc
                  ) AS Units ON Units.Location = Location.Loc
                  LEFT JOIN (
                      SELECT  Location.Loc AS Location,
                              [Open].Count AS [Open],
                              [On_Hold].Count AS On_Hold,
                              [Closed].Count AS Closed
                      FROM    Loc AS Location
                              LEFT JOIN (
                                  SELECT      Job.Loc AS Location,
                                              Count( Job.ID ) AS Count
                                  FROM        Job
                                  WHERE       Job.Status = 0
                                  GROUP BY    Job.Loc
                              ) AS [Open] ON [Open].Location = Location.Loc
                              LEFT JOIN (
                                  SELECT      Job.Loc AS Location,
                                              Count( Job.ID ) AS Count
                                  FROM        Job
                                  WHERE       Job.Status = 2
                                  GROUP BY    Job.Loc
                              ) AS [On_Hold] ON [On_Hold].Location = Location.Loc
                              LEFT JOIN (
                                  SELECT      Job.Loc AS Location,
                                              Count( Job.ID ) AS Count
                                  FROM        Job
                                  WHERE       Job.Status = 1
                                  GROUP BY    Job.Loc
                              ) AS [Closed] ON [Closed].Location = Location.Loc
                  ) AS Jobs ON Jobs.Location = Location.Loc
                  LEFT JOIN (
                      SELECT  Location.Loc AS Location,
                              Unassigned.Count AS Unassigned,
                              Assigned.Count AS Assigned,
                              En_Route.Count AS En_Route,
                              On_Site.Count AS On_Site,
                              Reviewing.Count AS Reviewing
                      FROM    Loc AS Location
                              LEFT JOIN (
                                SELECT    Location.Loc AS Location,
                                          Count( TicketO.ID ) AS Count
                                FROM      TicketO
                                          LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                WHERE     TicketO.Assigned = 0
                                GROUP BY  Location.Loc
                              ) AS Unassigned ON Unassigned.Location = Location.Loc
                              LEFT JOIN (
                                SELECT    Location.Loc AS Location,
                                          Count( TicketO.ID ) AS Count
                                FROM      TicketO
                                          LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                WHERE     TicketO.Assigned = 1
                                GROUP BY  Location.Loc
                              ) AS Assigned ON Assigned.Location = Location.Loc
                              LEFT JOIN (
                                SELECT    Location.Loc AS Location,
                                          Count( TicketO.ID ) AS Count
                                FROM      TicketO
                                          LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                WHERE     TicketO.Assigned = 2
                                GROUP BY  Location.Loc
                              ) AS En_Route ON En_Route.Location = Location.Loc
                              LEFT JOIN (
                                SELECT    Location.Loc AS Location,
                                          Count( TicketO.ID ) AS Count
                                FROM      TicketO
                                          LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                WHERE     TicketO.Assigned = 3
                                GROUP BY  Location.Loc
                              ) AS On_Site ON On_Site.Location = Location.Loc
                              LEFT JOIN (
                                SELECT    Location.Loc AS Location,
                                          Count( TicketO.ID ) AS Count
                                FROM      TicketO
                                          LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                WHERE     TicketO.Assigned = 6
                                GROUP BY  Location.Loc
                              ) AS Reviewing ON Reviewing.Location = Location.Loc
                  ) AS Tickets ON Tickets.Location = Location.Loc
                  LEFT JOIN (
                      SELECT  Location.Loc AS Location,
                              Preliminary.Count AS Preliminary,
                              Job_Created.Count AS Job_Created
                      FROM    Loc AS Location
                              LEFT JOIN (
                                SELECT    Location.Loc AS Location,
                                          Count( Violation.ID ) AS Count
                                FROM      Violation
                                          LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                WHERE     Violation.Status = 'Preliminary Report'
                                GROUP BY  Location.Loc
                              ) AS Preliminary ON Preliminary.Location = Location.Loc
                              LEFT JOIN (
                                SELECT    Location.Loc AS Location,
                                          Count( Violation.ID ) AS Count
                                FROM      Violation
                                          LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                WHERE     Violation.Status = 'Job Created'
                                GROUP BY  Location.Loc
                              ) AS Job_Created ON Job_Created.Location = Location.Loc
                  ) AS Violations ON Violations.Location = Location.Loc
                  LEFT JOIN (
                    SELECT    Location.Loc AS Location,
                              [Open].Count AS [Open],
                              [Closed].Count AS Closed
                    FROM      Loc AS Location
                              LEFT JOIN (
                                SELECT    Invoice.Loc AS Location,
                                          Count( Invoice.Ref ) AS Count
                                FROM      Invoice
                                WHERE     Invoice.Ref IN ( SELECT Ref FROM OpenAR )
                                GROUP BY  Invoice.Loc
                              ) AS [Open] ON Location.Loc = [Open].Location
                              LEFT JOIN (
                                SELECT    Invoice.Loc AS Location,
                                          Count( Invoice.Ref ) AS Count
                                FROM      Invoice
                                WHERE     Invoice.Ref NOT IN ( SELECT Ref FROM OpenAR )
                                GROUP BY  Invoice.Loc
                              ) AS [Closed] ON Location.Loc = [Closed].Location
                  ) AS Invoices ON Invoices.Location = Location.Loc
                  LEFT JOIN (
                    SELECT    Location.ID AS Location,
                              [Open].Count AS [Open],
                              [Closed].Count AS Closed
                    FROM      Loc AS Location
                              LEFT JOIN (
                                SELECT    Estimate.LocID AS Location,
                                          Count( Estimate.ID ) AS Count
                                FROM      Estimate
                                WHERE     Estimate.Status = 0
                                GROUP BY  Estimate.LocID
                              ) AS [Open] ON Location.ID = [Open].Location
                              LEFT JOIN (
                                SELECT    Estimate.LocID AS Location,
                                          Count( Estimate.ID ) AS Count
                                FROM      Estimate
                                WHERE     Estimate.Status = 1
                                GROUP BY  Estimate.LocID
                              ) AS [Closed] ON Location.ID = [Closed].Location
                  ) AS Proposals ON Proposals.Location = Location.Loc

            WHERE   	Location.Loc = ?
            		  OR 	Location.Tag = ?;",
        array(
        	$ID,
        	$Name
        )
    );
    var_dump(sqlsrv_errors ( ) );
    //var_dump( sqlsrv_errors( ) );
    $Location = in_array( $ID, array( null, 0, '', ' ' ) ) || !$result ? array(
    	'ID' => null,
    	'Name' => null,
    	'Status' => null,
    	'Street' => null,
    	'City' => null,
    	'State' => null,
    	'Zip' => null,
    	'Latitude' => null,
    	'Longitude' => null,
    	'Maintenance' => null,
    	'Geofence' => null,
    	'Customer_ID' => isset( $_GET[ 'Customer_ID' ] ) ? $_GET[ 'Customer_ID' ] : null,
    	'Customer_Name' => isset( $_GET[ 'Customer_Name' ] ) ? $_GET[ 'Customer_Name' ] : null,
    	'Division_ID' => isset( $_GET[ 'Division_ID' ] ) ? $_GET[ 'Division_ID' ] : null,
    	'Division_Name' => isset( $_GET[ 'Division' ] ) ? $_GET[ 'Division' ] : null,
    	'Route_ID' => isset( $_GET[ 'Route_ID' ] ) ? $_GET[ 'Route_ID' ] : null,
    	'Route_Name' => isset( $_GET[ 'Route_Name' ] ) ? $_GET[ 'Route_Name' ] : null,
    	'Territory_ID' => isset( $_GET[ 'Territory_ID' ] ) ? $_GET[ 'Territory_ID' ] : null,
    	'Territory_Name' => isset( $_GET[ 'Territory_Name' ] ) ? $_GET[ 'Territory_Name' ] : null,
    	'Sales_Tax' => isset( $_GET[ 'Sales_Tax' ] ) ? $_GET[ 'Sales_Tax' ] : null,
    	'In_Use' => isset( $_GET[ 'In_Use' ] ) ? $_GET[ 'In_Use' ] : null,
      'Violations_Preliminary_Report' => isset( $_GET[ 'Violations_Preliminary_Report' ] ) ? $_GET[ 'Violations_Preliminary_Report' ] : null,
      'Violations_Job_Created' => isset( $_GET[ 'Violations_Job_Created' ] ) ? $_GET[ 'Violations_Job_Created' ] : null,
      'Jobs_On_Hold' => isset( $_GET[ 'Jobs_On_Hold' ] ) ? $_GET[ 'Jobs_On_Hold' ] : null,
      'Jobs_Open' => isset( $_GET[ 'Jobs_Open' ] ) ? $_GET[ 'Jobs_Open' ] : null,
      'Jobs_Closed' => isset( $_GET[ 'Jobs_Closed' ] ) ? $_GET[ 'Jobs_Closed' ] : null,
      'Invoices_Open' => isset( $_GET[ 'Invoices_Open' ] ) ? $_GET[ 'Invoices_Open' ] : null,
      'Invoices_Closed' => isset( $_GET[ 'Invoices_Closed' ] ) ? $_GET[ 'Invoices_Closed' ] : null,
      'Tickets_Open' => isset( $_GET[ 'Tickets_Open' ] ) ? $_GET[ 'Tickets_Open' ] : null,
      'Tickets_Assigned' => isset( $_GET[ 'Tickets_Assigned' ] ) ? $_GET[ 'Tickets_Assigned' ] : null,
      'Tickets_En_Route' => isset( $_GET[ 'Tickets_En_Route' ] ) ? $_GET[ 'Tickets_En_Route' ] : null,
      'Tickets_On_Site' => isset( $_GET[ 'Tickets_On_Site' ] ) ? $_GET[ 'Tickets_On_Site' ] : null,
      'Tickets_Reviewing' => isset( $_GET[ 'Tickets_Reviewing' ] ) ? $_GET[ 'Tickets_Reviewing' ] : null,
      'Units_Elevators' => isset( $_GET[ 'Units_Elevators' ] ) ? $_GET[ 'Units_Elevators' ] : null,
      'Units_Escalators' => isset( $_GET[ 'Units_Escalators' ] ) ? $_GET[ 'Units_Escalators' ] : null,
      'Units_Other' => isset( $_GET[ 'Units_Other' ] ) ? $_GET[ 'Units_Other' ] : null,
      'Proposals_Open' => isset( $_GET[ 'Proposals_Open' ] ) ? $_GET[ 'Proposals_Open' ] : null,
      'Proposals_Closed' => isset( $_GET[ 'Proposals_Closed' ] ) ? $_GET[ 'Proposals_Closed' ] : null
    ) : sqlsrv_fetch_array( $result );


    if( isset( $_POST ) && count( $_POST ) > 0 ){
    	$Location[ 'Name' ] 	= isset( $_POST[ 'Name' ] ) 		? $_POST[ 'Name' ] 			: $Location[ 'Name' ];
    	$Location[ 'Status' ] 	= isset( $_POST[ 'Status' ] ) 		? $_POST[ 'Status' ] 		: $Location[ 'Status' ];
    	$Location[ 'Street' ] 	= isset( $_POST[ 'Street' ] ) 		? $_POST[ 'Street' ] 		: $Location[ 'Street' ];
    	$Location[ 'City' ] 	= isset( $_POST[ 'City' ] ) 		? $_POST[ 'City' ] 			: $Location[ 'City' ];
    	$Location[ 'State' ] 	= isset( $_POST[ 'State' ] ) 		? $_POST[ 'State' ] 		: $Location[ 'State' ];
    	$Location[ 'Zip' ] 		= isset( $_POST[ 'Zip' ] ) 			? $_POST[ 'Zip' ] 			: $Location[ 'Zip' ];
    	$Location[ 'Latitude'] 	= isset( $_POST[ 'Latitude' ] )		? $_POST[ 'Latitude' ]  	: $Location[ 'Latitude' ];
    	$Location[ 'Longitude'] = isset( $_POST[ 'Longitude' ] )	? $_POST[ 'Longitude' ] 	: $Location[ 'Longitude' ];
    	$Location[ 'Maintenance' ] = isset( $_POST[ 'Maintenance' ] ) ? $_POST[ 'Maintenance' ] : $Location[ 'Maintenance' ];
    	$Location[ 'Geofence' ] = isset( $_POST[ 'Geofence' ] ) ? $_POST[ 'Geofence' ] : $Location[ 'Geofence' ];
    	$Location[ 'Sales_Tax' ] = isset( $_POST[ 'Sales_Tax' ] ) ? $_POST[ 'Sales_Tax' ] : $Location[ 'Sales_Tax' ];
    	$Location[ 'In_Use' ] = isset( $_POST[ 'In_Use' ] ) ? $_POST[ 'In_Use' ] : $Location[ 'In_Use' ];
    	$Location[ 'Customer_ID' ] = isset( $_POST[ 'Customer_ID' ] ) ? $_POST[ 'Customer_ID' ] : $Location[ 'Customer_ID' ];
    	$Location[ 'Customer_Name' ] = isset( $_POST[ 'Customer_Name' ] ) ? $_POST[ 'Customer_Name' ] : $Location[ 'Customer_Name' ];

    	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
    		$result = \singleton\database::getInstance( )->query(
	    		null,
	    		"	DECLARE @MAXID INT;
        		SET @MAXID = CASE WHEN ( SELECT Max( Loc ) FROM dbo.Loc ) IS NULL THEN 0 ELSE ( SELECT Max( Loc ) FROM dbo.Loc ) END;
        		INSERT INTO dbo.Loc( Loc, Owner, Tag, Status, Address, City, State, Zip, Latt, fLong, Maint, Geolock, STax, InUse )
	    			VALUES( @MAXID + 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
        		SELECT @MAXID + 1;",
	    		array(
	    			$Location[ 'Customer_ID' ],
	    			$Location[ 'Name' ],
	    			$Location[ 'Status' ],
	    			$Location[ 'Street' ],
	    			$Location[ 'City' ],
	    			$Location[ 'State' ],
	    			$Location[ 'Zip' ],
	    			$Location[ 'Latitude' ],
	    			$Location[ 'Longitude' ],
	    			is_null( $Location[ 'Maintenance' ] ) ? 0 : $Location[ 'Maintenance' ],
	    			is_null( $Location[ 'Geofence' ] ) ? 0 : $Location[ 'Geofence' ],
	    			is_null( $Location[ 'Sales_Tax' ] ) ? 0 : $Location[ 'Sales_Tax' ],
	    			is_null( $Location[ 'In_Use' ] ) ? 0 : $Location[ 'In_Use' ]
	    		)
	    	);
        	$Location[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];

        	header( 'Location: location.php?ID=' . $Location[ 'ID' ] );
    	} else {
	    	\singleton\database::getInstance( )->query(
	    		null,
	    		"	UPDATE 	Loc
	    			SET 	Loc.Tag = ?,
	                Loc.Status = ?,
	      					Loc.Address = ?,
	      					Loc.City = ?,
	      					Loc.State = ?,
	      					Loc.Zip = ?,
	      					Loc.Latt = ?,
	      					Loc.fLong = ?,
	      					Loc.Maint = ?,
	      					Loc.Owner = (
	      						SELECT 	ID
	      						FROM 	(
                            SELECT  Owner.ID,
                                    Rol.Name,
                                    Owner.Status
                            FROM    Owner
                                    LEFT JOIN Rol ON Owner.Rol = Rol.ID
                          ) AS Customer
	      						WHERE 	Customer.Name = ?
	      					)
	    			WHERE 	Loc.Loc= ?;",
	    		array(
	    			$Location[ 'Name' ],
	          $Location[ 'Status' ],
	    			$Location[ 'Street' ],
	    			$Location[ 'City' ],
	    			$Location[ 'State' ],
	    			$Location[ 'Zip' ],
	    			$Location[ 'Latitude' ],
	    			$Location[ 'Longitude' ],
	    			$Location[ 'Maintenance' ],
	    			$Location[ 'Customer_Name' ],
	    			$Location[ 'ID' ]
	    		)
	    	);
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
    <?php require( bin_php . 'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary border-0'>
        <form action='location.php?ID=<?php echo $Location[ 'ID' ];?>' method='POST'>
          <input type='hidden' name='ID' value='<?php echo $Location[ 'ID' ];?>' />
          <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Location', 'Locations', $Location[ 'ID' ] );?>
  				<div class='card-body bg-dark text-white'>
            <div class='row g-0' data-masonry='{"percentPosition": true }'>
              <?php if( !in_array( $Location[ 'Latitude' ], array( null, 0 ) ) && !in_array( $Location['Longitude' ], array( null, 0 ) ) ){
                ?><div class='card card-primary my-3 col-12 col-lg-3'>
                  <?php \singleton\bootstrap::getInstance( )->card_header( 'Map' );?>
                  <div class='card-body bg-darker'>
                    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB05GymhObM_JJaRCC3F4WeFn3KxIOdwEU"></script>
                    <script type="text/javascript">
                      var map;
                      function initialize() {
                        map = new google.maps.Map(
                          document.getElementById( 'location_map' ),
                          {
                            zoom: 10,
                            center: new google.maps.LatLng( <?php echo $Location[ 'Latitude' ];?>, <?php echo $Location[ 'Longitude' ];?> ),
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                          }
                        );
                        var markers = [];
                        markers[0] = new google.maps.Marker({
                          position: {
                            lat:<?php echo $Location['Latitude'];?>,
                            lng:<?php echo $Location['Longitude'];?>
                          },
                          map: map,
                          title: '<?php echo $Location[ 'Name' ];?>'
                        });
                      }
                      $(document).ready(function(){ initialize(); });
                    </script>
                    <div class='card-body'>
                      <div id='location_map' class='map'>&nbsp;</div>
                    </div>
                  </div>
                </div>
              <?php }?>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php
                    \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', $Location[ 'Name' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Customer', 'Customers', $Location[ 'Customer_ID' ], $Location[ 'Customer_Name' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_select( 'Status', $Location[ 'Status' ], array( 0 => 'Disabled', 1 => 'Enabled' ) );
                    \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Territory', 'Territories', $Location[ 'Territory_ID' ], $Location[ 'Territory_Name' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Address', 'https://maps.google.com/?q=' . $Location['Street'].' '.$Location['City'].' '.$Location[ 'State' ].' '.$Location[ 'Zip' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Street', $Location[ 'Street' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'City', $Location[ 'City' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_select_sub( 'State', $Location[ 'State' ],  array( 'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming' ) );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Zip', $Location[ 'Zip' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub_number( 'Latitude',  $Location[ 'Latitude' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub_number( 'Longitude',  $Location[ 'Longitude' ] );
                  ?>
                </div>
  					 </div>
  					 <div class='card card-primary my-3 col-12 col-lg-3'>
              <?php \singleton\bootstrap::getInstance( )->card_header( 'Maintenance' );?>
  						<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
  							<?php
                  \singleton\bootstrap::getInstance( )->card_row_form_select( 'Maintenance', $Location[ 'Maintenance' ], array(
                      0 => 'Disabled',
                      1 => 'Enabled'
                  ) );
                  \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Division', 'Divisions', $Location[ 'Division_ID' ], $Location[ 'Division_Name' ] );
                  \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Route', 'Routes', $Location[ 'Route_ID' ], $Location[ 'Route_Name' ] );
                ?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Location', $Location[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Location=' . $Location[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Location[ 'Tickets_Open' ], true, true, 'tickets.php?Location=' . $Location[ 'ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Location[ 'Tickets_Assigned' ], true, true, 'tickets.php?Location=' . $Location[ 'ID' ] ) . '&Status=1';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En Route', $Location[ 'Tickets_En_Route' ], true, true, 'tickets.php?Location=' . $Location[ 'ID' ] ) . '&Status=2';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Site', $Location[ 'Tickets_On_Site' ], true, true, 'tickets.php?Location=' . $Location[ 'ID' ] ) . '&Status=3';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Location[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Location=' . $Location[ 'ID' ] ) . '&Status=6';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Units', 'Unit', 'Units', 'Location', $Location[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'units.php?Location=' . $Location[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Elevators', $Location[ 'Units_Elevators' ], true, true, 'units.php?Location=' . $Location[ 'ID' ] . '&Type=Elevator');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Escalators', $Location[ 'Units_Escalators' ], true, true, 'units.php?Location=' . $Location[ 'ID' ] ) . '&Type=Escalator';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Escalators', $Location[ 'Units_Other' ], true, true, 'units.php?Location=' . $Location[ 'ID' ] ) . '&Type=Other';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Jobs', 'Job', 'Jobs', 'Location', $Location[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Jobs' ] ) && $_SESSION[ 'Cards' ][ 'Jobs' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'jobs.php?Location=' . $Location[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Location[ 'Jobs_Open' ], true, true, 'jobs.php?Location=' . $Location[ 'ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Hold', $Location[ 'Jobs_On_Hold' ], true, true, 'jobs.php?Location=' . $Location[ 'ID' ] ) . '&Status=2';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Location[ 'Jobs_Closed' ], true, true, 'jobs.php?Location=' . $Location[ 'ID' ] ) . '&Status=1';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Violations', 'Violations', 'Violations', 'Location', $Location[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Violations' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'violations.php?Location=' . $Location[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Preliminary', $Location[ 'Violations_Preliminary_Report' ], true, true, 'violations.php?Location=' . $Location[ 'ID' ] . '&Status=Preliminary Report');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Ongoing', $Location[ 'Violations_Job_Created' ], true, true, 'violations.php?Location=' . $Location[ 'ID' ] ) . '&Status=Job Created';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Proposals', 'Proposal', 'Proposals', 'Location', $Location[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'proposals.php?Location=' . $Location[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Location[ 'Proposals_Open' ], true, true, 'proposals.php?Location=' . $Location[ 'ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Location[ 'Proposals_Closed' ], true, true, 'proposals.php?Location=' . $Location[ 'ID' ] ) . '&Status=1';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Invoices', 'Invoice', 'Invoices', 'Location', $Location[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'invoices.php?Location=' . $Location[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Location[ 'Invoices_Open' ], true, true, 'invoices.php?Location=' . $Location[ 'ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Location[ 'Invoices_Closed' ], true, true, 'invoices.php?Location=' . $Location[ 'ID' ] ) . '&Status=1';?>
                </div>
              </div>
            </div>
          </div>
  			</form>
      </div>
  	</div>
  </div>
</div>
</body>
</html><?php }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
