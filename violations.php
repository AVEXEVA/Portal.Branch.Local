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
        ||  !isset( $Privileges[ 'Route' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Violation' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'violations.php'
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
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php');?>
        <div id='page-wrapper' class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Violation( 1 );?> Violations</h4></div>
                <div class="mobile card-body bg-dark text-white"><form action='violations.php'>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null, false, false, false, 'redraw' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Customer', isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null, false, false, false, 'redraw' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Address', isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null, false, false, false, 'redraw' );?>
                  </div>
                  <div class='card-body bg-dark'>
                    <table id='Table_Violations' class='display' cellspacing='0' width='100%'>
                      <thead class='text-white border border-white'><?php
                          \singleton\table::getInstance( )->th( 'ID', 'ID' );
                          \singleton\table::getInstance( )->th( 'Customer', 'Customer' );
                          \singleton\table::getInstance( )->th( 'Address', 'Address' );
                          \singleton\table::getInstance( )->th( 'Date', 'Locations' );
                          \singleton\table::getInstance( )->th( 'Units', 'Units' );
                          \singleton\table::getInstance( )->th( 'Status', 'Status' );
                        ?><tr class='desktop'><?php
                          \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
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
                          \singleton\table::getInstance( )->th_input( 'Date', isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Units', isset( $_GET[ 'Units' ] ) ? $_GET[ 'Units' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Status', isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null );
                        ?></tr></thead>
                  </form></table>
                </div>
            </div>
        </div>
      </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=violations.php';</script></head></html><?php }?>
