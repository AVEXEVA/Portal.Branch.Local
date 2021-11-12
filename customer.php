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
				                    Rol.Latt 	AS Latitude,
				                    Rol.fLong   AS Longitude,
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
        	$Customer[ 'Name' ] 		= isset( $_POST[ 'Name' ] ) 	 ? $_POST[ 'Name' ] 	 : $Customer[ 'Name' ];
        	$Customer[ 'Status' ] 		= isset( $_POST[ 'Status' ] ) 	 ? $_POST[ 'Status' ] 	 : $Customer[ 'Status' ];
        	$Customer[ 'Website' ] 		= isset( $_POST[ 'Website' ] ) 	 ? $_POST[ 'Website' ] 	 : $Customer[ 'Website' ];
        	$Customer[ 'Street' ] 		= isset( $_POST[ 'Street' ] ) 	 ? $_POST[ 'Street' ] 	 : $Customer[ 'Street' ];
        	$Customer[ 'City' ] 		= isset( $_POST[ 'City' ] ) 	 ? $_POST[ 'City' ] 	 : $Customer[ 'City' ];
        	$Customer[ 'State' ] 		= isset( $_POST[ 'State' ] ) 	 ? $_POST[ 'State' ] 	 : $Customer[ 'State' ];
        	$Customer[ 'Zip' ] 			= isset( $_POST[ 'Zip' ] ) 		 ? $_POST[ 'Zip' ] 		 : $Customer[ 'Zip' ];
        	$Customer[ 'Latitude' ] 	= isset( $_POST[ 'Latitude' ] )  ? $_POST[ 'Latitude' ] 	 : $Customer[ 'Latitude' ];
        	$Customer[ 'Longitude' ] 	= isset( $_POST[ 'Longitude' ] ) ? $_POST[ 'Longitude' ] : $Customer[ 'Longitude' ];

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
        					Rol.Zip = ?,
        					Rol.Latt = ?,
        					Rol.fLong = ?
        			WHERE 	Rol.ID = ?;",
        		array(
        			$Customer[ 'Name' ],
        			$Customer[ 'Website' ],
        			$Customer[ 'Street' ],
        			$Customer[ 'City' ],
        			$Customer[ 'State' ],
        			$Customer[ 'Zip' ],
        			$Customer[ 'Latitude' ],
        			$Customer[ 'Longitude' ],
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
  pointer-events : none;
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
        	<div class='card'>
        		<div class='card-heading'><h5><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?><span><?php echo $Customer[ 'Name' ];?></span></h5></div>
        		<div class='card-body bg-dark text-white'>
					<div class='card-columns'>
						<?php if( !in_array( $Customer[ 'Latitude' ], array( null, 0 ) ) && !in_array( $Customer['Longitude' ], array( null, 0 ) ) ){
							?><div class='card card-primary my-3'>
								<div class='card-heading'>
									<div class='row g-0 px-3 py-2'>
										<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
										<div class='col-2'>&nbsp;</div>
									</div>
								</div>
								<div class='card-body bg-darker'>
									<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB05GymhObM_JJaRCC3F4WeFn3KxIOdwEU"></script>
									<script type="text/javascript">
						                var map;
						                function initialize() {
						                     map = new google.maps.Map(
						                        document.getElementById( 'location_map' ),
						                        {
						                          zoom: 10,
						                          center: new google.maps.LatLng( <?php echo $Customer[ 'Latitude' ];?>, <?php echo $Customer[ 'Longitude' ];?> ),
						                          mapTypeId: google.maps.MapTypeId.ROADMAP
						                        }
						                    );
						                    var markers = [];
						                    markers[0] = new google.maps.Marker({
						                        position: {
						                            lat:<?php echo $Customer['Latitude'];?>,
						                            lng:<?php echo $Customer['Longitude'];?>
						                        },
						                        map: map,
						                        title: '<?php echo $Customer[ 'Name' ];?>'
						                    });
						                }
						                $(document).ready(function(){ initialize(); });
						            </script>
							        <div class='card-body'>
							        	<div id='location_map' class='map'>&nbsp;</div>
							        </div>
								</div>
							</div><?php 
						}?>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
									<div class='col-2'>&nbsp;</div>
								</div>
							</div>
						 	<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>><form action='customer.php?ID=<?php echo $_GET[ 'ID' ];?>' method='POST'>
						 		<input type='hidden' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' />
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Name:</div>
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
									<div class='col-8'><select class='form-control edit' name='State'>
										<option <?php echo $Customer[ 'State' ] == 'AL' ? 'selected' : null;?> value='AL'>Alabama</option>
										<option <?php echo $Customer[ 'State' ] == 'AK' ? 'selected' : null;?> value='AK'>Alaska</option>
										<option <?php echo $Customer[ 'State' ] == 'AZ' ? 'selected' : null;?> value='AZ'>Arizona</option>
										<option <?php echo $Customer[ 'State' ] == 'AR' ? 'selected' : null;?> value='AR'>Arkansas</option>
										<option <?php echo $Customer[ 'State' ] == 'CA' ? 'selected' : null;?> value='CA'>California</option>
										<option <?php echo $Customer[ 'State' ] == 'CO' ? 'selected' : null;?> value='CO'>Colorado</option>
										<option <?php echo $Customer[ 'State' ] == 'CT' ? 'selected' : null;?> value='CT'>Connecticut</option>
										<option <?php echo $Customer[ 'State' ] == 'DE' ? 'selected' : null;?> value='DE'>Delaware</option>
										<option <?php echo $Customer[ 'State' ] == 'DC' ? 'selected' : null;?> value='DC'>District Of Columbia</option>
										<option <?php echo $Customer[ 'State' ] == 'FL' ? 'selected' : null;?> value='FL'>Florida</option>
										<option <?php echo $Customer[ 'State' ] == 'GA' ? 'selected' : null;?> value='GA'>Georgia</option>
										<option <?php echo $Customer[ 'State' ] == 'HI' ? 'selected' : null;?> value='HI'>Hawaii</option>
										<option <?php echo $Customer[ 'State' ] == 'ID' ? 'selected' : null;?> value='ID'>Idaho</option>
										<option <?php echo $Customer[ 'State' ] == 'IL' ? 'selected' : null;?> value='IL'>Illinois</option>
										<option <?php echo $Customer[ 'State' ] == 'IN' ? 'selected' : null;?> value='IN'>Indiana</option>
										<option <?php echo $Customer[ 'State' ] == 'IA' ? 'selected' : null;?> value='IA'>Iowa</option>
										<option <?php echo $Customer[ 'State' ] == 'KS' ? 'selected' : null;?> value='KS'>Kansas</option>
										<option <?php echo $Customer[ 'State' ] == 'KY' ? 'selected' : null;?> value='KY'>Kentucky</option>
										<option <?php echo $Customer[ 'State' ] == 'LA' ? 'selected' : null;?> value='LA'>Louisiana</option>
										<option <?php echo $Customer[ 'State' ] == 'ME' ? 'selected' : null;?> value='ME'>Maine</option>
										<option <?php echo $Customer[ 'State' ] == 'MD' ? 'selected' : null;?> value='MD'>Maryland</option>
										<option <?php echo $Customer[ 'State' ] == 'MA' ? 'selected' : null;?> value='MA'>Massachusetts</option>
										<option <?php echo $Customer[ 'State' ] == 'MI' ? 'selected' : null;?> value='MI'>Michigan</option>
										<option <?php echo $Customer[ 'State' ] == 'MN' ? 'selected' : null;?> value='MN'>Minnesota</option>
										<option <?php echo $Customer[ 'State' ] == 'MS' ? 'selected' : null;?> value='MS'>Mississippi</option>
										<option <?php echo $Customer[ 'State' ] == 'MO' ? 'selected' : null;?> value='MO'>Missouri</option>
										<option <?php echo $Customer[ 'State' ] == 'MT' ? 'selected' : null;?> value='MT'>Montana</option>
										<option <?php echo $Customer[ 'State' ] == 'NE' ? 'selected' : null;?> value='NE'>Nebraska</option>
										<option <?php echo $Customer[ 'State' ] == 'NV' ? 'selected' : null;?> value='NV'>Nevada</option>
										<option <?php echo $Customer[ 'State' ] == 'NH' ? 'selected' : null;?> value='NH'>New Hampshire</option>
										<option <?php echo $Customer[ 'State' ] == 'NJ' ? 'selected' : null;?> value='NJ'>New Jersey</option>
										<option <?php echo $Customer[ 'State' ] == 'NM' ? 'selected' : null;?> value='NM'>New Mexico</option>
										<option <?php echo $Customer[ 'State' ] == 'NY' ? 'selected' : null;?> value='NY'>New York</option>
										<option <?php echo $Customer[ 'State' ] == 'NC' ? 'selected' : null;?> value='NC'>North Carolina</option>
										<option <?php echo $Customer[ 'State' ] == 'ND' ? 'selected' : null;?> value='ND'>North Dakota</option>
										<option <?php echo $Customer[ 'State' ] == 'OH' ? 'selected' : null;?> value='OH'>Ohio</option>
										<option <?php echo $Customer[ 'State' ] == 'OK' ? 'selected' : null;?> value='OK'>Oklahoma</option>
										<option <?php echo $Customer[ 'State' ] == 'OR' ? 'selected' : null;?> value='OR'>Oregon</option>
										<option <?php echo $Customer[ 'State' ] == 'PA' ? 'selected' : null;?> value='PA'>Pennsylvania</option>
										<option <?php echo $Customer[ 'State' ] == 'RI' ? 'selected' : null;?> value='RI'>Rhode Island</option>
										<option <?php echo $Customer[ 'State' ] == 'SC' ? 'selected' : null;?> value='SC'>South Carolina</option>
										<option <?php echo $Customer[ 'State' ] == 'SD' ? 'selected' : null;?> value='SD'>South Dakota</option>
										<option <?php echo $Customer[ 'State' ] == 'TN' ? 'selected' : null;?> value='TN'>Tennessee</option>
										<option <?php echo $Customer[ 'State' ] == 'TX' ? 'selected' : null;?> value='TX'>Texas</option>
										<option <?php echo $Customer[ 'State' ] == 'UT' ? 'selected' : null;?> value='UT'>Utah</option>
										<option <?php echo $Customer[ 'State' ] == 'VT' ? 'selected' : null;?> value='VT'>Vermont</option>
										<option <?php echo $Customer[ 'State' ] == 'VA' ? 'selected' : null;?> value='VA'>Virginia</option>
										<option <?php echo $Customer[ 'State' ] == 'WA' ? 'selected' : null;?> value='WA'>Washington</option>
										<option <?php echo $Customer[ 'State' ] == 'WV' ? 'selected' : null;?> value='WV'>West Virginia</option>
										<option <?php echo $Customer[ 'State' ] == 'WI' ? 'selected' : null;?> value='WI'>Wisconsin</option>
										<option <?php echo $Customer[ 'State' ] == 'WY' ? 'selected' : null;?> value='WY'>Wyoming</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Zip:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Zip' value='<?php echo $Customer['Zip'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Latitude:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Latitude' value='<?php echo $Customer['Latitude'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Longitude:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Longitude' value='<?php echo $Customer['Longitude'];?>' /></div>
								</div>
							</form></div>
						</div>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Location( 1 );?><span>Locations</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='locations.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div> 
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Locations' ] ) && $_SESSION[ 'Cards' ][ 'Locations' ] == 0 ? "style='display:none;'" : null;?>>
								<?php
									$r = \singleton\database::getInstance( )->query(
										null,
										"	SELECT 	Count( Location.ID ) AS Locations
											FROM   	Loc AS Location
											WHERE  	Location.Owner = ? ;",
										array(
											$_GET[ 'ID' ] 
										)
									);
								?>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Category</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>All</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
										echo $r ? sqlsrv_fetch_array($r)['Locations'] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
						</div>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?><span>Units</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div> 
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
										$r = \singleton\database::getInstance( )->query(null,"
											SELECT 	Count( Unit.ID ) AS Units
											FROM   	Elev AS Unit
												   	LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
											WHERE  		Location.Owner = ? 
													AND Unit.Type NOT IN ( 'Elevator', 'Escalator' )
										;",array($_GET['ID']));
										echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' readonly onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
						</div>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Job( 1 );?><span>Jobs</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Jobs' ] ) && $_SESSION[ 'Cards' ][ 'Jobs' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Statuses</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Open</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Jobs' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Jobs' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Jobs' value='<?php
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
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?><span>Tickets</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Violations' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Violation(1);?><span>Violations</span></div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Preliminary</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Violations' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Violations' value='<?php
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
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Proposal( 1 );?><span>Proposals</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Status</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Open</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Proposals' value='<?php
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
								    <div class='col-6'><input class='form-control' type='text' readonly name='Proposals' value='<?php
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
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?>Invoices</h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='invoices.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Invoices</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >= 4) {?>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Open</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Collections' value='<?php
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
								<?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >= 4) {?>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Closed</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Collections' value='<?php
										$r = \singleton\database::getInstance( )->query(null,"
											SELECT 	Count( Invoice.Ref ) AS Count
											FROM   	Invoice
												   	LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
											WHERE  		Location.Owner = ?
													AND Invoice.Ref NOT IN ( SELECT Ref FROM OpenAR )

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
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Collection( 1 );?><span>Collections</span></h5></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</div>
							<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Collections' ] ) && $_SESSION[ 'Cards' ][ 'Collections' ] == 0 ? "style='display:none;'" : null;?> style='display:none;'>
								<?php if(isset($Privileges['Collection']) && $Privileges['Collection']['User_Privilege'] >= 4) {?>
								<div class='row g-0'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Balance</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Balance' value='<?php
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
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Profit</span></h5></div>
									<div class='col-2'>&nbsp;</div>
								</div>
							</div>
						 	<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Profit' ] ) && $_SESSION[ 'Cards' ][ 'Profit' ] == 0 ? "style='display:none;'" : null;?>><?php require( 'bin/js/chart/customer_profit.php' );?></div>
						 </div>
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
