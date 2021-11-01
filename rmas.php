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
	  		|| $My_Privileges['Job']['Group_Privilege'] < 4
	  		|| $My_Privileges['Job']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "customers.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
    <title>Nouveau Texas | Portal</title>    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
	
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><?php $Icons->Customer();?> RMAs</h3></div>
                        <div class="panel-body">
							<div id='Form_RMA'>
								<div class="panel panel-primary">
									<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>RMA Form</h2></div>
									<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
										<div style='display:block !important;'>
											<fieldset >
												<legend>Names</legend>
												<editor-field name='ID'></editor-field>
												<editor-field name='Name'></editor-field>
												<editor-field name='Date'></editor-field>
												<editor-field name='Location'></editor-field>
												<editor-field name='RMA'></editor-field>
												<editor-field name='Received'></editor-field>
												<editor-field name='Returned'></editor-field>
												<editor-field name='Tracking'></editor-field>
												<editor-field name='PO'></editor-field>
												<editor-field name='Link'></editor-field>
												<editor-field name='Status'></editor-field>
												<editor-field name='Description'></editor-field>
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
                            <table id='Table_RMAs' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th>ID</th>
                                    <th>Name</th>
									<th>Date</th>
                                    <th>Location</th>
                                    <th>RMA</th>
									<th>Received</th>
									<th>Returned</th>
									<th>Tracking</th>
									<th>PO</th>
									<th>Link</th>
									<th>Status</th>
									<th>Description</th>
                                </thead>
							</table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="../vendor/metisMenu/metisMenu.js"></script>
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="../dist/js/moment.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
    <script>
        var Editor_RMAs = new $.fn.dataTable.Editor({
			ajax: "php/post/RMA.php",
			table: "#Table_RMAs",
			template: '#Form_RMA',
			formOptions: {
				inline: {
					submit: "allIfChanged"
				}
			},
			idSrc: "ID",
			fields : [
				{ 
					label: "ID",
					name:"ID"
				},{ 
					label: "Name",
					name:"Name"
				},{ 
					label: "Date",
					name:"Date",
					type:"datetime"
				},{ 
					label: "Location",
					name:"Location",
					type:"select",
					options: [<?php
						$r = sqlsrv_query($NEI,"
							SELECT   Loc.Tag AS Location
							FROM     nei.dbo.Loc
							GROUP BY Loc.Tag
							ORDER BY Loc.Tag ASC
						;");
						$Locations = array();
						if($r){while($Location = sqlsrv_fetch_array($r)){
							$Location['Location'] = str_replace("'","",$Location['Location']);
							$Locations[] = '{' . "label: '{$Location['Location']}', value:'{$Location['Location']}'" . '}';
						}}
						echo implode(",",$Locations);
					?>]
				},{ 
					label: "RMA",
					name:"RMA"
				},{ 
					label: "Received",
					name:"Received",
					type:"datetime"
				},{ 
					label: "Returned",
					name:"Returned",
					type:"datetime"
				},{ 
					label: "Tracking",
					name:"Tracking"
				},{ 
					label: "PO",
					name:"PO"
				},{ 
					label: "Link",
					name:"Link"
				},{ 
					label: "Status",
					name:"Status",
					type:"radio",
					options: [
						{label: "Complete", value:'Complete'},
						{label: "Open", value:'Open'}
					]
				},{ 
					label: "Description",
					name:"Description",
					type:"textarea"
				}
			]
		});
		Editor_RMAs.field('ID').disable();
		Editor_RMAs.field('ID').hide();
		$('#Table_RMAs').on( 'click', 'tbody td:not(:first-child)', function (e) {
			Editor_RMAs.inline( this );
		} );
		var Table_RMAs = $('#Table_RMAs').DataTable( {
			"ajax": {
				"url":"cgi-bin/php/get/RMAs.php",
				"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
			},
			"columns": [
				{ 
					"data": "ID" ,
					"visible":false
				},{ 
					"data": "Name"
				},{ 	
					"data": "Date",
					render: function(data){if(data != '1900-01-01 00:00:00.000'){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}else{return null;}}
				},{
					"data": "Location"
				},{ 
					"data": "RMA"
				},{ 
					"data": "Received",
					render: function(data){if(data != '1900-01-01 00:00:00.000'){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}else{return null;}}
				},{
					"data":"Returned",
					render: function(data){if(data != '1900-01-01 00:00:00.000'){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}else{return null;}}
				},{
					"data":"Tracking"
				},{
					"data":"PO"
				},{
					"data":"Link"
				},{
					"data":"Status"
				},{
					"data":"Description"
				}
			],
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
				{ extend: "create", editor: Editor_RMAs },
				{ extend: "edit",   editor: Editor_RMAs },
				{ 

					extend: "remove", 
					editor: Editor_RMAs, 
					formButtons: [
						'Delete',
						{ text: 'Cancel', action: function () { this.close(); } }
					]
				},
				{ text:"View",
				  action:function(e,dt,node,config){
					  document.location.href = $("#Table_RMAs tbody tr.selected td:nth-child(9)").html();
				  }
				}
			],
			<?php require('cgi-bin/js/datatableOptions.php');?>
		} );
		
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=customers.php';</script></head></html><?php }?>