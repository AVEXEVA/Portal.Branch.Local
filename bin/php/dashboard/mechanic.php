<?php 
session_start( [ 'read_and_close' => true ] );
require('php/classes/database.php');
$Database = new Database();
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $User = $Database->escapeString($_SESSION['User']);
    $Hash = $Database->escapeString($_SESSION['Hash']);
    $array = $Database->query("select * from Connection where User='{$User}' AND Hash='{$Hash}'");
    if(!isset($array['ID'])){?><html><head><script>document.location.href='login.php';</script></head></html><?php }
    else {
//BEGIN PAGE
$c = odbc_connect("MSSQL","sa","sql");

$Mechanic = is_numeric($_SESSION['User']) ? $_SESSION['User'] : -1;

if($Mechanic > 0){
    $Call_Sign = "";
    $r = odbc_exec($c,"select * from Emp where ID = " . $_SESSION['User']);
    $array = odbc_fetch_array($r);
    $Call_Sign = $array['CallSign'];
    $Alias = $array['fFirst'][0] . $array['Last'];
    $Employee_ID = $array['fWork'];
    while($array = odbc_fetch_array($r)){}

    //GET TICKETS
    if($_GET['Start_Date'] > 0){$Start_Date = DateTime::createFromFormat('m/d/Y', $_GET['Start_Date'])->format("Y-m-d 00:00:00.000");}
    else{$Start_Date = DateTime::createFromFormat('m/d/Y',"1/1/1969")->format("Y-m-d 00:00:00.000");}

    if($_GET['End_Date'] > 0){$End_Date = DateTime::createFromFormat('m/d/Y', $_GET['End_Date'])->format("Y-m-d 00:00:00.000");}
    else{$End_Date = DateTime::createFromFormat('m/d/Y',"1/1/3000")->format("Y-m-d 00:00:00.000");}

    if(!isset($_GET['Location_Tag']) || $_GET['Location_Tag'] == "All" || $_GET['Location_Tag'] == ""){$Location_Tag = "' OR '1'='1";}
    else {$Location_Tag = addslashes($_GET['Location_Tag']);}
    
    if(!isset($_GET['Status']) || $_GET['Status'] == 'All'){$Status = "' OR '1'='1";}
    else{$Status = $_GET['Status'];}
    //$prepared = odbc_prepare($c,"select TicketO.*, Loc.Tag as Tag, Loc.Address as Address, Loc.City as City, Loc.State as State, Loc.Zip as Zip, Job.ID as Job_ID, Job.fDesc as Job_Description, OwnerWithRol.ID as Owner_ID, OwnerWithRol.Name as Customer, JobType.Type as Job_Type, Elev.Unit as Unit_Label, Elev.State as Unit_State, TickOStatus.Type as Status from (((((TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketO.Job = Job.ID) LEFT JOIN nei.dbo.OwnerWithRol ON TicketO.Owner = OwnerWithRol.ID) LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID) LEFT JOIN nei.dbo.Elev ON TicketO.LElev = Elev.ID) LEFT JOIN nei.dbo.TickOStatus ON TicketO.Assigned = TickOStatus.Ref where TicketO.DWork='" . $Call_Sign . "' AND CDate >= '" . $Start_Date . "' AND EDate <= '" . $End_Date . "' AND Loc.Loc = ?");
    //$r = odbc_exec($prepared,$Location_Tag);

    $r = odbc_exec($c,"select TicketO.*, Loc.Tag as Tag, Loc.Address as Address, Loc.City as City, Loc.State as State, Loc.Zip as Zip, Job.ID as Job_ID, Job.fDesc as Job_Description, OwnerWithRol.ID as Owner_ID, OwnerWithRol.Name as Customer, JobType.Type as Job_Type, Elev.Unit as Unit_Label, Elev.State as Unit_State, TickOStatus.Type as Status from (((((TicketO LEFT JOIN nei.dbo.Loc ON TicketO.LID = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketO.Job = Job.ID) LEFT JOIN nei.dbo.OwnerWithRol ON TicketO.Owner = OwnerWithRol.ID) LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID) LEFT JOIN nei.dbo.Elev ON TicketO.LElev = Elev.ID) LEFT JOIN nei.dbo.TickOStatus ON TicketO.Assigned = TickOStatus.Ref where TicketO.fWork='" . $Employee_ID . "' AND CDate >= '" . $Start_Date . "' AND EDate <= '" . $End_Date . "' AND (Tag = '" . $Location_Tag . "') AND (Assigned = '" . $Status .  "');");
    $Tickets = array();
    while($array = odbc_fetch_array($r)){
        $Tickets[$array['ID']] = $array;
    }
    
    if($Status == "4" || $Status == "" || !isset($_GET['Status'])){
        $r = odbc_exec($c,"select TicketD.*, Loc.Tag as Tag, Loc.Address as Address, Loc.City as City, Loc.State as State, Loc.Zip as Zip, Job.ID as Job_ID, Job.fDesc as Job_Description, OwnerWithRol.ID as Owner_ID, OwnerWithRol.Name as Customer, JobType.Type as Job_Type, Elev.Unit as Unit_Label, Elev.State as Unit_State from ((((TicketD LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc) LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID) LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner = OwnerWithRol.ID) LEFT JOIN nei.dbo.JobType ON Job.Type = JobType.ID) LEFT JOIN nei.dbo.Elev ON TicketD.Elev = Elev.ID where TicketD.fWork='" . $Employee_ID . "' AND CDate >= '" . $Start_Date . "' AND EDate <= '" . $End_Date . "' AND (Tag = '" . $Location_Tag . "');");
        while($array = odbc_fetch_array($r)){
            $Tickets[$array['ID']] = $array;
            $Tickets[$array['ID']]['Status'] = "Completed";
        }
    }
    $_SESSION['Tickets'] = $Tickets;
}?><!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Nouveau Elevator Admin</title>

    <!-- Bootstrap Core CSS -->
    <link href="https://www.nouveauelevator.com/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="https://www.nouveauelevator.com/vendor/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="https://www.nouveauelevator.com/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- JQUERY UI CSS-->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/lihtml5shiv/3.7html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
    <style>
    .hide {display:none;}
    </style>
</head>

<body>

    <div id="wrapper">

        <?php require('html/navigation.html');?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Dashboard</h1>
                </div>
            </div>
            <?php require("php/mechanic/activity_panel.php");?>
            <div class="row">
                <div class='col-md-4'>
                    <label class='tag' for="filter_location_tag">Location:</label>
                    <select name="filter_location_tag" onChange="filter_location();" onClick="reset_location();" style='max-width:50%;'><br />
                        <option value="All">All</option>
                        <?php 
                        $Location_Tags = array_unique(array_column($Tickets,"Tag"));
                        //$Location_Tags = array_unique(array_column($Tickets,"Loc"));
                        sort($Location_Tags);
                        foreach($Location_Tags as $key=>$Location_Tag){?>
                            <option value="<?php echo $Location_Tag;?>"<?php if($_GET['Location_Tag'] == $Location_Tag){?> selected='selected'<?php }?>><?php echo $Location_Tag;?></option>
                        <?php }?>
                    </select>
                </div>
                <style>
                label.date, label.tag {width:80px;text-align:right;}
                </style>
                <div class='col-md-4'>
                    <label class='date' for="filter_start_date">Start Date:</label>
                    <input class='start_date' name='filter_start_date' value='<?php echo $_GET['Start_Date'];?>' /><br />
                    <label class='date' for="filter_end_date">End Date:</label>
                    <input class='end_date' name='filter_end_date'  value='<?php echo $_GET['End_Date'];?>'/>
                </div>
            </div> 
            <div class="row">
                <div class="panel-body">
                    <div class="list-group"><?php foreach($Tickets as $Ticket){?>
                        <a style="" href="ticket.php?ID=<?php echo $Ticket['ID'];?>" class="list-group-item ticket-small" location_tag='<?php echo $Ticket['Tag'];?>' start_date='<?php echo $Ticket['CDate'];?>' end_date='<?php echo $Ticket['EDate'];?>'>
                            <div class='row'>
                                <div class='col-xs-3' style='font-weight:bold;'>
                                    <i class="fa fa-plus-square fa-fw"></i> <span><?php echo $Ticket['Tag'];?></span>
                                </div>
                                <div class='col-xs-4'>
                                    <span><?php echo $Ticket['fDesc'];?></span>
                                </div>
                                <div class='col-xs-4'>
                                    <span class="pull-right text-muted small"><em><?php echo $Ticket['Status'];?></em></span>
                                </div>
                            </div>
                        </a>
                    <?php }?></div>
                </div>
            </div>
            <style>
                .ticket-unit-label>div, .ticket-unit-state>div, .ticket-job>div, .ticket-creation>div, .ticket-due>div, .ticket-completed>div, .ticket-customer>div, .ticket-street>div,.ticket-city>div,.ticket-state>div, .ticket-type>div,.ticket-unit>div,.ticket-division>div {
                    display:inline-block;
                }
                .ticket-description>div:first-child {
                    font-weight:bold;
                }
                .ticket-unit-label>div:first-child, .ticket-unit-state>div:first-child, .ticket-job>div:first-child, .ticket-creation>div:first-child, .ticket-due>div:first-child, .ticket-completed>div:first-child, .ticket-customer>div:first-child, .ticket-street>div:first-child,.ticket-city>div:first-child,.ticket-state>div:first-child, .ticket-type>div:first-child,.ticket-unit>div:first-child,.ticket-division>div:first-child {
                    width:100px;
                    font-weight:bold;
                }
            </style>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Morris Charts JavaScript -->
    <!--<script src="https://www.nouveauelevator.com/vendor/raphael/raphael.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.php"></script>-->

    <!-- Custom Theme JavaScript -->
    

    <!-- JQUERY UI Javascript -->
    

    <script>
        /*function filter(){
            $(".ticket-small").each(function(){
                if(
                    (parseInt($("input.start_date").val().substr(6,9)) <= parseInt($(this).attr('start_date').substr(0,4)) && parseInt($("input.start_date").val().substr(0,2)) <= parseInt($(this).attr('start_date').substr(6,7)) && parseInt($("input.start_date").val().substr(4,5)) <= parseInt($(this).attr('start_date').substr(9,10)))
                ){
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }*/
        var reset_loc = 0;
        $(document).ready(function(){
            $("input.start_date").datepicker({
                onSelect:function(dateText, inst){
                    document.location.href="dashboard.php?Dashboard=Mechanic&Mechanic=<?php echo $_SESSION['User'];?>&Start_Date=" + dateText + "&End_Date=" + $("input.end_date").val() + "&Location_Tag=" + $("select[name='filter_location_tag']").val() + "&Status=<?php echo $_GET['Status'];?>";
                }
            });
            $("input.end_date").datepicker({
                onSelect:function(dateText, inst){
                    document.location.href="dashboard.php?Dashboard=Mechanic&Mechanic=<?php echo $_SESSION['User'];?>&Start_Date=" + $("input.start_date").val() + "&End_Date=" + dateText + "&Location_Tag=" + $("select[name='filter_location_tag']").val() + "&Status=<?php echo $_GET['Status'];?>"
                }
            });
        });
        function filter_location(){
            document.location.href="dashboard.php?Dashboard=Mechanic&Mechanic=<?php echo $_SESSION['User'];?>&Start_Date=" + $("input.start_date").val() + "&End_Date=" + $("input.end_date").val() + "&Location_Tag=" + $("select[name='filter_location_tag']").val() + "&Status=<?php echo $_GET['Status'];?>";
        }
        function reset_location(){
            /*var value = $("select[name='filter_location_tag']").val();
            if(value != "All" && reset_loc = 0){document.location.href="dashboard.php?Dashboard=Mechanic&Mechanic=<?php echo $_SESSION['User'];?>&Start_Date=" + $("input.start_date").val() + "&End_Date=" + $("input.end_date").val() + "&Location_Tag=All";}*/
        }
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='login.php';</script></head></html><?php }?>