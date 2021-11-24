<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $result = \singleton\database::getInstance( )->query(
      'Portal',
      " SELECT  [Connection].[ID]
        FROM    dbo.[Connection]
        WHERE       [Connection].[User] = ?
                AND [Connection].[Hash] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ],
        $_SESSION[ 'Connection' ][ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = \singleton\database::getInstance( )->query(
		null,
		" SELECT  Emp.fFirst  AS First_Name,
		          Emp.Last    AS Last_Name,
		          Emp.fFirst + ' ' + Emp.Last AS Name,
		          Emp.Title AS Title,
		          Emp.Field   AS Field
		  FROM  Emp
		  WHERE   Emp.ID = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$Access = 0;
	$Hex = 0;
	$result = \singleton\database::getInstance( )->query(
		'Portal',
		"   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
		  FROM      dbo.[Privilege]
		  WHERE     Privilege.[User] = ?;",
		array(
		  	$_SESSION[ 'Connection' ][ 'User' ],
		)
	);
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

        $key = $Privilege['Access'];
        unset( $Privilege[ 'Access' ] );
        $Privileges[ $key ] = implode( '', array(
        	dechex( $Privilege[ 'Owner' ] ),
        	dechex( $Privilege[ 'Group' ] ),
        	dechex( $Privilege[ 'Department' ] ),
        	dechex( $Privilege[ 'Database' ] ),
        	dechex( $Privilege[ 'Server' ] ),
        	dechex( $Privilege[ 'Other' ] ),
        	dechex( $Privilege[ 'Token' ] ),
        	dechex( $Privilege[ 'Internet' ] )
        ) );
    }}
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Job' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'repair.php'
        )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
       <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
       <?php  $_GET[ 'Entity_CSS' ] = 1;?>
       <?php	require( bin_meta . 'index.php');?>
       <?php	require( bin_css  . 'index.php');?>
       <?php  require( bin_js   . 'index.php');?>
