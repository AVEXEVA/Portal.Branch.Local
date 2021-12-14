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
        ||  !isset( $Privileges[ 'Unit' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'User' ] )
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
        " SELECT 	Violation.ID        AS ID,
                  Violation.Name      AS Name,
                  Job.ID              AS Job_ID,
                  Job.fDesc           AS Job_Name,
                  Customer.ID         AS Customer_ID,
                  Customer.Name       AS Customer_Name,
                  Location.Loc        AS Location_ID,
                  Location.Tag        AS Location_Name,
                  Location.Address    AS Location_Street,
                  Location.City       AS Location_City,
                  Location.State      AS Location_State,
                  Location.Zip        AS Location_Zip,
                  Location.Latt       AS Location_Latitude,
                  Location.fLong      AS Location_Longitude,
                  Unit.ID             AS Unit_ID,
                  Unit.State          AS Unit_Name,
                  Unit.State          AS Unit_City_ID,
                  Unit.Unit           AS Unit_Building_ID,
                  Violation.fdate     AS Date,
                  Violation.Status    AS Status,
                  Quote.Ref           AS Quote_ID,
                  Quote.fDesc         AS Quote_Name,
                  Inspection.ID       AS Inspection_ID,
                  Inspection.Type     AS Inspection_Name,
                  Violation.Ticket    AS Ticket_ID,
                  Violation.Ticket    AS Ticket_Name,
                  Violation.Remarks   AS Note,
                  Violation.Estimate  AS Proposal_ID,
                  Violation.Estimate  AS Proposal_Name,
                  Violation.Price     AS Price,
                  Violation.Custom1   AS File_Permit,
                  Violation.Custom2   AS Permit_Approved,
                  Violation.Custom3   AS Date_Sent,
                  Violation.Custom4   AS Forms_to_DOB,
                  Violation.Custom5   AS Inspection,
                  Violation.Custom6   AS Hearing,
                  Violation.Custom7   AS Due_Date,
                  Violation.Custom8   AS Forms_to_Customer,
                  Violation.Custom9   AS Recieved_from_Customer,
                  Violation.Custom10  AS Cancel_Contract,
                  Violation.Custom11  AS Sales,
                  Violation.Custom12  AS Division_2,
                  Violation.Custom13  AS Created,
                  Violation.Custom14  AS Modernization,
                  Violation.Custom15  AS Division_1,
                  Violation.Custom16  AS Division_3,
                  Violation.Custom17  AS Repair,
                  Violation.Custom18  AS Code,
                  Violation.Custom19  AS Division_4,
                  Violation.Custom20  AS Complete,
                  Violation.Custom21  AS Custom21,
                  Violation.Custom22  AS Custom22,
                  Violation.Custom23  AS Custom23,
                  Violation.Custom24  AS Custom24,
                  Violation.Custom25  AS Custom25,
                  Violation.Custom26  AS Custom26,
                  Violation.Custom27  AS Custom27,
                  Violation.Custom28  AS Custom28,
                  Violation.Custom29  AS Custom29,
                  Violation.Custom30  AS Custom30
		      FROM    Violation
                  LEFT JOIN Job                      ON Violation.Job   = Job.ID
                  LEFT JOIN (
                    SELECT  Owner.ID,
                            Rol.Name
                    FROM    Owner
                            LEFT JOIN Rol            ON Owner.Rol       = Rol.ID
                  ) AS Customer                      ON Customer.ID     = Job.Owner
                  LEFT JOIN Loc        AS Location   ON Violation.Loc   = Location.Loc
                  LEFT JOIN Elev       AS Unit       ON Violation.Elev  = Unit.ID
                  LEFT JOIN Quote      AS Quote      ON Violation.Quote = Quote.Ref
                  LEFT JOIN Inspection AS Inspection ON Violation.ID    = Inspection.Violation
        	WHERE   	  Violation.ID = ?
                  OR 	Violation.Name = ?;",
        array(
        	$ID,
        	$Name
        )
      );
      //var_dump( sqlsrv_errors( ) );
      $Violation =   (  empty( $ID )
                   &&  !empty( $Name )
                   &&  !$result
              )    || (empty( $ID )
                   &&  empty( $Name )
              )    ? array(
      	'ID' => null,
        'Name' => null,
        'Customer_ID' => null,
        'Customer_Name' => isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null,
        'Location_ID' => null,
        'Location_Name' => isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null,
        'Location_Street' => null,
        'Location_City' => null,
        'Location_State' => null,
        'Location_Zip' => null,
        'Location_Latitude' => null,
        'Location_Longitude' => null,
        'Unit_ID' => null,
        'Unit_Name' => isset( $_GET[ 'Unit' ] ) ? $_GET[ 'Unit' ] : null,
        'Proposal_ID' => isset( $_GET[ 'Proposal_ID' ] ) ? $_GET[ 'Proposal_ID' ] : null,
        'Proposal_Name' => isset( $_GET[ 'Proposal_Name' ] ) ? $_GET[ 'Proposal_Name' ] : null,
        'Quote_ID' => isset( $_GET[ 'Quote_ID' ] ) ? $_GET[ 'Quote_ID' ] : null,
        'Quote_Name' => isset( $_GET[ 'Quote_Name' ] ) ? $_GET[ 'Quote_Name' ] : null,
        'Job_ID' => isset( $_GET[ 'Job_ID' ] ) ? $_GET[ 'Job_ID' ] : null,
        'Job_Name' => isset( $_GET[ 'Job_Name' ] ) ? $_GET[ 'Job_Name' ] : null,
        'Ticket_ID' => isset( $_GET[ 'Ticket_ID' ] ) ? $_GET[ 'Ticket_ID' ] : null,
        'Inspection_ID' => isset( $_GET[ 'Inspection_ID' ] ) ? $_GET[ 'Inspection_ID' ] : null,
        'Inspection_Name' => isset( $_GET[ 'Inspection_Name' ] ) ? $_GET[ 'Inspection_Name' ] : null,
      	'Date' => null,
      	'Status' => null,
      	'Note' => null,
      	'Price' => null,
        'Address' => null,
        'Phone' => null,
      	'Contact' => null,
        'File_Permit' => null,
        'Permit_Approved' => null,
        'Date_Sent' => null,
        'Forms_to_DOB' => null,
        'Inspection' => null,
        'Hearing' => null,
        'Due_Date' => null,
        'Forms_to_Customer' => null,
        'Recieved_from_Customer' => null,
        'Cancel_Contract' => null,
        'Created' => null,
        'Code' => null,
        'Division_1' => null,
        'Division_2' => null,
        'Division_3' => null,
        'Division_4' => null,
        'Sales' => null,
        'Repair' => null,
        'Modernization' => null,
        'Complete' => null,
        'Custom21' => null,
        'Custom22' => null,
        'Custom23' => null,
        'Custom24' => null,
        'Custom25' => null,
        'Custom26' => null,
        'Custom27' => null,
        'Custom28' => null,
        'Custom29' => null,
        'Custom30' => null
      ) : sqlsrv_fetch_array($result);

      if( isset( $_POST ) && count( $_POST ) > 0 ){
      	$Violation[ 'Name' ] 		       = isset( $_POST[ 'Name' ] ) 	       ? $_POST[ 'Name' ] 	      : $Violation[ 'Name' ];
	      $Violation[ 'Customer_Name' ]  = isset( $_POST[ 'Customer' ] )     ? $_POST[ 'Customer' ]     : $Violation[ 'Customer_Name' ];
        $Violation[ 'Location_Name' ]  = isset( $_POST[ 'Location' ] )     ? $_POST[ 'Location' ]     : $Violation[ 'Location_Name' ];
        $Violation[ 'Unit_Name' ]      = isset( $_POST[ 'Unit' ] )         ? $_POST[ 'Unit' ]         : $Violation[ 'Unit_Name' ];
        $Violation[ 'Proposal_Name' ]  = isset( $_POST[ 'Proposal' ] )     ? $_POST[ 'Proposal' ]     : $Violation[ 'Proposal_Name' ];
        $Violation[ 'Quote_Name' ]     = isset( $_POST[ 'Quote' ] )        ? $_POST[ 'Quote' ]        : $Violation[ 'Quote_Name' ];
        $Violation[ 'Job_Name' ]       = isset( $_POST[ 'Job' ] )          ? $_POST[ 'Job' ]          : $Violation[ 'Job_Name' ];
        $Violation[ 'Ticket_ID' ]      = isset( $_POST[ 'Ticket' ] )       ? $_POST[ 'Ticket' ]       : $Violation[ 'Ticket_ID' ];
        $Violation[ 'Date' ]           = isset( $_POST[ 'Date' ] )         ? $_POST[ 'Date' ]         : $Violation[ 'Date' ];
        $Violation[ 'Status' ]         = isset( $_POST[ 'Status' ] )       ? $_POST[ 'Status' ]       : $Violation[ 'Status' ];
        $Violation[ 'Note' ]           = isset( $_POST[ 'Note' ] )         ? $_POST[ 'Note' ]         : $Violation[ 'Note' ];
        $Violation[ 'Price' ]          = isset( $_POST[ 'Price' ] )        ? $_POST[ 'Price' ]        : $Violation[ 'Price' ];

      	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
      		$result = \singleton\database::getInstance( )->query(
      			null,
      			"	INSERT INTO Violation(
                Job,
      					Loc,
      					Elev,
                Ticket,
                fDate,
                Status,
                Quote,
      					Remarks,
                Price
      				)
      				VALUES(
                (
                  SELECT  Job.ID
                  FROM    Job
                  WHERE   Job.fDesc = ?
                ),(
                  SELECT  Loc.Loc
                  FROM    Loc
                  WHERE   Loc.Tag = ?
                ),(
                  SELECT  Elev.ID
                  FROM    Elev
                  WHERE   Elev.State = ?
                ),
                ?,
                ?,
                ?,
                ?,
                ?,
                ?
              );
      				SELECT SCOPE_IDENTITY( );",
      			array(
      				$Violation[ 'Job_Name' ],
              $Violation[ 'Location_Name' ],
              $Violation[ 'Unit_Name' ],
              empty( $Violation[ 'Ticket_ID' ] ) ? null : $Violation[ 'Ticket_ID' ],
              empty( $Violation[ 'Date' ] ) ? null : date( 'Y-m-d h:i:s', strtotime( $Violation[ 'Date' ] ) ),
              $Violation[ 'Status' ],
              $Violation[ 'Quote_ID' ],
              $Violation[ 'Note' ],
              empty( $Violation[ 'Price' ] ) ? 0 : $Violation[ 'Price' ]
      			)
      		);
      		sqlsrv_next_result( $result );
      		$Violation[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
      		header( 'Location: violation.php?ID=' . $Violation[ 'ID' ] );
      		exit;
      	} else {
      		\singleton\database::getInstance( )->query(
        		null,
        		"	UPDATE 	Violation
        			SET     Violation.Job = (
                        SELECT  Top 1
                                Job.ID
                        FROM    Job
                        WHERE   Job.fDesc = ?
                      ),
        					    Violation.Loc = (
                        SELECT  Top 1
                                Loc.Loc
                        FROM    Loc
                        WHERE   Loc.Tag = ?
                      ),
        					    Violation.Elev = (
                        SELECT  Top 1
                                Elev.ID
                        FROM    Elev
                        WHERE   Elev.ID = ?
                      ),
                      Violation.Ticket = ?,
                      Violation.Name = ?,
        					    Violation.fDate = ?,
                      Violation.Status = ?,
                      Violation.Quote = ?,
                      Violation.Remarks = ?,
                      Violation.Price = ?
        			WHERE   Violation.ID = ?;",
        		array(
              $Violation[ 'Job_Name' ],
              $Violation[ 'Location_Name' ],
              $Violation[ 'Unit_Name' ],
              $Violation[ 'Ticket_ID' ],
              $Violation[ 'Name' ],
              $Violation[ 'Date' ],
              $Violation[ 'Status' ],
              $Violation[ 'Quote_ID' ],
              $Violation[ 'Note' ],
              $Violation[ 'Price' ],
      				$Violation[ 'ID' ]
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
      require( bin_meta . 'index.php');
      require( bin_css  . 'index.php');
      require( bin_js   . 'index.php');
    ?>
</head>
<body>
	<div id="wrapper">
    <?php require(bin_php .'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
    	<div class='card card-primary'>
        <form action='violation.php?ID=<?php echo $Violation[ 'ID' ];?>' method='POST'>
          <input type='hidden' name='ID' value='<?php echo $Violation[ 'ID' ];?>' />
          <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Violation', 'Violations', $Violation[ 'ID' ] );?>
        	<div class='card-body bg-dark text-white'>
        		<div class='row g-0' data-masonry='{"percentPosition": true }'>
          		<div class='card card-primary col-12 col-md-6 col-lg-4 col-xl-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' );?>
            		<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
              		<?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', $Violation[ 'Name' ] );?>
                	<?php \singleton\bootstrap::getInstance( )->card_row_form_select( 'Status', $Violation[ 'Status' ], array( 0 => 'Disabled', 1 => 'Enabled' ) );?>
                	<?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Date', $Violation[ 'Date' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Location', 'Locations', $Violation[ 'Location_ID' ], $Violation[ 'Location_Name' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Job', 'Jobs', $Violation[ 'Job_ID' ], $Violation[ 'Job_Name' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Ticket', 'Tickets', $Violation[ 'Ticket_ID' ], $Violation[ 'Ticket_ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Unit', 'Units', $Violation[ 'Unit_ID' ], $Violation[ 'Unit_Name' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Proposal', 'Proposals', $Violation[ 'Proposal_ID' ], $Violation[ 'Proposal_ID' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Inspection', 'Inspections', $Violation[ 'Inspection_ID' ], $Violation[ 'Inspection_Name' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_currency( 'Price', $Violation[ 'Price' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_textarea( 'Note', $Violation[ 'Note' ] );?>
                </div>
              </div>
              <div class='card card-primary col-12 col-md-6 col-lg-4 col-xl-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Dates' );?>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Dates' ] ) && $_SESSION[ 'Cards' ][ 'Dates' ] == 0 ? "style='display:none;'" : null;?>>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'File_Permit', $Violation[ 'File_Permit' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Permit_Approved', $Violation[ 'Permit_Approved' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Date_Sent', $Violation[ 'Date_Sent' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Forms_to_DOB', $Violation[ 'Forms_to_DOB' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Inspection', $Violation[ 'Inspection' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Hearing', $Violation[ 'Hearing' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Due_Date', $Violation[ 'Due_Date' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Forms_to_Customer', $Violation[ 'Forms_to_Customer' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Recieved_from_Customer', $Violation[ 'Recieved_from_Customer' ] );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Cancel_Contract', $Violation[ 'Cancel_Contract' ] );?>
                </div>
              </div>
            <div class='card card-primary col-12 col-md-6 col-lg-4 col-xl-3'>
              <?php \singleton\bootstrap::getInstance( )->card_header( 'Assignments' );?>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Assignments' ] ) && $_SESSION[ 'Cards' ][ 'Assignments' ] == 0 ? "style='display:none;'" : null;?>>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Created', $Violation[ 'Created' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Code', $Violation[ 'Code' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Sales', $Violation[ 'Sales' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Division_1', $Violation[ 'Division_1' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Division_2', $Violation[ 'Division_2' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Division_3', $Violation[ 'Division_3' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Division_4', $Violation[ 'Division_4' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Repair', $Violation[ 'Repair' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Modernization', $Violation[ 'Modernization' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Complete', $Violation[ 'Complete' ] );?>
              </div>
            </div>
            <div class='card card-primary col-12 col-md-6 col-lg-4 col-xl-3'>
              <?php \singleton\bootstrap::getInstance( )->card_header( 'Custom Fields' );?>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Dates' ] ) && $_SESSION[ 'Cards' ][ 'Dates' ] == 0 ? "style='display:none;'" : null;?>>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Custom21', $Violation[ 'Custom21' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Custom22', $Violation[ 'Custom22' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Custom23', $Violation[ 'Custom23' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Custom24', $Violation[ 'Custom24' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Custom25', $Violation[ 'Custom25' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Custom26', $Violation[ 'Custom26' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Custom27', $Violation[ 'Custom27' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Custom28', $Violation[ 'Custom28' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Custom29', $Violation[ 'Custom29' ] );?>
                <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Custom30', $Violation[ 'Custom30' ] );?>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=violation<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";<?php }?>
