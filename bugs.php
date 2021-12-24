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
        ||  !isset( $Privileges[ 'Admin' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Admin' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'bugs.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php require( bin_js   . 'index.php');?>
</head>
<body>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php');?>
        <div id='page-wrapper' class='content'>
          <div class='card card-full card-primary'>
            <div class='card-heading bg-white text-black'><h4><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Bugs</h4></div>
            <div class='mobile card-body bg-dark'>
                      <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );?>
                      <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );?>
                      <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );?>
                      <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );?>
                      <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );?>
                   
                  </div>
          <div class='card-body bg-darker'>
            <table id='Table_bugs' class='display' cellspacing='0' width='100%'>
              <thead class='text-white border border-white'><tr><?php
                \singleton\table::getInstance( )->th( 'ID', 'ID' );
                \singleton\table::getInstance( )->th( 'Name', 'Name' );
                \singleton\table::getInstance( )->th( 'Description', 'Description' );
                \singleton\table::getInstance( )->th( 'Severity', 'Severity' );
                \singleton\table::getInstance( )->th( 'Suggestion', 'Suggestion' );
                \singleton\table::getInstance( )->th( 'Resolution', 'Resolution' );
                \singleton\table::getInstance( )->th( 'Fixed', 'Fixed' );
              ?></tr>
                  <tr class='desktop'><?php
                    \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
                    \singleton\table::getInstance( )->th_input( 'Name', isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null );
                    \singleton\table::getInstance( )->th_input( 'Description', isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null );
                    \singleton\table::getInstance( )->th_input( 'Severity', isset( $_GET[ 'Severity' ] ) ? $_GET[ 'Severity' ] : null );
                    \singleton\table::getInstance( )->th_input( 'Suggestion', isset( $_GET[ 'Suggestion' ] ) ? $_GET[ 'Suggestion' ] : null );
                    \singleton\table::getInstance( )->th_input( 'Resolution', isset( $_GET[ 'Resolution' ] ) ? $_GET[ 'Resolution' ] : null );
                    \singleton\table::getInstance( )->th_input( 'Fixed', isset( $_GET[ 'Fixed' ] ) ? $_GET[ 'Fixed' ] : null );
                  ?></tr></thead>
                </table>
            </div>
        </div>
    </div>
 </body>
</html>
 <?php
    }
} else {?><script>document.location.href='../login.php?Forward=bugs.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
