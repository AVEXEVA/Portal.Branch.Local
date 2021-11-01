 <?php 
session_start();
require('../../../php/index.php');

if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
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
        	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
        	$Privileged = TRUE;}
        elseif($My_Privileges['Customer']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 ){
        	sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer.php"));
            $r = sqlsrv_query(  $NEI,"
                SELECT TicketO.ID AS ID 
                FROM nei.dbo.TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $r2 = sqlsrv_query(  $NEI,"
                SELECT TicketD.ID AS ID 
                FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
                WHERE Loc.Owner = ?;",array($_GET['ID']));
            $Privileged = (is_array(sqlsrv_fetch_array($r)) || is_array(sqlsrv_fetch_array($r2))) ? TRUE : FALSE;}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    OwnerWithRol.ID      AS Customer_ID,
                    OwnerWithRol.Name    AS Name,
                    OwnerWithRol.Address AS Street,
                    OwnerWithRol.City    AS City,
                    OwnerWithRol.State   AS State,
                    OwnerWithRol.Zip     AS Zip,
                    OwnerWithRol.Status  AS Status
            FROM    OwnerWithRol
            WHERE   OwnerWithRol.ID = '{$_GET['ID']}'");
        $Customer = sqlsrv_fetch_array($r);
        $job_result = sqlsrv_query($NEI,"
            SELECT 
                Job.ID AS ID
            FROM 
                Job 
            WHERE 
                Job.Owner = '{$_GET['ID']}'
        ;");
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
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
						<?php require(PROJECT_ROOT.'js/chart/maintenance_hours_by_customer.php');?>
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
								"ajax": "cgi-bin/php/reports/Active_Maintenance_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
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
								<div class="panel-heading"><h3><?php $Icons->Territory();?>Location Maintenance</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<table id='Table_Division_Route' class='display' cellspacing='0' width='100%'>
										<thead>
											<th>Locaton</th>
											<th>Division</th>
											<th>Mechanic</th>
										</thead>
									</table>	
								</div>
							</div>
							<script>
							var Table_Division_Route = $('#Table_Division_Route').DataTable( {
								"ajax": "cgi-bin/php/reports/Division_Route_by_Owner.php?ID=<?php echo $_GET['ID'];?>",
								"columns": [
									{ "data": "Location_Name" },
									{ "data": "Division_Name"},
									{ "data": "Maintenance_Mechanic_Name"}
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
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php $Icons->Territory();?> Required Maintenance</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<table id='Table_Required_Maintenance' class='display' cellspacing='0' width='100%'>
										<thead>
											<th>ID</th>
											<th>State</th>
											<th>Last Maintained</th>
											<th>Maintenance Mechanic</th>
										</thead>
									</table>	
								</div>
							</div>
							<script>
							var Table_Required_Maintenance = $('#Table_Required_Maintenance').DataTable( {
								"ajax": {
									"url":"cgi-bin/php/reports/Maintenances_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
									"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
								},
								"columns": [
									{ "data": "ID", "className" : "hidden" },
									{ "data": "State"},
									{ "data": "Last_Date",
									  render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
									},
									{ "data": "Route"}
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

<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>