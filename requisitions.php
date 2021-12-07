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
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
    <?php require(bin_php . 'element/navigation.php');?>
    <div id='page-wrapper' class='content'>
      <div class='card card-full card-primary border-0'>
        <div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Requisition( 1 );?> Requisitions</h4></div>
        <div class='card-body bg-dark'>
          <table id='Table_Requisitions' class='display' cellspacing='0' width='100%'>
              <thead><tr>
  							<th class='text-white border border-white' title='ID'><?php \singleton\fontawesome::getInstance( )->Proposal();?>ID</th>
  							<th class='text-white border border-white' title='User'><?php \singleton\fontawesome::getInstance( )->User();?>User</th>
                <th class='text-white border border-white' title='Item'><?php \singleton\fontawesome::getInstance( )->List1();?>Items</th>
  							<th class='text-white border border-white' title='Date'><?php \singleton\fontawesome::getInstance( )->Calendar();?>Date</th>
  							<th class='text-white border border-white' title='Required'><?php \singleton\fontawesome::getInstance( )->Description();?>Required</th>
  							<th class='text-white border border-white' title='Location'><?php \singleton\fontawesome::getInstance( )->Location();?>Location</th>
  							<th class='text-white border border-white' title='Drop Off'><?php \singleton\fontawesome::getInstance( )->Location();?>Drop Off</th>
  							<th class='text-white border border-white' title='Unit'><?php \singleton\fontawesome::getInstance( )->Unit();?>Units</th>
  							<th class='text-white border border-white' title='Job'><?php \singleton\fontawesome::getInstance( )->Job();?>Jobs</th>
              </tr><tr>
                <th><input type='text' class='form-control edit' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID'] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                <th><input type='text' class='form-control edit' name='User' placeholder='User' value='<?php echo isset( $_GET[ 'User'] ) ? $_GET[ 'User' ] : null;?>' /></th>
                <th><input type='text' class='form-control edit' name='Item' placeholder='Item' value='<?php echo isset( $_GET[ 'Item'] ) ? $_GET[ 'Item' ] : null;?>' /></th>
                <th><input type='text' class='form-control edit' name='Date' placeholder='Date' value='<?php echo isset( $_GET[ 'Date'] ) ? $_GET[ 'Date' ] : null;?>' /></th>
                <th><input type='text' class='form-control edit' name='Required' placeholder='Required' value='<?php echo isset( $_GET[ 'Required'] ) ? $_GET[ 'Required' ] : null;?>' /></th>
                <th><input type='text' class='form-control edit' name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location'] ) ? $_GET[ 'Location' ] : null;?>' /></th>
                <th><input type='text' class='form-control edit' name='Drop Off' placeholder='Drop Off' value='<?php echo isset( $_GET[ 'Drop Off'] ) ? $_GET[ 'Drop Off' ] : null;?>' /></th>
                <th><input type='text' class='form-control edit' name='Unit' placeholder='Unit' value='<?php echo isset( $_GET[ 'Unit'] ) ? $_GET[ 'Unit' ] : null;?>' /></th>
                <th><input type='text' class='form-control edit' name='Job' placeholder='Job' value='<?php echo isset( $_GET[ 'Job'] ) ? $_GET[ 'Job' ] : null;?>' /></th>
              </tr>
						</thead>
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
