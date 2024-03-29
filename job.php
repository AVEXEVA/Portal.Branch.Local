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
    if(   !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Job' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
      \singleton\database::getInstance( )->query(
            null,
            " INSERT INTO Activity([User], [Date], [Page] )
              VALUES( ?, ?, ? );",
            array(
              $_SESSION[ 'Connection' ][ 'User' ],
              date('Y-m-d H:i:s'),
              'job.php'
          )
        );
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
     		"	SELECT 	 TOP 1
              		Job.ID                AS ID,
              		Job.fDesc             AS Name,
              		Job.fDate             AS Date,
	                Job.BHour             AS Budgeted_Hours,
                  Job.Type              AS Job_Type_ID,
         			    JobType.Type          AS Job_Type_Name,
      				    Job.Remarks      		  AS Notes,
                  Job.Status            AS Status,
                  Emp.fFirst            AS Employee_First_Name,
                  Emp.Last              AS Employee_Last_Name,
    		          Loc.Loc               AS Location_ID,
              		Loc.Tag               AS Location_Name,
              		Loc.Address           AS Location_Street,
              		Loc.City              AS Location_City,
              		Loc.State             AS Location_State,
              		Loc.Zip               AS Location_Zip,
                  Loc.Latt              AS Location_Latitude,
                  Loc.fLong             AS Location_Longitude,
              		Route.ID              AS Route_ID,
                  Route.Name            AS Route_Name,
                  Zone.ID               AS Division_ID,
              		Zone.Name             AS Division_Name,
              		Customer.ID           AS Customer_ID,
              		Customer.Name     	  AS Customer_Name,
              		Customer.Street       AS Customer_Street,
              		Customer.City         AS Customer_City,
              		Customer.State        AS Customer_State,
              		Customer.Zip          AS Customer_Zip,
              		Customer.Contact      AS Customer_Contact,
              		Customer.Remarks      AS Customer_Remarks,
              		Elev.ID               AS Unit_ID,
              		CASE 	WHEN Elev.State IS NULL AND Elev.Unit IS NULL THEN null
              				WHEN Elev.State IS NULL THEN Elev.Unit
              				WHEN Elev.Unit IS NULL THEN Elev.State
              				ELSE Elev.State + ' - ' + Elev.Unit END AS Unit_Name,
              		Elev.Unit             AS Unit_Label,
              		Elev.State            AS Unit_State,
              		Elev.Cat              AS Unit_Category,
              		Elev.Type             AS Unit_Type,
              		Emp.fFirst            AS Employee_First_Name,
              		Emp.Last              AS Employee_Last_Name,
              		Route.ID              AS Route_ID,
      				    Violation.ID          AS Violation_ID,
      		        Violation.fdate       AS Violation_Date,
      				    Violation.Status      AS Violation_Status,
      				    Violation.Remarks     AS Violation_Remarks,
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
                  CASE    WHEN Invoices.[Open] IS NULL THEN 0
                          ELSE Invoices.[Open] END AS Invoices_Open,
                  CASE    WHEN Invoices.[Closed] IS NULL THEN 0
                          ELSE Invoices.[Closed] END AS Invoices_Closed,
                  CASE    WHEN Contracts.[Open] IS NULL THEN 0
                          ELSE Contracts.[Open] END AS Contracts_Open,
                  CASE    WHEN Contracts.[Closed] IS NULL THEN 0
                          ELSE Contracts.[Closed] END AS Contracts_Closed,
                  CASE    WHEN Proposals.[Open] IS NULL THEN 0
                          ELSE Proposals.[Open] END AS Proposals_Open,
                  CASE    WHEN Proposals.[Closed] IS NULL THEN 0
                          ELSE Proposals.[Closed] END AS Proposals_Closed
          	FROM 	 Job
              		 LEFT JOIN Loc ON Job.Loc = Loc.Loc
              		 LEFT JOIN Elev ON Elev.ID = Job.Elev
              		 LEFT JOIN Zone ON Loc.Zone = Zone.ID
              		 LEFT JOIN Route ON Route.ID = Loc.Route
              		 LEFT JOIN JobType ON Job.Type = JobType.ID
              		 LEFT JOIN (
              		 	SELECT 	Owner.ID,
              		 			    Rol.Name,
              		 			    Rol.Address AS Street,
              		 			    Rol.City,
              		 			    Rol.State,
              		 			    Rol.Zip,
              		 			    Rol.Contact,
              		 			    Rol.Remarks
              		 	FROM 	  Owner
              		 			    LEFT JOIN Rol ON Owner.Rol = Rol.ID
              		 ) AS Customer 	ON Job.Owner = Customer.ID
              		 LEFT JOIN Emp ON Emp.fWork = Route.Mech
        					 LEFT JOIN Violation ON Job.ID = Violation.Job
        					 LEFT JOIN Owner ON Owner.ID 	= Loc.Owner
                   LEFT JOIN (
                        SELECT  Job.ID AS Job,
                                Unassigned.Count AS Unassigned,
                                Assigned.Count AS Assigned,
                                En_Route.Count AS En_Route,
                                On_Site.Count AS On_Site,
                                Reviewing.Count AS Reviewing
                        FROM    Job AS Job
                                LEFT JOIN (
                                  SELECT    Job.ID AS Job,
                                            Count( TicketO.ID ) AS Count
                                  FROM      TicketO
                                            LEFT JOIN Job AS Job ON Job.ID = TicketO.Job
                                  WHERE     TicketO.Assigned = 0
                                  GROUP BY  Job.ID
                                ) AS Unassigned ON Unassigned.Job = Job.ID
                                LEFT JOIN (
                                  SELECT    Job.ID AS Job,
                                            Count( TicketO.ID ) AS Count
                                  FROM      TicketO
                                            LEFT JOIN Job AS Job ON Job.ID = TicketO.Job
                                  WHERE     TicketO.Assigned = 1
                                  GROUP BY  Job.ID
                                ) AS Assigned ON Assigned.Job = Job.ID
                                LEFT JOIN (
                                  SELECT    Job.ID AS Job,
                                            Count( TicketO.ID ) AS Count
                                  FROM      TicketO
                                            LEFT JOIN Job AS Job ON Job.ID = TicketO.Job
                                  WHERE     TicketO.Assigned = 2
                                  GROUP BY  Job.ID
                                ) AS En_Route ON En_Route.Job = Job.ID
                                LEFT JOIN (
                                  SELECT    Job.ID AS Job,
                                            Count( TicketO.ID ) AS Count
                                  FROM      TicketO
                                            LEFT JOIN Job AS Job ON Job.ID = TicketO.Job
                                  WHERE     TicketO.Assigned = 3
                                  GROUP BY  Job.ID
                                ) AS On_Site ON On_Site.Job = Job.ID
                                LEFT JOIN (
                                  SELECT    Job.ID AS Job,
                                            Count( TicketO.ID ) AS Count
                                  FROM      TicketO
                                            LEFT JOIN Job AS Job ON Job.ID = TicketO.Job
                                  WHERE     TicketO.Assigned = 6
                                  GROUP BY  Job.ID
                                ) AS Reviewing ON Reviewing.Job = Job.ID
                    ) AS Tickets ON Tickets.Job = Job.ID
                    LEFT JOIN (
                      SELECT    Job.ID AS Job,
                                [Open].Count AS [Open],
                                [Closed].Count AS Closed
                      FROM      Job
                                LEFT JOIN (
                                  SELECT    Invoice.Job,
                                            Count( Invoice.Ref ) AS Count
                                  FROM      Invoice
                                  WHERE     Invoice.Ref IN ( SELECT Ref FROM OpenAR )
                                  GROUP BY  Invoice.Job
                                ) AS [Open] ON Job.ID = [Open].Job
                                LEFT JOIN (
                                  SELECT    Invoice.Job,
                                            Count( Invoice.Ref ) AS Count
                                  FROM      Invoice
                                  WHERE     Invoice.Ref NOT IN ( SELECT Ref FROM OpenAR )
                                  GROUP BY  Invoice.Job
                                ) AS [Closed] ON Job.ID = [Closed].Job
                    ) AS Invoices ON Invoices.Job = Job.ID
                    LEFT JOIN (
                      SELECT    Job.ID AS Job,
                                [Open].Count AS [Open],
                                [Closed].Count AS Closed
                      FROM      Job
                                LEFT JOIN (
                                  SELECT    Contract.Job,
                                            Count( Contract.ID ) AS Count
                                  FROM      Contract
                                  WHERE     Contract.Status = 0
                                  GROUP BY  Contract.Job
                                ) AS [Open] ON Job.ID = [Open].Job
                                LEFT JOIN (
                                  SELECT    Contract.Job,
                                            Count( Contract.ID ) AS Count
                                  FROM      Contract
                                  WHERE     Contract.Status = 1
                                  GROUP BY  Contract.Job
                                ) AS [Closed] ON Job.ID = [Closed].Job
                    ) AS Contracts ON Contracts.Job = Job.ID
                    LEFT JOIN (
                      SELECT    Job.ID AS Job,
                                [Open].Count AS [Open],
                                [Closed].Count AS Closed
                      FROM      Job
                                LEFT JOIN (
                                  SELECT    Estimate.Job,
                                            Count( Estimate.ID ) AS Count
                                  FROM      Estimate
                                  WHERE     Estimate.Status = 0
                                  GROUP BY  Estimate.Job
                                ) AS [Open] ON Job.ID = [Open].Job
                                LEFT JOIN (
                                  SELECT    Estimate.Job,
                                            Count( Estimate.ID ) AS Count
                                  FROM      Estimate
                                  WHERE     Estimate.Status = 1
                                  GROUP BY  Estimate.Job
                                ) AS [Closed] ON Job.ID = [Closed].Job
                    ) AS Proposals ON Proposals.Job = Job.ID
          	WHERE      Job.ID = ?
                    OR Job.fDesc = ?;",
         array(
         	$ID,
          $Name
         )
       );
       $Job =   empty( $ID ) ? array(
          'ID' => null,
          'Name' => isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null,
          'Date' => null,
          'Type' => null,
          'Notes' => null,
          'Status' => null,
          'Job_Type_ID' => isset( $_GET[ 'Job_Type_ID' ] ) ? $_GET[ 'Job_Type_ID' ] : null,
          'Job_Type_Name' => isset( $_GET[ 'Job_Type_Name' ] ) ? $_GET[ 'Job_Type_Name' ] : null,
          'Location_ID' => isset( $_GET[ 'Territory_ID' ] ) ? $_GET[ 'Territory_ID' ] : null,
          'Location_Name' => isset( $_GET [ 'Location_Name' ] ) ? $_GET ['Location_Name'] : null,
          'Location_Street' => null,
          'Location_City' => null,
          'Location_State' => null,
          'Location_Zip' => null,
          'Location_Latitude' => null,
          'Location_Longitude' => null,
          'Customer_ID' =>  isset( $_GET[ 'Customer_ID' ] ) ? $_GET[ 'Customer_ID' ] : null,
          'Customer_Name' => isset( $_GET [ 'Customer_Name' ] ) ? $_GET ['Customer_Name'] : null,
          'Unit_ID' => isset( $_GET[ 'Unit_ID' ] ) ? $_GET[ 'Unit_ID' ] : null,
          'Unit_Name' => isset( $_GET [ 'Unit_Name' ] ) ? $_GET ['Unit_Name'] : null,
          'Violation_ID' => isset( $_GET[ 'Territory_ID' ] ) ? $_GET[ 'Territory_ID' ] : null,
          'Violation_Name' => isset( $_GET [ 'Violation_Name' ] ) ? $_GET ['Violation_Name'] : null,
          'Division_ID' => isset( $_GET[ 'Division_ID' ] ) ? $_GET[ 'Division_ID' ] : null,
          'Division_Name' => isset( $_GET [ 'Division_Name' ] ) ? $_GET ['Division_Name'] : null,
          'Tickets_Open' => isset( $_GET[ 'Tickets_Open' ] ) ? $_GET[ 'Tickets_Open' ] : null,
          'Tickets_Assigned' => isset( $_GET[ 'Tickets_Assigned' ] ) ? $_GET[ 'Tickets_Assigned' ] : null,
          'Tickets_En_Route' => isset( $_GET[ 'Tickets_En_Route' ] ) ? $_GET[ 'Tickets_En_Route' ] : null,
          'Tickets_On_Site' => isset( $_GET[ 'Tickets_On_Site' ] ) ? $_GET[ 'Tickets_On_Site' ] : null,
          'Tickets_Reviewing' => isset( $_GET[ 'Tickets_Reviewing' ] ) ? $_GET[ 'Tickets_Reviewing' ] : null,
          'Invoices_Open' => isset( $_GET[ 'Invoices_Open' ] ) ? $_GET[ 'Invoices_Open' ] : null,
          'Invoices_Closed' => isset( $_GET[ 'Invoices_Closed' ] ) ? $_GET[ 'Invoices_Closed' ] : null,
          'Proposals_Open' => isset( $_GET[ 'Proposals_Open' ] ) ? $_GET[ 'Proposals_Open' ] : null,
          'Proposals_Closed' => isset( $_GET[ 'Proposals_Closed' ] ) ? $_GET[ 'Proposals_Closed' ] : null,
          'Contracts_Open' => isset( $_GET[ 'Contracts_Open' ] ) ? $_GET[ 'Contracts_Open' ] : null,
          'Contracts_Closed' => isset( $_GET[ 'Contracts_Closed' ] ) ? $_GET[ 'Contracts_Closed' ] : null
        ) : sqlsrv_fetch_array($result);
        if( isset( $_POST ) && count( $_POST ) > 0 ){
          $Job[ 'Name' ] = isset( $_POST[ 'Name' ] ) ? $_POST[ 'Name' ] : $Job[ 'Name' ];
          $Job[ 'Customer_Name' ] = isset( $_POST[ 'Customer_Name' ] ) ? $_POST[ 'Customer_Name' ] : $Job[ 'Customer_Name' ];
          $Job[ 'Customer_ID' ] = isset( $_POST[ 'Customer_ID' ] ) ? $_POST[ 'Customer_ID' ] : $Job[ 'Customer_ID' ];
          $Job[ 'Location_Name' ] = isset( $_POST[ 'Location_Name' ] ) ? $_POST[ 'Location_Name' ] : $Job[ 'Location_Name' ];
          $Job[ 'Location_ID' ] = isset( $_POST[ 'Location_ID' ] ) ? $_POST[ 'Location_ID' ] : $Job[ 'Location_ID' ];
          $Job[ 'Unit_Name' ] = isset( $_POST[ 'Unit_Name' ] ) ? $_POST[ 'Unit_Name' ] : $Job[ 'Unit_Name' ];
          $Job[ 'Unit_ID' ] = isset( $_POST[ 'Unit_ID' ] ) ? $_POST[ 'Unit_ID' ] : $Job[ 'Unit_ID' ];
          $Job[ 'Date' ] = isset( $_POST[ 'Date' ] ) ? date( 'Y-m-d h:i:s', strtotime( $_POST[ 'Date' ] ) ) : $Job[ 'Date' ];
          $Job[ 'Job_Type_ID' ] = isset( $_POST[ 'Job_Type_ID' ] ) ? $_POST[ 'Job_Type_ID' ] : $Job[ 'Job_Type_ID' ];
          $Job[ 'Job_Type_Name' ] = isset( $_POST[ 'Job_Type_Name' ] ) ? $_POST[ 'Job_Type_Name' ] : $Job[ 'Job_Type_Name' ];
          $Job[ 'Notes' ] = isset( $_POST[ 'Notes' ] ) ? $_POST[ 'Notes' ] : $Job[ 'Notes' ];
          $Job[ 'Status' ] = isset( $_POST[ 'Status' ] ) ? $_POST[ 'Status' ] : $Job[ 'Status' ];
          if( empty( $_POST[ 'ID' ] ) ){
            $result = \singleton\database::getInstance( )->query(
              null,
              " DECLARE @MAXID INT;
                SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Job ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Job ) END ;
                INSERT INTO Job(
                  ID,
                  fDesc,
                  fDate,
                  Owner,
                  Loc,
                  Elev,
                  Type,
                  Status,
                  Remarks,
                  Custom1, Custom2, Custom3, Custom4, Custom5,
                  Custom6, Custom7, Custom8, Custom9, Custom10,
                  Custom11, Custom12, Custom13, Custom14, Custom15,
                  Custom16, Custom17, Custom18, Custom19, Custom20,
                  Rev, Mat, Labor, Cost, Profit, Ratio, Reg, OT, DT, TT, Hour, BRev, BMat, BLabor, BCost, BProfit, BRatio, BHour, NT
                )
                VALUES ( @MAXID + 1, ?, ?, ?, ?, ?, ?, ?, ?," . implode( ',', array_fill( 0, 20, "''" ) ) . ", " . implode( ',', array_fill( 0, 19, '0' ) ) . ");
                SELECT @MAXID + 1;",
              array(
                $Job[ 'Name' ],
                $Job[ 'Date' ],
                $Job[ 'Customer_ID' ],
                $Job[ 'Location_ID' ],
                $Job[ 'Unit_ID' ],
                $Job[ 'Job_Type_ID' ],
                $Job[ 'Status' ],
                $Job[ 'Notes' ]
              )
            );
            sqlsrv_next_result( $result );
            $Job[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
            header( 'Location: job.php?ID=' . $Job[ 'ID' ] );
            exit;
          } else {
            \singleton\database::getInstance( )->query(
              null,
              " UPDATE  Job
                SET Job.Owner = ?,
                		Job.Loc = ?,
                		Job.Elev = ?,
                		Job.Type = ?,
                		Job.fDesc = ?,
                		Job.fDate = ?,
                    Job.Remarks = ?
                WHERE   Job.ID = ?;",
              array(
                $Job[ 'Customer_ID' ],
                $Job[ 'Location_ID' ],
                $Job[ 'Unit_ID' ],
              	$Job[ 'Job_Type_ID' ],
                $Job[ 'Name' ],
                $Job[ 'Date' ],
                $Job[ 'Notes' ],
                $Job[ 'ID' ]
              )
            );
          }
        }
