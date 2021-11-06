<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT *
		FROM   Connection
		WHERE  Connection.Connector = ?
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name,
			   Emp.Field  AS Field
		FROM   Emp
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($r);
	$r = $database->query(null,"
		SELECT *
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$Privileges = array();
	if($r){while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}}
    if(	!isset($Connection['ID']) ){
				?><?php require('../404.html');?><?php }
    else {
		$_SESSION['Forward-Backward'] =isset($_SESSION['Foward-Backward']) && is_array($_SESSION['Foward-Backward']) ? $_SESSION['Forward-Backward'] : array();
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "locations.php"));
?><!DOCTYPE html>

<html lang="en">
<head>
    <title>Nouveau Texas | Portal</title>
    <?php 
        require( bin_meta . 'index.php' );
        require( bin_css  . 'index.php' );
        require( bin_js   . 'index.php' );
    ?>
</head>
<body onload='finishLoadingPage();'>
	<?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
    <div id="page-wrapper" class='content'>
  		<section class="container">
    		<div class="row">
          <div class='col-lg-12'>
            <button class='js-push-button' disabled>Enable Alerts</button>
          </div>
        </div>
      </section>
    </div>
    
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=locations.php';</script></head></html><?php }
?>
