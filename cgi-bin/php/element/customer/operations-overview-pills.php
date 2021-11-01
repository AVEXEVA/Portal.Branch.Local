 <?php 
session_start();
require('../../../php/index.php');

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
<div class='tab-pane fade in active' id='operations-overview-pills'>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-12' >
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Worker Feed</h3></div>
								<div class="panel-body white-background BankGothic shadow">
									<style>
										div#Worker_Feed>div {
											margin:10px;
											padding:15px;
											background-color:#252525;
											color:white;
											border-radius:10px;
											font-weight:bold;
											box-shadow:3px 3px 5px 6px #ccc;
											-moz-box-shadow:3px 3px 5px 6px #ccc;
											-webkit-box-shadow:3px 3px 5px 6px #ccc;
										}
									</style>
									<div id='Worker_Feed'>
										<?php 
										$r = sqlsrv_query($NEI,"
											SELECT TicketO.*,
												   TicketO.ID AS TicketID,
												   Emp.*,
												   Emp.fFirst AS First_Name,
												   Emp.Last   AS Last_Name
											FROM   nei.dbo.TicketO 
												   LEFT JOIN nei.dbo.Loc ON TicketO.LID   = Loc.Loc
												   LEFT JOIN nei.dbo.Emp ON TicketO.fWork = Emp.fWork
											WHERE  Loc.Owner                = ?
										;",array($_GET['ID']));
										if($r){
											$Triggered = FALSE;
											while($Ticket = sqlsrv_fetch_array($r)){
												if( ($Ticket['TimeRoute']       != '1899-12-30 00:00:00.000' 
														&& $Ticket['TimeRoute'] != '') 
													&& ($Ticket['TimeSite']     == '1899-12-30 00:00:00.000' 
														|| $Ticket['TimeSite']  == '')
													){
														$Triggered = TRUE;
														?><div><u><?php echo proper($Ticket['fFirst'] . " " . $Ticket['Last']);?></u> is en route at <?php echo date('h:i A',strtotime(substr($Ticket['TimeRoute'],10,99)));?> working on Ticket #<a style='color:white;' href="ticket.php?ID=<?php echo $Ticket['TicketID'];?>"><?php echo $Ticket['TicketID'];?></a></div><?php }
												elseif($Ticket['TimeSite']    != '1899-12-30 00:00:00.000' 
													   && $Ticket['TimeSite'] != '' 
													   && ($Ticket['TimeComp']    == '1899-12-30 00:00:00.000' 
														   || $Ticket['TimeComp'] == '')
													  ){
														$Triggered = TRUE;
														?><div><u><?php echo proper($Ticket['fFirst'] . " " . $Ticket['Last']);?></u> is on site at <?php echo date('h:i A',strtotime(substr($Ticket['TimeSite'],10,99)));?> working on Ticket #<a style='color:white;' href="ticket.php?ID=<?php echo $Ticket['TicketID'];?>"><?php echo $Ticket['TicketID'];?></a></div><?php }
												else {}
											}
											if(!$Triggered){?><h3>No Recent Worker Activity</h3><?php }
										}

										else {?><h3>No Mechanics En Route / On Site</h3><?php }
										?>
									</div>
								</div>
							</div>
						</div>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Service Call Feed</h3></div>
								<div class="panel-body white-background BankGothic shadow">
									<div id='Worker_Feed' style=''>
										<?php 
										$r = sqlsrv_query($NEI,"
											SELECT 
												TicketO.*,
												TicketO.ID       AS TicketID,
												Loc.*,
												Emp.*,
												TickOStatus.Type AS AssignedType,
												Elev.*,
												Emp.fFirst AS First_Name,
												Emp.Last AS Last_Name
											FROM 
												nei.dbo.TicketO 
												LEFT JOIN nei.dbo.Loc         ON TicketO.LID      = Loc.Loc
												LEFT JOIN nei.dbo.Emp         ON TicketO.fWork    = Emp.fWork
												LEFT JOIN nei.dbo.TickOStatus ON TicketO.Assigned = TickOStatus.Ref
												LEFT JOIN nei.dbo.Elev 		  ON Elev.ID 		  = TicketO.LElev
											WHERE 
												Loc.Owner = ?
												AND TicketO.Level = 1
										",array($_GET['ID']));
										if($r){
											$Triggered = FALSE;
											while($Ticket = sqlsrv_fetch_array($r)){?><div style=''><?php 
												$Triggered = TRUE;
												if($Ticket['First_Name'] == ''){
													?>Service Call <?php echo $Ticket['TicketID'];?> was created at <?php echo date('h:i A',strtotime(substr($Ticket['CDate'],10,99)));?> for Unit: <?php echo strlen($Ticket['State']) > 0 ? $Ticket['State'] : $Ticket['Unit'];?>.
												<?php } else {
													?>Service Call <?php echo $Ticket['TicketID'];?> has been assigned to <?php echo proper($Ticket['First_Name'] . " " . $Ticket['Last_Name']);?> for Unit: <?php echo strlen($Ticket['State']) > 0 ? $Ticket['State'] : $Ticket['Unit'];?>. The current status is <?php echo $Ticket['AssignedType'];?>.<?php	
												}
											?></div><?php }
											if(!$Triggered){
												?><td colspan='7'><h3>No Recent Service Calls</h3></td><?php
											}
										} 
										else {?><h3>No Mechanics En Route / On Site</h3><?php }
										?>
									</div>
								</div>
							</div>
						</div>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Job Hours</h3></div>
								<div class="panel-body white-background BankGothic shadow" style='height:500px;overflow-y:scroll;'>
									<style>
										table#Customer_Hours tbody tr td, table#Customer_Hours thead tr th {
											border:1px solid black;
											padding:3px;
										}
									</style>
									<table width="100%" class="" id="Customer_Hours">
										<thead>
											<tr>
												<th>Weeks</th>
												<th>Thu</th>
												<th>Fri</th>
												<th>Sat</th>
												<th>Sun</th>
												<th>Mon</th>
												<th>Tue</th>
												<th>Wed</th>
												<th>Total</th>
											</tr>
										</thead>
										<style>
										.hoverGray:hover {
											background-color:#dfdfdf !important;
										}
										</style>
										<tbody>
											<tr style='cursor:pointer;' class="odd gradeX hoverGray">
												<?php $Today = date('l');
												$Date = date('Y-m-d');
												if($Today == 'Thursday'){$WeekOf = date('Y-m-d');}
												elseif($Today == 'Friday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -1 days'));}
												elseif($Today == 'Saturday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -2 days'));}
												elseif($Today == 'Sunday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -3 days'));}
												elseif($Today == 'Monday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -4 days'));}
												elseif($Today == 'Tuesday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -5 days'));}
												elseif($Today == 'Wednesday'){$WeekOf = date('Y-m-d', strtotime($Date . ' -6 days'));}
												$WeekOf = date('Y-m-d',strtotime($WeekOf . ' +6 days'));?>
												<td class='WeekOf' rel='<?php echo $WeekOf;?>' onClick="refresh_this(this);"><?php
													echo $WeekOf;
												?></td><?php 
												$Today = date('l');
												if($Today == 'Thursday'){$Thursday = date('Y-m-d');}
												elseif($Today == 'Friday'){$Thursday = date('Y-m-d', strtotime($Date . ' -1 days'));}
												elseif($Today == 'Saturday'){$Thursday = date('Y-m-d', strtotime($Date . ' -2 days'));}
												elseif($Today == 'Sunday'){$Thursday = date('Y-m-d', strtotime($Date . ' -3 days'));}
												elseif($Today == 'Monday'){$Thursday = date('Y-m-d', strtotime($Date . ' -4 days'));}
												elseif($Today == 'Tuesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -5 days'));}
												elseif($Today == 'Wednesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -6 days'));}
												$r = sqlsrv_query($NEI,"
													SELECT Sum(Total) as Summed 
													FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID 
													WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
												<td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php 
												if($Today == 'Friday'){$Friday = date('Y-m-d');}
												elseif($Today == 'Saturday'){$Friday = date('Y-m-d', strtotime($Date . ' -1 days'));}
												elseif($Today == 'Sunday'){$Friday = date('Y-m-d', strtotime($Date . ' -2 days'));}
												elseif($Today == 'Monday'){$Friday = date('Y-m-d', strtotime($Date . ' -3 days'));}
												elseif($Today == 'Tuesday'){$Friday = date('Y-m-d', strtotime($Date . ' -4 days'));}
												elseif($Today == 'Wednesday'){$Friday = date('Y-m-d', strtotime($Date . ' -5 days'));}
												elseif($Today == 'Thursday'){$Friday = date('Y-m-d', strtotime($Date . ' +1 days'));}
												$r = sqlsrv_query($NEI,"
													SELECT Sum(Total) AS Summed 
													FROM nei.dbo.TicketD
													WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
												<td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php
												if($Today == 'Satuday'){$Saturday = date('Y-m-d');}
												elseif($Today == 'Sunday'){$Saturday = date('Y-m-d', strtotime($Date . ' -1 days'));}
												elseif($Today == 'Monday'){$Saturday = date('Y-m-d', strtotime($Date . ' -2 days'));}
												elseif($Today == 'Tuesday'){$Saturday = date('Y-m-d', strtotime($Date . ' -3 days'));}
												elseif($Today == 'Wednesday'){$Saturday = date('Y-m-d', strtotime($Date . ' -4 days'));}
												elseif($Today == 'Thursday'){$Saturday = date('Y-m-d', strtotime($Date . ' +2 days'));}
												elseif($Today == 'Friday'){$Saturday = date('Y-m-d', strtotime($Date . ' +1 days'));}
												$r = sqlsrv_query($NEI,"
													SELECT Sum(Total) AS Summed
													FROM nei.dbo.TicketD
													WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
												<td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php 
												if($Today == 'Sunday'){$Sunday = date('Y-m-d');}
												elseif($Today == 'Monday'){$Sunday = date('Y-m-d', strtotime($Date . ' -1 days'));}
												elseif($Today == 'Tuesday'){$Sunday = date('Y-m-d', strtotime($Date . ' -2 days'));}
												elseif($Today == 'Wednesday'){$Sunday = date('Y-m-d', strtotime($Date . ' -3 days'));}
												elseif($Today == 'Thursday'){$Sunday = date('Y-m-d', strtotime($Date . ' +3 days'));}
												elseif($Today == 'Friday'){$Sunday = date('Y-m-d', strtotime($Date . ' +2 days'));}
												elseif($Today == 'Saturday'){$Sunday = date('Y-m-d', strtotime($Date . ' +1 days'));}
												$r = sqlsrv_query($NEI,"
													SELECT Sum(Total) AS Summed 
													FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID 
													WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
												<td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php
												if($Today == 'Monday'){$Monday = date('Y-m-d');}
												elseif($Today == 'Tuesday'){$Monday = date('Y-m-d', strtotime($Date . ' -1 days'));}
												elseif($Today == 'Wednesday'){$Monday = date('Y-m-d', strtotime($Date . ' -2 days'));}
												elseif($Today == 'Thursday'){$Monday = date('Y-m-d', strtotime($Date . ' +4 days'));}
												elseif($Today == 'Friday'){$Monday = date('Y-m-d', strtotime($Date . ' +3 days'));}
												elseif($Today == 'Saturday'){$Monday = date('Y-m-d', strtotime($Date . ' +2 days'));}
												elseif($Today == 'Sunday'){$Monday = date('Y-m-d', strtotime($Date . ' +1 days'));}
												$r = sqlsrv_query($NEI,"
													SELECT Sum(Total) AS Summed
													FROM nei.dbo.TicketD
													WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
												<td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php 
												if($Today == 'Tuesday'){$Tuesday = date('Y-m-d');}
												elseif($Today == 'Wednesday'){$Tuesday = date('Y-m-d', strtotime($Date . ' -1 days'));}
												elseif($Today == 'Thursday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +5 days'));}
												elseif($Today == 'Friday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +4 days'));}
												elseif($Today == 'Saturday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +3 days'));}
												elseif($Today == 'Sunday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +2 days'));}
												elseif($Today == 'Monday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +1 days'));}
												$r = sqlsrv_query($NEI,"
													SELECT Sum(Total) AS Summed
													FROM nei.dbo.TicketD
													WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
												<td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php 
												if($Today == 'Wednesday'){$Wednesday = date('Y-m-d');}	
												elseif($Today == 'Thursday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +6 days'));}
												elseif($Today == 'Friday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +5 days'));}
												elseif($Today == 'Saturday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +4 days'));}
												elseif($Today == 'Sunday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +3 days'));}
												elseif($Today == 'Monday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +2 days'));}
												elseif($Today == 'Tuesday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +1 days'));}
												$r = sqlsrv_query($NEI,"
													SELECT Sum(Total) AS Summed
													FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
													WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
												<td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<td><?php
													$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
											</tr>
											<?php while($WeekOf > "2017-03-08 00:00:00.000"){?><tr style='cursor:pointer;' class="odd gradeX hoverGray">
												<?php $WeekOf = date('Y-m-d',strtotime($WeekOf . '-7 days')); ?>
												<td class='WeekOf' rel='<?php echo $WeekOf;?>' onClick="refresh_this(this);"><?php 
													echo $WeekOf;
												?></td>
												<?php 
												$Thursday = date('Y-m-d',strtotime($Thursday . '-7 days'));
													$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
												<td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php 
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php $Friday = date('Y-m-d',strtotime($Friday . '-7 days'));
													$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
												<td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php 
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php $Saturday = date('Y-m-d',strtotime($Saturday . '-7 days'));
													$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
												<td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php 
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php $Sunday = date('Y-m-d',strtotime($Sunday . '-7 days'));
													$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
												<td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php 
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php $Monday = date('Y-m-d',strtotime($Monday . '-7 days'));
													$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
												<td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php 
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php $Tuesday = date('Y-m-d',strtotime($Tuesday . '-7 days'));
													$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
												<td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php 
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<?php $Wednesday = date('Y-m-d',strtotime($Wednesday . '-7 days'));
													$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
												<td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
												<td><?php
													$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM nei.dbo.TicketD LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID WHERE Job.Owner='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
													echo sqlsrv_fetch_array($r)['Summed'];
												?></td>
											</tr><?php }?>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class='col-md-6'>
					<div class='row'>
						<div class='col-md-12'>
							<div class="panel panel-primary">
								<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Job Overview</h3></div>
								<div class="panel-body BankGothic">
									<div class='row'>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-suitcase fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Owner = ?
																		   AND Job.Type <> 9 
																		   AND Job.Type <> 12
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Total Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-cogs fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Owner = ?
																		   AND Job.Type = 0
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Maintenance Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-cogs fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Owner = ?
																		   AND Job.Type = 2
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Modernization Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-cogs fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Owner = ?
																		   AND Job.Type = 6
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Repair Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-warning fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Owner = ?
																		   AND Job.Type = 8
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Testing Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-warning fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Owner      = ?
																		   AND Job.Type = 19
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Violation Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<div class="col-lg-4 col-md-2">
											<div class="panel panel-primary">
												<div class="panel-heading">
													<div class="row">
														<div class="col-xs-3">
															<i class="fa fa-cogs fa-3x"></i>
														</div>
														<div class="col-xs-9 text-right">
															<div class="col-xs-9 text-right">
															<div class="medium"><?php 
																$Year = date("Y-01-01 00:00:00.000");
																$r = sqlsrv_query($NEI,"
																	SELECT Count(Job.ID) AS Jobs
																	FROM   nei.dbo.Job 
																	WHERE  Job.Owner = ?
																		   AND Job.Type = 4
																;",array($_GET['ID']));
																echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;?>
															</div>
															<div>Other Jobs</div></div>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div class='col-md-12' style=''>
									<div class="panel panel-primary">
										<div class="panel-heading"><h3>Active Jobs</h3></div>
										<div class='panel-body white-background shadow'>
											<table id='Table_Active_Jobs' class='display' cellspacing='0' width='100%'>
												<thead>
													<th>ID</th>
													<th>Name</th>
													<th>Type</th>
												</thead>
											</table>	
										</div>
									</div>
									<script>
									var Table_Active_Jobs = $('#Table_Active_Jobs').DataTable( {
										"ajax": "cgi-bin/php/reports/Active_Jobs_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
										"columns": [
											{ "data": "ID"},
											{ "data": "Name" },
											{ "data": "Type"}
										],
										"order": [[1, 'asc']],
										"language":{
											"loadingRecords":""
										},
										"initComplete":function(){
											$("#loading-pills").removeClass("active");
											$("#operations-pills").addClass('active');
										}
									} );
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
	$("#loading-sub-pills").removeClass("active");
	$("#operations-overview-pills").addClass('active');
});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>