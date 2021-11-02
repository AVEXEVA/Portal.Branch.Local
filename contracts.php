<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
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
    $User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$Privileges = array();
	if($r){while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}}
    if(	!isset($My_Connection['ID'])
	   	|| !isset($Privileges['Contract'])
	  		|| $Privileges['Contract']['User_Privilege']  < 4
	  		|| $Privileges['Contract']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "contracts.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    
    <title>Nouveau | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require('cgi-bin/css/index.php');?>
    <style>
        .form-group>label:first-child {
            min-width  : 175px;
            text-align : right;
        }
    </style>
    <?php require('cgi-bin/js/index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require('cgi-bin/php/element/navigation/index.php');?>
        <?php require('cgi-bin/php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h4><?php $Icons->Contract();?> Contracts</h4></div>
				<div class="panel-body no-print" id='Filters' style='border-bottom:1px solid #1d1d1d;'>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='form-group row'>
                        <label class='col-auto'>Search:</label>
                        <div class='col-auto'><input type='text' name='Search' placeholder='Search' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='form-group row'>
                    	<label class='col-auto'>ID:</label>
                    	<div class='col-auto'><input type='text' name='ID' placeholder='ID' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Customer:</label>
                    	<div class='col-auto'><input type='text' name='Customer' placeholder='Customer' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Location:</label>
                    	<div class='col-auto'><input type='text' name='Location' placeholder='Location' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Start:</label>
                    	<div class='col-auto'><input type='text' name='Start_Date' placeholder='Start Date' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>End:</label>
                    	<div class='col-auto'><input type='text' name='End_Date' placeholder='End Date' onChange='redraw( );' /></div>
                    </div>
                    <div class='form-group row'>
                    	<label class='col-auto'>Cycle:</label>
                    	<div class='col-auto'><select name='Cycle' onChange='redraw( );'>
                    		<option value=''>Select</option>
                    		<option value='0'>Monthly</option>
                    		<option value='1'>Bi-Monthly</option>
                    		<option value='2'>Quarterly</option>
                    		<option value='3'>Trimester</option>
                    		<option value='4'>Semi-Annually</option>
                    		<option value='5'>Annually</option>
                    		<option value='6'>Never</option>
                    	</select></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </div>
                <div class="panel-body">
            	<style>
                .hoverGray:hover {
                    background-color : gold !important;
                }
                table#Table_Contracts tbody tr, table#Table_Contracts tbody tr td a {
                    color : black !important;
                }
                table#Table_Contracts tbody tr:nth-child( even ) {
                    background-color : rgba( 240, 240, 240, 1 ) !important;
                }
                table#Table_Contracts tbody tr:nth-child( odd ) {
                    background-color : rgba( 255, 255, 255, 1 ) !important;
                }
                .paginate_button {
                	background-color : rgba( 255, 255, 255, .7 ) !important;
                }
                .paginate_button:hover {
                	color : white !important;
                }
                </style>
                <!--<div class='panel-body' style='padding:25px;'>
					<div class='row'>
						<div class='col-xs-1'><button onClick="filter();">Redraw</button></div>
						<div class='col-xs-1'><button onClick="filterActive();">Active</button></div>
						<div class='col-xs-1'><button onClick="filterExpired();">Expired</button></div>
						<div class='col-xs-1'><button onClick="filterExpiring();">Expiring</button></div>
					</div>
				</div>-->
				<div class='panel-body'>
					<table id='Table_Contracts' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
						<thead><tr>
							<th>ID</th>
							<th>Customer</th>
							<th>Location</th>
							<th>Job</th>
							<th>Start</th>
							<th>Amount</th>
							<th>Length</th>
							<th>Cycle</th>
							<th>End</th>
							<th>Esc. Factor</th>
							<th>Esc. Date</th>
							<th>Esc. Type</th>
							<th>Esc. Cycle</th>
							<th>Link</th>
							<th>Remarks</th>
						</tr></thead>
					</table>
				</div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
    <?php require('cgi-bin/js/datatables.php');?>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
		var isChromium = window.chrome,
			winNav = window.navigator,
			vendorName = winNav.vendor,
			isOpera = winNav.userAgent.indexOf("OPR") > -1,
			isIEedge = winNav.userAgent.indexOf("Edge") > -1,
			isIOSChrome = winNav.userAgent.match("CriOS");
		var Table_Contracts = $('#Table_Contracts').DataTable( {
			dom 	   : 'tp',
	        processing : true,
	        serverSide : true,
	        responsive : true,
	        autoWidth  : false,
			paging     : true,
			searching  : false,
			ajax       : {
	            url : 'cgi-bin/php/get/Contracts2.php',
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
                    d.Customer = $('input[name="Customer"]').val( );
                    d.Location = $('input[name="Location"]').val( );
                    d.Start_Date = $('input[name="Start_Date"]').val( );
                    d.End_Date = $('input[name="End_Date"]').val( );
                    d.Cycle = $('select[name="Cycle"]').val( );

                    /*d.ID = $('input[name="ID"]').val();
                    
                    d.Job = $('input[name="Job"]').val();
                    d.Start_Date_Start = $('input[name="Start_Date_Start"]').val();
                    d.Start_Date_End = $('input[name="Start_Date_End"]').val();
                    d.Amount_Start = $('input[name="Amount_Start"]').val();
                    d.Amount_End = $('input[name="Amount_End"]').val();
                    d.Length = $('input[name="Length"]').val();
                    d.Cycle = $('input[name="Cycle"]').val();
                    d.End_Date_Start = $('input[name="End_Date_Start"]').val();
                    d.End_Date_End = $('input[name="End_Date_End"]').val();
                    d.Escalation_Factor = $('input[name="Escalation_Factor"]').val(); 
                    d.Escalation_Date = $('input[name="Escalation_Date"]').val();
                    d.Escalation_Type = $('input[name="Escalation_Type"]').val();
                    d.Escalation_Cycle = $('input[name="Escalation_Cycle"]').val();
                    d.Link = $('input[name="Link"]').val();
                    d.Remarks = $('input[name="Remarks"]').val();*/
                    return d;
	            }
	        },
			columns: [ 
				{
					data 	: 'ID'
				},{
					data 	: 'Customer'
				},{
					data 	: 'Location'
				},{
					data 	: 'Job'
				},{
					data 	: 'Start_Date' 
				},{
					data 	: 'Amount'
				},{
					data 	: 'Length',
					render  : function( data ){ return data + ' months'; }
				},{
					data 	: 'Cycle'
				},{
					data 	: 'End_Date'
				},{
					data 	: 'Escalation_Factor'
				},{
					data 	: 'Escalation_Date'
				},{
					data 	: 'Escalation_Type'
				},{
					data 	: 'Escalation_Cycle'
				},{
					data 	: 'Link',
					render  : function( d ){ return d !== null ? "<a href='" + d + "'>" + d + "</a>" : ''; }
				},{
					data 	: 'Remarks'
				}
			]
		} );
		function redraw( ){ Table_Contracts.draw(); }
		function filter(){ redraw( ); }
		$('input[name="Start_Date"]').datepicker( { } );
		$('input[name="End_Date"]').datepicker( { } );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=contracts.php';</script></head></html><?php }?>
