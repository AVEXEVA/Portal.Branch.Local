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
  if( 	  !isset( $Connection[ 'ID' ] )
      ||  !isset( $Privileges[ 'Requisition' ] )
      || 	!check( privilege_read, level_group, $Privileges[ 'Requisition' ] )
    ){ ?><?php require('404.html');?><?php }
  else {
    \singleton\database::getInstance( )->query(
      null,
      " INSERT INTO Activity([User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        date('Y-m-d H:i:s'),
        'requisition.php'
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
      " SELECT  Requisition.ID,
                Requisition.[User],
                REquisition.[Required],
                Requisition.[Shutdown],
                Requisition.[ASAP],
                Requisition.[LSD],
                Requisition.[FRM],
                Requisition.Notes,
                DropOff.Tag       AS DropOff_Name,
                Unit.State        AS Unit_State,
                Unit.Unit         AS Unit_Label,
                Job.ID            AS Job_ID,
                Job.fDesc         AS Job_Name,
                Job_Type.Type     AS Job_Type,
                Location.Loc      AS Location_ID,
                Location.Tag      AS Location_Name,
                Location.Address  AS Location_Street,
                Location.City     AS Location_City,
                Location.State    AS Location_State,
                Location.Zip      AS Location_Zip,
                DropOff.Address   AS DropOff_Street,
                DropOff.City      AS DropOff_City,
                DropOff.State     AS DropOff_State,
                DropOff.Zip       AS DropOff_Zip,
                Job.ID            AS Job_ID,
                Job.fDesc         AS Job_Name,
                Employee.ID       AS Employee_ID,
                Employee.fFirst + ' ' + Employee.Last AS Employee_Name
        FROM    Requisition
                LEFT JOIN Loc     AS Location   ON Requisition.Location = Location.Loc
                LEFT JOIN Loc     AS DropOff    ON Requisition.DropOff  = DropOff.Loc
                LEFT JOIN Elev    AS Unit       ON Requisition.Unit     = Unit.ID
                LEFT JOIN Job     AS Job        ON Requisition.Job      = Job.ID
                LEFT JOIN JobType AS Job_Type   ON Job_Type.ID          = Job.Type
                LEFT JOIN Emp     AS Employee   ON Employee.ID          = Requisition.[User]
        WHERE   Requisition.ID = ?;",
      array(
        $ID
      )
    );
    $Requisition = sqlsrv_fetch_array( $result );
    $result = \singleton\database::getInstance( )->query(
      null,
      " SELECT  *
        FROM    Requisition_Item
        WHERE   Requisition_Item.Requisition = ?;",
      array(
        $ID
      )
    );
    $Requisition_Items = array( );
    if( $result ){while( $row = sqlsrv_fetch_array( $result ) ){ $Requisition_Items[ ] = $row; } }
    $Requisition  = in_array( $ID, array( null, 0, '', ' ' ) ) || !$result ? array(
      'ID' => null,
      'Date' => null,
      'Required' => null,
      '[User]' => null,
      'Location' => null,
      'DropOff' => null,
      'Unit' => null,
      'Job' => null,
      'Shutdown' => null,
      'ASAP:' => null,
      'Rush' => null,
      'LSD' => null,
      'FRM' => null,
      'ASAP' => null,
      'Notes' => null,
      'Unit_State' => null,
      'Unit_Label' => null,
      'Location_ID' => null,
      'Location_Name' => null,
      'Location_Street' => null,
      'Location_City' => null,
      'Location_State' => null,
      'Location_Zip' => null,
      'DropOff_Tag' => null,
      'DropOff_ID' => null,
      'DropOff_Name' => null,
      'DropOff_Street' => null,
      'DropOff_City' => null,
      'DropOff_State' => null,
      'DropOff_Zip' => null,
      'Job_ID' => null,
      'Job_Name' => null,
      'Job_Type' => null,
      'Unit_Name' => null,
      'Unit_ID' => null,
      'Employee_ID' => null,
      'Employee_Name' => null
    ) : sqlsrv_fetch_array($result);
    if( isset( $_POST ) && count( $_POST ) > 0  ){
      $Requisition[ 'Employee_ID' ]     = isset( $_POST[ 'Employee_ID' ] )   ? $_POST[ 'Employee_ID' ]   : $Requisition[ 'Employee_ID' ];
      $Requisition[ 'Date' ]            = isset( $_POST[ 'Date' ] )          ? date('Y-m-d 00:00:00.000', strtotime($_POST[ 'Date' ])) : $Requisition[ 'Date' ];
      $Requisition[ 'Unit_ID' ] 	      = isset( $_POST[ 'Unit_ID' ] )       ? $_POST[ 'Unit_ID' ]       : $Requisition[ 'Unit_ID' ];
      $Requisition[ 'Job_ID' ] 		      = isset( $_POST[ 'Job_ID' ] ) 	     ? $_POST[ 'Job_ID' ] 	     : $Requisition[ 'Job_ID' ];
      $Requisition[ 'Location_ID' ]     = isset( $_POST[ 'Location_ID' ] )   ? $_POST[ 'Location_ID' ]   : $Requisition[ 'Location_ID' ];
      $Requisition[ 'DropOff_ID' ]      = isset( $_POST[ 'DropOff_ID' ] )    ? $_POST[ 'DropOff_ID' ]    : $Requisition[ 'DropOff_ID' ];
      $Requisition[ 'Shutdown' ]        = isset( $_POST[ 'Shutdown' ] )      ? $_POST[ 'Shutdown' ]      : $Requisition[ 'Shutdown' ];
      if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
        $result = \singleton\database::getInstance( )->query(
          null,
          "	INSERT INTO Requisition (
              [User],
              Date,
              Required,
              Location,
              DropOff,
              Unit,
              Job,
              [Shutdown],
              ASAP,
              Rush,
              LSD,
              FRM,
              Notes
            )
            VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
            SELECT  SCOPE_IDENTITY( );",
          array(
            !empty( $Requisition[ 'User' ] )        ? $Requisition[ 'User' ] : null,
            !empty( $Requisition[ 'Date' ] )        ? $Requisition[ 'Date' ] : null,
            !empty( $Requisition[ 'Required' ] )    ? $Requisition[ 'Required' ] : null,
            !empty( $Requisition[ 'Location_ID' ] ) ? $Requisition[ 'Location_ID' ] : null,
            !empty( $Requisition[ 'DropOff_ID' ] )  ? $Requisition[ 'DropOff_ID' ] : null,
            !empty( $Requisition[ 'Unit_ID' ] )     ? $Requisition[ 'Unit_ID' ] : null,
            !empty( $Requisition[ 'Job_ID' ] )      ? $Requisition[ 'Job_ID' ] : null,
            !empty( $Requisition[ 'Shutdown' ] )    ? $Requisition[ 'Shutdown' ] : null,
            !empty( $Requisition[ 'ASAP' ] )        ? $Requisition[ 'ASAP' ] : null,
            !empty( $Requisition[ 'Rush' ] )        ? $Requisition[ 'Rush' ] : null,
            !empty( $Requisition[ 'LSD' ] )         ? $Requisition[ 'LSD' ] : null,
            !empty( $Requisition[ 'FRM' ] )         ? $Requisition[ 'FRM' ] : null,
            !empty( $Requisition[ 'Notes' ] )       ? $Requisition[ 'Notes' ] : null
          )
        );
        var_dump(sqlsrv_errors ( ) );
        sqlsrv_next_result( $result );
        $Requisition [ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
        //header( 'Location: requisition.php?ID=' . $Division [ 'ID' ] );
      } else {
        \singleton\database::getInstance( )->query(
          null,
          "	UPDATE 	Requisition
            SET       Requisition.ID   = ?,
                      Requisition.User = ?,
                      Requisition.Date = ?,
                      Requisition.Required = ?,
                      Requisition.Location = ?,
                      Requisition.DropOff = ?,
                      Requisition.Unit   = ?,
                      Requisition.Job   = ?,
                      Requisition.Shutdown = ?,
                      Requisition.ASAP = ?,
                      Requisition.Rush = ?,
                      Requisition.LSD = ?,
                      Requisition.FRM = ?,
                      Requisition.Notes = ?
            WHERE 	  Zone.ID = ?;",
          array(
          $Requisition[ 'ID' ],
          $Requisition[ 'User' ],
          $Requisition[ 'Date' ],
          $Requisition[ 'Required' ],
          $Requisition[ 'Location' ],
          $Requisition[ 'DropOff' ],
          $Requisition[ 'Unit' ],
          $Requisition[ 'Job' ],
          $Requisition[ 'Shutdown' ],
          $Requisition[ 'ASAP' ],
          $Requisition[ 'Rush' ],
          $Requisition[ 'LSD' ],
          $Requisition[ 'FRM' ],
          $Requisition[ 'Notes' ],
          !empty( $Requisition [ 'GeoLock' ] ) ? $Requisition [ 'GeoLock' ] : 0
        )
      );
    }
  }
