<?php 
session_start();
require('../../../php/index.php');
require('../../../php/class/Customer.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	$My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Job']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = sqlsrv_query(  $NEI,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
		}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
			"SELECT TOP 1
					Loc.Loc              AS Location_ID,
					Loc.ID               AS Name,
					Loc.Tag              AS Tag,
					Loc.Address          AS Street,
					Loc.City             AS City,
					Loc.State            AS State,
					Loc.Zip              AS Zip,
					Loc.Balance          as Location_Balance,
					Zone.Name            AS Zone,
					Loc.Route            AS Route_ID,
					Emp.ID               AS Route_Mechanic_ID,
					Emp.fFirst           AS Route_Mechanic_First_Name,
					Emp.Last             AS Route_Mechanic_Last_Name,
					Loc.Owner            AS Customer_ID,
					OwnerWithRol.Name    AS Customer_Name,
					OwnerWithRol.Balance AS Customer_Balance,
					Terr.Name            AS Territory_Domain/*,
					Sum(SELECT Location.ID FROM Loc AS Location WHERE Location.Owner='Loc.Owner') AS Customer_Locations*/
			FROM    Loc
					LEFT JOIN nei.dbo.Zone         ON Loc.Zone   = Zone.ID
					LEFT JOIN nei.dbo.Route        ON Loc.Route  = Route.ID
					LEFT JOIN nei.dbo.Emp          ON Route.Mech = Emp.fWork
					LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
					LEFT JOIN Terr         		   ON Terr.ID    = Loc.Terr
			WHERE
					Loc.Loc = ?
		;",array($_GET['ID']));
		$Location = sqlsrv_fetch_array($r);?>
<div class='tab-pane fade in' id='operations-maintenance-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
				<div class='col-md-6' style=''>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php $Icons->Territory();?>Total Maintenance Hours</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-maintenance-hours-by-customer"></div></div>
								</div>
							</div>
						</div>
						<?php require(PROJECT_ROOT.'js/chart/maintenance_hours_by_location.php');?>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php $Icons->Maintenance();?>Active Maintenance Jobs</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<table id='Table_Active_Maintenance' class='display' cellspacing='0' width='100%'>
										<thead>
											<th>ID</th>
											<th>Name</th>
											<th>Date</th>
										</thead>
									</table>
								</div>
							</div>
							<script>
							var Table_Active_Maintenance = $('#Table_Active_Maintenance').DataTable( {
								"ajax": "cgi-bin/php/reports/Active_Maintenance_by_Location.php?ID=<?php echo $_GET['ID'];?>",
								"columns": [
									{ "data": "ID"},
									{ "data": "Name" },
									{ "data": "Date",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
								],
								"order": [[1, 'asc']],
								"language":{
									"loadingRecords":""
								},
								"initComplete":function(){
									$("#loading-pills").removeClass("active");
									$("#operations-pills").addClass('active');
								}
							} );
							</script>
						</div>
					</div>
				</div>
				<div class='col-md-6' style=''>
					<div class='row'>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php $Icons->Territory();?> Required Maintenance</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<table id='Table_Required_Maintenance' class='display' cellspacing='0' width='100%'>
										<thead>
											<th>ID</th>
											<th>State</th>
											<th>Last Maintained</th>
										</thead>
									</table>
									<script>
									var Table_Required_Maintenance = $('#Table_Required_Maintenance').DataTable( {
										"ajax": {
											"url":"cgi-bin/php/reports/Maintenances_by_Location.php?ID=<?php echo $_GET['ID'];?>",
											"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
										},
										"columns": [
											{ "data": "ID", "className" : "hidden" },
											{ "data": "State"},
											{ "data": "Last_Date",
											  render: function(data){if(data == ''){return 'Never';}else{return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
											}
										],
										"order": [[1, 'asc']],
										"language":{"loadingRecords":""},
										//"paging":false,
										"searching":false,
										"info":false,
										"lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
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
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#operations-maintenance-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>