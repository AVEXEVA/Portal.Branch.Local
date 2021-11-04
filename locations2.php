<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = sqlsrv_query(
    	$NEI,
    	"	SELECT *
			FROM   Connection
			WHERE  Connection.Connector = ?
		       	   AND Connection.Hash  = ?;",
		array(
			$_SESSION['User'],
			$_SESSION['Hash']
		)
	);
    $Connection = sqlsrv_fetch_array( $r, SQLSRV_FETCH_ASSOC );
    $r = sqlsrv_query(
    	$NEI,
    	"	SELECT 	*,
		       		Emp.fFirst AS First_Name,
			   		Emp.Last   AS Last_Name
			FROM   	Emp
			WHERE  	Emp.ID = ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
    $User = sqlsrv_fetch_array( $r );
	$r = sqlsrv_query(
		$NEI,
		"	SELECT *
			FROM   Privilege
			WHERE  Privilege.User_ID = ?;",
		array(
			$_SESSION['User']
		)
	);
	$Privileges = array( );
	if( $r ){ while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if(	!isset( $Connection[ 'ID' ] )
	   	|| !isset($Privileges[ 'Location' ] )
	  		|| $Privileges[ 'Location' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Location' ][ 'Group_Privilege' ] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query(
			$NEI,
			"	INSERT INTO Activity([User], [Date], [Page])
				VALUES(?,?,?);",
			array(
				$_SESSION['User'],
				date("Y-m-d H:i:s"),
				'Locations.php'
			)
		);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title><?php echo $_SESSION[ 'Branch' ] == '' ? 'Nouveau' : $_SESSION[ 'Branch' ]; ?> | Portal</title>
    <?php require('cgi-bin/css/index.php');?>
    <style>#Table_Locations { font-size:12px; }</style>
    <?php require('cgi-bin/js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require('cgi-bin/php/element/navigation/index.php');?>
        <?php require('cgi-bin/php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading">
                    <div class='row'>
                        <div class='col-xs-10'><h4><?php $Icons->Location( 1 );?> Locations</div>
                        <div class='col-xs-2'><button style='width:100%;color:black;' onClick="$('#Filters').toggle();">+/-</button></div>
                    </div>
                </div>
				<div class="panel-body no-print" id='Filters' style='border-bottom:1px solid #1d1d1d;'>
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
                    	<div class='col-xs-4'>Customer:</div>
                    	<div class='col-xs-8'><input type='text' name='Customer' placeholder='Customer' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>City:</div>
                    	<div class='col-xs-8'><input type='text' name='City' placeholder='City' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Street:</div>
                    	<div class='col-xs-8'><input type='text' name='Street' placeholder='Street' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Maintained:</div>
                    	<div class='col-xs-8'><select name='Maintained' onChange='redraw( );'>
		                	<option value=''>Select</option>
		                	<option value='1'>Active</option>
		                	<option value='0'>Inactive</option>
		                </select></div>
		            </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Status:</div>
                    	<div class='col-xs-8'><select name='Status' onChange='redraw( );'>
		                	<option value=''>Select</option>
		                	<option value='0'>Active</option>
		                	<option value='1'>Inactive</option>
		                </select></div>
		            </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </div>
				<div class='panel-body'>
					<table id='Table_Locations' class='display' cellspacing='0' width='100%'>
						<thead><tr>
							<th title='ID'>ID</th>
							<th title='Name'>Name</th>
							<th title='Customer'>Customer</th>
							<th title='City'>City</th>
							<th title='Street'>Street</th>
							<th title='Maintained'>Maintained</th>
							<th title='Status'>Status</th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=contracts.php';</script></head></html><?php }?>
