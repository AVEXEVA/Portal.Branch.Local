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
    ){
        //If privleges dont check, 404s out
        ?><?php require('404.html');?><?php
    } else {
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
        // sets $ID, $Name Variable and Posts ID and Name into $result
        $result = \singleton\database::getInstance( )->query(
        	null,
            "	SELECT 	Customer.ID        AS ID,
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
                        CASE    WHEN Units.Moving_Walk IS NULL THEN 0
                                ELSE Units.Moving_Walk END AS Units_Moving_Walk,
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
                        CASE    WHEN Violations.Closed IS NULL THEN 0
                                ELSE Violations.Closed END AS Violations_Closed,
                        CASE    WHEN Invoices.[Open] IS NULL THEN 0
                                ELSE Invoices.[Open] END AS Invoices_Open,
                        CASE    WHEN Invoices.[Closed] IS NULL THEN 0
                                ELSE Invoices.[Closed] END AS Invoices_Closed,
                        CASE    WHEN Proposals.[Open] IS NULL THEN 0
                                ELSE Proposals.[Open] END AS Proposals_Open,
                        CASE    WHEN Proposals.[Closed] IS NULL THEN 0
                                ELSE Proposals.[Closed] END AS Proposals_Closed
				FROM    Owner AS Customer
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
                                        ) AS [Maintained] ON Location.Loc = Maintained.Location
                                        LEFT JOIN (
                                            SELECT      Location.Loc AS Location,
                                                        Count( Location.Loc ) AS Count
                                            FROM        Loc AS Location
                                            WHERE       Location.Maint = 0
                                            GROUP BY    Location.Loc
                                        ) AS [Unmaintained] ON Location.Loc = Unmaintained.Location
                            GROUP BY    Owner.ID
                        ) AS Locations ON Locations.Customer = Customer.ID
                        LEFT JOIN (
                            SELECT      Owner.ID AS Customer,
                                        Sum( Units.Count ) AS Count,
                                        Sum( Elevators.Count) AS Elevators,
                                        Sum( Escalators.Count ) AS Escalators,
                                        SUM( Moving_Walk.Count ) AS Moving_Walk,
                                        Sum( Other.Count ) AS Other
                            FROM        Owner
                                        LEFT JOIN Loc AS Location ON Owner.ID = Location.Owner
                                        LEFT JOIN (
                                            SELECT      Unit.Loc AS Location,
                                                        Count( Unit.ID ) AS Count
                                            FROM        Elev AS Unit
                                            GROUP BY    Unit.Loc
                                        ) AS [Units] ON Units.Location = Location.Loc
                                        LEFT JOIN (
                                            SELECT      Unit.Loc AS Location,
                                                        Count( Unit.ID ) AS Count
                                            FROM        Elev AS Unit
                                            WHERE       Unit.Type IN ( 'Elevator', 'Roped Hydro', 'Hydraulic' )
                                            GROUP BY    Unit.Loc
                                        ) AS [Elevators] ON Elevators.Location = Location.Loc
                                        LEFT JOIN (
                                            SELECT      Unit.Loc AS Location,
                                                        Count( Unit.ID ) AS Count
                                            FROM        Elev AS Unit
                                            WHERE       Unit.Type = 'Escalator'
                                            GROUP BY    Unit.Loc
                                        ) AS [Escalators] ON Escalators.Location = Location.Loc
                                        LEFT JOIN (
                                            SELECT      Unit.Loc AS Location,
                                                        Count( Unit.ID ) AS Count
                                            FROM        Elev AS Unit
                                            WHERE       Unit.Type = 'Moving Walk'
                                            GROUP BY    Unit.Loc
                                        ) AS [Moving_Walk] ON Escalators.Location = Location.Loc
                                        LEFT JOIN (
                                            SELECT      Unit.Loc AS Location,
                                                        Count( Unit.ID ) AS Count
                                            FROM        Elev AS Unit
                                            WHERE       Unit.Type NOT IN ( 'Elevator', 'Escalator' )
                                            GROUP BY    Unit.Loc
                                        ) AS [Other] ON Other.Location = Location.Loc
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
                                    ) AS [Unassigned] ON Unassigned.Customer = Owner.ID
                                    LEFT JOIN (
                                      SELECT    Location.Owner AS Customer,
                                                Count( TicketO.ID ) AS Count
                                      FROM      TicketO
                                                LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                      WHERE     TicketO.Assigned = 1
                                      GROUP BY  Location.Owner
                                    ) AS [Assigned] ON Assigned.Customer = Owner.ID
                                    LEFT JOIN (
                                      SELECT    Location.Owner AS Customer,
                                                Count( TicketO.ID ) AS Count
                                      FROM      TicketO
                                                LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                      WHERE     TicketO.Assigned = 2
                                      GROUP BY  Location.Owner
                                    ) AS [En_Route] ON En_Route.Customer = Owner.ID
                                    LEFT JOIN (
                                      SELECT    Location.Owner AS Customer,
                                                Count( TicketO.ID ) AS Count
                                      FROM      TicketO
                                                LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                      WHERE     TicketO.Assigned = 3
                                      GROUP BY  Location.Owner
                                    ) AS [On_Site] ON On_Site.Customer = Owner.ID
                                    LEFT JOIN (
                                      SELECT    Location.Owner AS Customer,
                                                Count( TicketO.ID ) AS Count
                                      FROM      TicketO
                                                LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                      WHERE     TicketO.Assigned = 6
                                      GROUP BY  Location.Owner
                                    ) AS [Reviewing] ON Reviewing.Customer = Owner.ID
                        ) AS Tickets ON Tickets.Customer = Customer.ID
                        LEFT JOIN (
                            SELECT  Owner.ID AS Customer,
                                    Preliminary.Count AS Preliminary,
                                    Job_Created.Count AS Job_Created,
                                    Closed.Count AS Closed
                            FROM    Owner
                                    LEFT JOIN (
                                      SELECT    Location.Owner AS Customer,
                                                Count( Violation.ID ) AS Count
                                      FROM      Violation
                                                LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                      WHERE     Violation.Status = 'Preliminary Report'
                                      GROUP BY  Location.Owner
                                    ) AS [Preliminary] ON Preliminary.Customer = Owner.ID
                                    LEFT JOIN (
                                      SELECT    Location.Owner AS Customer,
                                                Count( Violation.ID ) AS Count
                                      FROM      Violation
                                                LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                      WHERE     Violation.Status = 'Job Created'
                                      GROUP BY  Location.Owner
                                    ) AS [Job_Created] ON Job_Created.Customer = Owner.ID
                                    LEFT JOIN (
                                        SELECT  Location.Owner AS Customer,
                                                Count( Violation.ID ) AS Count
                                        FROM    Violation
                                                LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                        WHERE   Violation.Status IN ( 'Completed', 'Dismissed' )
                                        GROUP BY    Location.Owner
                                    ) AS [Closed] ON Closed.Customer = Owner.ID
                        ) AS Violations ON Violations.Customer = Customer.ID
                        LEFT JOIN (
                            SELECT      Location.Owner          AS Customer,
                                        Sum( [Open].Count )     AS [Open],
                                        Sum( [Closed].Count )   AS Closed
                            FROM        Loc AS Location
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
                            GROUP BY    Location.Owner
                        ) AS Invoices ON Invoices.Customer = Customer.ID
                        LEFT JOIN (
                            SELECT      Location.Owner        AS Customer,
                                        Sum( [Open].Count )   AS [Open],
                                        Sum( [Closed].Count ) AS Closed
                            FROM        Loc AS Location
                                        LEFT JOIN (
                                          SELECT    Estimate.LocID AS Location,
                                                    Count( Estimate.ID ) AS Count
                                          FROM      Estimate
                                          WHERE     Estimate.Status = 0
                                          GROUP BY  Estimate.LocID
                                        ) AS [Open] ON Location.Loc = [Open].Location
                                        LEFT JOIN (
                                          SELECT    Estimate.LocID AS Location,
                                                    Count( Estimate.ID ) AS Count
                                          FROM      Estimate
                                          WHERE     Estimate.Status = 1
                                          GROUP BY  Estimate.LocID
                                        ) AS [Closed] ON Location.Loc = [Closed].Location
                            GROUP BY    Location.Owner
                        ) AS Proposals ON Proposals.Customer = Customer.ID
                WHERE   Customer.ID = ?;",
            array(
            	$ID
            )
        );
        $Customer = (  !$result )
            ? array(
            	'ID'        => isset( $_GET [ 'ID' ] )         ? $_GET ['ID'] : null,
            	'Name'      => isset( $_GET [ 'Name' ] )       ? $_GET ['Name'] : null,
            	'Login'     => isset( $_GET [ 'Login' ] )      ? $_GET ['Login'] : null,
            	'Password'  => isset( $_GET [ 'Password' ] )   ? $_GET ['Password'] : null,
            	'Geofence'  => isset( $_GET [ 'Geofence' ] )   ? $_GET ['Geofence'] : null,
            	'Type'      => isset( $_GET [ 'Type' ] )       ? $_GET ['Type'] : null,
            	'Status'    => isset( $_GET [ 'Status' ] )     ? $_GET ['Status'] : null,
            	'Website'   => isset( $_GET [ 'Website' ] )    ? $_GET ['Website'] : null,
            	'Internet'  => isset( $_GET [ 'Internet' ] )   ? $_GET ['Internet'] : null,
            	'Street'    => isset( $_GET [ 'Street' ] )     ? $_GET ['Street'] : null,
            	'City'      => isset( $_GET [ 'City' ] )       ? $_GET ['City'] : null,
            	'State'     => isset( $_GET [ 'State' ] )      ? $_GET ['State'] : null,
            	'Zip'       => isset( $_GET [ 'Zip' ] )        ? $_GET ['Zip'] : null,
            	'Latitude'  => isset( $_GET [ 'Latitude' ] )   ? $_GET ['Latitude'] : null,
            	'Longitude' => isset( $_GET [ 'Longitude' ] )  ? $_GET ['Longitude'] : null,
                'Phone'     => isset( $_GET [ 'Phone' ] )      ? $_GET ['Phone'] : null,
                'Email'     => isset( $_GET [ 'Email' ] )      ? $_GET ['Email'] : null,
                'Rolodex'   => isset( $_GET [ 'Rolodex' ] )    ? $_GET ['Rolodex'] : null,
                'Phone'     => isset( $_GET [ 'Phone' ] )      ? $_GET ['Phone'] : null,
                'Email'     => isset( $_GET [ 'Email' ] )      ? $_GET ['Email'] : null,
                'Contact'   => isset( $_GET [ 'Contact' ] )    ? $_GET [ 'Contact' ] : null,
                'Locations_Count ' => null,
                'Locations_Maintained' => null,
                'Locations_Unmaintained' => null,
                'Units_Count' => null,
                'Units_Elevators' => null,
                'Units_Escalators' => null,
                'Units_Moving_Walk' => null,
                'Units_Other' => null,
                'Jobs_Open' => null,
                'Jobs_On_Hold' => null,
                'Jobs_Closed' => null,
                'Tickets_Open' => null,
                'Tickets_Assigned' => null,
                'Tickets_En_Route' => null,
                'Tickets_On_Site' => null,
                'Tickets_Reviewing' => null,
                'Violations_Preliminary_Report' => null,
                'Violations_Job_Created' => null,
                'Violations_Closed' => null,
                'Invoices_Open' => null,
                'Invoices_Closed' => null,
                'Proposals_Open' => null,
                'Proposals_Closed' => null
            )
            : sqlsrv_fetch_array($result);
        //Binds $ID, $Name, $Customer and query values into the $result variable

        if( isset( $_POST ) && count( $_POST ) > 0 ){
            // if the $_Post is set and the count is null, select if available
        	$Customer[ 'Name' ] 		    = isset( $_POST[ 'Name' ] ) 	   ? $_POST[ 'Name' ] 	   : $Customer[ 'Name' ];
          $Customer[ 'Contact' ] 	    = isset( $_POST[ 'Contact' ] )   ? $_POST[ 'Contact' ]   : $Customer[ 'Contact' ];
        	$Customer[ 'Phone' ] 		    = isset( $_POST[ 'Phone' ] ) 	   ? $_POST[ 'Phone' ] 	   : $Customer[ 'Phone' ];
        	$Customer[ 'Email' ] 		    = isset( $_POST[ 'Email' ] ) 	   ? $_POST[ 'Email' ] 	   : $Customer[ 'Email' ];
        	$Customer[ 'Login' ] 		    = isset( $_POST[ 'Login' ] ) 	   ? $_POST[ 'Login' ] 	   : $Customer[ 'Login' ];
        	$Customer[ 'Password' ]     = isset( $_POST[ 'Password' ] )  ? $_POST[ 'Password' ]  : $Customer[ 'Password' ];
        	$Customer[ 'Geofence' ]     = isset( $_POST[ 'Geofence' ] )  ? $_POST[ 'Geofence' ]  : $Customer[ 'Geofence' ];
        	$Customer[ 'Type' ]         = isset( $_POST[ 'Type' ] ) 	   ? $_POST[ 'Type' ] 	   : $Customer[ 'Type' ];
        	$Customer[ 'Status' ] 	    = isset( $_POST[ 'Status' ] ) 	 ? $_POST[ 'Status' ] 	 : $Customer[ 'Status' ];
        	$Customer[ 'Website' ] 	    = isset( $_POST[ 'Website' ] ) 	 ? $_POST[ 'Website' ] 	 : $Customer[ 'Website' ];
        	$Customer[ 'Internet' ]     = isset( $_POST[ 'Internet' ] )  ? $_POST[ 'Internet' ]  : $Customer[ 'Internet' ];
        	$Customer[ 'Street' ] 	    = isset( $_POST[ 'Street' ] ) 	 ? $_POST[ 'Street' ] 	 : $Customer[ 'Street' ];
        	$Customer[ 'City' ] 		    = isset( $_POST[ 'City' ] ) 	   ? $_POST[ 'City' ] 	   : $Customer[ 'City' ];
        	$Customer[ 'State' ] 		    = isset( $_POST[ 'State' ] ) 	   ? $_POST[ 'State' ] 	   : $Customer[ 'State' ];
        	$Customer[ 'Zip' ] 			    = isset( $_POST[ 'Zip' ] ) 		   ? $_POST[ 'Zip' ] 		   : $Customer[ 'Zip' ];
        	$Customer[ 'Latitude' ]    	= isset( $_POST[ 'Latitude' ] )  ? $_POST[ 'Latitude' ]  : $Customer[ 'Latitude' ];
        	$Customer[ 'Longitude' ] 	  = isset( $_POST[ 'Longitude' ] ) ? $_POST[ 'Longitude' ] : $Customer[ 'Longitude' ];

        	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
        		$result = \singleton\database::getInstance( )->query(
        			null,
        			"	DECLARE @MAXID INT;
        				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Rol ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Rol ) END ;
        				INSERT INTO Rol(
    						  ID,
        					Type,
        					Name,
                  Contact,
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
                $Customer[ 'Contact' ],
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
	        			SET     Owner.Status = ?,
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
                      Rol.Contact = ?,
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
                $Customer[ 'Contact' ],
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
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php
        $_GET[ 'Bootstrap' ] = '5.1';
        $_GET[ 'Entity_CSS' ] = 1;
        require( bin_meta . 'index.php');
        require( bin_css  . 'index.php');
        require( bin_js   . 'index.php');
    ?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<!-- required files from other locations, such as css, js, bootstrap and, Entity files  -->
<body>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php'); ?>
        <div id="page-wrapper" class='content'>
        	<div class='card card-primary'>
                <form action='customer.php?ID=<?php echo $Customer[ 'ID' ];?>' method='POST'>
                    <input type='hidden' name='ID' value='<?php echo $Customer[ 'ID' ];?>' />
                    <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Customer', 'Customers', $Customer[ 'ID' ] );?>
                    <div class='card-body bg-dark text-white'>
                        <div class='row g-0' data-masonry='{"percentPosition": true }'>
                            <?php \singleton\bootstrap::getInstance( )->card_map( 'customer_map', $Customer[ 'Name' ], $Customer[ 'Latitude' ], $Customer[ 'Longitude' ] );?>
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
                                <!-- Card hedding, that holds customer contacts, with a post call that gets customer contact information based on $Customer ID  -->
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Contacts', 'Contact', 'Contacts', 'Customer', $Customer[ 'ID' ] );?>
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
                                            class='form-control'
                                            name='Internet' >
                                            <option value=''>Select</option>
                                            <option value='0' <?php echo $Customer[ 'Internet' ] == 0 ? 'selected' : null;?>>Disabled</option>
                                            <option value='1' <?php echo $Customer[ 'Internet' ] == 1 ? 'selected' : null;?>>Enabled</option>
                                        </select></div>
                                    </div>
                                    <div class='row g-0' <?php echo $Customer[ 'Internet' ] == 0 ? "style='display:none;'" : null;?>>
                                        <div class='col-1'>&nbsp;</div>
                                        <div class='col-3'>Username:</div>
                                        <div class='col-8'><input type='text' class='form-control' name='Login' value='<?php echo $Customer[ 'Login' ];?>' /></div>
                                    </div>
                                    <div class='row g-0' <?php echo $Customer[ 'Internet' ] == 0 ? "style='display:none;'" : null;?>>
                                        <div class='col-1'>&nbsp;</div>
                                        <div class='col-3'>Password:</div>
                                        <div class='col-8'><input type='password' class='form-control' name='Login' value='<?php echo $Customer[ 'Login' ];?>' name='Password' value='<?php echo $Customer[ 'Password' ];?>' /></div>
                                    </div>
                                    <div class='row g-0'>
                                        <div class='col-1'>&nbsp;</div>
                                        <div class='col-3'>Geofence:</div>
                                        <div class='col-8'>
                                            <select
                                                <?php echo check( privilege_execute, level_server, isset( $Privileges[ 'Customer' ] ) ? $Privileges[ 'Customer' ] : 0 ) ? null : 'disabled';?>
                                                class='form-control'
                                                name='Geofence' >
                                                  <option value=''>Select</option>
                                                  <option value='0' <?php echo $Customer[ 'Geofence' ] == 0 ? 'selected' : null;?>>Disabled</option>
                                                  <option value='1' <?php echo $Customer[ 'Geofence' ] == 1 ? 'selected' : null;?>>Enabled</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Locations', 'Location', 'Locations', 'Customer', $Customer[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Locations' ] ) && $_SESSION[ 'Cards' ][ 'Locations' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Locations', 'locations.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ]);?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Maintain', $Customer[ 'Locations_Maintained' ], true, true, 'locations.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Maintained=1');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Customer[ 'Locations_Unmaintained' ], true, true, 'locations.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Maintained=0' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Units', 'Unit', 'Units', 'Customer', $Customer[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'units.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Elevators', $Customer[ 'Units_Elevators' ], true, true, 'units.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Type=Elevator');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Escalators', $Customer[ 'Units_Escalators' ], true, true, 'units.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Type=Escalator' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Escalators', $Customer[ 'Units_Other' ], true, true, 'units.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Type=Other' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Jobs', 'Job', 'Jobs', 'Customer', $Customer[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Jobs' ] ) && $_SESSION[ 'Cards' ][ 'Jobs' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'jobs.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Customer[ 'Jobs_Open' ], true, true, 'jobs.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=0');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Hold', $Customer[ 'Jobs_On_Hold' ], true, true, 'jobs.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=2' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Customer[ 'Jobs_Closed' ], true, true, 'jobs.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=1' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Customer', $Customer[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Customer[ 'Tickets_Open' ], true, true, 'tickets.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=0');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Customer[ 'Tickets_Assigned' ], true, true, 'tickets.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=1' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En Route', $Customer[ 'Tickets_En_Route' ], true, true, 'tickets.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=2' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Site', $Customer[ 'Tickets_On_Site' ], true, true, 'tickets.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=3' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Customer[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=6' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Violations', 'Violations', 'Violations', 'Customer', $Customer[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Violations' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'violations.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Preliminary', $Customer[ 'Violations_Preliminary_Report' ], true, true, 'violations.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=Preliminary Report');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Ongoing', $Customer[ 'Violations_Job_Created' ], true, true, 'violations.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=Job Created' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Proposals', 'Proposal', 'Proposals', 'Customer', $Customer[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'proposals.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Customer[ 'Proposals_Open' ], true, true, 'proposals.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=0');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Customer[ 'Proposals_Closed' ], true, true, 'proposals.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=1' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Invoices', 'Invoice', 'Invoices', 'Customer', $Customer[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'invoices.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Customer[ 'Invoices_Open' ], true, true, 'invoices.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=0');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Customer[ 'Invoices_Closed' ], true, true, 'invoices.php?Customer_ID=' . $Customer[ 'ID' ] . '&Customer_Name=' . $Customer[ 'Name' ] . '&Status=1' );?>
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
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($Customer[ 'ID' ]) || !is_numeric($Customer[ 'ID' ])) ? "s.php" : ".php?ID={$Customer[ 'ID' ]}";?>";</script></head></html><?php }?>
