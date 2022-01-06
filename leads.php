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
      ||  !isset( $Privileges[ 'Lead' ] )
      || 	!check( privilege_read, level_group, $Privileges[ 'Lead' ] )
  ){ ?><?php require('404.html');?><?php }
  else {
    \singleton\database::getInstance( )->query(
      null,
      " INSERT INTO Activity([User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        date('Y-m-d H:i:s'),
        'leads.php'
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
    <?php require( bin_php . 'element/navigation.php');?>
    <div id='page-wrapper' class='content'>
			<div class='card card-full card-primary border-0'>
				<div class='card-heading bg-white text-black'><h3><?php \singleton\fontawesome::getInstance( )->Customer();?> Leads</h3></div>
        <div class='mobile card-body bg-dark text-white'><?php
          \singleton\bootstrap::getInstance( )->card_row_form_input( 'Search', isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null, false, false, false, 'redraw' );
          \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null, false, false, false, 'redraw' );
        ?></div>
				<div class='card-body bg-dark'>
          <table id='Table_Leads' class='display' cellspacing='0' width='100%'>
  					<thead><tr><?php
              \singleton\table::getInstance( )->th( 'ID', 'ID' );
              \singleton\table::getInstance( )->th( 'Contact', 'Name' );
              \singleton\table::getInstance( )->th( 'Type', 'Type' );
              \singleton\table::getInstance( )->th( 'Customer', 'Customer' );
              \singleton\table::getInstance( )->th( 'Address', 'Address' );
              \singleton\table::getInstance( )->th( 'Probability', 'Probability' );
              \singleton\table::getInstance( )->th( 'Level', 'Level' );
              \singleton\table::getInstance( )->th( 'Status', 'Status' );
            ?></tr><tr class='desktop'><?php
              \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
              \singleton\table::getInstance( )->th_input( 'Contact', isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null );
              \singleton\table::getInstance( )->th_input( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null );
              \singleton\table::getInstance( )->th_autocomplete(
                'Customer', 'Customers',
                isset( $_GET[ 'Customer_ID' ] ) ? $_GET[ 'Customer_ID' ] : null,
                isset( $_GET[ 'Customer_Name' ] ) ? $_GET[ 'Customer_Name' ] : null
              );
              \singleton\table::getInstance( )->th_input( 'Address', isset( $_GET[ 'Address' ] ) ? $_GET[ 'Address' ] : null );
              \singleton\table::getInstance( )->th_input( 'Probability', isset( $_GET[ 'Probability' ] ) ? $_GET[ 'Probability' ] : null );
              \singleton\table::getInstance( )->th_input( 'Level', isset( $_GET[ 'Level' ] ) ? $_GET[ 'Level' ] : null );
              \singleton\table::getInstance( )->th_input( 'Status', isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null );
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
} else {?><html><head><script>document.location.href='../login.php?Forward=leads.php';</script></head></html><?php }?>
