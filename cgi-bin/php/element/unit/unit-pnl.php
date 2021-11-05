<?php
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Texas'){
        sqlsrv_query($NEI,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($NEI,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
			$r = sqlsrv_query($NEI,"
				SELECT Elev.Loc AS Location_ID
				FROM   Elev
				WHERE  Elev.ID = ?
			;",array($_GET['ID'] ));
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
            $r = sqlsrv_query($NEI,"
			SELECT Tickets.*
			FROM
			(
				(
					SELECT TicketO.ID
					FROM   TicketO
						   LEFT JOIN Loc  ON TicketO.LID   = Loc.Loc
						   LEFT JOIN Elev ON Loc.Loc       = Elev.Loc
						   LEFT JOIN Emp  ON TicketO.fWork = Emp.fWork
					WHERE  Emp.ID      = ?
						   AND Loc.Loc = ?
				)
				UNION ALL
				(
					SELECT TicketD.ID
					FROM   TicketD
						   LEFT JOIN Loc  ON TicketD.Loc   = Loc.Loc
						   LEFT JOIN Elev ON Loc.Loc       = Elev.Loc
						   LEFT JOIN Emp  ON TicketD.fWork = Emp.fWork
					WHERE  Emp.ID      = ?
						   AND Loc.Loc = ?
				)

			) AS Tickets
           	;", array($_SESSION['User'], $Location_ID, $_SESSION['User'], $Location_ID, $_SESSION['User'], $Location_ID));
            $r = sqlsrv_fetch_array($r);
            $Privileged = is_array($r) ? TRUE : FALSE;
        }
    }
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {

        if(count($_POST) > 0){
            fixArrayKey($_POST);
            foreach($_POST as $key=>$value){
				if($key == 'Price'){continue;}
				if($key == 'Type'){continue;}
                sqlsrv_query($NEI,"
                    UPDATE ElevTItem
                    SET    ElevTItem.Value     = ?
                    WHERE  ElevTItem.Elev      = ?
                           AND ElevTItem.ElevT = 1
                           AND ElevTItem.fDesc = ?
                ;",array($value,$_GET['ID'],$key));
            }
			if(isset($_POST['Price'])){
				sqlsrv_query($NEI,"
					UPDATE Elev
					SET    Elev.Price = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Price'],$_GET['ID']));
			}
			if(isset($_POST['Type'])){
				sqlsrv_query($NEI,"
					UPDATE Elev
					SET    Elev.Type = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Type'],$_GET['ID']));
			}
        }
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
                LEFT JOIN Loc           ON Elev.Loc = Loc.Loc
                LEFT JOIN Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN Route ON Loc.Route = Route.ID
                LEFT JOIN Emp ON Route.Mech = Emp.fWork
            WHERE
                Elev.ID = ?
		;",array($_GET['ID']));
        $Unit = sqlsrv_fetch_array($r);
        $unit = $Unit;
        $data = $Unit;
        $r2 = sqlsrv_query($NEI,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
?>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class='row'>
				<div class='col-md-12'>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Territory();?>Total Profit</h3></div>
						<div class='panel-body white-background BankGothic shadow' style='height:300px;'>
							<div class="flot-chart" style='height:300px;'><div class="flot-chart-content" id="flot-placeholder-profit" style='height:300px;'></div></div>
						</div>
					</div>
				</div>
				<?php require('../../../js/chart/location_profit.php');?>
				<div class='col-md-12' style=''>
					<div class="panel panel-primary">
						<div class="panel-heading"><h3>Yearly Profit</h3></div>
						<div class='panel-body white-background shadow'>
							<table id="Table_Profit" class="display" cellspacing='0' width='100%'>
								<?php
								$resource = sqlsrv_query($NEI,"
									SELECT   Overhead_Cost.*
									FROM     Overhead_Cost
									ORDER BY Overhead_Cost.Type ASC
								;");
								$Overhead_Costs = array();
								if($resource){while($Overhead_Cost = sqlsrv_fetch_array($resource)){
								if($Overhead_Cost['Type'] == '2012'){continue;}
								if($Overhead_Cost['Type'] == '2013'){continue;}
								if($Overhead_Cost['Type'] == '2014'){continue;}
								if($Overhead_Cost['Type'] == '2015'){continue;}
								if($Overhead_Cost['Type'] == '7 Year'){continue;}
								$Overhead_Costs[] = $Overhead_Cost;}}?>
								<thead style='border-left:3px solid black;border-right:3px solid black;border-top:3px solid black;'>
									<th></th>
									<?php
										foreach($Overhead_Costs as $Overhead_Cost){
											?><th style='border:1px solid black;padding:3px;'><?php echo $Overhead_Cost['Type'];?></th><?php
										}
									?>
								</thead>
								<tbody style='border:3px solid black;color:white !important;'>
									<tr>
										<td style='border:1px solid black;padding:3px;'>Revenue</td>
										<?php
										foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
											$resource = sqlsrv_query($NEI,"
												SELECT Sum(Invoice.Amount) AS Revenue
												FROM   Invoice
													   LEFT JOIN Loc ON Invoice.Loc = Loc.Loc
												WHERE  Loc.Loc = ?
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
												FROM   Loc
													   LEFT JOIN Job  ON Loc.Loc = Job.Loc
													   LEFT JOIN JobI ON Job.ID  = JobI.Job
												WHERE  Job.Loc      =  ?
													   AND JobI.Type  =  1
													   AND JobI.Labor =  1
													   AND JobI.fDate >= ?
													   AND JobI.fDate <  ?
													   AND JobI.fDate >= '2017-03-30 00:00:00.000'
											;",array($_GET['ID'],$Overhead_Cost['Start'],$Overhead_Cost['End']));
											$Overhead_Costs[$key]['Labor'] = sqlsrv_fetch_array($resource)['Labor'];
											$resource = sqlsrv_query($NEI,"
												SELECT SUM([JOBLABOR].[TOTAL COST]) AS Labor
												FROM   Job as Job
													   LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
												WHERE  Job.Loc = ?
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
												FROM   Loc
													   LEFT JOIN Job  ON Loc.Loc = Job.Loc
													   LEFT JOIN JobI ON Job.ID  = JobI.Job
												WHERE  Job.Loc      =  ?
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
