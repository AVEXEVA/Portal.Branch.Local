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
    null,
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
      ||  $Privileges[ 'Customer' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Customer' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Customer' ][ 'Other_Privilege' ] < 4
  ){ require( '../404.html' );
  } else {
    \singleton\database::getInstance( )->query(
      null,
      " INSERT INTO Activity( [User], [Date], [Page] )
        VALUES( ?, ?, ? );",
      array(
        $_SESSION[ 'User' ],
        date( 'Y-m-d H:i:s' ),
        'collector.php'
      )
    );
?><!DOCTYPE html>
<html lang="en"style="min-height:100%;height:100%;background-image:url('http://www.nouveauelevator.com/Images/Backgrounds/New_York_City_Skyline.jpg');webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require( bin_meta . 'index.php');?>
	<title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);height:100%;">
    <div id='container' style='min-height:100%;height:100%;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;'>
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
        <div id="page-wrapper" class='content' style='background-color:transparent !important;<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
            <div class='row'>
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3><?php \singleton\fontawesome::getInstance( )->Customer();?>Collector: <?php echo $My_User['fFirst'] . ' ' . $My_User['Last'];?></h3>
                        </div>
                        <div class="panel-body">
                            <ul class="nav nav-tabs BankGothic">
                                <li class=''><a href="#" tab="overview-pills" 	onClick="asyncPage(this);" ><?php \singleton\fontawesome::getInstance( )->Info();?>Overview</a></li>
								<li class=''><a href='#' tab='tables-pills' onClick='asyncPage(this);'><?php \singleton\fontawesome::getInstance( )->Table();?>Tables</a></li>
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
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
