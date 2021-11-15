<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $Connection = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  *
            FROM    Connection
            WHERE   Connector = ?
                    AND Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($Connection);

    //User
    $User = \singleton\database::getInstance( )->query(null,
        "  SELECT   *, fFirst AS First_Name, Last as Last_Name
           FROM Emp
           WHERE ID= ?",
    array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);

    //Privileges
    $r = \singleton\database::getInstance( )->query(
        null,
        "   SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?;",
        array($_SESSION['User']));
    $Privileges = array();
    while($Privilege = sqlsrv_fetch_array( $r )){ $Privileges[$Privilege['Access_Table']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Violation'])
        && $Privileges['Violation']['User_Privilege'] >= 4
        && $Privileges['Violation']['Group_Privilege'] >= 4){$Privileged = TRUE;}

    if(!isset($Connection['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=violations.php';</script></head></html><?php }
    else {
      \singleton\database::getInstance( )->query(
        null,
        "   INSERT INTO Activity([User], [Date], [Page])
            VALUES(?,?,?);",
        array(
            $_SESSION['User'],
            date("Y-m-d H:i:s"),
            'violations.php'
        )
    );
?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css  . 'index.php');?>
    <?php require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div class='card card-primary my-3'>
            <div class='card-heading'>
              <div class='row g-0 px-3 py-2'>
                <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Violation</span></h5></div>
                <div class='col-2'>&nbsp;</div>
              </div>
            </div>
                <div class="form-mobile card-body bg-dark text-white"><form method='GET' action='locations.php'>
                  <div class='row g-0'>
                    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Search(1);?>Search:</div>
                    <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Search' value='<?php echo $Violation['Search'];?>' /></div>
                  </div>
                  <div class='row g-0'>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                    <div class='row g-0'>
    									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Name:</div>
    									<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Violation['Name'];?>' /></div>
    								</div>
                    <div class='row g-0'>
    									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Date(1);?>Date:</div>
    									<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Date' value='<?php echo $Violation['Date'];?>' /></div>
    								</div>
                    <div class='row g-0'>
    									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?>Location:</div>
    									<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Location' value='<?php echo $Violation['Location'];?>' /></div>
    								</div>
                    <div class='row'>
                      <div class='col-8'><select name='Status' class='form-control edit'>
    										<option value=''>Select</option>
    										<option value='0' <?php echo $Violation[ 'Status' ] == 0 ? 'selected' : null;?>>Active</option>
    										<option value='1' <?php echo $Violation[ 'Status' ] == 1 ? 'selected' : null;?>>Inactive</option>
    									</select></div>
                    </div>
                    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
                </form></div>
                <div class='card-body bg-dark'>
                    <table id='Table_Violations' class='display' cellspacing='0' width='100%'>
                        <thead><tr>
                            <th title='ID'>ID</th>
                            <th title='Customer'>Customer</th>
                            <th title='Location'>Location</th>
                            <th title="Date">Date</th>
                            <th title='Status'>Status</th>
                        </tr><tr class='form-desktop'>
                            <th title='ID'><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                            <th title='Customer'><input class='redraw form-control' type='text' name='Customer' placeholder='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' /></th>
                            <th title='Location'><input class='redraw form-control' type='text' name='Location' placeholder='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' /></th>
                            <th title="Date"><input class='redraw form-control' type='text' name='Date' placeholder='Date' value='<?php echo isset( $_GET[ 'Date' ] ) ? $_GET[ 'Date' ] : null;?>' /></th>

                            <th title='Status'><input class='redraw form-control' type='text' name='Status' placeholder='Status' value='<?php echo isset( $_GET[ 'Status' ] ) ? $_GET[ 'Status' ] : null;?>' /></th>
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
} else {?><html><head><script>document.location.href='../login.php?Forward=violations.php';</script></head></html><?php }?>
