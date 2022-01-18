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
        ||  !check( privilege_read, level_group, $Privileges[ 'Route' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'routes.php'
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
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Route( 1 );?> Routes</h4></div>
                <div class="mobile card-body bg-dark text-white"><form action='routes.php'>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Employee', isset( $_GET[ 'Employee' ] ) ? $_GET[ 'Employee' ] : null, false, false, false, 'redraw' );?>
                </div>
                <div class="card-body bg-dark">
                    <table id='Table_Routes' class='display' cellspacing='0' width='100%'>
                        <thead class='text-white border border-white'><?php
                          \singleton\table::getInstance( )->th( 'ID', 'ID' );
                          \singleton\table::getInstance( )->th( 'Route Name', 'Route Name' );
                          \singleton\table::getInstance( )->th( 'Employee', 'Employee' );
                          \singleton\table::getInstance( )->th( 'Locations', 'Locations' );
                          \singleton\table::getInstance( )->th_2( 'Units', 'Units' );
                          \singleton\table::getInstance( )->th_2( 'Violations', 'Violations' );
                          \singleton\table::getInstance( )->th_2( 'Tickets', 'Tickets' );
                        ?><tr class='desktop'><?php
                          \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Name', isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Employee', isset( $_GET[ 'Employee' ] ) ? $_GET[ 'Employee' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Locations', isset( $_GET[ 'Locations' ] ) ? $_GET[ 'Locations' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Elevators', isset( $_GET[ 'Units_Elevators' ] ) ? $_GET[ 'Units_Elevators' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Others', isset( $_GET[ 'Units_Others' ] ) ? $_GET[ 'Units_Others' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Office', isset( $_GET[ 'Violations_Office' ] ) ? $_GET[ 'Violations_Office' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Field', isset( $_GET[ 'Violations_Field' ] ) ? $_GET[ 'Violations_Field' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Assigned', isset( $_GET[ 'Tickets_Assigned' ] ) ? $_GET[ 'Tickets_Assigned' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Active', isset( $_GET[ 'Tickets_Active' ] ) ? $_GET[ 'Tickets_Active' ] : null );
                      ?></tr></thead>
                    </table>
                </form></div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=routes.php';</script></head></html><?php }?>
