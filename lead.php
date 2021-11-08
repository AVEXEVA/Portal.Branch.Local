<?php 
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = $database->query(null,"
		SELECT * 
		FROM   Privilege 
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID']) 
	   	|| !isset($My_Privileges['Admin'])
	  		|| $My_Privileges['Admin']['User_Privilege']  < 4
	  		|| $My_Privileges['Admin']['Group_Privilege'] < 4
	  		|| $My_Privileges['Admin']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$r =$database->query(null,"
			SELECT   Lead.ID           AS ID,
				     Lead.fDesc        AS Name,
				     Lead.Address      AS Street,
				     Lead.City         AS City,
				     Lead.State        AS State,
				     Lead.Zip          AS Zip,
				     OwnerWithRol.Name AS Customer
			FROM     nei.dbo.Lead
					 LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Lead.Owner
			ORDER BY Lead.fDesc ASC
		",array(),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
		$Lead = sqlsrv_fetch_array($r);
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>    
    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload="finishLoadingPage();" id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
		<br><br>
	<div class='col-md-6' style=''>
		<div class="panel panel-primary">
			<div class="panel-heading"> Information</div>
			<div class='panel-body' style='padding:15px;'>
				<div class='col-xs-4' style='text-align:right;'><b>ID:</b></div>
				<div class='col-xs-8'><?php echo strlen($_GET['ID']) > 0 ? proper($_GET['ID']) : "Unlisted";?></div>
				<div class='col-xs-4' style='text-align:right;'><b>Name:</b></div>
				<div class='col-xs-8'><?php echo $Lead['Name'];?></div>
			</div>
			<div class="panel-heading"> Address</div>
			<div class='panel-body' style='padding:15px;'>
				<div class='col-xs-4' style='text-align:right;'><b>Street:</b></div>
				<div class='col-xs-8'><?php echo strlen($Lead['Street']) > 0 ?  $Lead['Street'] : "Unlisted";?></div>
			</div>
			<div class='panel-body' style='padding:15px;'>
				<div class='col-xs-4' style='text-align:right;'><b>City:</b></div>
				<div class='col-xs-8'><?php echo strlen($Lead['City']) > 0 ? proper($Lead['City']) : "Unlisted";?></div>
			</div>
			<div class='panel-body' style='padding:15px;'>
				<div class='col-xs-4' style='text-align:right;'><b>State:</b></div>
				<div class='col-xs-8'><?php echo strlen($Lead['State']) > 0 ? proper($Lead['State']) : "Unlisted";?></div>
			</div>
			<div class='panel-body' style='padding:15px;'>
				<div class='col-xs-4' style='text-align:right;'><b>Customer:</b></div>
				<div class='col-xs-8'><?php echo strlen($_GET["Customer"]) > 0 ? proper($_GET['Unit_Type']) : "Unlisted";?></div>
			</div>
		</div>
	</div>
</div>
								
    
    
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=violation<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>