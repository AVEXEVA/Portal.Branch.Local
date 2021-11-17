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
        ||  !isset( $Privileges[ 'Invoice' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Invoice' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'invoices.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo $_SESSION['Connection']['Branch'];?> | Portal</title>
    <?php $_GET [ 'Bootstrap' ] = '5.1'; ?>
    <?php require(bin_meta.'index.php');?>
    <?php require(bin_css.'index.php');?>
    <?php require(bin_js.'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require(bin_php.'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id='page-wrapper' class='content'>
            <div class='card card-full card-primary bg-dark text-white'>
                <div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Invoices</h4></div>
        				<div class='card-body form-mobile'><form action='invoices.php'>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                  <div class='row'>
                      <div class='col-4'>Search:</div>
                      <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Search' ] ) ? $_GET[ 'Search' ] : null;?>' /></div>
                  </div>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                  <div class='row'>
                    <div class='col-4'>Customer:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Locaton:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Locaton' ] ) ? $_GET[ 'Locaton' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Job:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Type:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Date:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Due:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Due' ] ) ? $_GET[ 'Due' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Origional:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Origional' ] ) ? $_GET[ 'Origional' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Balance:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Balance' ] ) ? $_GET[ 'Balance' ] : null;?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Description:</div>
                    <div class='col-8'><input class='redraw form-control' type='text' name='Search' placeholder='Search' value='<?php echo isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null;?>' /></div>
                  </div>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                </form></div>
                <div class='card-body'>
                    <table id='Table_Invoices' class='display' cellspacing='0' width='100%'>
                        <thead>
                          <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Location</th>
                            <th>Job</th>
							              <th>Type</th>
                            <th>Date</th>
                            <th>Due</th>
                            <th>Original</th>
                            <th>Balance</th>
                            <th>Description</th>
                        </tr>
                        <tr class='form-desktop'>
                          <th><input class='redraw form-control' type='text' name='Invoice #' placeholder='Invoice #' value='<?php echo isset( $_GET[ 'Invoice #' ] ) ? $_GET[ 'Invoice #' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Job' placeholder='Job' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Type' placeholder='Type' value='<?php echo isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Date' placeholder='Date' value='<?php echo isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Due' placeholder='Due' value='<?php echo isset( $_GET[ 'Due' ] ) ? $_GET[ 'Due' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Original' placeholder='Original' value='<?php echo isset( $_GET[ 'Original' ] ) ? $_GET[ 'Original' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Balance' placeholder='Balance' value='<?php echo isset( $_GET[ 'Balance' ] ) ? $_GET[ 'Balance' ] : null;?>' /></th>
                          <th><input class='redraw form-control' type='text' name='Description' placeholder='Description' value='<?php echo isset( $_GET[ 'Description' ] ) ? $_GET[ 'Description' ] : null;?>' /></th>
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
} else {?><script>document.location.href='../login.php?Forward=invoices.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
