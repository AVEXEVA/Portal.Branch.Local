<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = \singleton\database::getInstance( )->query(
      null,
      " SELECT *
      FROM   Connection
      WHERE  Connection.Connector = ?
               AND Connection.Hash  = ?;",
    array(
      $_SESSION['User'],
      $_SESSION['Hash']
    )
  );
    $Connection = sqlsrv_fetch_array( $r, SQLSRV_FETCH_ASSOC );
    $r = \singleton\database::getInstance( )->query(
      null,
      " SELECT  *,
              Emp.fFirst AS First_Name,
            Emp.Last   AS Last_Name
      FROM    Emp
      WHERE   Emp.ID = ?;",
    array( 
      $_SESSION[ 'User' ] 
    ) 
  );
    $User = sqlsrv_fetch_array( $r );
  $r = \singleton\database::getInstance( )->query(
    null,
    " SELECT *
      FROM   Privilege
      WHERE  Privilege.User_ID = ?;",
    array(
      $_SESSION['User']
    )
  );
  $Privileges = array( );
  if( $r ){ while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if( !isset( $Connection[ 'ID' ] )
      || !isset($Privileges[ 'Unit' ] )
        || $Privileges[ 'Unit' ][ 'User_Privilege' ]  < 4
        || $Privileges[ 'Unit' ][ 'Group_Privilege' ] < 4){
        ?><?php require('../404.html');?><?php }
    else {
    \singleton\database::getInstance( )->query(
      null,
      " INSERT INTO Activity([User], [Date], [Page])
        VALUES(?,?,?);",
      array(
        $_SESSION['User'],
        date("Y-m-d H:i:s"), 
        'units.php'
      )
    );
?><!DOCTYPE html>
<html lang='en'>
<head>
    
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php');?>
    <?php require( bin_js   . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
      <div class="card card-full card-primary border-0">
        <div class="card-heading"><h4><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?> Units</h4></div>
        <div class='card-body bg-dark'>
          <table id='Table_Units' class='display' cellspacing='0' width='100%'>
            <thead><tr class='text-center'>
              <th class='text-white border border-white' title='ID'>ID</th>
              <th class='text-white border border-white' title='Name'>Name</th>
              <th class='text-white border border-white' title='Customer'>Customer</th>
              <th class='text-white border border-white' title='Location'>Location</th>
              <th class='text-white border border-white' title='Type'>Type</th>
              <th class='text-white border border-white' title='Status'>Status</th>
              <th class='text-white border border-white' title='Last Ticket'>Last Ticket</th>
            </tr><tr>
              <th class='text-white border border-white' title='ID'><input class='redraw form-control' type='text' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null; ?>' placeholder='ID'  /></th>
              <th class='text-white border border-white' title='Name'><input class='redraw form-control' type='text' name='Name' value='<?php echo isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null; ?>' placeholder='Name' /></th>
              <th class='text-white border border-white' title='Customer'><input class='redraw form-control' type='text' name='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null; ?>' placeholder='Customer' /></th>
              <th class='text-white border border-white' title='Location'><input class='redraw form-control' type='text' name='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null; ?>' placeholder='Location' /></th>
              <th class='text-white border border-white' title='Type'><select class='redraw' name='Type'>
                                <option value=''>Select</option>
                                <?php 
                                  $result = \singleton\database::getInstance()->query(
                                    null,
                                    " SELECT    Elev.Type
                                      FROM    Elev
                                      GROUP BY  Elev.Type;",
                                  );
                                  if( $result ){while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){?><option value='<?php echo $row['Type'];?>'><?php echo $row['Type'];?></option><?php }}
                                ?>
                            </select></th>
              <th class='text-white border border-white' title='Status'><select class='redraw' name='Status'>
                <option value=''>Select</option>
                <option value='0'>Active</option>
                <option value='1'>Inactive</option>
                <option value='2'>Demolished</option>
              </select></th>
              <th><input class='form-control' type='text' value='disabled' disabled='disabled' /></th>
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
} else {?><script>document.location.href='../login.php?Forward=units.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>