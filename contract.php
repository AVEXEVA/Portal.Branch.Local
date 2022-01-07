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
      ||  !isset( $Privileges[ 'Contract' ] )
      || 	!check( privilege_read, level_group, $Privileges[ 'Contract' ] )
  ){ ?><?php require('404.html');?><?php }
  else {
    \singleton\database::getInstance( )->query(
      null,
      " INSERT INTO Activity([User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        date('Y-m-d H:i:s'),
        'contract.php'
      )
    );
    $ID = isset( $_GET[ 'ID' ] )
			? $_GET[ 'ID' ]
			: (
				isset( $_POST[ 'ID' ] )
					? $_POST[ 'ID' ]
					: null
			);
    $result = \singleton\database::getInstance( )->query(
      null,
      "	   SELECT 	  [Contract].[ID]         AS ID,
                      [Contract].[Owner]      AS Customer_ID,
                      [Customer].[Name]       AS Customer_Name,
                      [Contract].[Loc]        AS Location_ID,
                      [Location].[Tag]        AS Location_Name,
                      [Contract].[Job]        AS Job_ID,
                      [Job].[fDesc]           AS Job_Name,
                      [Contract].[Review]     AS Review,
                      [Contract].[Disc1]      AS Discount_1,
                      [Contract].[Disc2]      AS Discount_2,
                      [Contract].[Disc3]      AS Discount_3,
                      [Contract].[Disc4]      AS Discount_4,
                      [Contract].[Disc5]      AS Discount_5,
                      [Contract].[Disc6]      AS Discount_6,
                      [Contract].[DiscType]   AS Discount_Type,
                      [Contract].[DiscRate]   AS Discount_Rate,
                      [Contract].[BCycle]     AS Billing_Cycle,
                      [Contract].[BStart]     AS Billing_Start,
                      [Contract].[BLenght]    AS Billing_Length,
                      [Contract].[BFinish]    AS Billing_Finish,
                      [Contract].[BAmt]       AS Billing_Amount,
                      [Contract].[BEscType]   AS Billing_Escalation_Type,
                      [Contract].[BEscCycle]  AS Billing_Escalation_Cycle,
                      [Contract].[BEscFact]   AS Billing_Escalation_Factor,
                      [Contract].[SCycle]     AS Scheduling_Cycle,
                      [Contract].[SType]      AS Scheduling_Type,
                      [Contract].[SDay]       AS Scheduling_Day,
                      [Contract].[SDate]      AS Scheduling_Date,
                      [Contract].[STime]      AS Scheduling_Time,
                      [Contract].[SWE]        AS Scheduling_Weekends,
                      [Contract].[SStart]     AS Scheduling_Start,
                      [Contract].[Detail]     AS Detail,
                      [Contract].[Cycle]      AS Cycle,
                      [Contract].[EscLast]    AS Escalation_Last,
                      [Contract].[OldAmt]     AS Old_Amount,
                      [Contract].[WK]         AS Week,
                      [Contract].[Skill]      AS Skill,
                      [Contract].[Status]     AS Status,
                      [Contract].[Hours]      AS Hours,
                      [Contract].[Hour]       AS Hour,
                      [Contract].[Terms]      AS Terms,
                      [Contract].[OffService] AS Off_Service,
                      CASE    WHEN Invoices.[Open] IS NULL THEN 0
                              ELSE Invoices.[Open] END AS Invoices_Open,
                      CASE    WHEN Invoices.[Closed] IS NULL THEN 0
                              ELSE Invoices.[Closed] END AS Invoices_Closed,
                      CASE    WHEN Tickets.Unassigned IS NULL THEN 0
                              ELSE Tickets.Unassigned END AS Tickets_Open,
                      CASE    WHEN Tickets.Assigned IS NULL THEN 0
                              ELSE Tickets.Assigned END AS Tickets_Assigned,
                      CASE    WHEN Tickets.En_Route IS NULL THEN 0
                              ELSE Tickets.En_Route END AS Tickets_En_Route,
                      CASE    WHEN Tickets.On_Site IS NULL THEN 0
                              ELSE Tickets.On_Site END AS Tickets_On_Site,
                      CASE    WHEN Tickets.Reviewing IS NULL THEN 0
                              ELSE Tickets.Reviewing END AS Tickets_Reviewing
            FROM      dbo.[Contract]
                      LEFT JOIN Job AS Job      ON Job.ID = Contract.Job
                      LEFT JOIN Loc AS Location ON Location.Loc = Contract.Loc
                      LEFT JOIN (
                        SELECT  Owner.ID AS ID,
                                Rol.Name AS Name
                        FROM    Owner
                                LEFT JOIN Rol   ON Owner.Rol = Rol.ID
                      ) AS Customer             ON Customer.ID = Contract.Owner
                      LEFT JOIN (
                        SELECT    Job.ID AS Job,
                                  [Open].Count AS [Open],
                                  [Closed].Count AS Closed
                        FROM      Job AS Job
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
                      ) AS Invoices ON Invoices.Job = Job.ID
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
                    ) AS Tickets ON Tickets.Job = Contract.Job
            WHERE   	[Contract].ID = ?;",
      array(
        $ID
      )
    );
    $Contract =   (       empty( $ID )
                    &&    !$result
                  ) || (  empty( $ID ) )
                  ? array(
                      'ID' => null,
                      //Foreign Keys
                      'Customer_ID' => isset( $_GET[ 'Customer_ID' ] ) ? $_GET[ 'Customer_ID' ] : null,
                      'Customer_Name' => isset( $_GET[ 'Customer_Name' ] ) ? $_GET[ 'Customer_Name' ] : null,
                      'Location_ID' => isset( $_GET[ 'Location_ID' ] ) ? $_GET[ 'Location_ID' ] : null,
                      'Location_Name' => isset( $_GET[ 'Location_Name' ] ) ? $_GET[ 'Location_Name' ] : null,
                      'Job_ID' => isset( $_GET[ 'Job_ID' ] ) ? $_GET[ 'Job_ID' ] : null,
                      'Job_Name' => isset( $_GET[ 'Job_Name' ] ) ? $_GET[ 'Job_Name' ] : null,
                      //Columns
                      'Review' => null,
                      'Discount_1' => null,
                      'Discount_2' => null,
                      'Discount_3' => null,
                      'Discount_4' => null,
                      'Discount_5' => null,
                      'Discount_6' => null,
                      'Tickets_Open' => isset( $_GET[ 'Tickets_Open' ] ) ? $_GET[ 'Tickets_Open' ] : null,
                      'Tickets_Assigned' => isset( $_GET[ 'Tickets_Assigned' ] ) ? $_GET[ 'Tickets_Assigned' ] : null,
                      'Tickets_En_Route' => isset( $_GET[ 'Tickets_En_Route' ] ) ? $_GET[ 'Tickets_En_Route' ] : null,
                      'Tickets_On_Site' => isset( $_GET[ 'Tickets_On_Site' ] ) ? $_GET[ 'Tickets_On_Site' ] : null,
                      'Tickets_Reviewing' => isset( $_GET[ 'Tickets_Reviewing' ] ) ? $_GET[ 'Tickets_Reviewing' ] : null,
                      'Discount_Type' => isset( $_GET[ 'Discount_Type' ] ) ? $_GET[ 'Discount_Type' ] : null,
                      'Discount_Rate' => isset( $_GET[ 'Discount_Rate' ] ) ? $_GET[ 'Discount_Rate' ] : null,
                      'Billing_Cycle' => isset( $_GET[ 'Billing_Cycle' ] ) ? $_GET[ 'Billing_Cycle' ] : null,
                      'Billing_Start' => isset( $_GET[ 'Billing_Start' ] ) ? $_GET[ 'Billing_Start' ] : null,
                      'Billing_Length' => isset( $_GET[ 'Billing_Length' ] ) ? $_GET[ 'Billing_Length' ] : null,
                      'Billing_Finish' => isset( $_GET[ 'Billing_Finish' ] ) ? $_GET[ 'Billing_Finish' ] : null,
                      'Billing_Amount' => isset( $_GET[ 'Billing_Amount' ] ) ? $_GET[ 'Billing_Amount' ] : null,
                      'Billing_Escalation_Type' => isset( $_GET[ 'Billing_Escalation_Type' ] ) ? $_GET[ 'Billing_Escalation_Type' ] : null,
                      'Billing_Escalation_Cycle' => isset( $_GET[ 'Billing_Escalation_Cycle' ] ) ? $_GET[ 'Billing_Escalation_Cycle' ] : null,
                      'Billing_Escalation_Factor' => isset( $_GET[ 'Billing_Escalation_Factor' ] ) ? $_GET[ 'Billing_Escalation_Factor' ] : null,
                      'Invoices_Open' => isset( $_GET[ 'Invoices_Open' ] ) ? $_GET[ 'Invoices_Open' ] : null,
                      'Invoices_Closed' => isset( $_GET[ 'Invoices_Closed' ] ) ? $_GET[ 'Invoices_Closed' ] : null,
                      'Scheduling_Cycle' => null,
                      'Scheduling_Type' => null,
                      'Scheduling_Day' => null,
                      'Scheduling_Date' => null,
                      'Scheduling_Time' => null,
                      'Scheduling_Weekends' => null,
                      'Scheduling_Start' => null,
                      'Detail' => null,
                      'Cycle' => null,
                      'Escalation_Last' => null,
                      'Old_Amount' => null,
                      'Week' => null,
                      'Skill' => null,
                      'Status' => null,
                      'Hours' => null,
                      'Hour' => null,
                      'Terms' => null,
                      'Off_Service' => null
                    )
                  : sqlsrv_fetch_array($result);
    if( isset( $_POST ) && count( $_POST ) > 0 ){
      //Foreign Keys
      $Contract[ 'Customer_ID' ]                = isset( $_POST[ 'Customer_ID' ] )               ? $_POST[ 'Customer_ID' ]                                               : $Contract[ 'Customer_ID' ];
      $Contract[ 'Customer_Name' ]              = isset( $_POST[ 'Customer_Name' ] )             ? $_POST[ 'Customer_Name' ]                                             : $Contract[ 'Customer_Name' ];
      $Contract[ 'Location_ID' ]                = isset( $_POST[ 'Location_ID' ] )               ? $_POST[ 'Location_ID' ]                                               : $Contract[ 'Location_ID' ];
      $Contract[ 'Location_Name' ]              = isset( $_POST[ 'Location_Name' ] )             ? $_POST[ 'Location_Name' ]                                             : $Contract[ 'Location_Name' ];
      $Contract[ 'Job_ID' ]                     = isset( $_POST[ 'Job_ID' ] )                    ? $_POST[ 'Job_ID' ]                                                    : $Contract[ 'Job_ID' ];
      $Contract[ 'Job_Name' ]                   = isset( $_POST[ 'Job_Name' ] )                  ? $_POST[ 'Job_Name' ]                                                  : $Contract[ 'Job_Name' ];
      //Columns
      $Contract[ 'Review' ]                     = isset( $_POST[ 'Review' ] ) 	                 ? $_POST[ 'Review' ] 	                                                 : $Contract[ 'Review' ];
      $Contract[ 'Discount_1' ] 	              = isset( $_POST[ 'Discount_1' ] ) 	             ? $_POST[ 'Discount_1' ] 	                                             : $Contract[ 'Discount_1' ];
      $Contract[ 'Discount_2' ] 	              = isset( $_POST[ 'Discount_2' ] ) 	             ? $_POST[ 'Discount_2' ] 	                                             : $Contract[ 'Discount_2' ];
      $Contract[ 'Discount_3' ] 		            = isset( $_POST[ 'Discount_3' ] ) 	             ? $_POST[ 'Discount_3' ] 	                                             : $Contract[ 'Discount_3' ];
      $Contract[ 'Discount_4' ] 		            = isset( $_POST[ 'Discount_4' ] ) 	             ? $_POST[ 'Discount_4' ] 	                                             : $Contract[ 'Discount_4' ];
      $Contract[ 'Discount_5' ] 			          = isset( $_POST[ 'Discount_5' ] ) 	             ? $_POST[ 'Discount_5' ] 	                                             : $Contract[ 'Discount_5' ];
      $Contract[ 'Discount_6' ] 	              = isset( $_POST[ 'Discount_6' ] )                ? $_POST[ 'Discount_6' ]                                                : $Contract[ 'Discount_6' ];
      $Contract[ 'Discount_Type' ] 	            = isset( $_POST[ 'Discount_Type' ] )             ? $_POST[ 'Discount_Type' ]                                             : $Contract[ 'Discount_Type' ];
      $Contract[ 'Discount_Rate' ] 	            = isset( $_POST[ 'Discount_Rate' ] )             ? $_POST[ 'Discount_Rate' ]                                             : $Contract[ 'Discount_Rate' ];
      $Contract[ 'Billing_Cycle' ] 	            = isset( $_POST[ 'Billing_Cycle' ] )             ? $_POST[ 'Billing_Cycle' ]                                             : $Contract[ 'Billing_Cycle' ];
      $Contract[ 'Billing_Start' ] 	            = isset( $_POST[ 'Billing_Start' ] )             ? date( 'Y-m-d 00:00:00.000', strtotime( $_POST[ 'Billing_Start' ] ) )  : $Contract[ 'Billing_Start' ];
      $Contract[ 'Billing_Length' ] 	          = isset( $_POST[ 'Billing_Length' ] )            ? $_POST[ 'Billing_Length' ]                                            : $Contract[ 'Billing_Length' ];
      $Contract[ 'Billing_Finish' ] 	          = isset( $_POST[ 'Billing_Finish' ] )            ? date( 'Y-m-d 00:00:00.000', strtotime( $_POST[ 'Billing_Finish' ] ) ) : $Contract[ 'Billing_Finish' ];
      $Contract[ 'Billing_Amount' ] 	          = isset( $_POST[ 'Billing_Amount' ] )            ? $_POST[ 'Billing_Amount' ]                                            : $Contract[ 'Billing_Amount' ];
      $Contract[ 'Billing_Escalation_Cycle' ] 	= isset( $_POST[ 'Billing_Escalation_Cycle' ] )  ? $_POST[ 'Billing_Escalation_Cycle' ]                                  : $Contract[ 'Billing_Escalation_Cycle' ];
      $Contract[ 'Billing_Escalation_Factor' ] 	= isset( $_POST[ 'Billing_Escalation_Factor' ] ) ? $_POST[ 'Billing_Escalation_Factor' ]                                 : $Contract[ 'Billing_Escalation_Factor' ];
      $Contract[ 'Scheduling_Cycle' ] 	        = isset( $_POST[ 'Scheduling_Cycle' ] )          ? $_POST[ 'Scheduling_Cycle' ]                                          : $Contract[ 'Scheduling_Cycle' ];
      $Contract[ 'Scheduling_Day' ] 	          = isset( $_POST[ 'Scheduling_Day' ] )            ? $_POST[ 'Scheduling_Day' ]                                            : $Contract[ 'Scheduling_Day' ];
      $Contract[ 'Scheduling_Date' ] 	          = isset( $_POST[ 'Scheduling_Date' ] )           ? $_POST[ 'Scheduling_Date' ]                                           : $Contract[ 'Scheduling_Date' ];
      $Contract[ 'Scheduling_Time' ] 	          = isset( $_POST[ 'Scheduling_Time' ] )           ? $_POST[ 'Scheduling_Time' ]                                           : $Contract[ 'Scheduling_Time' ];
      $Contract[ 'Scheduling_Weekends' ] 	      = isset( $_POST[ 'Scheduling_Weekends' ] )       ? $_POST[ 'Scheduling_Weekends' ]                                       : $Contract[ 'Scheduling_Weekends' ];
      $Contract[ 'Scheduling_Start' ] 	        = isset( $_POST[ 'Scheduling_Start' ] )          ? $_POST[ 'Scheduling_Start' ]                                          : $Contract[ 'Scheduling_Start' ];
      $Contract[ 'Detail' ] 	                  = isset( $_POST[ 'Detail' ] )                    ? $_POST[ 'Detail' ]                                                    : $Contract[ 'Detail' ];
      $Contract[ 'Cycle' ] 	                    = isset( $_POST[ 'Cycle' ] )                     ? $_POST[ 'Cycle' ]                                                     : $Contract[ 'Cycle' ];
      $Contract[ 'Escalation_Last' ] 	          = isset( $_POST[ 'Escalation_Last' ] )           ? $_POST[ 'Escalation_Last' ]                                           : $Contract[ 'Escalation_Last' ];
      $Contract[ 'Old_Amount' ] 	              = isset( $_POST[ 'Old_Amount' ] )                ? $_POST[ 'Old_Amount' ]                                                : $Contract[ 'Old_Amount' ];
      $Contract[ 'Week' ] 	                    = isset( $_POST[ 'Week' ] )                      ? $_POST[ 'Week' ]                                                      : $Contract[ 'Week' ];
      $Contract[ 'Skill' ] 	                    = isset( $_POST[ 'Skill' ] )                     ? $_POST[ 'Skill' ]                                                     : $Contract[ 'Skill' ];
      $Contract[ 'Status' ]                     = isset( $_POST[ 'Status' ] )                    ? $_POST[ 'Status' ]                                                    : $Contract[ 'Status' ];
      $Contract[ 'Hours' ] 	                    = isset( $_POST[ 'Hours' ] )                     ? $_POST[ 'Hours' ]                                                     : $Contract[ 'Hours' ];
      $Contract[ 'Hour' ] 	                    = isset( $_POST[ 'Hour' ] )                      ? $_POST[ 'Hour' ]                                                      : $Contract[ 'Hour' ];
      $Contract[ 'Terms' ] 	                    = isset( $_POST[ 'Terms' ] )                     ? $_POST[ 'Terms' ]                                                     : $Contract[ 'Terms' ];
      $Contract[ 'Off_Service' ] 	              = isset( $_POST[ 'Off_Service' ] )               ? $_POST[ 'Off_Service' ]                                               : $Contract[ 'Off_Service' ];
      /*$Contract[ 'TFMID' ] 	= isset( $_POST[ 'TFMID' ] ) ? $_POST[ 'TFMID' ] : $Contract[ 'TFMID' ];
      $Contract[ 'TFMSource' ] 	= isset( $_POST[ 'TFMSource' ] ) ? $_POST[ 'TFMSource' ] : $Contract[ 'TFMSource' ];
      $Contract[ 'sDay2' ] 	= isset( $_POST[ 'sDay2' ] ) ? $_POST[ 'sDay2' ] : $Contract[ 'sDay2' ];
      $Contract[ 'sTime2' ] 	= isset( $_POST[ 'sTime2' ] ) ? $_POST[ 'sTime2' ] : $Contract[ 'sTime2' ];
      $Contract[ 'sWE2' ] 	= isset( $_POST[ 'sWE2' ] ) ? $_POST[ 'sWE2' ] : $Contract[ 'sWE2' ];*/
      if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
        $result = \singleton\database::getInstance( )->query(
          null,
          "	INSERT INTO Contract(
              Owner,
              Loc,
              Job,
              Review,
              BCycle,
              BStart,
              Blenght,
              Bfinish,
              BAmt,
              EscLast,
              BEscType,
              BEscCycle,
              BEscFact,
              SWE
            )
            VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
            SELECT SCOPE_IDENTITY( );",
          array(
            !empty( $Contract[ 'Customer_ID' ] ) ? $Contract[ 'Customer_ID' ] : null,
            !empty( $Contract[ 'Location_ID' ] ) ? $Contract[ 'Location_ID' ] : null,
            !empty( $Contract[ 'Job_ID' ] ) ? $Contract[ 'Job_ID' ] : null,
            !empty( $Contract[ 'Review' ] ) ? $Contract[ 'Review' ] : null,
            !empty( $Contract[ 'Billing_Cycle' ] ) ? $Contract[ 'Billing_Cycle' ] : null,
            !empty( $Contract[ 'Billing_Start' ] ) ? $Contract[ 'Billing_Start' ] : null,
            !empty( $Contract[ 'Billing_Length' ] ) ? $Contract[ 'Billing_Length' ] : null,
            !empty( $Contract[ 'Billing_Finish' ] ) ? $Contract[ 'Billing_Finish' ] : null,
            !empty( $Contract[ 'Billing_Amount' ] ) ? $Contract[ 'Billing_Amount' ] : null,
            !empty( $Contract[ 'Billing_Escalation_Last' ] ) ? $Contract[ 'Billing_Escalation_Last' ] : date( 'Y-m-d h:i:s' ),
            !empty( $Contract[ 'Billing_Escalation_Type' ] ) ? $Contract[ 'Billing_Escalation_Type' ] : null,
            !empty( $Contract[ 'Billing_Escalation_Cycle' ] ) ? $Contract[ 'Billing_Escalation_Cycle' ] : null,
            !empty( $Contract[ 'Billing_Escalation_Factor' ] ) ? $Contract[ 'Billing_Escalation_Factor' ] : null,
            !empty( $Contract[ 'Starting_Weekday' ] ) ? $Contract[ 'Starting_Weekday' ] : 0
          )
        );
        sqlsrv_next_result( $result );
        $Contract[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
        header( 'Location: contract.php?ID=' . $Contract[ 'ID' ] );
        exit;
      } else {
        \singleton\database::getInstance( )->query(
          null,
          "	UPDATE 	Contract
            SET 	Contract.Owner = ?,
                  Contract.Loc = ?,
                  Contract.Job = ?,
                  Contract.BCycle = ?,
                  Contract.BStart = ?,
                  Contract.BLenght = ?,
                  Contract.BFinish = ?,
                  Contract.BAmt = ?,
                  Contract.EscLast = ?,
                  Contract.BEscCycle = ?,
                  Contract.BEscFact = ?
            WHERE Contract.ID = ?;",
          array(
            !empty( $Contract[ 'Customer_ID' ] ) ? $Contract[ 'Customer_ID' ] : null,
            !empty( $Contract[ 'Location_ID' ] ) ? $Contract[ 'Location_ID' ] : null,
            !empty( $Contract[ 'Job_ID' ] ) ? $Contract[ 'Job_ID' ] : null,
            !empty( $Contract[ 'Billing_Cycle' ] ) ? $Contract[ 'Billing_Cycle' ] : null,
            !empty( $Contract[ 'Billing_Start' ] ) ? $Contract[ 'Billing_Start' ] : null,
            !empty( $Contract[ 'Billing_Length' ] ) ? $Contract[ 'Billing_Length' ] : null,
            !empty( $Contract[ 'Billing_Finish' ] ) ? $Contract[ 'Billing_Finish' ] : null,
            !empty( $Contract[ 'Billing_Amount' ] ) ? $Contract[ 'Billing_Amount' ] : null,
            !empty( $Contract[ 'Escalation_Last' ] ) ? date( 'Y-m-d h:i:s', strtotime( $Contract[ 'Escalation_Last' ] ) ) : null,
            !empty( $Contract[ 'Billing_Escalation_Cycle' ] ) ? $Contract[ 'Billing_Escalation_Cycle' ] : null,
            !empty( $Contract[ 'Billing_Escalation_Factor' ] ) ? $Contract[ 'Billing_Escalation_Factor' ] : null,
            $Contract[ 'ID' ]
          )
        );
      }
    }
