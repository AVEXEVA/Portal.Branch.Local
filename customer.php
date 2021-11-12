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
            						Rol.ID 		AS Rolodex,
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
            	isset( $_GET[ 'ID' ] ) 
            		? $_GET[ 'ID' ] 
            		: (
            			isset( $_POST[ 'ID' ] ) 
            				? $_POST[ 'ID' ]
            				: null
            		),
            	isset( $_GET[ 'Name' ] ) 
            		? $_GET[ 'Name' ] 
            		: (
            			isset( $_POST[ 'Name' ] ) 
            				? $_POST[ 'Name' ]
            				: null
            		)
            )
        );
        $Customer = sqlsrv_fetch_array($result);

        if( isset( $_POST ) ){
        	$Customer[ 'Name' ] = isset( $_POST[ 'Name' ] ) ? $_POST[ 'Name' ] : $Customer[ 'Name' ];
        	$Customer[ 'Status' ] = isset( $_POST[ 'Status' ] ) ? $_POST[ 'Status' ] : $Customer[ 'Status' ];
        	$Customer[ 'Website' ] = isset( $_POST[ 'Website' ] ) ? $_POST[ 'Website' ] : $Customer[ 'Website' ];
        	$Customer[ 'Street' ] = isset( $_POST[ 'Street' ] ) ? $_POST[ 'Street' ] : $Customer[ 'Street' ];
        	$Customer[ 'City' ] = isset( $_POST[ 'City' ] ) ? $_POST[ 'City' ] : $Customer[ 'City' ];
        	$Customer[ 'State' ] = isset( $_POST[ 'State' ] ) ? $_POST[ 'State' ] : $Customer[ 'State' ];
        	$Customer[ 'Zip' ] = isset( $_POST[ 'Zip' ] ) ? $_POST[ 'Zip' ] : $Customer[ 'Zip' ];

        	\singleton\database::getInstance( )->query(
        		null,
        		"	UPDATE 	Owner 
        			SET 	Owner.Status = ?
        			WHERE 	Owner.ID = ?;",
        		array(
        			$Customer[ 'Status' ],
        			$Customer[ 'ID' ]
        		)
        	);
        	\singleton\database::getInstance( )->query(
        		null,
        		"	UPDATE 	Rol
        			SET 	Rol.Name = ?,
        					Rol.Website = ?,
        					Rol.Address = ?,
        					Rol.City = ?,
        					Rol.State = ?,
        					Rol.Zip = ?
        			WHERE 	Rol.ID = ?;",
        		array(
        			$Customer[ 'Name' ],
        			$Customer[ 'Website' ],
        			$Customer[ 'Street' ],
        			$Customer[ 'City' ],
        			$Customer[ 'State' ],
        			$Customer[ 'Zip' ],
        			$Customer[ 'Rolodex' ]
        		)
        	);
        }

?><!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
	<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
	<?php $_GET[ 'Bootstrap' ] = '5.1';?>
	<?php require( bin_meta . 'index.php' );?>
	<style>
		.link-page {
  color: #FFF;
  transition: all 0.5s;
  position: relative;
}
.link-page::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
  background-color: rgba(0,0,0,0.1);
  transition: all 0.3s;
}
.link-page:hover::before {
  opacity: 0 ;
  transform: scale(0.5,0.5);
}
.link-page::after {
  content: '';
  position: absolute;
  top: -10%;
  left: 0;
  width: 100%;
  height: 120%;
  z-index: 1;
  opacity: 0;
  transition: all 0.3s;
  border: 1px solid rgba(255,255,255,0.5);
  transform: scale(1.2,1.2);
}
.link-page:hover::after {
  opacity: 1;
  transform: scale(1,1);
}
.nav-text{ text-align: center; }
.nav-icon{ text-align: center; }

