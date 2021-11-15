<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $result = \singleton\database::getInstance( )->query(
      null,
      "   SELECT    *
		      FROM      Connection
		      WHERE     Connection.Connector = ?
		      AND       Connection.Hash  = ?;",
    array(
        $_SESSION[ 'User' ],
        $_SESSION[ 'Hash' ]
    )
);
    $Connection = sqlsrv_fetch_array($result,SQLSRV_FETCH_ASSOC);
    $result = \singleton\database::getInstance( )->query(
      null,
      "   SELECT    *,
		                Emp.fFirst AS First_Name,
			              Emp.Last   AS Last_Name
      		FROM      Emp
      		WHERE     Emp.ID = ?;",
      array(
        $_SESSION['User']
    )
);
    $User = sqlsrv_fetch_array($result);
	$result = \singleton\database::getInstance( )->query(
      null,
      "   SELECT    *
		      FROM   Privilege
		      WHERE  Privilege.User_ID = ?;",
  array(
    $_SESSION[ 'User' ]
    )
);
	$Privileges = array();
	if($result){while($Privilege = sqlsrv_fetch_array($result)){$Privileges[$Privilege[ 'Access_Table' ]] = $Privilege;}}
    if(	!isset($Connection[ 'ID' ])
	   	|| !isset($Privileges[ 'Proposal' ])
	  		|| $Privileges[ 'Proposal' ][ 'User_Privilege' ]  < 4
	  		|| $Privileges[ 'Proposal' ][ 'Group_Privilege' ] < 4
	  	    || $Privileges[ 'Proposal' ][ 'Other_Privilege' ] < 4){
				?><?php require('../404.html');?><?php }
    else {
		\singleton\database::getInstance( )->query(
      null,
      "   INSERT INTO Activity([User], [Date], [Page])
			    VALUES(?,?,?);",
    array(
      $_SESSION[ 'User' ],
          date("Y-m-d H:i:s"),
              "requisitions.php")
 );
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(bin_php  . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div class='card card-primary my-3'>
            <div class='card-heading'>
              <div class='row g-0 px-3 py-2'>
                <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Requisitions</span></h5></div>
                <div class='col-2'>&nbsp;</div>
              </div>
            </div>
          <div style='float:right;' onClick="document.location.href='purchase-requisition.php';"><?php \singleton\fontawesome::getInstance( )->Add(1);?></div>
          <div style='clear:both;'></div>
        </div>
				<div class="panel-body">
					<table id='Table_Requisitions' class='display' cellspacing='0' width='100%'>
						<thead>
							<th>ID</th>
							<th>User</th>
							<th>Date</th>
							<th>Required</th>
							<th>Location</th>
							<th>Drop Off</th>
							<th>Unit</th>
							<th>Job</th>
						</thead>
					</table>
				</div>
      </div>
  </div>
</div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
