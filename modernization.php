<?php 
session_start( [ 'read_and_close' => true ] );
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Privilege 
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID']) 
	   	|| !isset($My_Privileges['Executive'])
	  		|| $My_Privileges['Executive']['User_Privilege']  < 4
	  		|| $My_Privileges['Executive']['Group_Privilege'] < 4
	  	    || $My_Privileges['Executive']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "modernization.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class='row'>
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3><?php $Icons->Modernization();?>Modernization Department
                            </h3>
                        </div>
                        <div class="panel-body">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#basic-pills" data-toggle="tab"><?php $Icons->Info();?> Summary</a></li>
                                <li class=""><a href="#hours-pills" data-toggle="tab"><?php $Icons->Info();?> Hours</a></li>
                                <li class=""><a href="#payroll-pills" data-toggle="tab"><?php $Icons->Info();?> Payroll</a></li>
                                <li class=""><a href="#financials-pills" data-toggle="tab"><?php $Icons->Financial();?> Financials</a></li>
                            </ul>
                            <br />
                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="basic-pills">
                                    <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-modernization-jobs"></div></div>
                                    <br />
                                    <div class='row'>
                                        <div class='col-md-6'><div class="flot-chart"><div class="flot-chart-content" id="flot-pie-chart-modernization-jobs-by-supervisor"></div></div></div>
                                        <div class='col-md-6'><h4>Below this pie chart is as of 3/30/2017 Paperless Time Tickets</h4><div class="flot-chart"><div class="flot-chart-content" id="flot-pie-chart-modernization-jobs-hours-by-supervisor"></div></div></div>
                                    </div>
                                </div>
                                <div class='tab-pane fade in' id='hours-pills'>
                                    <h3>Modernization Hours by Supervisor</h3>
                                    <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-modernization-hours-by-supervisor"></div></div>
                                    <div class='row'>
                                        <?php
                                        $data = array();
                                        $data2 = array();
                                        $job_result = sqlsrv_query($NEI,"
                                            SELECT 
                                                Job.Custom1     AS Supervisor, 
                                                Sum(TicketD.Reg)    AS Regular_Time, 
                                                Sum(TicketD.OT)     AS Overtime, 
                                                Sum(TicketD.DT)     AS Doubletime, 
                                                Sum(TicketD.TT)     AS Travel_Time, 
                                                Sum(TicketD.Total)  AS Total
                                            FROM (TicketD LEFT JOIN nei.dbo.Job ON Job.ID = TicketD.Job) LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc
                                            WHERE Job.Type='2' AND TicketD.EDate >= '2017-04-01 00:00:00.000' AND TicketD.EDate < '2017-07-01 00:00:00.000' AND TicketD.Total <= 24
                                            GROUP BY Job.Custom1
                                        ;");
                                        if($job_result){
                                            $Jobs = array();
                                            $Total_Regular_Time = 0;
                                            $Total_Overtime = 0;
                                            $Total_Doubletime = 0;
                                            $Total_Travel_Time = 0;
                                            $Total_Total = 0;
                                            while($Job = sqlsrv_fetch_array($job_result)){$Jobs[] = $Job;}
                                            foreach($Jobs as $Job){
                                                if($Job['Total'] == ''){continue;}
                                                $Division = $Job['Supervisor'];
                                                if(!isset($data[$Division])){
                                                    $data[$Division] = array(
                                                        'Regular_Time'=>0,
                                                        'Overtime'=>0,
                                                        'Doubletime'=>0,
                                                        'Travel_Time'=>0,
                                                        'Total'=>0
                                                    );
                                                }
                                                $data[$Division]['Regular_Time'] += $Job['Regular_Time'];
                                                $data[$Division]['Overtime'] += $Job['Overtime'];
                                                $data[$Division]['Doubletime'] += $Job['Doubletime'];
                                                $data[$Division]['Travel_Time'] += $Job['Travel_Time'];
                                                $data[$Division]['Total'] += $Job['Total'];
                                                $Total_Regular_Time += $Job['Regular_Time'];
                                                $Total_Overtime += $Job['Overtime'];
                                                $Total_Doubletime += $Job['Doubletime'];
                                                $Total_Travel_Time += $Job['Travel_Time'];
                                                $Total_Total += $Job['Total'];
                                            }
                                        }
                                        echo implode(",",$data2);
                                        ?>
                                        <div style='padding:15px;'>
                                            <h3>Quarter 2 Hours by Supervisor</h3>
                                            <table class="display" cellspacing='0' border='1' width='100%'>
                                                <thead>
                                                    <tr>
                                                        <th>Supervisor</th>
                                                        <th>Regular Time</th>
                                                        <th>Overtime</th>
                                                        <th>Double Time</th>
                                                        <th>Travel Time</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody><?php
                                                foreach($data as $Division=>$array){?><tr><?php
                                                    ?><td><?php echo $Division;?></td><?php
                                                    ?><td><?php echo $array['Regular_Time'];?></td><?php
                                                    ?><td><?php echo $array['Overtime'];?></td><?php
                                                    ?><td><?php echo $array['Doubletime'];?></td><?php
                                                    ?><td><?php echo $array['Travel_Time'];?></td><?php
                                                    ?><td><?php echo $array['Total'];?></td><?php
                                                ?></tr><?php }
                                                ?><tr style='border-top:2px solid black;'><?php
                                                    ?><td>All Supervisors</td><?php
                                                    ?><td><?php echo $Total_Regular_Time;?></td><?php
                                                    ?><td><?php echo $Total_Overtime;?></td><?php
                                                    ?><td><?php echo $Total_Doubletime;?></td><?php
                                                    ?><td><?php echo $Total_Travel_Time;?></td><?php
                                                    ?><td><?php echo $Total_Total;?></td><?php
                                                ?></tr><?php
                                                ?></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class='row'>
                                        <?php
                                        $data = array();
                                        $data2 = array();
                                        $job_result = sqlsrv_query($NEI,"
                                            SELECT 
                                                Job.Custom1     AS Supervisor, 
                                                Sum(TicketD.Reg)    AS Regular_Time, 
                                                Sum(TicketD.OT)     AS Overtime, 
                                                Sum(TicketD.DT)     AS Doubletime, 
                                                Sum(TicketD.TT)     AS Travel_Time, 
                                                Sum(TicketD.Total)  AS Total
                                            FROM (TicketD LEFT JOIN nei.dbo.Job ON Job.ID = TicketD.Job) LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc
                                            WHERE Job.Type='2' AND TicketD.EDate >= '2017-07-01 00:00:00.000' AND TicketD.EDate < '2017-10-01 00:00:00.000' AND TicketD.Total <= 24
                                            GROUP BY Job.Custom1
                                        ;");
                                        if($job_result){
                                            $Jobs = array();
                                            $Total_Regular_Time = 0;
                                            $Total_Overtime = 0;
                                            $Total_Doubletime = 0;
                                            $Total_Travel_Time = 0;
                                            $Total_Total = 0;
                                            while($Job = sqlsrv_fetch_array($job_result)){$Jobs[] = $Job;}
                                            foreach($Jobs as $Job){
                                                if($Job['Total'] == ''){continue;}
                                                $Division = $Job['Supervisor'];
                                                if(!isset($data[$Division])){
                                                    $data[$Division] = array(
                                                        'Regular_Time'=>0,
                                                        'Overtime'=>0,
                                                        'Doubletime'=>0,
                                                        'Travel_Time'=>0,
                                                        'Total'=>0
                                                    );
                                                }
                                                $data[$Division]['Regular_Time'] += $Job['Regular_Time'];
                                                $data[$Division]['Overtime'] += $Job['Overtime'];
                                                $data[$Division]['Doubletime'] += $Job['Doubletime'];
                                                $data[$Division]['Travel_Time'] += $Job['Travel_Time'];
                                                $data[$Division]['Total'] += $Job['Total'];
                                                $Total_Regular_Time += $Job['Regular_Time'];
                                                $Total_Overtime += $Job['Overtime'];
                                                $Total_Doubletime += $Job['Doubletime'];
                                                $Total_Travel_Time += $Job['Travel_Time'];
                                                $Total_Total += $Job['Total'];
                                            }
                                        }
                                        echo implode(",",$data2);
                                        ?>
                                        <div style='padding:15px;'>
                                            <h3>Quarter 3 Hours by Supervisor</h3>
                                            <table class="display" cellspacing='0' border='1' width='100%'>
                                                <thead>
                                                    <tr>
                                                        <th>Supervisor</th>
                                                        <th>Regular Time</th>
                                                        <th>Overtime</th>
                                                        <th>Double Time</th>
                                                        <th>Travel Time</th>
                                                        <th>Total</th>
                                                    </tr>
                                                </thead>
                                                <tbody><?php
                                                foreach($data as $Division=>$array){?><tr><?php
                                                    ?><td><?php echo $Division;?></td><?php
                                                    ?><td><?php echo $array['Regular_Time'];?></td><?php
                                                    ?><td><?php echo $array['Overtime'];?></td><?php
                                                    ?><td><?php echo $array['Doubletime'];?></td><?php
                                                    ?><td><?php echo $array['Travel_Time'];?></td><?php
                                                    ?><td><?php echo $array['Total'];?></td><?php
                                                ?></tr><?php }
                                                ?><tr style='border-top:2px solid black;'><?php
                                                    ?><td>All Supervisors</td><?php
                                                    ?><td><?php echo $Total_Regular_Time;?></td><?php
                                                    ?><td><?php echo $Total_Overtime;?></td><?php
                                                    ?><td><?php echo $Total_Doubletime;?></td><?php
                                                    ?><td><?php echo $Total_Travel_Time;?></td><?php
                                                    ?><td><?php echo $Total_Total;?></td><?php
                                                ?></tr><?php
                                                ?></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class='tab-pane fade in' id='payroll-pills'>
                                    <h3>Modenrization Payroll by Division</h3>
                                    <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-modernization-hours-payroll-by-supervisor"></div></div>
                                    <div class='row'>
                                        <?php
                                        $data = array();
                                        $data2 = array();
                                        $job_result = sqlsrv_query($NEI,"
                                            SELECT 
                                                Job.Custom1            AS Supervisor, 
                                                Sum(TicketD.Reg * PRWage.Reg)    AS Regular_Time, 
                                                Sum(TicketD.OT * PRWage.OT1)     AS Overtime, 
                                                Sum(TicketD.DT * PRWage.OT2)     AS Doubletime, 
                                                Sum(TicketD.TT * PRWage.Reg)     AS Travel_Time, 
                                                Sum(TicketD.Total)  AS Total
                                            FROM 
                                                (((TicketD 
                                                LEFT JOIN nei.dbo.Job ON Job.ID = TicketD.Job) 
                                                LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc)
                                                LEFT JOIN Emp ON TicketD.fWork = Emp.fWork)
                                                LEFT JOIN PRWage ON Emp.WageCat = PRWage.ID
                                            WHERE Job.Type='2' AND TicketD.EDate >= '2017-04-01 00:00:00.000' AND TicketD.EDate < '2017-07-01 00:00:00.000' AND TicketD.Total <= 24
                                            GROUP BY Job.Custom1
                                        ;");
                                        if($job_result){
                                            $Jobs = array();
                                            $Total_Regular_Time = 0;
                                            $Total_Overtime = 0;
                                            $Total_Doubletime = 0;
                                            $Total_Travel_Time = 0;
                                            $Total_Total = 0;
                                            while($Job = sqlsrv_fetch_array($job_result)){$Jobs[] = $Job;}
                                            foreach($Jobs as $Job){
                                                if($Job['Total'] == ''){continue;}
                                                $Division = $Job['Supervisor'];
                                                if(!isset($data[$Division])){
                                                    $data[$Division] = array(
                                                        'Regular_Time'=>0,
                                                        'Overtime'=>0,
                                                        'Doubletime'=>0,
                                                        'Travel_Time'=>0,
                                                        'Total'=>0
                                                    );
                                                }
                                                $data[$Division]['Regular_Time'] += $Job['Regular_Time'];
                                                $data[$Division]['Overtime'] += $Job['Overtime'];
                                                $data[$Division]['Doubletime'] += $Job['Doubletime'];
                                                $data[$Division]['Travel_Time'] += $Job['Travel_Time'];
                                                $data[$Division]['Total'] += $Job['Total'];
                                                $Total_Regular_Time += $Job['Regular_Time'];
                                                $Total_Overtime += $Job['Overtime'];
                                                $Total_Doubletime += $Job['Doubletime'];
                                                $Total_Travel_Time += $Job['Travel_Time'];
                                                $Total_Total += $Job['Total'];
                                            }
                                        }
                                        echo implode(",",$data2);
                                        ?>
                                        <div style='padding:15px;'>
                                            <h3>Quarter 2 Payroll by Supervisor</h3>
                                            <table class="display" cellspacing='0' border='1' width='100%'>
                                                <thead>
                                                    <tr>
                                                        <th>Supervisor</th>
                                                        <th>Regular Payroll</th>
                                                        <th>Overtime Payroll</th>
                                                        <th>Double Payroll</th>
                                                        <th>Travel Payroll</th>
                                                        <th>Total Payroll</th>
                                                    </tr>
                                                </thead>
                                                <tbody><?php
                                                foreach($data as $Division=>$array){?><tr><?php
                                                    ?><td><?php echo $Division;?></td><?php
                                                    ?><td>$<?php echo $array['Regular_Time'];?></td><?php
                                                    ?><td>$<?php echo $array['Overtime'];?></td><?php
                                                    ?><td>$<?php echo $array['Doubletime'];?></td><?php
                                                    ?><td>$<?php echo $array['Travel_Time'];?></td><?php
                                                    ?><td>$<?php echo $array['Total'];?></td><?php
                                                ?></tr><?php }
                                                ?><tr style='border-top:2px solid black;'><?php
                                                    ?><td>All Supervisors</td><?php
                                                    ?><td>$<?php echo $Total_Regular_Time;?></td><?php
                                                    ?><td>$<?php echo $Total_Overtime;?></td><?php
                                                    ?><td>$<?php echo $Total_Doubletime;?></td><?php
                                                    ?><td>$<?php echo $Total_Travel_Time;?></td><?php
                                                    ?><td>$<?php echo $Total_Total;?></td><?php
                                                ?></tr><?php
                                                ?></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class='row'>
                                        <?php
                                        $data = array();
                                        $data2 = array();
                                        $job_result = sqlsrv_query($NEI,"
                                            SELECT 
                                                Job.Custom1            AS Supervisor, 
                                                Sum(TicketD.Reg * PRWage.Reg)    AS Regular_Time, 
                                                Sum(TicketD.OT * PRWage.OT1)     AS Overtime, 
                                                Sum(TicketD.DT * PRWage.OT2)     AS Doubletime, 
                                                Sum(TicketD.TT * PRWage.Reg)     AS Travel_Time, 
                                                Sum(TicketD.Total)  AS Total
                                            FROM 
                                                (((TicketD 
                                                LEFT JOIN nei.dbo.Job ON Job.ID = TicketD.Job) 
                                                LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc)
                                                LEFT JOIN Emp ON TicketD.fWork = Emp.fWork)
                                                LEFT JOIN PRWage ON Emp.WageCat = PRWage.ID
                                            WHERE Job.Type='2' AND TicketD.EDate >= '2017-07-01 00:00:00.000' AND TicketD.EDate < '2017-10-01 00:00:00.000' AND TicketD.Total <= 24
                                            GROUP BY Job.Custom1
                                        ;");
                                        if($job_result){
                                            $Jobs = array();
                                            $Total_Regular_Time = 0;
                                            $Total_Overtime = 0;
                                            $Total_Doubletime = 0;
                                            $Total_Travel_Time = 0;
                                            $Total_Total = 0;
                                            while($Job = sqlsrv_fetch_array($job_result)){$Jobs[] = $Job;}
                                            foreach($Jobs as $Job){
                                                if($Job['Total'] == ''){continue;}
                                                $Division = $Job['Supervisor'];
                                                if(!isset($data[$Division])){
                                                    $data[$Division] = array(
                                                        'Regular_Time'=>0,
                                                        'Overtime'=>0,
                                                        'Doubletime'=>0,
                                                        'Travel_Time'=>0,
                                                        'Total'=>0
                                                    );
                                                }
                                                $data[$Division]['Regular_Time'] += $Job['Regular_Time'];
                                                $data[$Division]['Overtime'] += $Job['Overtime'];
                                                $data[$Division]['Doubletime'] += $Job['Doubletime'];
                                                $data[$Division]['Travel_Time'] += $Job['Travel_Time'];
                                                $data[$Division]['Total'] += $Job['Total'];
                                                $Total_Regular_Time += $Job['Regular_Time'];
                                                $Total_Overtime += $Job['Overtime'];
                                                $Total_Doubletime += $Job['Doubletime'];
                                                $Total_Travel_Time += $Job['Travel_Time'];
                                                $Total_Total += $Job['Total'];
                                            }
                                        }
                                        echo implode(",",$data2);
                                        ?>
                                        <div style='padding:15px;'>
                                            <h3>Quarter 3 Payroll by Supervisor</h3>
                                            <table class="display" cellspacing='0' border='1' width='100%'>
                                                <thead>
                                                    <tr>
                                                        <th>Supervisor</th>
                                                        <th>Regular Payroll</th>
                                                        <th>Overtime Payroll</th>
                                                        <th>Double Payroll</th>
                                                        <th>Travel Payroll</th>
                                                        <th>Total Payroll</th>
                                                    </tr>
                                                </thead>
                                                <tbody><?php
                                                foreach($data as $Division=>$array){?><tr><?php
                                                    ?><td><?php echo $Division;?></td><?php
                                                    ?><td>$<?php echo $array['Regular_Time'];?></td><?php
                                                    ?><td>$<?php echo $array['Overtime'];?></td><?php
                                                    ?><td>$<?php echo $array['Doubletime'];?></td><?php
                                                    ?><td>$<?php echo $array['Travel_Time'];?></td><?php
                                                    ?><td>$<?php echo $array['Total'];?></td><?php
                                                ?></tr><?php }
                                                ?><tr style='border-top:2px solid black;'><?php
                                                    ?><td>All Supervisors</td><?php
                                                    ?><td>$<?php echo $Total_Regular_Time;?></td><?php
                                                    ?><td>$<?php echo $Total_Overtime;?></td><?php
                                                    ?><td>$<?php echo $Total_Doubletime;?></td><?php
                                                    ?><td>$<?php echo $Total_Travel_Time;?></td><?php
                                                    ?><td>$<?php echo $Total_Total;?></td><?php
                                                ?></tr><?php
                                                ?></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade in" id="financials-pills">
                                    <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-modernization-profit"></div></div>
                                    <table id="Table_Profit" class="display" cellspacing='0' width='100%'>
                                        <thead style='border:1px solid black;'>
                                            <th>&nbsp;</th>
                                            <th>2012</th>
                                            <th>2013</th>
                                            <th>2014</th>
                                            <th>2015</th>
                                            <th>2016</th>
                                            <th>2017</th>
                                            <th>3 Year</th>
                                            <th>5 Year</th>
                                        </thead>
                                        <tbody style='border:1px solid black;'><?php if(isset($SQL_Jobs) || TRUE){?>
                                            <tr>
                                                <td><b>Revenue</b></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2012
                                                        FROM 
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2012-01-01 00:00:00.000' AND Invoice.fDate < '2013-01-01 00:00:00.000' AND Job.Type = '2'
                                                    ;");
                                                    $Total_Revenue_2012 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2012'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2012);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2013
                                                        FROM 
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2014-01-01 00:00:00.000' AND Job.Type = '2'
                                                    ;");
                                                    $Total_Revenue_2013 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2013'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2013);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2014
                                                        FROM 
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2014-01-01 00:00:00.000' AND Invoice.fDate < '2015-01-01 00:00:00.000' AND Job.Type = '2'
                                                    ;");
                                                    $Total_Revenue_2014 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2014'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2014);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2015
                                                        FROM 
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2016-01-01 00:00:00.000' AND Job.Type = '2'
                                                    ;");
                                                    $Total_Revenue_2015 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2015'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2015);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2016
                                                        FROM 
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2016-01-01 00:00:00.000' AND Invoice.fDate < '2017-01-01 00:00:00.000' AND Job.Type = '2'
                                                    ;");
                                                    $Total_Revenue_2016 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2016'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2016);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2017
                                                        FROM 
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2017-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND Job.Type = '2'
                                                    ;");
                                                    $Total_Revenue_2017 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2017'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2017);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_3_Year
                                                        FROM 
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND Job.Type = '2'
                                                    ;");
                                                    $Total_Revenue_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_3_Year'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_3_Year);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_5_Year
                                                        FROM 
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND Job.Type = '2'
                                                    ;");
                                                    $Total_Revenue_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_5_Year'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_5_Year);
                                                ?></td>
                                            </tr>
                                            <tr>
                                                <td><b>Labor</b></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_2012
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Temp_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                                                    $r = sqlsrv_query($Paradox,"
                                                        SELECT 
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2012
                                                        FROM 
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE 
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2012-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2013-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                                                    echo money_format('%(n',$Total_Labor_2012);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_2013
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Temp_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                                                    $r = sqlsrv_query($Paradox,"
                                                        SELECT 
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2013
                                                        FROM 
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE 
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2013-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2014-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                                                    echo money_format('%(n',$Total_Labor_2013);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_2014
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Temp_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                                                    $r = sqlsrv_query($Paradox,"
                                                        SELECT 
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2014
                                                        FROM 
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE 
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2014-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2015-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                                                    echo money_format('%(n',$Total_Labor_2014);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_2015
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Temp_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                                                    $r = sqlsrv_query($Paradox,"
                                                        SELECT 
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2015
                                                        FROM 
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE 
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2015-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2016-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                                                    echo money_format('%(n',$Total_Labor_2015);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_2016
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Temp_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                                                    $r = sqlsrv_query($Paradox,"
                                                        SELECT 
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2016
                                                        FROM 
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE 
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2016-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2017-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                                                    echo money_format('%(n',$Total_Labor_2016);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_2017
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    
                                                    $Temp_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                                                    $r = sqlsrv_query($Paradox,"
                                                        SELECT 
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2017
                                                        FROM 
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE 
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2017-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2017-03-30 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_2017
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ");
                                                    $Total_Labor_2017 = $r ? $Total_Labor_2017 + sqlsrv_fetch_array($r)['Total_Labor_2017'] : $Total_Labor_3_Year;
                                                    echo money_format('%(n',$Total_Labor_2017);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_3_Year
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Temp_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                                                    $r = sqlsrv_query($Paradox,"
                                                        SELECT 
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_3_Year
                                                        FROM 
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE 
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2015-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2017-03-30 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_3_Year
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ");
                                                    $Total_Labor_3_Year = $r ? $Total_Labor_3_Year + sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : $Total_Labor_3_Year;
                                                    echo money_format('%(n',$Total_Labor_3_Year);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_5_Year
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Temp_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                                                    $r = sqlsrv_query($Paradox,"
                                                        SELECT 
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_5_Year
                                                        FROM 
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE 
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2013-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2017-03-30 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Labor_5_Year
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ");
                                                    $Total_Labor_5_Year = $r ? $Total_Labor_5_Year + sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : $Total_Labor_5_Year;
                                                    echo money_format('%(n',$Total_Labor_5_Year);
                                                ?></td>
                                            </tr>
                                            <tr style='border-bottom:1px solid black;'>
                                                <td><b>Materials</b></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Materials_2012
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE  
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Materials_2012 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2012'] - $Temp_Labor_2012 : 0;
                                                    echo money_format('%(n',$Total_Materials_2012);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Materials_2013
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Materials_2013 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2013'] - $Temp_Labor_2013 : 0;
                                                    echo money_format('%(n',$Total_Materials_2013);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Materials_2014
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Materials_2014 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2014'] - $Temp_Labor_2014 : 0;
                                                    echo money_format('%(n',$Total_Materials_2014);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Materials_2015
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Materials_2015 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2015'] - $Temp_Labor_2015 : 0;
                                                    echo money_format('%(n',$Total_Materials_2015);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Materials_2016
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Materials_2016 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2016'] - $Temp_Labor_2016 : 0;
                                                    echo money_format('%(n',$Total_Materials_2016);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Materials_2017
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Materials_2017 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2017'] - $Temp_Labor_2017 : 0;
                                                    echo money_format('%(n',$Total_Materials_2017);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Materials_3_Year
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Materials_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Materials_3_Year'] - $Temp_Labor_3_Year : 0;
                                                    echo money_format('%(n',$Total_Materials_3_Year);
                                                ?></td>
                                                <td><?php 
                                                    $r = sqlsrv_query($NEI,"
                                                        SELECT 
                                                            Sum(JobI.Amount) AS Total_Materials_5_Year
                                                        FROM 
                                                            (Loc 
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE 
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND Job.Type = '2'
                                                    ;");
                                                    $Total_Materials_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Materials_5_Year'] - $Temp_Labor_5_Year : 0;
                                                    echo money_format('%(n',$Total_Materials_5_Year);
                                                ?></td>
                                            </tr>
                                            <tr>
                                                <td><b>Net Income</b></td>
                                                <td><?php
                                                    $Total_Net_Income_2012 = $Total_Revenue_2012 - ($Total_Labor_2012 + $Total_Materials_2012);
                                                    echo substr(money_format('%(n',$Total_Net_Income_2012),0,99);
                                                ?></td>
                                                <td><?php
                                                    $Total_Net_Income_2013 = $Total_Revenue_2013 - ($Total_Labor_2013 + $Total_Materials_2013);
                                                    echo substr(money_format('%(n',$Total_Net_Income_2013),0,99);
                                                ?></td>
                                                <td><?php
                                                    $Total_Net_Income_2014 = $Total_Revenue_2014 - ($Total_Labor_2014 + $Total_Materials_2014);
                                                    echo substr(money_format('%(n',$Total_Net_Income_2014),0,99);
                                                ?></td>
                                                <td><?php
                                                    $Total_Net_Income_2015 = $Total_Revenue_2015 - ($Total_Labor_2015 + $Total_Materials_2015);
                                                    echo substr(money_format('%(n',$Total_Net_Income_2015),0,99);
                                                ?></td>
                                                <td><?php
                                                    $Total_Net_Income_2016 = $Total_Revenue_2016 - ($Total_Labor_2016 + $Total_Materials_2016);
                                                    echo substr(money_format('%(n',$Total_Net_Income_2016),0,99);
                                                ?></td>
                                                <td><?php
                                                    $Total_Net_Income_2017 = $Total_Revenue_2017 - ($Total_Labor_2017 + $Total_Materials_2017);
                                                    echo substr(money_format('%(n',$Total_Net_Income_2017),0,99);
                                                ?></td>
                                                <td><?php
                                                    $Total_Net_Income_3_Year = $Total_Revenue_3_Year - ($Total_Labor_3_Year + $Total_Materials_3_Year);
                                                    echo substr(money_format('%(n',$Total_Net_Income_3_Year),0,99);
                                                ?></td>
                                                <td><?php
                                                    $Total_Net_Income_5_Year = $Total_Revenue_5_Year - ($Total_Labor_5_Year + $Total_Materials_5_Year);
                                                    echo substr(money_format('%(n',$Total_Net_Income_5_Year),0,99);
                                                ?></td>
                                            </tr>
                                            <tr>
                                                <td><b>Overhead %</b></td>
                                                <td>16.08%</td>
                                                <td>14.50%</td>
                                                <td>17.70%</td>
                                                <td>17.91%</td>
                                                <td>15.20%</td>
                                                <td>16.20%</td>
                                                <td>Cumulative</td>
                                                <td>Cumulative</td>
                                            </tr>
                                            <tr style='border-bottom:1px solid black;'>
                                                <td><b>Overhead Cost</b></td>
                                                <td><?php 
                                                    $Overhead_Cost_2012 = $Total_Revenue_2012 * .1608;
                                                    echo money_format('%(n',$Overhead_Cost_2012);
                                                ?></td>
                                                <td><?php 
                                                    $Overhead_Cost_2013 = $Total_Revenue_2013 * .1450;
                                                    echo money_format('%(n',$Overhead_Cost_2013);
                                                ?></td>
                                                <td><?php 
                                                    $Overhead_Cost_2014 = $Total_Revenue_2014 * .1770;
                                                    echo money_format('%(n',$Overhead_Cost_2014);
                                                ?></td>
                                                <td><?php 
                                                    $Overhead_Cost_2015 = $Total_Revenue_2015 * .1791;
                                                    echo money_format('%(n',$Overhead_Cost_2015);
                                                ?></td>
                                                <td><?php 
                                                    $Overhead_Cost_2016 = $Total_Revenue_2016 * .1520;
                                                    echo money_format('%(n',$Overhead_Cost_2016);
                                                ?></td>
                                                <td><?php 
                                                    $Overhead_Cost_2017 = $Total_Revenue_2017 * .1620;
                                                    echo money_format('%(n',$Overhead_Cost_2017);
                                                ?></td>
                                                <td><?php 
                                                    $Overhead_Cost_3_Year = $Overhead_Cost_2015 + $Overhead_Cost_2016 + $Overhead_Cost_2017;
                                                    echo money_format('%(n',$Overhead_Cost_3_Year);
                                                ?></td>
                                                <td><?php 
                                                    $Overhead_Cost_5_Year = $Overhead_Cost_2013 + $Overhead_Cost_2014 + $Overhead_Cost_3_Year;
                                                    echo money_format('%(n',$Overhead_Cost_5_Year);
                                                ?></td>
                                            </tr>
                                            <tr>
                                                <td><b>Profit</b></td>
                                                <td><?php 
                                                    $Total_Profit_2012 = $Total_Revenue_2012 - ($Total_Labor_2012 + $Total_Materials_2012 + $Overhead_Cost_2012);
                                                    echo money_format('%(n',$Total_Profit_2012);
                                                ?></td>
                                                <td><?php 
                                                    $Total_Profit_2013 = $Total_Revenue_2013 - ($Total_Labor_2013 + $Total_Materials_2013 + $Overhead_Cost_2013);
                                                    echo money_format('%(n',$Total_Profit_2013);
                                                ?></td>
                                                <td><?php 
                                                    $Total_Profit_2014 = $Total_Revenue_2014 - ($Total_Labor_2014 + $Total_Materials_2014 + $Overhead_Cost_2014);
                                                    echo money_format('%(n',$Total_Profit_2014);
                                                ?></td>
                                                <td><?php 
                                                    $Total_Profit_2015 = $Total_Revenue_2015 - ($Total_Labor_2015 + $Total_Materials_2015 + $Overhead_Cost_2015);
                                                    echo money_format('%(n',$Total_Profit_2015);
                                                ?></td>
                                                <td><?php 
                                                    $Total_Profit_2016 = $Total_Revenue_2016 - ($Total_Labor_2016 + $Total_Materials_2016 + $Overhead_Cost_2016);
                                                    echo money_format('%(n',$Total_Profit_2016);
                                                ?></td>
                                                <td><?php 
                                                    $Total_Profit_2017 = $Total_Revenue_2017 - ($Total_Labor_2017 + $Total_Materials_2017 + $Overhead_Cost_2017);
                                                    echo money_format('%(n',$Total_Profit_2017);
                                                ?></td>
                                                <td><?php 
                                                    $Total_Profit_3_Year = $Total_Revenue_3_Year - ($Total_Labor_3_Year + $Total_Materials_3_Year + $Overhead_Cost_3_Year);
                                                    echo money_format('%(n',$Total_Profit_3_Year);
                                                ?></td>
                                                <td><?php 
                                                    $Total_Profit_5_Year = $Total_Revenue_5_Year - ($Total_Labor_5_Year + $Total_Materials_5_Year + $Overhead_Cost_5_Year);
                                                    echo money_format('%(n',$Total_Profit_5_Year);
                                                ?></td>
                                            </tr>
                                        </tbody><?php }?>
                                    </table>
                                    <h3>Profit by Supervisor</h3>
                                    <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-modernization-profit-by-supervisor"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>    

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>
    
    <script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>
    <?php require(PROJECT_ROOT."js/chart/modernization_jobs.php");?>
    <?php require(PROJECT_ROOT."js/pie/modernization_jobs_by_supervisor.php");?>
    <?php require(PROJECT_ROOT."js/pie/modernization_jobs_hours_by_supervisor.php");?>
    <?php require(PROJECT_ROOT."js/chart/modernization_profit.php");?>
    <?php require(PROJECT_ROOT."js/chart/modernization_hours_by_supervisor.php");?>
    <?php require(PROJECT_ROOT."js/chart/modernization_hours_payroll_by_supervisor.php");?>
    <?php require(PROJECT_ROOT."js/chart/modernization_profit_by_supervisor.php");?>
    
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>