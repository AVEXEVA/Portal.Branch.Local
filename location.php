<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION[ 'User' ],$_SESSION[ 'Hash' ] ) ) {
    $result = sqlsrv_query(
    	$NEI,
    	"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",
    	array(
    		$_SESSION[ 'User' ],
    		$_SESSION[ 'Hash' ]
    	)
    );
    $Connection = sqlsrv_fetch_array($result);
    $User = sqlsrv_query(
    	$NEI,
    	"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",
    	array(
    		$_SESSION[ 'User' ]
    	)
    );
    $User = sqlsrv_fetch_array($User);
    $result = sqlsrv_query(
    	$NEI,
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
    if(isset($Privileges['Location'])
        && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4
        && $Privileges[ 'Location' ][ 'Group_Privilege' ] >= 4
        && $Privileges[ 'Location' ][ 'Other_Privilege' ] >= 4){$Privileged = TRUE;}
    elseif($Privileges[ 'Location' ][ 'User_Privilege' ] >= 4
        && is_numeric($_GET[ 'ID' ])){
        $result = sqlsrv_query(
        	$NEI,
        	"	SELECT Tickets.*
				FROM
				(
					(
						SELECT 	TicketO.ID
						FROM 	TicketO
						WHERE 	TicketO.LID = ?
								AND TicketO.fWork = ?
					)
					UNION ALL
					(
						SELECT 	TicketD.ID
						FROM 	TicketD
						WHERE 	TicketD.Loc = ?
								AND TicketD.fWork = ?
					)
				) AS Tickets;",
			array(
				$_GET[ 'ID' ],
				$User[ 'fWork' ],
				$_GET[ 'ID' ],
				$User[ 'fWork' ],
				$_GET[ 'ID' ],
				$User[ 'fWork' ]
			)
		);
        $result = sqlsrv_fetch_array($result);
        $Privileged = is_array($result) ? TRUE : FALSE;
    }
    sqlsrv_query(
      $NEI,
      "   INSERT INTO Activity([User], [Date], [Page])
          VALUES(?,?,?);",
    array(
      $_SESSION[ 'User' ],
              date("Y-m-d H:i:s"),
                  "location.php")
      );
    if(!isset($Connection[ 'ID' ])  || !$Privileged){
      /*?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php */ }
    else {
        $result = sqlsrv_query($NEI,"SELECT TOP 1
                    Loc.Loc              AS Location_ID,
                    Loc.ID               AS Name,
                    Loc.Tag              AS Tag,
                    Loc.Address          AS Street,
                    Loc.City             AS City,
                    Loc.State            AS State,
                    Loc.Zip              AS Zip,
                    Loc.Balance          AS Location_Balance,
                    Loc.Custom8          AS Resident_Mechanic,
                    Zone.Name            AS Zone,
                    Loc.Route            AS Route_ID,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Loc.Owner            AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Balance AS Customer_Balance,
                    Terr.Name            AS Territory_Domain
            FROM    Loc
                    LEFT JOIN Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN Route        ON Loc.Route  = Route.ID
                    LEFT JOIN Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN Terr         		   ON Terr.ID    = Loc.Terr
            WHERE
                    Loc.Loc = ?
        ;",array($_GET[ 'ID' ]));
        $Location = sqlsrv_fetch_array($result);
        $data = $Location;
		$location = $Location;
    if(isMobile() || TRUE){?>
        <!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require(bin_meta.'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require(bin_css.'index.php');?>
    <?php require(bin_js.'index.php');?>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:#1d1d1d;">
    <div id="wrapper" style='height:100%;overflow-y:scroll;'>
        <?php require(bin_php.'element/navigation/index.php');?>
        <?php require(bin_php.'element/loading.php');?>
        <div id="page-wrapper" class='content' style='background-color:transparent !important;<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
			<h4 style='margin:0px;padding:10px;background-color:whitesmoke;border-bottom:1px solid darkgray;'><a href='location.php?ID=<?php echo $_GET['ID'];?>'><?php $Icons->Location();?> Location: <?php echo $Location['Name'];?></a></h4>
			<div class ='Screen-Tabs shadower' style="margin: 0;border-bottom:3px solid black !important;">
				<div class='row' style="margin: 0">
					<?php if(isset($Privileges['Location']) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
					?><div tab='information' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' style="margin: 0" onClick="someFunction(this,'information.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon' style="margin: 0"><?php $Icons->Information(3);?></div>
							<div class ='nav-text' style="margin: 0">Information</div>
					</div><?php }?>
					<?php if(isset($Privileges['Contract']) && $Privileges[ 'Contract' ][ 'User_Privilege' ] >= 4 || $Privileges[ 'Contract' ][ 'Group_Privilege' ] >= 4){
					?><div tab='contracts' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-contracts.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Contract(3);?></div>
							<div class ='nav-text'>Contracts</div>
					</div><?php }?>
					<?php if(isset($Privileges['Collection']) && $Privileges[ 'Collection' ][ 'User_Privilege' ] >= 4){
					?><div tab='collections' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-collections.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Collection(3);?></div>
							<div class ='nav-text'>Collections</div>
					</div><?php }?>
					<?php if(isset($Privileges['Contact']) && $Privileges[ 'Contact' ][ 'User_Privilege' ] >= 4){
					?><div tab='contacts' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-contacts.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Info(3);?></div>
							<div class ='nav-text'>Contacts</div>
					</div><?php }?>
					<?php if(isset($Privileges['Customer']) && $Privileges[ 'Customer' ][ 'User_Privilege' ] >= 4){
					?><div tab='customer' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='customer.php?ID=<?php echo $Location[ 'Customer_ID' ];?>'">
							<div class='nav-icon'><?php $Icons->Customer(3);?></div>
							<div class ='nav-text'>Customer</div>
					</div><?php }?>
					<div tab='feed' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'feed.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Activities(3);?></div>
							<div class ='nav-text'>Feed</div>
					</div>
					<?php if(isset($Privileges['Invoice']) && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
					?><div tab='invoices' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-invoices.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Invoice(3);?></div>
							<div class ='nav-text'>Invoices</div>
					</div><?php }?>
					<?php if(isset($Privileges['Time']) && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
					?><div tab='hours' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-hours.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Hours(3);?></div>
							<div class ='nav-text'>Hours</div>
					</div><?php }?>
					<?php if(isset($Privileges['Job']) && $Privileges[ 'Job' ][ 'User_Privilege' ] >= 4){
					?><div tab='jobs' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-jobs.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Job(3);?></div>
							<div class ='nav-text'>Jobs</div>
					</div><?php }?>
					<?php if(isset($Privileges['Legal']) && $Privileges[ 'Legal' ][ 'User_Privilege' ] >= 4 && false){
					?><div tab='legal' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-legal.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Legal(3);?></div>
							<div class ='nav-text'>Legal</div>
					</div><?php }?>
					<?php if(isset($Privileges['Log']) && $Privileges[ 'Log' ][ 'User_Privilege' ] >= 4 ){
					?><div tab='logs' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-log.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Job(3);?></div>
							<div class ='nav-text'>Log</div>
					</div><?php }?>
					<?php if(isset($Privileges['Job']) && $Privileges[ 'Job' ][ 'User_Privilege' ] >= 4){
					?><div tab='maintenance' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-maintenance.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Maintenance(3);?></div>
							<div class ='nav-text'>Maintenance</div>
					</div><?php }?>
					<?php /*<div tab='map' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-map.php?ID=<?php echo $Location['Location_ID'];?>');">
							<div class='nav-icon'><?php $Icons->Map(3);?></div>
							<div class ='nav-text'>Map</div>
					</div>*/?>
					<?php /*if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
					?><div tab='modernization' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-modernization.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Modernization(3);?></div>
							<div class ='nav-text'>Modernization</div>
					</div><?php }*/?>
					<?php if(isset($Privileges['Finances']) && $Privileges[ 'Finances' ][ 'User_Privilege' ] >= 4){
					?><div tab='PNL' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-pnl.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Customer(3);?></div>
							<div class ='nav-text'>P&L</div>
					</div><?php }?>
					<?php if(isset($Privileges['Proposal']) && $Privileges[ 'Proposal' ][ 'User_Privilege' ] >= 4){
					?><div tab='proposals' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-proposals.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Proposal(3);?></div>
							<div class ='nav-text'>Proposals</div>
					</div><?php }?>
					<?php
				$result = sqlsrv_query(
          $NEI,
          " SELECT Count(Route.ID) AS Counter
						FROM	 Route
						LEFT JOIN Emp ON Route.Mech = Emp.fWork
						WHERE	Emp.ID = ?
						      AND Route.ID = ?;",
          array(
            $_SESSION['User'],
            $Location['Route_ID']
          )
      );
					$count = sqlsrv_fetch_array($result)[ 'Counter' ];
					if(isset($Privileges[ 'Route' ]) &&
						(($Privileges[ 'Route' ][ 'User_Privilege' ] >= 4
							&& $count > 0 )
						 || $Privileges[ 'Route' ][ 'Other_Privilege' ] >= 4)){
					?><div tab='route' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='route.php?ID=<?php echo $Location[ 'Route_ID' ];?>';">
							<div class='nav-icon'><?php $Icons->Route(3);?></div>
							<div class ='nav-text'>Route</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Service' ]) && $Privileges[ 'Service' ][ 'User_Privilege' ] >= 4){
					?><div tab='service' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-service.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Phone(3);?></div>
							<div class ='nav-text'>Service</div>
					</div><?php }?>
					<?php /*if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
					?><div tab='testing' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-testing.php?ID=<?php echo $Location['Location_ID'];?>');">
							<div class='nav-icon'><?php $Icons->Testing(3);?></div>
							<div class ='nav-text'>Testing</div>
					</div><?php }*/?>
					<?php if(isset($Privileges[ 'Ticket' ]) && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 4){
					?><div tab='tickets' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'tickets.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Ticket(3);?></div>
							<div class ='nav-text'>Tickets</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Time' ]) && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
					?><div tab='timeline' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'timeline.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->History(3);?></div>
							<div class ='nav-text'>Timeline</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Unit' ]) && $Privileges[ 'Unit' ][ 'User_Privilege' ] >= 4){
					?><div tab='units' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'units.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Unit(3);?></div>
							<div class ='nav-text'>Units</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Violation' ]) && $Privileges[ 'Violation' ][ 'User_Privilege' ] >= 4){
					?><div tab='violations' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-violations.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Violation(3);?></div>
							<div class ='nav-text'>Violations</div>
					</div><?php }?>
					<?php if(isset($Privileges[ 'Location' ]) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
					?><div tab='workers' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'location-workers.php?ID=<?php echo $Location[ 'Location_ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Users(3);?></div>
							<div class ='nav-text'>Workers</div>
					</div><?php }?>
				</div>
			</div>
		<div class='container-content'></div>
	</div>
</div>
</body>
</html><?php }
}} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
