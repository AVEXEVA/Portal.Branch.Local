<?php
session_start();
require('../../../php/index.php');
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
        $r2 = sqlsrv_query($NEI,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
?>
<div class='tab-pane fade in active' id='accounting-overview-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
				<div class='col-md-6' style=''>
					<div class='row'>
						<div class='col-md-12' style=''>
							<?php if(isset($My_Privileges['Admin'])){?><div class='col-md-12'>
								<div class="panel panel-primary">
									<div class="panel-heading">Financial Information</div>
									<div class='panel-body'>
										<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-6 col-md-6">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-dollar fa-5x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$r = sqlsrv_query($NEI,"
																	SELECT Sum(Balance) AS Count_of_Outstanding_Invoices 
																	FROM   OpenAR
																	WHERE  Loc = ?
																;",array($_GET['ID']));
																echo money_format('%(n',sqlsrv_fetch_array($r)['Count_of_Outstanding_Invoices']);
															?></div>
															<div>Needs Collection</div></div>
														</div>
													</div>
												</div>
											</div>
										</div><?php }?>
										<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-6 col-md-6">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-dollar fa-5x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$r = sqlsrv_query($NEI,"
																	SELECT Sum(Amount) AS Count_of_Outstanding_Invoices 
																	FROM   Invoice
																	WHERE  Invoice.Loc = ?
																;",array($_GET['ID']));
																$Revenue = $r ? sqlsrv_fetch_array($r)['Count_of_Outstanding_Invoices'] : 0;
																echo substr(money_format('%.2n',$Revenue),0);?>
															</div>
															<div>Total Revenue</div></div>
														</div>
													</div>
												</div>
											</div>
										</div><?php }?>
										<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-6 col-md-6">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-dollar fa-5x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$r = sqlsrv_query($NEI,"
																	SELECT Sum([JOBLABOR].[TOTAL COST]) AS Labor
																	FROM   Paradox.dbo.JOBLABOR
																		   LEFT JOIN nei.dbo.Job ON cast(Job.ID as varchar(15)) = JOBLABOR.[JOB #]
																	WHERE  Job.Loc = ?
																;",array($_GET['ID']));
																$Labor = $r ? sqlsrv_fetch_array($r)['Labor'] : 0;
																$r = sqlsrv_query($NEI,"
																	SELECT   Sum(JobI.Amount) AS Amount
																	FROM     nei.dbo.JobI 
																			 LEFT JOIN nei.dbo.Job ON JobI.Job = Job.ID
																	WHERE    Job.Loc        =  ?
																			 AND JobI.Type  =  1
																			 AND JobI.fDate >= '2017-03-30 00:00:00.000'
																;",array($_GET['ID']));
																$Labor = $r ? sqlsrv_fetch_array($r)['Amount'] + $Labor : $Labor;
																echo substr(money_format('%.2n',$Labor),0);?>
															</div>
															<div>Total Labor</div></div>
														</div>
													</div>
												</div>
											</div>
										</div><?php }?>
										<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-6 col-md-6">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-dollar fa-5x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$r = sqlsrv_query($NEI,"
																	SELECT   Sum(JobI.Amount) AS Amount
																	FROM     nei.dbo.JobI 
																			 LEFT JOIN nei.dbo.Job ON JobI.Job = Job.ID
																	WHERE    Job.Loc        =  ?
																			 AND JobI.Type  =  1
																			 AND (JobI.Labor <> 1
																				  OR JobI.Labor = ''
																				  OR JobI.Labor IS NULL)
																;",array($_GET['ID']));
																$Expenses = $r ? sqlsrv_fetch_array($r)['Amount'] : 0;
																echo substr(money_format('%.2n',$Expenses),0);?>
															</div>
															<div>Total Expenses</div></div>
														</div>
													</div>
												</div>
											</div>
										</div><?php }?>
										<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-6 col-md-6">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-dollar fa-5x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Profit = $Revenue - ($Labor + $Expenses);
																echo money_format('%.2n',$Profit);?>
															</div>
															<div>Total Profit</div></div>
														</div>
													</div>
												</div>
											</div>
										</div><?php }?>
										<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-6 col-md-6">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-dollar fa-5x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																function percent($number){return round($number * 100,2) . '%';}
																echo percent($Profit / $Revenue);
																?>
															</div>
															<div>Profit Percentage</div></div>
														</div>
													</div>
												</div>
											</div>
										</div><?php }?>
									</div>
								</div>
							</div><?php }?>
						</div>
					</div>
				</div>
				<div class='col-md-6' style=''>
					<div class='row'>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3></h3></div>
								<div class='panel-body white-background BankGothic shadow'>
								</div>
							</div>
						</div>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3></h3></div>
								<div class='panel-body white-background BankGothic shadow'>
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
	$("#accounting-overview-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>