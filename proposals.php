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
        ||  !isset( $Privileges[ 'Proposal' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Proposal' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'proposals.php'
        )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
	<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal </title>
  <?php
   $_GET[ 'Bootstrap' ] = '5.1';
   $_GET[ 'Entity_CSS' ] = 1;
   require( bin_meta . 'index.php');
   require( bin_css  . 'index.php');
   require( bin_js   . 'index.php');
  ?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION[ 'Toggle_Menu' ] : null;?>">
        <?php require( bin_php.'element/navigation.php');?>
        <div id="page-wrapper" class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading bg-white text-black"><h4><?php \singleton\fontawesome::getInstance( )->Proposal();?> Proposals</h4></div>
                <div class="mobile card-body bg-dark text-white"><form action='proposals.php'>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null, false, false, false, 'redraw' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Contact', isset( $_GET[ 'Contact' ] ) ? $_GET[ 'Contact' ] : null, false, false, false, 'redraw' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Customer', isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null, false, false, false, 'redraw' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Location', isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null, false, false, false, 'redraw' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Job', isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null, false, false, false, 'redraw' );?>
                  <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Title', isset( $_GET[ 'Title' ] ) ? $_GET[ 'Title' ] : null, false, false, false, 'redraw' );?>
                </div>
                <div class="card-body bg-darker ">
                    <table id='Table_Proposals' class='display' cellspacing='0' width='100%'>
                        <thead class='text-white border border-white'><?php
                          \singleton\table::getInstance( )->th( 'ID', 'ID' );
                          \singleton\table::getInstance( )->th( 'Territory', 'Territory' );
                          \singleton\table::getInstance( )->th( 'Customer', 'Customer' );
                          \singleton\table::getInstance( )->th( 'Location', 'Location' );
                          \singleton\table::getInstance( )->th( 'Contact', 'Contact' );
                          \singleton\table::getInstance( )->th( 'Title', 'Title' );
                          \singleton\table::getInstance( )->th( 'Status', 'Status' );
                          \singleton\table::getInstance( )->th( 'Phone', 'Phone' );
                          \singleton\table::getInstance( )->th( 'Email', 'Email' );
                          \singleton\table::getInstance( )->th( 'Address', 'Address' );
                          \singleton\table::getInstance( )->th( 'Date', 'Date' );
                          \singleton\table::getInstance( )->th( 'Job', 'Job' );
                          \singleton\table::getInstance( )->th( 'Cost', 'Cost' );
                          \singleton\table::getInstance( )->th( 'Price', 'Price' );
                        ?></tr><tr class='form-desktop'><?php
                          \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Territory', isset( $_GET[ 'Territory' ] ) ? $_GET[ 'Territory' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Customer', isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Location', isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Contact', isset( $_GET[ 'Contact' ] ) ? $_GET[ 'Contact' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Title', isset( $_GET[ 'Title' ] ) ? $_GET[ 'Title' ] : null );
                          \singleton\table::getInstance( )->th_select( 'Status', isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null, array( 0 => 'Open', 1 => 'Canceled',2 => 'Withdrawn', 3 => 'Disqualified',4 => 'Award Successful') );
                           \singleton\table::getInstance( )->th_input( 'Phone', isset( $_GET[ 'Phone' ] ) ? $_GET[ 'Phone' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Email', isset( $_GET[ 'Email' ] ) ? $_GET[ 'Email' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Address', isset( $_GET[ 'Address' ] ) ? $_GET[ 'Address' ] : null );
                        \singleton\table::getInstance( )->th_input( 'Date', isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Job', isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Cost', isset( $_GET[ 'Cost' ] ) ? $_GET[ 'Cost' ] : null );
                          \singleton\table::getInstance( )->th_input( 'Price', isset( $_GET[ 'Price' ] ) ? $_GET[ 'Price' ] : null );
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
} else {?><html><head><script>document.location.href='../login.php?Forward=proposals.php';</script></head></html><?php }?>
