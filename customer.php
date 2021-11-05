<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
	//Connection
    $result = sqlsrv_query(
    	$NEI,
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
	$result = sqlsrv_query(
		$NEI,
		"	SELECT 	*,
					fFirst AS First_Name,
					Last as Last_Name
			FROM 	Emp
			WHERE 	ID= ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$result = sqlsrv_query($NEI,
		" 	SELECT 	Privilege.*
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
    	sqlsrv_query(
    		$NEI,
    		"	INSERT INTO Activity( [User], [Date], [Page] ) VALUES( ?, ?, ? );",
    		array(
    			$_SESSION[ 'User' ],
    			date("Y-m-d H:i:s"),
    			"customer.php"
    		)
    	);
        $result = sqlsrv_query(
        	$NEI,
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
    <?php require(bin_meta.'index.php');?>
	<title>Nouveau Texas | Portal</title>
    <?php require(bin_css.'index.php');?>
    <?php require(bin_js.'index.php');?>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);height:100%;">
    <div id='container' style='min-height:100%;height:100%;'>
    <div id="wrapper" class="<?php echo isset($_SESSION[ 'Toggle_Menu' ]) ? $_SESSION[ 'Toggle_Menu' ] : null;?>" style='height:100%;'>
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
        <div id="page-wrapper" class='content' style='background-color:transparent !important;
        <?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
		<h4 style='margin:0px;padding:10px;background-color:whitesmoke;border-bottom:1px solid darkgray;'><a href='customer.php?ID=<?php echo $_GET[ 'ID' ];?>'><?php $Icons->Customer();?> Customer: <?php echo $Customer[ 'Name' ];?></a></h4>
			<div class ='Screen-Tabs shadower' style="margin: 0;border-bottom:3px solid black !important;">
				<div class='row'>
					<?php if(isset($Privileges[ 'Customer' ])
                && $Privileges[ 'Customer'][ 'User_Privilege' ] >= 4){
					?><div tab='information' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-information.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Info( 3 );?></div>
							<div class ='nav-text'>Information</div>
				</div><?php }?>
					<?php /*if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
					?><div tab='cod' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-code.php?ID=<?php echo $_GET['ID'];?>');">
					<div class='nav-icon'><?php $Icons->Job(3);?></div>
					<div class ='nav-text'>Code</div>
					</div><?php }*/?>
					<?php if(isset($Privileges[ 'Invoice' ])
                && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
					?><div tab='collection' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-collections.php?ID=<?php echo $_GET[ 'ID' ];?>');">
					<div class='nav-icon'><?php $Icons->Collection( 3 );?></div>
					<div class ='nav-text'>Collections</div>
				</div><?php }?>
					<?php if(isset($Privileges[ 'Contract' ])
                && $Privileges[ 'Contract' ][ 'User_Privilege' ] >= 4){
					?><div tab='contract'class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-contracts.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Contract( 3 );?></div>
							<div class ='nav-text'>Contracts</div>
				</div><?php }?>
					<div tab='feed' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-feed.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons ->Activities( 3 );?></div>
							<div class ='nav-text'>Feed</div>
				</div>
					<?php if(isset($Privileges[ 'Time' ])
                && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
					?><div tab='hours' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'hours.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Payroll( 3 );?></div>
							<div class ='nav-text'>Hours</div>
				</div><?php }?>
					<?php if(isset($Privileges[ 'Job' ])
                && $Privileges[ 'Job' ][ 'User_Privilege' ] >= 4){
					?><div tab='job' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-jobs.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Job( 3 );?></div>
							<div class ='nav-text'>Jobs</div>
				</div><?php }?>
					<?php if(isset($Privileges[ 'Invoice' ])
                && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
					?><div tab='invoice' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-invoices.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Invoice( 3 );?></div>
							<div class ='nav-text'>Invoices</div>
				</div><?php }?>
					<?php
					$result = sqlsrv_query($NEI,
        "   SELECT Count(Loc.Loc) AS Counter
						FROM   Loc
						WHERE  Loc.Owner = ?
					;",array($_GET[ 'ID' ])
         );
					$count = sqlsrv_fetch_array($result)[ 'Counter' ];
					if($count == 1){
						$result = sqlsrv_query($NEI,
        "   SELECT Loc.Loc AS Location_ID
						FROM   Loc
						WHERE  Loc.Owner = ?
						;",array($_GET[ 'ID' ])
          );
						$Location_ID = sqlsrv_fetch_array($result)[ 'Location_ID' ];
						if(isset($Privileges[ 'Location' ])
            && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
						?><div tab='location' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='location.php?ID=<?php echo $Location_ID;?>';">
								<div class='nav-icon'><?php $Icons->Location( 3 );?></div>
								<div class ='nav-text'>Location</div>
				</div><?php }
					} elseif($count > 1) {
						if(isset($Privileges[ 'Location' ])
            && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
						?><div tab='location' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-locations.php?ID=<?php echo $_GET[ 'ID' ];?>');">
								<div class='nav-icon'><?php $Icons->Location( 3 );?></div>
								<div class ='nav-text'>Locations</div>
				</div><?php }
					}?>
					<?php /*if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
					?><div tab='log' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-log.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Job(3);?></div>
							<div class ='nav-text'>Log</div>
					</div><?php }*/?>
					<?php if(isset($Privileges[ 'Job' ])
                && $Privileges[ 'Job' ][ 'User_Privilege' ] >= 4){
					?><div tab='maintenance' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-maintenance.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Maintenance( 3 );?></div>
							<div class ='nav-text'>Maintenance</div>
				</div><?php }?>
					<?php /*if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
					?><div tab='modernization' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-modernization.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Modernization(3);?></div>
							<div class ='nav-text'>Modernization</div>
					</div><?php }*/?>

					<?php /*if(isset($Privileges['Proposal']) && $Privileges['Proposal']['User_Privilege'] >= 4){
					?><div tab='proposals' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-proposals.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Proposal(3);?></div>
							<div class ='nav-text'>Proposals</div>
					</div><?php }*/?>
					<?php if(isset($Privileges[ 'Finances' ])
                && $Privileges['Finances']['User_Privilege'] >= 4){
					?><div tab='pnl' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-pnl.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Financial( 3 );?></div>
							<div class ='nav-text'>P&L</div>
				</div><?php }?>
					<?php /*if(isset($Privileges['Repair']) && $Privileges['Repair']['User_Privilege'] >= 4){
					?><div tab='repair' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-repair.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Repair(3);?></div>
							<div class ='nav-text'>Repair</div>
					</div><?php }*/?>
					<?php /*if(isset($Privileges['Service']) && $Privileges['Service']['User_Privilege'] >= 4){
					?><div tab='service' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-service.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Phone(3);?></div>
							<div class ='nav-text'>Service</div>
					</div><?php }*/?>
					<?php if(isset($Privileges[ 'Ticket' ])
                && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 4){
					?><div tab='tickets'class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-tickets.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Ticket( 3 );?></div>
							<div class ='nav-text'>Tickets</div>
				</div><?php }?>
					<?php if(isset($Privileges[ 'Time' ])
                && $Privileges['Time']['Group_Privilege'] >= 4){
					?><div tab='timeline' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-timeline.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->History( 3 );?></div>
							<div class ='nav-text'>Timeline</div>
				</div><?php }?>
					<?php /*if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
					?><div tab='testing' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-testing.php?ID=<?php echo $_GET['ID'];?>');">
							<div class='nav-icon'><?php $Icons->Testing(3);?></div>
							<div class ='nav-text'>Testing</div>
					</div><?php }*/?>
					<?php if(isset($Privileges[ 'Unit' ])
                && $Privileges[ 'Unit' ][ 'User_Privilege' ] >= 4){
					?><div tab='unit' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-units.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Unit( 3 );?></div>
							<div class ='nav-text'>Units</div>
				</div><?php }?>
					<?php if(isset($Privileges[ 'Violation' ])
                && $Privileges[ 'Violation' ][ 'User_Privilege' ] >= 4){
					?><div tab='violation' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-violations.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Violation( 3 );?></div>
							<div class ='nav-text'>Violations</div>
				</div><?php }?>

					<?php if(isset($Privileges[ 'User' ])
                && $Privileges[ 'User' ][ 'User_Privilege' ] >= 4){
					?><div tab='workers' class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'customer-workers.php?ID=<?php echo $_GET[ 'ID' ];?>');">
							<div class='nav-icon'><?php $Icons->Users( 3 );?></div>
							<div class ='nav-text'>Workers</div>
					</div><?php }?>
				 </div>
			 </div>
			<div class='container-content'>
		</div>
  </div>
</div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
