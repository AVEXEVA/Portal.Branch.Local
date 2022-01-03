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
            'jobs.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body>
  <div id='wrapper'>
    <?php require( bin_php . 'element/navigation.php'); ?>
    <div id='page-wrapper' class='content'>
			<div class='card card-full card-primary bg-dark text-white'>
				<div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Job( 1 );?> Jobs</h4></div>
        <div class='card-body mobile'><?php 
          \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );
          \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null, false, false, false, 'redraw' );
          \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Date_Start', isset( $_GET[ 'Date_Start' ] ) ? $_GET[ 'Date_Start' ] : null );
          \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Date_End', isset( $_GET[ 'Date_End' ] ) ? $_GET[ 'Date_End' ] : null );
          \singleton\bootstrap::getInstance( )->card_row_form_autocomplete(
              'Customer', 'Customers',
              isset( $_GET[ 'Customer_ID' ] ) ? $_GET[ 'Customer_ID' ] : null,
              isset( $_GET[ 'Customer_Name' ] ) ? $_GET[ 'Customer_Name' ] : null
          );
          \singleton\bootstrap::getInstance( )->card_row_form_autocomplete(
              'Location', 'Locations',
              isset( $_GET[ 'Location_ID' ] ) ? $_GET[ 'Location_ID' ] : null,
              isset( $_GET[ 'Location_Name' ] ) ? $_GET[ 'Location_Name' ] : null
          );
          \singleton\bootstrap::getInstance( )->card_row_form_input( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null, false, false, false, 'redraw' );
          \singleton\bootstrap::getInstance( )->card_row_form_input( 'Status', isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null, false, false, false, 'redraw' );
          \singleton\bootstrap::getInstance( )->card_row_form_input( 'Tickets', isset( $_GET[ 'Tickets' ] ) ? $_GET[ 'Tickets' ] : null, false, false, false, 'redraw' );
          \singleton\bootstrap::getInstance( )->card_row_form_input( 'Invoices', isset( $_GET[ 'Invoices' ] ) ? $_GET[ 'Invoices' ] : null, false, false, false, 'redraw' );
        ?></div>
				<div class='card-body bg-dark'>
					<table id='Table_Jobs' class='display' cellspacing='0' width='100%'>
						<thead><tr class='text-align:center;'><?php
              \singleton\table::getInstance( )->th( 'ID', 'ID' );
              \singleton\table::getInstance( )->th( 'Name', 'Name' );
              \singleton\table::getInstance( )->th( 'Date', 'Date' );
              \singleton\table::getInstance( )->th( 'Customer', 'Customer' );
              \singleton\table::getInstance( )->th( 'Location', 'Location' );
              \singleton\table::getInstance( )->th( 'Type', 'Type' );
              \singleton\table::getInstance( )->th( 'Status', 'Status' );
              \singleton\table::getInstance( )->th( 'Tickets', 'Tickets' );
              \singleton\table::getInstance( )->th( 'Invoices', 'Invoices' );
              \singleton\table::getInstance( )->th( 'Hours', 'Hours' );
            ?></tr><tr><?php
              \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
              \singleton\table::getInstance( )->th_input( 'Name', isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null );
              \singleton\table::getInstance( )->th_input( 'Date', isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null );
              \singleton\table::getInstance( )->th_autocomplete( 
                'Customer', 'Customers', 
                isset( $_GET[ 'Customer_ID' ] ) ? $_GET[ 'Customer_ID' ] : null, 
                isset( $_GET[ 'Customer_Name' ] ) ? $_GET[ 'Customer_Name' ] : null 
              );
              \singleton\table::getInstance( )->th_autocomplete( 
                'Location', 'Locations', 
                isset( $_GET[ 'Location_ID' ] ) ? $_GET[ 'Location_ID' ] : null, 
                isset( $_GET[ 'Location_Name' ] ) ? $_GET[ 'Location_Name' ] : null 
              );
              \singleton\table::getInstance( )->th_input( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null );
              \singleton\table::getInstance( )->th_input( 'Status', isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null );
              \singleton\table::getInstance( )->th_input( 'Tickets', isset( $_GET[ 'Tickets' ] ) ? $_GET[ 'Tickets' ] : null );
              \singleton\table::getInstance( )->th_input( 'Invoices', isset( $_GET[ 'Invoices' ] ) ? $_GET[ 'Invoices' ] : null );
              \singleton\table::getInstance( )->th_input( 'Hours', isset( $_GET[ 'Hours' ] ) ? $_GET[ 'Hours' ] : null );
            ?></tr></thead>
				  </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
}
} else {?><html><head><script>document.location.href='../login.php?Forward=jobs.php';</script></head></html><?php }?>
