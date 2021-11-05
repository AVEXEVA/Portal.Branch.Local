<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
        'read_and_close' => true
    ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
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
      ||  !isset($Privileges[ 'Location' ])
      ||  $Privileges[ 'Location' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Location' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Location' ][ 'Other_Privilege' ] < 4
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
        'contact.php'
      )
  );?>
        <!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;overflow-y:scroll;'>
        <?php require(bin_php.'element/navigation/index.php');?>
        <?php require(bin_php.'element/loading.php');?>
        <div id="page-wrapper" class='content' style='background-color:transparent !important;<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
			<h4 style='margin:0px;padding:10px;background-color:whitesmoke;border-bottom:1px solid darkgray;'><a href='contact.php?ID=<?php echo $_GET['ID'];?>'><?php \singleton\fontawesome::getInstance( )->Info();?> Contact: <?php echo $Contact['Contact'];?></a></h4>
			<div class='col-xs-4'>Name:</div>
			<div class='col-xs-8'><?php echo $Contact['Contact'];?></div>
			<div class='col-xs-4'>Phone:</div>
			<div class='col-xs-8'><?php echo $Contact['Phone'];?></div>
			<?php if(strlen($Contact['Email']) > 0){?>
			<div class='col-xs-4'>Email:</div>
			<div class='col-xs-8'><?php echo $Contact['Email'];?></div>
			<?php }?>
			<?php if(strlen($Contact['Fax']) > 0){?>
			<div class='col-xs-4'>Fax:</div>
			<div class='col-xs-8'><?php echo $Contact['Fax'];?></div>
			<?php }?>
			<div class='col-xs-4'>Street:</div>
			<div class='col-xs-8'><?php echo $Contact['Address'];?></div>
			<div class='col-xs-4'>City:</div>
			<div class='col-xs-8'><?php echo $Contact['City'];?></div>
			<div class='col-xs-4'>Zip:</div>
			<div class='col-xs-8'><?php echo $Contact['Zip'];?></div>
			<div class='col-xs-4'>State:</div>
			<div class='col-xs-8'><?php echo $Contact['State'];?></div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
