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
            SELECT Access, Owner, Group, Other
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['Owner'] >= 4 && $My_Privileges['Unit']['Group'] >= 4 && $My_Privileges['Unit']['Other'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['Owner'] >= 4 && $My_Privileges['Unit']['Group'] >= 4){
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
<div class='tab-pane fade in' id='sales-proposals-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Territory();?>Proposal Conversion Rate</h3></div>
								<div class='panel-body white-background BankGothic shadow'>
									<div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-estimate-success-rate-by-customer"></div></div>	
								</div>
							</div>
						</div>
						<?php require(PROJECT_ROOT.'js/chart/estimate_success_by_customer.php');?>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3>Awarded Proposals</h3></div>
								<div class='panel-body white-background shadow'>
									<table id='Table_Awarded_Proposals' class='display' cellspacing='0' width='100%'>
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
							var Table_Awarded_Proposals = $('#Table_Awarded_Proposals').DataTable( {
								"ajax": "bin/php/reports/Awarded_Proposals_by_Location.php?ID=<?php echo $_GET['ID'];?>",
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
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Proposal Overview</h3></div>
								<div class="panel-body BankGothic">
									<div class='row'>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-suitcase fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$r = $database->query(null,"
																	SELECT Count(Estimate.ID) AS Proposals
																	FROM   nei.dbo.Estimate 
																		   LEFT JOIN nei.dbo.Loc ON Estimate.LocID = Loc.Loc
																	WHERE  Loc.Loc = ?
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Proposals'] : 0;?>
															</div>
															<div>Total Proposals</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-cogs fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = $database->query(null,"
																	SELECT Count(Estimate.ID) AS Proposals
																	FROM   nei.dbo.Estimate 
																		   LEFT JOIN nei.dbo.Loc ON Estimate.LocID = Loc.Loc
																	WHERE  Loc.Loc = ?
																		   AND Estimate.Status = 0
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Proposals'] : 0;?>
															</div>
															<div>Open Proposals</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-cogs fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = $database->query(null,"
																	SELECT Count(Estimate.ID) AS Proposals
																	FROM   nei.dbo.Estimate 
																		   LEFT JOIN nei.dbo.Loc ON Estimate.LocID = Loc.Loc
																	WHERE  Loc.Loc = ?
																		   AND Estimate.Status = 4
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Proposals'] : 0;?>
															</div>
															<div>Awarded Proposals</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class='col-md-12' style=''>
									<div class="panel panel-primary">
										<div class="panel-heading"><h3>Open Proposals</h3></div>
										<div class='panel-body white-background shadow'>
											<table id='Table_Open_Proposals_2' class='display' cellspacing='0' width='100%'>
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
									var Table_Open_Proposals_2 = $('#Table_Open_Proposals_2').DataTable( {
										"ajax": "bin/php/reports/Open_Proposals_by_Location.php?ID=<?php echo $_GET['ID'];?>",
										"columns": [
											{ 
												"data": "ID"
											},{ 
												"data": "Name" 
											},{ 
												"data": "Contact"
											},{ 
												"data": "Date",
												render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
											},{ 
												"data": "Price",
											 	render:function(data){return "$" + parseFloat(data).toLocaleString();}
											}
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
	</div>
</div>
<script>
$(document).ready(function(){
	$("#loading-sub-pills").removeClass("active");
	$("#sales-proposals-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>