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
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "leads.php"));
if(isMobile()){?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
    <title>Nouveau Texas | Portal</title>    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>

</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h3><?php $Icons->Customer();?> Leads</h3></div>
				<div class="panel-body">
					<div id='Form_Lead'>
						<div class="panel panel-primary">
							<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>Location Form</h2></div>
							<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
								<div style='display:block !important;'>
									<fieldset >
										<legend>Names</legend>
										<editor-field name='ID'></editor-field>
										<editor-field name='Name'></editor-field>
										<editor-field name='Address'></editor-field>
										<editor-field name='City'></editor-field>
										<editor-field name='State'></editor-field>
										<editor-field name='Zip'></editor-field>
										<editor-field name='Customer'></editor-field>
									</fieldset>
								</div>
							</div>
						</div>
					</div>
					<table id='Table_Leads' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
						<thead>
							<th title="ID"></th>
							<th title='Name'></th>
							<th title="Address"></th>
							<th title="City"></th>
							<th title="State"></th>
							<th title="Zip"></th>
							<th title="Owner"></th>
						</thead>
					</table>
				</div>
			</div>
        </div>
    </div>
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>
    <?php require('cgi-bin/js/datatables.php');?>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="../dist/js/moment.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <script>
        var Editor_Leads = new $.fn.dataTable.Editor({
			ajax: "php/post/Lead.php?ID=<?php echo $_GET['ID'];?>",
			table: "#Table_Leads",
			template: '#Form_Lead',
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
				label: "Type",
				name: "Type"
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
				label:"Customer",
				name:"Customer",
				type:"select",
				options:[<?php
					$r = sqlsrv_query($NEI,"
						SELECT   OwnerWithRol.Name
						FROM     nei.dbo.OwnerWithRol
						WHERE    OwnerWithRol.Name <> ''
						ORDER BY OwnerWithRol.Name ASC
					;",array("DON'T USE THIS CODE"));
					$Customers = array();
					if($r){while($Customer = sqlsrv_fetch_array($r)){
						$Customer['Name'] = str_replace("'","",$Customer['Name']);
						$Customers[] = '{' . "label: '{$Customer['Name']}', value:'{$Customer['Name']}'" . '}';}}
					echo implode(",",$Customers);
				?>]
			}]
		});
		var Table_Leads = $('#Table_Leads').DataTable( {
			"ajax": {
				"url":"cgi-bin/php/get/Leads.php",
				"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
			},
			"columns": [
				{
					"data": "ID",
					"className":"hidden"
				},{
					"data": "Name"
				},{
					"data": "Street"
					<?php if(isMobile()){?>,"visible":false<?php }?>
				},{
					"data": "City"
					<?php if(isMobile()){?>,"visible":false<?php }?>
				},{
					"data": "State"
					<?php if(isMobile()){?>,"visible":false<?php }?>
				},{
					"data": "Zip"
					<?php if(isMobile()){?>,"visible":false<?php }?>
				},{
					"data": "Customer"
				}
			],
			<?php if(!isMobile()) { ?>
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
				},
				{ extend: "create", editor: Editor_Leads },
				{ extend: "edit",   editor: Editor_Leads },
				{
					extend: "remove",
					editor: Editor_Leads,
					formButtons: [
						'Delete',
						{ text: 'Cancel', action: function () { this.close(); } }
					]
				},
				/*{ text:"View",
				  action:function(e,dt,node,config){
					  document.location.href = 'lead.php?ID=' + $("#Table_Leads tbody tr.selected td:first-child").html();
				  }
				}*/
			], <?php } ?>
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
		/*yadcf.init(Table_Leads,[{
			column_number:0,
			filter_type:"auto_complete",
			filter_default_label:"ID"
		},{
			column_number:1,
			filter_type:"auto_complete",
			filter_default_label:"Name"
		},{
			column_number:2,
			filter_default_label:"Street",
			filter_type:"auto_complete"
		},{
			column_number:3,
			filter_default_label:"City"
		},{
			column_number:4,
			filter_default_label:"State"
		},{
			column_number:5,
			filter_default_label:"Zip"
		},{
			column_number:6,
			filter_default_label:"Customer"
		}]);*/
		function hrefLeads(){hrefRow("Table_Leads","lead");}
	$("Table#Table_Leads").on("draw.dt",function(){hrefLeads();});
    </script>
</body>
</html>
<?php
} else {
  $_GET['processing'] = 1;
  require('../beta/leads.php');
}
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=customers.php';</script></head></html><?php }?>
