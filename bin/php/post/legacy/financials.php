 <?php 
session_start( [ 'read_and_close' => true ] );
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Financials']) && $My_Privileges['Financials']['Owner'] >= 4 && $My_Privileges['Financials']['Group'] >= 4 && $My_Privileges['Financials']['Other'] >= 4){$Privileged = TRUE;}
    if(!isset($array['ID'])  || !$Privileged || count($_GET) == 0){?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }
    else {
        if(!isset($_SESSION['Financials'])){
            $_SESSION['Financials']             = array();
            $_SESSION['Financials']['Add']      = array();
            $_SESSION['Financials']['Subtract'] = array();
        }
        if(count($_GET) > 0){
            $index                                                         = count($_SESSION['Financials'][$_GET['Operator']]);
            $_SESSION['Financials'][$_GET['Operator']][$index]             = array();
            $_SESSION['Financials'][$_GET['Operator']][$index]['Customer'] = $_GET['Customer'];
            $_SESSION['Financials'][$_GET['Operator']][$index]['Location'] = $_GET['Location'];
            $_SESSION['Financials'][$_GET['Operator']][$index]['JobType']  = $_GET['JobType'];
            $_SESSION['Financials'][$_GET['Operator']][$index]['Job']      = $_GET['Job'];
            $_SESSION['Financials'][$_GET['Operator']][$index]['Unit']     = $_GET['Unit'];
	    }
	    //Establish Jobs
	    $Jobs = array();
        if(count($_SESSION['Financials']['Add']) > 0){foreach($_SESSION['Financials']['Add'] as $index=>$array){
            //Establish Parameters
            $Customer = ($array['Customer'] != '') ? $array['Customer'] : FALSE;
            $Location = ($array['Location'] != '') ? $array['Location'] : FALSE;
            $JobType  = ($array['JobType']  != '') ? $array['JobType']  : FALSE;
            $Job      = ($array['Job']      != '') ? $array['Job']      : FALSE;
            $Unit     = ($array['Unit']     != '') ? $array['Unit']     : FALSE;

            //Build SQL Where String
            $SQL_Where = array();
            if($Customer){ $SQL_Where[] = "Job.Owner = '{$Customer}'";}
            if($Location){ $SQL_Where[] = "Job.Loc   = '{$Location}'";}
            if(is_numeric($JobType)){  $SQL_Where[] = "Job.Type  = '{$JobType}'";}
            if($Job){      $SQL_Where[] = "Job.ID    = '{$Job}'";}
            if($Unit){     $SQL_Where[] = "Job.Elev  = '{$Unit}'";}
            $SQL_Where = implode(" AND ",$SQL_Where);
            //Check if parameters are set if not escape
            if($SQL_Where != ''){
                //Query
                $r = $database->query(null,"SELECT * FROM Job WHERE {$SQL_Where}");
                //Append Jobs
                if($r){while($array = sqlsrv_fetch_array($r)){$Jobs[$array['ID']] = $array['ID'];}}
            } else {continue;} 
        }}
        $Jobs2 = array();
        if(count($_SESSION['Financials']['Subtract']) > 0){foreach($_SESSION['Financials']['Subtract'] as $index=>$array){
            //Establish Parameters
            $Customer = ($array['Customer'] != '') ? $array['Customer'] : FALSE;
            $Location = ($array['Location'] != '') ? $array['Location'] : FALSE;
            $JobType  = ($array['JobType']  != '') ? $array['JobType']  : FALSE;
            $Job      = ($array['Job']      != '') ? $array['Job']      : FALSE;
            $Unit     = ($array['Unit']     != '') ? $array['Unit']     : FALSE;
            //Build SQL Where String
            $SQL_Where2 = array();
            if($Customer){ $SQL_Where2[] = "Job.Owner = '{$Customer}'";}
            if($Location){ $SQL_Where2[] = "Job.Loc   = '{$Location}'";}
            if(is_numeric($JobType)){  $SQL_Where2[] = "Job.Type  = '{$JobType}'";}
            if($Job){      $SQL_Where2[] = "Job.ID    = '{$Job}'";}
            if($Unit){     $SQL_Where2[] = "Job.Elev  = '{$Unit}'";}
            
            $SQL_Where2 = implode(" AND ",$SQL_Where2);

            //Check if parameters are set if not escape
            if($SQL_Where2 != ''){
                //Query
                $r = $database->query(null,"SELECT * FROM Job WHERE {$SQL_Where2}");
                //Append Jobs
                if($r){while($array = sqlsrv_fetch_array($r)){$Jobs2[$array['ID']] = $array['ID'];}}
            } else {continue;} 
        }}

	    if(count($Jobs) > 0 || count($Jobs2) > 0){
	    	if(count($Jobs) > 0){
                $Jobs_SQL_Invoices = array();
                foreach($Jobs as $Job){$Jobs_SQL_Invoices[] = "Invoice.Job = '{$Job}'";}
                $Jobs_SQL_Invoices = implode(" OR ",$Jobs_SQL_Invoices);

                $Jobs_SQL_Job = array();
                foreach($Jobs as $Job){$Jobs_SQL_Job[] = "Job.ID = '{$Job}'";}
                $Jobs_SQL_Job = implode(" OR ",$Jobs_SQL_Job);

                $Jobs_SQL_JobI = array();
                foreach($Jobs as $Job){$Jobs_SQL_JobI[] = "JobI.Job = '{$Job}'";}
                $Jobs_SQL_JobI = implode(" OR ",$Jobs_SQL_JobI);

                $Jobs_SQL_Labor = array();
                foreach($Jobs as $Job){$Jobs_SQL_Labor[] = "[JOBLABOR].[JOB #]='{$Job}'";}
                $Jobs_SQL_Labor = implode(" OR ",$Jobs_SQL_Labor);
            } else {
                $Jobs_SQL_Invoices = "1 <> 1";
                $Jobs_SQL_Job      = "1 <> 1";
                $Jobs_SQL_Labor    = "1 <> 1";
            }
            if(count($Jobs2) > 0){
                $Jobs_SQL_Invoices2 = array();
                foreach($Jobs2 as $Job){$Jobs_SQL_Invoices2[] = "Invoice.Job = '{$Job}'";}
                $Jobs_SQL_Invoices2 = implode(" OR ",$Jobs_SQL_Invoices2);

                $Jobs_SQL_Job2 = array();
                foreach($Jobs2 as $Job){$Jobs_SQL_Job2[] = "Job.ID = '{$Job}'";}
                $Jobs_SQL_Job2 = implode(" OR ",$Jobs_SQL_Job2);

                $Jobs_SQL_JobI2 = array();
                foreach($Jobs2 as $Job){$Jobs_SQL_JobI2[] = "JobI.Job = '{$Job}'";}
                $Jobs_SQL_JobI2 = implode(" OR ",$Jobs_SQL_JobI2);

                $Jobs_SQL_Labor2 = array();
                foreach($Jobs2 as $Job){$Jobs_SQL_Labor2[] = "[JOBLABOR].[JOB #]='{$Job}'";}
                $Jobs_SQL_Labor2 = implode(" OR ",$Jobs_SQL_Labor2);
            } else {
                $Jobs_SQL_Invoices2 = "1 <> 1";
                $Jobs_SQL_Job2      = "1 <> 1";
                $Jobs_SQL_Labor2    = "1 <> 1";
            }
	    	?>
	    	<style>
            table#Table_Profit thead tr th  {   text-align:center;              }
            table#Table_Profit tbody td     {   padding:3px;text-align:center;  }
            </style>
            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-profit"></div></div>
            <?php require('../../js/chart/financials.php');?>
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
                <tbody style='border:1px solid black;'><?php if(isset($Jobs_SQL_Labor)){?>
                    <tr>
                        <td><b>Revenue</b></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2012
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2012-01-01 00:00:00.000' AND Invoice.fDate < '2013-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices})
                            ;");
                            $Total_Revenue_2012 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2012'] : 0;
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2012
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2012-01-01 00:00:00.000' AND Invoice.fDate < '2013-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices2})
                            ;");
                            $Total_Revenue_2012 -= $r ? sqlsrv_fetch_array($r)['Total_Revenue_2012'] : 0;
                            echo money_format('$%(n',$Total_Revenue_2012);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2013
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2014-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices})
                            ;");
                            $Total_Revenue_2013 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2013'] : 0;
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2013
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2014-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices2})
                            ;");
                            $Total_Revenue_2013 -= $r ? sqlsrv_fetch_array($r)['Total_Revenue_2013'] : 0;
                            echo money_format('$%(n',$Total_Revenue_2013);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2014
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2014-01-01 00:00:00.000' AND Invoice.fDate < '2015-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices})
                            ;");
                            $Total_Revenue_2014 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2014'] : 0;
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2014
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2014-01-01 00:00:00.000' AND Invoice.fDate < '2015-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices2})
                            ;");
                            $Total_Revenue_2014 -= $r ? sqlsrv_fetch_array($r)['Total_Revenue_2014'] : 0;
                            echo money_format('$%(n',$Total_Revenue_2014);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2015
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2016-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices})
                            ;");
                            $Total_Revenue_2015 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2015'] : 0;
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2015
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2016-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices2})
                            ;");
                            $Total_Revenue_2015 -= $r ? sqlsrv_fetch_array($r)['Total_Revenue_2015'] : 0;
                            echo money_format('$%(n',$Total_Revenue_2015);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2016
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2016-01-01 00:00:00.000' AND Invoice.fDate < '2017-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices})
                            ;");
                            $Total_Revenue_2016 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2016'] : 0;
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2016
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2016-01-01 00:00:00.000' AND Invoice.fDate < '2017-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices2})
                            ;");
                            $Total_Revenue_2016 -= $r ? sqlsrv_fetch_array($r)['Total_Revenue_2016'] : 0;
                            echo money_format('$%(n',$Total_Revenue_2016);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2017
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2017-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices})
                            ;");
                            $Total_Revenue_2017 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2017'] : 0;
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_2017
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2017-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices2})
                            ;");
                            $Total_Revenue_2017 -= $r ? sqlsrv_fetch_array($r)['Total_Revenue_2017'] : 0;
                            echo money_format('$%(n',$Total_Revenue_2017);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_3_Year
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices})
                            ;");
                            $Total_Revenue_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_3_Year'] : 0;
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_3_Year
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices2})
                            ;");
                            $Total_Revenue_3_Year -= $r ? sqlsrv_fetch_array($r)['Total_Revenue_3_Year'] : 0;
                            echo money_format('$%(n',$Total_Revenue_3_Year);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_5_Year
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices})
                            ;");
                            $Total_Revenue_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_5_Year'] : 0;
                            $r = $database->query(null,"
                                SELECT Sum(Amount) AS Total_Revenue_5_Year
                                FROM nei.dbo.Invoice
                                WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND ({$Jobs_SQL_Invoices2})
                            ;");
                            $Total_Revenue_5_Year -= $r ? sqlsrv_fetch_array($r)['Total_Revenue_5_Year'] : 0;
                            echo money_format('$%(n',$Total_Revenue_5_Year);
                        ?></td>
                    </tr>
                    <tr>
                        <td><b>Labor</b></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2012
                                FROM 
                                    nei.dbo.Loc 
                                    LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                    LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2012
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor})
                                    AND convert(date,[WEEK ENDING]) >= '2012-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2013-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2012
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2012 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2012
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor2})
                                    AND convert(date,[WEEK ENDING]) >= '2012-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2013-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2012 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;

                            echo money_format('$%(n',$Total_Labor_2012);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2013
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2013
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor})
                                    AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2014-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2013
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2013 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2013
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor2})
                                    AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2014-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2013 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                            echo money_format('$%(n',$Total_Labor_2013);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2014
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2014
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor})
                                    AND convert(date,[WEEK ENDING]) >= '2014-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2015-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2014
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2014 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2014
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor2})
                                    AND convert(date,[WEEK ENDING]) >= '2014-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2015-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2014 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                            echo money_format('$%(n',$Total_Labor_2014);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2015
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2015
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor})
                                    AND convert(date,[WEEK ENDING]) >= '2015-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2016-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2015
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2015 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2015
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor2})
                                    AND convert(date,[WEEK ENDING]) >= '2015-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2016-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2015 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                            echo money_format('$%(n',$Total_Labor_2015);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2016
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2016
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor})
                                    AND convert(date,[WEEK ENDING]) >= '2016-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2016
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_2016 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2016
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor2})
                                    AND convert(date,[WEEK ENDING]) >= '2016-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-01-01 00:00:00.000'
                            ;");
                            $Total_Labor_2016 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                            echo money_format('$%(n',$Total_Labor_2016);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2017
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");

                            $Temp_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2017
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor})
                                    AND convert(date,[WEEK ENDING]) >= '2017-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                            ;");
                            $Total_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2017
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ");
                            $Total_Labor_2017 = $r ? $Total_Labor_2017 + sqlsrv_fetch_array($r)['Total_Labor_2017'] : $Total_Labor_2017;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2017
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");

                            $Temp_Labor_2017 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2017
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor2})
                                    AND convert(date,[WEEK ENDING]) >= '2017-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                            ;");
                            $Total_Labor_2017 -= $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_2017
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ");
                            $Total_Labor_2017 = $r ? $Total_Labor_2017 - sqlsrv_fetch_array($r)['Total_Labor_2017'] : $Total_Labor_2017;
                            echo money_format('$%(n',$Total_Labor_2017);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_3_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_3_Year
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor})
                                    AND convert(date,[WEEK ENDING]) >= '2015-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                            ;");
                            $Total_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_3_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ");
                            $Total_Labor_3_Year = $r ? $Total_Labor_3_Year + sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : $Total_Labor_3_Year;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_3_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_3_Year -= $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_3_Year
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor2})
                                    AND convert(date,[WEEK ENDING]) >= '2015-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                            ;");
                            $Total_Labor_3_Year -= $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_3_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ");
                            $Total_Labor_3_Year = $r ? $Total_Labor_3_Year - sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : $Total_Labor_3_Year;
                            echo money_format('$%(n',$Total_Labor_3_Year);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_5_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_5_Year
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor})
                                    AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                            ;");
                            $Total_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_5_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ");
                            $Total_Labor_5_Year = $r ? $Total_Labor_5_Year + sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : $Total_Labor_5_Year;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_5_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Temp_Labor_5_Year -= $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                            $r = $database->query($Paradox,"
                                SELECT 
                                    SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_5_Year
                                FROM Paradox.dbo.JOBLABOR
                                WHERE 
                                    ({$Jobs_SQL_Labor2})
                                    AND convert(date,[WEEK ENDING]) >= '2013-01-01 00:00:00.000'
                                    AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                            ;");
                            $Total_Labor_5_Year -= $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                            $r = $database->query(null,"
                                SELECT 
                                    Sum(JobI.Amount) AS Total_Labor_5_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1' AND JobI.Labor = '1'
                                    AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ");
                            $Total_Labor_5_Year = $r ? $Total_Labor_5_Year - sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : $Total_Labor_5_Year;
                            echo money_format('$%(n',$Total_Labor_5_Year);
                        ?></td>
                    </tr>
                    <tr style='border-bottom:1px solid black;'>
                        <td><b>Materials</b></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2012
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2012 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2012'] - $Temp_Labor_2012 : 0;
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2012
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2012 -= $r ? sqlsrv_fetch_array($r)['Total_Materials_2012'] : 0;
                            echo money_format('$%(n',$Total_Materials_2012);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2013
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2013 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2013'] - $Temp_Labor_2013 : 0;
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2013
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2013 -= $r ? sqlsrv_fetch_array($r)['Total_Materials_2013'] : 0;
                            echo money_format('$%(n',$Total_Materials_2013);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2014
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2014 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2014'] - $Temp_Labor_2014 : 0;
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2014
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2014 -= $r ? sqlsrv_fetch_array($r)['Total_Materials_2014'] : 0;
                            echo money_format('$%(n',$Total_Materials_2014);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2015
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2015 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2015'] - $Temp_Labor_2015 : 0;
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2015
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2015 -= $r ? sqlsrv_fetch_array($r)['Total_Materials_2015'] : 0;
                            echo money_format('$%(n',$Total_Materials_2015);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2016
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2016 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2016'] - $Temp_Labor_2016 : 0;
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2016
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2016 -= $r ? sqlsrv_fetch_array($r)['Total_Materials_2016'] : 0;
                            echo money_format('$%(n',$Total_Materials_2016);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2017
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2017 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2017'] - $Temp_Labor_2017 : 0;
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_2017
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_2017 -= $r ? sqlsrv_fetch_array($r)['Total_Materials_2017'] : 0;
                            echo money_format('$%(n',$Total_Materials_2017);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_3_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Materials_3_Year'] - $Temp_Labor_3_Year : 0;
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_3_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_3_Year -= $r ? sqlsrv_fetch_array($r)['Total_Materials_3_Year'] : 0;
                            echo money_format('$%(n',$Total_Materials_3_Year);
                        ?></td>
                        <td><?php 
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_5_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Materials_5_Year'] - $Temp_Labor_5_Year : 0;
                            $r = $database->query(null,"
                                SELECT Sum(JobI.Amount) AS Total_Materials_5_Year
                                FROM   nei.dbo.Loc 
                                       LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc
                                       LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                WHERE 
                                    ({$Jobs_SQL_Job2})
                                    AND JobI.Type='1'
                                    AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                            ;");
                            $Total_Materials_5_Year -= $r ? sqlsrv_fetch_array($r)['Total_Materials_5_Year'] : 0;
                            echo money_format('$%(n',$Total_Materials_5_Year);
                        ?></td>
                    </tr>
                    <tr>
                        <td><b>Net Income</b></td>
                        <td><?php
                            $Total_Net_Income_2012 = $Total_Revenue_2012 - ($Total_Labor_2012 + $Total_Materials_2012);
                            echo substr(money_format('$%(n',$Total_Net_Income_2012),0,99);
                        ?></td>
                        <td><?php
                            $Total_Net_Income_2013 = $Total_Revenue_2013 - ($Total_Labor_2013 + $Total_Materials_2013);
                            echo substr(money_format('$%(n',$Total_Net_Income_2013),0,99);
                        ?></td>
                        <td><?php
                            $Total_Net_Income_2014 = $Total_Revenue_2014 - ($Total_Labor_2014 + $Total_Materials_2014);
                            echo substr(money_format('$%(n',$Total_Net_Income_2014),0,99);
                        ?></td>
                        <td><?php
                            $Total_Net_Income_2015 = $Total_Revenue_2015 - ($Total_Labor_2015 + $Total_Materials_2015);
                            echo substr(money_format('$%(n',$Total_Net_Income_2015),0,99);
                        ?></td>
                        <td><?php
                            $Total_Net_Income_2016 = $Total_Revenue_2016 - ($Total_Labor_2016 + $Total_Materials_2016);
                            echo substr(money_format('$%(n',$Total_Net_Income_2016),0,99);
                        ?></td>
                        <td><?php
                            $Total_Net_Income_2017 = $Total_Revenue_2017 - ($Total_Labor_2017 + $Total_Materials_2017);
                            echo substr(money_format('$%(n',$Total_Net_Income_2017),0,99);
                        ?></td>
                        <td><?php
                            $Total_Net_Income_3_Year = $Total_Revenue_3_Year - ($Total_Labor_3_Year + $Total_Materials_3_Year);
                            echo substr(money_format('$%(n',$Total_Net_Income_3_Year),0,99);
                        ?></td>
                        <td><?php
                            $Total_Net_Income_5_Year = $Total_Revenue_5_Year - ($Total_Labor_5_Year + $Total_Materials_5_Year);
                            echo substr(money_format('$%(n',$Total_Net_Income_5_Year),0,99);
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
                            echo money_format('$%(n',$Overhead_Cost_2012);
                        ?></td>
                        <td><?php 
                            $Overhead_Cost_2013 = $Total_Revenue_2013 * .1450;
                            echo money_format('$%(n',$Overhead_Cost_2013);
                        ?></td>
                        <td><?php 
                            $Overhead_Cost_2014 = $Total_Revenue_2014 * .1770;
                            echo money_format('$%(n',$Overhead_Cost_2014);
                        ?></td>
                        <td><?php 
                            $Overhead_Cost_2015 = $Total_Revenue_2015 * .1791;
                            echo money_format('$%(n',$Overhead_Cost_2015);
                        ?></td>
                        <td><?php 
                            $Overhead_Cost_2016 = $Total_Revenue_2016 * .1520;
                            echo money_format('$%(n',$Overhead_Cost_2016);
                        ?></td>
                        <td><?php 
                            $Overhead_Cost_2017 = $Total_Revenue_2017 * .1620;
                            echo money_format('$%(n',$Overhead_Cost_2017);
                        ?></td>
                        <td><?php 
                            $Overhead_Cost_3_Year = $Overhead_Cost_2015 + $Overhead_Cost_2016 + $Overhead_Cost_2017;
                            echo money_format('$%(n',$Overhead_Cost_3_Year);
                        ?></td>
                        <td><?php 
                            $Overhead_Cost_5_Year = $Overhead_Cost_2013 + $Overhead_Cost_2014 + $Overhead_Cost_3_Year;
                            echo money_format('$%(n',$Overhead_Cost_5_Year);
                        ?></td>
                    </tr>
                    <tr>
                        <td><b>Profit</b></td>
                        <td><?php 
                            $Total_Profit_2012 = $Total_Revenue_2012 - ($Total_Labor_2012 + $Total_Materials_2012 + $Overhead_Cost_2012);
                            echo money_format('$%(n',$Total_Profit_2012);
                        ?></td>
                        <td><?php 
                            $Total_Profit_2013 = $Total_Revenue_2013 - ($Total_Labor_2013 + $Total_Materials_2013 + $Overhead_Cost_2013);
                            echo money_format('$%(n',$Total_Profit_2013);
                        ?></td>
                        <td><?php 
                            $Total_Profit_2014 = $Total_Revenue_2014 - ($Total_Labor_2014 + $Total_Materials_2014 + $Overhead_Cost_2014);
                            echo money_format('$%(n',$Total_Profit_2014);
                        ?></td>
                        <td><?php 
                            $Total_Profit_2015 = $Total_Revenue_2015 - ($Total_Labor_2015 + $Total_Materials_2015 + $Overhead_Cost_2015);
                            echo money_format('$%(n',$Total_Profit_2015);
                        ?></td>
                        <td><?php 
                            $Total_Profit_2016 = $Total_Revenue_2016 - ($Total_Labor_2016 + $Total_Materials_2016 + $Overhead_Cost_2016);
                            echo money_format('$%(n',$Total_Profit_2016);
                        ?></td>
                        <td><?php 
                            $Total_Profit_2017 = $Total_Revenue_2017 - ($Total_Labor_2017 + $Total_Materials_2017 + $Overhead_Cost_2017);
                            echo money_format('$%(n',$Total_Profit_2017);
                        ?></td>
                        <td><?php 
                            $Total_Profit_3_Year = $Total_Revenue_3_Year - ($Total_Labor_3_Year + $Total_Materials_3_Year + $Overhead_Cost_3_Year);
                            echo money_format('$%(n',$Total_Profit_3_Year);
                        ?></td>
                        <td><?php 
                            $Total_Profit_5_Year = $Total_Revenue_5_Year - ($Total_Labor_5_Year + $Total_Materials_5_Year + $Overhead_Cost_5_Year);
                            echo money_format('$%(n',$Total_Profit_5_Year);
                        ?></td>
                    </tr>
                    <tr>
                    	<?php function percent($number){return number_format($number * 100,2) . '%';}?>
                    	<td><b>Profit Percentage</b></td>
                    	<td><?php echo percent($Total_Profit_2012 / $Total_Revenue_2012);?></td>
                    	<td><?php echo percent($Total_Profit_2013 / $Total_Revenue_2013);?></td>
                    	<td><?php echo percent($Total_Profit_2014 / $Total_Revenue_2014);?></td>
                    	<td><?php echo percent($Total_Profit_2015 / $Total_Revenue_2015);?></td>
                    	<td><?php echo percent($Total_Profit_2016 / $Total_Revenue_2016);?></td>
                    	<td><?php echo percent($Total_Profit_2017 / $Total_Revenue_2017);?></td>
                    	<td><?php echo percent($Total_Profit_3_Year / $Total_Revenue_3_Year);?></td>
                    	<td><?php echo percent($Total_Profit_5_Year / $Total_Revenue_5_Year);?></td>
                   	</tr>
                </tbody><?php }?>
            </table>
            <div class="flot-chart" style='width:49%;height:500px;display:inline-block;'><div class="flot-chart-content" id="flot-placeholder-percentage"></div></div>
            <?php require('../../js/chart/financials_percentage.php');?>
            <div id="flot-placeholder" style="width:49%;height:500px;display:inline-block;"></div>    
            <?php require('../../js/bar/financials_open_ar.php');?>
        	<?php
	    } else {return;}

    }
} else {?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }?>