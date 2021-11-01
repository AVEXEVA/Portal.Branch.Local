 <?php 
session_start();
require('../../../../cgi-bin/php/index.php');

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
        if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = sqlsrv_query(  $NEI,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.LID='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Loc='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Loc='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
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
<div class='tab-pane fade in' id='sales-proposals-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php $Icons->Territory();?>Proposal Conversion Rate</h3></div>
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
								"ajax": "cgi-bin/php/reports/Awarded_Proposals_by_Location.php?ID=<?php echo $_GET['ID'];?>",
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
																$r = sqlsrv_query($NEI,"
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
																$r = sqlsrv_query($NEI,"
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
																$r = sqlsrv_query($NEI,"
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
										"ajax": "cgi-bin/php/get/Open_Proposals_by_Location.php?ID=<?php echo $_GET['ID'];?>",
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