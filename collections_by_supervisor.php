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
	   	|| !isset($My_Privileges['Admin'])
	  		|| $My_Privileges['Admin']['User_Privilege']  < 4
	  		|| $My_Privileges['Admin']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "units.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>    
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h3><?php $Icons->Unit();?> My Units</h3></div>
				<div class="panel-body">
					<div class="row">
						<?php 
						$r = sqlsrv_query($NEI,"
							SELECT   Elev.Type      AS Type,
									 Count(Elev.ID) AS Count_of_Units
							FROM     Elev
							GROUP BY Elev.Type
							ORDER BY Elev.Type ASC
						;",array());

						$Units = array();
						if($r){while($Unit = sqlsrv_fetch_array($r)){
							$Units[$Unit['Type']] = $Unit['Count_of_Units'];
						}}
						?>
						<style>
						@media (min-width: 1200px) {
							.col-lg-1dot5 {
								width:12.5% !important;;
							}
						</style>
						<?php /*foreach($Units as $Type=>$Count){?>

						<div class="col-lg-2 col-md-2 col-xs-4" onClick="">
							<div class="panel panel-primary"><div class="panel-heading">
								<div class="row">
									<?php if(!isMobile()){?><div class="col-xs-3">
										<i class="fa fa-cogs fa-3x"></i>
									</div><?php }?>
									<div class="col-xs-<?php if(isMobile()){?>12 text-center<?php } else {?>9 text-right<?php }?> " style='min-height:75px;'>
										<div class="<?php if(isMobile()){?>medium<?php } else {?>medium<?php }?>"><?php
											echo number_format($Count);	
										?></div>
										<div><?php echo $Type;?></div>
									</div>
								</div>
							</div></div>
						</div><?php }*/?>
					</div>
					<table id='Table_Collections' class='display' cellspacing='0' width='100%'>
						<thead>
							<tr>
								<th>Territory</th>
								<th class='sum'>Total</th>
								<th class='sum'>Maintenance</th>
								<th class='sum'>Modernization</th>
								<th class='sum'>Repair</th>
								<th class='sum'>XCalls</th>
								<th class='sum'>Lawsuits</th>
								<th class='sum'>Testing</th>
								<th class='sum'>Violations</th>
								<th class='sum'>Other</th>
								<th class='sum'>Billing Only</th>
								<th class='sum'>Loans</th>
								<th class='sum'>90 Days</th>
								<th class='sum'>180 Days</th>
								<th class='sum'>365 Days</th>
								<th class='sum'>2 Years</th>
								<th class='sum'>3 Years</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
								<th></th>
							</tr>
						</tfoot>
					</table>
				</div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>    

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
    <script>
	//function hrefUnits(){hrefRow("Table_Units","unit");}
	var Table_Collections = $('#Table_Collections').DataTable( {
		"ajax": "cgi-bin/php/get/Collections_by_Supervisor.php",
		"columns": [
			{ 
				"data": "Territory"
			},{ 
				"data": "Total_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{ 
				"data": "Maintenance_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{ 
				"data": "Modernization_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{ 
				"data": "Repair_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{ 
				"data": "XCALL_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{ 
				"data": "Lawsuits_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{ 
				"data": "Testing_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{ 
				"data": "Violations_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{
				"data": "Other_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{
				"data": "Billing_Only_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{
				"data": "Loans_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{
				"data" :"Ninety_Days_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{
				"data" :"One_Eighty_Days_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{
				"data" :"Three_Sixty_Five_Days_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{
				"data" :"Two_Years_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			},{
				"data" :"Three_Years_Past_Due",
				render:function(data){return "$" + parseFloat(data).toLocaleString();}
			}
		],
		"footerCallback": function(row, data, start, end, display) {
		  var api = this.api();

		  api.columns('.sum', {
			page: 'current'
		  }).every(function() {
			var sum = this
			  .data()
			  .reduce(function(a, b) {
				var x = parseFloat(a) || 0;
				var y = parseFloat(b) || 0;
				return x + y;
			  }, 0);
			console.log(sum); //alert(sum);
			$(this.footer()).html("$" + parseFloat(sum).toLocaleString());
		  });
		},
		"buttons":[
			{
				extend: 'collection',
				text: 'Export',
				buttons: [
					'copy',
					'excel',
					'csv',
					'pdf',
					'print'
				]
			}
		],
		<?php require('cgi-bin/js/datatableOptions.php');?>
	} );
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>