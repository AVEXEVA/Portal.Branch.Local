<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = \singleton\database::getInstance()->query(
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
  $result = \singleton\database::getInstance()->query((
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
      $NEI,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        'wall.php'
      )
    );?>
<!DOCTYPE html>
<html lang="en"style="min-height:100%;height:100%;background-image:url('http://www.nouveauelevator.com/Images/Backgrounds/New_York_City_Skyline.jpg');webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require( bin_meta . 'index.php');?>
	<title>Nouveau Illinois Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id='container'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
        <div id="page-wrapper" class='content'>
            <div class='row'>
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3><?php \singleton\fontawesome::getInstance( )->Admin();?>Admin: <?php echo $My_User['fFirst'] . ' ' . $My_User['Last'];?></h3>
                        </div>
                        <div class="panel-body">
                            <ul class="nav nav-tabs BankGothic">
                                <li class=''><a href="#" tab="overview-pills" onClick="asyncPage(this);"><?php \singleton\fontawesome::getInstance( )->Info();?>Overview</a></li>
								<li class=''><a href='#' tab='tables-pills'   onClick='asyncPage(this);'><?php \singleton\fontawesome::getInstance( )->Table();?>Tables</a></li>
                            </ul>
                            <br />
                            <div class="tab-content" id="main-tab-content">
								<div class='tab-pane fade in' id='loading-pills'>
									<?php require( bin_php . 'element/loading.php');?>
								                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
	</div>
</html>
<?php
    }
} else {require("404.html");}?>
