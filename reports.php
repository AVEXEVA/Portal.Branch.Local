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
               Emp.Last   AS Last_Name,
               Emp.Field  AS Field
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
?><!DOCTYPE html>

<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
    <div id="page-wrapper" class='content'>
      <div class='panel panel-primary'>
        <div class='panel-heading'>Reports</div>
        <div class='panel-body'>
        <?php if(FALSE){?><div class='col-xs-12'><form style='height:100%;float:left;width:100%;' action='search.php' method='GET'><input name='Keyword' type='text' placeholder='Search' style='height:50px;color:black;width:100%;'/></form></div><?php }?>
        <style>
            .nav-text{
                font-weight: bold;
                text-align: center;
            }
            .nav-icon{
                text-align: center;
            }
            .Home-Screen-Option{
                margin-top: 25px;
            }
        </style>
        <div class='row'>
        <?php ?>
            <?php if(isset($Ticket) && is_array($Ticket)){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='ticket.php?ID=<?php echo $Ticket['ID'];?>';">
                <div class='nav-icon'><?php $Icons->Ticket(3);?></div>
                <div class ='nav-text'>Active Ticket</div>
            </div><?php }?>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='billed_jobs.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Billed Jobs</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='birthdays.php'">
                <div class='nav-icon'><?php $Icons->birthday(3);?></div>
                <div class ='nav-text'>Birthdays</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='due_violations.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Due Violations</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job_closure.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Job Closure</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job_hours.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Job Hours</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job_labor.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Job Labor</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job_without_supervisor.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>no supervisor</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job_tickets.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Job Tickets</div>
            </div>

            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='locaton_labor.php'">
                <div class='nav-icon'><?php $Icons->Location(3);?></div>
                <div class ='nav-text'>Location Labor</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='maintenances.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Maintenances</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='modernization.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Modernization</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='outstanding_jobs.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Outstanding </div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='payroll.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Payroll</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='proposals.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Proposals</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='review.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Review</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='service_calls.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Service calls</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='time_sheet.php'">
                <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                <div class ='nav-text'>Time sheet</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='unit_labor.php'">
                <div class='nav-icon'><?php $Icons->Unit(3);?></div>
                <div class ='nav-text'>Unit Labor</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='../login.php?Logout=TRUE'">
                <div class='nav-icon'><?php $Icons->Logout(3);?></div>
                <div class ='nav-text'>Logout</div>
            </div>
          </div>
          <div class='row'><div class='col-md-12'>&nbsp;</div></div>
      </div>
    </div>
  </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>

</body>
</html>
<?php
    }
