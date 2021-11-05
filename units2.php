<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
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
	   	|| !isset($Privileges[ 'Unit' ] )
	  		|| $Privileges[ 'Unit' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Unit' ][ 'Group_Privilege' ] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query(
			$NEI,
			"	INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
				VALUES(?,?,?);",
			array(
				$_SESSION['User'],
				date("Y-m-d H:i:s"), 
				'units.php'
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
    <style>#Filters { max-width: 500px; }</style>
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
                        <div class='col-xs-10'><h4><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?> Units</div>
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
                    	<div class='col-xs-4'>City ID:</div>
                    	<div class='col-xs-8'><input type='text' name='City_ID' placeholder='City ID' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Location:</div>
                    	<div class='col-xs-8'><input type='text' name='Location' placeholder='Location' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Building ID:</div>
                    	<div class='col-xs-8'><input type='text' name='Building_ID' placeholder='Building ID' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Type:</div>
                    	<div class='col-xs-8'><select name='Type' onChange='redraw( );'><?php
			                	$rResult = sqlsrv_query(
			                		$NEI,
			                		"	SELECT 	 Elev.Type
			                			FROM 	 Elev
			                			GROUP BY Elev.Type;"
			                	);
			                	$Types = array( );
			                	?><option value=''>Select</option><?php
			                	if( $rResult ){ while( $Type = sqlsrv_fetch_array( $rResult )[ 'Type' ] ){
			                		?><option value='<?php echo $Type;?>'><?php echo $Type;?></option><?php
			                	} }
			                ?></select></div>
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
					<table id='Table_Units' class='display' cellspacing='0' width='100%' style='font-size:12px'>
						<thead><tr>
							<th title='ID'>ID</th>
							<th title='City ID'>City ID</th>
							<th title='Location'>Location</th>
							<th title='Building ID'>Building ID</th>
							<th title='Type'>Type</th>
							<th title='Status'>Status</th>
						</tr></thead>
					</table>
				</div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    
    <?php require('cgi-bin/js/datatables.php');?>
    
    <script>
		var isChromium = window.chrome,
			winNav = window.navigator,
			vendorName = winNav.vendor,
			isOpera = winNav.userAgent.indexOf("OPR") > -1,
			isIEedge = winNav.userAgent.indexOf("Edge") > -1,
			isIOSChrome = winNav.userAgent.match("CriOS");
		var Table_Units = $('#Table_Units').DataTable( {
			dom 	   : 'tlp',
	        processing : true,
	        serverSide : true,
	        responsive : true,
			ajax      : {
	            url : 'cgi-bin/php/get/Units2.php',
	            data : function( d ){
	                d = {
	                    start : d.start,
	                    length : d.length,
	                    order : {
	                        column : d.order[0].column,
	                        dir : d.order[0].dir
	                    }
	                };
	                d.Search = $('input[name="Search"]').val();
	                d.ID = $('input[name="ID"]').val( );
	                d.City_ID = $('input[name="City_ID"]').val( );
	                d.Location = $('input[name="Location"]').val( );
	                d.Building_ID = $('input[name="Building_ID"]').val( );
	                d.Type = $('select[name="Type"]').val( );
	                d.Status = $('select[name="Status"]').val( );
	                return d; 
	            }
	        },
			columns   : [
				{
					data      : 'ID',
					className : 'hidden'
				},{
					data : 'City_ID'
				},{
					data : 'Location'
				},{
					data : 'Building_ID'
				},{
					data : 'Type'
				},{
					data : 'Status',
					render:function(data){
						switch(data){
							case 0:return 'Active';
							case 1:return 'Inactive';
							case 2:return 'Demolished';
							case 3:return 'XXX';
							case 4:return 'YYY';
							case 5:return 'ZZZ';
							case 6:return 'AAA';
							default:return 'Error';
						}
					}
				}
			],
	        autoWidth : false,
			paging    : true,
			searching : false
		} );
		function redraw( ){ Table_Units.draw( ); }
		function hrefUnits(){hrefRow("Table_Units","unit");}
		$("Table#Table_Units").on("draw.dt",function(){hrefUnits();});
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=contracts.php';</script></head></html><?php }?>
