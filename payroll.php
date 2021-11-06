<?php 
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = $database->query(null,"
		SELECT * 
		FROM   Privilege 
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID'])){?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "payroll.php"));

$Mechanic = is_numeric($_SESSION['User']) ? $_SESSION['User'] : -1;

if($Mechanic > 0){
    $Call_Sign = "";
    $r = $database->query(null,"select * from Emp where ID = " . $_SESSION['User']);
    $array = sqlsrv_fetch_array($r);
    $Call_Sign = $array['CallSign'];
    $Alias = $array['fFirst'][0] . $array['Last'];
    $Employee_ID = $array['fWork'];
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
    //$prepared = odbc_prepare($c,"select TicketO.*, Loc.Tag as Tag, Loc.Address as Address, Loc.City as City, Loc.State as State, Loc.Zip as Zip, Job.ID as Job_ID, Job.fDesc as Job_Description, OwnerWithRol.ID as Owner_ID, OwnerWithRol.Name as Customer, JobType.Type as Job_Type, Elev.Unit as Unit_Label, Elev.State as Unit_State, TickOStatus.Type as Status from (((((TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketO.Job = Job.ID) LEFT JOIN nei.dbo.OwnerWithRol ON TicketO.Owner = OwnerWithRol.ID) LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID) LEFT JOIN nei.dbo.Elev ON TicketO.LElev = Elev.ID) LEFT JOIN nei.dbo.TickOStatus ON TicketO.Assigned = TickOStatus.Ref where TicketO.DWork='" . $Call_Sign . "' AND CDate >= '" . $Start_Date . "' AND EDate <= '" . $End_Date . "' AND Loc.Loc = ?");
    //$r = odbc_exec($prepared,$Location_Tag);

    $r = $database->query(null,"select TicketO.*, Loc.Tag as Tag, Loc.Address as Address, Loc.City as City, Loc.State as State, Loc.Zip as Zip, Job.ID as Job_ID, Job.fDesc as Job_Description, OwnerWithRol.ID as Owner_ID, OwnerWithRol.Name as Customer, JobType.Type as Job_Type, Elev.Unit as Unit_Label, Elev.State as Unit_State, TickOStatus.Type as Status from (((((TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketO.Job = Job.ID) LEFT JOIN nei.dbo.OwnerWithRol ON TicketO.Owner = OwnerWithRol.ID) LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID) LEFT JOIN nei.dbo.Elev ON TicketO.LElev = Elev.ID) LEFT JOIN nei.dbo.TickOStatus ON TicketO.Assigned = TickOStatus.Ref where TicketO.fWork='" . $Employee_ID . "' AND ((EDate >= '" . $Start_Date . "' AND EDate <= '" . $End_Date . "') OR Assigned=3 OR Assigned=1) AND (Tag = '" . $Location_Tag . "') AND (Assigned = '" . $Status .  "');");
    $Tickets = array();
    while($array = sqlsrv_fetch_array($r)){
        $Tickets[$array['ID']] = $array;
    }
    
    if($Status == "4" || $_GET['Status'] == "" || !isset($_GET['Status'])){
        $r = $database->query(null,"select TicketD.*, Loc.Tag as Tag, Loc.Address as Address, Loc.City as City, Loc.State as State, Loc.Zip as Zip, Job.ID as Job_ID, Job.fDesc as Job_Description, OwnerWithRol.ID as Owner_ID, OwnerWithRol.Name as Customer, JobType.Type as Job_Type, Elev.Unit as Unit_Label, Elev.State as Unit_State from ((((TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID) LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner = OwnerWithRol.ID) LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID) LEFT JOIN nei.dbo.Elev ON TicketD.Elev = Elev.ID where TicketD.fWork='" . $Employee_ID . "' AND EDate >= '" . $Start_Date . "' AND EDate <= '" . $End_Date . "' AND (Tag = '" . $Location_Tag . "');");
        while($array = sqlsrv_fetch_array($r)){
            $Tickets[$array['ID']] = $array;
            $Tickets[$array['ID']]['Status'] = "Completed";
        }
    }
    $_SESSION['Tickets'] = $Tickets;
    if(strlen($_GET['Start_Date']) > 0 || strlen($_GET['End_Date']) > 0 || strlen($_GET['Location_Tag']) > 0 || $Status > 0){
        $_SESSION['Last_Search'] = "tickets.php?Dashboard=Mechanic&Start_Date=" . $_GET['Start_Date'] . "&End_Date=" . $_GET['End_Date'] . "&Location_Tag=" . $_GET['Location_Tag'] . "&Status=" . $_GET['Status'] . "&Show_Hours=" . $_GET['show_hours'] . "&Show_Tickets=" . $_GET['show_Tickets'];
    }
}?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
            <h1 class="page-header">Payroll Dashboard</h1>
            </div>
            <!-- /.col-lg-12 -->
            </div>
            <div class='row'>
                <div class='col-lg-12'>
                    <div class='panel panel-default'>
                        <div class='panel-heading'></div>
                        <div class='panel-body'>
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example">
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
                                <?php 
                                    $Today = date('l');
                                    $Date = date('Y-m-d');
                                    if($Today == 'Thursday'){$WeekOf = date('Y-m-d');}
                                    elseif($Today == 'Friday'){$WeekOf = date('Y-m-d', strtotime($Today . ' -1 days'));}
                                    elseif($Today == 'Saturday'){$WeekOf = date('Y-m-d', strtotime($Today . ' -2 days'));}
                                    elseif($Today == 'Sunday'){$WeekOf = date('Y-m-d', strtotime($Today . ' -3 days'));}
                                    elseif($Today == 'Monday'){$WeekOf = date('Y-m-d', strtotime($Today . ' -4 days'));}
                                    elseif($Today == 'Tuesday'){$WeekOf = date('Y-m-d', strtotime($Today . ' -5 days'));}
                                    elseif($Today == 'Wednesday'){$WeekOf = date('Y-m-d', strtotime($Today . ' -6 days'));}
                                    $WeekOf = date('Y-m-d',strtotime($WeekOf . ' +6 days'));
                                    if($Today == 'Thursday'){$Thursday = date('Y-m-d');}
                                    elseif($Today == 'Friday'){$Thursday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                    elseif($Today == 'Saturday'){$Thursday = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                    elseif($Today == 'Sunday'){$Thursday = date('Y-m-d', strtotime($Date . ' -3 days'));}
                                    elseif($Today == 'Monday'){$Thursday = date('Y-m-d', strtotime($Date . ' -4 days'));}
                                    elseif($Today == 'Tuesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -5 days'));}
                                    elseif($Today == 'Wednesday'){$Thursday = date('Y-m-d', strtotime($Date . ' -6 days'));}
                                    if($Today == 'Friday'){$Friday = date('Y-m-d');}
                                    elseif($Today == 'Saturday'){$Friday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                    elseif($Today == 'Sunday'){$Friday = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                    elseif($Today == 'Monday'){$Friday = date('Y-m-d', strtotime($Date . ' -3 days'));}
                                    elseif($Today == 'Tuesday'){$Friday = date('Y-m-d', strtotime($Date . ' -4 days'));}
                                    elseif($Today == 'Wednesday'){$Friday = date('Y-m-d', strtotime($Date . ' -5 days'));}
                                    elseif($Today == 'Thursday'){$Friday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                    if($Today == 'Satuday'){$Saturday = date('Y-m-d');}
                                    elseif($Today == 'Sunday'){$Saturday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                    elseif($Today == 'Monday'){$Saturday = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                    elseif($Today == 'Tuesday'){$Saturday = date('Y-m-d', strtotime($Date . ' -3 days'));}
                                    elseif($Today == 'Wednesday'){$Saturday = date('Y-m-d', strtotime($Date . ' -4 days'));}
                                    elseif($Today == 'Thursday'){$Saturday = date('Y-m-d', strtotime($Date . ' +2 days'));}
                                    elseif($Today == 'Friday'){$Saturday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                    if($Today == 'Sunday'){$Sunday = date('Y-m-d');}
                                    elseif($Today == 'Monday'){$Sunday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                    elseif($Today == 'Tuesday'){$Sunday = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                    elseif($Today == 'Wednesday'){$Sunday = date('Y-m-d', strtotime($Date . ' -3 days'));}
                                    elseif($Today == 'Thursday'){$Sunday = date('Y-m-d', strtotime($Date . ' +3 days'));}
                                    elseif($Today == 'Friday'){$Sunday = date('Y-m-d', strtotime($Date . ' +2 days'));}
                                    elseif($Today == 'Saturday'){$Sunday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                    if($Today == 'Monday'){$Monday = date('Y-m-d');}
                                    elseif($Today == 'Tuesday'){$Monday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                    elseif($Today == 'Wednesday'){$Monday = date('Y-m-d', strtotime($Date . ' -2 days'));}
                                    elseif($Today == 'Thursday'){$Monday = date('Y-m-d', strtotime($Date . ' +4 days'));}
                                    elseif($Today == 'Friday'){$Monday = date('Y-m-d', strtotime($Date . '  +3 days'));}
                                    elseif($Today == 'Saturday'){$Monday = date('Y-m-d', strtotime($Date . ' +2 days'));}
                                    elseif($Today == 'Sunday'){$Monday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                    if($Today == 'Tuesday'){$Tuesday = date('Y-m-d');}
                                    elseif($Today == 'Wednesday'){$Tuesday = date('Y-m-d', strtotime($Date . ' -1 days'));}
                                    elseif($Today == 'Thursday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +5 days'));}
                                    elseif($Today == 'Friday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +4 days'));}
                                    elseif($Today == 'Saturday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +3 days'));}
                                    elseif($Today == 'Sunday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +2 days'));}
                                    elseif($Today == 'Monday'){$Tuesday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                    if($Today == 'Wednesday'){$Wednesday = date('Y-m-d');}
                                    elseif($Today == 'Thursday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +6 days'));}
                                    elseif($Today == 'Friday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +5 days'));}
                                    elseif($Today == 'Saturday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +4 days'));}
                                    elseif($Today == 'Sunday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +3 days'));}
                                    elseif($Today == 'Monday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +2 days'));}
                                    elseif($Today == 'Tuesday'){$Wednesday = date('Y-m-d', strtotime($Date . ' +1 days'));}
                                    while($WeekOf > "2017-03-08 00:00:00.000"){?><tr style='cursor:pointer;' class="odd gradeX hoverGray">
                                        <?php $WeekOf = date('Y-m-d',strtotime($WeekOf . ' -7 days')); ?>
                                        <td class='WeekOf' rel='<?php echo $WeekOf;?>' onClick="refresh_this(this);"><?php 
                                            echo $WeekOf;
                                        ?></td>
                                        <?php 
                                        $Thursday = date('Y-m-d',strtotime($Thursday . ' -7 days'));
                                            $r = $database->query(null,"
                                                SELECT Sum(Total) as Summed
                                                FROM nei.dbo.TicketD 
                                                WHERE 
                                                    fWork='" . $Employee_ID . "' 
                                                    AND EDate >= '" . $Thursday . " 00:00:00.000' 
                                                    AND EDate <= '" . $Thursday . " 23:59:59.999' AND ClearPR='1'");?>
                                        <td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php 
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Friday = date('Y-m-d',strtotime($Friday . ' -7 days'));
                                            $r = $database->query(null,"
                                                SELECT Sum(Total) as Summed
                                                FROM nei.dbo.TicketD 
                                                WHERE fWork='" . $Employee_ID . "' 
                                                    AND EDate >= '" . $Friday . " 00:00:00.000' 
                                                    AND EDate <= '" . $Friday . " 23:59:59.999' AND ClearPR='1'");?>
                                        <td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php 
                                           echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Saturday = date('Y-m-d',strtotime($Saturday . ' -7 days'));
                                            $r = $database->query(null,"
                                                SELECT Sum(Total) as Summed
                                                FROM nei.dbo.TicketD 
                                                WHERE 
                                                    fWork='" . $Employee_ID . "' 
                                                    AND EDate >= '" . $Saturday . " 00:00:00.000' 
                                                    AND EDate <= '" . $Saturday . " 23:59:59.999' AND ClearPR='1'");?>
                                        <td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php 
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Sunday = date('Y-m-d',strtotime($Sunday . ' -7 days'));
                                            $r = $database->query(null,"
                                                SELECT Sum(Total) as Summed
                                                FROM nei.dbo.TicketD 
                                                WHERE 
                                                    fWork='" . $Employee_ID . "' 
                                                    AND EDate >= '" . $Sunday . " 00:00:00.000' 
                                                    AND EDate <= '" . $Sunday . " 23:59:59.999' AND ClearPR='1'");?>
                                        <td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php 
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Monday = date('Y-m-d',strtotime($Monday . ' -7 days'));
                                            $r = $database->query(null,"
                                                SELECT Sum(Total) as Summed 
                                                FROM nei.dbo.TicketD 
                                                WHERE 
                                                    fWork='" . $Employee_ID . "' 
                                                    AND EDate >= '" . $Monday . " 00:00:00.000' 
                                                    AND EDate <= '" . $Monday . " 23:59:59.999' AND ClearPR='1'");?>
                                        <td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php 
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Tuesday = date('Y-m-d',strtotime($Tuesday . ' -7 days'));
                                            $r = $database->query(null,"
                                                SELECT Sum(Total) as Summed 
                                                FROM nei.dbo.TicketD 
                                                WHERE 
                                                    fWork='" . $Employee_ID . "' 
                                                    AND EDate >= '" . $Tuesday . " 00:00:00.000' 
                                                    AND EDate <= '" . $Tuesday . " 23:59:59.999' AND ClearPR='1'");?>
                                        <td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php 
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Wednesday = date('Y-m-d',strtotime($Wednesday . ' -7 days'));
                                            $r = $database->query(null,"
                                                SELECT Sum(Total) as Summed 
                                                FROM nei.dbo.TicketD 
                                                WHERE 
                                                    fWork='" . $Employee_ID . "' 
                                                    AND EDate >= '" . $Wednesday . " 00:00:00.000' 
                                                    AND EDate <= '" . $Wednesday . " 23:59:59.999' AND ClearPR='1'");?>
                                        <td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php 
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <td><?php
                                            $r = $database->query(null,"
                                                SELECT Sum(Total) as Summed 
                                                FROM nei.dbo.TicketD 
                                                WHERE 
                                                    fWork='" . $Employee_ID . "' 
                                                    AND EDate >= '" . $Thursday . " 00:00:00.000' 
                                                    AND EDate <= '" . $Wednesday . " 23:59:59.999' AND ClearPR='1'");
                                            $Hours = 0;
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <td>$<?php 
                                            $r = $database->query(null,"
                                                SELECT Sum(Zone) + 0 as Zone_Sum, Sum(OtherE) + 0 as Other_Sum
                                                FROM nei.dbo.TicketD
                                                WHERE 
                                                    fWork='" . $Employee_ID . "'
                                                    AND EDate >= '" . $Thursday . " 00:00:00.000' 
                                                    AND EDate <= '" . $Wednesday . " 23:59:59.999' AND ClearPR='1'");
                                            $array =  sqlsrv_fetch_array($r);
                                            echo $array['Zone_Sum'] + $array['Other_Sum'];
                                        ?></td>
                                    </tr><?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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
        $(document).ready(function() {
            $('#dataTables-example').DataTable({responsive: true});
            $(".sorting_asc").click();
        });
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=payroll.php';</script></head></html><?php }?>