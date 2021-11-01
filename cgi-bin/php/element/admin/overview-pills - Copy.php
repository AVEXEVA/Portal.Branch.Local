<?php 
session_start();
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Customer']['Group_Privilege'] >= 4 && $My_Privileges['Customer']['Other_Privilege'] >= 4){
        	$Privileged = TRUE;
		}
    }
    //
    if(!isset($array['ID']) || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
		sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "collector.php"));
?><div class="tab-pane fade in active" id="overview-pills">
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
				<div class='col-md-6'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Open AR Age</h3></div>
						<div class="panel-body BankGothic white-background shadow">
							<div id="collector_open_ar" style="width:100%;height:500px;display:inline-block;"></div>    
							<?php require('../../../js/bar/collector_open_ar.php');?>
						</div>
					</div>
				</div>
				<div class='col-md-6'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Overdue Invoices</h3></div>
						<div class="panel-body BankGothic white-background shadow">
							<table id='Table_Overdue_Collections' class='display' cellspacing='0' width='100%'>
								<thead>
									<th></th>
									<th>Invoice #</th>
									<th>Customer</th>
									<th>Date</th>
									<th>Due</th>
									<th>Balance</th>
								</thead>
							   <tfoot>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tfoot>
							</table>
							<script>
							var Table_Overdue_Collections = $('#Table_Overdue_Collections').DataTable( {
								"ajax": {
									"url":"php/get/Overdue_Collections.php",
									"dataSrc":function(json){
										if(!json.data){json.data = [];}
										return json.data;
									}
								},
								"columns": [
									{
										"className":      'details-control',
										"orderable":      false,
										"data":           null,
										"defaultContent": ''
									},
									{ "data" : "Invoice" },
									{ "data" : "Customer"},
									{ "data" : "Dated",
										render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
									{ "data" : "Due",
										render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
									{ "data" : "Balance",className:"sum"}
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
									},{ 
										text : "Preview",
										action:function(e,dt,node,config){
											$("tr.selected").each(function(){
												var tr = $(this);
												var row = Table_Collections.row( tr );

												if ( row.child.isShown() ) {
													row.child.hide();
													tr.removeClass('shown');
												}
												else {
													row.child( formatCollection(row.data()) ).show();
													tr.addClass('shown');
												}
											});
										}
									}
								],
								"language":{
									"loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
								},
								"dom":"Bfrtip",
								"select":true,
								"initComplete":function(){
								}
							} );
							</script>
						</div>
					</div>
				</div>
				<div class='col-md-6'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> New Invoices</h3></div>
						<div class="panel-body BankGothic white-background shadow">
							<table id='Table_New_Collections' class='display' cellspacing='0' width='100%'>
								<thead>
									<th></th>
									<th>Invoice #</th>
									<th>Customer</th>
									<th>Date</th>
									<th>Due</th>
									<th>Balance</th>
								</thead>
							   <tfoot>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
									<th></th>
								</tfoot>
							</table>
							<script>
							var Table_New_Collections = $('#Table_New_Collections').DataTable( {
								"ajax": {
									"url":"php/get/New_Collections.php",
									"dataSrc":function(json){
										if(!json.data){json.data = [];}
										return json.data;
									}
								},
								"columns": [
									{
										"className":      'details-control',
										"orderable":      false,
										"data":           null,
										"defaultContent": ''
									},
									{ "data" : "Invoice" },
									{ "data" : "Customer"},
									{ "data" : "Dated",
										render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
									{ "data" : "Due",
										render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
									{ "data" : "Balance",className:"sum"}
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
									},{ 
										text : "Preview",
										action:function(e,dt,node,config){
											$("tr.selected").each(function(){
												var tr = $(this);
												var row = Table_Collections.row( tr );

												if ( row.child.isShown() ) {
													row.child.hide();
													tr.removeClass('shown');
												}
												else {
													row.child( formatCollection(row.data()) ).show();
													tr.addClass('shown');
												}
											});
										}
									}
								],
								"language":{
									"loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
								},
								"dom":"Bfrtip",
								"select":true,
								"initComplete":function(){
								}
							} );
							</script>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
<script>
$(document).ready(function(){
	$("#loading-pills").removeClass("active");
	$("#overview-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=collector.php";?>";</script></head></html><?php }?>