<?php
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $My_User = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($My_User);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Territory']) && $My_Privileges['Territory']['User_Privilege'] >= 4 && $My_Privileges['Territory']['Group_Privilege'] >= 4 && $My_Privileges['Territory']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){
      ?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
      $database->query(null,
        " INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
  			  VALUES(?,?,?)
  		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "territory.php?ID=" . $_GET['ID']));
        $r = $database->query(null,
            "SELECT TOP 1
                    Terr.ID   AS Territory_ID,
					          Terr.Name AS Territory_Name
			       FROM   nei.dbo.Terr
			       WHERE  Terr.ID = ?;"
        ,array($_GET['ID']));
        $Territory = sqlsrv_fetch_array($r);
if(isMobile()){?><!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
	<style>
		.panel {background-color:transparent !important;}
		/*().panel > div.panel-body.white-background {background-color:rgba(255,255,255,.7) !important;}*/
		.nav-tabs > li:not(.active) {background-color:rgba(255,255,255,.6) !important;}
		<?php if(isMobile()){?>
		.panel-body {padding:0px !important;}
		<?php }?>
	</style>
</head>

<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:#1d1d1d;color:white;">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;overflow-y:scroll;'>
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content' style='background-color:transparent !important;<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
				<h4 style='margin:0px;padding:10px;background-color:whitesmoke;border-bottom:1px solid darkgray;'><a href='location.php?ID=<?php echo $_GET['ID'];?>'><?php \singleton\fontawesome::getInstance( )->Territory();?> Territory: <?php echo $Territory['Territory_Name'];?></a></h4>
			<style>
				* { margin: 0 }
				.Home-Screen-Option,.row,.nav-icon,.nav-text {
					margin: 0;
				}

				.Screen-Tabs { overflow-x: hidden }

				.Screen-Tabs>div {
					--n: 1;
					display: flex;
					align-items: center;
					overflow-y: hidden;
					width: 100%; // fallback
					width: calc(var(--n)*100%);
					/*height: 50vw;*/ max-height: 100vh;
					transform: translate(calc(var(--tx, 0px) + var(--i, 0)/var(--n)*-100%));

					div {
						/*width: 100%; // fallback
						width: calc(100%/var(--n));*/
						user-select: none;
						pointer-events: none
					}

				}

				.smooth { transition: transform  calc(var(--f, 1)*.5s) ease-out }
				div.Home-Screen-Option.active {
					background-color: black !important;
					color:white !important;
					margin: auto;

				}
				.Home-Screen-Option:hover  {
					background-color: black !important;
					color: white !important;
				}
			</style>
			<div class ='Screen-Tabs shadower' style="margin: 0;border-bottom:3px solid black !important;">
				<div class='row' style="margin: 0">
					<?php if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4){
					?><div tab='information' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' style="margin: 0" onClick="someFunction(this,'territory-information.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon' style="margin: 0"><?php \singleton\fontawesome::getInstance( )->Information(3);?></div>
							<div class ='nav-text' style="margin: 0">Information</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 || $My_Privileges['Job']['Group_Privilege'] >= 4 && false){
					?><div tab='code' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-code.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
							<div class ='nav-text'>Code</div>
					</div><?php }?>
					<?php/* if(isset($My_Privileges['Contract']) && $My_Privileges['Contract']['User_Privilege'] >= 4 || $My_Privileges['Contract']['Group_Privilege'] >= 4 && false){
					?><div tab='contracts' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-contracts.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Contract(3);?></div>
							<div class ='nav-text'>Contracts</div>
					</div><?php }*/?>
					<?php if(isset($My_Privileges['Collection']) && $My_Privileges['Collection']['User_Privilege'] >= 4 ){
					?><div tab='collections' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-collections.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection(3);?></div>
							<div class ='nav-text'>Collections</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Contact']) && $My_Privileges['Contact']['User_Privilege'] >= 4 && false ) {
					?><div tab='contacts' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-contacts.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Info(3);?></div>
							<div class ='nav-text'>Contacts</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4){
					?><div tab='customer' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-customers.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
							<div class ='nav-text'>Customers</div>
					</div><?php }?>
					<?php if(false) {?>
					<div tab='feed' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-feed.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Activities(3);?></div>
							<div class ='nav-text'>Feed</div>
					</div><?php } ?>
					<?php if(isset($My_Privileges['Invoice']) && $My_Privileges['Invoice']['User_Privilege'] >= 4 && false ){
					?><div tab='invoices' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-invoices.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice(3);?></div>
							<div class ='nav-text'>Invoices</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && false ){
					?><div tab='jobs' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-jobs.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
							<div class ='nav-text'>Jobs</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4){
					?><div tab='locations' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-locations.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location(3);?></div>
							<div class ='nav-text'>Locations</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Legal']) && $My_Privileges['Legal']['User_Privilege'] >= 4 && false){
					?><div tab='legal' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-legal.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Legal(3);?></div>
							<div class ='nav-text'>Legal</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && false){
					?><div tab='maintenance' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-maintenance.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Maintenance(3);?></div>
							<div class ='nav-text'>Maintenance</div>
					</div><?php }?>
					<?php if(false) {?>
					<div tab='map' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-map.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Map(3);?></div>
							<div class ='nav-text'>Map</div>
					</div><?php } ?>
					<?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && false ){
					?><div tab='modernization' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-modernization.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Modernization(3);?></div>
							<div class ='nav-text'>Modernization</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Finances']) && $My_Privileges['Finances']['User_Privilege'] >= 4 && false){
					?><div tab='PNL' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-pnl.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
							<div class ='nav-text'>P&L</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Proposal']) && $My_Privileges['Proposal']['User_Privilege'] >= 4 && false ){
					?><div tab='proposals' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-proposals.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Proposal(3);?></div>
							<div class ='nav-text'>Proposals</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Service']) && $My_Privileges['Service']['User_Privilege'] >= 4 && false){
					?><div tab='service' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-service.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Phone(3);?></div>
							<div class ='nav-text'>Service</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && false){
					?><div tab='testing' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-testing.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Testing(3);?></div>
							<div class ='nav-text'>Testing</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Time']) && $My_Privileges['Time']['Group_Privilege'] >= 4 && false){
					?><div tab='timeline' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-timeline.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->History(3);?></div>
							<div class ='nav-text'>Timeline</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && false){
					?><div tab='units' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-units.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
							<div class ='nav-text'>Units</div>
					</div><?php }?>
					<?php if(isset($My_Privileges['Violation']) && $My_Privileges['Violation']['User_Privilege'] >= 4 && false){
					?><div tab='violations' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'territory-violations.php?ID=<?php echo $Territory['Territory_ID'];?>');">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation(3);?></div>
							<div class ='nav-text'>Violations</div>
					</div><?php }?>
				</div>
			</div>
			<div class='container-content'>

			</div>
			<!-- /.panel -->
		</div>
	</div>
    <!-- Bootstrap Core JavaScript -->
    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <?php require('bin/js/datatables.php');?>
    
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
	

	<script src="bin/js/fragment/formatTicket.js"></script>
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
	function someFunction(link,URL){
		$(link).siblings().removeClass('active');
		$(link).addClass('active');
		$("div.container-content").html("<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Texas</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>");
		$.ajax({
			url:"bin/php/element/territory/" + URL,
			success:function(code){
				$("div.container-content").html(code);
			}
		});
	}
	$(document).ready(function(){
		<?php if(isset($_SESSION['Forward-Backward']['Territory']) && $_SESSION['Forward-Backward']['Territory']['ID'] == $_GET['ID'] && FALSE){
			?>$("div.Screen-Tabs>div>div[Tab='<?php echo $_SESSION['Forward-Backward']['Territory']['Tab'];?>']").click();<?php
		} else {
			?>$("div.Screen-Tabs>div>div:first-child").click();<?php
		}?>
	});
	</script>
</body>
</html>
<?php
} else {
  require('../beta/territory.php');
}
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
