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
        ||  !isset( $Privileges[ 'Territory' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Territory' ] )
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
                'territory.php'
            )
        );
        // If privleges check, Timestamp $_SESSION user and show territory.php
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
        " SELECT    Territory.ID            AS ID,
                    Territory.Name          AS Name,
                    Territory.SMan          AS SMAN,
                    Territory.SDesc         AS SDesc,
                    Territory.Remarks       AS Remarks,
                    Territory.Count         AS Count,
                    Territory.Symbol        AS Symbol,
                    Territory.EN            AS EN,
                    Territory.Address       AS Address,
                    Territory.TFMID         AS TFMID,
                    Territory.TFMSource     AS TFMSource,
                    Employee.ID             AS Employee_ID,
                    Employee.fFirst         AS Employee_First,
                    Employee.Last           AS Employee_Last,
                    Rolodex.Website         AS Website,
                    Rolodex.City            AS City,
                    Rolodex.State           AS State,
                    Rolodex.Zip             AS Zip,
                    Rolodex.Contact         AS Contact,
                    Rolodex.Phone           AS Phone,
                    Rolodex.Email           AS Email,
                    CASE    WHEN Units.Count IS NULL THEN 0
                            ELSE Units.Count END AS Units_Count,
                    CASE    WHEN Units.Elevators IS NULL THEN 0
                            ELSE Units.Elevators END AS Units_Elevators,
                    CASE    WHEN Units.Escalators IS NULL THEN 0
                            ELSE Units.Escalators END AS Units_Escalators,
                    CASE    WHEN Units.Moving_Walks IS NULL THEN 0
                            ELSE Units.Moving_Walks END AS Units_Moving_Walks,
                    CASE    WHEN Units.Others IS NULL THEN 0
                            ELSE Units.Others END AS Units_Others,
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
                    FROM    Terr  AS Territory
                    LEFT JOIN Emp AS Employee ON Territory.SMan = Employee.ID
                    LEFT JOIN Rol AS Rolodex  ON Employee.Rol   = Rolodex.ID
                    LEFT JOIN (
                      SELECT    Location.Terr               AS Territory,
                                Count( Location.Loc )       AS Location,
                                Sum(   Contacts.Count )     AS Contacts,
                                Sum(   Units.Count )        AS  Units,
                                Sum(   Elevators.Count )    AS  Elevators,
                                Sum(   Escalators.Count )   AS  Escalators,
                                Sum(   Moving_Walk.Count )  AS  Moving_Walks,
                                Sum(   Others.Count )       AS  Others
                      FROM      Loc AS Location
                                LEFT JOIN (
                                  SELECT    Rolodex.Name AS Name,
                                            Count(Rolodex.ID) AS Count
                                  FROM      Rol AS Rolodex
                                  GROUP BY  Rolodex.Name
                                ) AS Contacts ON  Contacts.Name = Location.Tag
                                LEFT JOIN (
                                  SELECT    Elev.Loc AS Location,
                                            Count( Elev.ID )  AS Count
                                  FROM      Elev
                                  GROUP BY  Elev.Loc
                                ) AS Units ON Location.Loc = Units.Location
                                LEFT JOIN (
                                  SELECT    Elev.Loc AS Location,
                                            Count( Elev.ID )  AS Count
                                  FROM      Elev
                                  WHERE     Elev.Type = 'Elevator'
                                  GROUP BY  Elev.Loc
                                ) AS Elevators ON Location.Loc = Elevators.Location
                                LEFT JOIN (
                                  SELECT    Elev.Loc AS Location,
                                            Count( Elev.ID )  AS Count
                                  FROM      Elev
                                  WHERE     Elev.Type = 'Escalator'
                                  GROUP BY  Elev.Loc
                                ) AS Escalators ON Location.Loc = Escalators.Location
                                LEFT JOIN (
                                  SELECT    Elev.Loc AS Location,
                                            Count( Elev.ID )  AS Count
                                  FROM      Elev
                                  WHERE     Elev.Type = 'Moving Walk'
                                  GROUP BY  Elev.Loc
                                ) AS Moving_Walks ON Location.Loc = Moving_Walks.Location
                                LEFT JOIN (
                                  SELECT    Elev.Loc AS Location,
                                            Count( Elev.ID )  AS Count
                                  FROM      Elev
                                  WHERE     Elev.Type NOT IN ( 'Elevator', 'Escalator', 'Moving Walk' )
                                  GROUP BY  Elev.Loc
                                ) AS Others ON Location.Loc = Others.Location
                      GROUP BY  Location.Terr
                    ) AS  Locations ON Locations.Territory = Territory.ID
                  LEFT JOIN (
                      SELECT      Location.Terr        AS Territory,
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
                  LEFT JOIN (
                      SELECT      Location.Terr          AS Territory,
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
                            SELECT  Location.Terr     AS Territory,
                                    Preliminary.Count AS Preliminary,
                                    Job_Created.Count AS Job_Created,
                                    Closed.Count AS Closed
                            FROM    Owner
                                    LEFT JOIN (
                                      SELECT    Location.Loc AS Territory,
                                                Count( Violation.ID ) AS Count
                                      FROM      Violation
                                                LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                      WHERE     Violation.Status = 'Preliminary Report'
                                      GROUP BY  Location.Owner
                                    ) AS [Preliminary] ON Preliminary.Customer = Owner.ID
                                    LEFT JOIN (
                                      SELECT    Location.Loc AS Territory,
                                                Count( Violation.ID ) AS Count
                                      FROM      Violation
                                                LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                      WHERE     Violation.Status = 'Job Created'
                                      GROUP BY  Location.Owner
                                    ) AS [Job_Created] ON Job_Created.Customer = Owner.ID
                                    LEFT JOIN (
                                        SELECT  Location.Loc AS Terr,
                                                Count( Violation.ID ) AS Count
                                        FROM    Violation
                                                LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                                        WHERE   Violation.Status IN ( 'Completed', 'Dismissed' )
                                        GROUP BY    Location.Owner
                                    ) AS [Closed] ON Closed.Customer = Owner.ID
                        ) AS Violations ON Violations.Customer = Customer.ID
                          LEFT JOIN (
                              SELECT  Location.Loc        AS Territory,
                                      Unassigned.Count    AS Unassigned,
                                      Assigned.Count      AS Assigned,
                                      En_Route.Count      AS En_Route,
                                      On_Site.Count       AS On_Site,
                                      Reviewing.Count     AS Reviewing
                              FROM    Owner
                                      LEFT JOIN (
                                        SELECT    Location.Terr AS Location,
                                                  Count( TicketO.ID ) AS Count
                                        FROM      TicketO
                                                  LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                        WHERE     TicketO.Assigned = 0
                                        GROUP BY  Location.Terr
                                      ) AS [Unassigned] ON Unassigned.Customer = Owner.ID
                                      LEFT JOIN (
                                        SELECT    Location.Terr AS Location,
                                                  Count( TicketO.ID ) AS Count
                                        FROM      TicketO
                                                  LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                        WHERE     TicketO.Assigned = 1
                                        GROUP BY  Location.Terr
                                      ) AS [Assigned] ON Assigned.Customer = Owner.ID
                                      LEFT JOIN (
                                        SELECT    Location.Terr AS Location,
                                                  Count( TicketO.ID ) AS Count
                                        FROM      TicketO
                                                  LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                        WHERE     TicketO.Assigned = 2
                                        GROUP BY  Location.Terr
                                      ) AS [En_Route] ON En_Route.Customer = Owner.ID
                                      LEFT JOIN (
                                        SELECT    Location.Terr AS Location,
                                                  Count( TicketO.ID ) AS Count
                                        FROM      TicketO
                                                  LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                        WHERE     TicketO.Assigned = 3
                                        GROUP BY  Location.Terr
                                      ) AS [On_Site] ON On_Site.Customer = Owner.ID
                                      LEFT JOIN (
                                        SELECT    Location.Terr AS Location,
                                                  Count( TicketO.ID ) AS Count
                                        FROM      TicketO
                                                  LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                        WHERE     TicketO.Assigned = 6
                                        GROUP BY  Location.Terr
                                      ) AS [Reviewing] ON Reviewing.Customer = Owner.ID
                          ) AS Tickets ON Tickets.Customer = Customer.ID
                            LEFT JOIN (
                                SELECT  Location.Terr   AS Territory,
                                        [Open].Count    AS [Open],
                                        [On_Hold].Count AS On_Hold,
                                        [Closed].Count  AS Closed
                                FROM    Owner
                                    LEFT JOIN (
                                        SELECT      Job.Location AS Location,
                                                    Count( Job.ID ) AS Count
                                        FROM        Job
                                        WHERE       Job.Status = 0
                                        GROUP BY    Job.Owner
                                    ) AS [Open] ON [Open].Customer = Owner.ID
                                    LEFT JOIN (
                                        SELECT      Job.Location AS Location,
                                                    Count( Job.ID ) AS Count
                                        FROM        Job
                                        WHERE       Job.Status = 2
                                        GROUP BY    Job.Owner
                                    ) AS [On_Hold] ON [On_Hold].Customer = Owner.ID
                                    LEFT JOIN (
                                        SELECT      Job.Location AS Location,
                                                    Count( Job.ID ) AS Count
                                        FROM        Job
                                        WHERE       Job.Status = 1
                                        GROUP BY    Job.Owner
                                    ) AS [Closed] ON [Closed].Customer = Owner.ID
                            ) AS Jobs ON Jobs.Customer = Customer.ID
                          LEFT JOIN (
                                        SELECT      Location.Terr AS Territory,
                                                    Sum( Maintained.Count ) AS Maintained,
                                                    Sum( Unmaintained.Count ) AS Unmaintained,
                                                    Count( Location.Loc ) AS Count
                                        FROM        Location
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
                      WHERE       Territory.ID = ?;",
        array(
          $ID
        )
    );
    var_dump(sqlsrv_errors ( ) );
    $Territory = in_array( $ID, array( null, 0, '', ' ' ) ) || !$result ? array(
      'ID' => null,
      'Name' => null,
      'SMAN' => null,
      'SDesc' => null,
      'Remarks' => null,
      'Count' => null,
      'Symbol' => null,
      'EN' => null,
      'Website' => null,
      'City' => null,
      'State' => null,
      'Zip' => null,
      'Contact' => null,
      'Phone' => null,
      'Email' => null,
      'Maintenance' => null,
      'TFMID' =>  isset( $_GET[ 'TFMID' ] ) ? $_GET[ 'TFMID' ] : null,
      'TFMSource' => isset( $_GET[  'TFMSource' ] ) ? $_GET[  'TFMSource'] :  null,
      'Customer_ID' => isset( $_GET[ 'Customer_ID' ] ) ? $_GET[ 'Customer_ID' ] : null,
      'Customer_Name' => isset( $_GET[ 'Customer_Name' ] ) ? $_GET[ 'Customer_Name' ] : null,
      'Employee_ID' => isset( $_GET[ 'Employee_ID' ] ) ? $_GET[ 'Employee_ID' ] : null,
      'Employee_First' => isset( $_GET[ 'Employee_First' ] ) ? $_GET[ 'Employee_First' ] : null,
      'Employee_Last' => isset( $_GET[ 'Employee_Last' ] ) ? $_GET[ 'Employee_Last' ] : null,
      'Division_ID' => isset( $_GET[ 'Division_ID' ] ) ? $_GET[ 'Division_ID' ] : null,
      'Division_Name' => isset( $_GET[ 'Division_Name' ] ) ? $_GET[ 'Division_Name' ] : null,
      'Route_ID' => isset( $_GET[ 'Route_ID' ] ) ? $_GET[ 'Route_ID' ] : null,
      'Route_Name' => isset( $_GET[ 'Route_Name' ] ) ? $_GET[ 'Route_Name' ] : null,
      'Territory_ID' => isset( $_GET[ 'Territory_ID' ] ) ? $_GET[ 'Territory_ID' ] : null,
      'Territory_Name' => isset( $_GET[ 'Territory_Name' ] ) ? $_GET[ 'Territory_Name' ] : null,
      'Sales_Tax' => isset( $_GET[ 'Sales_Tax' ] ) ? $_GET[ 'Sales_Tax' ] : null,
      'In_Use' => isset( $_GET[ 'In_Use' ] ) ? $_GET[ 'In_Use' ] : null,
      'Locations_Maintained' =>  isset( $_GET[ 'Locations_Maintained' ] ) ? $_GET[ 'Locations_Maintained' ] : null,
      'Locations_Unmaintained' =>  isset( $_GET[ 'Locations_Unmaintained' ] ) ? $_GET[ 'Locations_Unmaintained' ] : null,
      'Units_Moving_Walks' =>  isset( $_GET[ 'Units_Moving_Walks' ] ) ? $_GET[ 'Units_Moving_Walks' ] : null,
      'Units_Others' =>  isset( $_GET[ 'Units_Others' ] ) ? $_GET[ 'Units_Others' ] : null,
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
      $Territory[ 'Name' ] 	= isset( $_POST[ 'Name' ] ) 		? $_POST[ 'Name' ] 			: $Territory[ 'Name' ];
      $Territory[ 'SMAN' ] 	= isset( $_POST[ 'SMAN' ] ) 		? $_POST[ 'SMAN' ] 		: $Territory[ 'SMAN' ];
      $Territory[ 'SDesc' ] 	= isset( $_POST[ 'SDesc' ] ) 		? $_POST[ 'SDesc' ] 		: $Territory[ 'SDesc' ];
      $Territory[ 'Remarks' ] 	= isset( $_POST[ 'Remarks' ] ) 		? $_POST[ 'Remarks' ] 		: $Territory[ 'Remarks' ];
      $Territory[ 'Count' ] 	= isset( $_POST[ 'Count' ] ) 		? $_POST[ 'Count' ] 		: $Territory[ 'Count' ];
      $Territory[ 'Symbol' ] 	= isset( $_POST[ 'Symbol' ] ) 		? $_POST[ 'Symbol' ] 		: $Territory[ 'Symbol' ];
      $Territory[ 'EN' ] 	= isset( $_POST[ 'EN' ] ) 		? $_POST[ 'EN' ] 		: $Territory[ 'EN' ];
      $Territory[ 'Address' ] 	= isset( $_POST[ 'EN' ] ) 		? $_POST[ 'EN' ] 		: $Territory[ 'EN' ];
      $Territory[ 'TFMID' ] 	= isset( $_POST[ 'TFMID' ] ) 		? $_POST[ 'TFMID' ] 		: $Territory[ 'TFMID' ];
      $Territory[ 'TFMSource' ] 	= isset( $_POST[ 'TFMSource' ] ) 		? $_POST[ 'TFMSource' ] 		: $Territory[ 'TFMSource' ];
      $Territory[ 'City' ] 	= isset( $_POST[ 'City' ] ) 		? $_POST[ 'City' ] 			: $Territory[ 'City' ];
      $Territory[ 'State' ] 	= isset( $_POST[ 'State' ] ) 		? $_POST[ 'State' ] 		: $Territory[ 'State' ];
      $Territory[ 'Zip' ] 		= isset( $_POST[ 'Zip' ] ) 			? $_POST[ 'Zip' ] 			: $Territory[ 'Zip' ];
      $Territory[ 'TFMID' ] 	= isset( $_POST[ 'TFMID' ] ) 		? $_POST[ 'TFMID' ] 			: $Territory[ 'TFMID' ];
      $Territory[ 'TFMSource' ] 	= isset( $_POST[ 'TFMSource' ] ) 		? $_POST[ 'TFMSource' ] 			: $Territory[ 'TFMSource' ];
      $Territory[ 'Maintenance' ] = isset( $_POST[ 'Maintenance' ] ) ? $_POST[ 'Maintenance' ] : $Territory[ 'Maintenance' ];
      $Territory[ 'Sales_Tax' ] = isset( $_POST[ 'Sales_Tax' ] ) ? $_POST[ 'Sales_Tax' ] : $Territory[ 'Sales_Tax' ];
      $Territory[ 'In_Use' ] = isset( $_POST[ 'In_Use' ] ) ? $_POST[ 'In_Use' ] : $Territory[ 'In_Use' ];
      $Territory[ 'Customer_ID' ] = isset( $_POST[ 'Customer_ID' ] ) ? $_POST[ 'Customer_ID' ] : $Territory[ 'Customer_ID' ];
      $Territory[ 'Customer_Name' ] = isset( $_POST[ 'Customer_Name' ] ) ? $_POST[ 'Customer_Name' ] : $Territory[ 'Customer_Name' ];
      $Territory[ 'Route_ID' ] = isset( $_POST[ 'Route_ID' ] ) ? $_POST[ 'Route_ID' ] : $Territory[ 'Route_ID' ];
      $Territory[ 'Route_Name' ] = isset( $_POST[ 'Route_Name' ] ) ? $_POST[ 'Route_Name' ] : $Territory[ 'Route_Name' ];
      $Territory[ 'Division_ID' ] = isset( $_POST[ 'Division_ID' ] ) ? $_POST[ 'Division_ID' ] : $Territory[ 'Division_ID' ];
      $Territory[ 'Division_Name' ] = isset( $_POST[ 'Division_Name' ] ) ? $_POST[ 'Division_Name' ] : $Territory[ 'Division_Name' ];
      if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
        $result = \singleton\database::getInstance( )->query(
          null,
          "	DECLARE @MAXID INT;
            SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM dbo.Terr ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM dbo.Terr ) END;
            INSERT INTO dbo.Terr(
              ID,
              Name,
              SMAN,
              SDesc,
              Remarks,
              Count,
              Symbol,
              EN,
              Address,
              TFMID,
              TFMSource
            )
            VALUES( @MAXID + 1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
            SELECT @MAXID + 1;",
          array(
            $Territory[ 'Name' ],
            $Territory[ 'SMAN' ],
            $Territory[ 'SDesc' ],
            $Territory[ 'Remarks' ],
            $Territory[ 'Count' ],
            $Territory[ 'Symbol' ],
            $Territory[ 'EN' ],
            $Territory[ 'Address' ],
            is_null( $Territory[ 'TFMID' ] ) ? 0 : $Territory[ 'TFMID' ],
            is_null( $Territory[ 'TFMSource' ] ) ? 0 : $Territory[ 'TFMSource' ]
          )
        );
        var_dump(sqlsrv_errors ( ) );
        sqlsrv_next_result( $result );
          $Territory[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
          //header( 'Location: territory.php?ID=' . $Territory[ 'ID' ] );
      } else {
        \singleton\database::getInstance( )->query(
          null,
          "	UPDATE 	Territory
            SET 	  Territory.Name = ?,
                    Territory.SMan = ?,
                    Territory.SDesc = ?,
                    Territory.Remarks = ?,
                    Territory.Count = ?,
                    Territory.Symbol = ?,
                    Territory.EN = ?,
                    Territory.Address = ?,
                    Territory.TFMID = ?,
                    Territory.TFMSource = ?,
            WHERE 	Territory.Loc= ?;",
          array(
            $Territory[ 'Name' ],
            $Territory[ 'SMAN' ],
            $Territory[ 'SDesc' ],
            $Territory[ 'Remarks' ],
            $Territory[ 'Count' ],
            $Territory[ 'Symbol' ],
            $Territory[ 'EN' ],
            $Territory[ 'Address' ],
            $Territory[ 'TFMID' ],
            $Territory[ 'TFMSource' ]
          )
        );
      }
    }
    var_dump(sqlsrv_errors ( ) );
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
                <form action='territory.php?ID=<?php echo $Territory[ 'ID' ];?>' method='POST'>
                    <input type='hidden' name='ID' value='<?php echo $Territory[ 'ID' ];?>' />
                    <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Territory', 'Territorys', $Territory[ 'ID' ] );?>
                    <div class='card-body bg-dark text-white'>
                        <div class='row g-0' data-masonry='{"percentPosition": true }'>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' ); ?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php
                                        \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Employee', 'Employees', $Territory[ 'Employee_ID' ], $Territory[ 'Employee_First' ] && $Territory['Employee_Last'] );
                      							    \singleton\bootstrap::getInstance( )->card_row_form_input_url( 'Website', $Territory[ 'Website' ] );
                                        \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'City', $Territory[ 'City' ] );
                                        \singleton\bootstrap::getInstance( )->card_row_form_select_sub( 'State', $Territory[ 'State' ],  array( 'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming' ) );
                                        \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Zip', $Territory[ 'Zip' ] );
                                    ?>
                                </div>
                            </div>
                            <!-- End of customer inforation card, ending with card-footer div class with a button for save  -->
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <!-- Card hedding, that holds customer contacts, with a post call that gets customer contact information based on $Territory ID  -->
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Contacts', 'Contact', 'Contacts', 'Customer', $Territory[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Contacts' ] ) && $_SESSION[ 'Cards' ][ 'Contacts' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Contact', $Territory[ 'Contact' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input_tel( 'Phone', $Territory[ 'Phone' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input_email( 'Email', $Territory[ 'Email' ] );?>
                                </div>
                            </div>
                            <!-- End of customer contact information card, ending with customer card-footer and a submit button-->
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Locations', 'Location', 'Locations', 'Territory', $Territory[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Locations' ] ) && $_SESSION[ 'Cards' ][ 'Locations' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Locations', 'locations.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ]);?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Maintain', $Territory[ 'Locations_Maintained' ], true, true, 'locations.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Maintained=1');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Territory[ 'Locations_Unmaintained' ], true, true, 'locations.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Maintained=0' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Units', 'Unit', 'Units', 'Territory', $Territory[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'units.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Elevators', $Territory[ 'Units_Elevators' ], true, true, 'units.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Type=Elevator');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Escalators', $Territory[ 'Units_Escalators' ], true, true, 'units.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Type=Escalator' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Moving_Walks', $Territory[ 'Units_Moving_Walks' ], true, true, 'units.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Type=Escalator' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Others', $Territory[ 'Units_Others' ], true, true, 'units.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Type=Other' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Jobs', 'Job', 'Jobs', 'Territory', $Territory[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Jobs' ] ) && $_SESSION[ 'Cards' ][ 'Jobs' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'jobs.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Territory[ 'Jobs_Open' ], true, true, 'jobs.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=0');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Hold', $Territory[ 'Jobs_On_Hold' ], true, true, 'jobs.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=2' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Territory[ 'Jobs_Closed' ], true, true, 'jobs.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=1' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Territory', $Territory[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Territory[ 'Tickets_Open' ], true, true, 'tickets.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=0');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Territory[ 'Tickets_Assigned' ], true, true, 'tickets.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=1' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En Route', $Territory[ 'Tickets_En_Route' ], true, true, 'tickets.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=2' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Site', $Territory[ 'Tickets_On_Site' ], true, true, 'tickets.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=3' );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Territory[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=6' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Violations', 'Violations', 'Violations', 'Territory', $Territory[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Violations' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'violations.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Preliminary', $Territory[ 'Violations_Preliminary_Report' ], true, true, 'violations.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=Preliminary Report');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Ongoing', $Territory[ 'Violations_Job_Created' ], true, true, 'violations.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=Job Created' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Proposals', 'Proposal', 'Proposals', 'Territory', $Territory[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'proposals.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Territory[ 'Proposals_Open' ], true, true, 'proposals.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=0');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Territory[ 'Proposals_Closed' ], true, true, 'proposals.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=1' );?>
                                </div>
                            </div>
                            <div class='card card-primary my-3 col-12 col-lg-3'>
                                <?php \singleton\bootstrap::getInstance( )->card_header( 'Invoices', 'Invoice', 'Invoices', 'Territory', $Territory[ 'ID' ] );?>
                                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'invoices.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] );?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Territory[ 'Invoices_Open' ], true, true, 'invoices.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=0');?>
                                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Territory[ 'Invoices_Closed' ], true, true, 'invoices.php?Territory_ID=' . $Territory[ 'ID' ] . '&Territory_Name=' . $Territory[ 'Name' ] . '&Status=1' );?>
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
} else {?><html><head><script>document.location.href="../login.php?Forward=territory<?php echo (!isset($Territory[ 'ID' ]) || !is_numeric($Territory[ 'ID' ])) ? "s.php" : ".php?ID={$Territory[ 'ID' ]}";?>";</script></head></html><?php }?>
