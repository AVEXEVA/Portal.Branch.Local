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
        ||  !isset( $Privileges[ 'Unit' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Unit' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'units.php'
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
</head>
<body>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php');?>
        <div id="page-wrapper" class='content'>
      <div class="card card-full card-primary border-0">
        <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?> Units</h4></div>
        <div class='mobile card-body bg-dark text-white'><form action='units.php'>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'City_ID', isset( $_GET[ 'City_ID' ] ) ? $_GET[ 'City_ID' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Building_ID', isset( $_GET[ 'Building_ID' ] ) ? $_GET[ 'Building_ID' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Territory', isset( $_GET[ 'Territory' ] ) ? $_GET[ 'Territory' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Division', isset( $_GET[ 'Division' ] ) ? $_GET[ 'Division' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Customer', isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Location', isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Status', isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null, false, false, false, 'redraw' );?>
        </div>
        <div class='card-body bg-dark'>
          <table id='Table_Units' class='display' cellspacing='0' width='100%'>
            <thead class='text-white border border-white'> <?php
            \singleton\table::getInstance( )->th( 'ID', 'ID' );
            \singleton\table::getInstance( )->th( 'City_ID', 'City_ID' );
            \singleton\table::getInstance( )->th( 'Building_ID', 'Building_ID' );
            \singleton\table::getInstance( )->th( 'Territory', 'Territory' );
            \singleton\table::getInstance( )->th( 'Division', 'Division' );
            \singleton\table::getInstance( )->th( 'Customer', 'Customer' );
            \singleton\table::getInstance( )->th( 'Location', 'Location' );
            \singleton\table::getInstance( )->th( 'Type', 'Type' );
            \singleton\table::getInstance( )->th( 'Status', 'Status' );
            \singleton\table::getInstance( )->th( 'Tickets', 'Tickets' );
            \singleton\table::getInstance( )->th( 'Last_Ticket', 'Last_Ticket' );
            ?><tr class='desktop'><?php
            \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
            \singleton\table::getInstance( )->th_input( 'City_ID', isset( $_GET[ 'City_ID' ] ) ? $_GET[ 'City_ID' ] : null );
            \singleton\table::getInstance( )->th_input( 'Building_ID', isset( $_GET[ 'Building_ID' ] ) ? $_GET[ 'Building_ID' ] : null );
            \singleton\table::getInstance( )->th_autocomplete( 
              'Territory', 'Territories', 
              isset( $_GET[ 'Territory_ID' ] ) ? $_GET[ 'Territory_ID' ] : null, 
              isset( $_GET[ 'Territory_Name' ] ) ? $_GET[ 'Territory_Name' ] : null 
            );
            \singleton\table::getInstance( )->th_autocomplete( 
              'Division', 'Divisions', 
              isset( $_GET[ 'Division_ID' ] ) ? $_GET[ 'Division_ID' ] : null, 
              isset( $_GET[ 'Division_Name' ] ) ? $_GET[ 'Division_Name' ] : null 
            );
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
            \singleton\table::getInstance( )->th_input( 'Ticket_ID', isset( $_GET[ 'Ticket_ID' ] ) ? $_GET[ 'Ticket_ID' ] : null );
            ?> </tr></thead>
          </table>
        </form></div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><script>document.location.href='../login.php?Forward=units.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
