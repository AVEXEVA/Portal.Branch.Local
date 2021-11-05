<?php
if( session_id( ) == '' || !isset( $_SESSION ) ) {
    session_start( );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
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
            $_SESSION['User']
        )
    );
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if( !isset( $Connection[ 'ID' ] )
        || !isset($Privileges[ 'Ticket' ])
            || $Privileges[ 'Ticket' ][ 'User_Privilege' ]  < 4
            || $Privileges[ 'Ticket' ][ 'Group_Privilege' ] < 4
            || $Privileges[ 'Ticket' ][ 'Other_Privilege' ] < 4
        || $Privileges[ 'Contract' ][ 'Other_Privilege' ] < 4){
                ?><?php require( '../404.html' );?><?php }
    else {
        sqlsrv_query(
          $NEI,
          "   INSERT INTO Activity([User], [Date], [Page])
              VALUES( ?, ?, ? );",
          array(
              $_SESSION['User'],
              date( 'Y-m-d H:i:s' ),
              'tickets.php'
          )
      );
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Nouveau Elevator Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js  . 'index.php' );?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading"><h4><?php $Icons->Ticket( 1 );?> Tickets</h4></div>
                <div class="card-body bg-dark">
                    <table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
                        <thead><tr>
                            <th class='text-white border border-white' title='ID'>ID</th>
                            <th class='text-white border border-white' title='Person'>Person</th>
                            <th class='text-white border border-white' title='Customer'>Customer</th>
                            <th class='text-white border border-white' title='Location'>Location</th>
                            <th class='text-white border border-white' title='Unit'>Unit</th>
                            <th class='text-white border border-white' title='Job'>Job</th>
                            <th class='text-white border border-white' title='Type'>Type</th>
                            <th class='text-white border border-white' title='Status'>Status</th>
                            <th class='text-white border border-white' title='Date'>Date</th>
                            <th class='text-white border border-white' title='En Route'>En Route</th>
                            <th class='text-white border border-white' title='On Site'>On Site</th>
                            <th class='text-white border border-white' title='Completed'>Completed</th>
                            <th class='text-white border border-white' title='Hours'>Hours</th>
                            <th class='text-white border border-white' title='LSD'>LSD</th>
                            
                        </tr><tr>
                            <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' /></th>
                            <th class='text-white border border-white' title='Person'><input class='redraw form-control' type='text' name='Person' /></th>
                            <th class='text-white border border-white' title='Customer'><input class='redraw form-control' type='text' name='Customer' /></th>
                            <th class='text-white border border-white' title='Location'><input class='redraw form-control' type='text' name='Location' /></th>
                            <th class='text-white border border-white' title='Unit'><input class='redraw form-control' type='text' name='Unit' /></th>
                            <th class='text-white border border-white' title='Job'><input class='redraw form-control' type='text' name='Job' /></th>
                            <th class='text-white border border-white' title='Type'>
                                <div class='row'>
                                    <div class='col-12'><select class='redraw' name='Type'>
                                        <option value=''>Select</option><?php
                                        $Types = array( );
                                        $result = sqlsrv_query(
                                            $NEI,
                                            "   SELECT  JobType.ID,
                                                        JobType.Type
                                                FROM    JobType;",
                                        );
                                        if( $result ) { while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){
                                            ?><option value='<?php echo $row[ 'ID' ];?>'><?php echo $row[ 'Type' ];?></option><?php
                                        }}
                                    ?></select></div>
                                    <div class='col-12'><select class='redraw' name='Level'>
                                        <option value=''>Select</option><?php 
                                        $Levels = array(
                                            0  => '',
                                            1  => 'Service Call',
                                            2  => 'Trucking',
                                            3  => 'Modernization',
                                            4  => 'Violations',
                                            5  => 'Level 5',
                                            6  => 'Repair',
                                            7  => 'Annual',
                                            8  => 'Escalator',
                                            9  => 'Email',
                                            10 => 'Maintenance',
                                            11 => 'Survey',
                                            12 => 'Engineering',
                                            13 => 'Support',
                                            14 => "M/R"
                                        );
                                        foreach( $Levels as $ID => $Level ){?><option value='<?php echo $ID;?>'><?php echo $Level;?></option><?php }
                                    ?></select></div>
                                </div>
                            </th>
                            <th class='text-white border border-white' title='Status'><select class='redraw' name='Status'>
                                <option value=''>Select</option>
                                <option value='0'>Unassigned</option>
                                <option value='1'>Assigned</option>
                                <option value='2'>En Route</option>
                                <option value='3'>On Site</option>
                                <option value='6'>Reviewing</option>
                                <option value='4'>Completed</option>
                                <option value='5'>On Hold</option>
                            </select></th>
                            <th class='text-white border border-white' title='Date'>
                                <div class='row g-0'>
                                    <input class='col-12 date redraw' type='text' name='Start_Date' />
                                    <input class='col-12 date' type='text' name='End_Date' />
                                </div>
                            </th>
                            <th class='text-white border border-white' title='Time Route'>
                                <div class='row g-0'>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Route_Start' /></div>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Route_End' /></div>
                                </div>
                            </th>
                            <th class='text-white border border-white' title='Time Site'>
                                <div class='row g-0'>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Site_Start' /></div>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Site_End' /></div>
                                </div>
                            </th>
                            <th class='text-white border border-white' title='Time Completed'>
                                <div class='row g-0'>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Completed_Start' /></div>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Completed_End' /></div>
                                </div>
                            </th>
                            <th class='text-white border border-white' title='Hours'><input class='redraw form-control ' type='text' name='Hours' /></th>
                            <th class='text-white border border-white' title='Hours'><select class='redraw' name='LSD'>
                                <option value=''>Select</option>
                                <option value='0'>Running</option>
                                <option value='1'>Left Shutdown</option>
                            </select></th>
                        </tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php }
}?>