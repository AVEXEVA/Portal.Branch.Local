<?php
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
            $r = $database->query(null,"
				SELECT * 
				FROM   TicketO 
					   LEFT JOIN nei.dbo.Loc  ON TicketO.LID   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc       = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketO.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r2 = $database->query(null,"
				SELECT * 
				FROM   TicketD 
					   LEFT JOIN nei.dbo.Loc  ON TicketD.Loc   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc       = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketD.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r3 = $database->query(null,"
				SELECT * 
				FROM   TicketDArchive
					   LEFT JOIN nei.dbo.Loc  ON TicketDArchive.Loc   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc              = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketDArchive.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
            $r3 = sqlsrv_fetch_array($r2);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
        $SQL_Result = $database->query(null,"
            SELECT Elev.Owner 
            FROM   nei.dbo.Elev 
            WHERE  Elev.ID        = ? 
			       AND Elev.Owner = ?
        ;",array($_GET['ID'],$_SESSION['Branch_ID']));
        if($SQL_Result){
            $sql = sqlsrv_fetch_array($SQL_Result);
            if($sql){$Privileged = true;}
        }
    }
    if(!isset($array['ID'])  || !$Privileged || !(is_numeric($_GET['ID']) || is_numeric($_POST['ID']))){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
            "SELECT TOP 1
                Elev.ID,
                Elev.Unit           AS Unit,
                Elev.State          AS State,
                Elev.Cat            AS Category,
                Elev.Type           AS Type,
                Elev.Building       AS Building,
                Elev.Since          AS Since,
                Elev.Last           AS Last,
                Elev.Price          AS Price,
                Elev.fDesc          AS Description,
                Loc.Loc             AS Location_ID,
                Loc.ID              AS Name,
                Loc.Tag             AS Tag,
                Loc.Tag             AS Location_Tag,
                Loc.Address         AS Street,
                Loc.City            AS City,
                Loc.State           AS Location_State,
                Loc.Zip             AS Zip,
                Loc.Route           AS Route,
                Zone.Name           AS Zone,
                OwnerWithRol.Name   AS Customer_Name,
                OwnerWithRol.ID     AS Customer_ID,
                Emp.ID AS Route_Mechanic_ID,
                Emp.fFirst AS Route_Mechanic_First_Name,
                Emp.Last AS Route_Mechanic_Last_Name
            FROM 
                Elev
                LEFT JOIN nei.dbo.Loc           ON Elev.Loc = Loc.Loc
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN nei.dbo.Route ON Loc.Route = Route.ID
                LEFT JOIN nei.dbo.Emp ON Route.Mech = Emp.fWork
            WHERE
                Elev.ID = ?
		;",array($_GET['ID']));
        $Unit = sqlsrv_fetch_array($r);
        $data = $Unit;
        $r2 = $database->query(null,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
?>
<div class='tab-pane fade in' id='operations-maintenance-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
				<div class='col-md-6' style=''>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Territory();?>Total Maintenance Hours</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-maintenance-hours-by-customer"></div></div>
								</div>
							</div>
						</div>
						<?php require(PROJECT_ROOT.'js/chart/maintenance_hours_by_location.php');?>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Maintenance();?>Active Maintenance Jobs</h3></div>
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
								<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Territory();?> Required Maintenance</h3></div>
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