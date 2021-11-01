<?php
session_start();
require('../../../php/index.php');
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
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Job']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = sqlsrv_query(  $NEI,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r2 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r3 = sqlsrv_query( $NEI,"SELECT * FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Job='{$_GET['ID']}' AND fWork='{$My_User['fWork']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
		}
    } elseif($_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']){$Privileged = TRUE;}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
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
																	FROM   nei.dbo.OpenAR
																	       LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref = Invoice.Ref
																	WHERE  Invoice.Job = ?
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
																	WHERE  Invoice.Job = ?
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
																	WHERE  Job.ID = ?
																;",array($_GET['ID']));
																$Labor = $r ? sqlsrv_fetch_array($r)['Labor'] : 0;
																$r = sqlsrv_query($NEI,"
																	SELECT   Sum(JobI.Amount) AS Amount
																	FROM     nei.dbo.JobI
																			 LEFT JOIN nei.dbo.Job ON JobI.Job = Job.ID
																	WHERE    Job.ID        =  ?
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
																	WHERE    Job.ID        =  ?
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
