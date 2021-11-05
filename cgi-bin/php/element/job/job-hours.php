<?php 
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Texas'){
    	$My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($NEI,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Job']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
			$a = sqlsrv_query($NEI,"
				SELECT Job.Loc
				FROM Job
				WHERE Job.ID = ?
			;",array($_GET['ID']));
			$loc = sqlsrv_fetch_array($a)['Loc'];
            $r = sqlsrv_query(  $NEI,"
				SELECT *
				FROM 		Job
				LEFT JOIN 	TicketO ON Job.ID = TicketO.Job
				WHERE 		TicketO.LID= ?
					AND 	TicketO.fWork= ?
			;",array($loc,$My_User['fWork']));
            $r2 = sqlsrv_query( $NEI,"
				SELECT *
				FROM 		Job
				LEFT JOIN 	TicketD ON Job.ID = TicketD.Job
				WHERE 		TicketD.Loc= ?
							AND TicketD.fWork= ? 
			;",array($loc,$My_User['fWork']));
			$r3 = sqlsrv_query( $NEI,"
				SELECT *
				FROM 		Job
				LEFT JOIN 	TicketDArchive ON Job.ID = TicketDArchive.Loc
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
                LEFT JOIN Loc           ON Job.Loc      = Loc.Loc
                LEFT JOIN Zone          ON Loc.Zone     = Zone.ID
                LEFT JOIN JobType       ON Job.Type     = JobType.ID
                LEFT JOIN OwnerWithRol  ON Job.Owner    = OwnerWithRol.ID
                LEFT JOIN Elev          ON Job.Elev     = Elev.ID
                LEFT JOIN Route         ON Loc.Route    = Route.ID
                LEFT JOIN Emp           ON Emp.fWork    = Route.Mech
				LEFT JOIN Violation     ON Job.ID       = Violation.Job
            WHERE
                Job.ID = ?
        ;",array($_GET['ID']));
        $Job = sqlsrv_fetch_array($r);?>
<style>
	table#Job_Hours tbody tr td, table#Job_Hours thead tr th {
		border:1px solid black;
		padding:3px;
	}
</style>
<div class='row shadower' style='border-bottom:3px;padding-top:10px;padding-bottom:10px;'>
	
	<?php 
		$r = sqlsrv_query($NEI,"
			SELECT Sum(Tickets.Regular)		AS Regular,
				   Sum(Tickets.Overtime) 	AS Overtime,
				   Sum(Tickets.Doubletime) 	AS Doubletime,
				   Sum(Tickets.Total) 		AS Total
			FROM 
			(
				(
					SELECT TicketD.Reg 		AS Regular,
						   TicketD.OT  		AS Overtime,
						   TicketD.DT  		AS Doubletime,
						   TicketD.Total 	AS Total
					FROM   TicketD 
					WHERE  TicketD.Job = ?
				)
				UNION ALL
				(
					SELECT TicketDArchive.Reg 		AS Regular,
						   TicketDArchive.OT  		AS Overtime,
						   TicketDArchive.DT  		AS Doubletime,
						   TicketDArchive.Total 	AS Total
					FROM   TicketDArchive
					WHERE  TicketDArchive.Job = ?
				)
			) AS Tickets
		;",array($_GET['ID'],$_GET['ID']));
		$Sums = sqlsrv_fetch_array($r);
	?>
	<div class='col-xs-4'><?php $Icons->Hours(1);?> Regular:</div>
	<div class='col-xs-8'><?php echo strlen($Sums['Regular']) ? $Sums['Regular'] : "&nbsp;";;?></div>
	<div class='col-xs-4'><?php $Icons->Blank(1);?> Overtime:</div>
	<div class='col-xs-8'><?php echo strlen($Sums['Overtime']) ? $Sums['Overtime'] : "&nbsp;";;?></div>
	<div class='col-xs-4'><?php $Icons->Blank(1);?> Doubletime:</div>
	<div class='col-xs-8'><?php echo strlen($Sums['Doubletime']) ? $Sums['Doubletime'] : "&nbsp;";;?></div>
	<div class='col-xs-4'><?php $Icons->Blank(1);?> Total:</div>
	<div class='col-xs-8'><?php echo strlen($Sums['Total']) ? $Sums['Total'] : "&nbsp;";;?></div>
	<div class='col-xs-4'><?php $Icons->Blank(1);?> Budgeted:</div>
	<div class='col-xs-8'><?php echo strlen($Job['Job_Budgeted_Hours']) ? $Job['Job_Budgeted_Hours'] : "&nbsp;";;?></div>
</div>
<table width="100%" class="" id="Job_Hours">
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
				FROM TicketD 
				WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
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
				FROM TicketD
				WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
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
				FROM TicketD
				WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
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
				FROM TicketD 
				WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
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
				FROM TicketD
				WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
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
				FROM TicketD
				WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
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
				FROM TicketD 
				WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
			<td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
				echo sqlsrv_fetch_array($r)['Summed'];
			?></td>
			<td><?php
				$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
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
				$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
			<td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php 
				echo sqlsrv_fetch_array($r)['Summed'];
			?></td>
			<?php $Friday = date('Y-m-d',strtotime($Friday . '-7 days'));
				$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
			<td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php 
				echo sqlsrv_fetch_array($r)['Summed'];
			?></td>
			<?php $Saturday = date('Y-m-d',strtotime($Saturday . '-7 days'));
				$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
			<td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php 
				echo sqlsrv_fetch_array($r)['Summed'];
			?></td>
			<?php $Sunday = date('Y-m-d',strtotime($Sunday . '-7 days'));
				$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
			<td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php 
				echo sqlsrv_fetch_array($r)['Summed'];
			?></td>
			<?php $Monday = date('Y-m-d',strtotime($Monday . '-7 days'));
				$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
			<td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php 
				echo sqlsrv_fetch_array($r)['Summed'];
			?></td>
			<?php $Tuesday = date('Y-m-d',strtotime($Tuesday . '-7 days'));
				$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
			<td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php 
				echo sqlsrv_fetch_array($r)['Summed'];
			?></td>
			<?php $Wednesday = date('Y-m-d',strtotime($Wednesday . '-7 days'));
				$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
			<td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
				echo sqlsrv_fetch_array($r)['Summed'];
			?></td>
			<td><?php
				$r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE Job='" . $_GET['ID'] . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
				echo sqlsrv_fetch_array($r)['Summed'];
			?></td>
		</tr><?php }?>
	</tbody>
</table>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>