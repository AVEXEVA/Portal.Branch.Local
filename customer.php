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
    	|| 	!is_numeric($_GET['ID'])
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
            "	SELECT 	Customer.*
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
            	WHERE   Customer.ID = ?;",
            array(
            	$_GET[ 'ID' ]
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
        	<div class='card card-full card-primary border-0'>
        		<div class='card-heading'><h4><a href='customer.php?ID=<?php echo $_GET[ 'ID' ];?>'><?php \singleton\fontawesome::getInstance( )->Customer( );?> Customer: <?php echo $Customer[ 'Name' ];?></a></h4></div>
        		<div class='card-body bg-darker'>
					<div class='Screen-Tabs text-white'>
						<div class='row'>
						<?php if(isset($Privileges[ 'Customer' ]) && $Privileges[ 'Customer'][ 'User_Privilege' ] >= 4){
							?><div tab='information' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="someFunction(this,'customer-information.php?ID=<?php echo $_GET[ 'ID' ];?>');">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Info( 3 );?></div>
									<div class ='nav-text'>Information</div>
							</div><?php 
						}?>
						<?php if(isset($Privileges[ 'Invoice' ]) && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
							?><div tab='collection' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection( 3 );?></div>
								<div class ='nav-text'>Collections</div>
							</div><?php 
						}?>
						<?php if(isset($Privileges[ 'Contract' ]) && $Privileges[ 'Contract' ][ 'User_Privilege' ] >= 4){
							?><div tab='contract'class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='contracts.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Contract( 3 );?></div>
									<div class ='nav-text'>Contracts</div>
							</div><?php 
						}?>
						<div tab='feed' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="someFunction(this,'customer-feed.php?ID=<?php echo $_GET[ 'ID' ];?>');">
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( ) ->Activities( 3 );?></div>
								<div class ='nav-text'>Feed</div>
						</div>
						<?php if(isset($Privileges[ 'Time' ]) && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
							?><div tab='hours' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="someFunction(this,'hours.php?ID=<?php echo $_GET[ 'ID' ];?>');">
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Payroll( 3 );?></div>
								<div class ='nav-text'>Hours</div>
							</div><?php 
						}?>
						<?php if(isset($Privileges[ 'Job' ]) && $Privileges[ 'Job' ][ 'User_Privilege' ] >= 4){
							?><div tab='job' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job( 3 );?></div>
									<div class ='nav-text'>Jobs</div>
						</div><?php }?>
						<?php if(isset($Privileges[ 'Invoice' ]) && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
							?><div tab='invoice' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='invoices.php?Customer=</php echo $Customer[ 'Name' ];?>';">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice( 3 );?></div>
									<div class ='nav-text'>Invoices</div>
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
								?><div tab='location' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='location.php?ID=<?php echo $Location_ID;?>';">
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location( 3 );?></div>
										<div class ='nav-text'>Location</div>
								</div><?php 
							}
						} elseif($count > 1) {
							if(isset($Privileges[ 'Location' ]) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
								?><div tab='location' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='locations.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location( 3 );?></div>
										<div class ='nav-text'>Locations</div>
								</div><?php 
							}
						}?>
						<?php if(isset($Privileges['Proposal']) && $Privileges['Proposal']['User_Privilege'] >= 4){
							?><div tab='proposals' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Proposal(3);?></div>
									<div class ='nav-text'>Proposals</div>
							</div><?php 
						}?>
						<?php if(isset($Privileges[ 'Finances' ]) && $Privileges['Finances']['User_Privilege'] >= 4){
							?><div tab='pnl' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="someFunction(this,'customer-pnl.php?ID=<?php echo $_GET[ 'ID' ];?>');">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Financial( 3 );?></div>
									<div class ='nav-text'>P&L</div>
							</div><?php 
						}?>
						<?php if(isset($Privileges[ 'Ticket' ]) && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 4){
							?><div tab='tickets'class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name'];?>';">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket( 3 );?></div>
									<div class ='nav-text'>Tickets</div>
							</div><?php 
						}?>
						<?php if(isset($Privileges[ 'Time' ]) && $Privileges['Time']['Group_Privilege'] >= 4){
							?><div tab='timeline' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="someFunction(this,'customer-timeline.php?ID=<?php echo $_GET[ 'ID' ];?>');">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->History( 3 );?></div>
									<div class ='nav-text'>Timeline</div>
							</div><?php 
						}?>
						<?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
							?><div tab='testing' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='inspections.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Testing(3);?></div>
									<div class ='nav-text'>Inspections</div>
							</div><?php 
						}?>
						<?php if(isset($Privileges[ 'Unit' ]) && $Privileges[ 'Unit' ][ 'User_Privilege' ] >= 4){
							?><div tab='unit' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit( 3 );?></div>
									<div class ='nav-text'>Units</div>
							</div><?php }?>
						<?php if(isset($Privileges[ 'Violation' ]) && $Privileges[ 'Violation' ][ 'User_Privilege' ] >= 4){
							?><div tab='violation' class='Home-Screen-Option col-lg-1 col-md-2 col-3' onClick="document.location.href='violations.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation( 3 );?></div>
									<div class ='nav-text'>Violations</div>
							</div><?php 
						}?>
					</div>
				</div>
			 </div>
		 	<div class='card-body bg-darker'>
				<div class='container-content border-0 text-white'>
				</div>
			</div>
		</div>
  	</div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
