<?php
session_start();
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
	   	|| !isset($My_Privileges['Job'])
	  		|| $My_Privileges['Job']['User_Privilege']  < 4
	  		|| $My_Privileges['Job']['Group_Privilege'] < 4
	  	    || $My_Privileges['Job']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "overtime.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><div style='float:left;'><?php $Icons->Timesheet();?>PIVOT: Employee Supervisor - <?php echo $_GET['Start'];?> to <?php echo $_GET['End'];?></div><div style='float:right;'><button onClick="document.location.href='overtime.php';" style='color:black;'>Job Supervisor</button></div><div style='float:right;'><button onClick="document.location.href='overtime-employee.php';" style='color:black;'>Employee</button></div><div style='clear:both;'></div></h3></div>
                        <div class='panel-heading'>
                          <form>
                              <div class='row'>
                                <div class='col-xs-1' style='text-align:right;'>Start:</div>
                                <div class='col-xs-11'><input type='text' style='color:black;' name='Start' value='<?php echo isset($_GET['Start']) ? date('m/d/Y',strtotime($_GET['Start'])) : NULL; ?>' />
                              </div></div>
                              <div class='row'>
                                <div class='col-xs-1' style='text-align:right;'>End:</div>
                                <div class='col-xs-11'> <input type='text' style='color:black;' name='End' value='<?php echo isset($_GET['End']) ? date('m/d/Y',strtotime($_GET['End'])) : NULL; ?>' /></div>
                              </div>
                              <div class='row'>
                                <div class='col-xs-1' style='text-align:right;'>Job Type:</div>
                                <div class='col-xs-11'><select name='Job_Type' style='color:black !important;'><option value=''>Select</option>
                                  <?php
                                    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.JobType WHERE ID <> 9 AND ID <> 12;");
                                    if($r){while($row = sqlsrv_fetch_array($r)){
                                      ?><option value='<?php echo $row['ID'];?>' <?php echo isset($_GET['Job_Type']) && $row['ID'] == $_GET['Job_Type'] && $_GET['Job_Type'] != '' ? 'selected' : '';?>><?php echo $row['Type'];?></option><?php
                                    }}?>
                                </select></div>
                              </div>
                              <div class='row'>
                                <div class='col-xs-1' style='text-align:right;'>Supervisor:</div>
                                <div class='col-xs-11'><select name='Supervisor' style='color:black !important;'><option value='' style='color:black;'>Select</option>
                                  <?php
                                    $r = sqlsrv_query($NEI,"SELECT tblWork.Super FROM nei.dbo.tblWork WHERE tblWork.Super <> '' GROUP BY tblWork.Super ORDER BY tblWork.Super ASC ;");
                                    if($r){while($row = sqlsrv_fetch_array($r)){?><option style='color:black !important;' value='<?php echo $row['Super'];?>' <?php echo isset($_GET['Supervisor']) && $row['Super'] == $_GET['Supervisor']  && $_GET['Supervisor'] != '' ? 'selected' : '';?>><?php echo $row['Super'];?></option><?php }}?>
                                </select></div>
                              </div>
                              <div class='row'>
                                <div class='col-xs-1' style='text-align:right;'>Job Name:</div>
                                <div class='col-xs-11'><input type='text' style='color:black !important;' name='fDesc' value='<?php echo isset($_GET['fDesc']) ? $_GET['fDesc'] : NULL;?>' /></div>
                              </div>
                              <div class='row'>
                                <div class='col-xs-1' style='text-align:right;'>Show Regular:</div>
                                <div class='col-xs-11'><input type='checkbox' style='color:black;' value='1' name='ShowRegular' <?php echo isset($_GET['ShowRegular']) && $_GET['ShowRegular'] == 1 ? 'checked' : NULL;?> /></div>
                              </div>
                              <div class='row'><input style='color:black;' type='submit' /></div>
                          </form>
                          <div class='row'>

                        </div></div>
                        <?php if(!isset($_GET['ShowRegular'])){?><div class='panel-heading'>REGULAR HOURS ARE ONLY SHOWN FOR OVERTIME TICKETS</div><?php }?>
                        <div class="panel-body">
                          <style>
                          table#Table_Overtime tbody tr th:first-child, table#Table_Overtime tbody tr td:first-child {
                            width:300px;
                          }
                          table th, table td {
                            border:1px solid white !important;
                            text-align:left;
                            vertical-align:top;
                          }
                          * {
                            padding: 0;
                            margin: 0;
                          }
                          body {
                            font-family: arial, helvetica, sans-serif;
                          }
                          table {
                            border-collapse: collapse;
                            margin: 10px;
                          }
                          table td, table th {
                            border: none;
                          }
                          body h1 {
                            color: red;
                          }
                          @media print {
                            table td, table th {
                              border: 1px solid black;
                            }
                            body {
                              font-family: serif;
                            }
                            h1 {
                              color: green;
                              /* specificity prevents this from being used */
                            }
                          }
                          </style>
                            <table id='Table_Overtime' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th>Employee Supervisor</th>
                                    <th>Location</th>
                                    <th>Name</th>
                                    <th>Job Supervisor</th>
                                    <th>Regular</th>
                                    <tH>ND</th>
                                    <th>OT</th>
                                    <th>DT</th>
                                    <th>Total</th>
                                    <th>OT to Date</th>
                                </thead>
                                <tbody style='color:white !important;'>
                                    <?php
                                        if(isset($_GET['Start'],$_GET['End'],$_GET['Job_Type'])){
                                            $_GET['Start'] = date('Y-m-d', strtotime($_GET['Start']));
                                            $_GET['End'] = date('Y-m-d', strtotime($_GET['End']));
                                            $job_type = isset($_GET['Job_Type']) && strlen($_GET['Job_Type']) > 0 ? '' : " OR '1' = '1'";
                                            $supervisor = isset($_GET['Supervisor']) && strlen($_GET['Supervisor']) > 0 ? '' : " OR '1' = '1'";
                                            $showRegular = isset($_GET['ShowRegular']) && $_GET['ShowRegular'] == 1 ? NULL : "AND (SUM(Tickets.OT) > 0 OR Sum(Tickets.DT) > 0)";
                                            $showRegular2 = isset($_GET['ShowRegular']) && $_GET['ShowRegular'] == 1 ? "'1' = '1'" : "(SUM(TicketD.OT) > 0 OR Sum(TicketD.DT) > 0)";
                                            $showRegular5 = isset($_GET['ShowRegular']) && $_GET['ShowRegular'] == 1 ? NULL : "AND (TicketD.OT > 0 OR TicketD.DT > 0)";
                                            $sQuery = " SELECT  Tickets.Custom1 AS Supervisor,
                                                        Tickets.Tag,
                                                        Tickets.fDesc,
                                                        Tickets.fFirst,
                                                        Tickets.Last,
                                                        Sum(Tickets.Reg) AS SumOfReg,
                                                        Sum(Tickets.OT) AS SumOfOT,
                                                        Sum(Tickets.DT) AS SumOfDT,
                                                        Sum(Tickets.NT) AS SumOfNT,
                                                        Sum(Tickets.Total) AS SumOfTotal,
                                                        Tickets.Title AS Title,
                                                        Tickets.Job AS Job,
                                                        Tickets.Status,
                                                        Tickets.Emp AS Emp,
                                                        Tickets.Custom20 AS Project_Manager,
                                                        Tickets.Employee_Supervisor,
                                                        Tickets.fWork
                                                FROM    ((SELECT TicketD.Reg,
                                                                 TicketD.OT,
                                                                 TicketD.DT,
                                                                 TicketD.NT,
                                                                 TicketD.Total,
                                                                 TicketD.EDate,
                                                                 TicketD.fWork,
                                                                 Emp.ID AS Emp,
                                                                 Emp.fFirst,
                                                                 Emp.Last,
                                                                 Emp.Title,
                                                                 Job.fDesc,
                                                                 Job.ID AS Job,
                                                                 Job.Custom20,
                                                                 Job.Custom1,
                                                                 Job.Type,
                                                                 Job_Status.Status,
                                                                 Loc.Tag,
                                                                 tblWork.Super AS Employee_Supervisor
                                                          FROM  nei.dbo.TicketD
                                                                LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                                                                LEFT JOIN nei.dbo.Job ON TicketD.Job = Job.ID
                                                                LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc
                                                                LEFT JOIN nei.dbo.Job_Status ON Job.Status = Job_Status.ID - 1
                                                                LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members)) AS Tickets
                                                WHERE   Tickets.EDate >= ?
                                                        AND Tickets.EDate <= ?
                                                        AND Tickets.Job IN (
                                                          SELECT Job.ID
                                                          FROM nei.dbo.Job
                                                               LEFT JOIN nei.dbo.TicketD ON Job.ID = TicketD.Job
                                                               LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                                                               LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                                                          WHERE (Job.Type = ? {$job_type}) AND (tblWork.Super = ? {$supervisor}) AND TicketD.EDate >= ? AND TicketD.EDate <= ? AND Job.fDesc LIKE '%' + ? + '%'
                                                          GROUP BY Job.ID
                                                          HAVING {$showRegular2}
                                                        )
                                                GROUP BY Tickets.Custom1, Tickets.Tag, Tickets.fDesc, Tickets.fFirst, Tickets.Last, Tickets.Job, Tickets.Type, Tickets.Title, Tickets.Status, Tickets.Emp, Tickets.Custom20, Tickets.Employee_Supervisor, Tickets.fWork
                                                HAVING  (Tickets.Type=? {$job_type}) AND (Tickets.Employee_Supervisor = ? {$supervisor}) AND Tickets.Type <> 9 AND Tickets.Type <> 12 AND Tickets.fDesc LIKE '%' + ? + '%' {$showRegular};
                                            ;";
                                            //echo $sQuery;
                                            $r = sqlsrv_query($NEI, $sQuery, array($_GET['Start'], $_GET['End'],$_GET['Job_Type'],$_GET['Supervisor'],$_GET['Start'], $_GET['End'],$_GET['fDesc'],$_GET['Job_Type'],$_GET['Supervisor'],$_GET['fDesc']));
                                            if( ($errors = sqlsrv_errors() ) != null) {
                                                foreach( $errors as $error ) {
                                                    echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
                                                    echo "code: ".$error[ 'code']."<br />";
                                                    echo "message: ".$error[ 'message']."<br />";
                                                }
                                            }
                                            //php_sqlsrv_errors();
                                            $locations = array();
                                            $statuses = array();
                                            $job_totals = array();
                                            if($r){while($array = sqlsrv_fetch_array($r)){
                                              $r2 = sqlsrv_query($NEI,"SELECT Sum(TicketD.OT) + Sum(TicketD.DT) AS Total_Overtime FROM TicketD WHERE TicketD.Job = ?",array($array['Job']));
                                              if($r2){$job_totals[$array['Job'] . ' - ' . $array['fDesc']] = sqlsrv_fetch_array($r2)['Total_Overtime'];}
                                            	if($array['Status'] == NULL){ $statuses[$array['Job'] . ' - ' . $array['fDesc']] = 'Unknown';}
                                            	elseif(strtolower($array['Status']) == 'open'){$statuses[$array['Job'] . ' - ' . $array['fDesc']] = 'Active';}
                                            	else {$statuses[$array['Job'] . ' - ' . $array['fDesc']] = $array['Status'];}
                                                //$statuses[$array['Job']] = $array['Status'] == NULL ? 'Unknown' : ;
                                                $locations[trim($array['Employee_Supervisor'])][$array['Tag']][$array['Job'] . ' - ' . $array['fDesc']][] = $array;
                                                $employees[$array['Emp']] = $array['Title'];
                                            }}
                                            foreach($locations as $supervisor=>$arrays0){
                                                ?><tr><td colspan='9'><b><?php echo $supervisor;?></b></td></tr><?php
                                                foreach($arrays0 as $location=>$arrays){
                                                    ?><tr><td></td><td colspan='8'><b><?php echo $location;?></b></td></tr><?php
                                                    $reg_total = 0;
                                                    $ot_total = 0;
                                                    $dt_total = 0;
                                                    $nt_total = 0;
                                                    $total_total = 0;
                                                    foreach($arrays as $job=>$arrayed){
                                                      ?><tr><td></td><td><?php echo $statuses[$job];?></td><td colspan='7'><b><?php echo $job;?></b></td></tr><?php
                                                      foreach($arrayed as $array){
                                                          $reg_total += $array['SumOfReg'];
                                                          $ot_total += $array['SumOfOT'];
                                                          $dt_total += $array['SumOfDT'];
                                                          $nt_total += $array['SumOfNT'];
                                                          $total_total += $array['SumOfTotal'];
                                                          ?><tr><td></td><td><?php echo $array['Title'];?></td><td><?php echo $array['fFirst'] . ' ' . $array['Last'];?></td><td><?php echo $array['Supervisor'];?></td></tr><?php
                                                          $resource = sqlsrv_query($NEI,
                                                            "SELECT *
                                                             FROM   nei.dbo.TicketD
                                                             WHERE  TicketD.EDate >= ?
                                                                    AND TicketD.EDate <= ?
                                                                    AND TicketD.Job = ?
                                                                    {$showRegular5}
                                                                    AND TicketD.fWork = ?
                                                          ;",array($_GET['Start'], $_GET['End'], $array['Job'], $array['fWork']));

                                                          if($resource){while($row = sqlsrv_fetch_array($resource)){
                                                            ?><tr><td colspan='2'><?php echo $row['fDesc'];?></td><td colspan='2'><?php echo $row['DescRes'];?></td><td><?php echo $row['Reg'];?></td><td><?php echo $row['NT'];?></td><td><?php echo $row['OT'];?></td><td><?php echo $row['DT'];?></td><td><?php echo $row['Total'];?></td></tr><?php
                                                          }}
                                                          ?><tr><td colspan='4'></td><td><?php echo $array['SumOfReg'];?></td><td><?php echo $array['SumOfNT'];?></td><td><?php echo $array['SumOfOT'];?></td><td><?php echo $array['SumOfDT'];?></td><td><?php echo $array['SumOfTotal'];?></td></tr><?php
                                                      }

                                                      ?><tr><td></td><td><td></td></td><td>&nbsp;</td><td><b><?php echo $reg_total;?></b></td><td><b><?php echo $nt_total;?></td><td><b><?php echo $ot_total;?></b></td><td><b><?php echo $dt_total;?></b></td><td><b><?php echo $total_total;?></b></td><td><?php echo $job_totals[$job];?></tr><?php
                                                    }

                                                }
                                            }
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.js"></script>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $(document).ready(function(){
            $("input[name='Start']").datepicker();
            $("input[name='End']").datepicker();
        });
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=overtime.php';</script></head></html><?php }?>
