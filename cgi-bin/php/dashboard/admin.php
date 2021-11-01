<?php 
//CONNECT TO SERVER
$c = odbc_connect("MSSQL","sa","sql");

//GET OPEN TICKETS
$r = odbc_exec($c,"select * from TicketO where Assigned = 0;");
$o = array();
while($array = odbc_fetch_array($r)){$o[$array["ID"]] = $array;}

///GET COMPLETED TICKETS
//FROM PUSHED TicketD
$r = odbc_exec($c,"select * FROM nei.dbo.TicketD where EDate >= CAST(CURRENT_TIMESTAMP AS DATE) and EDate < DATEADD(DD, 1, CAST(CURRENT_TIMESTAMP AS DATE));");
$d = array();
while($array = odbc_fetch_array($r)){$d[$array["ID"]] = $array;}
//FROM CURRENT TicketO
$r = odbc_exec($c,"select * from TicketO where EDate >= CAST(CURRENT_TIMESTAMP AS DATE) and EDate < DATEADD(DD, 1, CAST(CURRENT_TIMESTAMP AS DATE)) and Assigned = 4;");
while($array = odbc_fetch_array($r)){$d[$array["ID"]] = $array;}

//GET ACTIVE TICKETS
$a = array();
$r = odbc_exec($c,"select * from TicketO where Assigned = 3;");
while($array = odbc_fetch_array($r)){$a[$array["ID"]] = $array;}?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>Nouveau Elevator Admin</title>

    <!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="../vendor/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- JQUERY UI CSS-->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/lihtml5shiv/3.7html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

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
            <style>
                .ticket-job>div, .ticket-creation>div, .ticket-due>div, .ticket-completed>div, .ticket-customer>div, .ticket-street>div,.ticket-city>div,.ticket-state>div, .ticket-type>div,.ticket-unit>div,.ticket-division>div {
                    display:inline-block;
                }
                .ticket-description>div:first-child, .ticket-job>div:first-child, .ticket-creation>div:first-child, .ticket-due>div:first-child, .ticket-completed>div:first-child, .ticket-customer>div:first-child, .ticket-street>div:first-child,.ticket-city>div:first-child,.ticket-state>div:first-child, .ticket-type>div:first-child,.ticket-unit>div:first-child,.ticket-division>div:first-child {
                    width:100px;
                    font-weight:bold;
                }
                .hide { display:none; }
            </style>
            <?php require('php/statistics/activity_panel.php');?>
            <div class="row">
                <div class="col-lg-8">
                    <?php require('html/ticket_activity_area_graph.php');?>
                </div>
                <div class="col-lg-4">
                    <?php require('html/notifications_panel.php');?>
                </div>
            </div>
            <?php /*<div class="row">
                <?php $r = odbc_exec($c,"select * from TicketO");?>
                <?php 
                $row = 0;
                while(odbc_fetch_row($r)){
                    $row++;?><div class="col-lg-12 ticket <?php if($row > 5){?>hide<?php }?>">
                    <div class="panel panel-red">
                        <div class="panel-heading">
                            Ticket #<?php echo odbc_result($r,"ID");?>
                        </div>
                        <div class="panel-body">   
                            <div class="row">
                                <div class="col-xs-4">
                                    <div class="ticket-customer">
                                        <div>Customer:</div><div><?php echo $d[42]["CDate"];?></div>
                                    </div>
                                    <div class="ticket-street">
                                        <div>Street:</div><div><?php echo odbc_result($r,"LDesc3");?></div>
                                    </div>
                                    <div class="ticket-city">
                                        <div>City:</div><div>Long Island City</div>
                                    </div>
                                    <div class="ticket-state">
                                        <div>State:</div><div>New York</div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="ticket-job">
                                        <div>Job:</div><div><a href="job.php?=<?php echo odbc_result($r,"Job");?>"><?php echo odbc_result($r,"Job");?></a></div>
                                    </div>
                                    <div class="ticket-type">
                                        <div>Type:</div><div>Maintenance</div>
                                    </div>
                                    <div class="ticket-unit">
                                        <div>Unit:</div><div>Elevator #1</div>
                                    </div>
                                    <div class="ticket-division">
                                        <div>Division:</div><div>Division #1</div>
                                    </div>
                                </div>
                                <div class="col-xs-4">
                                    <div class="ticket-creation">
                                        <div>Creation:</div><div><?php echo odbc_result($r,"CDATE");?></div>
                                    </div>
                                    <div class="ticket-due">
                                        <div>Due:</div><div><?php echo odbc_result($r,"DDate");?></div>
                                    </div>
                                    <div class="ticket-completed">
                                        <div>Completed:</div><div><?php echo odbc_result($r,"EDate");?></div>
                                    </div>
                                </div> 
                                <div class="col-xs-12">
                                    <hr>
                                </div>
                                <div class="col-xs-12">
                                    <div class="ticket-description">
                                        <div>Description:</div>
                                        <div>To perform maintenance on the #1 Car. Please clean the pit and motor room, fix the led lights that are broken and get a Nouveau Elevator logo that is transparent for the LED Screen in the cab.</div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                        <div class="panel-footer">
                            <a href="#">View Ticket</a>
                        </div>
                    </div>
                </div><?php }?>
            </div>
            <!-- /.row -->*/?>
            <div class="row">
                <div class="col-lg-8">
                    <?php require('html/bar_chart_example.html');?>
                    <?php require('html/responsive_timeline.html');?>
                </div>
                <!-- /.col-lg-8 -->
                <div class="col-lg-4">
                    <?php require('html/donut_chart.php');?>
                    <?php require('html/chat_panel.php');?>
                </div>
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>

    <!-- Morris Charts JavaScript -->
    <script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.php"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

</body>
</html>
