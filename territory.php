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
      ||  !isset( $Privileges[ 'Territory' ] )
      ||  !check( privilege_read, level_group, $Privileges[ 'Territory' ] )
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
    $result = \singleton\database::getInstance( )->query(
      null,
      " SELECT 	Territory.ID             AS ID,
                Territory.Name           AS Name,
              	Territory.SMan           AS SMAN,
                Territory.SDesc          AS Description,
                Territory.Remarks        AS Remarks,
                Territory.Count          AS Count,
                Territory.Symbol         AS Symbol,
                Territory.EN             AS EN,
    	          Territory.Address        AS Address,
                Territory.TFMID          AS TFMID,
                Territory.TFMSource      AS TFMSource,
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
                        ELSE Violations.Job_Created END AS Violations_Job_Created,
                CASE    WHEN Invoices.[Open] IS NULL THEN 0
                        ELSE Invoices.[Open] END AS Invoices_Open,
                CASE    WHEN Invoices.[Closed] IS NULL THEN 0
                        ELSE Invoices.[Closed] END AS Invoices_Closed,
                CASE    WHEN Proposals.[Open] IS NULL THEN 0
                        ELSE Proposals.[Open] END AS Proposals_Open,
                CASE    WHEN Proposals.[Closed] IS NULL THEN 0
                        ELSE Proposals.[Closed] END AS Proposals_Closed
        FROM    Terr AS Territory
                LEFT JOIN (
                    SELECT      Location.Terr AS Territory,
                                Sum( Maintained.Count ) AS Maintained,
                                Sum( Unmaintained.Count ) AS Unmaintained,
                                Count( Location.Loc ) AS Count
                    FROM        Loc AS Location
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
                    GROUP BY    Location.Terr
                ) AS Locations ON Locations.Territory = Territory.ID
                LEFT JOIN (
                  SELECT      Location.Terr AS Territory,
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
                  GROUP BY    Location.Terr
              ) AS Units ON Units.Territory = Territory.ID
              LEFT JOIN (
                      SELECT  Location.Terr AS Territory,
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
                  ) AS Jobs ON Jobs.Territory = Territory.ID
              LEFT JOIN (
                SELECT  Territory.ID AS Territory,
                        Unassigned.Count AS Unassigned,
                        Assigned.Count AS Assigned,
                        En_Route.Count AS En_Route,
                        On_Site.Count AS On_Site,
                        Reviewing.Count AS Reviewing
                FROM    Terr AS Territory
                        LEFT JOIN (
                          SELECT    Location.Terr AS Territory,
                                    Count( TicketO.ID ) AS Count
                          FROM      TicketO
                                    LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                                    WHERE     TicketO.Assigned = 0
                          GROUP BY  Location.Terr
                        ) AS Unassigned ON Unassigned.Territory = Territory.ID
                        LEFT JOIN (
                          SELECT    Location.Terr AS Territory,
                                    Count( TicketO.ID ) AS Count
                          FROM      TicketO
                                    LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                          WHERE     TicketO.Assigned = 1
                          GROUP BY  Location.Terr
                        ) AS Assigned ON Assigned.Territory = Territory.ID
                        LEFT JOIN (
                          SELECT    Location.Terr AS Territory,
                                    Count( TicketO.ID ) AS Count
                          FROM      TicketO
                                    LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                          WHERE     TicketO.Assigned = 2
                          GROUP BY  Location.Terr
                        ) AS En_Route ON En_Route.Territory = Territory.ID
                        LEFT JOIN (
                          SELECT    Location.Terr AS Territory,
                                    Count( TicketO.ID ) AS Count
                          FROM      TicketO
                                    LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                          WHERE     TicketO.Assigned = 3
                          GROUP BY  Location.Terr
                        ) AS On_Site ON On_Site.Territory = Territory.ID
                        LEFT JOIN (
                          SELECT    Location.Terr AS Territory,
                                    Count( TicketO.ID ) AS Count
                          FROM      TicketO
                                    LEFT JOIN Loc AS Location ON Location.Loc = TicketO.LID
                          WHERE     TicketO.Assigned = 6
                          GROUP BY  Location.Terr
                        ) AS Reviewing ON Reviewing.Territory = Territory.ID
                ) AS Tickets ON Tickets.Territory = Territory.ID
                LEFT JOIN (
                    SELECT  Location.Terr AS Territory,
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
                ) AS Violations ON Violations.Territory = Territory.ID
                LEFT JOIN (
                    SELECT      Location.Terr           AS Territory,
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
                    GROUP BY    Location.Terr
                ) AS Invoices ON Invoices.Territory = Territory.ID
                LEFT JOIN (
                  SELECT    Location.Terr AS Territory,
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
                ) AS Proposals ON Proposals.Territory = Territory.ID
        WHERE       Territory.ID = ?
                OR 	Territory.Name = ?;",
      array(
        $ID,
        $Name
      )
    );
    //var_dump( sqlsrv_errors( ) );
    $Territory =   (      empty( $ID )
                      &&  !empty( $Name )
                      &&  !$result
                    )    || (empty( $ID )
                      &&  empty( $Name )
                    )    ? array(
                    	'ID' => null,
                      'Name' => null,
                    	'SMan' => null,
                    	'SDesc' => null,
                    	'Remarks' => null,
                    	'Count' => null,
                    	'Symbol' => null,
                    	'EN' => null,
                    	'Address' => null,
                      'TFMID' => null,
                    	'TFMSource' => null,
                      'Locations_Count' => null,
                      'Locations_Maintained' => null,
                      'Locations_Unmaintained' => null,
                      'Units_Count' => null,
                      'Units_Elevators' => null,
                      'Units_Escalators' => null,
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
                      'Invoices_Open' => null,
                      'Invoices_Closed' => null,
                      'Proposals_Open' => null,
                      'Proposals_Closed' => null
    ) : sqlsrv_fetch_array($result);
    //Binds $ID, $Name, $Territory and query values into the $resultesult variable
    if( isset( $_POST ) && count( $_POST ) > 0 ){
      // if the $_Post is set and the count is null, select if available
      $Territory[ 'ID' ] 		= isset( $_POST[ 'ID' ] ) 	 ? $_POST[ 'ID' ] 	 : $Territory[ 'ID' ];
      $Territory[ 'Name' ] 	= isset( $_POST[ 'Name' ] ) ? $_POST[ 'Name' ] : $Territory[ 'Name' ];
      $Territory[ 'SMan' ] 		= isset( $_POST[ 'SMan' ] ) 	 ? $_POST[ 'SMan' ] 	 : $Territory[ 'SMan' ];
      $Territory[ 'SDesc' ] 		= isset( $_POST[ 'SDesc' ] ) 	 ? $_POST[ 'SDesc' ] 	 : $Territory[ 'SDesc' ];
      $Territory[ 'Remarks' ] 		= isset( $_POST[ 'Remarks' ] ) 	 ? $_POST[ 'Remarks' ] 	 : $Territory[ 'Remarks' ];
      $Territory[ 'Count' ] = isset( $_POST[ 'Count' ] )  ? $_POST[ 'Count' ]  : $Territory[ 'Count' ];
      $Territory[ 'Symbol' ] = isset( $_POST[ 'Symbol' ] )  ? $_POST[ 'Symbol' ]  : $Territory[ 'Symbol' ];
      $Territory[ 'EN' ]     = isset( $_POST[ 'EN' ] ) 	   ? $_POST[ 'EN' ] 	   : $Territory[ 'EN' ];
      $Territory[ 'Address' ] 	= isset( $_POST[ 'Address' ] ) 	 ? $_POST[ 'Address' ] 	 : $Territory[ 'Address' ];
      $Territory[ 'TFMID' ] 	= isset( $_POST[ 'TFMID' ] ) 	 ? $_POST[ 'TFMID' ] 	 : $Territory[ 'TFMID' ];
      $Territory[ 'TFMSource' ] = isset( $_POST[ 'TFMSource' ] )  ? $_POST[ 'TFMSource' ]  : $Territory[ 'TFMSource' ];
      $Territory[ 'Address' ] 	= isset( $_POST[ 'Address' ] ) 	 ? $_POST[ 'Address' ] 	 : $Territory[ 'Address' ];
      $Territory[ 'Price' ] 	= isset( $_POST[ 'Price' ] ) 	 ? $_POST[ 'Price' ] 	 : $Territory[ 'Price' ];
      if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
        $result = \singleton\database::getInstance( )->query(
          null,
          "	DECLARE @MAXID INT;
            SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Terr ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Terr ) END ;
            INSERT INTO Terr(
              ID,
              Name,
              SDesc,
              Remarks,
              Count,
              Symbol,
              EN,
              Address,
              TFMID,
              TFMSource
            )
            VALUES( @MAXID + 1 , ?, ?, ?, ?, ?, ?, ?, ?, ? );
            SELECT @MAXID + 1;",
          array(
          	$Territory[ 'ID' ],
            $Territory[ 'Name' ],
            $Territory[ 'SDesc' ],
            $Territory[ 'Remarks' ],
            $Territory[ 'Count' ],
            $Territory[ 'Symbol' ],
            $Territory[ 'EN' ],
            $Territory[ 'Address' ],
            $Territory[ 'TFMID' ],
            $Territory[ 'TFMSource' ],
            $Territory[ 'Price' ]
          )
        );
        sqlsrv_next_result( $result );
        //Update query to fill values for $Territory and appends to $resultesult for any updated colums
        $Territory[ 'Rolodex' ] = sqlsrv_fetch_array( $result )[ 0 ];
        // finds any result with the value of 0/ null
        // query that inserts values into the $Territory [rolodex] variable datatable and appends it to the $resultesult variable
        sqlsrv_next_result( $result );
        $Territory[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
        // Checks the $Territory[ID] for any fields that are null, if none exit,
        header( 'Location: territory.php?ID=' . $Territory[ 'ID' ] );
        exit;
      } else {
        \singleton\database::getInstance( )->query(
          null,
          "	UPDATE 	Territory
            SET     Territory.ID = ?,
                    Territory.Name = ?,
                    Territory.SMan = ?,
                    Territory.SDesc = ?,
                    Territory.Remarks = ?,
                    Territory.Count = ?,
                    Territory.Symbol = ?,
                    Territory.EN = ?,
                    Territory.Address = ?,
                    Territory.TFMID = ?,
                    Territory.TFMSource = ?,
            WHERE 	Terr.ID = ?;",
          array(
            $Territory[ 'Name' ],
            $Territory[ 'SMan' ],
            $Territory[ 'SDesc' ],
            $Territory[ 'Remarks' ],
            $Territory[ 'Count' ],
            $Territory[ 'Symbol' ],
            $Territory[ 'EN' ],
            $Territory[ 'Address' ],
            $Territory[ 'TFMID' ],
            $Territory[ 'TFMSource' ],
            $Territory[ 'Price' ]
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
<body onload='finishLoadingPage();'>
  <div id="wrapper">
    <?php require(bin_php .'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'>
        <form method='POST' action='territory.php?ID=<?php echo $Territory[ 'ID' ];?>'>
          <input type='hidden' name='ID' value='<?php echo $Territory[ 'ID' ];?>' />
          <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Territory', 'Territories', $Territory[ 'ID' ] );?>
          <div class='card-body bg-dark text-white'>
            <div class='row g-0' data-masonry='{"percentPosition": true }'>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Locations', 'Location', 'Locations', 'Territory', $Territory[ 'ID' ] );?>
                <div class='card-body bg-dark text-white' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Territories' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'locations.php?Territory=' . $Territory[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Total', $Territory[ 'Locations_Count' ], true, true, 'locations.php?Territory=' . $Territory[ 'ID' ] . '&Status=Preliminary Report');?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Units', 'Unit', 'Units', 'Territory', $Territory[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'units.php?Territory=' . $Territory[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Elevators', $Territory[ 'Units_Elevators' ], true, true, 'units.php?Territory=' . $Territory[ 'ID' ] . '&Type=Elevator');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Escalators', $Territory[ 'Units_Escalators' ], true, true, 'units.php?Territory=' . $Territory[ 'ID' ] ) . '&Type=Escalator';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Escalators', $Territory[ 'Units_Other' ], true, true, 'units.php?Territory=' . $Territory[ 'ID' ] ) . '&Type=Other';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Territory', $Territory[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Territory=' . $Territory[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Territory[ 'Tickets_Open' ], true, true, 'tickets.php?Territory=' . $Territory[ 'ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Territory[ 'Tickets_Assigned' ], true, true, 'tickets.php?Territory=' . $Territory[ 'ID' ] ) . '&Status=1';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En_Route', $Territory[ 'Tickets_En_Route' ], true, true, 'tickets.php?Territory=' . $Territory[ 'ID' ] ) . '&Status=2';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On_Site', $Territory[ 'Tickets_On_Site' ], true, true, 'tickets.php?Territory=' . $Territory[ 'ID' ] ) . '&Status=3';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Territory[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Territory=' . $Territory[ 'ID' ] ) . '&Status=6';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Jobs', 'Job', 'Jobs', 'Territory', $Territory[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Jobs' ] ) && $_SESSION[ 'Cards' ][ 'Jobs' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Types', 'jobs.php?Territory=' . $Territory[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Territory[ 'Jobs_Open' ], true, true, 'jobs.php?Territory=' . $Territory[ 'ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Hold', $Territory[ 'Jobs_On_Hold' ], true, true, 'jobs.php?Territory=' . $Territory[ 'ID' ] ) . '&Status=2';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Territory[ 'Jobs_Closed' ], true, true, 'jobs.php?Territory=' . $Territory[ 'ID' ] ) . '&Status=1';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Violations', 'Violations', 'Violations', 'Territory', $Territory[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Violations' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'violations.php?Territory=' . $Territory[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Preliminary', $Territory[ 'Violations_Preliminary_Report' ], true, true, 'violations.php?Territory=' . $Territory[ 'ID' ] . '&Status=Preliminary Report');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Ongoing', $Territory[ 'Violations_Job_Created' ], true, true, 'violations.php?Territory=' . $Territory[ 'ID' ] ) . '&Status=Job Created';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Proposals', 'Proposal', 'Proposals', 'Territory', $Territory[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'proposals.php?Territory=' . $Territory[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Territory[ 'Proposals_Open' ], true, true, 'proposals.php?Territory=' . $Territory[ 'ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Territory[ 'Proposals_Closed' ], true, true, 'proposals.php?Territory=' . $Territory[ 'ID' ] ) . '&Status=1';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Invoices', 'Invoice', 'Invoices', 'Territory', $Territory[ 'ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'invoices.php?Territory=' . $Territory[ 'ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Territory[ 'Invoices_Open' ], true, true, 'invoices.php?Territory=' . $Territory[ 'ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Territory[ 'Invoices_Closed' ], true, true, 'invoices.php?Territory=' . $Territory[ 'ID' ] ) . '&Status=1';?>
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
} else {?><html><head><script>document.location.href="../login.php?Forward=territory<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
