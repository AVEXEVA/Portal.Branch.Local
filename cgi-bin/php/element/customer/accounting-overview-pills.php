 <?php 
session_start();
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');setlocale(LC_MONETARY, 'en_US');
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
<div class='tab-pane fade in active' id='accounting-overview-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
				<div class='col-md-6' style=''>
					<div class='row'>
						<div class='col-md-12' style=''>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><?php $Icons->Chart();?>Financial Information</h3></div>
								<div class='panel-body BankGothic'>
									<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-6 col-md-6">
										<div class="panel panel-primary">
											<div class="panel-heading">
												<div class="row">
													<div class="col-xs-3">
														<i class="fa fa-dollar fa-3x"></i>
													</div>
													<div class="col-xs-9 text-right">
														<div class="col-xs-9 text-right">
														<div class="medium"><?php 
															$r = sqlsrv_query($NEI,"
																SELECT Sum(OpenAR.Balance) AS Count_of_Outstanding_Invoices 
																FROM   nei.dbo.OpenAR
																	   LEFT JOIN nei.dbo.Loc ON OpenAR.Loc = Loc.Loc
																WHERE  Loc.Owner = ?
															;",array($_GET['ID']));
															echo $r ? substr(money_format('%.2n',sqlsrv_fetch_array($r)['Count_of_Outstanding_Invoices']),0) : 0;?>
														</div>
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
														<i class="fa fa-dollar fa-3x"></i>
													</div>
													<div class="col-xs-9 text-right">
														<div class="col-xs-9 text-right">
														<div class="medium"><?php 
															$r = sqlsrv_query($NEI,"
																SELECT Sum(Invoice.Amount) AS Count_of_Outstanding_Invoices 
																FROM   nei.dbo.Invoice
																	   LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
																WHERE  Loc.Owner = ?
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
														<i class="fa fa-dollar fa-3x"></i>
													</div>
													<div class="col-xs-9 text-right">
														<div class="col-xs-9 text-right">
														<div class="medium"><?php 
															$r = sqlsrv_query($NEI,"
																SELECT Sum([JOBLABOR].[TOTAL COST]) AS Labor
																FROM   Paradox.dbo.JOBLABOR
																	   LEFT JOIN nei.dbo.Job ON cast(Job.ID as varchar(15)) = JOBLABOR.[JOB #]
																	   LEFT JOIN nei.dbo.Loc ON Loc.Loc = Job.Loc
																WHERE  Loc.Owner = ?
															;",array($_GET['ID']));
															$Labor = $r ? sqlsrv_fetch_array($r)['Labor'] : 0;
															$r = sqlsrv_query($NEI,"
																SELECT   Sum(JobI.Amount) AS Amount
																FROM     nei.dbo.JobI 
																		 LEFT JOIN nei.dbo.Job ON JobI.Job = Job.ID
																		 LEFT JOIN Loc ON Loc.Loc = Job.Loc
																WHERE    Loc.Owner      =  ?
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
														<i class="fa fa-dollar fa-3x"></i>
													</div>
													<div class="col-xs-9 text-right">
														<div class="col-xs-9 text-right">
														<div class="medium"><?php 
															$r = sqlsrv_query($NEI,"
																SELECT   Sum(JobI.Amount) AS Amount
																FROM     nei.dbo.JobI 
																		 LEFT JOIN nei.dbo.Job ON JobI.Job = Job.ID
																		 LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc
																WHERE    Loc.Owner        =  ?
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
														<i class="fa fa-dollar fa-3x"></i>
													</div>
													<div class="col-xs-9 text-right">
														<div class="col-xs-9 text-right">
														<div class="medium"><?php 
															$Profit = $Revenue - ($Labor + $Expenses);
															echo money_format('%.2n',$Profit);?>
														</div>
														<div>Total Profit </br> without Overhead</div></div>
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
														<i class="fa fa-dollar fa-3x"></i>
													</div>
													<div class="col-xs-9 text-right">
														<div class="col-xs-9 text-right">
														<div class="medium"><?php 
															function percent($number){return round($number * 100,2) . '%';}
															echo percent($Profit / $Revenue);
															?>
														</div>
														<div>Profit Percentage without Overhead</div></div>
													</div>
												</div>
											</div>
										</div>
									</div><?php }?>
								</div>
							</div>
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