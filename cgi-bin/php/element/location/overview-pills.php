 <?php 
session_start();
require('../../../../cgi-bin/php/index.php');
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
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){?><?php require('../../../401.html');?><?php }
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
					Rol.Phone            AS Route_Mechanic_Phone_Number,
					Portal.Email         AS Route_Mechanic_Email,
                    Loc.Owner            AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Balance AS Customer_Balance,
					OwnerWithROl.Address AS Customer_Street,
					OwnerWithRol.City    AS Customer_City,
					OwnerWithRol.State   AS Customer_State,
					OwnerWithRol.Zip     AS Customer_Zip,
					OwnerWithRol.Contact AS Customer_Contact,
                    Terr.Name            AS Territory_Domain/*,
                    Sum(SELECT Location.ID FROM Loc AS Location WHERE Location.Owner='Loc.Owner') AS Customer_Locations*/
            FROM    Loc
                    LEFT JOIN nei.dbo.Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN nei.dbo.Route        ON Loc.Route  = Route.ID
                    LEFT JOIN nei.dbo.Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN nei.dbo.Terr 		   ON Terr.ID    = Loc.Terr
					LEFT JOIN nei.dbo.Rol          ON Emp.Rol    = Rol.ID 
					LEFT JOIN Portal.dbo.Portal    ON Emp.ID     = Portal.Branch_ID AND Portal.Branch = 'Nouveau Elevator'
            WHERE
                    Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);?>
