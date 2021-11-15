<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = sqlsrv_query(
    $NEI,
    " SELECT  *
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
    " SELECT  *,
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
    " SELECT  Privilege.Access_Table,
              Privilege.User_Privilege,
              Privilege.Group_Privilege,
              Privilege.Other_Privilege
      FROM    Privilege
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $Privileges = array();
  if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
  if(     !isset( $Connection[ 'ID' ] )
      ||  !isset($Privileges[ 'Activity' ])
      ||  $Privileges[ 'Activity' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Activity' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Activity' ][ 'Other_Privilege' ] < 4
  ){
      ?><?php require( '../404.html' );?><?php
  } else {
    sqlsrv_query(
      $NEI,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        'activity.php'
      )
   );
?>
<!DOCTYPE html>
<html lang='en'>
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload=''>
  <div id='wrapper' class='<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>'>
    <?php require( bin_php . 'element/navigation.php' ); ?>
    <?php require( bin_php . 'element/loading.php' ); ?>
    <div id='page-wrapper' class='content'>
      <div class='panel panel-primary'>
        <div class='panel-heading'><h3>Dispatch<div style='float:right'><button onClick='refresh_get();' style='color:black;'>Refresh</button></div></h3></div>
        <div class='panel-body'>
          <table width='100%' class='table table-striped table-bordered table-hover' id='Table_Activities'>
            <thead>
                <tr>
                  <th>ID</th>
                  <th>DateTime</th>
                  <th>Person</th>
                  <th>Page</th>
                  <th>Parameters</th>
                </tr>
            </thead>
            <tfooter>
                <tr>
                  <th><input class='redraw form-control' type='text' name='ID' placeholder='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' /></th>
                  <th><input class='redraw form-control' type='text' name='Person' placeholder='Person' value='<?php echo isset( $_GET[ 'Person' ] ) ? $_GET[ 'Person' ] : null;?>' /></th>
                  <th><input class='redraw form-control' type='text' name='Page' placeholder='Page' value='<?php echo isset( $_GET[ 'Page' ] ) ? $_GET[ 'Page' ] : null;?>' /></th>
                  <th><input class='redraw form-control' type='text' name='Parameters' placeholder='Parameters' value='<?php echo isset( $_GET[ 'Parameters' ] ) ? $_GET[ 'Parameters' ] : null;?>' /></th>
                  <th><input class='redraw form-control' type='text' name='DateTime' placeholder='DateTime' value='<?php echo isset( $_GET[ 'DateTime' ] ) ? $_GET[ 'DateTime' ] : null;?>' /></th>
                </tr>
            </tfooter>
          </table>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='login.php?Forward=dispatch.php';</script></head></html><?php }?>
