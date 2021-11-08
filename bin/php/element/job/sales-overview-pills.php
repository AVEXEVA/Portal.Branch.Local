<?php 
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
require('../../../php/class/Customer.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	$My_User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Job']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = $database->query(  null,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = $database->query( null,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = $database->query( null,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
		}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
            "SELECT TOP 1
                Job.ID                AS Job_ID,
                Job.fDesc             AS Job_Name,
                Job.fDate             AS Job_Start_Date,
                Job.BHour             AS Job_Budgeted_Hours,
                JobType.Type          AS Job_Type,
				Job.Remarks 		  AS Job_Remarks,
                Loc.Loc               AS Location_ID,
                Loc.ID                AS Location_Name,
                Loc.Tag               AS Location_Tag,
                Loc.Address           AS Location_Street,
                Loc.City              AS Location_City,
                Loc.State             AS Location_State,
                Loc.Zip               AS Location_Zip,
                Loc.Route             AS Route,
                Zone.Name             AS Division,
                OwnerWithRol.ID       AS Customer_ID,
                OwnerWithRol.Name     AS Customer_Name,
                OwnerWithRol.Status   AS Customer_Status,
                OwnerWithRol.Elevs    AS Customer_Elevators,
                OwnerWithRol.Address  AS Customer_Street,
                OwnerWithRol.City     AS Customer_City,
                OwnerWithRol.State    AS Customer_State,
                OwnerWithRol.Zip      AS Customer_Zip,
                OwnerWithRol.Contact  AS Customer_Contact,
                OwnerWithRol.Remarks  AS Customer_Remarks,
                OwnerWithRol.Email    AS Customer_Email,
                OwnerWithRol.Cellular AS Customer_Cellular,
                Elev.ID               AS Unit_ID,
                Elev.Unit             AS Unit_Label,
                Elev.State            AS Unit_State,
                Elev.Cat              AS Unit_Category,
                Elev.Type             AS Unit_Type,
                Emp.fFirst            AS Mechanic_First_Name,
                Emp.Last              AS Mechanic_Last_Name,
                Route.ID              AS Route_ID,
				Violation.ID          AS Violation_ID,
				Violation.fdate       AS Violation_Date,
				Violation.Status      AS Violation_Status,
				Violation.Remarks     AS Violation_Remarks
            FROM 
                Job 
                LEFT JOIN nei.dbo.Loc           ON Job.Loc      = Loc.Loc
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone     = Zone.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type     = JobType.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Job.Owner    = OwnerWithRol.ID
                LEFT JOIN nei.dbo.Elev          ON Job.Elev     = Elev.ID
                LEFT JOIN nei.dbo.Route         ON Loc.Route    = Route.ID
                LEFT JOIN nei.dbo.Emp           ON Emp.fWork    = Route.Mech
				LEFT JOIN nei.dbo.Violation     ON Job.ID       = Violation.Job
            WHERE
                Job.ID = ?
        ;",array($_GET['ID']));
        $Job = sqlsrv_fetch_array($r);?>
<div class='tab-pane fade in active' id='sales-overview-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
				<div class='col-md-6' style=''>
					<div class='row'>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Territory();?> Sales Information</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<div class='row'><div class='col-xs-12' style='font-size:24px;'><?php 
										$r = $database->query(null,"
											SELECT TOP 1 Terr.Name AS Territory_Domain
											FROM   nei.dbo.Job
											       LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc
												   LEFT JOIN Terr ON Terr.ID = Loc.Terr
											WHERE  Job.ID = ?
										;",array($_GET['ID']));
										echo $r ? sqlsrv_fetch_array($r)['Territory_Domain'] : "Unknown";
									?>'s Territory</div></div>
								</div>
							</div>
						</div>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3>Active Jobs</h3></div>
								<div class='panel-body white-background shadow'>
									<table id='Table_Active_Jobs_3' class='display' cellspacing='0' width='100%'>
										<thead>
											<th>ID</th>
											<th>Name</th>
											<th>Type</th>
										</thead>
									</table>	
								</div>
							</div>
							<script>
							var Table_Active_Jobs_3 = $('#Table_Active_Jobs_3').DataTable( {
								"ajax": "bin/php/reports/Active_Jobs_by_Location.php?ID=<?php echo $_GET['ID'];?>",
								"columns": [
									{ "data": "ID"},
									{ "data": "Name" },
									{ "data": "Type"}
								],
								"order": [[1, 'asc']],
								"language":{
									"loadingRecords":""
								},
								"initComplete":function(){
								}
							} );
							</script>
						</div>
					</div>
				</div>
				<div class='col-md-6' style=''>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Territory();?>Active Contracts</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<table id='Table_Sales_Contracts' class='display' cellspacing='0' width='100%'>
										<thead><tr>
											<th title=''>Location</th>
											<th title=''>Amount</th>
											<th title=''>Start</th>
											<th title=''>Cycle</th>
											<th title=''>Months</th>
										</tr></thead>
									</table>
								</div>
							</div>
							<script>

							var Table_Sales_Contracts = $('#Table_Sales_Contracts').DataTable( {
								"ajax": "bin/php/get/Contracts_by_Location.php?ID=<?php echo $_GET['ID'];?>",
								"columns": [
									{ "data": "Location"},
									{ "data": "Contract_Amount"},
									{ "data": "Contract_Start",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
									{ 
										"data": "Contract_Billing_Cycle",
										render:function(data){
											switch(data){
												case 0:return 'Monthly';
												case 1:return 'Bi-Monthly';
												case 2:return 'Quarterly';
												case 3:return 'Trimester';
												case 4:return 'Semi-Annualy';
												case 5:return 'Annually';
												case 6:return 'Never';}}
									},
									{ "data": "Contract_Length"}
								],
								"order": [[1, 'asc']],
								"language":{
									"loadingRecords":""
								},
								"initComplete":function(){}
							} );
							</script>
						</div>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3>Open Proposals</h3></div>
								<div class='panel-body white-background shadow'>
									<table id='Table_Open_Proposals' class='display' cellspacing='0' width='100%'>
										<thead>
											<th>ID</th>
											<th>Name</th>
											<th>Contact</th>
											<th>Date</th>
											<th>Price</th>
										</thead>
									</table>	
								</div>
							</div>
							<script>
							var Table_Open_Proposals = $('#Table_Open_Proposals').DataTable( {
								"ajax": "bin/php/reports/Open_Proposals_by_Location.php?ID=<?php echo $_GET['ID'];?>",
								"columns": [
									{ "data": "ID"},
									{ "data": "Name" },
									{ "data": "Contact"},
									{ "data": "Date",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
									{ "data": "Price",render:function(data){return data.toLocaleString();}}
								],
								"order": [[1, 'asc']],
								"language":{
									"loadingRecords":""
								},
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
	$("#loading-sub-pills").removeClass("active");
	$("#sales-overview-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>