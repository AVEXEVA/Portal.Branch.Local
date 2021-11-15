<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [
        'read_and_close' => true
    ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
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
        || !isset($Privileges[ 'Division' ])
            || $Privileges[ 'Division' ][ 'User_Privilege' ]  < 4
            || $Privileges[ 'Division' ][ 'Group_Privilege' ] < 4
            || $Privileges[ 'Division' ][ 'Other_Privilege' ] < 4
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
              'divisions.php'
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
                    <div class='col-4'>ID:</div>
                    <div class='col-8'><input type='text' name='Person' placeholder='Person' class='redraw' value='<?php echo $_GET[ 'Person' ];?>' /></div>
                  </div>
                  <div class='row'>
                    <div class='col-4'>Name:</div>
                    <div class='col-8'><input type='text' name='Customer' placeholder='Customer' class='redraw' value='<?php echo $_GET[ 'Customer' ];?>' /></div>
                  </div>
                </form></div>
                <div class="card-body bg-dark">
                    <table id='Table_Divisions' class='display' cellspacing='0' width='100%'>
                        <thead><tr class='text-center'>
                            <th class='text-white border border-white' title='ID'>ID</th>
                            <th class='text-white border border-white' title='Name'>Name</th>
                            <th class='text-white border border-white' title='Customers'>Customers</th>
                            <th class='text-white border border-white' title='Locations'>Locations</th>
                            <th class='text-white border border-white' title='Units'>Units</th>
                            <th class='text-white border border-white' title='Jobs'>Jobs</th>
                            <th class='text-white border border-white' title='Tickets'>Tickets</th>
                        </tr><tr class='form-desktop'>
                            <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Name'><input class='redraw form-control' type='text' name='Name' placeholder='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Customers'><input class='redraw form-control' type='text' name='Customers' placeholder='Customers' value='<?php echo isset( $_GET[ 'Customers' ] ) ? $_GET[ 'Customers' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Locations'><input class='redraw form-control' type='text' name='Locations' placeholder='Locations' value='<?php echo isset( $_GET[ 'Locations' ] ) ? $_GET[ 'Locations' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Units'><input class='redraw form-control' type='text' name='Units' placeholder='Units' value='<?php echo isset( $_GET[ 'Units' ] ) ? $_GET[ 'Units' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Jobs'><input class='redraw form-control' type='text' name='Jobs' placeholder='Jobs' value='<?php echo isset( $_GET[ 'Jobs' ] ) ? $_GET[ 'Jobs' ] : null;?>' /></th>
                            <th class='text-white border border-white' title='Tickets'><input class='redraw form-control' type='text' name='Tickets' placeholder='Tickets' value='<?php echo isset( $_GET[ 'Tickets' ] ) ? $_GET[ 'Tickets' ] : null;?>' /></th>
                        </tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php }
} else {?><script>document.location.href='../login.php?Forward=divisions.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>