?><!DOCTYPE html>
<html lang='en'>
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php
    $_GET[ 'Bootstrap' ] = '5.1';
    $_GET[ 'Entity_CSS' ] = 1;
    require( bin_meta . 'index.php');
    require( bin_css  . 'index.php');
    require( bin_js   . 'index.php');
  ?>
</head>
<body>
  <div id="wrapper">
    <?php require( bin_php . 'element/navigation.php' ); ?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'>
        <form action='contract.php?ID=<?php echo $Contract[ 'ID' ];?>' method='POST'>
          <input type='hidden' name='ID' value='<?php echo $Contract[ 'ID' ];?>' />
          <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Contract', 'Contracts', $Contract[ 'ID' ] );?>
          <div class='card-body bg-dark text-white'>
            <div class='row g-0' data-masonry='{"percentPosition": true }'>
						  <div class='card card-primary my-3 col-12 col-lg-3'>
							 <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' ); ?>
						 	  <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Information' ] ) && $_SESSION[ 'Cards' ][ 'Information' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Customer', 'Customers', $Contract[ 'Customer_ID' ], $Contract[ 'Customer_Name' ] );?>
								  <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Location', 'Locations', $Contract[ 'Location_ID' ], $Contract[ 'Location_Name' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Job', 'Jobs', $Contract[ 'Job_ID' ], $Contract[ 'Job_Name' ] );?>
                </div>
						  </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Accounting' ); ?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Billing' ] ) && $_SESSION[ 'Cards' ][ 'Billing' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php
                    \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Billing_Start', $Contract[ 'Billing_Start' ], 'Start' );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Billing_Finish', $Contract[ 'Billing_Finish' ], 'Finish' );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_number( 'Billing_Length', $Contract[ 'Billing_Length' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_select( 'Cycle', $Contract[ 'Billing_Cycle' ],  array( 0 => 'Monthly', 1 => 'Bi-Monthly', 2 => 'Quarterly', 3 => 'Trimester', 4 => 'Semi-Annually', 5 => 'Annually', 6 => 'Never' ) );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_currency( 'Billing_Amount', $Contract[ 'Billing_Amount' ] );
                  ?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Escalation' ); ?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Escalation' ] ) && $_SESSION[ 'Cards' ][ 'Escalation' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php
                    \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Escalation_Last', $Contract[ 'Escalation_Last' ], 'Escalated' );
                    \singleton\bootstrap::getInstance( )->card_row_form_select( 'Cycle',  $Contract[ 'Billing_Escalation_Cycle' ],  array( 0 => 'Monthly', 1 => 'Bi-Monthly', 2 => 'Quarterly', 3 => 'Trimester', 4 => 'Semi-Annually', 5 => 'Annually', 6 => 'Never' ) );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_number( 'Billing_Escalation_Factor', $Contract[ 'Billing_Escalation_Factor' ], 'Factor' );
                  ?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Job_ID', $Contract[ 'Job_ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Job_ID=' . $Contract[ 'Job_ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Contract[ 'Tickets_Open' ], true, true, 'tickets.php?Job_ID=' . $Contract[ 'Job_ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Contract[ 'Tickets_Assigned' ], true, true, 'tickets.php?Job_ID=' . $Contract[ 'Job_ID' ] ) . '&Status=1';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En Route', $Contract[ 'Tickets_En_Route' ], true, true, 'tickets.php?Job_ID=' . $Contract[ 'Job_ID' ] ) . '&Status=2';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Site', $Contract[ 'Tickets_On_Site' ], true, true, 'tickets.php?Job_ID=' . $Contract[ 'Job_ID' ] ) . '&Status=3';?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Contract[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Job_ID=' . $Contract[ 'Job_ID' ] ) . '&Status=6';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Invoices', 'Invoice', 'Invoices', 'Job_ID', $Contract[ 'Job_ID' ] );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $Contract[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'invoices.php?Job_ID=' . $Contract[ 'Job_ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Contract[ 'Invoices_Open' ], true, true, 'invoices.php?Job_ID=' . $Contract[ 'Job_ID' ] . '&Status=0');?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Closed', $Contract[ 'Invoices_Closed' ], true, true, 'invoices.php?Job_ID=' . $Contract[ 'Job_ID' ] ) . '&Status=1';?>
                </div>
              </div>
              <div class='card card-primary my-3 col-12 col-lg-3'>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Collections' ] ) && $_SESSION[ 'Cards' ][ 'Collections' ] == 0 ? "style='display:none;'" : null;?> style='display:none;'>
                <?php if(isset($Privileges['Collection']) && $Privileges['Collection']['Customer'] >= 4) {?>
                <div class='row g-0'>
                    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Balance</div>
                    <div class='col-6'><input class='form-control' type='text' readonly name='Balance' value='<?php
                    $r = \singleton\database::getInstance( )->query(null,
                      " SELECT Sum( OpenAR.Balance ) AS Balance
                        FROM   OpenAR
                           LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
                        WHERE  Location.Owner = ?
                    ;",array($Contract[ 'Owner' ]));
                    $Balance = $r ? sqlsrv_fetch_array($r)['Balance'] : 0;
                    echo money_format('%(n',$Balance);
                  ?>' /></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
                <?php }?>
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
} else {?><html><head><script>document.location.href="../login.php?Forward=contract<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
