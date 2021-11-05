<?php 
session_start( [ 'read_and_close' => true ] );

require('cgi-bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User); 
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
	}
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "location.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
			SELECT Rol.*
			FROM   nei.dbo.Rol
			WHERE  Rol.ID      = ?
        ;",array($_GET['ID']));
        $Contact = sqlsrv_fetch_array($r);?><!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require( bin_meta . 'index.php');?>    
    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
	<style>
		.panel {background-color:transparent !important;}
		.panel > div.panel-body.white-background {background-color:rgba(255,255,255,.7) !important;}
		.nav-tabs > li:not(.active) {background-color:rgba(255,255,255,.6) !important;}
		<?php if(isMobile()){?>
		.panel-body {padding:0px !important;}
		<?php }?>
	</style>
</head>

<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;overflow-y:scroll;'>
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
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
	<style>
		.border-seperate {
			border-bottom:3px solid #333333;
		}
	</style>
    <!-- Bootstrap Core JavaScript -->
    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <?php require('cgi-bin/js/datatables.php');?>
    
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
	

	<script src="cgi-bin/js/fragment/formatTicket.js"></script>
    <style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    </style>
    <script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.categories.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>
	
	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAycrIPh5udy_JLCQHLNlPup915Ro4gPuY"></script>
	
	<script>
	function clickTab(Tab,Subtab){
		$("a[tab='" + Tab + "']").click();
		setTimeout(function(){$("a[tab='" + Subtab + "']").click();},1000);
	}
	$(document).ready(function(){
		$("a[tab='overview-pills']").click();
	});
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>