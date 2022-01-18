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
      ||  !isset( $Privileges[ 'Territory' ] )
      || 	!check( privilege_read, level_group, $Privileges[ 'Territory' ] )
  ){ ?><?php require('404.html');?><?php }
  else {
    \singleton\database::getInstance( )->query(
      null,
      " INSERT INTO Activity([User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        date('Y-m-d H:i:s'),
        'territories.php'
    )
  );
?><!DOCTYPE html>
<html lang='en'>
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php
    $_GET[ 'Bootstrap' ] = '5.1';
    $_GET[ 'Entity_CSS' ] = 1;
    require( bin_meta . 'index.php');
    require( bin_css  . 'index.php');
    require( bin_js   . 'index.php');
  ?>
</head>
<body>
	<div id='wrapper'>
    <?php require( bin_php . 'element/navigation.php' );?>
    <div id='page-wrapper' class='content'>
			<div class="card card-full card-primary border-0">
        <form method='GET' action='territories.php'>
          <div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Territory( 1 );?> Territories</h4></div>
  				<div class="mobile card-body bg-dark text-white"><form action='territories.php'>
            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null, false, false, false, 'redraw' );?>
            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Employee', isset( $_GET[ 'Employee_Name' ] ) ? $_GET[ 'Employee_Name' ] : null, false, false, false, 'redraw' );?>
            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Locations', isset( $_GET[ 'Locations' ] ) ? $_GET[ 'Locations' ] : null, false, false, false, 'redraw' );?>
            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Units', isset( $_GET[ 'Units' ] ) ? $_GET[ 'Units' ] : null, false, false, false, 'redraw' );?>
            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Leads', isset( $_GET[ 'Leads' ] ) ? $_GET[ 'Leads' ] : null, false, false, false, 'redraw' );?>
            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Proposals', isset( $_GET[ 'Proposals' ] ) ? $_GET[ 'Proposals' ] : null, false, false, false, 'redraw' );?>
            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Collections', isset( $_GET[ 'Collections' ] ) ? $_GET[ 'Collections' ] : null, false, false, false, 'redraw' );?>
            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Invoices', isset( $_GET[ 'Invoices' ] ) ? $_GET[ 'Invoices' ] : null, false, false, false, 'redraw' );?>
          </div>
          <div class="card-body bg-dark">
    				<table id='Table_Territories' class='display' cellspacing='0' width='100%'>
    					<thead class='text-white border border-white'><?php
                \singleton\table::getInstance( )->th( 'ID', 'ID' );
                \singleton\table::getInstance( )->th( 'Employee', 'Employee' );
                \singleton\table::getInstance( )->th( 'Locations', 'Locations' );
                \singleton\table::getInstance( )->th( 'Units', 'Units' );
                \singleton\table::getInstance( )->th( 'Leads', 'Leads' );
                \singleton\table::getInstance( )->th( 'Proposal', 'Proposal' );
                \singleton\table::getInstance( )->th( 'Collection', 'Collection' );
                \singleton\table::getInstance( )->th( 'Invoices', 'Invoices' );
    			    ?><tr class='desktop'><?php
              \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
              \singleton\table::getInstance( )->th_input( 'Employee', isset( $_GET[ 'Employee_Name' ] ) ? $_GET[ 'Employee_Name' ] : null );
              \singleton\table::getInstance( )->th_input( 'Locations', isset( $_GET[ 'Locations' ] ) ? $_GET[ 'Locations' ] : null );
              \singleton\table::getInstance( )->th_input( 'Units', isset( $_GET[ 'Units' ] ) ? $_GET[ 'Units' ] : null );
              \singleton\table::getInstance( )->th_input( 'Lead', isset( $_GET[ 'Lead' ] ) ? $_GET[ 'Lead' ] : null );
              \singleton\table::getInstance( )->th_input( 'Proposal', isset( $_GET[ 'Proposal' ] ) ? $_GET[ 'Proposal' ] : null );
              \singleton\table::getInstance( )->th_input( 'Collection', isset( $_GET[ 'Collection' ] ) ? $_GET[ 'Collection' ] : null );
              \singleton\table::getInstance( )->th_input( 'Invoice', isset( $_GET[ 'Invoice' ] ) ? $_GET[ 'Invoice' ] : null );
              ?></tr></thead>
            </table>
    		  </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=territories.php';</script></head></html><?php }?>
