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
        if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "customer-pnl.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    OwnerWithRol.ID      AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Address AS Customer_Street,
                    OwnerWithRol.City    AS Customer_City,
                    OwnerWithRol.State   AS Customer_State,
                    OwnerWithRol.Zip     AS Customer_Zip,
                    OwnerWithRol.Status  AS Customer_Status,
                    OwnerWithRol.Website AS Customer_Website
            FROM    nei.dbo.OwnerWithRol
            WHERE   OwnerWithRol.ID = ?
        ;",array($_GET['ID']));
        $Customer = sqlsrv_fetch_array($r);?>
			<div class="panel panel-primary">
				<!--<div class="panel-heading"><h3>Yearly Profit</h3></div>-->
				<div class='panel-body'>
					<table id="Table_Profit" class="display" cellspacing='0' width='100%' style='font-size:10px;'>
						<?php 
						$resource = sqlsrv_query($NEI,"
							SELECT   Overhead_Cost.*
							FROM     Portal.dbo.Overhead_Cost
							ORDER BY Overhead_Cost.Type ASC
						;");
						$Overhead_Costs = array();
						if($resource){while($Overhead_Cost = sqlsrv_fetch_array($resource)){
							if($Overhead_Cost['Type'] == '2012'){continue;}
							if($Overhead_Cost['Type'] == '2013'){continue;}
							if($Overhead_Cost['Type'] == '2014'){continue;}
							if($Overhead_Cost['Type'] == '2015'){continue;}
							if($Overhead_Cost['Type'] == '7 Year'){continue;}
							$Overhead_Costs[] = $Overhead_Cost;
						}}?>
						<thead style='border-left:3px solid black;border-right:3px solid black;border-top:3px solid black;'>
							<th></th>
							<?php
								foreach($Overhead_Costs as $Overhead_Cost){
									?><th style='border:1px solid black;padding:3px;'><?php echo $Overhead_Cost['Type'];?></th><?php
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
										WHERE  Loc.Owner = ?
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
										WHERE  Job.Owner      =  ?
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
										WHERE  Job.Owner = ?
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
										WHERE  Job.Owner      =  ?
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


			
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>