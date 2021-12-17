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
        ||  !isset( $Privileges[ 'Requisition' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Requisition' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'requisitions.php'
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
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
    <?php require(bin_php . 'element/navigation.php');?>
    <div id='page-wrapper' class='content'>
      <div class='card card-full card-primary bg-dark text-white'>
        <div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Requisition( 1 );?> Requisitions</h4></div>
        <div class='card-body mobile'><form action='requisitions.php'>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'User', isset( $_GET[ 'User' ] ) ? $_GET[ 'User' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Items', isset( $_GET[ 'Items' ] ) ? $_GET[ 'Items' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Date', isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Required', isset( $_GET[ 'Required' ] ) ? $_GET[ 'Required' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Location', isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Drop Off', isset( $_GET[ 'Drop Off' ] ) ? $_GET[ 'Drop Off' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Units', isset( $_GET[ 'Units' ] ) ? $_GET[ 'Units' ] : null, false, false, false, 'redraw' );?>
          <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Jobs', isset( $_GET[ 'Jobs' ] ) ? $_GET[ 'Jobs' ] : null, false, false, false, 'redraw' );?>
        </div>
        <div class='card-body bg-darker'>
          <table id='Table_Requisitions' class='display' cellspacing='0' width='100%'>
              <thead class='text-white border border-white'>
                <?php
                \singleton\table::getInstance( )->th( 'Proposal', 'Proposal' );
                \singleton\table::getInstance( )->th( 'User', 'User' );
                \singleton\table::getInstance( )->th( 'Item', 'Item' );
                \singleton\table::getInstance( )->th( 'Calendar', 'Calendar' );
                \singleton\table::getInstance( )->th( 'Description', 'Description' );
                \singleton\table::getInstance( )->th( 'Location', 'Location' );
                \singleton\table::getInstance( )->th( 'Drop Off', 'Drop Off' );
                \singleton\table::getInstance( )->th( 'Units', 'Units' );
                \singleton\table::getInstance( )->th( 'Jobs', 'Jobs' );
              ?></tr>
              <tr class='desktop'><?php
                \singleton\table::getInstance( )->th_input( 'ID', isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null );
                \singleton\table::getInstance( )->th_input( 'User', isset( $_GET[ 'User' ] ) ? $_GET[ 'User' ] : null );
                \singleton\table::getInstance( )->th_input( 'Item', isset( $_GET[ 'Item' ] ) ? $_GET[ 'Item' ] : null );
                \singleton\table::getInstance( )->th_input( 'Date', isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null );
                \singleton\table::getInstance( )->th_input( 'Required', isset( $_GET[ 'Required' ] ) ? $_GET[ 'Required' ] : null );
                \singleton\table::getInstance( )->th_input( 'Location', isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null );
                \singleton\table::getInstance( )->th_input( 'Drop Off', isset( $_GET[ 'Drop Off' ] ) ? $_GET[ 'Drop Off' ] : null );
                \singleton\table::getInstance( )->th_input( 'Unit', isset( $_GET[ 'Unit' ] ) ? $_GET[ 'Unit' ] : null );
                \singleton\table::getInstance( )->th_input( 'Job', isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null );
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
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
