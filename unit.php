<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $Connection = sqlsrv_query(
        $NEI,
        "   SELECT  Connection.* 
            FROM    Connection 
            WHERE   Connection.Connector = ? 
                    AND Connection.Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($Connection);

    //User
    $User = sqlsrv_query(
        $NEI,
        "   SELECT  Emp.*, 
                    Emp.fFirst  AS First_Name, 
                    Emp.Last    AS Last_Name 
            FROM    Emp 
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION['User']
        )
    );
    $User = sqlsrv_fetch_array($User);

    //Privileges
    $r = sqlsrv_query(
        $NEI,
        "   SELECT  Privilege.Access_Table, 
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
    while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Unit']) 
        && $Privileges['Unit']['User_Privilege'] >= 4 
        && $Privileges['Unit']['Group_Privilege'] >= 4 
        && $Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Unit']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = sqlsrv_query(  
            $NEI,
            "   SELECT  Sum( Ticket.Count ) AS Count 
                FROM    (
                            SELECT  Ticket.Unit,
                                    Ticket.Field,
                                    Sum( Ticket.Count ) AS Count
                            FROM (
                                (
                                    SELECT      TicketO.LElev AS Unit,
                                                TicketO.fWork AS Field,
                                                Count( TicketO.ID ) AS Count
                                    FROM        TicketO
                                    GROUP BY    TicketO.LElev,
                                                TicketO.fWork
                                ) UNION ALL (
                                    SELECT      TicketD.Elev AS Unit,
                                                TicketD.fWork AS Field, 
                                                Count( TicketD.ID ) AS Count
                                    FROM        TicketD
                                    GROUP BY    TicketD.Elev,
                                                TicketD.fWork
                                )
                            ) AS Ticket
                            GROUP BY    Ticket.Unit,
                                        Ticket.Field
                        ) AS Ticket
                        LEFT JOIN Emp AS Employee ON Ticket.Field = Employee.fWork
                WHERE   Employee.ID = ?
                        AND Ticket.Unit = ?;",
            array( 
                $_SESSION[ 'User' ],
                $_GET[ 'ID' ]
            )
        );
        $Tickets = 0;
        if ( $r ){ $Tickets = sqlsrv_fetch_array( $r )[ 'Count' ]; }
        $Privileged =  $Tickets > 0 ? true : false;
    }
    if(     !isset( $Connection[ 'ID' ] )  
        ||  !$Privileged 
        ||  !is_numeric( $_GET[ 'ID' ] ) ){
            ?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,
          " SELECT  TOP 1
                    Elev.ID,
                    Elev.Unit           AS Unit,
                    Elev.State          AS State,
                    Elev.Cat            AS Category,
                    Elev.Type           AS Type,
                    Elev.Building       AS Building,
                    Elev.Since          AS Since,
                    Elev.Last           AS Last,
                    Elev.Price          AS Price,
                    Elev.fDesc          AS Description,
                    Loc.Loc             AS Location_ID,
                    Loc.ID              AS Name,
                    Loc.Tag             AS Tag,
                    Loc.Tag             AS Location_Tag,
                    Loc.Address         AS Street,
                    Loc.City            AS City,
                    Loc.State           AS Location_State,
                    Loc.Zip             AS Zip,
                    Loc.Route           AS Route,
                    Zone.Name           AS Zone,
                    OwnerWithRol.Name   AS Customer_Name,
                    OwnerWithRol.ID     AS Customer_ID,
            				OwnerWithRol.Contact AS Customer_Contact,
            				OwnerWithRol.Address AS Customer_Street,
            				OwnerWithRol.City 	AS Customer_City,
            				OwnerWithRol.State 	AS Customer_State,
                    Emp.ID AS Route_Mechanic_ID,
                    Emp.fFirst AS Route_Mechanic_First_Name,
                    Emp.Last AS Route_Mechanic_Last_Name
            FROM    Elev
                    LEFT JOIN Loc           ON Elev.Loc = Loc.Loc
                    LEFT JOIN Zone          ON Loc.Zone = Zone.ID
                    LEFT JOIN OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                    LEFT JOIN Route ON Loc.Route = Route.ID
                    LEFT JOIN Emp ON Route.Mech = Emp.fWork
            WHERE   Elev.ID = ?;",
          array(
            $_GET[ 'ID' ]
          )
        );
        $Unit = sqlsrv_fetch_array($r);
        $r = sqlsrv_query(
          $NEI,
          " SELECT  *
            FROM    ElevTItem
            WHERE   ElevTItem.ElevT    = 1
                    AND ElevTItem.Elev = ?;",
          array(
            $_GET[ 'ID' ]
          )
        );
        if( $r ){while( $array = sqlsrv_fetch_array( $r ) ){ $Unit[ $array[ 'fDesc' ] ] = $array[ 'Value' ]; } }
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
    <title>Nouveau Elevator Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
	<style>
		* { margin: 0 }
		html {
			min-height:100%;
			height:100%;
			webkit-background-size:cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
			height:100%;
		}
		body {
			min-height:100%;
			background-size:cover;
			background-color:#1d1d1d;
		}
		#wrapper {
			height:100%;
			overflow-y:scroll;
		}
		#page-wrapper>h4 {
			margin:0px;
			padding:10px;
			background-color:whitesmoke;
			border-bottom:1px solid darkgray;
		}
		.Screen-Tabs {
			margin:0;
			border-bottom:3px solid black !important;
			overflow-x: hidden;
		}
		.nav-tab,.row,.nav-icon,.nav-text {
			margin: 0;
		}
		.Screen-Tabs>div {
			--n: 1;
			display: flex;
			align-items: center;
			overflow-y: hidden;
			width: 100%; // fallback
			width: calc(var(--n)*100%);
			max-height: 100vh;
			transform: translate(calc(var(--tx, 0px) + var(--i, 0)/var(--n)*-100%));
			div {
				user-select: none;
				pointer-events: none
			}
		}
		.panel {background-color:transparent !important;}
		.panel > div.panel-body.white-background {background-color:rgba(255,255,255,.7) !important;}
		nav-tabs > li:not(.active) {background-color:rgba(255,255,255,.6) !important;} 
		.smooth { transition: transform  calc(var(--f, 1)*.5s) ease-out }
		.nav-tab {
			text-align:center;
		}
		div.nav-tab.active {
			background-color: gold !important;
			color:black;
			margin: auto;
		}
		.nav-tab:hover  {
			background-color: gold !important;
			color: black !important;
		}
		
		
		.border-seperate { border-bottom:3px solid #333333; }
		div.column {display:inline-block;vertical-align:top;}
	    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
	    div.data {display:inline-block;width:300px;vertical-align:top;}
	    .panel-heading {
		    background-color: #d0d0d0 !important;
		    color:black !important;
		    font-size:18px !important;
		    padding: 0px !important;
		}
		.panel-body {
		    font-size:16px; 
		    padding: 10px 0px 10px 0px; 
		}
		.panel-heading h4 {
		    margin:0px;
		    padding:10px;
		}
		button {
			width:100%;
			font-size:16px;
		}
		input {
			width:100%;
			font-size:16px;
		} 
		.panel-body>.row {
			min-height:35px;
			vertical-align:middle;
			font-size:16px;
		}
		.panel-body>.row>.col-xs-8>.row {
			max-width:350px;
		}
		div#map { height:350px; }
	</style>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();' style='height:100%;background-color:#151515;'>
    <div id="wrapper"  style='height:100%;overflow-x:hidden;' class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
		<?php if(!isMobile() && FALSE){?>
        <style>
        .panel-primary, .panel-primary .panel-body {
          background-color:white !important;
          color:black !important;
        }
        body {
          background-color:white !important;
          color:black !important;
        }
        </style>
        <div id="page-wrapper" class='content' style='background-color:transparent !important;overflow-y:scroll;height:100%;<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
            <div class='row'>
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading" onClick="document.location.href='unit.php?ID=<?php echo $Unit['ID'];?>';"><h3><?php $Icons->Unit();?> Unit: <?php echo strlen($Unit['State']) > 0 ? $Unit['State'] : $Unit['Unit'];?></h3></div>
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
								url:"cgi-bin/php/element/unit/" + tab + ".php",
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
								url:"cgi-bin/php/element/unit/" + tab + ".php",
								method:"GET",
								data:"ID=<?php echo $_GET['ID'];?>",
								success:function(code){$("div#sub-tab-content." + maintab).append(code);}
							});
                        }
                        </script>
                        <div class="panel-body">
                            <ul class="nav nav-tabs BankGothic">
                                <li class=""><a href="#" tab="overview-pills" 	onClick="asyncPage(this);" ><?php $Icons->Info();?>Overview</a></li>
								<li class=''><a href='#' tab='operations-pills' onClick='asyncPage(this);'><?php $Icons->Operations();?>Operations</a>
								</li>
								<?php if(isset($Privileges['Customer']) && $Privileges['Customer']['Other_Privilege'] >= 4){?><li class=''><a href='#' tab='sales-pills' onClick='asyncPage(this);'><?php $Icons->Sales();?>Sales</a></li><?php }?>
								<?php if((!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator') && (isset($Privileges['Financials']) && $Privileges['Financials']['Other_Privilege'] >= 4)){?><li class=''><a href='#' tab='accounting-pills' onClick='asyncPage(this);'><?php $Icons->Collection();?>Accounting</a></li><?php }?>
								<li class=''><a href='#' tab='tables-pills' onClick='asyncPage(this);'><?php $Icons->Operations();?>Tables</a></li>
                            </ul>
                            <div class="tab-content" id="main-tab-content">
								<div class='tab-pane fade in' id='loading-pills'>
									<?php require('php/element/loading.php');?>
								</div>
                            </div>
                        </div>
                        <!-- /.panel-body -->
                    </div>
                    <!-- /.panel -->
                </div>
            </div>
        </div><?php } else {?>
		<div id="page-wrapper" class='content' style=''>
			<h4><a href='unit.php?ID=<?php echo $_GET['ID'];?>'><?php $Icons->Unit();?> Unit: <?php echo $Unit['Unit'];?></a></h4>
			<div class='Screen-Tabs shadower'>
				<div class='row'>
					<div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'information.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Info(3);?></div>
							<div class='nav-text'>Information</div>
					</div>
         	 <?php if($Unit['Type'] == 'Elevator' && isset($Privileges['Unit']) && ($Privileges['Unit']['User_Privilege'] >= 4 || $Privileges['Unit']['Group_Privilege'] >= 4)){
					?><div tab='cod' class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-items.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><img src='media/images/icons/elevator.png' width='auto' height='35px' /></div>
							<div class ='nav-text'>Elevator</div>
					</div><?php }?>
          <?php
          $r = sqlsrv_query($database_Device,"SELECT CM_Fault.* FROM Device.dbo.CM_Unit LEFT JOIN Device.dbo.CM_Fault ON CM_Unit.Location = CM_Fault.Location AND CM_Unit.Unit = CM_Fault.Unit WHERE CM_Unit.Elev_ID = ?",array($_GET['ID']));
          if($r && is_array(sqlsrv_fetch_array($r)) && ($Privileges['Unit']['User_Privilege'] >= 4 || $Privileges['Unit']['Group_Privilege'] >= 4)){
					?><div tab='cod' class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-faults.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><img src='media/images/icons/fault.png' width='auto' height='35px' /></div>
							<div class ='nav-text'>Faults</div>
					</div><?php }?>
          <?php if($Unit['Type'] == 'Elevator' && isset($Privileges['Unit']) && $Privileges['Unit']['User_Privilege'] >= 4 || $Privileges['Unit']['Group_Privilege'] >= 4){
					?><div tab='cod' class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-survey-sheet.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Sitemap(3);?></div>
							<div class ='nav-text'>Survey</div>
					</div><?php }?>
          <?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4 || $Privileges['Job']['Group_Privilege'] >= 4){
					?><div tab='cod' class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-code.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Job(3);?></div>
							<div class ='nav-text'>Code</div>
					</div><?php }?>
					<?php if(isset($Privileges['Customer']) && $Privileges['Customer']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-customer.php?ID=<?php echo $Unit['Customer_ID'];?>');">
							<div class='nav-icon'><?php $Icons->Customer(3);?></div>
							<div class ='nav-text'>Customer</div>
					</div><?php }?>
					<?php if(isset($Privileges['Collection']) && $Privileges['Collection']['User_Privilege'] >= 4 && FALSE){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-collection.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Collection(3);?></div>
							<div class ='nav-text'>Collections</div>
					</div><?php }?>
					<div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-feed.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Activities(3);?></div>
							<div class ='nav-text'>Feed</div>
					</div>
					<?php if(isset($Privileges['Time']) && $Privileges['Time']['Group_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-hours.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Hours(3);?></div>
							<div class ='nav-text'>Hours</div>
					</div><?php }?>
					<?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-invoices.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Invoice(3);?></div>
							<div class ='nav-text'>Invoices</div>
					</div><?php }?>

					<?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-jobs.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Job(3);?></div>
							<div class ='nav-text'>Jobs</div>
					</div><?php }?>
					<?php if(isset($Privileges['Log']) && $Privileges['Log']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-log.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Job(3);?></div>
							<div class ='nav-text'>Log</div>
					</div><?php }?>
					<?php if(isset($Privileges['Legal']) && $Privileges['Legal']['User_Privilege'] >= 4 && false){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-legal.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Legal(3);?></div>
							<div class ='nav-text'>Legal</div>
					</div><?php }?>
					<?php if(isset($Privileges['Location']) && $Privileges['Location']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='location.php?ID=<?php echo $Unit['Location_ID'];?>';">
							<div class='nav-icon'><?php $Icons->Location(3);?></div>
							<div class ='nav-text'>Location</div>
					</div><?php }?>
					<?php /*if(isset($Privileges['Maintenance']) && $Privileges['Maintenance']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-maintenance.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Maintenance(3);?></div>
							<div class ='nav-text'>Maintenance</div>
					</div><?php }*/?>
					<?php /*if(isset($Privileges['Map']) && $Privileges['Map']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-map.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Map(3);?></div>
							<div class ='nav-text'>Map</div>
					</div><?php }*/?>
					<?php /*if(isset($Privileges['Modernization']) && $Privileges['Modernization']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-modernization.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Modernization(3);?></div>
							<div class ='nav-text'>Modernization</div>
					</div><?php }*/?>
					<?php if(isset($Privileges['Finances']) && $Privileges['Finances']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-pnl.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Financial(3);?></div>
							<div class ='nav-text'>P&L</div>
					</div><?php }?>

					<?php /*if(isset($Privileges['Repair']) && $Privileges['Repair']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-repair.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Repair(3);?></div>
							<div class ='nav-text'>Repair</div>
					</div><?php }*/?>
					<?php if(isset($Privileges['Route']) && $Privileges['Route']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick=document.location.href="route.php?ID=<?php echo $Unit['Route'];?>">
							<div class='nav-icon'><?php $Icons->Route(3);?></div>
							<div class ='nav-text'>Route</div>
					</div><?php }?>
					<?php if(isset($Privileges['Service']) && $Privileges['Service']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-service.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Phone(3);?></div>
							<div class ='nav-text'>Service</div>
					</div><?php }?>
					<?php /*if(isset($Privileges['Testing']) && $Privileges['Testing']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-testing.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Testing(3);?></div>
							<div class ='nav-text'>Testing</div>
					</div><?php }*/?>
					<?php if(isset($Privileges['Ticket']) && $Privileges['Ticket']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-tickets.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Ticket(3);?></div>
							<div class ='nav-text'>Tickets</div>
					</div><?php }?>
					<?php if(isset($Privileges['Time']) && $Privileges['Time']['Group_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-timeline.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->History(3);?></div>
							<div class ='nav-text'>Timeline</div>
					</div><?php }?>
					<?php if(isset($Privileges['Violation']) && $Privileges['Violation']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-violations.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Violation(3);?></div>
							<div class ='nav-text'>Violations</div>
					</div><?php }?>
					<?php if(isset($Privileges['User']) && $Privileges['User']['User_Privilege'] >= 4){
					?><div class='nav-tab col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'unit-workers.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Users(3);?></div>
							<div class ='nav-text'>Workers</div>
					</div><?php }?>

				</div>
			</div>
			<div id='container-content' class='container-content'>

			</div>
		<?php }?>
    </div>
	</div>
	<style>
		.border-seperate {
			border-bottom:3px solid #333333;
		}
	</style>
    <!-- Bootstrap Core JavaScript -->
    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.js"></script>

    <?php require('cgi-bin/js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

	<script src="cgi-bin/js/fragment/formatTicket.js"></script>
    <style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    </style>
    <script src="../vendor/flot/excanvas.min.js"></script>
    <script src="../vendor/flot/jquery.flot.js"></script>
    <script src="../vendor/flot/jquery.flot.pie.js"></script>
    <script src="../vendor/flot/jquery.flot.resize.js"></script>
    <script src="../vendor/flot/jquery.flot.time.js"></script>
    <script src="../vendor/flot/jquery.flot.categories.js"></script>
    <script src="../vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>

	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAycrIPh5udy_JLCQHLNlPup915Ro4gPuY"></script>
	<script>
		function someFunction(link,URL){
			$(link).siblings().removeClass('active');
			$(link).addClass('active');
      $("div.container-content").html("<div style='text-align:center;style='color:white !important;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>");
			$.ajax({
				url:"cgi-bin/php/element/unit/" + URL,
				success:function(code){
					$("div.container-content").html(code);
				}
			});
		}
		$(document).ready(function(){
			<?php if(isset($_GET['Ticket_Update']) && $_GET['Ticket_Update'] == 1){?>someFunction(this,'unit-controller.php?ID=10747');<?php }
      else {?>$("div.Screen-Tabs>div>div:first-child").click();<?php }?>
		});
	</script>
  <script data-pagespeed-no-defer src="https://hammerjs.github.io/dist/hammer.min.js"></script>
  <script>
  // credit: http://www.javascriptkit.com/javatutors/touchevents2.shtml
  function swipedetect(callback){

      var touchsurface = document.getElementById('container-content'),
      swipedir,
      startX,
      startY,
      distX,
      distY,
      threshold = 150, //required min distance traveled to be considered swipe
      restraint = 100, // maximum distance allowed at the same time in perpendicular direction
      allowedTime = 300, // maximum time allowed to travel that distance
      elapsedTime,
      startTime,
      handleswipe = callback || function(swipedir){}

      touchsurface.addEventListener('touchstart', function(e){
          var touchobj = e.changedTouches[0]
          swipedir = 'none'
          dist = 0
          startX = touchobj.pageX
          startY = touchobj.pageY
          startTime = new Date().getTime() // record time when finger first makes contact with surface
          //e.preventDefault()
      }, false)

      touchsurface.addEventListener('touchmove', function(e){
          //e.preventDefault() // prevent scrolling when inside DIV
      }, false)

      touchsurface.addEventListener('touchend', function(e){
          var touchobj = e.changedTouches[0]
          distX = touchobj.pageX - startX // get horizontal dist traveled by finger while in contact with surface
          distY = touchobj.pageY - startY // get vertical dist traveled by finger while in contact with surface
          elapsedTime = new Date().getTime() - startTime // get time elapsed
          if (elapsedTime <= allowedTime){ // first condition for awipe met
              if (Math.abs(distX) >= threshold && Math.abs(distY) <= restraint){ // 2nd condition for horizontal swipe met
                  swipedir = (distX < 0)? 'left' : 'right' // if dist traveled is negative, it indicates left swipe
              }
              else if (Math.abs(distY) >= threshold && Math.abs(distX) <= restraint){ // 2nd condition for vertical swipe met
                  swipedir = (distY < 0)? 'up' : 'down' // if dist traveled is negative, it indicates up swipe
              }
          }
          handleswipe(swipedir)
          //e.preventDefault()
      }, false)
  }

  //USAGE:

  //var el = document.getElementById('wrapper');
  swipedetect(function(swipedir){
    if(swipedir == 'left'){
      $(".nav-tab.active").next().click();
    }
    if(swipedir == 'right'){
      $(".nav-tab.active").prev().click();
    }
  });

</script>
</body>
</html>
<?php
	}
} else {?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
