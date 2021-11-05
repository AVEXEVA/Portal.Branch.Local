<?php
session_start( [ 'read_and_close' => true ] );
require('cgi-bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Admin']) && $My_Privileges['Admin']['User_Privilege'] >= 4 && $My_Privileges['Admin']['Group_Privilege'] >= 4 && $My_Privileges['Admin']['Other_Privilege'] >= 4){
        	$Privileged = TRUE;
		}
    }
    //
    if(!isset($array['ID']) || !$Privileged){require('401.html');}
    else {
		sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "collector.php"));
?><!DOCTYPE html>
<html lang="en"style="min-height:100%;height:100%;background-image:url('http://www.nouveauelevator.com/Images/Backgrounds/New_York_City_Skyline.jpg');webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require( bin_meta . 'index.php');?>
	<title>Nouveau Illinois Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
	<style>
		.panel {background-color:transparent !important;}
		.panel > div.panel-body.white-background {background-color:rgba(255,255,255,.7) !important;}
		.nav-tabs > li:not(.active) {background-color:rgba(255,255,255,.6) !important;}
		.panel-heading {font-family: 'BankGothic' !important;}
		.shadow {box-shadow:0px 5px 5px 0px;}
		<?php if(isMobile()){?>
		.panel-body {padding:0px !important;}
		<?php }?>

			div#wrapper {
				overflow:scroll;
			}
		@media print {
			div#wrapper {overflow:visible;}
		}
	</style>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);height:100%;">
    <div id='container' style='min-height:100%;height:100%;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;'>
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
        <div id="page-wrapper" class='content' style='background-color:transparent !important;<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
            <div class='row'>
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3><?php \singleton\fontawesome::getInstance( )->Admin();?>Admin: <?php echo $My_User['fFirst'] . ' ' . $My_User['Last'];?></h3>
                        </div>
                        <script>
                        var asyncArray = new Array();
                        function asyncPage(a){
                        	var tab = $(a).attr("tab");
                        	$("div#main-tab-content>div.tab-pane.active").each(function(){$(this).removeClass("active");});
                        	$(a).parent().siblings().removeClass("active");
                        	$(a).parent().addClass("active");
							$("div#" + tab).remove();
							$("#loading-pills").addClass('active');
							$("#loading-pills .loading").css("display","block");
							$.ajax({
								url:"cgi-bin/php/element/admin/" + tab + ".php",
								method:"GET",
								success:function(code){$("div#main-tab-content").append(code);}
							});
                        }
						function asyncSubPage(a){
                        	var tab = $(a).attr("tab");
							var maintab = $(a).attr("main");
                        	$("div#sub-tab-content." + maintab + ">div.tab-pane.active").each(function(){$(this).removeClass("active");});
                        	$(a).parent().siblings().removeClass("active");
                        	$(a).parent().addClass("active");
							$("div#" + tab).remove();
							$("#loading-sub-pills").addClass('active');
							$("#loading-sub-pills .loading").css("display","block");
							$.ajax({
								url:"cgi-bin/php/element/admin/" + tab + ".php",
								method:"GET",
								data:"ID=<?php echo $_GET['ID'];?>",
								success:function(code){$("div#sub-tab-content." + maintab).append(code);}
							});
                        }
                        </script>
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
    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
    

	<script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.symbol.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.axislabels.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>
	<style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    div#map * {overflow:visible;}
    </style>
    <script>
	$(document).ready(function(){
		$("a[tab='overview-pills']").click();
	});
	</script>
</body>
</html>
<?php
    }
} else {require("404.html");}?>
