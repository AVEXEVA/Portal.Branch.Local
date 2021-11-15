<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = \singleton\database::getInstance()->query(
    null,
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
  $result = \singleton\database::getInstance()->query(
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
  $User = sqlsrv_fetch_array( $result );
  //Privileges
  $result = \singleton\database::getInstance()->query(
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
      ||  !isset($Privileges[ 'Admin' ])
      ||  $Privileges[ 'Admin' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Admin' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Admin' ][ 'Other_Privilege' ] < 4
  ){
      ?><?php require( '../404.html' );?><?php
  } else {
    \singleton\database::getInstance()->query(
      null,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        'connection.php'
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
<body onload=''>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Connection();?>Connections</h3></div>
                        <div class="panel-body">
                            <table id='Table_Connections' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th>ID</th>
                                    <th>Page</th>
                                    <th>Timestamp</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