?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php $_GET[ 'Bootstrap' ] = '5.1';?>
  <?php $_GET[ 'Entity_CSS' ] = 1;?>
  <?php	require( bin_meta . 'index.php');?>
  <?php	require( bin_css  . 'index.php');?>
  <?php require( bin_js   . 'index.php');?>
</head>
<body>
    <div id='wrapper'>
      <?php require(bin_php .'element/navigation.php');?>
      <div id="page-wrapper" class='content'>
        <div class='card card-primary'>
          <form action='requisition.php?ID=<?php echo $Requisition[ 'ID' ];?>' method='POST'>
            <input type='hidden' name='ID' value='<?php echo $Requisition[ 'ID' ];?>' />
            <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Requisition', 'Requisitions', $Requisition[ 'ID' ] );?>
            <div class='card-body bg-dark text-white'>
              <div class='row g-0'>
                <div class='card card-primary my-3 col-3'>
                  <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' );?>
                  <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Employee', 'Employees', $Requisition[ 'Employee_ID' ], $Requisition[ 'Employee_Name' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Date', $Requisition[ 'Date' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Required', $Requisition[ 'Required' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Location', 'Locations', $Requisition[ 'Location_ID' ], $Requisition[ 'Location_Name' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'DropOff', 'Locations', $Requisition[ 'DropOff_ID' ], $Requisition[ 'DropOff_Name' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Unit', 'Units', $Requisition[ 'Unit_ID' ], $Requisition[ 'Unit_Name' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Job', 'Jobs', $Requisition[ 'Job_ID' ], $Requisition[ 'Job_Name' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Shutdown', $Requisition[ 'Shutdown' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'ASAP', $Requisition[ 'ASAP' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'Rush', $Requisition[ 'Rush' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'LSD', $Requisition[ 'LSD' ] );?>
                    <?php \singleton\bootstrap::getInstance( )->card_row_form_input_checkbox( 'FRM', $Requisition[ 'FRM' ] );?>
                </div>
              </div>
              <div class='card card-primary my-3 col-7'>
                <div class='card-heading'>
                  <div class='row g-0 px-3 py-2'>
                    <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Items</span></h5></div>
                  </div>
                </div>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                  <div class='row g-0'>
                    <div class='col-12'>
                      <table id='Table_Requisition_Items' class='display' cellspacing='0' width='100%'>
                        <thead class='text-white border border-white'><tr><?php
                        \singleton\table::getInstance( )->th( 'ID', 'ID' );
                        \singleton\table::getInstance( )->th( 'Quantity', 'Quantity' );
                        \singleton\table::getInstance( )->th( 'Description', 'Description' );
                        \singleton\table::getInstance( )->th( 'Image', 'Image' );
                      ?></tr>
                         <tr class='desktop'><?php
                         \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
                         \singleton\table::getInstance( )->th_input( 'Quantity', isset( $_GET[ 'Quantity' ] ) ? $_GET[ 'Quantity' ] : null );
                         \singleton\table::getInstance( )->th_input( 'Description', isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null );
                         \singleton\table::getInstance( )->th_input( 'Image', isset( $_GET[ 'Image' ] ) ? $_GET[ 'Image' ] : null );
                        ?></tr></thead>
                      </table>
                    </div>
                  </div>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
