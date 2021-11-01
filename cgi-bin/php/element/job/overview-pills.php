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
		$r = sqlsrv_query($NEI,"
			SELECT TOP 1
                Job.ID                AS Job_ID,
                Job.fDesc             AS Job_Name,
                Job.fDate             AS Job_Start_Date,
                Job.BHour             AS Job_Budgeted_Hours,
                JobType.Type          AS Job_Type,
				Job.Remarks 		  AS Job_Remarks,
				Job.Custom15          AS Link,
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
        $Job = sqlsrv_fetch_array($r);
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
        ;",array($Job['Location_ID']));
        $Location = sqlsrv_fetch_array($r);?>
<div class="tab-pane fade in active" id="overview-pills">
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h4><?php $Icons->Job();?> Job Description</h4></div>
								<div class='panel-body white-background'>
									<div style='font-size:18px;text-decoration:underline;'><b>
										<div class='row'>
											<div class='col-xs-3'>Date:</div>
											<div class='col-xs-9'><pre><?php echo date("m/d/Y",strtotime($Job['Job_Start_Date']));?></pre></div>
										</div>
										<div class='row'>
											<div class='col-xs-3'>Type:</div>
											<div class='col-xs-9'><pre><?php echo $Job['Job_Type'];?></pre></div>
										</div>
										<div class='row'>
											<div class='col-xs-3'>Remarks:</div>
											<div class='col-xs-9'><pre><?php echo $Job['Job_Remarks'];?></pre></div>
										</div>
										<div class='row'>
											<div class='col-xs-3'>Budgeted Hours:</div>
											<div class='col-xs-9'><pre><?php echo $Job['Job_Budgeted_Hours'];?></pre></div>
										</div>
										<div class='row'>
											<div class='col-xs-3'>Actual Hours:</div>
											<div class='col-xs-9'><pre><?php 
												$r = sqlsrv_query($NEI,"
													SELECT Sum(Tickets.Total) AS Total
													FROM (
															(SELECT TicketD.Total    AS Total
															 FROM   nei.dbo.TicketD
															 WHERE  TicketD.Job = ?
															)
															UNION ALL
															(SELECT TicketDArchive.Total    AS Total
															 FROM   nei.dbo.TicketDArchive
															 WHERE  TicketDArchive.Job = ?
															)
														) AS Tickets
												",array($_GET['ID'],$_GET['ID'],$_GET['ID'],$_GET['ID']));
												echo sqlsrv_fetch_array($r)['Total'];
											;?></pre></div>
										</div>
										<div class='row' onClick="<?php if(isset($Job['Link']) && strlen($Job['Link']) > 0){?>document.location.href='<?php echo $Job['Link'];?>';<?php }?>">
											<div class='col-xs-3'>Google Drive</div>
											<div class='col-xs-9'><pre><?php echo $Job['Link'];?></pre></div>
										</div>
									</b></div>
								</div>
							</div>
						</div>
						<div class='col-md-6'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h4><?php $Icons->Location();?> Location</h4></div>
								<div class='panel-body white-background'>
									<div style='font-size:20px;text-decoration:underline;cursor:pointer;' onClick="document.location.href='location.php?ID=<?php echo $Location['Location_ID'];?>';"><b>
										<div class='row'><div class='col-xs-12'><?php echo $Location["Tag"];?></div></div>
										<div class='row'><div class='col-xs-12'><?php echo $Location["Street"];?></div></div>
										<div class='row'><div class='col-xs-12'><?php echo $Location["City"];?> <?php echo $Location["State"];?> <?php echo $Location["Zip"];?></div></div>
									</b></div>
								</div>
							</div>
						</div>
						<div class='col-md-6'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h4><?php $Icons->Customer();?> Customer</h4></div>
								<div class='panel-body white-background'>
									<div style='font-size:20px;text-decoration:underline;'><b>
										<div class='row'><div class='col-xs-12'><?php if($My_Privileges['Customer']['Other_Privilege'] >= 4){?><a href="customer.php?ID=<?php echo $Location['Customer_ID'];?>"><?php }?><?php echo $Location['Customer_Name'];?><?php if($My_Privileges['Customer']['Other_Privilege'] >= 4){?></a><?php }?></div></div>
										<div class='row'><div class='col-xs-12'><?php echo $Location["Street"];?></div></div>
										<div class='row'><div class='col-xs-12'><?php echo $Location["City"];?> <?php echo $Location["State"];?> <?php echo $Location["Zip"];?></div></div>
									</b></div>
								</div>
							</div>
						</div>
						<div class='col-md-12'>
							<div class='row'>
								<div class='col-md-6'>
									<div class="panel panel-primary">
										<div class="panel-heading"><h4><?php $Icons->Maintenance();?> Maintenance Information</h4></div>
										<div class='panel-body white-background' style='font-size:20px;padding:5px;'>
											<div class='row'><div class='col-xs-12'><?php $Icons->Route();?> <?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Location['Route_Mechanic_ID']){?><a href="route.php?ID=<?php echo $Location['Route_ID'];?>"><?php }?><?php echo proper($Location["Route_Mechanic_First_Name"] . " " . $Location["Route_Mechanic_Last_Name"]);?><?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Location['Route_Mechanic_ID']){?></a><?php }?></div></div>
											<div class='row'><div class='col-xs-12'><?php $Icons->Division();?> <?php if($My_Privileges['Ticket']['Other_Privilege'] >= 4){?><a href="dispatch.php?Supervisors=Division%201&Mechanics=undefined&Start_Date=07/13/2017&End_Date=07/13/2017"><?php }?><?php echo proper($Location["Zone"]);?><?php if($My_Privileges['Ticket']['Other_Privilege'] >= 4){?></a><?php }?></div></div>
										</div>
									</div>
								</div>
								<div class='col-md-6'>
									<div class="panel panel-primary">
										<div class="panel-heading"><h4><?php $Icons->Contract();?> Sales Information</h4></div>
										<div class='panel-body white-background' style='font-size:20px;padding:5px;'>
											<div class='row'><div class='col-xs-12'><?php echo $Location['Territory_Domain'];?>'s Territory</div></div>
										</div>
									</div>
								</div>
								<?php if(isset($My_Privileges['Admin'])){?><div class='col-md-12'>
									<div class="panel panel-primary">
										<div class="panel-heading"><?php $Icons->Chart();?> Finances</div>
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
				</div>	
				<div class='col-md-6'>
					<div class="panel panel-primary">
						<div class="panel-heading">Basic Information</div>
						<div class='panel-body'>
							<div class="col-lg-4 col-md-4">
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
							<div class="col-lg-4 col-md-4">
								<div class="panel panel-primary">
									<div class="panel-heading">
										<div class="row">
											<div class="col-xs-3">
												<i class="fa fa-suitcase fa-5x"></i>
											</div>
											<div class="col-xs-9 text-right">
												<div class="col-xs-9 text-right">
												<div class="medium"><?php 
													$r = sqlsrv_query($NEI,"SELECT Count(ID) AS Count_of_Jobs FROM Violation WHERE Job='{$_GET['ID']}';");
													echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;?>
												</div>
												<div>Violations</div></div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-4 col-md-4" onClick="document.location.href='http://www.nouveauelevator.com/portal/tickets.php?Start_Date=01/01/1980&End_Date=12/31/2017';">
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
																	(SELECT ID FROM TicketO WHERE TicketO.Job = ?)
																	UNION ALL 
																	(SELECT ID FROM TicketD WHERE TicketD.Job = ?)
																	UNION ALL
																	(SELECT ID FROM TicketDArchive WHERE TicketDArchive.Job = ?)
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
							<div class="col-lg-4 col-md-4">
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
														WHERE  Estimate.Job = ?
													;",array($_GET['ID']));
													echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;?>
												</div>
												<div>Proposals</div></div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-4 col-md-4">
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
														WHERE  Job='{$_GET['ID']}';
													;",array($_GET['ID']));
													echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Invoices']) : 0;?>
												</div>
												<div>Invoices</div></div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php if(isset($My_Privileges['Admin'])){?><div class="col-lg-4 col-md-4">
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
														SELECT Count(OpenAR.TransID) AS Count_of_Outstanding_Invoices 
														FROM   nei.dbo.OpenAR LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref = Invoice.Ref
														WHERE  Invoice.Job = ?
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
								<div class='panel-body white-background BankGothic shadow' style='height:600px;overflow:auto;'>
									<div class='row' style='font-size:20px;'><?php 
										$Timeline = array();
										$SQL_Completed_Tickets = sqlsrv_query($NEI,"
											SELECT Tickets.ID,
												   Tickets.EDate  AS Date,
												   Tickets.Object AS Object,
												   'Completed'    AS Field,
												   Tickets.Level  AS Level
											FROM   ((SELECT  TicketO.ID,
															 TicketO.EDate,
															 TicketO.Level,
															 'TicketO' AS Object
													FROM     nei.dbo.TicketO
															 LEFT JOIN nei.dbo.Job ON TicketO.Job = Job.ID
													WHERE    Job.ID = ?
															 AND TicketO.Assigned = 4)
													UNION ALL
													(SELECT  TicketD.ID,
															 TicketD.EDate,
															 TicketD.Level,
															 'TicketD' AS Object
													FROM     nei.dbo.TicketD
															 LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
													WHERE    Job.ID = ?)
													UNION ALL
													(SELECT  TicketDArchive.ID,
															 TicketDArchive.EDate,
															 TicketDArchive.Level,
															 'TicketDArchive' AS Object
													FROM     nei.dbo.TicketDArchive
															 LEFT JOIN nei.dbo.Job ON TicketDArchive.Job = Job.ID
													WHERE    Job.ID = ?)) AS Tickets
											WHERE Tickets.EDate >= dateadd(month,-6,getdate())
											ORDER BY Tickets.EDate DESC
										;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
										if($SQL_Completed_Tickets){while($Ticket = sqlsrv_fetch_array($SQL_Completed_Tickets)){
											if(!isset($Timeline[date('Y-m-d',strtotime($Ticket['Date']))])){$Timeline[date('Y-m-d',strtotime($Ticket['Date']))] = array();}
											$Timeline[date('Y-m-d',strtotime($Ticket['Date']))][] = $Ticket; 
										}}
										$SQL_Created_Tickets = sqlsrv_query($NEI,"
											SELECT Tickets.ID,
												   Tickets.CDate  AS Date,
												   Tickets.Object AS Object,
												   'Created'      AS Field,
												   Tickets.Level  AS Level
											FROM   ((SELECT  TicketO.ID,
															 TicketO.CDate,
															 TicketO.Level,
															 'TicketO' AS Object
													FROM     nei.dbo.TicketO
															 LEFT JOIN nei.dbo.Job ON TicketO.Job = Job.ID
													WHERE    Job.ID = ?
															 AND TicketO.Assigned = 4)
													UNION ALL
													(SELECT  TicketD.ID,
															 TicketD.CDate,
															 TicketD.Level,
															 'TicketD' AS Object
													FROM     nei.dbo.TicketD
															 LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
													WHERE    Job.ID = ?)
													UNION ALL
													(SELECT  TicketDArchive.ID,
															 TicketDArchive.CDate,
															 TicketDArchive.Level,
															 'TicketDArchive' AS Object
													FROM     nei.dbo.TicketDArchive
															 LEFT JOIN nei.dbo.Job ON TicketDArchive.Job = Job.ID
													WHERE    Job.ID = ?)) AS Tickets
											WHERE Tickets.CDate >= dateadd(month,-6,getdate())
											ORDER BY Tickets.ID DESC
										;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
										if($SQL_Created_Tickets){while($Ticket = sqlsrv_fetch_array($SQL_Created_Tickets)){
											if(!isset($Timeline[date('Y-m-d',strtotime($Ticket['Date']))])){$Timeline[date('Y-m-d',strtotime($Ticket['Date']))] = array();}
											$Timeline[date('Y-m-d',strtotime($Ticket['Date']))][] = $Ticket; 
										}}
										$SQL_Completed_Jobs = sqlsrv_query($NEI,"
											SELECT Job.ID,
												   Job.CloseDate AS Date,
												   'Job'         AS Object,
												   'Completed'   AS Field
											FROM   nei.dbo.Job
											WHERE  Job.ID = ?
												   AND Job.CloseDate <> ''
											ORDER BY Job.CloseDate DESC
										;",array($_GET['ID']));
										if($SQL_Completed_Jobs){while($Job = sqlsrv_fetch_array($SQL_Completed_Jobs)){
											//echo $Job['Date'];
											if(date("Y-m-d H:i:s", strtotime("-6 months")) > $Job['Date']){continue;}
											if(!isset($Timeline[date('Y-m-d',strtotime($Job['Date']))])){$Timeline[date('Y-m-d',strtotime($Job['Date']))] = array();}
											$Timeline[date('Y-m-d',strtotime($Job['Date']))][] = $Job; 
										}}
										$SQL_Created_Jobs = sqlsrv_query($NEI,"
											SELECT Job.ID,
												   Job.fDate  AS Date,
												   'Job'      AS Object,
												   'Created'  AS Field
											FROM   nei.dbo.Job
											WHERE  Job.ID = ?
											       AND Job.fDate >= dateadd(month,-6,getdate())
											ORDER BY Job.ID DESC
										;",array($_GET['ID']));
										if($SQL_Created_Jobs){while($Job = sqlsrv_fetch_array($SQL_Created_Jobs)){
											if(!isset($Timeline[date('Y-m-d',strtotime($Job['Date']))])){$Timeline[date('Y-m-d',strtotime($Job['Date']))] = array();}
											$Timeline[date('Y-m-d',strtotime($Job['Date']))][] = $Job; 
										}}

										$SQL_Created_Estimates = sqlsrv_query($NEI,"
											SELECT Estimate.ID,
												   Estimate.fDate  AS Date,
												   'Proposal'      AS Object,
												   'Created'       AS Field
											FROM   nei.dbo.Estimate
												   LEFT JOIN nei.dbo.Loc ON Loc.Loc = Estimate.LocID
											WHERE  Estimate.Job = ?
											       AND Estimate.fDate >= dateadd(month,-6,getdate())
											ORDER BY Estimate.fDate DESC
										;",array($_GET['ID']));
										if($SQL_Created_Estimates){while($Estimate = sqlsrv_fetch_array($SQL_Created_Estimates)){
											if(!isset($Timeline[date('Y-m-d',strtotime($Estimate['Date']))])){$Timeline[date('Y-m-d',strtotime($Estimate['Date']))] = array();}
											$Timeline[date('Y-m-d',strtotime($Estimate['Date']))][] = $Estimate; 
										}}
										$SQL_Paid_Invoices = sqlsrv_query($NEI,"
											SELECT Trans.ID      AS ID,
												   Trans.fDate   AS Date,
												   'Transaction' AS Object,
												   'Paid'        AS Field,
												   Trans.Ref     AS Ref
											FROM   nei.dbo.Trans
												   LEFT JOIN nei.dbo.Invoice ON Trans.Ref = Invoice.Ref
												   LEFT JOIN nei.dbo.Job     ON Job.ID    = Invoice.Job
											WHERE  Job.ID = ?
												   AND Trans.Type = 1
												   AND Trans.fDate >= dateadd(month,-6,getdate())
										;",array($_GET['ID']));

										if($SQL_Paid_Invoices){while($Payment = sqlsrv_fetch_array($SQL_Paid_Invoices)){

											if(!isset($Timeline[date('Y-m-d',strtotime($Payment['Date']))])){$Timeline[date('Y-m-d',strtotime($Payment['Date']))] = array();}
											$Timeline[date('Y-m-d',strtotime($Payment['Date']))][] = $Payment; 
										}}
										$SQL_Created_Invoices = sqlsrv_query($NEI,"
											SELECT Invoice.Ref   AS ID,
												   Invoice.fDate AS Date,
												   'Invoice'     AS Object,
												   'Created'     AS Field
											FROM   nei.dbo.Invoice
												   LEFT JOIN nei.dbo.Job ON Job.ID = Invoice.Job
												   AND Invoice.fDate >= dateadd(month,-6,getdate())
											WHERE  Job.ID = ?
											ORDER BY Job.fDate DESC
										;",array($_GET['ID']));
										if($SQL_Created_Invoices){while($Invoice = sqlsrv_fetch_array($SQL_Created_Invoices)){
											if(!isset($Timeline[date('Y-m-d',strtotime($Invoice['Date']))])){$Timeline[date('Y-m-d',strtotime($Invoice['Date']))] = array();}
											$Timeline[date('Y-m-d',strtotime($Invoice['Date']))][] = $Invoice; 
										}}


										$SQL_Created_Violation = sqlsrv_query($NEI,"
											SELECT Violation.ID    AS ID,
												   Violation.fdate AS Date,
												   'Violation'     AS Object,
												   'Created'       AS Field
											FROM   nei.dbo.Violation
												   LEFT JOIN nei.dbo.Loc ON Loc.Loc = Violation.Loc
											WHERE  Violation.Job = ?
												   AND Violation.fDate >= dateadd(month,-6,getdate())
											ORDER BY Violation.fDate DESC
										;",array($_GET['ID']));
										if($SQL_Created_Violation){while($Violation = sqlsrv_fetch_array($SQL_Created_Violation)){
											if(!isset($Timeline[date('Y-m-d',strtotime($Violation['Date']))])){$Timeline[date('Y-m-d',strtotime($Violation['Date']))] = array();}
											$Timeline[date('Y-m-d',strtotime($Violation['Date']))][] = $Violation; 
										}}
										$SQL_Overdue_Violations = sqlsrv_query($NEI,"
											SELECT *,
												   Violations.Due_Date  AS Date,
												   'Overdue'            AS Field
												FROM
													((SELECT 0					 	   AS ID,
														   Job.fDesc	               AS Name,
														   ''						   AS fDate,
														   'Job Created'   			   AS Status,
														   Loc.Tag                     AS Location,
														   Elev.State                  AS Unit,
														   Job.Custom1                 AS Division,
														   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
														   Job.ID 			           AS Job,
														   Job.Custom16 			   AS Due_Date,
														   '' 						   AS Remarks,
														   'Job'                       AS Object
													FROM   nei.dbo.Job 	
														   LEFT JOIN nei.dbo.Elev  ON Job.Elev       = Elev.ID
														   LEFT JOIN nei.dbo.Loc   ON Job.Loc        = Loc.Loc
														   LEFT JOIN nei.dbo.Zone  ON Loc.Zone       = Zone.ID
														   LEFT JOIN nei.dbo.Route ON Loc.Route      = Route.ID
														   LEFT JOIN nei.dbo.Emp   ON Route.Mech     = Emp.fWork
													WHERE  ((Job.fDesc LIKE '%CAT%' AND Job.fDesc LIKE '%DEF%')
														   OR Job.fDesc LIKE '%PVT%')
														   AND Job.Status = 0
														   AND Job.ID = ?)
													UNION ALL
													(SELECT Violation.ID               AS ID,
														   Violation.Name              AS Name,
														   Violation.fdate             AS fDate,
														   Violation.Status            AS Status,
														   Loc.Tag                     AS Location,
														   Elev.State                  AS Unit,
														   Zone.Name                   AS Division,
														   Emp.fFirst + ' ' + Emp.Last AS Mechanic,
														   Violation.Job 			   AS Job,
														   SUBSTRING(Violation.Remarks,CHARINDEX('DUE: ',Violation.Remarks)+5,8) AS Due_Date,
														   '' 						   AS Remarks,
														   'Violation'                 AS Object
													FROM   nei.dbo.Violation
														   LEFT JOIN nei.dbo.Elev  ON Violation.Elev = Elev.ID
														   LEFT JOIN nei.dbo.Loc   ON Violation.Loc  = Loc.Loc
														   LEFT JOIN nei.dbo.Zone  ON Loc.Zone       = Zone.ID
														   LEFT JOIN nei.dbo.Route ON Loc.Route      = Route.ID
														   LEFT JOIN nei.dbo.Emp   ON Route.Mech     = Emp.fWork
														   LEFT JOIN nei.dbo.Job   ON Violation.Job  = Job.ID
													WHERE  Violation.Remarks LIKE '%DUE: [0123456789][0123456789]/[0123456789][0123456789]/[0123456789][0123456789]%'
														   AND Violation.Status <> 'Dismissed'
														   AND Violation.ID     <> 0
														   AND Job.ID = ?
														   AND (Violation.Job = 0
																OR 
																(Violation.Job > 0
																AND Job.Status = 0)))) AS Violations
										;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
										if($SQL_Overdue_Violations){while($Violation = sqlsrv_fetch_array($SQL_Overdue_Violations)){
											if(date('Y-m-d') < '20'. substr($Violation['Date'],6,2) . '-' .substr($Violation['Date'],0,2) . '-' . substr($Violation['Date'],3,2)){continue;}
											if(!isset($Timeline['20'. substr($Violation['Date'],6,2) . '-' .substr($Violation['Date'],0,2) . '-' . substr($Violation['Date'],3,2)])){$Timeline['20'. substr($Violation['Date'],6,2) . '-' .substr($Violation['Date'],0,2) . '-' . substr($Violation['Date'],3,2)] = array();}
											$Timeline['20'. substr($Violation['Date'],6,2) . '-' .substr($Violation['Date'],0,2) . '-' . substr($Violation['Date'],3,2)][] = $Violation; 

										}}
										$SQL_Contract_Starts = sqlsrv_query($NEI,"
											SELECT Contract.Job    AS ID,
												   Contract.BStart AS Date,
												   'Contract'      AS Object,
												   'Starts'        AS Field
											FROM   nei.dbo.Contract
												   LEFT JOIN nei.dbo.Job ON Job.ID = Contract.Job
											WHERE  Job.ID = ?
												   AND Contract.BStart >= dateadd(month,-6,getdate())
											ORDER BY Contract.BStart DESC
										;",array($_GET['ID']));
										if($SQL_Contract_Starts){while($Contract = sqlsrv_fetch_array($SQL_Contract_Starts)){
											if(!isset($Timeline[date('Y-m-d',strtotime($Contract['Date']))])){$Timeline[date('Y-m-d',strtotime($Contract['Date']))] = array();}
											$Timeline[date('Y-m-d',strtotime($Contract['Date']))][] = $Contract; 
											$Now                  = new DateTime(date('Y-m-d'));
											$Date                 = new DateTime(date('Y-m-d',strtotime($Contract['Date'])));
											$oneDayDateInterval   = new DateInterval('P1D');
											$oneMonthDateInterval = new DateInterval('P1M');
											$Contract['Field'] = 'Billed';
											while($Now->format('Y-m-d') >= $Date->format('Y-m-d')){
												if(!isset($Timeline[$Date->format('Y-m-d')])){$Timeline[$Date->format('Y-m-d')] = array();}
												$Timeline[$Date->format('Y-m-d')][] = $Contract;
												while($Date->format('d') != 1){$Date->sub($oneDayDateInterval);}
												$Date->add($oneMonthDateInterval);
											}
										}}
										$SQL_Overdue_Invoices = sqlsrv_query($NEI,"
											SELECT OpenAR.Ref AS ID,
												   OpenAR.Due AS Date,
												   'OpenAR'   AS Object,
												   'Overdue'  AS Field
											FROM   nei.dbo.OpenAR
												   LEFT JOIN nei.dbo.Invoice ON Invoice.Ref = OpenAR.Ref
												   LEFT JOIN nei.dbo.Job     ON Job.ID      = Invoice.Job
												   AND OpenAR.Due >= dateadd(month,-6,getdate())
											WHERE  Job.Loc = ?
											ORDER BY Job.fDate DESC
										;",array($_GET['ID']));
										if($SQL_Overdue_Invoices){while($Invoice = sqlsrv_fetch_array($SQL_Overdue_Invoices)){
											if(!isset($Timeline[date('Y-m-d',strtotime($Invoice['Date']))])){$Timeline[date('Y-m-d',strtotime($Invoice['Date']))] = array();}
											$Timeline[date('Y-m-d',strtotime($Invoice['Date']))][] = $Invoice; 
										}}
										krsort($Timeline);
										if(count($Timeline) > 0){foreach($Timeline as $Date=>$DayTimeline){
											?><div class='col-md-12' style='background-color:#252525;color:white;'>
												<h3 style='text-align:center;'><?php echo date('m/d/Y',strtotime($Date));?></h3>
											</div><?php
											
											foreach($DayTimeline as $Instance){
												//$Instance['Date'] = date('m/d/Y',strtotime($Instance['Date']));
												if(substr($Instance['Object'],0,6) == 'Ticket' && $Instance['Field'] == 'Completed'){
													?><div class='col-md-12'><a href='ticket.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Ticket();?> Completed <?php if($Instance['Level'] == 1){?>Service Call <?php }elseif($Instance['Level'] == 10){?>Preventative Maintenance <?php }?>Ticket #<?php echo $Instance['ID'];?></a></div><?php
												} elseif(substr($Instance['Object'],0,6) == 'Ticket' && $Instance['Field'] == 'Created'){
													?><div class='col-md-12'><a href='ticket.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Ticket();?> Created <?php if($Instance['Level'] == 1){?>Service Call <?php }elseif($Instance['Level'] == 10){?>Preventative Maintenance <?php }?>Ticket #<?php echo $Instance['ID'];?></a></div><?php
												} elseif(substr($Instance['Object'],0,3) == 'Job' && $Instance['Field'] == 'Created'){
													?><div class='col-md-12'><a href='job.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Job();?> Created Job #<?php echo $Instance['ID'];?></a></div><?php
												} elseif(substr($Instance['Object'],0,3) == 'Job' && $Instance['Field'] == 'Completed'){
													?><div class='col-md-12'><a href='job.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Job();?> Completed Job #<?php echo $Instance['ID'];?></a></div><?php
												} elseif(substr($Instance['Object'],0,8) == 'Proposal' && $Instance['Field'] == 'Created'){
													?><div class='col-md-12'><a href='proposal.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Proposal();?> Created Proposal #<?php echo $Instance['ID'];?></a></div><?php
												} elseif(substr($Instance['Object'],0,7) == 'Invoice' && $Instance['Field'] == 'Created'){
													?><div class='col-md-12'><a href='invoice.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Invoice();?> Created Invoice #<?php echo $Instance['ID'];?></a></div><?php
												} elseif(substr($Instance['Object'],0,10) == 'Violation' && $Instance['Field'] == 'Created'){
													?><div class='col-md-12'><a href='violation.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Violation();?> Created Violation #<?php echo $Instance['ID'];?></a></div><?php
												} elseif($Instance['Object'] == 'Transaction' && $Instance['Field'] == 'Paid'){
													?><div class='col-md-12'><a href='transaction.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Invoice();?> Paid Invoice #<?php echo $Instance['Ref'];?></a></div><?php
												} elseif(substr($Instance['Object'],0,10) == 'Violation' && $Instance['Field'] == 'Overdue'){
													?><div class='col-md-12'><a href='violation.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Violation();?> Overdue Violation #<?php echo $Instance['ID'];?></a></div><?php
												} elseif(substr($Instance['Object'],0,10) == 'Job' && $Instance['Field'] == 'Overdue'){
													?><div class='col-md-12'><a href='job.php?ID=<?php echo $Instance['Job'];?>'><?php $Instance['Date'];?> <?php $Icons->Violation();?> Overdue Violation Job #<?php echo $Instance['Job'];?></a></div><?php
												} elseif($Instance['Object'] == 'Contract' && $Instance['Field'] == 'Starts'){
													?><div class='col-md-12'><a href='contract.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Contract();?> Contract Starts Job #<?php echo $Instance['ID'];?></a></div><?php
												} elseif($Instance['Object'] == 'Contract' && $Instance['Field'] == 'Billed'){
													?><div class='col-md-12'><a href='contract.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Contract();?> Contract Billed Job #<?php echo $Instance['ID'];?></a></div><?php
												} elseif(substr($Instance['Object'],0,10) == 'OpenAR' && $Instance['Field'] == 'Overdue'){
													?><div class='col-md-12'><a href='violation.php?ID=<?php echo $Instance['ID'];?>'><?php $Instance['Date'];?> <?php $Icons->Violation();?> Overdue Invoice #<?php echo $Instance['ID'];?></a></div><?php
												}
											}
										}}
									?></div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				
				<?php /*<div class='col-md-6'>
					<div class="chat-panel panel panel-primary">
						<div class="panel-heading">
							<i class="fa fa-comments fa-fw"></i> Chat
							<div class="btn-group pull-right">
								<button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
									<i class="fa fa-chevron-down"></i>
								</button>
								<ul class="dropdown-menu slidedown">
									<li>
										<a href="#">
											<i class="fa fa-refresh fa-fw"></i> Refresh
										</a>
									</li>
								</ul>
							</div>
						</div>
						<!-- /.panel-heading -->
						<div class="panel-body white-background">
							<ul class="chat">
								<li class="left clearfix">
									<span class="chat-img pull-left">
										<img src="http://placehold.it/50/55C1E7/fff" alt="User Avatar" class="img-circle" />
									</span>
									<div class="chat-body clearfix">
										<div class="header">
											<strong class="primary-font">Jack Sparrow</strong>
											<small class="pull-right text-muted">
												<i class="fa fa-clock-o fa-fw"></i> 12 mins ago
											</small>
										</div>
										<p>
											Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur bibendum ornare dolor, quis ullamcorper ligula sodales.
										</p>
									</div>
								</li>
								<li class="right clearfix">
									<span class="chat-img pull-right">
										<img src="http://placehold.it/50/FA6F57/fff" alt="User Avatar" class="img-circle" />
									</span>
									<div class="chat-body clearfix">
										<div class="header">
											<small class=" text-muted">
												<i class="fa fa-clock-o fa-fw"></i> 13 mins ago</small>
											<strong class="pull-right primary-font">Bhaumik Patel</strong>
										</div>
										<p>
											Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur bibendum ornare dolor, quis ullamcorper ligula sodales.
										</p>
									</div>
								</li>
								<li class="left clearfix">
									<span class="chat-img pull-left">
										<img src="http://placehold.it/50/55C1E7/fff" alt="User Avatar" class="img-circle" />
									</span>
									<div class="chat-body clearfix">
										<div class="header">
											<strong class="primary-font">Jack Sparrow</strong>
											<small class="pull-right text-muted">
												<i class="fa fa-clock-o fa-fw"></i> 14 mins ago</small>
										</div>
										<p>
											Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur bibendum ornare dolor, quis ullamcorper ligula sodales.
										</p>
									</div>
								</li>
								<li class="right clearfix">
									<span class="chat-img pull-right">
										<img src="http://placehold.it/50/FA6F57/fff" alt="User Avatar" class="img-circle" />
									</span>
									<div class="chat-body clearfix">
										<div class="header">
											<small class=" text-muted">
												<i class="fa fa-clock-o fa-fw"></i> 15 mins ago</small>
											<strong class="pull-right primary-font">Bhaumik Patel</strong>
										</div>
										<p>
											Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur bibendum ornare dolor, quis ullamcorper ligula sodales.
										</p>
									</div>
								</li>
							</ul>
						</div>
						<!-- /.panel-body white-background -->
						<div class="panel-footer">
							<div class="input-group">
								<input id="btn-input" type="text" class="form-control input-sm" placeholder="Type your message here..." />
								<span class="input-group-btn">
									<button class="btn btn-warning btn-sm" id="btn-chat">
										Send
									</button>
								</span>
							</div>
						</div>
						<!-- /.panel-footer -->
					</div>
					<!-- /.panel .chat-panel -->
				</div>*/?>
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