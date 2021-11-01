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
        if(isset($My_Privileges['Admin']) && $My_Privileges['Admin']['User_Privilege'] >= 4 && $My_Privileges['Admin']['Group_Privilege'] >= 4 && $My_Privileges['Admin']['Other_Privilege'] >= 4){
        	$Privileged = TRUE;
		}
    }
    //
    if(!isset($array['ID']) || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=admin.php";</script></head></html><?php }
    else {
		sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "admin.php"));
?><div class="tab-pane fade in active" id="overview-pills">
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
        <div class='col-md-6'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Logins</h3></div>
						<div class="panel-body BankGothic white-background shadow">
							<div id="flot-activity" style="width:100%;height:500px;display:inline-block;"></div>
							<?php require('../../../js/chart/activity.php');?>
						</div>
					</div>
				</div>
        <div class='col-md-6'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Actions</h3></div>
						<div class="panel-body BankGothic white-background shadow">
							<div id="flot-actions" style="width:100%;height:500px;display:inline-block;"></div>
							<?php require('../../../js/chart/actions.php');?>
						</div>
					</div>
				</div>
				<div class='col-md-12'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Activity</h3></div>
						<div class="panel-body BankGothic white-background shadow">
							<table id='Table_Activity' class='display' cellspacing='0' width='100%'>
								<thead>
									<th>First Name</th>
									<th>Last Name</th>
									<th>Page</th>
									<th>Timestamp</th>
								</thead>
							</table>
							<script>
							var Table_Activity = $('#Table_Activity').DataTable( {
								"ajax": {
									"url":"cgi-bin/php/reports/Recent_Activity.php",
									"dataSrc":function(json){
										if(!json.data){json.data = [];}
										return json.data;
									}
								},
								"columns": [
									{
										"data" : "First_Name"
									},{
										"data" : "Last_Name"
									},{
										"data" : "Date"
									},{
										"data" : "Page"
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
									}
								],
								<?php require('../../../js/datatableOptions.php');?>,
								"scrollY" : "500px",
								"scrollCollapse":true
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
} else {?><html><head><script>document.location.href="../login.php?Forward=admin.php";";</script></head></html><?php }?>
