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
        ||  !isset( $Privileges[ 'Collection' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Collection' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'collections.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php' );?>
    <?php require( bin_js   . 'index.php' );?>
</head>
<body>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php');?>
        <div id='page-wrapper' class='content'>
            <div class='card card-full card-primary border-0'>
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Collections</h4></div>
				<div class="mobile card-body bg-darker text-white"><?php
                    \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );
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
                    \singleton\bootstrap::getInstance( )->card_row_form_autocomplete(
                        'Job', 'Jobs',
                        isset( $_GET[ 'Job_ID' ] ) ? $_GET[ 'Job_ID' ] : null,
                        isset( $_GET[ 'Job_Name' ] ) ? $_GET[ 'Job_Name' ] : null
                    );
                    \singleton\bootstrap::getInstance( )->card_row_form_input( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null, false, false, false, 'redraw' );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_date( 'Date', isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_currency( 'Due', isset( $_GET[ 'Due' ] ) ? $_GET[ 'Due' ] : null );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_currency( 'Original', isset( $_GET[ 'Original' ] ) ? $_GET[ 'Original' ] : null );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_currency( 'Balance', isset( $_GET[ 'Balance' ] ) ? $_GET[ 'Balance' ] : null );
                    \singleton\bootstrap::getInstance( )->card_row_form_input( 'Description', isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null, false, false, false, 'redraw' );
                    ?><div class='row'><div class='col-12'><input type='submit' value='Submit' /></div></div>
                </div>
                <div class='card-body card-body bg-darker'>
                    <table id='Table_Collections' class='display' cellspacing='0' width='100%'>
                        <thead><tr class='text-white text-center'><tr><?php
                            \singleton\table::getInstance( )->th( 'ID', 'ID' );
                            \singleton\table::getInstance( )->th( 'Territory', 'Territory' );
                            \singleton\table::getInstance( )->th( 'Customer', 'Customer' );
                            \singleton\table::getInstance( )->th( 'Location', 'Location' );
                            \singleton\table::getInstance( )->th( 'Job', 'Job' );
                            \singleton\table::getInstance( )->th( 'Type', 'Type' );
                            \singleton\table::getInstance( )->th( 'Date', 'Date' );
                            \singleton\table::getInstance( )->th( 'Due', 'Due' );
                            \singleton\table::getInstance( )->th( 'Original', 'Original' );
                            \singleton\table::getInstance( )->th( 'Balance', 'Balance' );
                            \singleton\table::getInstance( )->th( 'Description', 'Description' );
                        ?></tr><tr class='desktop'><?php
                                \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
                                \singleton\table::getInstance( )->th_input( 'Territory', isset( $_GET[ 'Territory' ] ) ? $_GET[ 'Territory' ] : null );
                                \singleton\table::getInstance( )->th_input( 'Customer', isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null );
                                \singleton\table::getInstance( )->th_input( 'Location', isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null );
                                \singleton\table::getInstance( )->th_input( 'Job', isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null );
                                \singleton\table::getInstance( )->th_input( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null );
                                \singleton\table::getInstance( )->th_input( 'Date', isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null );
                                \singleton\table::getInstance( )->th_input( 'Due', isset( $_GET[ 'Due' ] ) ? $_GET[ 'Due' ] : null );
                                \singleton\table::getInstance( )->th_input( 'Original', isset( $_GET[ 'Original' ] ) ? $_GET[ 'Original' ] : null );
                                \singleton\table::getInstance( )->th_input( 'Balance', isset( $_GET[ 'Balance' ] ) ? $_GET[ 'Balance' ] : null );
                                \singleton\table::getInstance( )->th_input( 'Description', isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null );
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
} else {?><html><head><script>document.location.href='../login.php?Forward=collections.php';</script></head></html><?php }?>