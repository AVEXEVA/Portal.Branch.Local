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
          "	   SELECT 	  [Contract].[ID] AS ID,
                          [Contract].[Owner] AS Customer_ID,
                          [Customer].[Name] AS Customer_Name,
                          [Contract].[Loc] AS Location_ID,
                          [Location].[Tag] AS Location_Name,
                          [Contract].[Job] AS Job_ID,
                          [Job].[fDesc] AS Job_Name,
                          [Contract].[Review] AS Review,
                          [Contract].[Disc1] AS Discount_1,
                          [Contract].[Disc2] AS Discount_2,
                          [Contract].[Disc3] AS Discount_3,
                          [Contract].[Disc4] AS Discount_4,
                          [Contract].[Disc5] AS Discount_5,
                          [Contract].[Disc6] AS Discount_6,
                          [Contract].[DiscType] AS Discount_Type,
                          [Contract].[DiscRate] AS Discount_Rate,
                          [Contract].[BCycle] AS Billing_Cycle,
                          [Contract].[BStart] AS Billing_Start,
                          [Contract].[BLenght] AS Billing_Length,
                          [Contract].[BFinish] AS Billing_Finish,
                          [Contract].[BAmt] AS Billing_Amount,
                          [Contract].[BEscType] AS Billing_Escalation_Type,
                          [Contract].[BEscCycle] AS Billing_Escalation_Cycle,
                          [Contract].[BEscFact] AS Billing_Escalation_Factor,
                          [Contract].[SCycle] AS Scheduling_Cycle,
                          [Contract].[SType] AS Scheduling_Type,
                          [Contract].[SDay] AS Scheduling_Day,
                          [Contract].[SDate] AS Scheduling_Date,
                          [Contract].[STime] AS Scheduling_Time,
                          [Contract].[SWE] AS Scheduling_Weekends,
                          [Contract].[SStart] AS Scheduling_Start,
                          [Contract].[Detail] AS Detail,
                          [Contract].[Cycle] AS Cycle,
                          [Contract].[EscLast] AS Escalation_Last,
                          [Contract].[OldAmt] AS Old_Amount,
                          [Contract].[WK] AS Week,
                          [Contract].[Skill] AS Skill,
                          [Contract].[Status] AS Status,
                          [Contract].[Hours] AS Hours,
                          [Contract].[Hour] AS Hour,
                          [Contract].[Terms] AS Terms,
                          [Contract].[OffService] AS Off_Service
                FROM      dbo.[Contract]
                          LEFT JOIN Job AS Job ON Job.ID = Contract.Job
                          LEFT JOIN Loc AS Location ON Location.Loc = Contract.Loc
                          LEFT JOIN (
                            SELECT  Owner.ID AS ID,
                                    Rol.Name AS Name
                            FROM    Owner
                                    LEFT JOIN Rol ON Owner.Rol = Rol.ID
                          ) AS Customer ON Customer.ID = Contract.Owner
                WHERE   	    [Contract].ID = ?;",
          array(
            $ID
          )
      );
      //var_dump( sqlsrv_errors( ) );
      $Contract =   (       empty( $ID )
                      &&    !$result
                    ) || (  empty( $ID ) )  ? array(
        'ID' => null,
        'Customer_ID' => null,
        'Customer_Name' => null,
        'Location_ID' => null,
        'Location_Name' => null,
        'Job_ID' => null,
        'Job_Name' => null,
        'Review' => null,
        'Discount_1' => null,
        'Discount_2' => null,
        'Discount_3' => null,
        'Discount_4' => null,
        'Discount_5' => null,
        'Discount_6' => null,
        'Discount_Type' => null,
        'Discount_Rate' => null,
        'Billing_Cycle' => null,
        'Billing_Start' => null,
        'Billing_Length' => null,
        'Billing_Finish' => null,
        'Billing_Amount' => null,
        'Billing_Escalation_Type' => null,
        'Billing_Escalation_Cycle' => null,
        'Billing_Escalation_Factor' => null,
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
      ) : sqlsrv_fetch_array($result);


      if( isset( $_POST ) && count( $_POST ) > 0 ){
        $Contract[ 'ID' ] 		                    = isset( $_POST[ 'ID' ] ) 	                     ? $_POST[ 'ID' ] 	                                                     : $Contract[ 'ID' ];
        $Contract[ 'Job_Name' ] 	                = isset( $_POST[ 'Job' ] )                       ? $_POST[ 'Job' ]                                                       : $Contract[ 'Job_Name' ];
        $Contract[ 'Location_Name' ] 		          = isset( $_POST[ 'Location' ] ) 	               ? $_POST[ 'Location' ] 	                                               : $Contract[ 'Location_Name' ];
        $Contract[ 'Customer_Name' ] 		          = isset( $_POST[ 'Customer' ] ) 	               ? $_POST[ 'Customer' ] 	                                               : $Contract[ 'Customer_Name' ];
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
            "	DECLARE @Customer INT;
              DECLARE @Location INT;
              DECLARE @Job INT;
              SET @Customer = ( SELECT Top 1 Customer.ID FROM Owner AS Customer LEFT JOIN Rol ON Customer.Rol = Rol.ID WHERE Rol.Name = ? AND Rol.Type = 0 );
              SET @Location = ( SELECT Top 1 Location.Loc FROM Loc AS Location WHERE Location.Tag = ? AND Location.Owner = @Customer );
              SET @Job = ( SELECT Top 1 Job.ID FROM Job WHERE Job.fDesc = ? AND Job.Owner = @Customer AND Job.Loc = @Location );
              INSERT INTO Contract(
                [Job],
                [Loc],
                [Owner],
                [Review],
                [Disc1],
                [Disc2],
                [Disc3],
                [Disc4],
                [Disc5],
                [Disc6],
                [DiscType],
                [DiscRate],
                [BCycle],
                [BStart],
                [BLenght],
                [BFinish],
                [BAmt],
                [BEscType],
                [BEscCycle],
                [BEscFact],
                [SCycle],
                [SType],
                [SDay],
                [SDate],
                [STime],
                [SWE],
                [SStart],
                [Detail],
                [Cycle],
                [EscLast],
                [OldAmt],
                [WK],
                [Skill],
                [Status],
                [Hours],
                [Hour],
                [Terms],
                [OffService],
                [TFMID],
                [TFMSource]
              )
              VALUES( @Job, @Location, @Customer, " . implode( ',', array_fill( 0, 35, '?' ) ) . ", ' ', ' ' );
              SELECT SCOPE_IDENTITY( );",
            array(
              $Contract[ 'Customer_Name' ],
              $Contract[ 'Location_Name'],
              $Contract[ 'Job_Name' ],
              $Contract[ 'Review' ],
              $Contract[ 'Discount_1' ],
              $Contract[ 'Discount_2' ],
              $Contract[ 'Discount_3' ],
              $Contract[ 'Discount_4' ],
              $Contract[ 'Discount_5' ],
              $Contract[ 'Discount_6' ],
              $Contract[ 'Discount_Type' ],
              $Contract[ 'Discount_Rate' ],
              $Contract[ 'Billing_Cycle' ],
              $Contract[ 'Billing_Start' ],
              $Contract[ 'Billing_Length' ],
              $Contract[ 'Billing_Finish' ],
              $Contract[ 'Billing_Amount' ],
              $Contract[ 'Billing_Escalation_Type' ],
              $Contract[ 'Billing_Escalation_Cycle' ],
              $Contract[ 'Billing_Escalation_Factor' ],
              $Contract[ 'Scheduling_Cycle' ],
              $Contract[ 'Scheduling_Type' ],
              $Contract[ 'Scheduling_Day' ],
              $Contract[ 'Scheduling_Date' ],
              $Contract[ 'Scheduling_Time' ],
              is_null( $Contract[ 'Scheduling_Weekends' ] ) ? 0 : $Contract[ 'Scheduling_Weekends' ],
              $Contract[ 'Scheduling_Start' ],
              $Contract[ 'Detail' ],
              $Contract[ 'Cycle' ],
              is_null( $Contract[ 'Escalation_Last' ] ) ? '1969-12-30 00:00:00.000' : null,
              $Contract[ 'Old_Amount' ],
              $Contract[ 'Week'],
              $Contract[ 'Skill' ],
              $Contract[ 'Status' ],
              $Contract[ 'Hours' ],
              $Contract[ 'Hour' ],
              $Contract[ 'Terms' ],
              $Contract[ 'Off_Service']
            )
          );
          sqlsrv_next_result( $result );
          $Contract[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];

          header( 'Location: contract.php?ID=' . $Contract[ 'ID' ] );
          exit;
        } else {
          \singleton\database::getInstance( )->query(
            null,
            "	DECLARE @Customer INT;
              DECLARE @Location INT;
              DECLARE @Job INT;
              SET @Customer = ( SELECT Top 1 Owner.ID FROM Owner LEFT JOIN Rol ON Owner.Rol = Rol.ID WHERE Rol.Name = ? );
              SET @Location = ( SELECT Top 1 Location.Loc FROM Loc AS Location WHERE Location.Tag = ? AND Location.Owner = @Customer );
              SET @Job = ( SELECT Top 1 Job.ID FROM Job WHERE Job.fDesc = ? AND Job.Owner = @Customer AND Job.Loc = @Location );
              UPDATE 	Contract
              SET 	Contract.Owner = @Customer,
                    Contract.Loc = @Location,
                    Contract.Job = @Job,
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
              $Contract[ 'Customer_Name' ],
              $Contract[ 'Location_Name' ],
              $Contract[ 'Job_Name' ],
              $Contract[ 'Billing_Cycle' ],
              $Contract[ 'Billing_Start' ],
              $Contract[ 'Billing_Length' ],
              $Contract[ 'Billing_Finish' ],
              $Contract[ 'Billing_Amount' ],
              $Contract[ 'Escalation_Last' ],
              $Contract[ 'Billing_Escalation_Cycle' ],
              $Contract[ 'Billing_Escalation_Factor' ],
              $Contract[ 'ID' ]
            )
          );
        }
      }
