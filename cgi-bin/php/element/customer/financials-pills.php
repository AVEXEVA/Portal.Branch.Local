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
        $Field = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
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
<?php if((!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator') && (isset($My_Privileges['Financials']) && $My_Privileges['Financials']['Other_Privilege'] >= 4)){?>
<div class="tab-pane fade in" id="financials-pills">
    <ul class="nav nav-tabs">
        <li class="active"><a href="#financials-summary-pills" >Summary</a></li>
        <li><a href="#financials-location-analysis-pills" >Location Anaylsis</a></li>
        <li><a href="#financials-job-analysis-pills" >Job Anaylsis</a></li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade in active" id="financials-summary-pills">
            <div class='row'><div class="col-lg-12">
                <div class="panel panel-red">
                    <div class="panel-heading">Profit</div>
                    <div class="panel-body">
                        <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-profit"></div></div>
                    </div>
                </div>  
            </div></div>
            <style>
            table#Table_Profit thead tr th {
                text-align:center;
            }
            table#Table_Profit tbody td {
                padding:3px;
                text-align:center;
            }
            </style>
            <table id="Table_Profit" class="display" cellspacing='0' width='100%'>
                <thead style='border:1px solid black;'>
                    <th></th>
                    <th>2012</th>
                    <th>2013</th>
                    <th>2014</th>
                    <th>2015</th>
                    <th>2016</th>
                    <th>2017</th>
                    <th>3 Year</th>
                    <th>5 Year</th>
                </thead>
                <tbody style='border:1px solid black;'><?php if(isset($SQL_Jobs)){?>
                    <tr>
                        <td><b>Revenue</b></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(Amount) AS Total_Revenue_2012
                                FROM 
                                    Invoice
                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                                WHERE Invoice.fDate >= '2012-01-01 00:00:00.000' AND Invoice.fDate < '2013-01-01 00:00:00.000' AND Loc.Owner='{$_GET['ID']}'
                            ;");
                            $Total_Revenue_2012 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2012'] : 0;
                            echo money_format('%(n',$Total_Revenue_2012);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(Amount) AS Total_Revenue_2013
                                FROM 
                                    Invoice
                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                                WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2014-01-01 00:00:00.000' AND Loc.Owner='{$_GET['ID']}'
                            ;");
                            $Total_Revenue_2013 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2013'] : 0;
                            echo money_format('%(n',$Total_Revenue_2013);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(Amount) AS Total_Revenue_2014
                                FROM 
                                    Invoice
                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                                WHERE Invoice.fDate >= '2014-01-01 00:00:00.000' AND Invoice.fDate < '2015-01-01 00:00:00.000' AND Loc.Owner='{$_GET['ID']}'
                            ;");
                            $Total_Revenue_2014 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2014'] : 0;
                            echo money_format('%(n',$Total_Revenue_2014);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(Amount) AS Total_Revenue_2015
                                FROM 
                                    Invoice
                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                                WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2016-01-01 00:00:00.000' AND Loc.Owner='{$_GET['ID']}'
                            ;");
                            $Total_Revenue_2015 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2015'] : 0;
                            echo money_format('%(n',$Total_Revenue_2015);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(Amount) AS Total_Revenue_2016
                                FROM 
                                    Invoice
                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                                WHERE Invoice.fDate >= '2016-01-01 00:00:00.000' AND Invoice.fDate < '2017-01-01 00:00:00.000' AND Loc.Owner='{$_GET['ID']}'
                            ;");
                            $Total_Revenue_2016 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2016'] : 0;
                            echo money_format('%(n',$Total_Revenue_2016);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(Amount) AS Total_Revenue_2017
                                FROM 
                                    Invoice
                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                                WHERE Invoice.fDate >= '2017-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND Loc.Owner='{$_GET['ID']}'
                            ;");
                            $Total_Revenue_2017 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2017'] : 0;
                            echo money_format('%(n',$Total_Revenue_2017);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(Amount) AS Total_Revenue_3_Year
                                FROM 
                                    Invoice
                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                                WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND Loc.Owner='{$_GET['ID']}'
                            ;");
                            $Total_Revenue_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_3_Year'] : 0;
                            echo money_format('%(n',$Total_Revenue_3_Year);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(Invoice.Amount) AS Total_Revenue_5_Year
                                FROM 
                                    nei.dbo.Invoice
                                    LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                                WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' 
								AND Invoice.fDate   <  '2018-01-01 00:00:00.000' 
								AND Loc.Owner='{$_GET['ID']}'
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
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                            $r = sqlsrv_query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2012
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$SQL_Jobs})
                                    AND convert(date,[WEEK ENDING]) >= '2012-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2013-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                            echo money_format('%(n',$Total_Labor_2012);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2013
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                            $r = sqlsrv_query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2013
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$SQL_Jobs})
                                    AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2014-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                            echo money_format('%(n',$Total_Labor_2013);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2014
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                            $r = sqlsrv_query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2014
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$SQL_Jobs})
                                    AND convert(date,[WEEK ENDING]) >= '2014-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2015-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                            echo money_format('%(n',$Total_Labor_2014);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2015
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                            $r = sqlsrv_query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2015
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$SQL_Jobs})
                                    AND convert(date,[WEEK ENDING]) >= '2015-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2016-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                            echo money_format('%(n',$Total_Labor_2015);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2016
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                            $r = sqlsrv_query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2016
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$SQL_Jobs})
                                    AND convert(date,[WEEK ENDING]) >= '2016-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                            echo money_format('%(n',$Total_Labor_2016);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2017
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            
                            $Temp_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                            $r = sqlsrv_query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2017
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$SQL_Jobs})
                                    AND convert(date,[WEEK ENDING]) >= '2017-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                            ;");
                            $Total_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                            $r = sqlsrv_query($NEI,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2017
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ");
                            $Total_Labor_2017 = $r ? $Total_Labor_2017 + sqlsrv_fetch_array($r)['Total_Labor_2017'] : $Total_Labor_3_Year;
                            echo money_format('%(n',$Total_Labor_2017);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_3_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                            $r = sqlsrv_query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_3_Year
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$SQL_Jobs})
                                    AND convert(date,[WEEK ENDING]) >= '2015-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                            ;");
                            $Total_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                            $r = sqlsrv_query($NEI,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_3_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ");
                            $Total_Labor_3_Year = $r ? $Total_Labor_3_Year + sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : $Total_Labor_3_Year;
                            echo money_format('%(n',$Total_Labor_3_Year);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_5_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                            $r = sqlsrv_query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_5_Year
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$SQL_Jobs})
                                    AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                            ;");
                            $Total_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                            $r = sqlsrv_query($NEI,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_5_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ");
                            $Total_Labor_5_Year = $r ? $Total_Labor_5_Year + sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : $Total_Labor_5_Year;
                            echo money_format('%(n',$Total_Labor_5_Year);
                        ?></td>
                    </tr>
                    <tr style='border-bottom:1px solid black;'>
                        <td><b>Materials</b></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2012
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2012 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2012'] - $Temp_Labor_2012 : 0;
                            echo money_format('%(n',$Total_Materials_2012);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2013
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2013 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2013'] - $Temp_Labor_2013 : 0;
                            echo money_format('%(n',$Total_Materials_2013);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2014
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2014 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2014'] - $Temp_Labor_2014 : 0;
                            echo money_format('%(n',$Total_Materials_2014);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2015
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2015 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2015'] - $Temp_Labor_2015 : 0;
                            echo money_format('%(n',$Total_Materials_2015);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2016
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2016 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2016'] - $Temp_Labor_2016 : 0;
                            echo money_format('%(n',$Total_Materials_2016);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2017
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2017 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2017'] - $Temp_Labor_2017 : 0;
                            echo money_format('%(n',$Total_Materials_2017);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_3_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Materials_3_Year'] - $Temp_Labor_3_Year : 0;
                            echo money_format('%(n',$Total_Materials_3_Year);
                        ?></td>
                        <td><?php 
                            $r = sqlsrv_query($NEI,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_5_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    Loc.Owner='{$_GET['ID']}'
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
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
            <br />
            <div class='row'><div class="col-lg-12">
                <div class="panel panel-blue">
                    <div class="panel-heading">Profit</div>
                    <div class="panel-body">
                        <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-profit-billed-ratio"></div></div>
                    </div>
                </div>  
            </div></div>
        </div>
        <div class="tab-pane fade in" id="financials-job-analysis-pills">  
            <div id='Job_Analysis'>&nbsp;</div>
            <div class='input-group'>
                <button class='form-control' onClick="analyzeJobs();">Analyze Profit of Selected Jobs</button>
            </div>

            <br />
            <table id='Table_Jobs_for_Anaylsis' class='display' cellspacing='0' width='100%'>
                <thead>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Status</th>
                </thead>
               <tfooter>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Status</th>
                </tfooter>
            </table>
        </div>
        <div class="tab-pane fade in" id="financials-location-analysis-pills">  
            <div id='Location_Analysis'>&nbsp;</div>
            <div class='input-group'>
                <button class='form-control' onClick="analyzeLocations();">Analyze Profit of Selected Locations</button>
            </div>

            <br />
            <table id='Table_Locations_for_Anaylsis' class='display' cellspacing='0' width='100%'>
                <thead>
                    <th title="Location's ID">ID</th>
                    <th title="Location's Name State ID">Name</th>
                    <th title="Location's Tag">Tag</th>
                    <th title="Location's Street">Street</th>
                    <th title="Location's City">City</th>
                    <th title="Location's State">State</th>
                    <th title="Location's Zip">Zip</th>
                    <th title="Location's Route">Route</th>
                    <th title="Location's Zone">Zone</th>
                </thead>
               <tfooter><th title="Location's ID">ID</th><th title="Location's Name State ID">Name</th><th title="Location's Tag">Tag</th><th title="Location's Street">Street</th><th title="Location's City">City</th><th title="Location's State">State</th><th title="Location's Zip">Zip</th><th title="Location's Route">Route</th><th title="Location's Zone">Zone</th></tfooter>
            </table>
        </div>
    </div>
</div>
<script>
$(document).ready(function(){
	$("#loading-pills").removeClass("active");
	$("#financials-pills").addClass('active');
});
</script>
<?php }?>
    <!-- /#wrapper -->
    <?php if((!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator') && (isset($My_Privileges['Financials']) && $My_Privileges['Financials']['Other_Privilege'] >= 4)){
        require(PROJECT_ROOT."js/chart/customer_profit.php");
    }?>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>