?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php
    $_GET[ 'Bootstrap' ] = '5.1';
    $_GET[ 'Entity_CSS' ] = 1;
  ?>
  <?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php' );?>
    <?php require( bin_js   . 'index.php' );?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body>
  <div id="wrapper">
    <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
    <div id="page-wrapper" class='content' style='height:100%;overflow-y:scroll;'>
      <div class='card card-primary'><form action='job.php?ID=<?php echo $Job[ 'ID' ];?>' method='POST'>
        <input type='hidden' name='ID' value='<?php echo $Job[ 'ID' ];?>' />
        <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Job', 'Jobs', $Job[ 'ID' ] );?>
        <div class='card-body bg-dark text-white'>
          <div class='row g-0' data-masonry='{"percentPosition": true }'>
            <?php \singleton\bootstrap::getInstance( )->card_map( 'job_map', $Job[ 'Name' ], $Job[ 'Location_Latitude' ], $Job[ 'Location_Longitude' ] );?>
          <div class='card card-primary my-3 col-12 col-lg-3'>
            <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' );?>
            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infom ation' ] == 0 ? "style='display:none;'" : null;?>>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', $Job[ 'Name' ] );?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Date', $Job[ 'Date' ] );?>
              <?php
                $result = \singleton\database::getInstance( )->query(
                  null,
                  " SELECT  Job_Type.ID   AS ID,
                            Job_Type.Type AS Name
                    FROM    JobType AS Job_Types;"
                );
                $Job_Types = array();
                if( $result ){while ( $row = sqlsrv_fetch_array( $result ) ){ $Job_Types[ $row[ 'ID' ] ] = $row[ 'Name' ]; } }
                \singleton\bootstrap::getInstance( )->card_row_form_select( 'Type', $Job[ 'Job_Type_ID' ], $Job_Types );
              ?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Customer', 'Customers', $Job[ 'Customer_ID' ], $Job[ 'Customer_Name' ] );?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Location', 'Locations', $Job[ 'Location_ID' ], $Job[ 'Location_Name' ] );?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Unit', 'Units', $Job[ 'Unit_ID' ], $Job[ 'Unit_Name' ] );?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_textarea( 'Notes', $Job[ 'Notes' ] );?>
            </div>
          </div>
          <div class='card card-primary my-3 col-12 col-lg-3'>
            <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Job', $Job[ 'ID' ] );?>
            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Job_ID=' . $Job[ 'ID' ] );?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Job[ 'Tickets_Open' ], true, true, 'tickets.php?Job_ID=' . $Job[ 'ID' ] . '&Status=0');?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Job[ 'Tickets_Assigned' ], true, true, 'tickets.php?Job_ID=' . $Job[ 'ID' ]. '&Status=1');?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En Route', $Job[ 'Tickets_En_Route' ], true, true, 'tickets.php?Job_ID=' . $Job[ 'ID' ] . '&Status=2');?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Site', $Job[ 'Tickets_On_Site' ], true, true, 'tickets.php?Job_ID=' . $Job[ 'ID' ] . '&Status=3');?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Job[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Job_ID=' . $Job[ 'ID' ] . '&Status=6');?>
            </div>
          </div>
          <div class='card card-primary my-3 col-12 col-lg-3'>
            <?php \singleton\bootstrap::getInstance( )->card_header( 'Invoices', 'Invoice', 'Invoices', 'Job', $Job[ 'ID' ] );?>
            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'invoices.php?Job_ID=' . $Job[ 'ID' ] );?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Job[ 'Invoices_Open' ], true, true, 'invoices.php?Job_ID=' . $Job[ 'ID' ] . '&Status=0');?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Job[ 'Invoices_Closed' ], true, true, 'invoices.php?Job_ID=' . $Job[ 'ID' ] . '&Status=1');?>
            </div>
          </div>
          <div class='card card-primary my-3 col-12 col-lg-3'>
            <?php \singleton\bootstrap::getInstance( )->card_header( 'Contracts', 'Contract', 'Contracts', 'Job', $Job[ 'ID' ] );?>
            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Contracts' ] ) && $_SESSION[ 'Cards' ][ 'Contracts' ] == 0 ? "style='display:none;'" : null;?>>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'contracts.php?Job_ID=' . $Job[ 'ID' ] );?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Job[ 'Contracts_Open' ], true, true, 'contracts.php?Job_ID=' . $Job[ 'ID' ] . '&Status=0');?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Job[ 'Contracts_Closed' ], true, true, 'contracts.php?Job_ID=' . $Job[ 'ID' ] . '&Status=1');?>
            </div>
          </div>
          <div class='card card-primary my-3 col-12 col-lg-3'>
            <?php \singleton\bootstrap::getInstance( )->card_header( 'Proposals', 'Proposal', 'Proposals', 'Job', $Job[ 'ID' ] );?>
            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'proposals.php?Job_ID=' . $Job[ 'ID' ] );?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Job[ 'Proposals_Open' ], true, true, 'proposals.php?Job_ID=' . $Job[ 'ID' ] . '&Status=0');?>
              <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Job[ 'Proposals_Closed' ], true, true, 'proposals.php?Job_ID=' . $Job[ 'ID' ] . '&Status=1');?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
}
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($Job[ 'ID' ]) || !is_numeric($Job[ 'ID' ])) ? "s.php" : ".php?ID={$Job[ 'ID' ]}";?>";</script></head></html><?php }?>
