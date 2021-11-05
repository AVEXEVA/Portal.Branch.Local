<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT *
		FROM   Connection
		WHERE  Connection.Connector = ?
			   AND Connection.Hash = ?
	;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
	$My_User    = $database->query(null,"
		SELECT Emp.*,
			   Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp
		WHERE  Emp.ID = ?
	;", array($_SESSION['User']));
	$My_User = sqlsrv_fetch_array($My_User);
	$My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
	$r = $database->query(null,"
		SELECT Privilege.Access_Table,
			   Privilege.User_Privilege,
			   Privilege.Group_Privilege,
			   Privilege.Other_Privilege
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
	$Privileged = False;
	if( isset($My_Privileges['Job'])
	  	&& $My_Privileges['Job']['Other_Privilege'] >= 4){
            $Privileged = True;}
	if(isset($My_Privileges['Job'])
		&& $My_Privileges['Job']['Group_Privilege'] >= 4){
			$r = $database->query(null,"
				SELECT Job.Loc AS Location_ID
				FROM   Job
				WHERE  Job.ID = ?
			;", array($_GET['ID']));
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
			$r = $database->query(null,"
				SELECT Tickets.ID
				FROM
				(
					(
						SELECT TicketO.ID
						FROM   TicketO
						WHERE  TicketO.LID       = ?
						       AND TicketO.fWork = ?
					)
					UNION ALL
					(
						SELECT TicketD.ID
						FROM   TicketD
						WHERE  TicketD.Loc       = ?
						       AND TicketD.fWork = ?
					)
				) AS Tickets
			;", array($Location_ID, $My_User['fWork'],$Location_ID, $My_User['fWork'],$Location_ID, $My_User['fWork']));
			if(is_array(sqlsrv_fetch_array($r))){$Privileged = True;}}
	if(isset($My_Privileges['Job'])
		&& $My_Privileges['Job']['User_Privilege'] >= 4
		&& is_numeric($_GET['ID'])){
			$r = $database->query(null,"
				SELECT Tickets.ID
				FROM
				(
					(
						SELECT TicketO.ID
						FROM   TicketO
						WHERE  TicketO.Job       = ?
						       AND TicketO.fWork = ?
					)
					UNION ALL
					(
						SELECT TicketD.ID
						FROM   TicketD
						WHERE  TicketD.Job       = ?
						       AND TicketD.fWork = ?
					)
				) AS Tickets
			;", array($_GET['ID'], $My_User['fWork'],$_GET['ID'], $My_User['fWork'],$_GET['ID'], $My_User['fWork']));
			if(is_array(sqlsrv_fetch_array($r))){$Privileged = True;}}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){?>Privileged Denied<?php }
	else {
    $database->query(null,"
      INSERT INTO Activity([User], [Date], [Page])
      VALUES(?,?,?)
    ;",array($_SESSION['User'],date("Y-m-d H:i:s"), "job.php?ID=" . $_GET['ID']));
       $r = $database->query(null,"
			SELECT TOP 1
                Job.ID                AS Job_ID,
                Job.fDesc             AS Job_Name,
                Job.fDate             AS Job_Start_Date,
                Job.BHour             AS Job_Budgeted_Hours,
                JobType.Type          AS Job_Type,
				Job.Remarks 		  AS Job_Remarks,
                Loc.Loc               AS Location_ID,
                Loc.ID                AS Location_Name,
                Loc.Tag               AS Location_Tag,
                Loc.Address           AS Location_Street,
                Loc.City              AS Location_City,
                Loc.State             AS Location_State,
                Loc.Zip               AS Location_Zip,
                Loc.Route             AS Route,
                Zone.Name             AS Division,
                OwnerWithRol.ID       AS Customer_ID,
                OwnerWithRol.Name     AS Customer_Name,
                OwnerWithRol.Status   AS Customer_Status,
                OwnerWithRol.Elevs    AS Customer_Elevators,
                OwnerWithRol.Address  AS Customer_Street,
                OwnerWithRol.City     AS Customer_City,
                OwnerWithRol.State    AS Customer_State,
                OwnerWithRol.Zip      AS Customer_Zip,
                OwnerWithRol.Contact  AS Customer_Contact,
                OwnerWithRol.Remarks  AS Customer_Remarks,
                OwnerWithRol.Email    AS Customer_Email,
                OwnerWithRol.Cellular AS Customer_Cellular,
                Elev.ID               AS Unit_ID,
                Elev.Unit             AS Unit_Label,
                Elev.State            AS Unit_State,
                Elev.Cat              AS Unit_Category,
                Elev.Type             AS Unit_Type,
                Emp.fFirst            AS Mechanic_First_Name,
                Emp.Last              AS Mechanic_Last_Name,
                Route.ID              AS Route_ID,
				Violation.ID          AS Violation_ID,
				Violation.fdate       AS Violation_Date,
				Violation.Status      AS Violation_Status,
				Violation.Remarks     AS Violation_Remarks
            FROM
                Job
                LEFT JOIN Loc           ON Job.Loc      = Loc.Loc
                LEFT JOIN Zone          ON Loc.Zone     = Zone.ID
                LEFT JOIN JobType       ON Job.Type     = JobType.ID
                LEFT JOIN OwnerWithRol  ON Job.Owner    = OwnerWithRol.ID
                LEFT JOIN Elev          ON Job.Elev     = Elev.ID
                LEFT JOIN Route         ON Loc.Route    = Route.ID
                LEFT JOIN Emp           ON Emp.fWork    = Route.Mech
				LEFT JOIN Violation     ON Job.ID       = Violation.Job
            WHERE
                Job.ID = ?
        ;",array($_GET['ID']));
        $Job = sqlsrv_fetch_array($r);

if(isMobile() || true ){?><!DOCTYPE html>
<html lang="en"style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
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
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:#1d1d1d;height:100%;color:white;">
    <div id='container' style='min-height:100%;height:100%;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;'>
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
        <?php if(!isMobile()){?><div id="page-wrapper" class='content' style='background-color:transparent !important;<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
		<div class="panel-heading"><h3><?php \singleton\fontawesome::getInstance( )->Job();?>Job: <?php echo $Job['Job_Name'];?></h3></div>
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
				url:"cgi-bin/php/element/job/" + tab + ".php",
				method:"GET",
				data:"ID=<?php echo $_GET['ID'];?>",
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
				url:"cgi-bin/php/element/job/" + tab + ".php",
				method:"GET",
				data:"ID=<?php echo $_GET['ID'];?>",
				success:function(code){$("div#sub-tab-content." + maintab).append(code);}
			});
		}
		</script>
		<div class="panel-body">
			<ul class="nav nav-tabs BankGothic">
				<li class=""><a href="#" tab="overview-pills" 	onClick="asyncPage(this);" ><?php \singleton\fontawesome::getInstance( )->Info();?>Overview</a></li>
				<li class=''><a href='#' tab='operations-pills' onClick='asyncPage(this);'><?php \singleton\fontawesome::getInstance( )->Operations();?>Operations</a>
				</li>
				<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['Other_Privilege'] >= 4){?><li class=''><a href='#' tab='sales-pills' onClick='asyncPage(this);'><?php \singleton\fontawesome::getInstance( )->Sales();?>Sales</a></li><?php }?>
				<?php if((!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator') && (isset($My_Privileges['Financials']) && $My_Privileges['Financials']['Other_Privilege'] >= 4)){?><li class=''><a href='#' tab='accounting-pills' onClick='asyncPage(this);'><?php \singleton\fontawesome::getInstance( )->Collection();?>Accounting</a></li><?php }?>
				<li class=''><a href='#' tab='tables-pills' onClick='asyncPage(this);'><?php \singleton\fontawesome::getInstance( )->Operations();?>Tables</a></li>
			</ul>
			<div class="tab-content" id="main-tab-content">
				<div class='tab-pane fade in' id='loading-pills'>
					<?php require('php/element/loading.php');?>
				</div>
			</div>
		</div>
	</div>
        </div><?php } else {?><div id="page-wrapper" class='content'>
			<h4 style='margin:0px;padding:10px;background-color:whitesmoke;border-bottom:1px solid darkgray;'><a href='job.php?ID=<?php echo $_GET['ID'];?>'><?php \singleton\fontawesome::getInstance( )->Job();?> Job: <?php echo $Job['Job_Name'];?></a></h4>
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
			<div class='row'>
				<div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-information.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Information(3);?></div>
						<div class ='nav-text'>Information</div>
				</div>
				<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='customer.php?ID=<?php echo $Job['Customer_ID'];?>'">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
							<div class ='nav-text'>Customer</div>
					</div><?php }?>
				<?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-code.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
						<div class ='nav-text'>Code</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Collection']) && $My_Privileges['Collection']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-collections.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection(3);?></div>
						<div class ='nav-text'>Collections</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Contract']) && $My_Privileges['Contract']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-contracts.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Contract(3);?></div>
						<div class ='nav-text'>Contracts</div>
				</div><?php }?>
				<div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-feed.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Activities(3);?></div>
						<div class ='nav-text'>Feed</div>
				</div>
				<?php if(isset($My_Privileges['Time']) && $My_Privileges['Time']['Group_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-hours.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Payroll(3);?></div>
						<div class ='nav-text'>Hours</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Invoice']) && $My_Privileges['Invoice']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-invoices.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice(3);?></div>
						<div class ='nav-text'>Invoices</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Legal']) && $My_Privileges['Legal']['User_Privilege'] >= 4 && false){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-legal.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Legal(3);?></div>
						<div class ='nav-text'>Legal</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href ='location.php?ID=<?php echo $Job['Location_ID'];?>';">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location(3);?></div>
						<div class ='nav-text'>Location</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Log']) && $My_Privileges['Log']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-log.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
						<div class ='nav-text'>Log</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-maintenance.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Maintenance(3);?></div>
						<div class ='nav-text'>Maintenance</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Map']) && $My_Privileges['Map']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-map.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Map(3);?></div>
						<div class ='nav-text'>Map</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-modernization.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Modernization(3);?></div>
						<div class ='nav-text'>Modernization</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Finances']) && $My_Privileges['Finances']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-pnl.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
						<div class ='nav-text'>P&L</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Proposal']) && $My_Privileges['Proposal']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-proposals.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Proposal(3);?></div>
						<div class ='nav-text'>Proposals</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Repair']) && $My_Privileges['Repair']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-repair.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Repair(3);?></div>
						<div class ='nav-text'>Repair</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-testing.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Testing(3);?></div>
						<div class ='nav-text'>Testing</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-tickets.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket(3);?></div>
						<div class ='nav-text'>Tickets</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Time']) && $My_Privileges['Time']['Group_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-timeline.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->History(3);?></div>
						<div class ='nav-text'>Timeline</div>
				</div><?php }?>
				<?php if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['Group_Privilege'] >= 4 && is_numeric($Job['Unit_ID']) && $Job['Unit_ID'] > 0){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='unit.php?ID=<?php echo $Job['Unit_ID'];?>';">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
						<div class ='nav-text'>Unit</div>
				</div><?php } elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['Group_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-units.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
						<div class ='nav-text'>Units</div>
				</div><?php }?>
				<?php
				$r = $database->query(null,"SELECT Violation.ID FROM Violation WHERE Violation.Job = ?",array($_GET['ID']));
				if($r){
					$Violation = sqlsrv_fetch_array($r)['ID'];
					if($Violation && $Violation > 0){
						if(isset($My_Privileges['Violation']) && $My_Privileges['Violation']['User_Privilege'] >= 4){
					?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='violation.php?ID=<?php echo $Violation;?>';">
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation(3);?></div>
							<div class ='nav-text'>Violation</div>
					</div><?php
						}
					}
				}?>
				<?php if(isset($My_Privileges['User']) && $My_Privileges['User']['User_Privilege'] >= 4){
				?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'job-workers.php?ID=<?php echo $_GET['ID'];?>');">
						<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Users(3);?></div>
						<div class ='nav-text'>Workers</div>
				</div><?php }?>

			</div>
		</div>
		<div class='container-content'></div>
	</div><?php }?>

    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <?php require('cgi-bin/js/datatables.php');?>
    
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
    

    <!-- Custom Date Filters-->
    



    <style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    div#map * {overflow:visible;}
    </style>
    <script>
		function clickTab(Tab,Subtab){
			$("a[tab='" + Tab + "']").click();
			setTimeout(function(){
				$("a[tab='" + Subtab + "']").click();
			},2500);
		}
		$(document).ready(function(){
			$("a[tab='overview-pills']").click();
		});
    </script>
	<script>
		function someFunction(link,URL){
			$(link).siblings().removeClass('active');
			$(link).addClass('active');
			$.ajax({
				url:"cgi-bin/php/element/job/" + URL,
				success:function(code){
					$("div.container-content").html(code);
				}
			});
		}
		$(document).ready(function(){
			$("div.Screen-Tabs>div>div:first-child").click();
		});
	</script>
    <?php require('cgi-bin/js/flotcharts.php');?>
</body>
</html>
<?php
}
    }
} else {require("404.html");}?>
