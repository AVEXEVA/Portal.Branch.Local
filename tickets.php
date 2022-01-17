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
        ||  !isset( $Privileges[ 'Ticket' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Ticket' ] )
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
<body>
  <div id="wrapper">
    <?php require( bin_php . 'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
      <div class="card card-full card-primary border-0">
        <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?> Tickets</h4></div>
        <div class="mobile card-body bg-dark text-white"><form action='locations.php'>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Employee', isset( $_GET[ 'Employee' ] ) ? $_GET[ 'Employee' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Customer', isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Location', isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Unit', isset( $_GET[ 'Unit' ] ) ? $_GET[ 'Unit' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Job', isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Title', isset( $_GET[ 'Title' ] ) ? $_GET[ 'Title' ] : null, false, false, false, 'redraw' );?>
        </div>
        <div class="card-body bg-dark">
          <table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
            <thead><tr class='text-center'><?php
              \singleton\table::getInstance( )->th( 'ID', 'ID' );
              \singleton\table::getInstance( )->th( 'Open', 'Open' );
              \singleton\table::getInstance( )->th( 'Employee', 'Employee' );
              \singleton\table::getInstance( )->th( 'Division', 'Division' );
              \singleton\table::getInstance( )->th( 'Route', 'Route' );
              \singleton\table::getInstance( )->th( 'Customer', 'Customer' );
              \singleton\table::getInstance( )->th( 'Location', 'Location' );
              \singleton\table::getInstance( )->th( 'Unit', 'Unit' );
              \singleton\table::getInstance( )->th( 'Job', 'Job' );
              \singleton\table::getInstance( )->th( 'Type', 'Type' );
              \singleton\table::getInstance( )->th( 'Level', 'Level' );
              \singleton\table::getInstance( )->th( 'Status', 'Status' );
              \singleton\table::getInstance( )->th( 'Date', 'Date' );
              \singleton\table::getInstance( )->th( 'En_Route', 'En_Route' );
              \singleton\table::getInstance( )->th( 'On_Site', 'On_Site' );
              \singleton\table::getInstance( )->th( 'Completed', 'Completed' );
              \singleton\table::getInstance( )->th( 'Hours', 'Hours' );
            ?></tr><tr class='desktop'><?php
              \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
              \singleton\table::getInstance( )->th_select( 'Open', isset( $_GET[ 'Open' ] ) ? $_GET[ 'Open' ] : null, array(
                0 => 'No',
                1 => 'Yes'
              ) );
              \singleton\table::getInstance( )->th_autocomplete( 
                'Employee', 'Employees', 
                isset( $_GET[ 'Employee_ID' ] ) ? $_GET[ 'Employee_ID' ] : null, 
                isset( $_GET[ 'Employee_Name' ] ) ? $_GET[ 'Employee_Name' ] : null 
              );
              \singleton\table::getInstance( )->th_autocomplete( 
                'Division', 'Divisions', 
                isset( $_GET[ 'Division_ID' ] ) ? $_GET[ 'Division_ID' ] : null, 
                isset( $_GET[ 'Division_Name' ] ) ? $_GET[ 'Division_Name' ] : null 
              );
              \singleton\table::getInstance( )->th_autocomplete( 
                'Route', 'Routes', 
                isset( $_GET[ 'Route_ID' ] ) ? $_GET[ 'Route_ID' ] : null, 
                isset( $_GET[ 'Route_Name' ] ) ? $_GET[ 'Route_Name' ] : null 
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
              \singleton\table::getInstance( )->th_autocomplete( 
                'Unit', 'Units', 
                isset( $_GET[ 'Unit_ID' ] ) ? $_GET[ 'Unit_ID' ] : null, 
                isset( $_GET[ 'Unit_Name' ] ) ? $_GET[ 'Unit_Name' ] : null 
              );
              \singleton\table::getInstance( )->th_autocomplete( 
                'Job', 'Jobs', 
                isset( $_GET[ 'Job_ID' ] ) ? $_GET[ 'Job_ID' ] : null, 
                isset( $_GET[ 'Job_Name' ] ) ? $_GET[ 'Job_Name' ] : null 
              );
              \singleton\table::getInstance( )->th_input( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null );
              \singleton\table::getInstance( )->th_select( 'Level', isset( $_GET[ 'Level' ] ) ? $_GET[ 'Level' ] : null, array(
                1  => 'Service Call',
                2  => 'Trucking',
                3  => 'Modernization',
                4  => 'Violations',
                5  => 'Level 5',
                6  => 'Repair',
                7  => 'Annual',
                8  => 'Escalator',
                9  => 'Email',
                10 => 'Maintenance',
                11 => 'Survey',
                12 => 'Engineering',
                13 => 'Support',
                14 => "M/R"
              ) );
              \singleton\table::getInstance( )->th_select( 'Status', isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null, array(
                0 => 'Unassigned',
                1 => 'Assigned',
                2 => 'En Route',
                3 => 'On Site',
                4 => 'Completed',
                5 => 'On Hold',
                6 => 'Reviewing'
              ) );
              \singleton\table::getInstance( )->th_input( 'Date', isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null );
              \singleton\table::getInstance( )->th_input( 'En_Route', isset( $_GET[ 'En_Route' ] ) ? $_GET[ 'En_Route' ] : null );
              \singleton\table::getInstance( )->th_input( 'On_Site', isset( $_GET[ 'On_Site' ] ) ? $_GET[ 'On_Site' ] : null );
              \singleton\table::getInstance( )->th_input( 'Completed', isset( $_GET[ 'Completed' ] ) ? $_GET[ 'Completed' ] : null );
              \singleton\table::getInstance( )->th_input( 'Hours', isset( $_GET[ 'Hours' ] ) ? $_GET[ 'Hours' ] : null );
            ?></tr></thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php }
} else {?><script>document.location.href='../login.php?Forward=tickets.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
