<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION[ 'User'],$_SESSION[ 'Hash' ])){
    //Connection
    $result = \singleton\database::getInstance( )->query(
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
    $Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);

    //User
    $result = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  *,
                    Emp.fFirst AS First_Name,
                    Emp.Last   AS Last_Name
            FROM    Emp
            WHERE   Emp.ID = ?
    ;",array($_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array($result);

    //Privileges
    $result = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  *
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    if($result){while($Privilege = sqlsrv_fetch_array($result)){$Privileges[$Privilege[ 'Access_Table' ]] = $Privilege;}}
    if( !isset($Connection[ 'ID' ])
        || !isset($Privileges[ 'Customer' ])
            || $Privileges[ 'Customer' ][ 'User_Privilege' ]  < 4
            || $Privileges[ 'Customer' ][ 'Group_Privilege' ] < 4
            || $Privileges[ 'Customer' ][ 'Other_Privilege' ] < 4){
                ?><?php require('../404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          $NEI,
          " INSERT INTO Activity([User], [Date], [Page] ) 
            VALUES( ?, ?, ? );",
          array(
            $_SESSION['User'],
            date('Y-m-d H:i:s'),
            'customers.php'
        )
      );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title>Nouveau Elevator Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css  . 'index.php');?>
    <?php require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id='page-wrapper' class='content'>
            <div class='card card-full card-primary border-0'>
                <div class='card-heading'><h4><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Customers</h4></div>
                <div class='card-body bg-dark'>
                    <table id='Table_Customers' class='display' cellspacing='0' width='100%'>
                        <thead><tr>
                            <th class='text-white border border-white' title='ID'>ID</th>
                            <th class='text-white border border-white' title='Name'>Name</th>
                            <th class='text-white border border-white' title='Status'>Status</th>
                            <th class='text-white border border-white' title='Locations'>Locations</th>
                            <th class='text-white border border-white' title='Units'>Units</th>
                            <th class='text-white border border-white' title='Jobs'>Jobs</th>
                            <th class='text-white border border-white' title='Tickets'>Tickets</th>
                        </tr><tr>
                            <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null; ?>' placeholder='ID' /></th>
                            <th class='text-white border border-white' title='Name'><input class='redraw form-control' type='text' name='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null; ?>' placeholder='Name' /></th>
                            <th class='text-white border border-white' title='Status'><input class='redraw form-control' type='text' name='Status' value='<?php echo isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null; ?>' placeholder='Status' /></th>
                            <th class='text-white border border-white' title='Locations'><input class='redraw form-control' type='text' name='Locations' value='<?php echo isset( $_GET[ 'Locations' ] ) ? $_GET[ 'Locations' ] : null; ?>' placeholder='Locations' /></th>
                            <th class='text-white border border-white' title='Units'><input class='redraw form-control' type='text' name='Units' value='<?php echo isset( $_GET[ 'Units' ] ) ? $_GET[ 'Units' ] : null; ?>' placeholder='Units' /></th>
                            <th class='text-white border border-white' title='Jobs'><input class='redraw form-control' type='text' name='Units' value='<?php echo isset( $_GET[ 'Jobs' ] ) ? $_GET[ 'Jobs' ] : null; ?>' placeholder='Jobs' /></th>
                            <th class='text-white border border-white' title='Tickets'><input class='redraw form-control' type='text' name='Units' value='<?php echo isset( $_GET[ 'Tickets' ] ) ? $_GET[ 'Tickets' ] : null; ?>' placeholder='Tickets' /></th>
                        </tr></thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=customers.php';</script></head></html><?php }?>
