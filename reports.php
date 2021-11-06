<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *
                FROM    Connection
                WHERE       Connection.Connector = ?
                    AND Connection.Hash  = ?;",
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array( $result );
    //User
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *,
                    Emp.fFirst AS First_Name,
                    Emp.Last   AS Last_Name
            FROM    Emp
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $result );
    //Privileges
    $result = sqlsrv_query(
        $NEI,
        "   SELECT  *
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if( !isset( $Connection[ 'ID' ] )
        || !isset($Privileges[ 'Report' ])
            || $Privileges[ 'Report' ][ 'User_Privilege' ]  < 4
            || $Privileges[ 'Report' ][ 'Group_Privilege' ] < 4
            || $Privileges[ 'Report' ][ 'Other_Privilege' ] < 4
    ){
        ?><?php require( '../404.html' );?><?php
    } else {
        sqlsrv_query(
          $NEI,
          "   INSERT INTO Activity([User], [Date], [Page])
              VALUES( ?, ?, ? );",
          array(
              $_SESSION['User'],
              date( 'Y-m-d H:i:s' ),
              'reports.php'
          )
      );
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
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket(3);?></div>
                <div class ='nav-text'>Active Ticket</div>
            </div><?php }?>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='billed_jobs.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Billed Jobs</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='birthdays.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->birthday(3);?></div>
                <div class ='nav-text'>Birthdays</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='due_violations.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Due Violations</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job_closure.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Job Closure</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job_hours.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Job Hours</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job_labor.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Job Labor</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job_without_supervisor.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>no supervisor</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job_tickets.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Job Tickets</div>
            </div>

            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='locaton_labor.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location(3);?></div>
                <div class ='nav-text'>Location Labor</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='maintenances.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Maintenances</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='modernization.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Modernization</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='outstanding_jobs.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Outstanding </div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='payroll.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Payroll</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='proposals.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Proposals</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='review.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Review</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='service_calls.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Service calls</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='time_sheet.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
                <div class ='nav-text'>Time sheet</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='unit_labor.php'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
                <div class ='nav-text'>Unit Labor</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='../login.php?Logout=TRUE'">
                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Logout(3);?></div>
                <div class ='nav-text'>Logout</div>
            </div>
          </div>
          <div class='row'><div class='col-md-12'>&nbsp;</div></div>
      </div>
    </div>
  </div>
    <!-- Bootstrap Core JavaScript -->


    <!-- Metis Menu Plugin JavaScript -->


    <?php require(PROJECT_ROOT.'js/datatables.php');?>

    <!-- Custom Theme JavaScript -->


    <!--Moment JS Date Formatter-->


    <!-- JQUERY UI Javascript -->


    <!-- Custom Date Filters-->


</body>
</html>
<?php
    }
}?>
