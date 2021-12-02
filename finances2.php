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
        ||  !isset( $Privileges[ 'Finance' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Finance' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'finances2.php'
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
                        <div class="panel-heading">Finances</div>
                        <div class='panel-body'>
                          <table id="Table_Profit" class="display" cellspacing='0' width='100%' style='font-size:8px !important;'>
                            <?php
                            $resource = $database->query(null,"
                              SELECT   Overhead_Cost.*
                              FROM     Portal.dbo.Overhead_Cost
                              ORDER BY Overhead_Cost.Type ASC
                            ;");
                            $Overhead_Costs = array();
                            if($resource){while($Overhead_Cost = sqlsrv_fetch_array($resource)){
                              /*if($Overhead_Cost['Type'] == '2012'){continue;}
                              if($Overhead_Cost['Type'] == '2013'){continue;}
                              if($Overhead_Cost['Type'] == '2014'){continue;}
                              if($Overhead_Cost['Type'] == '2015'){continue;}
                              if($Overhead_Cost['Type'] == '7 Year'){continue;}*/
                              $Overhead_Costs[] = $Overhead_Cost;}}?>
                            <thead style='border-left:3px solid black;border-right:3px solid black;border-top:3px solid black;'>
                              <th></th>
                              <?php
                                foreach($Overhead_Costs as $Overhead_Cost){
                                  ?><th style='border:1px solid black;padding:3px;'><?php echo $Overhead_Cost['Type'];?></th><?php
                                }
                              ?>
                            </thead>
                            <tbody style='border:3px solid black;color:white !important;'>
                              <tr>
                                <td style='border:1px solid black;padding:3px;'>Revenue</td>
                                <?php
                                foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
                                  $resource = $database->query(null,"
                                    SELECT Sum(Invoice.Amount) AS Revenue
                                    FROM   nei.dbo.Invoice
                                         LEFT JOIN nei.dbo.Loc ON Invoice.Loc = Loc.Loc
                                    WHERE  Invoice.fDate >= ?
                                         AND Invoice.fDate < ?
                                  ;",array($Overhead_Cost['Start'],$Overhead_Cost['End']));
                                  $Overhead_Costs[$key]['Revenue'] = sqlsrv_fetch_array($resource)['Revenue'];
                                  echo money_format('%(n',$Overhead_Costs[$key]['Revenue']);
                                ?></td><?php }?>
                              </tr>
                              <tr>
                                <td style='border:1px solid black;padding:3px;'>Labor</td>
                                <?php
                                foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
                                  //var_dump($Overhead_Cost);
                                  $resource = $database->query(null,"
                                    SELECT Sum(JobI.Amount) AS Labor
                                    FROM   nei.dbo.Loc
                                         LEFT JOIN nei.dbo.Job  ON Loc.Loc = Job.Loc
                                         LEFT JOIN nei.dbo.JobI ON Job.ID  = JobI.Job
                                    WHERE  JobI.Type  =  1
                                         AND JobI.Labor =  1
                                         AND JobI.fDate >= ?
                                         AND JobI.fDate <  ?
                                         AND JobI.fDate >= '2017-03-30 00:00:00.000'
                                  ;",array($Overhead_Cost['Start'],$Overhead_Cost['End']));
                                  $Overhead_Costs[$key]['Labor'] = sqlsrv_fetch_array($resource)['Labor'];
                                  $resource = $database->query(null,"
                                    SELECT SUM([JOBLABOR].[TOTAL COST]) AS Labor
                                    FROM   nei.dbo.Job as Job
                                         LEFT JOIN Paradox.dbo.JOBLABOR AS JOBLABOR ON Job.ID = [JOBLABOR].[JOB #]
                                    WHERE  convert(date,[WEEK ENDING]) >= ?
                                         AND convert(date,[WEEK ENDING]) < ?
                                         AND convert(date,[WEEK ENDING]) < '2017-03-30 00:00:00.000'
                                         AND [JOBLABOR].[jobAlpha] <> '1111'
                                         AND [JOBLABOR].[JobAlpha] <> '2222'
                                         AND [JOBLABOR].[JobAlpha] <> '3333'
                                         AND [JOBLABOR].[JobAlpha] <> '4444'
                                         AND [JOBLABOR].[JobAlpha] <> '5555'
                                         AND [JOBLABOR].[JobAlpha] <> '6666'
                                         AND [JOBLABOR].[JobAlpha] <> '2222'
                                         AND [JOBLABOR].[JobAlpha] <> '7777'
                                         AND [JOBLABOR].[JobAlpha] <> '8888'
                                         AND [JOBLABOR].[JobAlpha] <> '9999'
                                  ;",array($Overhead_Cost['Start'],$Overhead_Cost['End']));
                                  $Overhead_Costs[$key]['Labor'] += sqlsrv_fetch_array($resource)['Labor'];
                                  echo money_format('%(n',$Overhead_Costs[$key]['Labor']);
                                ?></td><?php }?>
                              </tr>
                              <tr>
                                <td style='border:1px solid black;padding:3px;'>Materials</td>
                                <?php
                                foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
                                  //var_dump($Overhead_Cost);
                                  $resource = $database->query(null,"
                                    SELECT Sum(JobI.Amount) AS Materials
                                    FROM   nei.dbo.Loc
                                         LEFT JOIN nei.dbo.Job  ON Loc.Loc = Job.Loc
                                         LEFT JOIN nei.dbo.JobI ON Job.ID  = JobI.Job
                                    WHERE  JobI.Type  =  1
                                         AND (
                                          JobI.Labor =  0
                                          OR JobI.Labor IS NULL
                                          OR JobI.Labor = ' ')
                                         AND JobI.fDate >= ?
                                         AND JobI.fDate <  ?
                                  ;",array($Overhead_Cost['Start'],$Overhead_Cost['End']));
                                  $Overhead_Costs[$key]['Materials'] = sqlsrv_fetch_array($resource)['Materials'];
                                  echo money_format('%(n',$Overhead_Costs[$key]['Materials']);
                                ?></td><?php }?>
                              </tr>
                              <tr style='border-top:3px solid black;'>
                                <td style='border:1px solid black;padding:3px;'>Net Income</td>
                                <?php
                                foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
                                  $Overhead_Costs[$key]['Net_Income'] = $Overhead_Costs[$key]['Revenue'] - ($Overhead_Costs[$key]['Labor'] + $Overhead_Costs[$key]['Materials']);
                                  echo money_format('%(n',$Overhead_Costs[$key]['Net_Income']);
                                ?></td><?php }?>
                              </tr>
                              <tr>
                                <td style='border:1px solid black;padding:3px;'>Overhead Rate</td>
                                <?php
                                foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
                                  echo $Overhead_Cost['Rate'] . '%';
                                ?></td><?php }?>
                              </tr>
                              <tr>
                                <td style='border:1px solid black;padding:3px;'>Overhead Cost</td>
                                <?php
                                foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
                                  $Overhead_Costs[$key]['Overhead_Cost'] = $Overhead_Costs[$key]['Revenue'] * ($Overhead_Costs[$key]['Rate'] / 100);
                                  echo money_format('%(n',$Overhead_Costs[$key]['Overhead_Cost']);
                                ?></td><?php }?>
                              </tr>
                              <tr>
                                <td style='border:1px solid black;padding:3px;'>Profit</td>
                                <?php
                                foreach($Overhead_Costs as $key=>$Overhead_Cost){?><td style='border:1px solid black;padding:3px;'><?php
                                  $Overhead_Costs[$key]['Profit'] = $Overhead_Costs[$key]['Net_Income'] - $Overhead_Costs[$key]['Overhead_Cost'];
                                  echo money_format('%(n',$Overhead_Costs[$key]['Profit']);
                                ?></td><?php }?>
                              </tr>
                            </tbody>
                          </table>
                        </div>
                        <div class="panel-heading">Profit</div>
                        <div class="panel-body">
                            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-profit"></div></div>
                        </div>
                        <div class="panel-heading">Maintenance Profit</div>
                        <div class="panel-body">
                            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-maintenance-profit"></div></div>
                        </div>
                        <div class="panel-heading">Modernization Profit</div>
                        <div class="panel-body">
                            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-modernization-profit"></div></div>
                        </div>
                        <div class="panel-heading">Testing Profit</div>
                        <div class="panel-body">
                            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-test-profit"></div></div>
                        </div>
                        <div class="panel-heading">"Repair" and "New Repair" Profit</div>
                        <div class="panel-body">
                            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-repair-profit"></div></div>
                        </div>
                        <div class="panel-heading">Other Profit</div>
                        <div class="panel-body">
                            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-other-profit"></div></div>
                        </div>
                        <?php /*<div class="panel-heading">No Job Type Profit</div>
                        <div class="panel-body">
                            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-no-job-type-profit"></div></div>
                        </div>*/?>
                        <div class="panel-heading">Lawsuit Cost</div>
                        <div class="panel-body">
                            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-lawsuit-cost"></div></div>
                        </div>
                        <div class="panel-heading">General Liability Cost</div>
                        <div class="panel-body">
                            <div class="flot-chart"><div class="flot-chart-content" id="flot-placeholder-gl-incidents-cost"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->


    <!-- Metis Menu Plugin JavaScript -->


    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <!-- Custom Theme JavaScript -->


    <!--Moment JS Date Formatter-->


    <script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>
    <?php require(PROJECT_ROOT."js/chart/profit.php");?>
    <?php require(PROJECT_ROOT."js/chart/maintenance_profit.php");?>
    <?php require(PROJECT_ROOT."js/chart/test_profit.php");?>
    <?php require(PROJECT_ROOT."js/chart/modernization_profit.php");?>
    <?php require(PROJECT_ROOT."js/chart/repair_profit.php");?>
    <?php require(PROJECT_ROOT."js/chart/other_profit.php");?>
    <?php /*require(PROJECT_ROOT."js/chart/no_job_type_profit.php");*/?>
    <?php require(PROJECT_ROOT."js/chart/lawsuit_cost.php");?>
    <?php require(PROJECT_ROOT."js/chart/gl_incidents_cost.php");?>

</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
