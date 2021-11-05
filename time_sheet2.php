<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset(
        $_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r);
    $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($NEI,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Time']) && $My_Privileges['Time']['User_Privilege'] >= 4 && $My_Privileges['Time']['Group_Privilege'] >= 4 && $My_Privileges['Time']['Other_Privilege'] >= 0){$Privileged = TRUE;}
    sqlsrv_query($NEI,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "time_sheet2.php"));
    if(!isset($My_Connection['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=time_sheet2.php';</script></head></html><?php }
    else {

$Mechanic = is_numeric($_SESSION['User']) ? $_SESSION['User'] : -1;

if($Mechanic > 0){
    $Dispatch_Users = array();
    //$Mechanic = addSlashes($_GET['Mechanic']);

    $Call_Sign = "";
    $r = sqlsrv_query($NEI,"select * from Emp where ID = " . $Mechanic);
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

    $r = sqlsrv_query($NEI,"
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
        $r = sqlsrv_query($NEI,"
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
    <title>Nouveau Elevator Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class='row'>
                <div class='col-lg-12'>
                    <div class='panel panel-default'>
                        <div class='panel-heading'><h2><?php echo ucfirst(strtolower($Mechanic_Array['fFirst'])) . " " . ucfirst(strtolower($Mechanic_Array['Last']));?>'s Timesheet </h2></div>
                        <div class='panel-body'>
                            <style>
                            .hoverGray:hover {
                                background-color : gold !important;
                            }
                            table#TimeSheet tbody tr {
                                color : black !important;
                            }
                            table#TimeSheet tbody tr:nth-child( even ) {
                                background-color : rgba( 240, 240, 240, 1 ) !important;
                            }
                            table#TimeSheet tbody tr:nth-child( odd ) {
                                background-color : rgba( 255, 255, 255, 1 ) !important;
                            }
                            </style>
                            <table id="TimeSheet" class='table table-bordered table-hover' width="100%">
                                <thead>
                                    <tr>
                                        <th>Weeks</th>
                                        <th colspan='2'>Thu</th>
                                        <th colspan='2'>Fri</th>
                                        <th colspan='2'>Sat</th>
                                        <th colspan='2'>Sun</th>
                                        <th colspan='2'>Mon</th>
                                        <th colspan='2'>Tue</th>
                                        <th colspan='2'>Wed</th>
                                        <th colspan='3'>Total</th>
										                    <th>Expenses</th>
                                    </tr>
                                    <tr>
                                      <th>&nbsp;</th>
                                      <th>Reg</th>
                                      <th>OT</th>
                                      <th>Reg</th>
                                      <th>OT</th>
                                      <th>Reg</th>
                                      <th>OT</th>
                                      <th>Reg</th>
                                      <th>OT</th>
                                      <th>Reg</th>
                                      <th>OT</th>
                                      <th>Reg</th>
                                      <th>OT</th>
                                      <th>Reg</th>
                                      <th>OT</th>
                                      <th>Reg</th>
                                      <th>OT</th>
                                      <th>Total</th>
                                      <th>&nbsp;</th>
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
                                            SELECT Sum(Reg) + Sum(NT) as Summed
                                            FROM TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
                                        <td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $r = sqlsrv_query($NEI,"
                                            SELECT Sum(OT) + Sum(DT) as Summed
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
                                        $r = sqlsrv_query($NEI,"
                                            SELECT SUM(Reg) + SUM(NT) AS Summed
                                            FROM TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
                                        <td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"
                                            SELECT SUM(OT) + SUM(DT) AS Summed
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
                                        $r = sqlsrv_query($NEI,"
                                            SELECT SUM(Reg) + SUM(NT) AS Summed
                                            FROM TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
                                        <td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"
                                            SELECT SUM(OT) + SUM(DT) AS Summed
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
                                        $r = sqlsrv_query($NEI,"
                                            SELECT Sum(Reg) + Sum(NT) AS Summed
                                            FROM TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
                                        <td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"
                                            SELECT Sum(OT) + SUM(DT) AS Summed
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
                                        $r = sqlsrv_query($NEI,"
                                            SELECT Sum(Reg) + SUM(NT) AS Summed
                                            FROM TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
                                        <td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"
                                            SELECT Sum(OT) + SUM(DT) AS Summed
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
                                        $r = sqlsrv_query($NEI,"
                                            SELECT Sum(Reg) + SUM(NT) AS Summed
                                            FROM TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
                                        <td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"
                                            SELECT Sum(OT) + SUM(DT) AS Summed
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
                                        $r = sqlsrv_query($NEI,"
                                            SELECT Sum(Reg) + SUM(NT) AS Summed
                                            FROM TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
                                        <td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"
                                            SELECT Sum(OT) + SUM(DT) AS Summed
                                            FROM TicketD
                                            WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
                                        <td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <td><?php
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + Sum(NT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><td><?php
                                            $r = sqlsrv_query($NEI,"SELECT Sum(OT) + Sum(DT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><td><?php
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
										<td>$<?php
											$r = sqlsrv_query($NEI,"SELECT Sum(Zone) + Sum(Toll) + Sum(OtherE) AS Expenses FROM TicketD WHERE fWork = ? AND EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'",array($Employee_ID));
											echo sqlsrv_fetch_array($r)['Expenses'];
										?></td>
                                    </tr>
                                    <?php while($WeekOf > "2018-09-26 00:00:00.000"){?><tr style='cursor:pointer;' class="odd gradeX hoverGray">
                                        <?php $WeekOf = date('Y-m-d',strtotime($WeekOf . '-7 days')); ?>
                                        <td class='WeekOf' rel='<?php echo $WeekOf;?>' onClick="refresh_this(this);"><?php
                                            echo $WeekOf;
                                        ?></td>
                                        <?php
                                        $Thursday = date('Y-m-d',strtotime($Thursday . '-7 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + Sum(NT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
                                        <td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php
                                            $r = sqlsrv_query($NEI,"SELECT Sum(OT) + Sum(DT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Thursday . " 23:59:59.999'");?>
                                        <td class='Thursday' rel='<?php echo $Thursday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Friday = date('Y-m-d',strtotime($Friday . '-7 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + SUM(NT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
                                        <td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"SELECT Sum(OT) + SUM(DT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Friday . " 00:00:00.000' AND EDate <= '" . $Friday . " 23:59:59.999'");?>
                                        <td class='Friday' rel='<?php echo $Friday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Saturday = date('Y-m-d',strtotime($Saturday . '-7 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + SUM(NT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
                                        <td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"SELECT Sum(OT) + SUM(DT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Saturday . " 00:00:00.000' AND EDate <= '" . $Saturday . " 23:59:59.999'");?>
                                        <td class='Saturday' rel='<?php echo $Saturday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Sunday = date('Y-m-d',strtotime($Sunday . '-7 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + SUM(NT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
                                        <td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"SELECT Sum(OT) + SUM(DT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Sunday . " 00:00:00.000' AND EDate <= '" . $Sunday . " 23:59:59.999'");?>
                                        <td class='Sunday' rel='<?php echo $Sunday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Monday = date('Y-m-d',strtotime($Monday . '-7 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + SUM(NT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
                                        <td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"SELECT Sum(OT) + SUM(DT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Monday . " 00:00:00.000' AND EDate <= '" . $Monday . " 23:59:59.999'");?>
                                        <td class='Monday' rel='<?php echo $Monday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Tuesday = date('Y-m-d',strtotime($Tuesday . '-7 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + SUM(NT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
                                        <td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"SELECT Sum(OT) + SUM(DT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Tuesday . " 00:00:00.000' AND EDate <= '" . $Tuesday . " 23:59:59.999'");?>
                                        <td class='Tuesday' rel='<?php echo $Tuesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <?php $Wednesday = date('Y-m-d',strtotime($Wednesday . '-7 days'));
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + SUM(NT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
                                        <td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td><?php $r = sqlsrv_query($NEI,"SELECT Sum(OT) + SUM(DT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Wednesday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");?>
                                        <td class='Wednesday' rel='<?php echo $Wednesday;?>' onClick="refresh_this(this);"><?php
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <td><?php
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Reg) + SUM(NT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <td><?php
                                            $r = sqlsrv_query($NEI,"SELECT Sum(OT) + SUM(DT) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
                                        <td><?php
                                            $r = sqlsrv_query($NEI,"SELECT Sum(Total) AS Summed FROM TicketD WHERE fWork='" . $Employee_ID . "' and EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'");
                                            echo sqlsrv_fetch_array($r)['Summed'];
                                        ?></td>
										<td>$<?php
											$r = sqlsrv_query($NEI,"SELECT Sum(Zone) + Sum(Toll) + Sum(OtherE) AS Expenses FROM TicketD WHERE fWork = ? AND EDate >= '" . $Thursday . " 00:00:00.000' AND EDate <= '" . $Wednesday . " 23:59:59.999'",array($Employee_ID));
											echo sqlsrv_fetch_array($r)['Expenses'];
										?></td>
                                    </tr><?php }?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-lg-12'>
                    <div class='panel panel-default'>
                        <div class='panel-heading'><button onClick="document.location.href='time_sheet2.php';">Show All</button></div>
                        <div class='panel-body'>
                            <table width="100%" class="table table-striped table-bordered table-hover" id="dataTables-example2"  style='<?php if(isMobile()){?>font-size:8px;<?php }?>'>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Location</th>
                                        <th>Job</th>
                                        <th>Ticket #</th>
                                        <th>Reg.</th>
                                        <th>Travel</th>
                                        <th>OT</th>
                                        <th>DB</th>
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
                                        if(isset($_GET['Date'])){
                                            $Date = $_GET['Date'];
                                            $Type = str_replace(" sorting_1","",$_GET['Type']);
                                            $SQL = ($Type == "WeekOf") ? $SQL = "EDate >= '" . date('Y-m-d',strtotime($Date . '-6 days')) . " 00:00:00.000' AND EDate <= '" . $Date . " 23:59:59.999'" : "EDate >= '" . $Date . " 00:00:00.000' AND EDate <= '" . $Date . " 23:59:59.999'";
                                            $r = sqlsrv_query($NEI,"
                                                SELECT
                                                    TicketD.*,
                                                    Loc.Tag
                                                FROM
                                                    TicketD
                                                    LEFT JOIN Loc ON TicketD.Loc = Loc.Loc
                                                WHERE
                                                    fWork='" . $Employee_ID . "'
                                                    AND EDate >= '2017-03-02 00:00:00.000' AND " . $SQL);  }
                                        else {
                                            $r = sqlsrv_query($NEI,"
                                                SELECT
                                                    TicketD.*,
                                                    Loc.Tag,
                                                    Job.fDesc
                                                FROM
                                                    TicketD
                                                    LEFT JOIN Loc ON TicketD.Loc = Loc.Loc
                                                    LEFT JOIN Job ON TicketD.Job = Job.ID
                                                WHERE
                                                    fWork='" . $Employee_ID . "'
                                                    AND EDate >= '2017-03-02 00:00:00.000'");    }
                                        $Tickets = array();
                                        while($array = sqlsrv_fetch_array($r)){
                                            $array['Status'] = "Complete";
                                            $Tickets[] = $array;}
                                    ?>
                                    <?php foreach($Tickets as $Ticket){?><tr style='cursor:pointer;' class="odd gradeX hoverGray" onClick="document.location.href='ticket.php?ID=<?php echo $Ticket['ID'];?>';">
                                        <td><?php echo $Ticket['EDate'];?></td>
                                        <td><?php echo $Ticket['Tag'];?></td>
                                        <td><?php echo $Ticket['fDesc'];?></td>
                                        <td><?php echo $Ticket['ID'];?></td>
                                        <td><?php echo $Ticket['Reg'];?></td>
                                        <td><?php echo $Ticket['TT'];?></td>
                                        <td><?php echo $Ticket['OT'];?></td>
                                        <td><?php echo $Ticket['DT'];?></td>
                                        <td><?php echo $Ticket['Total'];?></td>
                                        <td>$<?php echo ($Ticket['Zone'] + $Ticket['OtherE']);?></td>
                                    <?php }?></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php require('cgi-bin/js/datatables.php');?>
    
    <script>
        function refresh_this(link){document.location.href='time_sheet2.php?Date=' + $(link).attr('rel') + '&Type=' + $(link).attr('class') + "&Mechanic=<?php echo $_GET['Mechanic'];?>";}
        $('#dataTables-example').DataTable({
			responsive   : true,
			searching    : false,
			lengthChange : false
		});
        $('#dataTables-example2').DataTable({
			responsive   : true,
			searching    : false,
			lengthChange : false
		});
        $(".sorting_asc").click();
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=time_sheet2.php';</script></head></html><?php }?>