.row.g-0::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  z-index: 1;
  background-color: rgba(0,0,0,0.1);
  transition: all 0.3s;
}
.row.g-0:hover::before {
	background-color: transparent;
}
.row.g-0 {
	position : relative;
}
</style>
    <?php require( bin_css  . 'index.php' );?>
    <?php require( bin_js   . 'index.php' );?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php'); ?>
        <?php require( bin_php . 'element/loading.php'); ?>
        <style>
        	@media (min-width: 0px) {
			    .card-columns {
			        column-count: 1;
			        -webkit-column-count : 1;
			    }
			}

			@media (min-width: 760px) {
			    .card-columns {
			        column-count: 2;
			        -webkit-column-count : 2;
			    }
			}

			@media (min-width: 1100px) {
			    .card-columns {
			        column-count: 2;
			        -webkit-column-count : 2;
			    }
			}

			@media (min-width: 1600px) {
			    .card-columns {
			        column-count: 4;
			        -webkit-column-count : 4;
			    }
			}
			.card-columns>.card {
				display: inline-block;
				width : 100%;
				position : relative;
			}
		</style>
        <div id="page-wrapper" class='content'>
        	<div class='card-deck g-3 text-white'>
	        	<div class='card card-primary border'>
	        		<div class='card-heading'><h4><a href='customers.php'><?php \singleton\fontawesome::getInstance( )->Customer( );?> Customer: <?php echo $Customer[ 'Name' ];?></a></h4></div>
	        		<div class='card-body bg-dark'>
						<div class='Screen-Tabs'>
							<div class='row g-3'>
							<?php if(isset($Privileges[ 'Invoice' ]) && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
								?><div tab='collection' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection( 2 );?></div>
										<div class ='nav-text'>Collections</div>
									</div>
								</div><?php 
							}
							if(isset($Privileges[ 'Customer' ]) && $Privileges[ 'Customer' ][ 'User_Privilege' ] >= 4){
								?><div tab='contract'class='link-page text-white col-xl-1 col-4' onClick="document.location.href='contacts.php?Type=0&Entity=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Users( 2 );?></div>
										<div class ='nav-text'>Contacts</div>
									</div>
								</div><?php 
							}
							if(isset($Privileges[ 'Contract' ]) && $Privileges[ 'Contract' ][ 'User_Privilege' ] >= 4){
								?><div tab='contract'class='link-page text-white col-xl-1 col-4' onClick="document.location.href='contracts.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Contract( 2 );?></div>
										<div class ='nav-text'>Contracts</div>
									</div>
								</div><?php 
							}
							?><div tab='feed' class='link-page text-white col-xl-1 col-4' onClick="someFunction(this,'customer-feed.php?ID=<?php echo $_GET[ 'ID' ];?>');">
								<div class='p-1 border'>
									<div class='nav-icon'><?php \singleton\fontawesome::getInstance( ) ->Activities( 2 );?></div>
									<div class ='nav-text'>Feed</div>
								</div>
							</div><?php 
							if(isset($Privileges[ 'Time' ]) && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
								?><div tab='hours' class='link-page text-white col-xl-1 col-4' onClick="someFunction(this,'hours.php?ID=<?php echo $_GET[ 'ID' ];?>');">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Payroll( 2 );?></div>
										<div class ='nav-text'>Hours</div>
									</div>
								</div><?php 
							}
							if(isset($Privileges[ 'Job' ]) && $Privileges[ 'Job' ][ 'User_Privilege' ] >= 4){
								?><div tab='job' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job( 2 );?></div>
										<div class ='nav-text'>Jobs</div>
									</div>
							</div><?php }
							if(isset($Privileges[ 'Invoice' ]) && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
								?><div tab='invoice' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='invoices.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice( 2 );?></div>
										<div class ='nav-text'>Invoices</div>
									</div>
								</div><?php 
							}

							$result = \singleton\database::getInstance( )->query(
								null,
								"   SELECT Count(Location.Loc) AS Counter
									FROM   Loc AS Location
									WHERE  Location.Owner = ?;",
								array( $_GET[ 'ID' ] )
							);
							$count = sqlsrv_fetch_array($result)[ 'Counter' ];
							if($count == 1){
								$result = \singleton\database::getInstance( )->query(null,
									"   SELECT Location.Loc AS Location_ID
										FROM   Loc AS Locaiton
										WHERE  Location.Owner = ?;",
									array(
										$_GET[ 'ID' ]
									)
								);
								$Location_ID = sqlsrv_fetch_array($result)[ 'Location_ID' ];
								if(isset($Privileges[ 'Location' ]) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
									?><div tab='location' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='location.php?ID=<?php echo $Location_ID;?>';">
										<div class='p-1 border'>
											<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location( 2 );?></div>
											<div class ='nav-text'>Location</div>
										</div>
									</div><?php 
								}
							} elseif($count > 1) {
								if(isset($Privileges[ 'Location' ]) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
									?><div tab='location' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='locations.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
										<div class='p-1 border'>
											<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location( 2 );?></div>
											<div class ='nav-text'>Locations</div>
										</div>
									</div><?php 
								}
							}?>
							<?php if(isset($Privileges['Proposal']) && $Privileges['Proposal']['User_Privilege'] >= 4){
								?><div tab='proposals' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Proposal( 2 );?></div>
										<div class ='nav-text'>Proposals</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges[ 'Finances' ]) && $Privileges['Finances']['User_Privilege'] >= 4){
								?><div tab='pnl' class='link-page text-white col-xl-1 col-4' onClick="someFunction(this,'customer-pnl.php?ID=<?php echo $_GET[ 'ID' ];?>');">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Financial( 2 );?></div>
										<div class ='nav-text'>P&L</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges[ 'Ticket' ]) && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 4){
								?><div tab='tickets'class='link-page text-white col-xl-1 col-4' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name'];?>';">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket( 2 );?></div>
										<div class ='nav-text'>Tickets</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges[ 'Time' ]) && $Privileges['Time']['Group_Privilege'] >= 4){
								?><div tab='timeline' class='link-page text-white col-xl-1 col-4' onClick="someFunction(this,'customer-timeline.php?ID=<?php echo $_GET[ 'ID' ];?>');">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->History( 2 );?></div>
										<div class ='nav-text'>Timeline</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
								?><div tab='testing' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='inspections.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Inspection( 2 );?></div>
										<div class ='nav-text'>Inspections</div>
									</div>
								</div><?php 
							}?>
							<?php if(isset($Privileges[ 'Unit' ]) && $Privileges[ 'Unit' ][ 'User_Privilege' ] >= 4){
								?><div tab='unit' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit( 2 );?></div>
										<div class ='nav-text'>Units</div>
									</div>
								</div><?php }?>
							<?php if(isset($Privileges[ 'Violation' ]) && $Privileges[ 'Violation' ][ 'User_Privilege' ] >= 4){
								?><div tab='violation' class='link-page text-white col-xl-1 col-4' onClick="document.location.href='violations.php?Customer=<?php echo $Customer[ 'Name' ];?>';">
									<div class='p-1 border'>
										<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation( 2 );?></div>
										<div class ='nav-text'>Violations</div>
									</div>
								</div><?php 
							}?>
						</div>
					</div>
				 </div>
			</div>
			<div class='card-columns'>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?> Infomation</h5></div>
							<div class='col-2'>&nbsp;</div>
						</div>
					</div>
				 	<div class='card-body bg-dark'><form action='customer.php?ID=<?php echo $_GET[ 'ID' ];?>' method='POST'>
				 		<input type='hidden' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' />
						<div class='row g-0'>
							<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Name:</div>
							<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Customer['Name'];?>' /></div>
						</div>
						<div class='row g-0'>
							<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
							<div class='col-8'><select name='Status' class='form-control edit'>
								<option value=''>Select</option>
								<option value='0' <?php echo $Customer[ 'Status' ] == 0 ? 'selected' : null;?>>Active</option>
								<option value='1' <?php echo $Customer[ 'Status' ] == 1 ? 'selected' : null;?>>Inactive</option>
							</select></div>
						</div>
						<div class='row g-0'>
							<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Web(1);?> Website:</div>
							<div class='col-8'><input type='text' class='form-control edit' name='Website' value='<?php echo strlen($Customer['Website']) > 0 ?  $Customer['Website'] : "&nbsp;";?>' /></div>
						</div>
						<div class='row g-0'>
							<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Address:</div>
							<div class='col-6'></div>
							<div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
							<div class='col-3 border-bottom border-white my-auto'>Street:</div>
							<div class='col-8'><input type='text' class='form-control edit' name='Street' value='<?php echo $Customer['Street'];?>' /></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
							<div class='col-3 border-bottom border-white my-auto'>City:</div>
							<div class='col-8'><input type='text' class='form-control edit' name='City' value='<?php echo $Customer['City'];?>' /></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
							<div class='col-3 border-bottom border-white my-auto'>State:</div>
							<div class='col-8'><input type='text' class='form-control edit' name='State' value='<?php echo $Customer['State'];?>' /></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
							<div class='col-3 border-bottom border-white my-auto'>Zip:</div>
							<div class='col-8'><input type='text' class='form-control edit' name='Zip' value='<?php echo $Customer['Zip'];?>' /></div>
						</div>
					</form></div>
				</div>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?> Units</h5></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
					<div class='card-body bg-dark'>
						<?php
							$r = \singleton\database::getInstance( )->query(
								null,
								"	SELECT 	Count( Unit.ID ) AS Units
									FROM   	Elev AS Unit
									   		LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
									WHERE  	Location.Owner = ? ;",
								array(
									$_GET[ 'ID' ] 
								)
							);
						?>
						<div class='row g-0'>
						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Type</div>
						    <div class='col-6'>&nbsp;</div>
							<div class='col-2'>&nbsp;</div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Elevators</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Units' value='<?php
								$r = \singleton\database::getInstance( )->query(
									null,
									"	SELECT 	Count( Unit.ID ) AS Units
										FROM   	Elev AS Unit
										   		LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
										WHERE  		Location.Owner = ? 
												AND Unit.Type = 'Elevator'
								;",array($_GET['ID']));
								echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Escalators</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Units' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT 	Count( Unit.ID ) AS Units
									FROM   	Elev AS Unit
										   	LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
									WHERE  		Location.Owner = ? 
											AND Unit.Type = 'Escalator'
								;",array($_GET['ID']));
								echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Escalator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Other</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Units' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT 	Count( Unit.ID ) AS Units
									FROM   	Elev AS Unit
										   	LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
									WHERE  		Location.Owner = ? 
											AND Unit.Type NOT IN ( 'Elevator', 'Escalator' )
								;",array($_GET['ID']));
								echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' disabled onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
				</div>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Violation( 1 );?> Violations</h5></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
					<div class='card-body bg-dark'>
						<div class='row g-0'>
						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Violation(1);?> Violations</div>
						    <div class='col-6'>&nbsp;</div>
							<div class='col-2'>&nbsp;</div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Preliminary</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Violations' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT Count( Violation.ID ) AS Violations
									FROM   Violation
										   LEFT JOIN Loc AS Location ON Violation.Loc = Location.Loc
									WHERE  Location.Owner = ?
											AND Violation.Status = 'Preliminary Report'
								;",array($_GET['ID']));
								echo $r ? sqlsrv_fetch_array($r)['Violations'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=Preliminary Report';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Job Created</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Violations' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT Count( Violation.ID ) AS Violations
									FROM   Violation
										   LEFT JOIN Loc AS Location ON Violation.Loc = Location.Loc
									WHERE  Location.Owner = ?
											AND Violation.Status = 'Job Created'
								;",array($_GET['ID']));
								echo $r ? sqlsrv_fetch_array($r)['Violations'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=Preliminary Report';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
				</div>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Job( 1 );?> Jobs</h5></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
					<div class='card-body bg-dark'>
						<div class='row g-0'>
						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Statuses</div>
						    <div class='col-6'>&nbsp;</div>
							<div class='col-2'>&nbsp;</div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Open</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Jobs' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT Count( Job.ID ) AS Jobs
									FROM   Job
										   LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
									WHERE  		Location.Owner = ?
											AND Job.Type <> 9
											AND Job.Status = 0
								;",array($_GET['ID']));
							echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>On Hold</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Jobs' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT Count( Job.ID ) AS Jobs
									FROM   Job
										   LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
									WHERE  Location.Owner = ? AND Job.Status = 2
								;",array($_GET['ID']));
							echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Closed</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Jobs' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT Count( Job.ID ) AS Jobs
									FROM   Job
										   LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
									WHERE  		Location.Owner = ?
											AND Job.Type <> 9
											AND Job.Status = 1
								;",array($_GET['ID']));
							echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
				</div>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-9'><h5><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?>Tickets</h5></div>
							<div class='col-3'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
					<div class='card-body bg-dark'>
						<div class='row g-0'>
						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Statuses</div>
						    <div class='col-6'>&nbsp;</div>
							<div class='col-2'>&nbsp;</div>
						</div>
						<div class='row g-0'><?php
								$r = \singleton\database::getInstance( )->query(
									null,
									"	SELECT Count( Tickets.ID ) AS Tickets
										FROM   (
													(
														SELECT 	TicketO.ID AS ID
														FROM   	TicketO
															   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
														WHERE  		Location.Owner = ?
																AND TicketO.Assigned = 0
													)
												) AS Tickets;",
									array(
										$_GET[ 'ID' ]
									)
								);
							?><div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Open</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Tickets' value='<?php
								echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'><?php
								$r = \singleton\database::getInstance( )->query(
									null,
									"	SELECT Count( Tickets.ID ) AS Tickets
										FROM   (
													(
														SELECT 	TicketO.ID AS ID
														FROM   	TicketO
															   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
														WHERE  		Location.Owner = ?
																AND TicketO.Assigned = 1
													)
												) AS Tickets;",
									array(
										$_GET[ 'ID' ]
									)
								);
							?><div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Assigned</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Tickets' value='<?php
								echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'><?php
								$r = \singleton\database::getInstance( )->query(
									null,
									"	SELECT Count( Tickets.ID ) AS Tickets
										FROM   (
													(
														SELECT 	TicketO.ID AS ID
														FROM   	TicketO
															   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
														WHERE  		Location.Owner = ?
																AND TicketO.Assigned = 2
													)
												) AS Tickets;",
									array(
										$_GET[ 'ID' ]
									)
								);
							?><div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>En Route</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Tickets' value='<?php
								echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'><?php
								$r = \singleton\database::getInstance( )->query(
									null,
									"	SELECT Count( Tickets.ID ) AS Tickets
										FROM   (
													(
														SELECT 	TicketO.ID AS ID
														FROM   	TicketO
															   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
														WHERE  		Location.Owner = ?
																AND TicketO.Assigned = 3
													)
												) AS Tickets;",
									array(
										$_GET[ 'ID' ]
									)
								);
							?><div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>On Site</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Tickets' value='<?php
								echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=3';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'><?php
								$r = \singleton\database::getInstance( )->query(
									null,
									"	SELECT Count( Tickets.ID ) AS Tickets
										FROM   (
													(
														SELECT 	TicketO.ID AS ID
														FROM   	TicketO
															   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
														WHERE  		Location.Owner = ?
																AND TicketO.Assigned = 6
													)
												) AS Tickets;",
									array(
										$_GET[ 'ID' ]
									)
								);
							?><div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Review</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Tickets' value='<?php
								echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=6';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'><?php
								$r = \singleton\database::getInstance( )->query(
									null,
									"	SELECT Count( Tickets.ID ) AS Tickets
										FROM   (
													(
														SELECT 	TicketO.ID AS ID
														FROM   	TicketO
															   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
														WHERE  		Location.Owner = ?
																AND TicketO.Assigned = 4
													)
												) AS Tickets;",
									array(
										$_GET[ 'ID' ]
									)
								);
							?><div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Complete</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Tickets' value='<?php
								echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
				</div>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-9'><h5><?php \singleton\fontawesome::getInstance( )->Proposal( 1 );?>Proposals</h5></div>
							<div class='col-3'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
					<div class='card-body bg-dark'>
						<div class='row g-0'>
						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Status</div>
						    <div class='col-6'>&nbsp;</div>
							<div class='col-2'>&nbsp;</div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Open</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Proposals' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT 	Count(Estimate.ID) AS Proposals
									FROM   	Estimate
										   	LEFT JOIN Loc AS Location ON Estimate.LocID = Location.Loc
									WHERE  		Location.Owner = ?
											AND Estimate.Status = 0
								;",array($_GET['ID']));
								echo $r ? sqlsrv_fetch_array($r)['Proposals'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Awarded</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Proposals' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT 	Count(Estimate.ID) AS Proposals 
									FROM   	Estimate
										   	LEFT JOIN Loc AS Location ON Estimate.LocID = Location.Loc
									WHERE  		Location.Owner = ?
											AND Estimate.Status = 4
								;",array($_GET['ID']));
								echo $r ? sqlsrv_fetch_array($r)['Proposals'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
				</div>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-9'><h5><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?>Invoices</h5></div>
							<div class='col-3'><button class='h-100 w-100' onClick="document.location.href='invoices.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
					<div class='card-body bg-dark'>
						<div class='row g-0'>
						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Invoices</div>
						    <div class='col-6'>&nbsp;</div>
							<div class='col-2'>&nbsp;</div>
						</div>
						<?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >= 4) {?>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Open</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Collections' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT Count( OpenAR.Ref ) AS Count
									FROM   OpenAR
										   LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
									WHERE  Location.Owner = ?
								;",array($_GET['ID']));
								$Count = $r ? sqlsrv_fetch_array($r)['Count'] : 0;
								echo $Count
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<?php }?>
					</div>
				</div>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-9'><h5><?php \singleton\fontawesome::getInstance( )->Collection( 1 );?>Collections</h5></div>
							<div class='col-3'><button class='h-100 w-100' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
					<div class='card-body bg-dark'>
						<?php if(isset($Privileges['Collection']) && $Privileges['Collection']['User_Privilege'] >= 4) {?>
						<div class='row g-0'>
						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Balance</div>
						    <div class='col-6'><input class='form-control' type='text' disabled name='Balance' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT Sum( OpenAR.Balance ) AS Balance
									FROM   OpenAR
										   LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
									WHERE  Location.Owner = ?
								;",array($_GET['ID']));
								$Balance = $r ? sqlsrv_fetch_array($r)['Balance'] : 0;
								echo money_format('%(n',$Balance);
							?>' /></div>
							<div class='col-2'>&nbsp;</div>
						</div>
						<?php }?>
						
					</div>
				</div>
			</div>
		</div>
  	</div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
