<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION[ 'User' ],$_SESSION[ 'Hash' ] ) ) {
    $result = \singleton\database::getInstance( )->query(
    	null,
    	"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",
    	array(
    		$_SESSION[ 'User' ],
    		$_SESSION[ 'Hash' ]
    	)
    );
    $Connection = sqlsrv_fetch_array($result);
    $User = \singleton\database::getInstance( )->query(
    	null,
    	"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",
    	array(
    		$_SESSION[ 'User' ]
    	)
    );
    $User = sqlsrv_fetch_array($User);
    $result = \singleton\database::getInstance( )->query(
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
    if(isset($Privileges['Location'])
        && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4
        && $Privileges[ 'Location' ][ 'Group_Privilege' ] >= 4
        && $Privileges[ 'Location' ][ 'Other_Privilege' ] >= 4){$Privileged = TRUE;}
    elseif($Privileges[ 'Location' ][ 'User_Privilege' ] >= 4
        && is_numeric($_GET[ 'ID' ])){
        $result = \singleton\database::getInstance( )->query(
        	null,
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
    \singleton\database::getInstance( )->query(
      null,
      "   INSERT INTO Activity([User], [Date], [Page])
          VALUES(?,?,?);",
    array(
      $_SESSION[ 'User' ],
              date("Y-m-d H:i:s"),
                  "location.php")
      );
    if(		!isset($Connection[ 'ID' ])
    	|| 	!$Privileged
    ){
    	?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php
	} else {
        $result = \singleton\database::getInstance( )->query(
        	null,
        	"	SELECT TOP 1
	                    Location.Loc         AS ID,
	                    Location.Tag         AS Name,
                      Location.Status      AS Status,
	                    Location.Address     AS Street,
	                    Location.City        AS City,
	                    Location.State       AS State,
	                    Location.Zip         AS Zip,
	                    Location.fLong 		   AS Longitude,
	                    Location.Latt 		   AS Latitude,
	                    Location.Balance     AS Balance,
	                    Location.Custom8 	   AS Resident_Mechanic,
	                    Zone.Name            AS Zone,
	                    Location.Route       AS Route_ID,
	                    Emp.ID               AS Route_Mechanic_ID,
	                    Emp.fFirst           AS Route_Mechanic_First_Name,
	                    Emp.Last             AS Route_Mechanic_Last_Name,
	                    Location.Owner 	     AS Customer_ID,
	                    Customer.Name    	 AS Customer_Name,
	                    Territory.ID 		 AS Territory_ID,
	                    Territory.Name       AS Territory_Name
	            FROM    Loc AS Location
	                    LEFT JOIN Zone         ON Location.Zone   = Zone.ID
	                    LEFT JOIN Route        ON Location.Route  = Route.ID
	                    LEFT JOIN Emp          ON Route.Mech = Emp.fWork
	                    LEFT JOIN (
	            				SELECT 	Owner.ID    	AS ID,
			                    		Rol.Name    	AS Name,
			                    		Rol.Address 	AS Street,
					                    Rol.City    	AS City,
					                    Rol.State   	AS State,
					                    Rol.Zip     	AS Zip,
					                    Owner.Status  	AS Status,
										Rol.Website 	AS Website
								FROM    Owner
								LEFT JOIN Rol ON Owner.Rol 			= Rol.ID
	            		) AS Customer ON Location.Owner 			= Customer.ID
	                    LEFT JOIN Terr AS Territory ON Territory.ID = Location.Terr
	            WHERE 		Location.Loc = ?
	            		OR 	Location.Tag = ?;",
	        array(
	        	isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null,
	        	isset( $_GET[ 'Name' ] ) ? $_GET[ 'Name' ] : null
	        )
	    );
        $Location = sqlsrv_fetch_array( $result );


        if( isset( $_POST ) ){
        	$Location[ 'Name' ] = isset( $_POST[ 'Name' ] ) ? $_POST[ 'Name' ] : $Location[ 'Name' ];
        	$Location[ 'Status' ] = isset( $_POST[ 'Status' ] ) ? $_POST[ 'Status' ] : $Location[ 'Status' ];
        	$Location[ 'Street' ] = isset( $_POST[ 'Street' ] ) ? $_POST[ 'Street' ] : $Location[ 'Street' ];
        	$Location[ 'City' ] = isset( $_POST[ 'City' ] ) ? $_POST[ 'City' ] : $Location[ 'City' ];
        	$Location[ 'State' ] = isset( $_POST[ 'State' ] ) ? $_POST[ 'State' ] : $Location[ 'State' ];
        	$Location[ 'Zip' ] = isset( $_POST[ 'Zip' ] ) ? $_POST[ 'Zip' ] : $Location[ 'Zip' ];

        	\singleton\database::getInstance( )->query(
        		null,
        		"	UPDATE 	Loc
        			SET 	Loc.Tag = ?,
                    Loc.Status = ?,
          					Loc.Address = ?,
          					Loc.City = ?,
          					Loc.State = ?,
          					Loc.Zip = ?
        			WHERE 	Rol.ID = ?;",
        		array(
        			$Location[ 'Name' ],
              $Location[ 'Status' ],
        			$Location[ 'Street' ],
        			$Location[ 'City' ],
        			$Location[ 'State' ],
        			$Location[ 'Zip' ],
        			$Location[ 'Rolodex' ]
        		)
        	);
        }
?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php
    	$_GET[ 'Bootstrap' ] = '5.1';
    	require( bin_meta . 'index.php');
    	require( bin_css  . 'index.php');
    	require( bin_js   . 'index.php');
    ?><style>
    	.link-page {
    		font-size : 14px;
    	}
    </style>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class='card card-primary border-0'>
				<div class='card-heading'><h4><a href='location.php?ID=<?php echo $_GET['ID'];?>'><?php \singleton\fontawesome::getInstance( )->Location();?> Location : <?php echo substr( $Location['Name'], 0, 20 );?></a></h4></div>
				<div class='card-body links-page bg-darker row'>
					<?php if(isset($Privileges['Location']) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
					?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='location/information.php'">
				        <div class='p-1 border border-white'>
							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Information( 2 );?></div>
							<div class='nav-text'>Information</div>
				        </div>
				    </div><?php }?>
				    <?php if(isset($Privileges['Location']) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='contacts.php?Type=Location&Entity=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Users( 2 );?></div>
								<div class='nav-text'>Contacts</div>
					        </div>
					    </div><?php
					}?>
				    <?php if(isset($Privileges['Location']) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='contracts.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Contract( 2 );?></div>
								<div class='nav-text'>Contracts</div>
					        </div>
					    </div><?php
					}?>
				    <?php if(isset($Privileges['Location']) && $Privileges[ 'Location' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='collections.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
						          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection( 2 );?></div>
						          <div class='nav-text'>Collections</div>
					        </div>
					    </div><?php
					}?>
				    <?php if(isset($Privileges['Contact']) && $Privileges[ 'Contact' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='contacts.php'">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->User( 2 );?></div>
								<div class='nav-text'>Contacts</div>
					        </div>
					    </div><?php
					}?>
					<?php if(isset($Privileges['Collection']) && $Privileges[ 'Collection' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='collection.php>Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection(2);?></div>
								<div class ='nav-text'>Collections</div>
							</div>
						</div><?php
					}?>
					<?php if(isset($Privileges['Customer']) && $Privileges[ 'Customer' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='customer.php?ID=<?php echo $Location[ 'Customer_ID' ];?>';">
					        <div class='p-1 border border-white'>
						          <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer( 2 );?></div>
						          <div class='nav-text'>Customer</div>
					        </div>
					    </div><?php
					}?>
					<?php if(isset($Privileges['Invoice']) && $Privileges[ 'Invoice' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='invoices.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice(2);?></div>
								<div class ='nav-text'>Invoices</div>
							</div>
						</div><?php
					}?>
					<?php if(isset($Privileges['Job']) && $Privileges[ 'Job' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='jobs.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(2);?></div>
								<div class ='nav-text'>Jobs</div>
							</div>
						</div><?php
					}?>
					<?php if(isset($Privileges['Admin']) && $Privileges[ 'Admin' ][ 'User_Privilege' ] >= 4 ){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='lobs.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Admin(2);?></div>
								<div class ='nav-text'>Log</div>
							</div>
						</div><?php
					}?>
					<?php if(isset($Privileges['Finances']) && $Privileges[ 'Finances' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='pnl.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->PnL(2);?></div>
								<div class ='nav-text'>P&L</div>
							</div>
						</div><?php
					}?>
					<?php if(isset($Privileges['Proposal']) && $Privileges[ 'Proposal' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='invoices.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Proposal(2);?></div>
								<div class ='nav-text'>Proposals</div>
							</div>
						</div><?php
					}?>
					<?php
						$result = \singleton\database::getInstance( )->query(
						    null,
						    " 	SELECT 	Count(Route.ID) AS Counter
								FROM	Route
										LEFT JOIN Emp ON Route.Mech = Emp.fWork
								WHERE		Emp.ID = ?
										AND Route.ID = ?;",
						    array(
						        $_SESSION[ 'User' ],
						        $Location[ 'Route_ID' ]
						    )
						);
					$count = sqlsrv_fetch_array($result)[ 'Counter' ];
					if(		isset($Privileges[ 'Route' ])
						&& 	( (	$Privileges[ 'Route' ][ 'User_Privilege' ] >= 4
							&& 	$count > 0 )
							||	$Privileges[ 'Route' ][ 'Other_Privilege' ] >= 4
					) ){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='route.php?ID=<?php echo $Location[ 'Route_ID' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Route(2);?></div>
								<div class ='nav-text'>Route</div>
							</div>
						</div><?php
					}?>
					<?php if(isset($Privileges[ 'Ticket' ]) && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='tickets.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket(2);?></div>
								<div class ='nv-text'>Tickets</div>
							</div>
						</div><?php
					}?>
					<?php if(isset($Privileges[ 'Time' ]) && $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='timeline.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
					        	<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(2);?></div>
								<div class ='nav-text'>Timeline</div>
							</div>
						</div><?php
					}?>
					<?php if(isset($Privileges[ 'Unit' ]) && $Privileges[ 'Unit' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='units.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(2);?></div>
								<div class ='nav-text'>Units</div>
							</div>
						</div><?php
					}?>
					<?php if(isset($Privileges[ 'Violation' ]) && $Privileges[ 'Violation' ][ 'User_Privilege' ] >= 4){
						?><div class='link-page text-white col-xl-1 col-4' onclick="document.location.href='violations.php?Location=<?php echo $Location[ 'Name' ];?>';">
					        <div class='p-1 border border-white'>
								<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation(2);?></div>
								<div class ='nav-text'>Violations</div>
							</div>
						</div><?php
					}?>
				</div>
			</div>
			<div class='card-group'>
				<?php if( !in_array( $Location[ 'Latitude' ], array( null, 0 ) ) && !in_array( $Location['Longitude' ], array( null, 0 ) ) ){
					?><div class='card card-primary'>
						<div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Map( 1 );?> Map</div>
						<div class='card-body bg-darker'>
							<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB05GymhObM_JJaRCC3F4WeFn3KxIOdwEU"></script>
							<script type="text/javascript">
				                var map;
				                function initialize() {
				                     map = new google.maps.Map(
				                        document.getElementById( 'location_map' ),
				                        {
				                          zoom: 10,
				                          center: new google.maps.LatLng( <?php echo $Location[ 'Latitude' ];?>, <?php echo $Location[ 'Longitude' ];?> ),
				                          mapTypeId: google.maps.MapTypeId.ROADMAP
				                        }
				                    );
				                    var markers = [];
				                    markers[0] = new google.maps.Marker({
				                        position: {
				                            lat:<?php echo $Location['Latitude'];?>,
				                            lng:<?php echo $Location['Longitude'];?>
				                        },
				                        map: map,
				                        title: '<?php echo $Location[ 'Name' ];?>'
				                    });
				                }
				                $(document).ready(function(){ initialize(); });
				            </script>
					        <div class='card-body'>
					        	<div id='location_map' class='map'>&nbsp;</div>
					        </div>
						</div>
					</div>
				<?php }?>
				<div class='card card-primary '>
					<div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Info( 1 );?> Information</div>
					<div class='card-body bg-darker'>
                <div class='row g-0'>
                    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Name:</div>
                    <div class='col-8'><input class='form-control edit' type='text' name='Name' value='<?php echo strlen($Location['Name']) ? $Location['Name'] : "&nbsp;";?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
                  <div class='col-8'><select name='Status' class='form-control edit'>
                    <option value=''>Select</option>
                    <option value='0' <?php echo $Location[ 'Status' ] == 0 ? 'selected' : null;?>>Active</option>
                    <option value='1' <?php echo $Location[ 'Status' ] == 1 ? 'selected' : null;?>>Inactive</option>
                  </select></div>
                </div>
                <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Address:</div>
				            <div class='col-6'></div>
                    <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?Customer=<?php echo $Location[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
				        </div>
                <div class='row g-0'>
                  <div class='col-1'>&nbsp;</div>
				          <div class='col-3'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Street:</div>
				          <div class='col-8'><input class='form-control edit' type='text' name='Street' value='<?php echo strlen($Location['Street']) ? $Location['Street'] : "&nbsp;";?>' /></div>
				        </div>
				        <div class='row g-0'>
                  <div class='col-1'>&nbsp;</div>
				            <div class='col-3'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
				            <div class='col-8'><input class='form-control edit' type='text' name='City' value='<?php echo strlen($Location['City']) ? $Location['City'] : "&nbsp;";?>' /></div>
				        </div>
				        <div class='row g-0'>
                  <div class='col-1'>&nbsp;</div>
				            <div class='col-3'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State:</div>
				            <div class='col-8'><input class='form-control edit' type='text' name='State' value='<?php echo strlen($Location['State']) ? $Location['State'] : "&nbsp;";?>' /></div>
				        </div>
				        <div class='row g-0'>
                  <div class='col-1'>&nbsp;</div>
				            <div class='col-3'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
				            <div class='col-8'><input class='form-control edit' type='text' name='Zip' value='<?php echo strlen($Location['Zip']) ? $Location['Zip'] : "&nbsp;";?>' /></div>
				        </div>
                <div class='row g-0'>
                  <div class='col-1'>&nbsp;</div>
                   <div class='col-3'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Latitude:</div>
                   <div class='col-8'><input class='form-control edit' type='text' name='Latitude' value='<?php echo strlen($Location['Latitude']) ? $Location['Latitude'] : "&nbsp;";?>' /></div>
               </div>
               <div class='row g-0'>
                 <div class='col-1'>&nbsp;</div>
                   <div class='col-3'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Longitude:</div>
                   <div class='col-8'><input class='form-control edit' type='text' name='Longitude' value='<?php echo strlen($Location['Longitude']) ? $Location['Longitude'] : "&nbsp;";?>' /></div>
               </div>
				    </div>
				</div>
				<div class='card card-primary'>
				    <div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Maintenance( 1 );?> Operations</div>
				    <div class='card-body bg-darker'>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Route();?> Route:</div>
				            <div class='col-8'><?php
				                echo $Privileges['Route']['Other_Privilege'] >= 4 || $User[ 'ID' ] == $Location['Route_Mechanic_ID']
				                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . proper( $Location['Route_Mechanic_First_Name'] . ' ' . $Location['Route_Mechanic_Last_Name'] ) . "' /></div><div class='col-4'><button onClick=\"document.location.href='route.php?ID=" . $Location['Route_ID'] . "';\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
				                    :   proper( $Location['Route_Mechanic_First_Name'] . ' ' . $Location['Route_Mechanic_Last_Name'] );
				            ?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Resident(1);?> Resident:</div>
				            <div class='col-8'><input readonly type='text' name='Resident' value='<?php echo isset($Location['Resident_Mechanic']) && $Location['Resident_Mechanic'] != '' ? proper($Location['Resident_Mechanic']) : "No";?>' /></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Division(1);?> Division:</div>
				            <div class='col-8'><?php
				                echo $Privileges['Division']['Other_Privilege'] >= 4
				                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . proper( $Location['Division'] ) . "' /></div><div class='col-4'><button onClick=\"document.location.href='division.php?ID=" . $Location['Division_ID'] . "';\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
				                    :   "<input readonly type='text' name='Division' value='" . proper( $Location['Division'] ) . "' />";?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Units</div>
				            <div class='col-8'><?php
				                $r = \singleton\database::getInstance( )->query(
				                    null,
				                    "   SELECT  Count(Unit.ID) AS Count
				                        FROM    Elev AS Unit
				                        WHERE   Unit.Loc = ?;",
				                    array(
				                        $_GET[ 'ID' ]
				                    )
				                );
				                echo $r
				                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-4'><button tab='units' onClick=\"linkTab('units');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
				                    :   0;
				            ?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Jobs</div>
				            <div class='col-8'><?php
				                $r = \singleton\database::getInstance( )->query(
				                    null,
				                    "   SELECT  Count(Job.ID) AS Count
				                        FROM    Job
				                        WHERE   Job.Loc = ?;",
				                    array(
				                        $_GET[ 'ID' ]
				                    )
				                );
				                echo $r
				                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-4'><button onClick=\"linkTab('jobs');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
				                    :   0;
				            ?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Violation(1);?> Violations</div>
				            <div class='col-8'><?php
				                $r = \singleton\database::getInstance( )->query(
				                    null,
				                    "   SELECT  Count(ID) AS Count
				                        FROM    Violation
				                        WHERE   Violation.Loc = ?;",
				                    array(
				                        $_GET[ 'ID' ]
				                    )
				                );
				                echo $r
				                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-4'><button onClick=\"linkTab('violations');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
				                    :   0;
				            ?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Tickets</div>
				            <div class='col-8'><?php
				                $r = \singleton\database::getInstance( )->query(
				                    null,
				                    "   SELECT  Count(Ticket.ID) AS Count
				                        FROM    TicketO AS Ticket
				                        WHERE   Ticket.LID = ?;",
				                    array(
				                        $_GET[ 'ID' ]
				                    )
				                );
				                echo $r
				                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-4'><button onClick=\"linkTab('tickets');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
				                    :   0;
				            ?></div>
				        </div>
				    </div>
				</div>
				<?php if(isset($Privileges['Finances']) && $Privileges['Finances']['Other_Privilege'] >= 4){?>
				<div class='card card-primary '>
				    <div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Sales( 1 );?> Sales</div>
				    <div class='card-body bg-darker'>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Territory(1);?> Territory:</div>
				            <div class='col-8'>
				                <div class='row g-0'>
				                    <div class='col-8'><input readonly type='text' value='<?php echo $Location[ 'Territory_Name'];?>' /></div>
				                    <div class='col-4'>
				                        <button onClick="document.location.href='territory.php?ID=<?php echo $Location['Territory_ID'];?>';">
				                            <i class='fa fa-search fa-fw fa-1x'></i>
				                        </button>
				                    </div>
				                </div>
				            </div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Proposals</div>
				            <div class='col-8'><?php
				                $r = \singleton\database::getInstance( )->query(null,"
				                    SELECT Count(Estimate.ID) AS Count
				                    FROM   Estimate
				                    WHERE  Estimate.LocID = ?
				                ;",array($_GET['ID']));
				                echo $r
				                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-4'><button onClick=\"someFunction(this,'proposals.php?ID=" . $Location['Location_ID'] . "');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
				                    :   0;
				            ?></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Collection(1);?> Balance:</div>
				            <div class='col-8'><input readonly type='text' name='Balance' value='<?php echo isset($Location['Location_Balance']) && $Location['Location_Balance'] != '' ? money_format('%.2n',$Location['Location_Balance']) : "&nbsp;";?>' /></div>
				        </div>
				        <div class='row g-0'>
				            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Collection</div>
				            <div class='col-8'><?php
				                $r = \singleton\database::getInstance( )->query(
				                    null,
				                    "   SELECT  Count( OpenAR.Ref ) AS Count
				                        FROM    OpenAR
				                                LEFT JOIN Invoice ON  OpenAR.Ref = Invoice.Ref
				                        WHERE   OpenAR.Loc = ?
				                                AND Invoice.Status = 1;",
				                    array(
				                        $_GET['ID']
				                    )
				                );
				                echo $r
				                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-4'><button onClick=\"someFunction(this,'proposals.php?ID=" . $Location['Location_ID'] . "');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
				                    :   0;
				            ?></div>
				        </div>
				    </div>
				</div>
				<?php }?>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html><?php }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
