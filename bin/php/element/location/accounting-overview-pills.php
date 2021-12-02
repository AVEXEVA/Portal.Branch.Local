 <?php 
session_start( [ 'read_and_close' => true ] );
require('../../../../bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
    	$My_User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Location']) && $My_Privileges['Location']['Owner'] >= 4 && $My_Privileges['Location']['Group'] >= 4 && $My_Privileges['Location']['Other'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Location']['Owner'] >= 4 && is_numeric($_GET['ID'])){
            $r = $database->query(  null,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.LID='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = $database->query( null,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Loc='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = $database->query( null,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Loc='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
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
																$r = $database->query(null,"
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
																$r = $database->query(null,"
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
																$r = $database->query(null,"
																	SELECT Sum([JOBLABOR].[TOTAL COST]) AS Labor
																	FROM   Paradox.dbo.JOBLABOR
																		   LEFT JOIN nei.dbo.Job ON cast(Job.ID as varchar(15)) = JOBLABOR.[JOB #]
																	WHERE  Job.Loc = ?
																;",array($_GET['ID']));
																$Labor = $r ? sqlsrv_fetch_array($r)['Labor'] : 0;
																$r = $database->query(null,"
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
																$r = $database->query(null,"
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