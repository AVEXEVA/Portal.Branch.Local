<?php
session_start();
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Connection
		WHERE  Connection.Connector = ?
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID'])
	   	|| !isset($My_Privileges['Job'])
	  		|| $My_Privileges['Job']['User_Privilege']  < 4
	  		|| $My_Privileges['Job']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "jobs.php"));

if(isMobile() || true){?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<title>Nouveau Elevator Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading">
                    <div class='row'>
                        <div class='col-xs-10'><h4><?php $Icons->Job( 1 );?> Jobs</div>
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
                    	<div class='col-xs-4'>Location:</div>
                    	<div class='col-xs-8'><input type='text' name='Location' placeholder='Location' onChange='redraw( );' /></div>
                    </div>
                    <div class='row'>
                    	<div class='col-xs-4'>Type:</div>
                    	<div class='col-xs-8'><input type='text' name='Type' placeholder='Type' onChange='redraw( );' /></div>
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
					<table id='Table_Jobs' class='display' cellspacing='0' width='100%'>
						<thead><tr>
							<th title='ID'>ID</th>
							<th title='Name'>Name</th>
							<th title='Customer'>Customer</th>
							<th title='Location'>Location</th>
							<th title='Type'>Type</th>
							<th title='Status'>Status</th>
						</tr></thead>
					</table>
				</div>
            </div>
        </div>
    </div>
    <script src="../vendor/bootstrap/js/bootstrap.js"></script>
    <?php require('cgi-bin/js/datatables.php');?>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script> 
		var isChromium = window.chrome,
			winNav = window.navigator,
			vendorName = winNav.vendor,
			isOpera = winNav.userAgent.indexOf("OPR") > -1,
			isIEedge = winNav.userAgent.indexOf("Edge") > -1,
			isIOSChrome = winNav.userAgent.match("CriOS");
		var Table_Jobs = $('#Table_Jobs').DataTable( {
			dom 	   : 'tlp',
	        processing : true,
	        serverSide : true,
	        responsive : true,
	        autoWidth : false,
			paging    : true,
			searching : false,
			ajax      : {
	            url : 'cgi-bin/php/get/Jobs2.php',
	            data : function( d ){
	                d = {
	                    start : d.start,
	                    length : d.length,
	                    order : {
	                        column : d.order[0].column,
	                        dir : d.order[0].dir
	                    }
	                };
	                d.Search = $('input[name="Search"]').val( );
	                d.ID = $('input[name="ID"]').val( );
	                d.Name = $('input[name="Name"]').val( );
	                d.Customer = $('input[name="Customer"]').val( );
	                d.Location = $('input[name="Location"]').val( );
	                d.Type = $('input[name="Type"]').val( );
	                d.Status = $('select[name="Status"]').val( );
	                return d; 
	            }
	        },
			columns   : [
				{
					data 	  : 'ID'
				},{
					data : 'Name'
				},{
					data : 'Customer'
				},{
					data : 'Location'
				},{
					data : 'Type'
				},{
					data : 'Status'
				}
			]
		} );
		function redraw( ){ Table_Jobs.draw( ); }
		function hrefJobs(){hrefRow("Table_Jobs","job");}
		$("Table#Table_Jobs").on("draw.dt",function(){hrefJobs();});
	</script>
</body>
</html>
<?php
} else {
  $_GET['processing'] = 1;
  require("../beta/jobs.php");
}
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=jobs.php';</script></head></html><?php }?>
