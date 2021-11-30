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
        ||  !isset( $Privileges[ 'Division' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Division' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'divisions.php'
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
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?> Tickets</h4></div>
                <div class="form-mobile card-body bg-dark text-white"><form method='GET' action='locations.php'>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                  <div class='row'>
                      <div class='col-4'>Search:</div>
                      <div class='col-8'><input type='text' name='Search' placeholder='Search' class='redraw' /></div>
                  </div>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                  <div class='row'>
                    <div class='col-4'>ID:</div>
                    <div class='col-8'><input type='text' name='Person' placeholder='Person' class='redraw' value='<?php echo $_GET[ 'Person' ];?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Name:</div>
                    <div class='col-8'><input type='text' name='Customer' placeholder='Customer' class='redraw' value='<?php echo $_GET[ 'Customer' ];?>' /></div>
                  </div>
                </form></div>
                <div class="card-body bg-dark">
                    <table id='Table_Divisions' class='display' cellspacing='0' width='100%'>
                        <thead><tr class='text-center'>
                            <th class='text-white border border-white' title='ID'>ID</th>
                            <th class='text-white border border-white' title='Name'>Name</th>
                            <th class='text-white border border-white' title='Location'>Location</th>
                            <th class='text-white border border-white' title='Units'>Units</th>
                            <th class='text-white border border-white' title='Violation'>Violation</th>
                            <th class='text-white border border-white' title='Tickets'>Tickets</th>
                        </tr><tr class='form-desktop'>
                            <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Name'><input class='redraw form-control' type='text' name='Name' placeholder='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Customers'><input class='redraw form-control' type='text' name='Customers' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Locations'><input class='redraw form-control' type='text' name='Locations' placeholder='Units' value='<?php echo isset( $_GET[ 'Units' ] ) ? $_GET[ 'Units' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Units'><input class='redraw form-control' type='text' name='Units' placeholder='Units' value='<?php echo isset( $_GET[ 'Units' ] ) ? $_GET[ 'Units' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Jobs'><input class='redraw form-control' type='text' name='Jobs' placeholder='Violation' value='<?php echo isset( $_GET[ 'Violation' ] ) ? $_GET[ 'Violation' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Tickets'><input class='redraw form-control' type='text' name='Tickets' placeholder='Tickets' value='<?php echo isset( $_GET[ 'Tickets' ] ) ? $_GET[ 'Tickets' ] : null;?>' /></th>
                        </tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php }
} else {?><script>document.location.href='../login.php?Forward=divisions.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
