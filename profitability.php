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
        ||  !isset( $Privileges[ 'Executive' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Executive' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'profitability.php'
        )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
       <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
       <?php  $_GET[ 'Entity_CSS' ] = 1;?>
       <?php	require( bin_meta . 'index.php');?>
       <?php	require( bin_css  . 'index.php');?>
       <?php  require( bin_js   . 'index.php');?>
</head>
<body>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
    <?php require(bin_php . 'element/navigation.php');?>
    <div id='page-wrapper' class='content'>
      <div class='card card-full card-primary border-0'>
        <div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Requisition( 1 );?> Profitability</h4></div>
        <div class='mobile card-body bg-dark' 'text-white'>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Customer', isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Profit', isset( $_GET[ 'Profit' ] ) ? $_GET[ 'Profit' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Profit_Percentage', isset( $_GET[ 'Profit_Percentage' ] ) ? $_GET[ 'Profit_Percentage' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Revenue', isset( $_GET[ 'Revenue' ] ) ? $_GET[ 'Revenue' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Material', isset( $_GET[ 'Material' ] ) ? $_GET[ 'Material' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Labor', isset( $_GET[ 'Labor' ] ) ? $_GET[ 'Labor' ] : null, false, false, false, 'redraw' );?>
        </div>
          <table id='Table_Profitability' class='display' cellspacing='0' width='100%'>
              <thead class='text-white border border-white'><?php
                \singleton\table::getInstance( )->th( 'Customer', 'Customer' );
                \singleton\table::getInstance( )->th( 'Profit', 'Profit' );
                \singleton\table::getInstance( )->th( 'Profit_Percentage', 'Profit_Percentage' );
                \singleton\table::getInstance( )->th( 'Revenue', 'Revenue' );
                \singleton\table::getInstance( )->th( 'Material', 'Material' );
                \singleton\table::getInstance( )->th( 'Labor', 'Labor' );
              ?><tr class='desktop'><?php
                \singleton\table::getInstance( )->th_input( 'Customer', isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null );
                \singleton\table::getInstance( )->th_input( 'Profit', isset( $_GET[ 'Profit' ] ) ? $_GET[ 'Profit' ] : null );
                \singleton\table::getInstance( )->th_input( 'Profit_Percentage', isset( $_GET[ 'Profit_Percentage' ] ) ? $_GET[ 'Profit_Percentage' ] : null );
                \singleton\table::getInstance( )->th_input( 'Revenue', isset( $_GET[ 'Revenue' ] ) ? $_GET[ 'Revenue' ] : null );
                \singleton\table::getInstance( )->th_input( 'Material', isset( $_GET[ 'Material' ] ) ? $_GET[ 'Material' ] : null );
                \singleton\table::getInstance( )->th_input( 'Labor', isset( $_GET[ 'Labor' ] ) ? $_GET[ 'Labor' ] : null );
					?></tr></thead>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
