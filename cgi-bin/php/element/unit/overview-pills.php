<?php
session_start();
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
            $r = sqlsrv_query($NEI,"
				SELECT * 
				FROM   TicketO 
					   LEFT JOIN nei.dbo.Loc  ON TicketO.LID   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc       = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketO.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r2 = sqlsrv_query($NEI,"
				SELECT * 
				FROM   TicketD 
					   LEFT JOIN nei.dbo.Loc  ON TicketD.Loc   = Loc.Loc
					   LEFT JOIN nei.dbo.Elev ON Loc.Loc       = Elev.Loc
					   LEFT JOIN nei.dbo.Emp  ON TicketD.fWork = Emp.fWork
				WHERE  Emp.ID      = ?
					   AND Elev.ID = ?
            ;",array($_SESSION['User'],$_GET['ID']));
            $r3 = sqlsrv_query($NEI,"
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
        $SQL_Result = sqlsrv_query($NEI,"
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
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    Elev.ID              AS ID,
                    Elev.Unit            AS Unit,
                    Elev.State           AS State,
                    Elev.Cat             AS Category,
                    Elev.Type            AS Type,
                    Elev.Building        AS Building,
                    Elev.Since           AS Since,
                    Elev.Last            AS Last,
                    Elev.Price           AS Price,
                    Elev.fDesc           AS Description,
                    Loc.Loc              AS Location_ID,
                    Loc.ID               AS Location_Name,
                    Loc.Tag              AS Location_Tag,
                    Loc.Address          AS Location_Street,
                    Loc.City             AS Location_City,
                    Loc.State            AS Location_State,
                    Loc.Zip              AS Location_Zip,
                    Loc.Route            AS Route,
                    Zone.Name            AS Zone,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.ID      AS Customer_ID,
					OwnerWithRol.Address AS Customer_Street,
					OwnerWithRol.City    AS Customer_City,
					OwnerWithRol.State   AS Customer_State,
					OwnerWithRol.Zip     AS Customer_Zip,
					OwnerWithRol.Phone   AS Customer_Phone,
					OwnerWithRol.Fax     AS Customer_Fax,
					OwnerWithRol.Contact AS Customer_Contact,
					OwnerWithRol.Remarks AS Customer_Remarks,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
					Terr.Name            AS Territory_Domain
            FROM    nei.dbo.Elev
                    LEFT JOIN nei.dbo.Loc           ON Elev.Loc = Loc.Loc
                    LEFT JOIN nei.dbo.Zone          ON Loc.Zone = Zone.ID
                    LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                    LEFT JOIN nei.dbo.Route         ON Loc.Route = Route.ID
                    LEFT JOIN nei.dbo.Emp           ON Route.Mech = Emp.fWork
					LEFT JOIN Terr         		    ON Terr.ID    = Loc.Terr
            WHERE   Elev.ID = ?
		;",array($_GET['ID']));
        $Unit = sqlsrv_fetch_array($r);
        $data = $Unit;
        $r2 = sqlsrv_query($NEI,"
            SELECT *
            FROM   nei.dbo.ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
?>
<div class="tab-pane fade in active" id="overview-pills">
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<!--<div class="panel-heading"><h3><?php $Icons->Dashboard();?> <?php echo $Location['Tag'];  ?> Dashboard</h3></div>-->
		<div class="panel-body">
			<div class="row">
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-6'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h4><?php $Icons->Unit();?> Unit</h4></div>
								<div class='panel-body white-background'>
									<div style='font-size:20px;text-decoration:underline;'><b>
										<div class='row' style='padding:5px;'>
											<div class='col-xs-4' style='text-align:right;'>State:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['State'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Label:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Unit'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Type:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Type'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Category:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Category'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Price:</div>
											<div class='col-xs-8'><pre><?php echo substr(money_format('%.2n',$Unit['Price']),0);?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Description:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Description'];?></pre></div>
										</div>
									</b></div>
								</div>
							</div>
						</div>
						<div class='col-md-6'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h4><?php $Icons->Customer();?> Customer</h4></div>
								<div class='panel-body white-background'>
									<div style='font-size:20px;text-decoration:underline;'><b>
										<div class='row' style='padding:5px;'>
											<div class='col-xs-4' style='text-align:right;'>Name:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Customer_Name'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Contact:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Customer_Contact'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Street:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Customer_Street'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>City:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Customer_City'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>State:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Customer_State'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Zip:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Customer_Zip'];?></pre></div>
											<!--<div class='col-xs-4' style='text-align:right;'>Phone:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Customer_Phone'];?></pre></div>-->
											<!--<div class='col-xs-4' style='text-align:right;'>Fax:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Customer_Fax'];?></pre></div>-->
										</div>
									</b></div>
								</div>
							</div>
						</div>
						<div class='col-md-6'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h4><?php $Icons->Location();?> Location</h4></div>
								<div class='panel-body white-background'>
									<div style='font-size:20px;text-decoration:underline;'><b>
										<div class='row' style='padding:5px;'>
											<div class='col-xs-4' style='text-align:right;'>ID:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Location_Name'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Tag:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Location_Tag'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Street:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Location_Street'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>City:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Location_City'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>State:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Location_State'];?></pre></div>
											<div class='col-xs-4' style='text-align:right;'>Zip:</div>
											<div class='col-xs-8'><pre><?php echo $Unit['Location_Zip'];?></pre></div>
										</div>
									</b></div>
								</div>
							</div>
						</div>
						<div class='col-md-6'>
							<div class='row'>
								<div class='col-md-12'>
									<div class="panel panel-primary">
										<div class="panel-heading"><h4>Maintenance Information</h4></div>
										<div class='panel-body white-background'>
											<div class='row' style='font-size:20px;padding:5px;'>
												<div class='col-xs-12'><?php $Icons->Route();?> <?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Unit['Route_Mechanic_ID']){?><a href="route.php?ID=<?php echo $Unit['Route_ID'];?>"><?php }?><?php echo proper($Unit["Route_Mechanic_First_Name"] . " " . $Unit["Route_Mechanic_Last_Name"]);?><?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Unit['Route_Mechanic_ID']){?></a><?php }?>
												</div>
												<div class='col-xs-12'><?php $Icons->Division();?> <?php if($My_Privileges['Ticket']['Other_Privilege'] >= 4){?><a href="dispatch.php?Supervisors=Division%201&Mechanics=undefined&Start_Date=07/13/2017&End_Date=07/13/2017"><?php }?><?php echo proper($Unit["Zone"]);?><?php if($My_Privileges['Ticket']['Other_Privilege'] >= 4){?></a><?php }?></div>
											</div>
										</div>
									</div>
								</div>
								<div class='col-md-12'>
									<div class="panel panel-primary">
										<div class="panel-heading"><h4>Sales Information</h4></div>
										<div class='panel-body white-background'>
											<div class='row' style='font-size:20px;padding:5px;'>
												<div class='col-xs-12'><?php echo $Unit['Territory_Domain'];?>'s Territory</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>	
				<div class='col-md-6'>
					<div class="panel panel-primary">
						<div class="panel-heading">Basic Information</div>
						<div class='panel-body'>
							<div class="col-lg-4 col-md-4" onClick='clickTab("tables-pills","tables-jobs-pills");'>
								<div class="panel panel-primary">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4">
												<i class="fa fa-suitcase fa-5x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="col-xs-8 text-right">
												<div class="medium"><?php 
													$r = sqlsrv_query($NEI,"
														SELECT Count(ID) AS Count_of_Jobs 
														FROM   nei.dbo.Job 
														WHERE  Elev= ?
													;",array($_GET['ID']));
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
											<div class="col-xs-4">
												<i class="fa fa-suitcase fa-5x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="col-xs-8 text-right">
												<div class="medium"><?php 
													$r = sqlsrv_query($NEI,"
														SELECT Count(ID) AS Count_of_Jobs 
														FROM   nei.dbo.Violation 
														WHERE  Violation.Elev ='{$_GET['ID']}'
													;");
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
											<div class="col-xs-4">
												<i class="fa fa-ticket fa-5x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="col-xs-8 text-right">
												<div class="medium"><?php 
													$r = sqlsrv_query($NEI,"
														SELECT Count(Tickets.ID) AS Count_of_Tickets 
														FROM   (
																	(SELECT ID FROM TicketO WHERE TicketO.LElev = ?)
																	UNION ALL 
																	(SELECT ID FROM TicketD WHERE TicketD.Elev = ?)
																	UNION ALL
																	(SELECT ID FROM TicketDArchive WHERE TicketDArchive.Elev = ?)
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
							<div class="col-lg-4 col-md-4" onClick='clickTab("tables-pills","tables-proposals-pills");'>
								<div class="panel panel-primary">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4">
												<i class="fa fa-folder-open fa-5x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="col-xs-8 text-right">
												<div class="medium"><?php 
													$r = sqlsrv_query($NEI,"
														SELECT Count(Estimate.ID) AS Count_of_Tickets 
														FROM   nei.dbo.Estimate
															   LEFT JOIN nei.dbo.Job ON Estimate.Job = Job.ID
														WHERE  Job.Elev = ?
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
											<div class="col-xs-4">
												<i class="fa fa-stack-overflow fa-5x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="col-xs-8 text-right">
												<div class="medium"><?php 
													$r = sqlsrv_query($NEI,"
														SELECT Count(Ref) AS Count_of_Invoices 
														FROM   nei.dbo.Invoice 
															   LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
														WHERE  Job.Elev = ?
													;",array($_GET['ID']));
													echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Invoices']) : 0;?>
												</div>
												<div>Invoices</div></div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-4 col-md-4" onClick='clickTab("tables-pills","tables-collections-pills");'>
								<div class="panel panel-primary">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-4">
												<i class="fa fa-stack-overflow fa-5x"></i>
											</div>
											<div class="col-xs-8 text-right">
												<div class="col-xs-8 text-right">
												<div class="medium"><?php 
													$r = sqlsrv_query($NEI,"
														SELECT Count(TransID) AS Count_of_Outstanding_Invoices 
														FROM   nei.dbo.OpenAR
															   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref = Invoice.Ref
															   LEFT JOIN nei.dbo.Job     ON Job.ID     = Invoice.Job
														WHERE  Job.Elev = ?
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
								<div class="panel-heading"><h3><?php $Icons->Calendar();?> Timeline</h3></div>
								<div class='panel-body white-background BankGothic shadow' style='height:400px;overflow:auto;'>
									<div id='overview-timeline'><?php require('../../../php/element/loading-active.php');?></div>
									<script>
									$.ajax({
										url:"cgi-bin/php/element/unit/overview-timeline.php?ID=<?php echo $_GET['ID'];?>",
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
<script>
$(document).ready(function(){
	$("#loading-pills").removeClass("active");
	$("#overview-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>