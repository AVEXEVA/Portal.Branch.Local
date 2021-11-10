<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION[ 'User' ],$_SESSION[ 'Hash' ] ) ) {
    $result = $database->query(
    	null,
    	"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",
    	array(
    		$_SESSION[ 'User' ],
    		$_SESSION[ 'Hash' ]
    	)
    );
    $Connection = sqlsrv_fetch_array($result);
    $User = $database->query(
    	null,
    	"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",
    	array(
    		$_SESSION[ 'User' ]
    	)
    );
    $User = sqlsrv_fetch_array($User);
    $result = $database->query(
    	null,
    	"	SELECT 	  Access_Table,
        			    User_Privilege,
        			    Group_Privilege,
        			    Other_Privilege
        	FROM   	Privilege
        	WHERE  	User_ID = ?;",
        array(
        	$_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($result)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($Privileges['Executive'])
        && $Privileges[ 'Executive' ][ 'User_Privilege' ] >= 4
        && $Privileges[ 'Executive' ][ 'Group_Privilege' ] >= 4
        && $Privileges[ 'Executive' ][ 'Other_Privilege' ] >= 4){$Privileged = TRUE;}
    if(!isset($Connection[ 'ID' ])  || !$Privileged){
      /*?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php */ }
    else {
      \singleton\database::getInstance( )->query(null,
        " INSERT INTO Activity([User], [Date], [Page])
  			  VALUES(?,?,?)
  		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "territory.php?ID=" . $_GET['ID']));
        $r = \singleton\database::getInstance( )->query(null,
            "	SELECT 	TOP 1
                    	Terr.ID   AS Territory_ID,
						Terr.Name AS Territory_Name
			 	FROM   	Terr
			    WHERE  	Terr.ID = ?;"
        ,array($_GET['ID']));
        $Territory = sqlsrv_fetch_array($r);
?><!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php $_GET[ 'Bootstrap' ] = '5.1';?>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>

<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
        	<div class='card-deck'>
				<div class='card card-full card-primary'>
					<div class='card-heading'><h4>Territory: <?php echo $Territroy[ 'Name' ];?></h4></div>
					<div class='card-body bg-dark'>
						<div class='row' style="margin: 0">
							<?php if(isset($Privileges['Location']) && $Privileges['Location']['User_Privilege'] >= 4){
							?><div tab='information' class='Home-Screen-Option col-lg-1 col-md-2 col-' style="margin: 0" onClick="document.location.href='information.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon' style="margin: 0"><?php \singleton\fontawesome::getInstance( )->Information(3);?></div>
									<div class ='nav-text' style="margin: 0">Information</div>
							</div><?php }?>
							<?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4 || $Privileges['Job']['Group_Privilege'] >= 4 && false){
							?><div tab='code' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='violations.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation(3);?></div>
									<div class ='nav-text'>Violations</div>
							</div><?php }?>
							<?php if(isset($Privileges['Collection']) && $Privileges['Collection']['User_Privilege'] >= 4 ){
							?><div tab='collections' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='collections.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection(3);?></div>
									<div class ='nav-text'>Collections</div>
							</div><?php }?>
							<?php if(isset($Privileges['Contact']) && $Privileges['Contact']['User_Privilege'] >= 4 && false ) {
							?><div tab='contacts' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='contacts.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Info(3);?></div>
									<div class ='nav-text'>Contacts</div>
							</div><?php }?>
							<?php if(isset($Privileges['Customer']) && $Privileges['Customer']['User_Privilege'] >= 4){
							?><div tab='customer' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='customers.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
									<div class ='nav-text'>Customers</div>
							</div><?php }?>
							<?php if(false) {?>
							<div tab='feed' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="someFunction(this,'location-feed.php?ID=<?php echo $Territory['Territory_ID'];?>');">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Activities(3);?></div>
									<div class ='nav-text'>Feed</div>
							</div><?php } ?>
							<?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >= 4 && false ){
							?><div tab='invoices' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='invoices.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice(3);?></div>
									<div class ='nav-text'>Invoices</div>
							</div><?php }?>
							<?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4 && false ){
							?><div tab='jobs' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='jobs.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
									<div class ='nav-text'>Jobs</div>
							</div><?php }?>
							<?php if(isset($Privileges['Location']) && $Privileges['Location']['User_Privilege'] >= 4){
							?><div tab='locations' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='locations.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location(3);?></div>
									<div class ='nav-text'>Locations</div>
							</div><?php }?>
							<?php if(isset($Privileges['Legal']) && $Privileges['Legal']['User_Privilege'] >= 4 && false){
							?><div tab='legal' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='legal.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Legal(3);?></div>
									<div class ='nav-text'>Legal</div>
							</div><?php }?>
							<?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4 && false){
							?><div tab='maintenance' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="someFunction(this,'territory-maintenance.php?ID=<?php echo $_GET['ID'];?>');">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Maintenance(3);?></div>
									<div class ='nav-text'>Maintenance</div>
							</div><?php }?>
							<?php if(false) {?>
							<div tab='map' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='map.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Map(3);?></div>
									<div class ='nav-text'>Map</div>
							</div><?php } ?>
							<?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4 && false ){
							?><div tab='modernization' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="someFunction(this,'territory-modernization.php?ID=<?php echo $_GET['ID'];?>');">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Modernization(3);?></div>
									<div class ='nav-text'>Modernization</div>
							</div><?php }?>
							<?php if(isset($Privileges['Finances']) && $Privileges['Finances']['User_Privilege'] >= 4 && false){
							?><div tab='PNL' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='pnl.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
									<div class ='nav-text'>P&L</div>
							</div><?php }?>
							<?php if(isset($Privileges['Proposal']) && $Privileges['Proposal']['User_Privilege'] >= 4 && false ){
							?><div tab='proposals' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='proposals.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Proposal(3);?></div>
									<div class ='nav-text'>Proposals</div>
							</div><?php }?>
							<?php if(isset($Privileges['Service']) && $Privileges['Service']['User_Privilege'] >= 4 && false){
							?><div tab='service' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='service.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Phone(3);?></div>
									<div class ='nav-text'>Service</div>
							</div><?php }?>
							<?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4 && false){
							?><div tab='testing' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='testing.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Testing(3);?></div>
									<div class ='nav-text'>Testing</div>
							</div><?php }?>
							<?php if(isset($Privileges['Time']) && $Privileges['Time']['Group_Privilege'] >= 4 && false){
							?><div tab='timeline' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='timeline.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->History(3);?></div>
									<div class ='nav-text'>Timeline</div>
							</div><?php }?>
							<?php if(isset($Privileges['Unit']) && $Privileges['Unit']['User_Privilege'] >= 4 && false){
							?><div tab='units' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='units.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
									<div class ='nav-text'>Units</div>
							</div><?php }?>
							<?php if(isset($Privileges['Violation']) && $Privileges['Violation']['User_Privilege'] >= 4 && false){
							?><div tab='violations' class='Home-Screen-Option col-lg-1 col-md-2 col-' onClick="document.location.href='violations.php?Territory=<?php echo $Territory[ 'Territory_Name' ];?>'">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation(3);?></div>
									<div class ='nav-text'>Violations</div>
							</div><?php }?>
						</div>
					</div>
				</div>
				<div class='card card-primary '>
					<div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Info( 1 );?> Information</div>
					<div class='card-body'>
				        <div class='row g-0'>
							<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Territory(1);?> Name:</div>
							<div class='col-left'><?php echo $Territory['Territory_Name'];?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Units</div>
				            <div class='col-8'><?php
				                $r = $database->query(null,"
									SELECT Count(Elev.ID) AS Count_of_Elevators
									FROM   Elev
										   LEFT JOIN Loc ON Elev.Loc = Loc.Loc
									WHERE  Loc.Terr = ?
										   AND Elev.Status = 0
										   AND Loc.Maint = 1
								;",array($_GET['ID']));
				                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Elevators']) : 0;
				            ?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Jobs</div>
				            <div class='col-8'><?php
				                $r = $database->query(null,"
									SELECT Count(Job.ID) AS Count_of_Jobs
									FROM   Job
										   LEFT JOIN Loc ON Job.Loc = Loc.Loc
									WHERE Loc.Terr = ?
										  AND Job.Status = 0
								;",array($_GET['ID']));
				                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;
				            ?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Violation(1);?> Violations</div>
				            <div class='col-8'><?php
				                $r = $database->query(null,"
									SELECT Count(Violation.ID) AS Count_of_Jobs
									FROM   Violation
										   LEFT JOIN Loc ON Violation.Loc = Loc.Loc
									WHERE Loc.Terr = ?
								;",array($_GET['ID']));
				                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;
				            ?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Tickets</div>
				            <div class='col-8'><?php
				                $r = $database->query(null,"
				                    SELECT Count(Tickets.ID) AS Count_of_Tickets
				                    FROM   (
				                                (
													SELECT TicketO.ID
													FROM   TicketO
														   LEFT JOIN Loc ON TicketO.LID = Loc.Loc
													WHERE  Loc.Terr = ?
												)
				                                UNION ALL
				                                (
													SELECT TicketD.ID
													FROM   TicketD
														   LEFT JOIN Loc ON TicketD.Loc = Loc.Loc
													WHERE  Loc.Terr = ?
												)
				                                UNION ALL
				                                (
													SELECT TicketDArchive.ID
													FROM   TicketDArchive
														   LEFT JOIN Loc ON TicketDArchive.Loc = Loc.Loc
													WHERE  Loc.Terr = ?)
				                            ) AS Tickets
				                ;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
				                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;
				            ?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Proposals</div>
				            <div class='col-8'><?php
				                $r = $database->query(null,"
				                    SELECT Count(Estimate.ID) AS Count_of_Tickets
				                    FROM   Estimate
										   LEFT JOIN Loc ON Estimate.LocID = Loc.Loc
				                    WHERE  Loc.Terr = ?
				                ;",array($_GET['ID']));
				                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;
				            ?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Invoices</div>
				            <div class='col-8'><?php
				                $r = $database->query(null,"
				                    SELECT Count(Invoice.Ref) AS Count_of_Invoices
				                    FROM   Invoice
										   LEFT JOIN Loc ON Invoice.Loc = Loc.Loc
				                    WHERE  Loc.Terr = ?;
				                ;",array($_GET['ID']));
				                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Invoices']) : 0;
				            ?></div>
				        </div>
				    </div>
				</div>
				<div class="card card-full card-primary" id='location-information'>
					<div class='card-body card-col-4-max-350 white-background'>
						
					</div>
				</div>
			</div>
		</div>
	</div>
    <script src="https://www.nouveauelevator.com/vendor/flot/excanvas.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.pie.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.resize.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.time.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot/jquery.flot.categories.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/flot-tooltip/jquery.flot.tooltip.min.js"></script>

	<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyAycrIPh5udy_JLCQHLNlPup915Ro4gPuY"></script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>