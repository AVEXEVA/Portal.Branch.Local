<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
	//Connection
    $result = \singleton\database::getInstance( )->query(
    	null,
    	"	SELECT 	*
    		FROM 	Connection
    		WHERE 		Connector = ?
    				AND Hash = ?;",
    	array(
    		$_SESSION['User'],
    		$_SESSION['Hash']
    	)
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = \singleton\database::getInstance( )->query(
		null,
		"	SELECT 	Emp.fFirst 	AS First_Name,
					Emp.Last 	AS Last_Name
			FROM 	Emp
			WHERE 	Emp.ID = ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$result = \singleton\database::getInstance( )->query(null,
		" 	SELECT 	Privilege.Access_Table,
					Privilege.User_Privilege,
					Privilege.Group_Privilege,
					Privilege.Other_Privilege
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$Privileges = array();
	$Privileged = false;
	while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
	if(		isset($Privileges['Customer'])
		&& 	$Privileges[ 'Customer' ][ 'User_Privilege' ]  >= 4
		&& 	$Privileges[ 'Customer' ][ 'Group_Privilege' ] >= 4
		&& 	$Privileges[ 'Customer' ][ 'Other_Privilege' ] >= 4){
				$Privileged = true;}
    if(		!isset($Connection['ID'])
    	|| !$Privileged
    ){ ?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET[ 'ID' ])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
    	\singleton\database::getInstance( )->query(
    		null,
    		"	INSERT INTO Activity( [User], [Date], [Page] ) VALUES( ?, ?, ? );",
    		array(
    			$_SESSION[ 'User' ],
    			date("Y-m-d H:i:s"),
    			"customer.php"
    		)
    	);
        $result = \singleton\database::getInstance( )->query(
        	null,
            "	SELECT 	Top 1 
            			Customer.*
            	FROM    (
            				SELECT 	Owner.ID    AS ID,
		                    		Rol.Name    AS Name,
		                    		Rol.Address AS Street,
				                    Rol.City    AS City,
				                    Rol.State   AS State,
				                    Rol.Zip     AS Zip,
				                    Owner.Status  AS Status,
									Rol.Website AS Website
							FROM    Owner
									LEFT JOIN Rol ON Owner.Rol = Rol.ID
            		) AS Customer
            	WHERE   	Customer.ID = ?
            			OR 	Customer.Name = ?;",
            array(
            	isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null,
            	isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null
            )
        );
        $Customer = sqlsrv_fetch_array($result);
?><!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
	<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
	<?php $_GET[ 'Bootstrap' ] = '5.1';?>
	<?php require( bin_meta . 'index.php' );?>
    <?php require( bin_css  . 'index.php' );?>
    <?php require( bin_js   . 'index.php' );?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php'); ?>
        <?php require( bin_php . 'element/loading.php'); ?>
        
        <div id="page-wrapper" class='content'>
        	<div class='card-deck row g-0 text-white'>
	        	<div class='card col-12 card-primary border border-dark border-5'>
	        		<div class='card-heading'><h4><a href='customer.php?ID=<?php echo $_GET[ 'ID' ];?>'><?php \singleton\fontawesome::getInstance( )->Customer( );?> Customer: <?php echo $Customer[ 'Name' ];?></a></h4></div>
	        		<div class='card-body bg-dark'>
						<div class='Screen-Tabs'>
							<div class='row'>
							<?php if(isset($Privileges[ 'Invoice' ]) && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
								?><div tab='collection' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection( 3 );?></div>
										<div class ='nav-text'>Collections</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges[ 'Contract' ]) && $Privileges[ 'Contract' ][ 'User_Privilege' ] >= 4){
								?><div tab='contract'class='link-page text-white col-xl-1 col-4' onClick="document.location.href='contracts.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Contract( 3 );?></div>
										<div class ='nav-text'>Contracts</div>
									</div>
								</div><?php 
							}?>
							<div tab='feed' class='link-page text-white col-xl-1 col-4' onClick="someFunction(this,'customer-feed.php?ID=<?php echo $_GET[ 'ID' ];?>');">
								<div class='p-1 border border-dark border-5'>
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( ) ->Activities( 3 );?></div>
									<div class ='nav-text'>Feed</div>
								</div>
							</div>
							<?php if(isset($Privileges[ 'Time' ]) && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
								?><div tab='hours' class='link-page text-white col-xl-1 col-4' onClick="someFunction(this,'hours.php?ID=<?php echo $_GET[ 'ID' ];?>');">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Payroll( 3 );?></div>
										<div class ='nav-text'>Hours</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges[ 'Job' ]) && $Privileges[ 'Job' ][ 'User_Privilege' ] >= 4){
								?><div tab='job' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job( 3 );?></div>
										<div class ='nav-text'>Jobs</div>
									</div>
							</div><?php }?>
							<?php if(isset($Privileges[ 'Invoice' ]) && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
								?><div tab='invoice' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='invoices.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice( 3 );?></div>
										<div class ='nav-text'>Invoices</div>
									</div>
								</div><?php 
							}?>
							<?php
							$result = \singleton\database::getInstance( )->query(
								null,
								"   SELECT Count(Loc.Loc) AS Counter
									FROM   Loc
									WHERE  Loc.Owner = ?;",
								array( $_GET[ 'ID' ] )
							);
							$count = sqlsrv_fetch_array($result)[ 'Counter' ];
							if($count == 1){
								$result = \singleton\database::getInstance( )->query(null,
									"   SELECT Loc.Loc AS Location_ID
										FROM   Loc
										WHERE  Loc.Owner = ?;",
									array(
										$_GET[ 'ID' ]
									)
								);
								$Location_ID = sqlsrv_fetch_array($result)[ 'Location_ID' ];
								if(isset($Privileges[ 'Location' ]) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
									?><div tab='location' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='location.php?ID=<?php echo $Location_ID;?>';">
										<div class='p-1 border border-dark border-5'>
											<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location( 3 );?></div>
											<div class ='nav-text'>Location</div>
										</div>
									</div><?php 
								}
							} elseif($count > 1) {
								if(isset($Privileges[ 'Location' ]) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
									?><div tab='location' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='locations.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
										<div class='p-1 border border-dark border-5'>
											<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location( 3 );?></div>
											<div class ='nav-text'>Locations</div>
										</div>
									</div><?php 
								}
							}?>
							<?php if(isset($Privileges['Proposal']) && $Privileges['Proposal']['User_Privilege'] >= 4){
								?><div tab='proposals' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Proposal(3);?></div>
										<div class ='nav-text'>Proposals</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges[ 'Finances' ]) && $Privileges['Finances']['User_Privilege'] >= 4){
								?><div tab='pnl' class='link-page text-white col-xl-1 col-4' onClick="someFunction(this,'customer-pnl.php?ID=<?php echo $_GET[ 'ID' ];?>');">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Financial( 3 );?></div>
										<div class ='nav-text'>P&L</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges[ 'Ticket' ]) && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 4){
								?><div tab='tickets'class='link-page text-white col-xl-1 col-4' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name'];?>';">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket( 3 );?></div>
										<div class ='nav-text'>Tickets</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges[ 'Time' ]) && $Privileges['Time']['Group_Privilege'] >= 4){
								?><div tab='timeline' class='link-page text-white col-xl-1 col-4' onClick="someFunction(this,'customer-timeline.php?ID=<?php echo $_GET[ 'ID' ];?>');">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->History( 3 );?></div>
										<div class ='nav-text'>Timeline</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
								?><div tab='testing' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='inspections.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Testing(3);?></div>
										<div class ='nav-text'>Inspections</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges[ 'Unit' ]) && $Privileges[ 'Unit' ][ 'User_Privilege' ] >= 4){
								?><div tab='unit' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit( 3 );?></div>
										<div class ='nav-text'>Units</div>
									</div>
								</div><?php }?>
							<?php if(isset($Privileges[ 'Violation' ]) && $Privileges[ 'Violation' ][ 'User_Privilege' ] >= 4){
								?><div tab='violation' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='violations.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border border-dark border-5'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation( 3 );?></div>
										<div class ='nav-text'>Violations</div>
									</div>
								</div><?php 
							}?>
						</div>
					</div>
				 </div>
			</div>
			<div class='card card-primary border border-dark border-5 col-4'>
				<div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Info( 1 );?>Information</div>
			 	<div class='card-body bg-dark'>
					<div class='row'>
						<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Name:</div>
						<div class='col-8'><?php echo $Customer['Name'];?></div>
					</div>
					<div class='row'>
						<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
						<div class='col-8'><?php echo isset($Customer['Status']) && $Customer['Status'] == 0? "Active" : "Inactive";?></div>
					</div>
					<div class='row'>
						<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Web(1);?> Website:</div>
						<div class='col-8'><?php echo strlen($Customer['Website']) > 0 ?  $Customer['Website'] : "&nbsp;";?></div>
					</div>
				</div>
			</div>
			<div class='card card-primary border border-dark border-5 col-4'>
				<div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Address(1);?>Address</div>
				<div class='card-body bg-dark'>
					<div class='row'>
						<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Street:</div>
						<div class='col-8'><?php echo $Customer['Street'];?></div>
					</div>
					<div class='row'>
						<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
						<div class='col-8'><?php echo $Customer['City'];?></div>
					</div>
					<div class='row'>
						<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State:</div>
						<div class='col-8'><?php echo $Customer['State'];?></div>
					</div>
					<div class='row'>
						<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
						<div class='col-8'><?php echo $Customer['Zip'];?></div>
					</div>
				</div>
			</div>
			<div class='card card-primary border border-dark border-5 col-4'>
				<div class='card-heading'>Operations</div>
				<div class='card-body bg-dark'>
					<div class='row'>
					    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Units</div>
					    <div class='col-8'><?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT Count(Elev.ID) AS Count_of_Elevators
								FROM   Elev
									   LEFT JOIN Loc ON Elev.Loc = Loc.Loc
								WHERE  Loc.Owner = ?
							;",array($_GET['ID']));
							echo $r ? sqlsrv_fetch_array($r)['Count_of_Elevators'] : 0;
						?></div>
					</div>
					<div class='row'>
					    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Jobs</div>
					    <div class='col-8'><?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT Count(Job.ID) AS Count_of_Jobs
								FROM   Job
									   LEFT JOIN Loc ON Job.Loc = Loc.Loc
								WHERE  Loc.Owner = ? AND Job.Status = 1
							;",array($_GET['ID']));
						echo $r ? sqlsrv_fetch_array($r)['Count_of_Jobs'] : 0;
						?></div>
					</div>
					<div class='row'>
					    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Violation(1);?> Violations</div>
					    <div class='col-8'><?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT Count(Violation.ID) AS Count_of_Violations
								FROM   Violation
									   LEFT JOIN Loc ON Violation.Loc = Loc.Loc
								WHERE  Loc.Owner = ?
							;",array($_GET['ID']));
							echo $r ? sqlsrv_fetch_array($r)['Count_of_Violations'] : 0;?>
						</div>
					</div>
					<div class='row'>
					    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Tickets</div>
					    <div class='col-8'><?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT Count(Tickets.ID) AS Count_of_Tickets
								FROM   (
											(
												SELECT TicketO.ID AS ID
												FROM   TicketO
													   LEFT JOIN Loc ON TicketO.LID = Loc.Loc
												WHERE  Loc.Owner = ?
											)
											UNION ALL
											(
												SELECT TicketD.ID AS ID
												FROM   TicketD
													   LEFT JOIN Loc ON TicketD.Loc = Loc.Loc
												WHERE  Loc.Owner = ?
											)
										) AS Tickets
							;",array($_GET['ID'],$_GET['ID']));
							echo $r ? sqlsrv_fetch_array($r)['Count_of_Tickets'] : 0;
						?></div>
					</div>
				</div>
			</div>
			<div class='card card-primary border border-dark border-5 col-4'>
				<div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Sales( 1 );?> Sales</div>
				<div class='card-body bg-dark'>
					<div class='row'>
					    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Proposals</div>
					    <div class='col-8'><?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT Count(Estimate.ID) AS Count_of_Estimates
								FROM   Estimate
									   LEFT JOIN Loc ON Estimate.LocID = Loc.Loc
								WHERE  Loc.Owner = ?
							;",array($_GET['ID']));
							echo $r ? sqlsrv_fetch_array($r)['Count_of_Estimates'] : 0;
						?></div>
					</div>
					<div class='row'>
					    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Invoices</div>
					    <div class='col-8'>
							<?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT Count(Invoice.Ref) AS Count_of_Invoices
								FROM   Invoice
									   LEFT JOIN Loc ON Invoice.Loc = Loc.Loc
								WHERE  Loc.Owner = ? AND Invoice.Status = 1;
							;",array($_GET['ID']));
							echo $r ? sqlsrv_fetch_array($r)['Count_of_Invoices'] : 0;?>
						</div>
					</div>
				</div>
			</div>
			<div class='card card-primary border border-dark border-5 col-4'>
				<div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Collections</div>
				<div class='card-body bg-dark'>
					<?php if(isset($Privileges['Collection']) && $Privileges['Collection']['User_Privilege'] >= 4) {?>
					<div class='row' style='padding-top:10px;padding-bottom:10px;'>
					    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Balance</div>
					    <div class='col-8'><?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT Sum(OpenAR.Balance) AS Balance
								FROM   OpenAR
									   LEFT JOIN Loc ON OpenAR.Loc = Loc.Loc
								WHERE  Loc.Owner = ?
							;",array($_GET['ID']));
							$Balance = $r ? sqlsrv_fetch_array($r)['Balance'] : 0;
							echo money_format('%(n',$Balance);
						?></div>
					</div>
					<?php }?>
					<?php if(isset($Privileges['Collection']) && $Privileges['Collection']['User_Privilege'] >= 4) {?>
					<div class='row' style='padding-top:10px;padding-bottom:10px;'>
					    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Collections</div>
					    <div class='col-8'><?php
							$r = \singleton\database::getInstance( )->query(null,"
								SELECT Count( OpenAR.Ref ) AS Count
								FROM   OpenAR
									   LEFT JOIN Loc ON OpenAR.Loc = Loc.Loc
								WHERE  Loc.Owner = ?
							;",array($_GET['ID']));
							$Count = $r ? sqlsrv_fetch_array($r)['Count'] : 0;
							echo $Count
						?></div>
					</div>
					<?php }?>
				</div>
			</div>
		</div>
  	</div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
