<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [
        'read_and_close' => true
    ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = $database->query(
        null,
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
    $result = $database->query(
        null,
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
    $result = $database->query(
        null,
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
    ){      
        ?><?php require( '../404.html' );?><?php 
    } else {
        $database->query(
          null,
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
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="card card-full card-primary border-0">
                <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?> Tickets</h4></div>
                <div class="form-mobile card-body bg-dark text-white"><form method='GET' action='locations.php'>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                  <div class='row'>
                      <div class='col-4'>Search:</div>
                      <div class='col-8'><input type='text' name='Search' placeholder='Search' class='redraw' /></div>
                  </div>
                  <div class='row'><div class='col-12'>&nbsp;</div></div>
                  <div class='row'>
                    <div class='col-4'>Person:</div>
                    <div class='col-8'><input type='text' name='Person' placeholder='Person' class='redraw' value='<?php echo $_GET[ 'Person' ];?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Customer:</div>
                    <div class='col-8'><input type='text' name='Customer' placeholder='Customer' class='redraw' value='<?php echo $_GET[ 'Customer' ];?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Location:</div>
                    <div class='col-8'><input type='text' name='Location' placeholder='Location' class='redraw' value='<?php echo $_GET[ 'Location' ];?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Unit:</div>
                    <div class='col-8'><input type='text' name='Unit' placeholder='Unit' class='redraw' value='<?php echo $_GET[ 'Unit' ];?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Job:</div>
                    <div class='col-8'><input type='text' name='Job' placeholder='Job' class='redraw' value='<?php echo $_GET[ 'Job' ];?>' /></div>
                  </div>
                </form></div>
                <div class="card-body bg-dark">
                    <table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
                        <thead><tr class='text-center'>
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
                            <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null; ?>' /></th>
                            <th class='text-white border border-white' title='Person'><input class='redraw form-control' type='text' name='Person' value='<?php echo isset( $_GET[ 'Person' ] ) ? $_GET[ 'Person' ] : null; ?>' /></th>
                            <th class='text-white border border-white' title='Customer'><input class='redraw form-control' type='text' name='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null; ?>' /></th>
                            <th class='text-white border border-white' title='Location'><input class='redraw form-control' type='text' name='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null; ?>' /></th>
                            <th class='text-white border border-white' title='Unit'><input class='redraw form-control' type='text' name='Unit' value='<?php echo isset( $_GET[ 'Unit' ] ) ? $_GET[ 'Unit' ] : null; ?>' /></th>
                            <th class='text-white border border-white' title='Job'><input class='redraw form-control' type='text' name='Job' value='<?php echo isset( $_GET[ 'Job' ] ) ? $_GET[ 'Job' ] : null; ?>' /></th>
                            <th class='text-white border border-white' title='Type'>
                                <div class='row'>
                                    <div class='col-12'><select class='redraw' name='Type'>
                                        <option value=''>Select</option><?php
                                        $Types = array( );
                                        $result = $database->query(
                                            null,
                                            "   SELECT  JobType.ID,
                                                        JobType.Type
                                                FROM    JobType;",
                                        );
                                        if( $result ) { while( $row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){
                                            ?><option value='<?php echo $row[ 'ID' ];?>' <?php echo isset( $_GET[ 'Type' ] ) && !empty( $_GET[ 'Type' ] )  && $_GET[ 'Type' ] == $row[ 'ID' ] ? "selected='selected'" : null;?>><?php echo $row[ 'Type' ];?></option><?php
                                        }}
                                    ?></select></div>
                                    <div class='col-12'><select class='redraw' name='Level'>
                                        <option value=''>Select</option><?php 
                                        $Levels = array(
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
                                        foreach( $Levels as $ID => $Level ){?><option value='<?php echo $ID;?>' <?php echo isset( $_GET[ 'Level' ] ) && !empty( $_GET[ 'Level' ] ) && $_GET[ 'Level' ] == $row[ 'ID' ] ? "selected='selected'" : null;?>><?php echo $Level;?></option><?php }
                                    ?></select></div>
                                </div>
                            </th>
                            <th class='text-white border border-white' title='Status'><select class='redraw' name='Status'>
                                <option value=''>Select</option>
                                <option value='0' <?php echo isset( $_GET[ 'Status' ] ) && !empty( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 0 ? "selected='selected'" : null;?>>Unassigned</option>
                                <option value='1' <?php echo isset( $_GET[ 'Status' ] ) && !empty( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 1 ? "selected='selected'" : null;?>>Assigned</option>
                                <option value='2' <?php echo isset( $_GET[ 'Status' ] ) && !empty( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 2 ? "selected='selected'" : null;?>>En Route</option>
                                <option value='3' <?php echo isset( $_GET[ 'Status' ] ) && !empty( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 3 ? "selected='selected'" : null;?>>On Site</option>
                                <option value='6' <?php echo isset( $_GET[ 'Status' ] ) && !empty( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 6 ? "selected='selected'" : null;?>>Reviewing</option>
                                <option value='4' <?php echo isset( $_GET[ 'Status' ] ) && !empty( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 4 ? "selected='selected'" : null;?>>Completed</option>
                                <option value='5' <?php echo isset( $_GET[ 'Status' ] ) && !empty( $_GET[ 'Status' ] ) && $_GET[ 'Status' ] == 5 ? "selected='selected'" : null;?>>On Hold</option>
                            </select></th>
                            <th class='text-white border border-white' title='Date'>
                                <div class='row g-0'>
                                    <input class='col-12 date redraw' type='text' name='Start_Date' value='<?php echo isset( $_GET[ 'Start_Date' ] ) ? $_GET[ 'Start_Date' ] : null;?>' />
                                    <input class='col-12 date redraw' type='text' name='End_Date' value='<?php echo isset( $_GET[ 'End_Date' ] ) ? $_GET[ 'End_Date' ] : null;?>' />
                                </div>
                            </th>
                            <th class='text-white border border-white' title='Time Route'>
                                <div class='row g-0'>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Route_Start' value='<?php echo isset( $_GET[ 'Time_Route_Start' ] ) ? $_GET[ 'Time_Route_Start' ] : null;?>' /></div>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Route_End' value='<?php echo isset( $_GET[ 'Time_Route_End' ] ) ? $_GET[ 'Time_Route_End' ] : null;?>' /></div>
                                </div>
                            </th>
                            <th class='text-white border border-white' title='Time Site'>
                                <div class='row g-0'>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Site_Start' value='<?php echo isset( $_GET[ 'Time_Site_Start' ] ) ? $_GET[ 'Time_Site_Start' ] : null;?>' /></div>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Site_End' value='<?php echo isset( $_GET[ 'Time_Site_End' ] ) ? $_GET[ 'Time_Site_End' ] : null;?>' /></div>
                                </div>
                            </th>
                            <th class='text-white border border-white' title='Time Completed'>
                                <div class='row g-0'>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Completed_Start' value='<?php echo isset( $_GET[ 'Time_Completed_Start' ] ) ? $_GET[ 'Time_Completed_Start' ] : null;?>' /></div>
                                    <div class='col-12'><input class='redraw time' type='text' name='Time_Completed_End' value='<?php echo isset( $_GET[ 'Time_Completed_End' ] ) ? $_GET[ 'Time_Completed_End' ] : null;?>' /></div>
                                </div>
                            </th>
                            <th class='text-white border border-white' title='Hours'><input class='redraw form-control ' type='text' name='Hours' /></th>
                            <th class='text-white border border-white' title='Hours'><select class='redraw' name='LSD'>
                                <option value=''>Select</option>
                                <option value='0' <?php echo isset( $_GET[ 'LSD' ] ) && $_GET[ 'LSD' ] && $_GET[ 'LSD' ] == 0 ? "selected='selected'" : null;?>>Running</option>
                                <option value='1' <?php echo isset( $_GET[ 'LSD' ] ) && $_GET[ 'LSD' ] && $_GET[ 'LSD' ] == 0 ? "selected='selected'" : null;?>>Left Shutdown</option>
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
} else {?><script>document.location.href='../login.php?Forward=tickets.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>