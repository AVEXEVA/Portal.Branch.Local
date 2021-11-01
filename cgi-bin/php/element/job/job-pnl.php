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
			$a = sqlsrv_query($NEI,"
				SELECT Job.Loc
				FROM nei.dbo.Job
				WHERE Job.ID = ?
			;",array($_GET['ID']));
			$loc = sqlsrv_fetch_array($a)['Loc'];
            $r = sqlsrv_query(  $NEI,"
				SELECT *
				FROM 		nei.dbo.Job
				LEFT JOIN 	nei.dbo.TicketO ON Job.ID = TicketO.Job
				WHERE 		TicketO.LID= ?
					AND 	TicketO.fWork= ?
			;",array($loc,$My_User['fWork']));
            $r2 = sqlsrv_query( $NEI,"
				SELECT *
				FROM 		nei.dbo.Job
				LEFT JOIN 	nei.dbo.TicketD ON Job.ID = TicketD.Job
				WHERE 		TicketD.Loc= ?
							AND TicketD.fWork= ?
			;",array($loc,$My_User['fWork']));
			$r3 = sqlsrv_query( $NEI,"
				SELECT *
				FROM 		nei.dbo.Job
				LEFT JOIN 	nei.dbo.TicketDArchive ON Job.ID = TicketDArchive.Loc
				WHERE 		TicketDArchive.Loc= ?
							AND TicketDArchive.fWork= ?
			;",array($loc,$My_User['fWork']));
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
			$r3 = sqlsrv_fetch_array($r3);
            $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
		}
    }
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged ){require("401.html");}
    else {
       $r = sqlsrv_query($NEI,"
			SELECT TOP 1
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
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
				<div class='col-md-12'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><?php $Icons->Territory();?>Total Profit</h3></div>
						<div class='panel-body white-background BankGothic shadow'>
							<div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-profit"></div></div>
						</div>
					</div>
				</div>
				<?php require('../../../js/chart/job_profit.php');?>
				<div class='col-md-12' style=''>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3>Yearly Profit</h3></div>
						<div class='panel-body white-background shadow'>
							<table id="Table_Profit" class="display" cellspacing='0' width='100%'>
								<?php
								$resource = sqlsrv_query($NEI,"
									SELECT   Overhead_Cost.*
									FROM     Portal.dbo.Overhead_Cost
									ORDER BY Overhead_Cost.Type ASC
								;");
								$Overhead_Costs = array();
								if($resource){while($Overhead_Cost = sqlsrv_fetch_array($resource)){$Overhead_Costs[] = $Overhead_Cost;}}?>
								<thead style='border-left:3px solid black;border-right:3px solid black;border-top:3px solid black;'>
									<th></th>
									<?php
										foreach($Overhead_Costs as $Overhead_Cost){
											?><th style='border:1px solid black;padding:3px;color:black;'><?php echo $Overhead_Cost['Type'];?></th><?php
										}
									?>
								</thead>
								<tbody style='border:3px solid black;'>
									<tr>
										<td style='border:1px solid black;padding:3px;'>Revenue</td>
										<?php
										foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
											$resource = sqlsrv_query($NEI,"
												SELECT Sum(Invoice.Amount) AS Revenue
												FROM   nei.dbo.Invoice
													   LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
												WHERE  Invoice.Job = ?
													   AND Invoice.fDate >= ?
													   AND Invoice.fDate < ?
											;",array($_GET['ID'],$Overhead_Cost['Start'],$Overhead_Cost['End']));
											$Overhead_Costs[$key]['Revenue'] = sqlsrv_fetch_array($resource)['Revenue'];
											echo money_format('%(n',$Overhead_Costs[$key]['Revenue']);
										?></td><?php }?>
									</tr>
									<tr>
										<td style='border:1px solid black;padding:3px;'>Labor</td>
										<?php
										foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
											//var_dump($Overhead_Cost);
											$resource = sqlsrv_query($NEI,"
												SELECT Sum(JobI.Amount) AS Labor
												FROM   nei.dbo.Loc
													   LEFT JOIN nei.dbo.Job  ON Loc.Loc = Job.Loc
													   LEFT JOIN nei.dbo.JobI ON Job.ID  = JobI.Job
												WHERE  Job.ID      =  ?
													   AND JobI.Type  =  1
													   AND JobI.Labor =  1
													   AND JobI.fDate >= ?
													   AND JobI.fDate <  ?
													   AND JobI.fDate >= '2017-03-30 00:00:00.000'
											;",array($_GET['ID'],$Overhead_Cost['Start'],$Overhead_Cost['End']));
											$Overhead_Costs[$key]['Labor'] = sqlsrv_fetch_array($resource)['Labor'];
											$resource = sqlsrv_query($NEI,"
												SELECT SUM([JOBLABOR].[TOTAL COST]) AS Labor
												FROM   nei.dbo.Job as Job
													   LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
												WHERE  Job.ID = ?
													   AND convert(date,[WEEK ENDING]) >= ?
													   AND convert(date,[WEEK ENDING]) < ?
													   AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
													   AND [JOBLABOR].[jobAlpha] <> '1111'
													   AND [JOBLABOR].[JobAlpha] <> '2222'
													   AND [JOBLABOR].[JobAlpha] <> '3333'
													   AND [JOBLABOR].[JobAlpha] <> '4444'
													   AND [JOBLABOR].[JobAlpha] <> '5555'
													   AND [JOBLABOR].[JobAlpha] <> '6666'
													   AND [JOBLABOR].[JobAlpha] <> '2222'
													   AND [JOBLABOR].[JobAlpha] <> '7777'
													   AND [JOBLABOR].[JobAlpha] <> '8888'
													   AND [JOBLABOR].[JobAlpha] <> '9999'
											;",array($_GET['ID'],$Overhead_Cost['Start'],$Overhead_Cost['End']));
											$Overhead_Costs[$key]['Labor'] += sqlsrv_fetch_array($resource)['Labor'];
											echo money_format('%(n',$Overhead_Costs[$key]['Labor']);
										?></td><?php }?>
									</tr>
									<tr>
										<td style='border:1px solid black;padding:3px;'>Materials</td>
										<?php
										foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
											//var_dump($Overhead_Cost);
											$resource = sqlsrv_query($NEI,"
												SELECT Sum(JobI.Amount) AS Materials
												FROM   nei.dbo.Loc
													   LEFT JOIN nei.dbo.Job  ON Loc.Loc = Job.Loc
													   LEFT JOIN nei.dbo.JobI ON Job.ID  = JobI.Job
												WHERE  Job.ID      =  ?
													   AND JobI.Type  =  1
													   AND (
															JobI.Labor =  0
															OR JobI.Labor IS NULL
															OR JobI.Labor = ' ')
													   AND JobI.fDate >= ?
													   AND JobI.fDate <  ?
											;",array($_GET['ID'],$Overhead_Cost['Start'],$Overhead_Cost['End']));
											$Overhead_Costs[$key]['Materials'] = sqlsrv_fetch_array($resource)['Materials'];
											echo money_format('%(n',$Overhead_Costs[$key]['Materials']);
										?></td><?php }?>
									</tr>
									<tr style='border-top:3px solid black;'>
										<td style='border:1px solid black;padding:3px;'>Net Income</td>
										<?php
										foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
											$Overhead_Costs[$key]['Net_Income'] = $Overhead_Costs[$key]['Revenue'] - ($Overhead_Costs[$key]['Labor'] + $Overhead_Costs[$key]['Materials']);
											echo money_format('%(n',$Overhead_Costs[$key]['Net_Income']);
										?></td><?php }?>
									</tr>
									<tr>
										<td style='border:1px solid black;padding:3px;'>Overhead Rate</td>
										<?php
										foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
											echo $Overhead_Cost['Rate'] . '%';
										?></td><?php }?>
									</tr>
									<tr>
										<td style='border:1px solid black;padding:3px;'>Overhead Cost</td>
										<?php
										foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
											$Overhead_Costs[$key]['Overhead_Cost'] = $Overhead_Costs[$key]['Revenue'] * ($Overhead_Costs[$key]['Rate'] / 100);
											echo money_format('%(n',$Overhead_Costs[$key]['Overhead_Cost']);
										?></td><?php }?>
									</tr>
									<tr>
										<td style='border:1px solid black;padding:3px;'>Profit</td>
										<?php
										foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
											$Overhead_Costs[$key]['Profit'] = $Overhead_Costs[$key]['Net_Income'] - $Overhead_Costs[$key]['Overhead_Cost'];
											echo money_format('%(n',$Overhead_Costs[$key]['Profit']);
										?></td><?php }?>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