?><!DOCTYPE html>
<html lang='en'>
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
    <?php require( bin_php . 'element/navigation.php' ); ?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'><form action='contract.php?ID=<?php echo $Contract[ 'ID' ];?>' method='POST'>
        <input type='hidden' name='ID' value='<?php echo $Contract[ 'ID' ];?>' />
        <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Contract', 'Contracts', $Contract[ 'ID' ] );?>
        <div class='card-body bg-dark text-white'>
					<div class='row g-0'>
						<div class='card card-primary my-3 col-12 col-lg-3'>
							<?php \singleton\bootstrap::getInstance( )->card_header( 'Information' ); ?>
						 	<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Information' ] ) && $_SESSION[ 'Cards' ][ 'Information' ] == 0 ? "style='display:none;'" : null;?>>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Customer', 'Customers', $Contract[ 'Customer_ID' ], $Contract[ 'Customer_Name' ] );?>
								<?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Location', 'Locations', $Contract[ 'Location_ID' ], $Contract[ 'Location_Name' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Job', 'Jobs', $Contract[ 'Job_ID' ], $Contract[ 'Job_Name' ] );?>
              </div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'>&nbsp;</div>
                  </div>
              </div>
						</div>
            <div class='card card-primary my-3 col-12 col-lg-3'>
              <?php \singleton\bootstrap::getInstance( )->card_header( 'Accounting' ); ?>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Billing' ] ) && $_SESSION[ 'Cards' ][ 'Billing' ] == 0 ? "style='display:none;'" : null;?>>
                <?php 
                  \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Billing_Start', $Contract[ 'Billing_Start' ], 'Start' );
                  \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Billing_Finish', $Contract[ 'Billing_Finish' ], 'Finish' );
                  \singleton\bootstrap::getInstance( )->card_row_form_input( 'Length', $Contract[ 'Billing_Length' ] );
                ?>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Email( 1 );?> Length:</div>
                  <div class='col-8'><input placeholder='monthes' type='text' class='form-control edit' name='Billing_Length' value='<?php echo $Contract[ 'Billing_Length' ];?>' /></div>
                </div>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_select( 'Cycle', 'Billing_Cycle', $Contract[ 'Billing_Cycle' ],  array(
                  0 => 'Monthly',
                  1 => 'Bi-Monthly',
                  2 => 'Quarterly',
                  3 => 'Trimester',
                  4 => 'Semi-Annually',
                  5 => 'Annually',
                  6 => 'Never'
                ) );?>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Amount:</div>
                  <div class='col-8'><input placeholder='$$$' type='text' class='form-control edit' name='Billing_Amount' value='<?php echo $Contract[ 'Billing_Amount' ];?>' /></div>
                </div>
              </div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'>&nbsp;</div>
                  </div>
              </div>
            </div>
            <div class='card card-primary my-3 col-12 col-lg-3'>
              <?php \singleton\bootstrap::getInstance( )->card_header( 'Escalation' ); ?>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Escalation' ] ) && $_SESSION[ 'Cards' ][ 'Escalation' ] == 0 ? "style='display:none;'" : null;?>>
                <input type='hidden' name='ID' value='<?php echo $Contract[ 'ID' ];?>' />
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Escalation_Last', $Contract[ 'Escalation_Last' ], 'Escalated' );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_select( 'Cycle', 'Billing_Escalation_Cycle', $Contract[ 'Billing_Cycle' ],  array(
                  0 => 'Monthly',
                  1 => 'Bi-Monthly',
                  2 => 'Quarterly',
                  3 => 'Trimester',
                  4 => 'Semi-Annually',
                  5 => 'Annually',
                  6 => 'Never'
                ) );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_number( 'Billing_Escalation_Factor', $Contract[ 'Billing_Escalation_Factor' ], 'Factor' );?>
              </div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'>&nbsp;</div>
                  </div>
              </div>
            </div>
            <div class='card card-primary my-3 col-12 col-lg-3'>
              <?php \singleton\bootstrap::getInstance( )->card_header( 'Invoices' ); ?>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Invoices', 'Invoices', 'invoices.php?Job=' . $Contract[ 'Job_Name' ] );?>
                <?php if(isset($Privileges['Invoice']) ) {?>
                <div class='row g-0'>
                  <div class='col-1'>&nbsp;</div>
                    <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Open</div>
                    <div class='col-6'><input class='form-control' type='text' readonly name='Collections' value='<?php
                    $r = \singleton\database::getInstance( )->query(null,"
                      SELECT Count( OpenAR.Ref ) AS Count
                      FROM   OpenAR
                           LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
                      WHERE  OpenAR.Job = ?
                    ;",array($Contract[ 'Job_ID' ]));
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
                      SELECT  Count( Invoice.Ref ) AS Count
                      FROM    Invoice
                            LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
                      WHERE     Invoice.Job = ?
                          AND Invoice.Ref NOT IN ( SELECT Ref FROM OpenAR )
                    ;",array($Contract[ 'Job_ID' ]));
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
			</div>
		</div>
	</div>
</div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=contract<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
