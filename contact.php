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
      ||  !isset($Privileges[ 'Contact' ])
      ||  $Privileges[ 'Contact' ][ 'User_Privilege' ]  < 4
      ||  $Privileges[ 'Contact' ][ 'Group_Privilege' ] < 4
      ||  $Privileges[ 'Contact' ][ 'Other_Privilege' ] < 4
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
  );
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php 
    	$_GET[ 'Bootstrap' ] = '5.1';
      	require( bin_meta . 'index.php');
      	require( bin_css  . 'index.php');
      	require( bin_js   . 'index.php');
    ?>
</head>
<body onload='finishLoadingPage();'>
    <div id='wrapper'>
        <?php require( bin_php . 'element/navigation.php' ); ?>
        <?php require( bin_php . 'element/loading.php' ); ?>
        <div id='page-wrapper' class='content'>
          <div class='card card-primary text-white'>
            <div class='card-heading'><?php \singleton\fontawesome::getInstance( )->User( 1 );?> Contact: <?php echo $Contact['Contact'];?></div>
            <div class='card-body bg-dark'>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Name:</div>
          			<div class='col-xs-8'><?php echo $Contact['Contact'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Phone( 1 );?> Phone:</div>
          			<div class='col-xs-8'><?php echo $Contact['Phone'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Email( 1 );?> Email:</div>
          			<div class='col-xs-8'><?php echo $Contact['Email'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Fax:</div>
          			<div class='col-xs-8'><?php echo $Contact['Fax'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Address( 1 );?> Street:</div>
          			<div class='col-xs-8'><?php echo $Contact['Address'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> City:</div>
          			<div class='col-xs-8'><?php echo $Contact['City'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Zip:</div>
          			<div class='col-xs-8'><?php echo $Contact['Zip'];?></div>
              </div>
              <div class='row'>
          			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> State:</div>
          			<div class='col-xs-8'><?php echo $Contact['State'];?></div>
              </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=contact<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
