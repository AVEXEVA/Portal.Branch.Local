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
        ||  !isset( $Privileges[ 'Job' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'job.php'
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
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php' ); ?>
    <?php require( bin_css  . 'index.php' ); ?>
    <?php require( bin_js   . 'index.php' ); ?>
</head>
<body onload='finishLoadingPage();'>
  <div id='wrapper'>
    <?php require( bin_php . 'element/navigation.php');?>
    <?php require( bin_php . 'element/loading.php');?>
    <div id='page-wrapper' class='content'>
			<div class='card card-full card-primary border-0'>
				<div class='card-heading'><h3><?php \singleton\fontawesome::getInstance( )->Customer();?> Leads</h3></div>
				<div class='card-body bg-dark'>
          <table id='Table_Leads' class='display' cellspacing='0' width='100%'>
						<thead><tr>
              <th class='text-white border border-white' title='ID'>ID</th>
              <th class='text-white border border-white' title='Name'>Name</th>
              <th class='text-white border border-white' title='Customer'>Customer</th>
              <th class='text-white border border-white' title='Type'>Type</th>
              <th class='text-white border border-white' title='Street'>Street</th>
              <th class='text-white border border-white' title='City'>City</th>
              <th class='text-white border border-white' title='State'>State</th>
              <th class='text-white border border-white' title='Zip'>Zip</th>

            </tr><tr>
              <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Name'><input class='redraw form-control' type='text' name='Name' placeholder='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Customer'><input class='redraw form-control' type='text' name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Type'><input class='redraw form-control' type='text' name='Type' placeholder='Type' value='<?php echo isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Street'><input class='redraw form-control' type='text' name='Street' placeholder='Street' value='<?php echo isset( $_GET[ 'Street' ] ) ? $_GET[ 'Street' ] : null;?>' /></th>
              <th class='text-white border border-white' title='City'><input class='redraw form-control' type='text' name='City' placeholder='City' value='<?php echo isset( $_GET[ 'City' ] ) ? $_GET[ 'City' ] : null;?>' /></th>
              <th class='text-white border border-white' title='State'><input class='redraw form-control' type='text' name='State' placeholder='State' value='<?php echo isset( $_GET[ 'State' ] ) ? $_GET[ 'State' ] : null;?>' /></th>
              <th class='text-white border border-white' title='Zip'><input class='redraw form-control' type='text' name='Zip' placeholder='Zip' value='<?php echo isset( $_GET[ 'Zip' ] ) ? $_GET[ 'Zip' ] : null;?>' /></th>

            </tr></thead>
					</table>
				</div>
		  </div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=customers.php';</script></head></html><?php }?>