</head>
<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class='row'>
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3><?php \singleton\fontawesome::getInstance( )->Modernization();?>Repair Department
                            </h3>
                        </div>
                        <div class="panel-body">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#basic-pills" data-toggle="tab"><?php \singleton\fontawesome::getInstance( )->Info();?> Summary</a></li>
                                <li class=""><a href="#financials-pills" data-toggle="tab"><?php \singleton\fontawesome::getInstance( )->Financial();?> Financials</a></li>
                            </ul>
                            <br />
                            <div class="tab-content">
                                <div class="tab-pane fade in active" id="basic-pills">
                                    <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-repair-jobs"></div></div>
                                    <br />
                                    <div class='row'>
                                        <div class='col-md-6'><div class="flot-chart"><div class="flot-chart-content" id="flot-pie-chart-repair-jobs-by-supervisor"></div></div></div>
                                        <div class='col-md-6'><h4>Below this pie chart is as of 3/30/2017 Paperless Time Tickets</h4><div class="flot-chart"><div class="flot-chart-content" id="flot-pie-chart-repair-jobs-hours-by-supervisor"></div></div></div>
                                    </div>
                                </div>
                                <div class="tab-pane fade in" id="financials-pills">
                                    <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-repair-profit"></div></div>
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
                                                    $r = $database->query(null,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2012
                                                        FROM
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2012-01-01 00:00:00.000' AND Invoice.fDate < '2013-01-01 00:00:00.000' AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Revenue_2012 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2012'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2012);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2013
                                                        FROM
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2014-01-01 00:00:00.000' AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Revenue_2013 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2013'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2013);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2014
                                                        FROM
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2014-01-01 00:00:00.000' AND Invoice.fDate < '2015-01-01 00:00:00.000' AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Revenue_2014 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2014'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2014);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2015
                                                        FROM
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2016-01-01 00:00:00.000' AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Revenue_2015 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2015'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2015);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2016
                                                        FROM
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2016-01-01 00:00:00.000' AND Invoice.fDate < '2017-01-01 00:00:00.000' AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Revenue_2016 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2016'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2016);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_2017
                                                        FROM
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2017-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Revenue_2017 = $r ? sqlsrv_fetch_array($r)['Total_Revenue_2017'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_2017);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_3_Year
                                                        FROM
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2015-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Revenue_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_3_Year'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_3_Year);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT Sum(Invoice.Amount) AS Total_Revenue_5_Year
                                                        FROM
                                                            (Invoice
                                                            LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc)
                                                            LEFT JOIN nei.dbo.Job ON Invoice.Job = Job.ID
                                                        WHERE Invoice.fDate >= '2013-01-01 00:00:00.000' AND Invoice.fDate < '2018-01-01 00:00:00.000' AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Revenue_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Revenue_5_Year'] : 0;
                                                    echo money_format('%(n',$Total_Revenue_5_Year);
                                                ?></td>
                                            </tr>
                                            <tr>
                                                <td><b>Labor</b></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_2012
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Temp_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                                                    $r = $database->query($Paradox,"
                                                        SELECT
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2012
                                                        FROM
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2012-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2013-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Labor_2012 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2012'] : 0;
                                                    echo money_format('%(n',$Total_Labor_2012);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_2013
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Temp_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                                                    $r = $database->query($Paradox,"
                                                        SELECT
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2013
                                                        FROM
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2013-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2014-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Labor_2013 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2013'] : 0;
                                                    echo money_format('%(n',$Total_Labor_2013);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_2014
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Temp_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                                                    $r = $database->query($Paradox,"
                                                        SELECT
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2014
                                                        FROM
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2014-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2015-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Labor_2014 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2014'] : 0;
                                                    echo money_format('%(n',$Total_Labor_2014);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_2015
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Temp_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                                                    $r = $database->query($Paradox,"
                                                        SELECT
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2015
                                                        FROM
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2015-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2016-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Labor_2015 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2015'] : 0;
                                                    echo money_format('%(n',$Total_Labor_2015);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_2016
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Temp_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                                                    $r = $database->query($Paradox,"
                                                        SELECT
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2016
                                                        FROM
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2016-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2017-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Labor_2016 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2016'] : 0;
                                                    echo money_format('%(n',$Total_Labor_2016);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_2017
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");

                                                    $Temp_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                                                    $r = $database->query($Paradox,"
                                                        SELECT
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_2017
                                                        FROM
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2017-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2017-03-30 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Labor_2017 = $r ? sqlsrv_fetch_array($r)['Total_Labor_2017'] : 0;
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_2017
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ");
                                                    $Total_Labor_2017 = $r ? $Total_Labor_2017 + sqlsrv_fetch_array($r)['Total_Labor_2017'] : $Total_Labor_3_Year;
                                                    echo money_format('%(n',$Total_Labor_2017);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_3_Year
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Temp_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                                                    $r = $database->query($Paradox,"
                                                        SELECT
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_3_Year
                                                        FROM
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2015-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2017-03-30 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Labor_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : 0;
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_3_Year
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ");
                                                    $Total_Labor_3_Year = $r ? $Total_Labor_3_Year + sqlsrv_fetch_array($r)['Total_Labor_3_Year'] : $Total_Labor_3_Year;
                                                    echo money_format('%(n',$Total_Labor_3_Year);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_5_Year
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Temp_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                                                    $r = $database->query($Paradox,"
                                                        SELECT
                                                            SUM([JOBLABOR].[TOTAL COST])     AS Total_Labor_5_Year
                                                        FROM
                                                            nei.dbo.Job as Job
                                                            LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                                        WHERE
                                                            convert(date,[JOBLABOR].[Week Ending]) >= '2013-01-01 00:00:00.000'
                                                            AND convert(date,[JOBLABOR].[Week Ending]) < '2017-03-30 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Labor_5_Year = $r ? sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : 0;
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Labor_5_Year
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1' AND JobI.Labor = '1'
                                                            AND JobI.fDate >= '2017-03-30 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ");
                                                    $Total_Labor_5_Year = $r ? $Total_Labor_5_Year + sqlsrv_fetch_array($r)['Total_Labor_5_Year'] : $Total_Labor_5_Year;
                                                    echo money_format('%(n',$Total_Labor_5_Year);
                                                ?></td>
                                            </tr>
                                            <tr style='border-bottom:1px solid black;'>
                                                <td><b>Materials</b></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Materials_2012
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2012-01-01 00:00:00.000' AND JobI.fDate < '2013-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Materials_2012 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2012'] - $Temp_Labor_2012 : 0;
                                                    echo money_format('%(n',$Total_Materials_2012);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Materials_2013
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2014-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Materials_2013 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2013'] - $Temp_Labor_2013 : 0;
                                                    echo money_format('%(n',$Total_Materials_2013);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Materials_2014
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2014-01-01 00:00:00.000' AND JobI.fDate < '2015-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Materials_2014 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2014'] - $Temp_Labor_2014 : 0;
                                                    echo money_format('%(n',$Total_Materials_2014);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Materials_2015
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2016-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Materials_2015 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2015'] - $Temp_Labor_2015 : 0;
                                                    echo money_format('%(n',$Total_Materials_2015);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Materials_2016
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2016-01-01 00:00:00.000' AND JobI.fDate < '2017-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Materials_2016 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2016'] - $Temp_Labor_2016 : 0;
                                                    echo money_format('%(n',$Total_Materials_2016);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Materials_2017
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2017-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Materials_2017 = $r ? sqlsrv_fetch_array($r)['Total_Materials_2017'] - $Temp_Labor_2017 : 0;
                                                    echo money_format('%(n',$Total_Materials_2017);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Materials_3_Year
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2015-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
                                                    ;");
                                                    $Total_Materials_3_Year = $r ? sqlsrv_fetch_array($r)['Total_Materials_3_Year'] - $Temp_Labor_3_Year : 0;
                                                    echo money_format('%(n',$Total_Materials_3_Year);
                                                ?></td>
                                                <td><?php
                                                    $r = $database->query(null,"
                                                        SELECT
                                                            Sum(JobI.Amount) AS Total_Materials_5_Year
                                                        FROM
                                                            (Loc
                                                            LEFT JOIN nei.dbo.Job ON Loc.Loc = Job.Loc)
                                                            LEFT JOIN nei.dbo.JobI ON Job.ID = JobI.Job
                                                        WHERE
                                                            JobI.Type='1'
                                                            AND JobI.fDate >= '2013-01-01 00:00:00.000' AND JobI.fDate < '2018-01-01 00:00:00.000'
                                                            AND (Job.Type='3' OR Job.Type='6')
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
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://www.nouveauelevator.com/vendor/jquery/jquery.min.js"></script>


    <?php require(PROJECT_ROOT.'js/datatables.php');?>

	<?php require('bin/js/flotcharts.php');?>
    <?php require(PROJECT_ROOT."js/chart/repair_jobs.php");?>
    <?php require(PROJECT_ROOT."js/pie/repair_jobs_by_supervisor.php");?>
    <?php require(PROJECT_ROOT."js/pie/repair_jobs_hours_by_supervisor.php");?>
    <?php require(PROJECT_ROOT."js/chart/repair_profit.php");?>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=repair.php';</script></head></html><?php }?>
