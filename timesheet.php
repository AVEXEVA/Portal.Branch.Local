<?php
session_start( [ 'read_and_close' => true ] );
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r);
    $r = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query(null,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Time']) && $My_Privileges['Time']['User_Privilege'] >= 4 && $My_Privileges['Time']['Group_Privilege'] >= 4 && $My_Privileges['Time']['Other_Privilege'] >= 0){$Privileged = TRUE;}
    $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "time_sheet.php"));
    if(!isset($My_Connection['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=time_sheet.php';</script></head></html><?php }
    else {

$Mechanic = is_numeric($_SESSION['User']) ? $_SESSION['User'] : -1;

if($Mechanic > 0){
    $Dispatch_Users = array(673,925,250,895,223,767,1137,465,371,569,418,772,254,763,273,19,232,17,1011,987,773,472,480,133,881,183,225,906);
    if(in_array($_SESSION['User'],$Dispatch_Users)){$Mechanic = addSlashes($_GET['Mechanic']);}

    $Call_Sign = "";
    $r = $database->query(null,"select * from Emp where ID = " . $Mechanic);
    $array = sqlsrv_fetch_array($r);
    $Call_Sign = $array['CallSign'];
    $Alias = $array['fFirst'][0] . $array['Last'];
    $Employee_ID = $array['fWork'];
    $Mechanic_Array = $array;
    while($array = sqlsrv_fetch_array($r)){}

    //GET TICKETS
    if($_GET['Start_Date'] > 0){$Start_Date = DateTime::createFromFormat('m/d/Y', $_GET['Start_Date'])->format("Y-m-d 00:00:00.000");}
    else{$Start_Date = DateTime::createFromFormat('m/d/Y',"1/1/2017")->format("Y-m-d 00:00:00.000");}

    if($_GET['End_Date'] > 0){$End_Date = DateTime::createFromFormat('m/d/Y', $_GET['End_Date'])->format("Y-m-d 23:59:59.999");}
    else{$End_Date = DateTime::createFromFormat('m/d/Y',"1/1/3000")->format("Y-m-d 23:59:59.999");}

    if(!isset($_GET['Location_Tag']) || $_GET['Location_Tag'] == "All" || $_GET['Location_Tag'] == ""){$Location_Tag = "' OR '1'='1";}
    else {$Location_Tag = addslashes($_GET['Location_Tag']);}

    if(!isset($_GET['Status']) || $_GET['Status'] == 'All' || $_GET['Status'] == ""){$Status = "' OR '1'='1";}
    else{$Status = $_GET['Status'];}

    $r = $database->query(null,"
        SELECT
            TicketO.*,
            Loc.Tag             AS Tag,
            Loc.Address         AS Address,
            Loc.City            AS City,
            Loc.State           AS State,
            Loc.Zip             AS Zip,
            Job.ID              AS Job_ID,
            Job.fDesc           AS Job_Description,
            OwnerWithRol.ID     AS Owner_ID,
            OwnerWithRol.Name   AS Customer,
            JobType.Type        AS Job_Type,
            Elev.Unit           AS Unit_Label,
            Elev.State          AS Unit_State,
            TickOStatus.Type    AS Status
        FROM
            (((((TicketO
            LEFT JOIN Loc           ON TicketO.LID = Loc.Loc)
            LEFT JOIN Job           ON TicketO.Job = Job.ID)
            LEFT JOIN OwnerWithRol  ON TicketO.Owner = OwnerWithRol.ID)
            LEFT JOIN JobType       ON Job.Type = JobType.ID)
            LEFT JOIN Elev          ON TicketO.LElev = Elev.ID)
            LEFT JOIN TickOStatus   ON TicketO.Assigned = TickOStatus.Ref
        WHERE
            TicketO.fWork='{$Employee_ID}'
            AND (   (EDate >= '{$Start_Date}'
                        AND EDate <= '{$End_Date}')
                    OR Assigned=3
                    OR Assigned=1)
            AND (Tag = '{$Location_Tag}')
            AND (Assigned = '{$Status}');");
    $Tickets = array();
    while($array = sqlsrv_fetch_array($r)){$Tickets[$array['ID']] = $array;}
    if($Status == "4" || $_GET['Status'] == "" || !isset($_GET['Status'])){
        $r = $database->query(null,"
            SELECT
                TicketD.*,
                Loc.Tag             AS Tag,
                Loc.Address         AS Address,
                Loc.City            AS City,
                Loc.State           AS State,
                Loc.Zip             AS Zip,
                Job.ID              AS Job_ID,
                Job.fDesc           AS Job_Description,
                OwnerWithRol.ID     AS Owner_ID,
                OwnerWithRol.Name   AS Customer,
                JobType.Type        AS Job_Type,
                Elev.Unit           AS Unit_Label,
                Elev.State          AS Unit_State
            FROM
                ((((TicketD
                LEFT JOIN Loc           ON TicketD.Loc = Loc.Loc)
                LEFT JOIN Job           ON TicketD.Job = Job.ID)
                LEFT JOIN OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID)
                LEFT JOIN JobType       ON Job.Type = JobType.ID)
                LEFT JOIN Elev          ON TicketD.Elev = Elev.ID
            WHERE
                TicketD.fWork='{$Employee_ID}'
                AND EDate >= '{$Start_Date}'
                AND EDate <= '{$End_Date}'
                AND (Tag = '{$Location_Tag}')
        ;");
        while($array = sqlsrv_fetch_array($r)){
            $Tickets[$array['ID']] = $array;
            $Tickets[$array['ID']]['Status'] = "Completed";}
    }
    $_SESSION['Tickets'] = $Tickets;
    if(strlen($_GET['Start_Date']) > 0 || strlen($_GET['End_Date']) > 0 || strlen($_GET['Location_Tag']) > 0 || $Status > 0){
        $_SESSION['Last_Search'] = "tickets.php?Dashboard=Mechanic&Start_Date=" . $_GET['Start_Date'] . "&End_Date=" . $_GET['End_Date'] . "&Location_Tag=" . $_GET['Location_Tag'] . "&Status=" . $_GET['Status'] . "&Show_Hours=" . $_GET['show_hours'] . "&Show_Tickets=" . $_GET['show_Tickets'];
    }
}?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<h4 style='text-align:center;background-color:whitesmoke;color:black;margin:0px;padding:10px;'>My Timesheet</h4>
			<style>
				#table-my-calendar thead th, #table-my-calendar tbody td {
					border:1px solid black;
				}
			</style>
			<table width="100%" class="" id="table-my-calendar" style='font-size:12px;'>
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
							<th>Expenses</th>
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
							$Employee_ID = $My_User['fWork'];
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
								echo date("m/d",strtotime($WeekOf));
							?></td><?php
							$Today = date('l');
							if($Today == 'Thursday'){$Thursday = date('Y-m-d');}
							elseif($Today == 'Friday'){$Thursday = date('Y-m-d', strtotime($Date . ' -1 days'));}
							elseif($Today == 'Saturday'){$Thursday = date('Y-m-d', strtotime($Date . ' -2 days'));}
							elseif($Today == 'Sunday'){$Thursday = date('Y-m-d', strtotime($Date . ' -3 days'));}
							elseif($Today == 'Monday'){$Thursday = date('Y-m-d', strtotime($Date . ' -4 days'));}
							elseif($Today == 'Tuesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -5 days'));}
							elseif($Today == 'Wednesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -6 days'));}
							$r = $database->query(null,"
								SELECT Sum(Total) as Summed
								FROM TicketD
								WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
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
							$r = $database->query(null,"
								SELECT Sum(Total) AS Summed
								FROM TicketD
								WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
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
							$r = $database->query(null,"
								SELECT Sum(Total) AS Summed
								FROM TicketD
								WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
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
							$r = $database->query(null,"
								SELECT Sum(Total) AS Summed
								FROM TicketD

								WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
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
							$r = $database->query(null,"
								SELECT Sum(Total) AS Summed
								FROM TicketD
								WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
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
							$r = $database->query(null,"
								SELECT Sum(Total) AS Summed
								FROM TicketD
								WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
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
							$r = $database->query(null,"
								SELECT Sum(Total) AS Summed
								FROM TicketD
								WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
							<td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
								echo sqlsrv_fetch_array($r)['Summed'];
							?></td>
							<td><?php
								$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
								echo sqlsrv_fetch_array($r)['Summed'];
							?></td>
							<td>$<?php
								$r = $database->query(null,"SELECT Sum(Zone) + Sum(Toll) + Sum(OtherE) AS Expenses FROM TicketD WHERE fWork = ? AND EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'",array($Employee_ID));
								echo sqlsrv_fetch_array($r)['Expenses'];
							?></td>
						</tr>
						<?php while($WeekOf > "2019-04-10 00:00:00.000"){?><tr style='cursor:pointer;' class="odd gradeX hoverGray">
							<?php $WeekOf = date('Y-m-d',strtotime($WeekOf . '-7 days')); ?>
							<td class='WeekOf' rel='<?php echo $WeekOf;?>' onClick="refresh_this(this);"><?php
								echo date("m/d",strtotime($WeekOf));
							?></td>
							<?php
							$Thursday = date('Y-m-d',strtotime($Thursday . '-7 days'));
								$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
							<td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php
								echo sqlsrv_fetch_array($r)['Summed'];
							?></td>
							<?php $Friday = date('Y-m-d',strtotime($Friday . '-7 days'));
								$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
							<td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php
								echo sqlsrv_fetch_array($r)['Summed'];
							?></td>
							<?php $Saturday = date('Y-m-d',strtotime($Saturday . '-7 days'));
								$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
							<td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php
								echo sqlsrv_fetch_array($r)['Summed'];
							?></td>
							<?php $Sunday = date('Y-m-d',strtotime($Sunday . '-7 days'));
								$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
							<td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php
								echo sqlsrv_fetch_array($r)['Summed'];
							?></td>
							<?php $Monday = date('Y-m-d',strtotime($Monday . '-7 days'));
								$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
							<td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php
								echo sqlsrv_fetch_array($r)['Summed'];
							?></td>
							<?php $Tuesday = date('Y-m-d',strtotime($Tuesday . '-7 days'));
								$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
							<td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php
								echo sqlsrv_fetch_array($r)['Summed'];
							?></td>
							<?php $Wednesday = date('Y-m-d',strtotime($Wednesday . '-7 days'));
								$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
							<td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
								echo sqlsrv_fetch_array($r)['Summed'];
							?></td>
							<td><?php
								$r = $database->query(null,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
								echo sqlsrv_fetch_array($r)['Summed'];
							?></td>
							<td>$<?php
								$r = $database->query(null,"SELECT Sum(Zone) + Sum(Toll) + Sum(OtherE) AS Expenses FROM TicketD WHERE fWork = ? AND EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'",array($Employee_ID));
								echo sqlsrv_fetch_array($r)['Expenses'];
							?></td>
						</tr><?php }?>
					</tbody>
				</table>
			</div>
		</div>
    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <!-- Morris Charts JavaScript -->
    <!--<script src="https://www.nouveauelevator.com/vendor/raphael/raphael.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.php"></script>-->

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
    

    <script>
        function refresh_this(link){document.location.href='time_sheet2.php?Date=' + $(link).attr('rel') + '&Type=' + $(link).attr('class') + "&Mechanic=<?php echo $Mechanic;?>";}
    </script>
    <script>
        $(document).ready(function() {
            $('#dataTables-example').DataTable({responsive: true});
            $('#dataTables-example2').DataTable({responsive: true});
            $(".sorting_asc").click();
        });
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=time_sheet.php';</script></head></html><?php }?>