<div class="tab-pane fade in active" id="overview-pills">
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<!--<div class="panel-heading"><h4><?php $Icons->Dashboard();?> <?php echo $Location['Tag'];  ?> Dashboard</h4></div>-->
		<div class="panel-body">
			<div class="row">
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-6'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h4><?php $Icons->Customer();?> Customer</h4></div>
								<div class='panel-body white-background'>
									<div style='font-size:20px;text-decoration:underline;'><b>
										<div class='row' style='padding:5px;'>
											<div class='col-xs-12'><?php if($My_Privileges['Customer']['Other_Privilege'] >= 4){?><a href="customer.php?ID=<?php echo $Location['Customer_ID'];?>"><?php }?><?php echo $Location['Customer_Name'];?><?php if($My_Privileges['Customer']['Other_Privilege'] >= 4){?></a><?php }?>
											</div>
											<div class='col-xs-12'><?php echo $Location["Street"];?></div>
											<div class='col-xs-12'><?php echo $Location["City"];?> <?php echo $Location["State"];?> <?php echo $Location["Zip"];?></div>
										</div>
									</b></div>
								</div>
							</div>
						</div>
						<div class='col-md-6'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h4><?php $Icons->Customer();?> Billing</h4></div>
								<div class='panel-body white-background'>
									<div style='font-size:20px;text-decoration:underline;'><b>
										<div class='row' style='padding:5px;'>
											<div class='col-xs-12'><?php if($My_Privileges['Customer']['Other_Privilege'] >= 4){?><a href="customer.php?ID=<?php echo $Location['Customer_ID'];?>"><?php }?><?php echo $Location['Customer_Contact'];?><?php if($My_Privileges['Customer']['Other_Privilege'] >= 4){?></a><?php }?>
											</div>
											<div class='col-xs-12'><?php echo $Location["Customer_Street"];?></div>
											<div class='col-xs-12'><?php echo $Location["Customer_City"];?> <?php echo $Location["Customer_State"];?> <?php echo $Location["Customer_Zip"];?></div>
										</div>
									</b></div>
								</div>
							</div>
						</div>
							<div class='col-md-6'>
								<div class="panel panel-primary">
									<div class="panel-heading"><h4><?php $Icons->Maintenance();?> Maintenance</h4></div>
									<div class='panel-body white-background'>
										<div class='row' style='font-size:20px;padding:5px;'>
											<div class='col-xs-12'><?php $Icons->Route();?> <?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Location['Route_Mechanic_ID']){?><a href="route.php?ID=<?php echo $Location['Route_ID'];?>"><?php }?><?php echo proper($Location["Route_Mechanic_First_Name"] . " " . $Location["Route_Mechanic_Last_Name"]);?><?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Location['Route_Mechanic_ID']){?></a><?php }?>
											</div>
											<div class='col-xs-12'><?php $Icons->Phone();?> <?php echo $Location['Route_Mechanic_Phone_Number'];?></div>
											<?php if(strlen($Location['Route_Mechanic_Email']) > 0){?><div class='col-xs-12'><?php $Icons->Email();?> <?php echo $Location['Route_Mechanic_Email'];?></div><?php }?>
											<div class='col-xs-12'><?php $Icons->Division();?> <?php if($My_Privileges['Ticket']['Other_Privilege'] >= 4){?><a href="dispatch.php?Supervisors=Division%201&Mechanics=undefined&Start_Date=07/13/2017&End_Date=07/13/2017"><?php }?><?php echo proper($Location["Zone"]);?><?php if($My_Privileges['Ticket']['Other_Privilege'] >= 4){?></a><?php }?></div>
										</div>
									</div>
								</div>
							</div>
							<div class='col-md-6'>
								<div class="panel panel-primary">
									<div class="panel-heading"><h4><?php $Icons->Territory();?> Sales Information</h4></div>
									<div class='panel-body white-background'>
										<div class='row' style='font-size:20px;padding:5px;'>
											<div class='col-xs-12'><?php echo $Location['Territory_Domain'];?>'s Territory</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class='col-md-6'>
						<div class="panel panel-primary">
							<div class="panel-heading"><h4><?php $Icons->Info();?> Records</h4></div>
							<div class='panel-body'>
								<div class="col-lg-4 col-md-4" onClick='clickTab("tables-pills","tables-units-pills");'>
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3">
													<i class="fa fa-cogs fa-5x"></i>
												</div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
													<div class="medium"><?php 
														$r = sqlsrv_query($NEI,"SELECT Count(ID) AS Count_of_Elevators FROM Elev WHERE Loc='{$_GET['ID']}';");
														echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Elevators']) : 0;
													?></div>
													<div>Units</div></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4" onClick='clickTab("tables-pills","tables-jobs-pills");'>
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3">
													<i class="fa fa-suitcase fa-5x"></i>
												</div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
													<div class="medium"><?php 
														$r = sqlsrv_query($NEI,"SELECT Count(ID) AS Count_of_Jobs FROM Job WHERE Loc='{$_GET['ID']}';");
														echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;?>
													</div>
													<div>Jobs</div></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4" onClick='clickTab("tables-pills","tables-violations-pills");'>
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3">
													<i class="fa fa-suitcase fa-5x"></i>
												</div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
													<div class="medium"><?php 
														$r = sqlsrv_query($NEI,"SELECT Count(ID) AS Count_of_Jobs FROM Violation WHERE Loc='{$_GET['ID']}';");
														echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;?>
													</div>
													<div>Violations</div></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4" onClick='clickTab("tables-pills","tables-tickets-pills");'>
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3">
													<i class="fa fa-ticket fa-5x"></i>
												</div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
													<div class="medium"><?php 
														$r = sqlsrv_query($NEI,"
															SELECT Count(Tickets.ID) AS Count_of_Tickets 
															FROM   (
																		(SELECT ID FROM TicketO WHERE TicketO.LID = ?)
																		UNION ALL 
																		(SELECT ID FROM TicketD WHERE TicketD.Loc = ?)
																		UNION ALL
																		(SELECT ID FROM TicketDArchive WHERE TicketDArchive.Loc = ?)
																	) AS Tickets
														;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
														echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;?>
													</div>
													<div>Tickets</div></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<?php if(isset($My_Privileges['Location']) && $My_Privileges['Location']['Other_Privilege'] >= 4){?><div class="col-lg-4 col-md-4" onClick='clickTab("tables-pills","tables-proposals-pills");'>
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3">
													<i class="fa fa-folder-open fa-5x"></i>
												</div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
													<div class="medium"><?php 
														$r = sqlsrv_query($NEI,"
															SELECT Count(Estimate.ID) AS Count_of_Tickets 
															FROM   nei.dbo.Estimate
															WHERE  Estimate.LocID = ?
														;",array($_GET['ID']));
														echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;?>
													</div>
													<div>Proposals</div></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class="col-lg-4 col-md-4" onClick='clickTab("tables-pills","tables-invoices-pills");'>
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3">
													<i class="fa fa-stack-overflow fa-5x"></i>
												</div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
													<div class="medium"><?php 
														$r = sqlsrv_query($NEI,"
															SELECT Count(Ref) AS Count_of_Invoices 
															FROM   nei.dbo.Invoice 
															WHERE  Loc='{$_GET['ID']}';
														;",array($_GET['ID']));
														echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Invoices']) : 0;?>
													</div>
													<div>Invoices</div></div>
												</div>
											</div>
										</div>
									</div>
								</div><?php }?>
								<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-4 col-md-4">
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3">
													<i class="fa fa-legal fa-5x"></i>
												</div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
													<div class="medium"><?php 
														$r = sqlsrv_query($NEI,"
															SELECT Count(ID) AS Count_of_Outstanding_Invoices 
															FROM   Job
															WHERE  Job.Loc = ?
																   AND (Job.Type = 9 
																	 OR Job.Type = 12)
														;",array($_GET['ID']));
														echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Outstanding_Invoices']) : 0;?>
													</div>
													<div>Lawsuits</div></div>
												</div>
											</div>
										</div>
									</div>
								</div><?php }?>
								<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-4 col-md-4" onClick='clickTab("tables-pills","tables-collections-pills");'>
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3">
													<i class="fa fa-stack-overflow fa-5x"></i>
												</div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
													<div class="medium"><?php 
														$r = sqlsrv_query($NEI,"
															SELECT Count(TransID) AS Count_of_Outstanding_Invoices 
															FROM   OpenAR
															WHERE  Loc = ?
														;",array($_GET['ID']));
														echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Outstanding_Invoices']) : 0;?>
													</div>
													<div>Collectables</div></div>
												</div>
											</div>
										</div>
									</div>
								</div><?php }?>
							</div>
						</div>
					</div>
					<div class='col-md-6' style=''>
						<div class='row'>
							<div class='col-md-12'>
								<div class="panel panel-primary">
									<div class="panel-heading"><h4><?php $Icons->Calendar();?> Timeline</h4></div>
									<div class='panel-body white-background BankGothic shadow' style='height:600px;overflow:auto;'>
										<div id='overview-timeline'><?php require('../../../php/element/loading-active.php');?></div>
										<script>
										$.ajax({
											url:"cgi-bin/php/element/location/overview-timeline.php?ID=<?php echo $_GET['ID'];?>",
											method:"GET",
											success:function(code){$("div#overview-timeline").html(code);}
										})
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
</div>
<script>
$(document).ready(function(){
	$("#loading-pills").removeClass("active");
	$("#overview-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>