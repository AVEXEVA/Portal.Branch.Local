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
<div class='tab-pane fade in active' id='sales-overview-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
				<div class='col-md-6' style=''>
					<div class='row'>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php $Icons->Territory();?> Sales Information</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<div class='row'><div class='col-xs-12' style='font-size:24px;'><?php 
										$r = sqlsrv_query($NEI,"
											SELECT TOP 1 Terr.Name AS Territory_Domain
											FROM   nei.dbo.Loc
												   LEFT JOIN Terr ON Terr.ID = Loc.Terr
											WHERE  Loc.Owner = ?
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
								"ajax": "cgi-bin/php/reports/Active_Jobs_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
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
								},
								"scrollY" : "400px",
								"scrollCollapse":true,
								"paging":false
							} );
							</script>
						</div>
					</div>
				</div>
				<div class='col-md-6' style=''>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php $Icons->Territory();?>Active Contracts</h3></div>
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
								"ajax": "cgi-bin/php/get/Contracts_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
								"columns": [
									{ 
										"data": "Location"
									},{ 
										"data": "Contract_Amount",
										render:function(data){return "$" + parseFloat(data).toLocaleString();}
									},{ 
										"data": "Contract_Start",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
									},{ 
										
										"data": "Contract_Billing_Cycle",
										render:function(data){
											switch(data){
												case 0:return 'Monthly';
												case 1:return 'Bi-Monthly';
												case 2:return 'Quarterly';
												case 3:return 'Trimester';
												case 4:return 'Semi-Annualy';
												case 5:return 'Annually';
												case 6:return 'Never';}
										}
									},{ 
										"data": "Contract_Length"
									}
								],
								"order": [[1, 'asc']],
								"language":{
									"loadingRecords":""
								},
								"initComplete":function(){},
								"scrollY" : "250px",
								"scrollCollapse":true,
								"paging":false
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
								"ajax": "cgi-bin/php/get/Open_Proposals_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
								"columns": [
									{ "data": "ID"},
									{ "data": "Name" },
									{ "data": "Contact"},
									{ "data": "Date",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
									{ "data": "Price",render:function(data){return "$" + parseFloat(data).toLocaleString();}}
								],
								"order": [[1, 'asc']],
								"language":{
									"loadingRecords":""
								},
								"initComplete":function(){

								},
								"scrollY" : "250px",
								"scrollCollapse":true,
								"paging":false
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