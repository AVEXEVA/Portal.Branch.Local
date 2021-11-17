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
	$result = \singleton\database::getInstance( )->query(
		'Portal',
		"   SELECT  [Privilege].[Access],
		            [Privilege].[Owner],
		            [Privilege].[Group],
		            [Privilege].[Other]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$Privileges = array();
	if( $result ){ while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access' ] ] = $Privilege; } }
    if(		!isset( $Connection[ 'ID' ] )
	   	|| 		!isset($Privileges[ 'Route' ] )
	  		|| 	$Privileges[ 'Route' ][ 'Owner' ]  < 4
	  		|| 	$Privileges[ 'Route' ][ 'Group' ] < 4
	  	    || 	$Privileges[ 'Route' ][ 'Other' ] < 4){
				?><?php require('404.html');?><?php }
    else {
      \singleton\database::getInstance( )->query(
          null,
      		" 	INSERT INTO Activity( [User], [Date], [Page] )
  				VALUES( ?, ?, ? );"
    		, array(
    			$_SESSION[ 'User' ],
    			date('Y-m-d H:i:s' ),
    			'routes.php'
    		)
    	);
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>    <?php require(PROJECT_ROOT.'css/index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper' class='<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>'>
      <?php require( bin_php . 'element/navigation.php');?>
      <?php require( bin_php . 'element/loading.php');?>
      <div id='page-wrapper' class='content'>
        <div class='panel panel-primary'>
            <div class='panel-heading'><h3>Routes</h3></div>
            <div class='panel-body no-print' id='Filters' style='border-bottom:1px solid #1d1d1d;'>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='row'>
                        <div class='col-xs-4'>Search:</div>
                        <div class='col-xs-8'><input type='text' name='Search' placeholder='Search' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='row'>
                    	<div class='col-xs-4'>Name:</div>
                    	<div class='col-xs-8'><input type='text' name='Name' placeholder='Name' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>User:</div>
                    	<div class='col-xs-8'><input type='text' name='Customer' placeholder='Customer' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </div>
            <div class='panel-body'>
                <table id='Table_Routes' class='display' cellspacing='0' width='100%'>
                    <thead>
                        <th title='ID'>ID</th>
                        <th title='Name'>Name</th>
                        <th title='User'>User</th>
                        <th title='Locations'>Locations</th>
                        <th title='Units'>Units</th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=routes.php';</script></head></html><?php }?>
