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
        $_SESSION[ 'Connection' ][ 'User' ]
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
      ||  !isset( $Privileges[ 'Location' ] )
      ||  !check( privilege_read, level_group, $Privileges[ 'Location' ] )
  ){ ?><?php require('404.html');?><?php }
  else {
    $database->query(
      null,
      " INSERT INTO Activity([User], [Date], [Page])
        VALUES(?,?,?);",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        date("Y-m-d H:i:s"),
        'Locations.php'
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
        <form method='GET' action='locations.php'>
          <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Locations</h4></div>
          <div class="mobile card-body bg-dark text-white"><?php 
            \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );
            \singleton\bootstrap::getInstance( )->card_row_form_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null, false, false, false, 'redraw' );
            \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null, false, false, false, 'redraw' );
            \singleton\bootstrap::getInstance( )->card_row_form_autocomplete(
              'Customer', 'Customers',
              isset( $_GET[ 'Customer_ID' ] ) ? $_GET[ 'Customer_ID' ] : null,
              isset( $_GET[ 'Customer_Name' ] ) ? $_GET[ 'Customer_Name' ] : null
            );
            \singleton\bootstrap::getInstance( )->card_row_form_input( 'Street', isset( $_GET[ 'Street' ] ) ? $_GET[ 'Street' ] : null, false, false, false, 'redraw' );
            \singleton\bootstrap::getInstance( )->card_row_form_input( 'City', isset( $_GET[ 'City' ] ) ? $_GET[ 'City' ] : null, false, false, false, 'redraw' );
            \singleton\bootstrap::getInstance( )->card_row_form_input( 'State', isset( $_GET[ 'State' ] ) ? $_GET[ 'State' ] : null, false, false, false, 'redraw' );
            \singleton\bootstrap::getInstance( )->card_row_form_input( 'Zip', isset( $_GET[ 'Zip' ] ) ? $_GET[ 'Zip' ] : null, false, false, false, 'redraw' );
            \singleton\bootstrap::getInstance( )->card_row_form_input( 'Maintained', isset( $_GET[ 'Maintained' ] ) ? $_GET[ 'Maintained' ] : null, false, false, false, 'redraw' );
            \singleton\bootstrap::getInstance( )->card_row_form_select( 'Status', isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null, array( 0 => 'Disabled', 1 => 'Enabled' ) );
          ?></div>
          <div class="card-body bg-dark">
            <table id='Table_Locations' class='display' cellspacing='0' width='100%'>
              <thead><tr class='text-center'><?php 
                \singleton\table::getInstance( )->th( 'ID', 'ID' );
                \singleton\table::getInstance( )->th( 'Name', 'Name' );
                \singleton\table::getInstance( )->th( 'Customer', 'Customer' );
                \singleton\table::getInstance( )->th( 'Type', 'Type' );
                \singleton\table::getInstance( )->th( 'Division', 'Division' );
                \singleton\table::getInstance( )->th( 'Route', 'Route' );
                \singleton\table::getInstance( )->th( 'Street', 'Street' );
                \singleton\table::getInstance( )->th( 'City', 'City' );
                \singleton\table::getInstance( )->th( 'State', 'State' );
                \singleton\table::getInstance( )->th( 'Zip', 'Zip' );
                \singleton\table::getInstance( )->th( 'Units', 'Units' );
                \singleton\table::getInstance( )->th( 'Maintained', 'Maintained' );
                \singleton\table::getInstance( )->th( 'Status', 'Status' );
              ?></tr><tr class='form-desktop'><?php 
                \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
                \singleton\table::getInstance( )->th_input( 'Name', isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null );
                \singleton\table::getInstance( )->th_autocomplete( 
                  'Customer', 'Customers', 
                  isset( $_GET[ 'Customer_ID' ] ) ? $_GET[ 'Customer_ID' ] : null, 
                  isset( $_GET[ 'Customer_Name' ] ) ? $_GET[ 'Customer_Name' ] : null 
                );
                \singleton\table::getInstance( )->th_input( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null );
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
                \singleton\table::getInstance( )->th_input( 'Street', isset( $_GET[ 'Street' ] ) ? $_GET[ 'Street' ] : null );
                \singleton\table::getInstance( )->th_input( 'City', isset( $_GET[ 'City' ] ) ? $_GET[ 'City' ] : null );
                \singleton\table::getInstance( )->th_input( 'State', isset( $_GET[ 'State' ] ) ? $_GET[ 'State' ] : null );
                \singleton\table::getInstance( )->th_input( 'Zip', isset( $_GET[ 'Zip' ] ) ? $_GET[ 'Zip' ] : null );
                \singleton\table::getInstance( )->th_input( 'Units', isset( $_GET[ 'Units' ] ) ? $_GET[ 'Units' ] : null );
                \singleton\table::getInstance( )->th_select( 'Maintained', isset( $_GET[ 'Maintained' ] ) ? $_GET[ 'Maintained' ] : null, array( 0 => 'Disabled', 1 => 'Enabled' ) );
                \singleton\table::getInstance( )->th_select( 'Status', isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null, array( 0 => 'Disabled', 1 => 'Enabled' ) );
              ?></tr></thead>
            </table>
          </div>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><script>document.location.href='../login.php?Forward=locations.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
