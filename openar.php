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
	  		|| $My_Privileges['Admin']['Group_Privilege'] < 4
	  		|| $My_Privileges['Admin']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "customers.php"));

if(isMobile()){?>
?><!DOCTYPE html>
<html lang="en"style="min-height:100%;<?php if(!isMobile()){ ?>height:100%;background-image:url('http://www.nouveauelevator.com/Images/Backgrounds/New_York_City_Skyline.jpg');webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;<?php } ?>">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
	<title>Nouveau Texas | Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
	<style>
		.panel {background-color:transparent !important;}
		.panel > div.panel-body.white-background {background-color:rgba(255,255,255,.7) !important;}
		.nav-tabs > li:not(.active) {background-color:rgba(255,255,255,.6) !important;}
		.panel-heading {font-family: 'BankGothic' !important;}
		.shadow {box-shadow:0px 5px 5px 0px;}
		<?php if(isMobile()){?>
		.panel-body {padding:0px !important;}
		<?php }?>

		@media print {
			div#wrapper {overflow:visible;}
		}
	</style>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);height:100%;">
    <div id='container' style='min-height:100%;height:100%;'>
		<div id="wrapper" style='height:100%;'>
			<?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
			<?php require(PROJECT_ROOT.'php/element/loading.php');?>
			<div id="page-wrapper" class='content' style='background-color:transparent !important;'>
				<div class="row">
					<div class="col-lg-12">
						<div class="panel panel-primary">
							<div class="panel-heading"><h4><?php $Icons->Customer();?> Customers</h4></div>
							<div class="panel-body">
								<div id='Form_Customer'>
									<div class="panel panel-primary">
										<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h4 style='display:block;'>Location Form</h4></div>
										<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
											<div style='display:block !important;'>
												<fieldset >
													<legend>Names</legend>
													<editor-field name='ID'></editor-field>
													<editor-field name='Name'></editor-field>
													<editor-field name='Tag'></editor-field>
												</fieldset>
												<fieldset>
													<legend>Address</legend>
													<editor-field name='Street'></editor-field>
													<editor-field name='City'></editor-field>
													<editor-field name='State'></editor-field>
													<editor-field name='Zip'></editor-field>
													<editor-field name='Latitude'></editor-field>
													<editor-field name='Longitude'></editor-field>
												</fieldset>
												<fieldset>
													<legend>Contact</legend>
													<editor-field name='Contact_Name'></editor-field>
													<editor-field name='Contact_Phone'></editor-field>
													<editor-field name='Contact_Fax'></editor-field>
													<editor-field name='Contact_Cellular'></editor-field>
													<editor-field name='Contact_Email'></editor-field>
													<editor-field name='Contact_Website'></editor-field>
												</fieldset>
												<fieldset>
													<legend>Maintenance</legend>
													<editor-field name='Route'></editor-field>
													<editor-field name='Division'></editor-field>
													<editor-field name='Maintenance'></editor-field>
												</fieldset>
												<fieldset>
													<legend>Financials</legend>
													<editor-field name='Sales_Tax'></editor-field>
													<editor-field name='Collector'></editor-field>
												</fieldset>
												<fieldset>
													<legend>Sales</legend>
													<editor-field name='Territory'></editor-field>
												</fieldset>
											</div>
										</div>
									</div>
								</div>
								<table id='Table_Customers' class='display' cellspacing='0' width='100%'>
									<thead>
										<th title="Customer's ID">ID</th>
										<th title='Customer Name'>Name</th>
										<th title='Customer Status'>Status</th>
									</thead>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/metisMenu/metisMenu.js"></script>
    <?php require('cgi-bin/js/datatables.php');?>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="../dist/js/moment.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
    <script>
        var Editor_Customers = new $.fn.dataTable.Editor({
			ajax: "php/post/Customer.php?ID=<?php echo $_GET['ID'];?>",
			table: "#Table_Customers",
			template: '#Form_Customer',
			formOptions: {
				inline: {
					submit: "allIfChanged"
				}
			},
			idSrc: "ID",
			fields : [{
				label: "ID",
				name: "ID"
			},{
				label: "Name",
				name: "Name"
			},{
				label: "Status",
				name: "Status"
			},{
				label: "Street",
				name: "Street"
			},{
				label: "City",
				name: "City",
				type: "select",
				options: [<?php
					$r = sqlsrv_query($NEI,"
						SELECT   OwnerWithRol.City
						FROM     nei.dbo.OwnerWithRol
						WHERE    OwnerWithRol.City <> ''
						GROUP BY OwnerWithRol.City
						ORDER BY OwnerWithRol.City ASC
					;");
					$Cities = array();
					if($r){while($City = sqlsrv_fetch_array($r)){$Cities[] = '{' . "label: '{$City['City']}', value:'{$City['City']}'" . '}';}}
					echo implode(",",$Cities);
				?>]
			},{
				label: "State",
				name: "State",
				type: "select",
				options: [<?php
					$r = sqlsrv_query($NEI,"
						SELECT   OwnerWithRol.State
						FROM     nei.dbo.OwnerWithRol
						WHERE    OwnerWithRol.State <> ''
						GROUP BY OwnerWithRol.State
						ORDER BY OwnerWithRol.State ASC
					;");
					$States = array();
					if($r){while($State = sqlsrv_fetch_array($r)){$States[] = '{' . "label: '{$State['State']}', value:'{$State['State']}'" . '}';}}
					echo implode(",",$States);
				?>]
			},{
				label: "Zip",
				name: "Zip"
			},{
				label:"Name",
				name:"Contact_Name"
			},{
				label:"Phone",
				name:"Contact_Phone"
			},{
				label:"Fax",
				name:"Contact_Fax"
			},{
				label:"Email",
				name:"Contact_Email"
			},{
				label:"Website",
				name:"Contact_Website"
			}]
		});
		var Table_Customers = $('#Table_Customers').DataTable( {
			"ajax": {
				"url":"cgi-bin/php/get/Customers.php"
			},
			"processing":true,
			"serverSide":true,
			"order": [[ 1, "asc" ]],
			"columns": [
				{
				},{
				},{
					render:function(data){
						if(data == 0){return 'Active';}
						else{return 'Inactive';}
					}
				}
			],
			<?php if(!isMobile()){?>"buttons":[
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
				},
				/*{ extend: "create", editor: Editor_Customers },
				{ extend: "edit",   editor: Editor_Customers },
				{
					extend: "remove",
					editor: Editor_Customers,
					formButtons: [
						'Delete',
						{ text: 'Cancel', action: function () { this.close(); } }
					]
				},*/
				{ text:"View",
				  action:function(e,dt,node,config){
					  document.location.href = 'customer.php?ID=' + $("#Table_Customers tbody tr.selected td:first-child").html();
				  }
				}
			],<?php }?>
			"language":{
				"loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Texas</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
			},
			"paging":true,
			<?php if(!isMobile()){?>"dom":"Bfrtip",<?php }?>
			"select":true,
			"initComplete":function(){
			},
			"scrollY" : "600px",
			"scrollCollapse":true,
			"lengthChange": false
		} );
		function hrefCustomers(){hrefRows("Table_Customers","customer");}
        $("Table#Table_Customers").on("draw.dt",function(){hrefCustomers();});
    </script>
</body>
</html>
<?php
} else {
  $_GET['processing'] = 1;
  require('../beta/customers.php');
}
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=customers.php';</script></head></html><?php }?>
