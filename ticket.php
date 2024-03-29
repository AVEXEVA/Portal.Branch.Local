<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
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
        ||  !isset( $Privileges[ 'Ticket' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Ticket' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
            null,
            "   INSERT INTO Activity([User], [Date], [Page] )
                VALUES( ?, ?, ? );",
            array(
                $_SESSION[ 'Connection' ][ 'User' ],
                date('Y-m-d H:i:s'),
                'ticket.php?ID=' . ( isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null )
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
            "	SELECT 	Top 1
            			Ticket.*
            	FROM    (
            			    (
                                SELECT  TicketO.ID                      AS ID,
                                        TicketO.WorkOrder               AS Work_Order,
                                        /*Job*/
                                        Job.ID                          AS Job_ID,
                                        JobType.ID                      AS Job_Type_Id,
                                        /*ForeignKeys*/
                                        /*Customer*/
                                        Customer.ID                     AS Customer_ID,
                                        Customer.Name                   AS Customer_Name,
                                        /*Route*/
                                        Route.ID                        AS Route_ID,
                                        Route.Name                      AS Route_Name,
                                        /*Territory*/
                                        Territory.ID                    AS Territory_ID,
                                        Territory.Name                  AS Territory_Name,
                                        /*Level*/
                                        TicketO.Level                   AS Level_ID,
                                        CASE    WHEN TicketO.Level = 0  THEN  ''
                                                WHEN TicketO.Level = 1  THEN 'Service Call'
                                                WHEN TicketO.Level = 2  THEN 'Trucking'
                                                WHEN TicketO.Level = 3  THEN 'Modernization'
                                                WHEN TicketO.Level = 4  THEN 'Violations'
                                                WHEN TicketO.Level = 5  THEN 'Level 5'
                                                WHEN TicketO.Level = 6  THEN 'Repair'
                                                WHEN TicketO.Level = 7  THEN 'Annual'
                                                WHEN TicketO.Level = 8  THEN 'Escalator'
                                                WHEN TicketO.Level = 9  THEN 'Email'
                                                WHEN TicketO.Level = 10 THEN 'Maintenance'
                                                WHEN TicketO.Level = 11 THEN 'Survey'
                                                WHEN TicketO.Level = 12 THEN 'Engineering'
                                                WHEN TicketO.Level = 13 THEN 'Support',
                                                WHEN TicketO.Level = 14 THEN 'M&R'
                                        END AS Level,
                                        /*GPS*/
                                        TicketO.gpsStatus               AS GPS_Status,
                                        TicketO.fLong                   AS Longitude,
                                        TicketO.Latt                    AS Latitude,
                                        TicketO.City                    AS City,
                                        TicketO.State                   AS State,
                                        TicketO.Zip                     AS Zip,
                                        /*Unit*/
                                        TicketO.LElev                   AS Unit_ID,
                                        TicketO.fGroup                  AS Unit_Group,
                                        /*Location*/
                                        TicketO.LID                     AS Location_ID,
                                        TicketO.LType                   AS Location_Type,
                                        TicketO.LDesc1                  AS Location_Street,
                                        TicketO.LDesc2                  AS Location_city,
                                        TicketO.LDesc3                  AS Location_state,
                                        TicketO.LDesc4                  AS Location_Zip,
                                        /*Employee*/
                                        TicketO.fWork                   AS Field_ID,
                                        TicketO.DWork                   AS Employee_CallSign,
                                        /*Contact*/
                                        TicketO.idRolCustomContact      AS Contact_ID,
                                        TicketO.Who                     AS Contact_Name,
                                        TicketO.CPhone                  AS Contact_Phone,
                                        TicketO.CallIn                  AS Called_In,
                                        TicketO.Phone                   AS Phone,
                                        TicketO.Phone2                  AS Phone_Alternate,
                                        /*WeakKeys*/
                                        TicketO.fBy                     AS Dispatcher_Name,
                                        /*Dates*/
                                        TicketO.CDate                   AS Created,
                                        TicketO.DDate                   AS Dispatched,
                                        TicketO.EDate                   AS Last,
                                        /*Times*/
                                        TicketO.TimeRoute               AS Time_En_Route,
                                        TicketO.TimeSite                AS Time_On_Site,
                                        TicketO.TimeComp                AS Time_Completed,
                                        TicketO.Est                     AS Time_Estimate,
                                        /*Scope*/
                                        TicketO.Nature                  AS Nature,
                                        TicketO.Assigned                AS Status,
                                        TicketO.High                    AS Priority,
                                        TicketO.Confirmed               AS Confirmed,
                                        TicketO.Cat                     AS Category,
                                        TicketO.Notes                   AS Notes,
                                        TicketO.fDesc                   AS Description,
                                        TicketO.BRemarks                AS Remarks,
                                        TicketO.Follow                  AS Follow_Up,
                                        TicketO.Locked                  AS Locked,
                                        TicketO.Comments                AS Comments,
                                        /*Sales*/
                                        TicketO.PriceL                  AS Price_Level,
                                        TicketO.SpecType                AS Special_Type,
                                        TicketO.SpecID                  AS Special_ID,
                                        /*Customs*/
                                        TicketO.Custom1                 AS Custom1,
                                        TicketO.Custom2                 AS Custom2,
                                        TicketO.Custom3                 AS Custom3,
                                        TicketO.Custom4                 AS Custom4,
                                        TicketO.Custom5                 AS Custom5,
                                        TicketO.Custom6                 AS Custom6,
                                        TicketO.Custom7                 AS Custom7,
                                        TicketO.Custom8                 AS Custom8,
                                        TicketO.Custom9                 AS Custom9,
                                        TicketO.Custom10                AS Custom10,
                                        TicketO.TFMCustom1              AS TFMCustom1,
                                        TicketO.TFMCustom2              AS TFMCustom2,
                                        TicketO.TFMCustom3              AS TFMCustom3,
                                        TicketO.TFMCustom4              AS TFMCustom4,
                                        TicketO.TFMCustom5              AS TFMCustom5                                       
                                        /*Other*/
                                        TicketO.EN                      AS EN,
                                        TicketO.SMile                   AS SMile,
                                        TicketO.EMile                   AS EMile,
                                        /*Platform*/
                                        TicketO.AID                     AS AID,
                                        TicketO.Source                  AS Source,
                                        TicketO.ResolveSource           AS Source,
                                        TicketO.Internet                AS Internet,
                                        TicketO.HandHeldFieldsUpdated   AS Fields_Updated,
                            ) UNION ALL (
                                SELECT  TicketD.ID                 AS ID,
                                        TicketD.WorkOrder          AS Work_Order_ID,
                                        /*ForeignKeys*/
                                        /*Job*/
                                        Job.ID                     AS Job_ID,
                                        JobType.ID                 AS Job_Type_ID,
                                        /*Location*/
                                        Location.Loc               AS Location_ID,
                                        Location.Tag               AS Location_Tag,
                                        /*Unit*/
                                        Unit.ID                    AS Unit_ID,
                                        Unit.State                 AS Unit_Name,
                                        /*Employee*/
                                        Employee.ID                AS Employee_ID,
                                        Employee.fWork             AS Employee_Work_ID,
                                        /*Customer*/
                                        Customer.ID                AS Customer_ID,
                                        Customer.Name              AS Customer_Name,
                                        /*Route*/
                                        Route.ID                   AS Route_ID,
                                        Route.Name                 AS Route_Name,
                                        /*Territory*/
                                        Territory.ID               AS Territory_ID,
                                        Territory.Name             AS Territory_Name,
                                        /*Invoice*/
                                        Invoice.Ref                AS Invoice_ID,
                                        /*Contact*/
                                        Contact.ID                 AS Contact_ID,
                                        Contact.Contact            AS Contact_Name,
                                        /*Inspection*/
                                        Inspection.ID              AS Inspection_ID,
                                        /*WeakKeys*/
                                        TicketD.fBy                AS Dispatcher_Name,
                                        TicketD.RBy                AS Reciever_Name,
                                        TicketD.CPhone             AS Caller_Phone,
                                        /*Dates*/
                                        TicketD.CDate              AS Date_Created,
                                        TicketD.DDate              AS Date_Dispatched,
                                        TicketD.EDate              AS Date_Worked,
                                        /*Times*/
                                        TicketD.CTime              AS Time_Created,
                                        TicketD.DTime              AS Time_Dispatched,
                                        TicketD.ETime              AS Time_Worked,
                                        TicketD.TimeRoute          AS Time_En_Route,
                                        TicketD.TimeSite           AS Time_On_Site,
                                        TicketD.TimeComp           AS Time_Completed,
                                        TicketD.Est                AS Time_Estimate,
                                        TicketD.StartBreak         AS Time_Break_Start,
                                        TicketD.EndBreak           AS Time_Break_End,
                                        /*Hours*/
                                        TicketD.Total              AS Hours_Total,
                                        TicketD.Reg                AS Hours_Regular,
                                        TicketD.OT                 AS Hours_Overtime,
                                        TicketD.DT                 AS Hours_Doubletime,
                                        TicketD.TT                 AS Hours_Traveltime,
                                        TicketD.downtime           AS Hours_Downtime,
                                        /*Scope*/
                                        TicketD.Cat                AS Category,
                                        TicketD.Recommendations    AS Recommendations,
                                        TicketD.Status             AS Status,
                                        TicketD.fDesc              AS Description,
                                        TicketD.Level              AS Level_ID,
                                        TicketD.DescRes            AS Resolution,
                                        TicketD.Comments           AS Comments,
                                        TicketD.PartsUsed          AS Parts_Used,
                                        TicketD.WorkComplete       AS Work_Complete,
                                        /*Signature*/
                                        TicketD.SignatureText      AS Signature_Name,
                                        /*Traveling*/
                                        TicketD.RegTrav            AS Regular_Travel,
                                        TicketD.OTTrav             AS Overtime_Travel,
                                        TicketD.DTTrav             AS Doubletime_Travel,
                                        TicketD.NTTrav             AS Nighttime_Travel,
                                        /*Expenses*/
                                        TicketD.Zone               AS Expenses_Zone,
                                        TicketD.OtherE             AS Expenses_Other,
                                        /*Sales*/
                                        TicketD.Charge             AS Chargeable,
                                        TicketD.PriceL             AS Price_Level,
                                        TicketD.BRemarks           AS Remarks,
                                        TicketD.BReview            AS Reviewed,
                                        TicketD.ManualInvoice      AS Manual_Invoice,
                                        /*Accounting*/
                                        TicketD.WageC              AS Wage_ID,
                                        TicketD.ClearCheck         AS Checked,
                                        TicketD.ClearPR            AS Payrolled,
                                        TicketD.PRWBR              AS Payroll_Wage,
                                        /*GPS*/
                                        TicketD.fLong              AS Longitude,
                                        TicketD.Latt               AS Latitude,
                                        /*Other*/
                                        TicketD.CauseID            AS Cause_ID,
                                        TicketD.CauseDesc          AS Cause_Description,
                                        /*Unknown*/
                                        TicketD.SMile              AS SMile,
                                        TicketD.EMile              AS EMile,
                                        TicketD.AID                AS AID,
                                        /*Platform*/
                                        TicketD.Source             AS Source,
                                        TicketD.Internet           AS Internet,
                                        TicketD.Email              AS Emailed,
                                        TicketD.ResolveSource      AS Resolve_Source,
                                        /*Custom*/
                                        TicketD.Custom1            AS Custom1,
                                        TicketD.Custom2            AS Custom2,
                                        TicketD.Custom3            AS Custom3,
                                        TicketD.Custom4            AS Custom4,
                                        TicketD.Custom5            AS Custom5,
                                        TicketD.Custom6            AS Custom6,
                                        TicketD.Custom7            AS Custom7,
                                        TicketD.Custom8            AS Custom8,
                                        TicketD.Custom9            AS Custom9,
                                        TicketD.Custom10           AS Custom10,
                                        TicketD.TFMCustom1         AS TFMCustom1,
                                        TicketD.TFMCustom2         AS TFMCustom2,
                                        TicketD.TFMCustom3         AS TFMCustom3,
                                        TicketD.TFMCustom4         AS TFMCustom4,
                                        TicketD.TFMCustom5         AS TFMCustom5
                                FROM    TicketD
                                        LEFT JOIN Job               AS Job        ON TicketD.Job                = Job.ID
                                        LEFT JOIN (
                                            SELECT  Owner.ID        AS ID,
                                                    Rolodex.Name    AS Name
                                            FROM    Owner
                                                    LEFT JOIN Rol   AS Rolodex    ON Owner.Rol                  = Rolodex.ID
                                        )                           AS Customer   ON Customer.ID                = Job.Owner
                                        LEFT JOIN Loc               AS Location   ON Job.Loc                    = Location.Loc
                                        LEFT JOIN Elev              AS Unit       ON TicketD.Elev               = Unit.ID
                                        LEFT JOIN Route             AS Route      ON Location.Route             = Route.ID
                                        LEFT JOIN Terr              AS Territory  ON Location.Terr              = Territory.ID
                                        LEFT JOIN Emp               AS Employee   ON TicketD.fWork              = Employee.fWork
                                        LEFT JOIN Rol               AS Contact    ON TicketD.idRolCustomContact = Contact.ID
                                        LEFT JOIN Invoice           AS Invoice    ON TicketD.Invoice            = Invoice.ID
                            )	
                        ) AS Ticket
            	WHERE  Ticket.ID = ?;",
            array(
            	$ID
            )
        );
        $Customer = empty( $ID )
            ?   array(
                    'ID' => null,
                    'Name' => null,
                    'Login' => null,
                    'Password' => null,
                    'Geofence' => null,
                    'Type' => null,
                    'Status' => null,
                    'Website' => null,
                    'Internet' => null,
                    'Street' => null,
                    'City' => null,
                    'State' => null,
                    'Zip' => null,
                    'Latitude' => null,
                    'Longitude' => null,
                    'Phone'   =>  null,
                    'Email'   =>  null,
                    'Rolodex' => null,
                    'Phone' => null,
                    'Email' => null
                ) 
            : sqlsrv_fetch_array($result);
        if( isset( $_POST ) && count( $_POST ) > 0 ){
        	$Customer[ 'Name' ] 		= isset( $_POST[ 'Name' ] ) 	 ? $_POST[ 'Name' ] 	 : $Customer[ 'Name' ];
            $Customer[ 'Contact' ] 	= isset( $_POST[ 'Contact' ] ) ? $_POST[ 'Contact' ] : $Customer[ 'Contact' ];
        	$Customer[ 'Phone' ] 		= isset( $_POST[ 'Phone' ] ) 	 ? $_POST[ 'Phone' ] 	 : $Customer[ 'Phone' ];
        	$Customer[ 'Email' ] 		= isset( $_POST[ 'Email' ] ) 	 ? $_POST[ 'Email' ] 	 : $Customer[ 'Email' ];
        	$Customer[ 'Login' ] 		= isset( $_POST[ 'Login' ] ) 	 ? $_POST[ 'Login' ] 	 : $Customer[ 'Login' ];
        	$Customer[ 'Password' ] = isset( $_POST[ 'Password' ] )  ? $_POST[ 'Password' ]  : $Customer[ 'Password' ];
        	$Customer[ 'Geofence' ] = isset( $_POST[ 'Geofence' ] )  ? $_POST[ 'Geofence' ]  : $Customer[ 'Geofence' ];
        	$Customer[ 'Type' ]     = isset( $_POST[ 'Type' ] ) 	   ? $_POST[ 'Type' ] 	   : $Customer[ 'Type' ];
        	$Customer[ 'Status' ] 	= isset( $_POST[ 'Status' ] ) 	 ? $_POST[ 'Status' ] 	 : $Customer[ 'Status' ];
        	$Customer[ 'Website' ] 	= isset( $_POST[ 'Website' ] ) 	 ? $_POST[ 'Website' ] 	 : $Customer[ 'Website' ];
        	$Customer[ 'Internet' ] = isset( $_POST[ 'Internet' ] )  ? $_POST[ 'Internet' ]  : $Customer[ 'Internet' ];
        	$Customer[ 'Street' ] 	= isset( $_POST[ 'Street' ] ) 	 ? $_POST[ 'Street' ] 	 : $Customer[ 'Street' ];
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
        				VALUES( @MAXID + 1 , 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
        				SELECT @MAXID + 1;",
        			array(
        				$Customer[ 'Name' ],
        				$Customer[ 'Status' ],
        				$Customer[ 'Website' ],
        				$Customer[ 'Street' ],
        				$Customer[ 'City' ],
        				$Customer[ 'State' ],
        				$Customer[ 'Zip' ],
        				$Customer[ 'Latitude' ],
        				$Customer[ 'Longitude' ],
        				$Customer[ 'Geofence' ]
        			)
        		);
        		sqlsrv_next_result( $result );
        		$Customer[ 'Rolodex' ] = sqlsrv_fetch_array( $result )[ 0 ];

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

        		sqlsrv_next_result( $result );
        		$Customer[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];

        		header( 'Location: ticket.php?ID=' . $Customer[ 'ID' ] );
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
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php'); ?>
        <div id="page-wrapper" class='content'>
        	<div class='card card-primary'>
              <form action='ticket.php?ID=<?php echo $Customer[ 'ID' ];?>' method='POST'>
                <input type='hidden' name='ID' value='<?php echo $Customer[ 'ID' ];?>' />
                <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Customer', 'Customers', $Customer[ 'ID' ] );?>
                <div class="card-body bg-dark text-white">
                  <div class='row g-0' data-masonry='{"percentPosition": true }'>
                    <div class='card card-primary my-3 col-12 col-lg-3'>
                      <?php \singleton\bootstrap::getInstance( )->card_header( 'Ticket' );?>
                      <div class='card-body bg-dark text-white'>
                        <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', $Customer[ 'Name' ] );?>
                        <?php \singleton\bootstrap::getInstance( )->card_row_form_select( 'Type', $Customer[ 'Type' ], array( 0 => 'General', 1 => 'Bank', 2 => 'Churches', 3 => 'Commercial', 4 => 'General', 5 => 'Property Manage', 6 => 'Restaraunts', 7 => 'Schools' ) );?>
                        <?php \singleton\bootstrap::getInstance( )->card_row_form_select( 'Status', $Customer[ 'Status' ], array( 0 => 'Inactive', 1 => 'Active') );?>
                        <?php \singleton\bootstrap::getInstance( )->card_row_form_input_url( 'Website', $Customer[ 'Website' ] );?>
                        <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Address', 'https://maps.google.com/?q=' . $Customer['Street'].' '.$Customer['City'].' '.$Customer[ 'State' ].' '.$Customer[ 'Zip' ] ); ?>
                        <?php \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Street', $Customer[ 'Street' ] );?>
                        <?php \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'City', $Customer[ 'City' ] ); ?>
                        <?php \singleton\bootstrap::getInstance( )->card_row_form_select_sub( 'State', $Customer[ 'State' ],  array( 'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming' ) ); ?>
                        <?php \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Zip', $Customer[ 'Zip' ] ); ?>
                      </div>
                  </div>
                <div class='card card-primary my-3 col-12 col-lg-3'>
						<div class='card card-primary my-3'><form action='ticket.php?ID=<?php echo $Customer[ 'ID' ];?>' method='POST'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
									<div class='col-2'>&nbsp;</div>
								</div>
							</div>
						 	<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
						 		<input type='hidden' name='ID' value='<?php echo $Customer[ 'ID' ];?>' />
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Name:</div>
									<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Customer['Name'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Type:</div>
									<div class='col-8'><select name='Type' class='form-control edit'>
										<option value=''>Select</option>
										<option value='General' <?php echo $Customer[ 'Type' ] == 'General' ? 'selected' : null;?>>General</option>
										<option value='Bank' <?php echo $Customer[ 'Type' ] == 'Bank' ? 'selected' : null;?>>Bank</option>
										<option value='Churches' <?php echo $Customer[ 'Type' ] == 'Churches' ? 'selected' : null;?>>Churches</option>
										<option value='Commercial' <?php echo $Customer[ 'Type' ] == 'Commercial' ? 'selected' : null;?>>Commercial</option>
										<option value='Hospitals' <?php echo $Customer[ 'Type' ] == 'Hospitals' ? 'selected' : null;?>>General</option>
										<option value='Property Manage' <?php echo $Customer[ 'Type' ] == 'Property Manage' ? 'selected' : null;?>>Property Manage</option>
										<option value='Restaraunts' <?php echo $Customer[ 'Type' ] == 'General' ? 'selected' : null;?>>Restaraunts</option>
										<option value='Schools' <?php echo $Customer[ 'Type' ] == 'Schools' ? 'selected' : null;?>>Schools</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
									<div class='col-8'><select name='Status' class='form-control edit <?php echo $Customer[ 'Status' ] == 1 ? 'bg-warning' : 'bg-success';?>'>
										<option value=''>Select</option>
										<option value='0' <?php echo $Customer[ 'Status' ] == 1 ? 'selected' : null;?>>Inactive</option>
										<option value='1' <?php echo $Customer[ 'Status' ] == 0 ? 'selected' : null;?>>Active</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Web(1);?> Website:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Website' value='<?php echo strlen($Customer['Website']) > 0 ?  $Customer['Website'] : "&nbsp;";?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Address:</div>
									<div class='col-6'></div>
									<div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Street:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Street' value='<?php echo $Customer['Street'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>City:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='City' value='<?php echo $Customer['City'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>State:</div>
									<div class='col-8'><select class='form-control edit' name='State'>
										<option value=''>Select</option>
										<option <?php echo $Customer[ 'State' ] == 'AL' ? 'selected' : null;?> value='AL'>Alabama</option>
										<option <?php echo $Customer[ 'State' ] == 'AK' ? 'selected' : null;?> value='AK'>Alaska</option>
										<option <?php echo $Customer[ 'State' ] == 'AZ' ? 'selected' : null;?> value='AZ'>Arizona</option>
										<option <?php echo $Customer[ 'State' ] == 'AR' ? 'selected' : null;?> value='AR'>Arkansas</option>
										<option <?php echo $Customer[ 'State' ] == 'CA' ? 'selected' : null;?> value='CA'>California</option>
										<option <?php echo $Customer[ 'State' ] == 'CO' ? 'selected' : null;?> value='CO'>Colorado</option>
										<option <?php echo $Customer[ 'State' ] == 'CT' ? 'selected' : null;?> value='CT'>Connecticut</option>
										<option <?php echo $Customer[ 'State' ] == 'DE' ? 'selected' : null;?> value='DE'>Delaware</option>
										<option <?php echo $Customer[ 'State' ] == 'DC' ? 'selected' : null;?> value='DC'>District Of Columbia</option>
										<option <?php echo $Customer[ 'State' ] == 'FL' ? 'selected' : null;?> value='FL'>Florida</option>
										<option <?php echo $Customer[ 'State' ] == 'GA' ? 'selected' : null;?> value='GA'>Georgia</option>
										<option <?php echo $Customer[ 'State' ] == 'HI' ? 'selected' : null;?> value='HI'>Hawaii</option>
										<option <?php echo $Customer[ 'State' ] == 'ID' ? 'selected' : null;?> value='ID'>Idaho</option>
										<option <?php echo $Customer[ 'State' ] == 'IL' ? 'selected' : null;?> value='IL'>Illinois</option>
										<option <?php echo $Customer[ 'State' ] == 'IN' ? 'selected' : null;?> value='IN'>Indiana</option>
										<option <?php echo $Customer[ 'State' ] == 'IA' ? 'selected' : null;?> value='IA'>Iowa</option>
										<option <?php echo $Customer[ 'State' ] == 'KS' ? 'selected' : null;?> value='KS'>Kansas</option>
										<option <?php echo $Customer[ 'State' ] == 'KY' ? 'selected' : null;?> value='KY'>Kentucky</option>
										<option <?php echo $Customer[ 'State' ] == 'LA' ? 'selected' : null;?> value='LA'>Louisiana</option>
										<option <?php echo $Customer[ 'State' ] == 'ME' ? 'selected' : null;?> value='ME'>Maine</option>
										<option <?php echo $Customer[ 'State' ] == 'MD' ? 'selected' : null;?> value='MD'>Maryland</option>
										<option <?php echo $Customer[ 'State' ] == 'MA' ? 'selected' : null;?> value='MA'>Massachusetts</option>
										<option <?php echo $Customer[ 'State' ] == 'MI' ? 'selected' : null;?> value='MI'>Michigan</option>
										<option <?php echo $Customer[ 'State' ] == 'MN' ? 'selected' : null;?> value='MN'>Minnesota</option>
										<option <?php echo $Customer[ 'State' ] == 'MS' ? 'selected' : null;?> value='MS'>Mississippi</option>
										<option <?php echo $Customer[ 'State' ] == 'MO' ? 'selected' : null;?> value='MO'>Missouri</option>
										<option <?php echo $Customer[ 'State' ] == 'MT' ? 'selected' : null;?> value='MT'>Montana</option>
										<option <?php echo $Customer[ 'State' ] == 'NE' ? 'selected' : null;?> value='NE'>Nebraska</option>
										<option <?php echo $Customer[ 'State' ] == 'NV' ? 'selected' : null;?> value='NV'>Nevada</option>
										<option <?php echo $Customer[ 'State' ] == 'NH' ? 'selected' : null;?> value='NH'>New Hampshire</option>
										<option <?php echo $Customer[ 'State' ] == 'NJ' ? 'selected' : null;?> value='NJ'>New Jersey</option>
										<option <?php echo $Customer[ 'State' ] == 'NM' ? 'selected' : null;?> value='NM'>New Mexico</option>
										<option <?php echo $Customer[ 'State' ] == 'NY' ? 'selected' : null;?> value='NY'>New York</option>
										<option <?php echo $Customer[ 'State' ] == 'NC' ? 'selected' : null;?> value='NC'>North Carolina</option>
										<option <?php echo $Customer[ 'State' ] == 'ND' ? 'selected' : null;?> value='ND'>North Dakota</option>
										<option <?php echo $Customer[ 'State' ] == 'OH' ? 'selected' : null;?> value='OH'>Ohio</option>
										<option <?php echo $Customer[ 'State' ] == 'OK' ? 'selected' : null;?> value='OK'>Oklahoma</option>
										<option <?php echo $Customer[ 'State' ] == 'OR' ? 'selected' : null;?> value='OR'>Oregon</option>
										<option <?php echo $Customer[ 'State' ] == 'PA' ? 'selected' : null;?> value='PA'>Pennsylvania</option>
										<option <?php echo $Customer[ 'State' ] == 'RI' ? 'selected' : null;?> value='RI'>Rhode Island</option>
										<option <?php echo $Customer[ 'State' ] == 'SC' ? 'selected' : null;?> value='SC'>South Carolina</option>
										<option <?php echo $Customer[ 'State' ] == 'SD' ? 'selected' : null;?> value='SD'>South Dakota</option>
										<option <?php echo $Customer[ 'State' ] == 'TN' ? 'selected' : null;?> value='TN'>Tennessee</option>
										<option <?php echo $Customer[ 'State' ] == 'TX' ? 'selected' : null;?> value='TX'>Texas</option>
										<option <?php echo $Customer[ 'State' ] == 'UT' ? 'selected' : null;?> value='UT'>Utah</option>
										<option <?php echo $Customer[ 'State' ] == 'VT' ? 'selected' : null;?> value='VT'>Vermont</option>
										<option <?php echo $Customer[ 'State' ] == 'VA' ? 'selected' : null;?> value='VA'>Virginia</option>
										<option <?php echo $Customer[ 'State' ] == 'WA' ? 'selected' : null;?> value='WA'>Washington</option>
										<option <?php echo $Customer[ 'State' ] == 'WV' ? 'selected' : null;?> value='WV'>West Virginia</option>
										<option <?php echo $Customer[ 'State' ] == 'WI' ? 'selected' : null;?> value='WI'>Wisconsin</option>
										<option <?php echo $Customer[ 'State' ] == 'WY' ? 'selected' : null;?> value='WY'>Wyoming</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Zip:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Zip' value='<?php echo $Customer['Zip'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Latitude:</div>
									<div class='col-8'><input type='text' class='form-control edit <?php echo $Customer[ 'Latitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Latitude' value='<?php echo $Customer['Latitude'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Longitude:</div>
									<div class='col-8'><input type='text' class='form-control edit <?php echo $Customer[ 'Longitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Longitude' value='<?php echo $Customer['Longitude'];?>' /></div>
								</div>
							</div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                  </div>
              </div>
						</form></div>
            <div class='card card-primary my-3'><form action='ticket.php?ID=<?php echo $Customer[ 'ID' ];?>' method='POST'>
                <input type='hidden' name='ID' value='<?php echo $Customer[ 'ID' ];?>' />
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->User( 1 );?><span>Contacts</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
              </div>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Contacts' ] ) && $_SESSION[ 'Cards' ][ 'Contacts' ] == 0 ? "style='display:none;'" : null;?>>
                <div class='row'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->User( 1 );?> Name:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Name' value='<?php echo $Customer[ 'Name' ];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Phone( 1 );?> Phone:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Phone' value='<?php echo $Customer[ 'Phone' ];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Email( 1 );?> Email:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Email' value='<?php echo $Customer[ 'Email' ];?>' /></div>
                </div>
              </div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                  </div>
              </div>
            </form>
            </div>
            <div class='card card-primary my-3'><form action='ticket.php?ID=<?php echo $Customer[ 'ID' ];?>' method='POST'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Privilege( 1 );?><span>Portal</span></h5></div>
									<div class='col-2'>&nbsp;</div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Portal' ] ) && $_SESSION[ 'Cards' ][ 'Portal' ] == 0 ? "style='display:none;'" : null;?>>
						 		<input type='hidden' name='ID' value='<?php echo $Customer[ 'ID' ];?>' />
						 		<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Privilege(1);?> Login:</div>
									<div class='col-6'></div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
						 			<div class='col-3'>Portal:</div>
						 			<div class='col-8'><select class='form-control edit <?php echo $Customer[ 'Internet' ] == 1 ? 'bg-success' : 'bg-warning';?>' name='Internet' >
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
						 			<div class='col-8'><select class='form-control edit <?php echo $Customer[ 'Geofence' ] == 1 ? 'bg-success' : 'bg-warning';?>' name='Geofence' >
						 				<option value=''>Select</option>
						 				<option value='0' <?php echo $Customer[ 'Geofence' ] == 0 ? 'selected' : null;?>>Disabled</option>
						 				<option value='1' <?php echo $Customer[ 'Geofence' ] == 1 ? 'selected' : null;?>>Enabled</option>
						 			</select></div>
						 		</div>
							</div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                  </div>
              </div>
						</form></div>
            <?php
            $r = \singleton\database::getInstance( )->query(
                null,
                "   SELECT  Count(TicketD.ID) AS Count,
                            Substring(TicketD.DescRes, 18, PATINDEX('%-----Notes-----%', TicketD.DescRes ) - 18 ) AS Codes
                    FROM    [TicketD]
                            LEFT JOIN Job ON TicketD.Job = Job.ID
                    WHERE   TicketD.DescRes LIKE '%-----Notes-----%'
                            AND TicketD.EDate >= ?
                            AND Job.Owner = ?
                    GROUP BY TicketD.DescRes
                    ORDER BY Count( TicketD.ID ) DESC;",
                array(
                    date( 'Y-m-d H:i:s', strtotime( '-1 year' ) ),
                    $Customer[ 'ID' ]
                )
            );
            $tResolutionCodes = array( );
            $total = 0;
            while( $rResolutionCodes = sqlsrv_fetch_array( $r ) ){
                if( strpos( $rResolutionCodes['Codes'],  "\n" ) !== false ){
                    $eResolutionCodes = explode("\n", $rResolutionCodes[ 'Codes' ] );
                    while( ( $eResolutionCode = array_pop( $eResolutionCodes ) ) !== null ){
                        $eResolutionCode = trim( $eResolutionCode );
                        $tResolutionCodes[ $eResolutionCode ] = isset( $tResolutionCodes[ $eResolutionCode ] ) ? $tResolutionCodes[ $eResolutionCode ] + $rResolutionCodes[ 'Count' ] : $rResolutionCodes[ 'Count' ];
                        $total += $rResolutionCodes[ 'Count' ];
                    }
                } else {
                    $tResolutionCodes[ trim( $rResolutionCodes[ 'Codes' ] ) ] = isset( $tResolutionCodes[ trim( $rResolutionCodes[ 'Codes' ] ) ] ) ? $tResolutionCodes[ trim( $rResolutionCodes ['Codes' ] ) ] + $rResolutionCodes[ 'Count' ] : $rResolutionCodes ['Count' ];
                    $total += $rResolutionCodes[ 'Count' ];
                }
            }
            $ttResolutionCodes = array();
            foreach( $tResolutionCodes as $key=>$value ){ $ttResolutionCodes[ explode( ' - ', $key )[ 0 ] ] = $value; }
            ?><div class='card card-primary my-3'>
                <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                        <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Privilege( 1 );?><span>Codes</span></h5></div>
                        <div class='col-2'>&nbsp;</div>
                    </div>
                </div>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Codes' ] ) && $_SESSION[ 'Cards' ][ 'Codes' ] == 0 || true ? "style='display:none;'" : null;?>><form action='ticket.php?ID=<?php echo $Customer[ 'ID' ];?>' method='POST'>
                    <div class='row'>
                        <div id='ticket-resolution-codes-pie-chart' class='col-xs-12'><div id='ticketResolutionCodes-flot-pie' style='width:100%;height:350px;'>&nbsp;</div></div>
                        <script>
                            function resize_ticketResolutionCodes(){
                                $('#ticketResolutionCodes-flot-pie').width( 'width', $('#ticketTypes-flot-pie').width( ) + 'px' );
                            }
                            function plotResolutionCodes(){
                                $.plot(
                                    $('#ticketResolutionCodes-flot-pie'),
                                    [
                                        <?php
                                            $pResolutionCodes = array( );
                                            if( count( $ttResolutionCodes ) > 0 ){ foreach( $ttResolutionCodes as $key=>$value ){
                                                $pResolutionCodes[ ] = "{ label : \"" . $key . "\", data : " . $value . ", color: '#" . str_pad(dechex(rand(0x000000, 0x333333)), 6, 0, STR_PAD_LEFT) . "' }";
                                            } }
                                            echo implode( ', ', $pResolutionCodes );
                                        ?>
                                    ],{
                                        series: {
                                            pie: {
                                                show: true
                                            }
                                        },
                                        legend : {
                                            show : false
                                        },
                                        grid: {
                                            hoverable: true
                                        },
                                        tooltip : true,
                                        tooltipOpts: {
                                            cssClass: "flotTip",
                                            content: "%p.0%, %s",
                                            shifts: {
                                                x: 55,
                                                y: 0
                                            },
                                            defaultTheme: false
                                        }
                                    }
                                );
                            }
                            $(document).ready( function() {
                                resize_ticketResolutionCodes( );
                                plotResolutionCodes();
                            });
                            $(window).resize( function(){
                                resize_ticketResolutionCodes( )
                                plotResolutionCodes( );
                            });
                        </script>
                        <div id='ticket-resolution-codes-table' class='col-xs-12 action-rows' style='display:none;'>
                            <div class='row'>
                                <div class='col-xs-6'>Type</div>
                                <div class='col-xs-3'>Count</div>
                                <div class='col-xs-3'>Percent</div>
                            </div>
                            <?php
                                foreach( $tResolutionCodes as $key=>$value ){
                                    if( $key == '' ){ continue; }
                                    ?><div class='row'>
                                        <div class='col-xs-6'><?php echo $key;?></div>
                                        <div class='col-xs-3'><?php echo $value;?></div>
                                        <div class='col-xs-3'><?php echo round( $value / $total * 100, 2 ) . '%';?></div>
                                    </div><?php
                                }
                            ?><script>
                            function hoverType( level ){
                              document.location.href='dashboard.php?Location=<?php
                                echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;
                              ?>&Unit=<?php
                                echo isset($_GET['Unit']) ? $_GET['Unit'] : null;
                              ?>&Assigned=<?php
                                echo isset($_GET['Assigned']) ? $_GET['Assigned'] : null;
                              ?>&Level=' + level;
                            }
                            </script>
                        </div>
                    </div>
                </div>
                <div class='card-footer'><div class='row'><div class='col-xs-12'>&nbsp;</div></div></div>
            </div>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Location( 1 );?><span>Locations</span></h5></div>
									<div class='col-2'>
                    <button class='h-100 w-100' type='button' onClick="document.location.href='locations.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button>
                  </div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Locations' ] ) && $_SESSION[ 'Cards' ][ 'Locations' ] == 0 ? "style='display:none;'" : null;?>>
								<?php
									$result = \singleton\database::getInstance( )->query(
										null,
										"	SELECT 		Count( Location.ID ) AS Count,
														    Location.Maint AS Maintenance
											FROM   		Loc AS Location
											WHERE  		Location.Owner = ?
											GROUP BY 	Location.Maint
											ORDER BY 	Location.Maint DESC;",
										array(
											$Customer[ 'ID' ]
										)
									);
									$Locations = array( );
									if( $result ){while( $row = sqlsrv_fetch_array( $result ) ){ $Locations[ $row[ 'Maintenance' ] ] = $row[ 'Count' ]; } }
								?>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Maintained</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Yes</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Locations' value='<?php
										echo isset( $Locations[ 1 ] ) ? $Locations[ 1 ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>No</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Locations' value='<?php
										echo isset( $Locations[ 0 ] ) ? $Locations[ 0 ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
						</div>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?><span>Units</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
								<?php
									$r = \singleton\database::getInstance( )->query(
										null,
										"	SELECT 	Count( Unit.ID ) AS Units
											FROM   	Elev AS Unit
											   		LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
											WHERE  	Location.Owner = ? ;",
										array(
											$Customer[ 'ID' ]
										)
									);
								?>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Type</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Elevators</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
										$r = \singleton\database::getInstance( )->query(
											null,
											"	SELECT 	Count( Unit.ID ) AS Units
												FROM   	Elev AS Unit
												   		LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
												WHERE  		Location.Owner = ?
														AND Unit.Type = 'Elevator'
										;",array($Customer[ 'ID' ]));
										echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Escalators</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
										$r = \singleton\database::getInstance( )->query(null,"
											SELECT 	Count( Unit.ID ) AS Units
											FROM   	Elev AS Unit
												   	LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
											WHERE  		Location.Owner = ?
													AND Unit.Type = 'Escalator'
										;",array($Customer[ 'ID' ]));
										echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Escalator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Other</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
										$r = \singleton\database::getInstance( )->query(null,"
											SELECT 	Count( Unit.ID ) AS Units
											FROM   	Elev AS Unit
												   	LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
											WHERE  		Location.Owner = ?
													AND Unit.Type NOT IN ( 'Elevator', 'Escalator' )
										;",array($Customer[ 'ID' ]));
										echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' readonly onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
						</div>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Job( 1 );?><span>Jobs</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Jobs' ] ) && $_SESSION[ 'Cards' ][ 'Jobs' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Statuses</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Open</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Jobs' value='<?php
										$r = \singleton\database::getInstance( )->query(null,"
											SELECT Count( Job.ID ) AS Jobs
											FROM   Job
												   LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
											WHERE  		Location.Owner = ?
													AND Job.Type <> 9
													AND Job.Status = 0
										;",array($Customer[ 'ID' ]));
									echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>On Hold</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Jobs' value='<?php
										$r = \singleton\database::getInstance( )->query(null,"
											SELECT Count( Job.ID ) AS Jobs
											FROM   Job
												   LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
											WHERE  Location.Owner = ? AND Job.Status = 2
										;",array($Customer[ 'ID' ]));
									echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Closed</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Jobs' value='<?php
										$r = \singleton\database::getInstance( )->query(null,"
											SELECT Count( Job.ID ) AS Jobs
											FROM   Job
												   LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
											WHERE  		Location.Owner = ?
													AND Job.Type <> 9
													AND Job.Status = 1
										;",array($Customer[ 'ID' ]));
									echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
						</div>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?><span>Tickets</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Statuses</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'><?php
										$r = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Owner = ?
																		AND TicketO.Assigned = 0
															)
														) AS Tickets;",
											array(
												$Customer[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Open</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$r = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Owner = ?
																		AND TicketO.Assigned = 1
															)
														) AS Tickets;",
											array(
												$Customer[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Assigned</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$r = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Owner = ?
																		AND TicketO.Assigned = 2
															)
														) AS Tickets;",
											array(
												$Customer[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>En Route</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$r = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Owner = ?
																		AND TicketO.Assigned = 3
															)
														) AS Tickets;",
											array(
												$Customer[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>On Site</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=3';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$r = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Owner = ?
																		AND TicketO.Assigned = 6
															)
														) AS Tickets;",
											array(
												$Customer[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Review</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=6';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$r = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Owner = ?
																		AND TicketO.Assigned = 4
															)
														) AS Tickets;",
											array(
												$Customer[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Complete</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
						</div>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Violation( 1 );?><span>Violations</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Violations' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Violation(1);?><span>Violations</span></div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Preliminary</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Violations' value='<?php
										$r = \singleton\database::getInstance( )->query(null,"
											SELECT Count( Violation.ID ) AS Violations
											FROM   Violation
												   LEFT JOIN Loc AS Location ON Violation.Loc = Location.Loc
											WHERE  Location.Owner = ?
													AND Violation.Status = 'Preliminary Report'
										;",array($Customer[ 'ID' ]));
										echo $r ? sqlsrv_fetch_array($r)['Violations'] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=Preliminary Report';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Job Created</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Violations' value='<?php
										$r = \singleton\database::getInstance( )->query(null,"
											SELECT Count( Violation.ID ) AS Violations
											FROM   Violation
												   LEFT JOIN Loc AS Location ON Violation.Loc = Location.Loc
											WHERE  Location.Owner = ?
													AND Violation.Status = 'Job Created'
										;",array($Customer[ 'ID' ]));
										echo $r ? sqlsrv_fetch_array($r)['Violations'] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=Preliminary Report';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
						</div>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Proposal( 1 );?><span>Proposals</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Status</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
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
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?><span>Invoices</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='invoices.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Invoices</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['Customer'] >= 4) {?>
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
								<?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['Customer'] >= 4) {?>
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
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Collection( 1 );?><span>Collections</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Collections' ] ) && $_SESSION[ 'Cards' ][ 'Collections' ] == 0 ? "style='display:none;'" : null;?> style='display:none;'>
								<?php if(isset($Privileges['Collection']) && $Privileges['Collection']['Customer'] >= 4) {?>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Balance</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Balance' value='<?php
										$r = \singleton\database::getInstance( )->query(null,"
											SELECT Sum( OpenAR.Balance ) AS Balance
											FROM   OpenAR
												   LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
											WHERE  Location.Owner = ?
										;",array($Customer[ 'ID' ]));
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
			</div>
		</div>
  	</div>

</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($Customer[ 'ID' ]) || !is_numeric($Customer[ 'ID' ])) ? "s.php" : ".php?ID={$Customer[ 'ID' ]}";?>";</script></head></html><?php }?>
