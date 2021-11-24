<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
  $result = \singleton\database::getInstance( )->query(
    'Portal',
    " SELECT  [Connection].[ID]
      FROM    dbo.[Connection]
      WHERE       [Connection].[User] = ?
              AND [Connection].[Hash] = ?;",
    array(
      $_SESSION[ 'Connection' ][ 'User' ],
      $_SESSION[ 'Connection' ][ 'Hash' ]
    )
  );
  $Connection = sqlsrv_fetch_array($result);
  //User
  $result = \singleton\database::getInstance( )->query(
    null,
    " SELECT  Emp.fFirst  AS First_Name,
              Emp.Last    AS Last_Name,
              Emp.fFirst + ' ' + Emp.Last AS Name,
              Emp.Title AS Title,
              Emp.Field   AS Field
      FROM  Emp
      WHERE   Emp.ID = ?;",
    array(
        $_SESSION[ 'Connection' ][ 'User' ]
    )
  );
  $User   = sqlsrv_fetch_array( $result );
  //Privileges
  $Access = 0;
  $Hex = 0;
  $result = \singleton\database::getInstance( )->query(
    'Portal',
    "   SELECT  [Privilege].[Access],
                    [Privilege].[Owner],
                    [Privilege].[Group],
                    [Privilege].[Department],
                    [Privilege].[Database],
                    [Privilege].[Server],
                    [Privilege].[Other],
                    [Privilege].[Token],
                    [Privilege].[Internet]
      FROM      dbo.[Privilege]
      WHERE     Privilege.[User] = ?;",
    array(
        $_SESSION[ 'Connection' ][ 'User' ]
    )
  );
  $Privileges = array();
  if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

      $key = $Privilege['Access'];
      unset( $Privilege[ 'Access' ] );
      $Privileges[ $key ] = implode( '', array(
        dechex( $Privilege[ 'Owner' ] ),
        dechex( $Privilege[ 'Group' ] ),
        dechex( $Privilege[ 'Department' ] ),
        dechex( $Privilege[ 'Database' ] ),
        dechex( $Privilege[ 'Server' ] ),
        dechex( $Privilege[ 'Other' ] ),
        dechex( $Privilege[ 'Token' ] ),
        dechex( $Privilege[ 'Internet' ] )
      ) );
  }}
  if(   !isset( $Connection[ 'ID' ] )
      ||  !isset( $Privileges[ 'Location' ] )
      ||  !check( privilege_read, level_group, $Privileges[ 'Location' ] )
  ){ ?><?php require('404.html');?><?php }
  else {
  	$ID = isset( $_GET[ 'ID' ] )
		? $_GET[ 'ID' ]
		: (
			isset( $_POST[ 'ID' ] )
				? $_POST[ 'ID' ]
				: null
		);
	$Name = isset( $_GET[ 'Name' ] )
		? $_GET[ 'Name' ]
		: (
			isset( $_POST[ 'Name' ] )
				? $_POST[ 'Name' ]
				: null
		);
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
                    Location.Maint 		   AS Maintenance,
                    Location.Geolock 	   AS Geofence,
                    Location.STax 		   AS Sales_Tax,
                    Location.InUse 		   AS In_Use,
                    Customer.ID 		     AS Customer_ID,
                    Customer.Name        AS Customer_Name,
                    Location.Route       AS Route_ID,
                    Route.NAme 			     AS Route_Name,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Location.Owner 	     AS Customer_ID,
                    Customer.Name    	   AS Customer_Name,
                    Territory.ID 		     AS Territory_ID,
                    Territory.Name       AS Territory_Name,
                    Division.ID 		     AS Division_ID,
                    Division.Name 		    AS Division_Name
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
                    LEFT JOIN Zone AS Division ON Location.Zone = Division.ID
            WHERE 		Location.Loc = ?
            		OR 	Location.Tag = ?;",
        array(
        	$ID,
        	$Name
        )
    );

    $Location = in_array( $ID, array( null, 0, '', ' ' ) ) || !$result ? array(
    	'ID' => null,
    	'Name' => null,
    	'Status' => null,
    	'Street' => null,
    	'City' => null,
    	'State' => null,
    	'Zip' => null,
    	'Latitude' => null,
    	'Longitude' => null,
    	'Maintenance' => null,
    	'Geofence' => null,
    	'Sales_Tax' => null,
    	'Customer_ID' => null,
    	'Customer_Name' => null,
    	'Division_ID' => null,
    	'Division_Name' => null,
    	'Route_ID' => null,
    	'Route_Name' => null,
    	'Territory_ID' => null,
    	'Territory_Name' => null,
    	'Sales_Tax' => null,
    	'In_Use' => null
    ) : sqlsrv_fetch_array( $result );


    if( isset( $_POST ) && count( $_POST ) > 0 ){
    	$Location[ 'Name' ] 	= isset( $_POST[ 'Name' ] ) 		? $_POST[ 'Name' ] 			: $Location[ 'Name' ];
    	$Location[ 'Status' ] 	= isset( $_POST[ 'Status' ] ) 		? $_POST[ 'Status' ] 		: $Location[ 'Status' ];
    	$Location[ 'Street' ] 	= isset( $_POST[ 'Street' ] ) 		? $_POST[ 'Street' ] 		: $Location[ 'Street' ];
    	$Location[ 'City' ] 	= isset( $_POST[ 'City' ] ) 		? $_POST[ 'City' ] 			: $Location[ 'City' ];
    	$Location[ 'State' ] 	= isset( $_POST[ 'State' ] ) 		? $_POST[ 'State' ] 		: $Location[ 'State' ];
    	$Location[ 'Zip' ] 		= isset( $_POST[ 'Zip' ] ) 			? $_POST[ 'Zip' ] 			: $Location[ 'Zip' ];
    	$Location[ 'Latitude'] 	= isset( $_POST[ 'Latitude' ] )		? $_POST[ 'Latitude' ]  	: $Location[ 'Latitude' ];
    	$Location[ 'Longitude'] = isset( $_POST[ 'Longitude' ] )	? $_POST[ 'Longitude' ] 	: $Location[ 'Longitude' ];
    	$Location[ 'Maintenance' ] = isset( $_POST[ 'Maintenance' ] ) ? $_POST[ 'Maintenance' ] : $Location[ 'Maintenance' ];
    	$Location[ 'Geofence' ] = isset( $_POST[ 'Geofence' ] ) ? $_POST[ 'Geofence' ] : $Location[ 'Geofence' ];
    	$Location[ 'Sales_Tax' ] = isset( $_POST[ 'Sales_Tax' ] ) ? $_POST[ 'Sales_Tax' ] : $Location[ 'Sales_Tax' ];
    	$Location[ 'In_Use' ] = isset( $_POST[ 'In_Use' ] ) ? $_POST[ 'In_Use' ] : $Location[ 'In_Use' ];
    	$Location[ 'Customer_ID' ] = isset( $_POST[ 'Customer_ID' ] ) ? $_POST[ 'Customer_ID' ] : $Location[ 'Customer_ID' ];
    	$Location[ 'Customer_Name' ] = isset( $_POST[ 'Customer' ] ) ? $_POST[ 'Customer' ] : $Location[ 'Customer_Name' ];

    	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
    		$result = \singleton\database::getInstance( )->query(
	    		null,
	    		"	DECLARE @MAXID INT;
	    			DECLARE @OwnerID INT;
        			SET @MAXID = CASE WHEN ( SELECT Max( Loc ) FROM dbo.Loc ) IS NULL THEN 0 ELSE ( SELECT Max( Loc ) FROM dbo.Loc ) END;
        			SET @OwnerID = ( SELECT Top 1 Owner.ID FROM dbo.Owner LEFT JOIN dbo.Rol ON Owner.Rol = Rol.ID WHERE Rol.Name = ? );
        			INSERT INTO dbo.Loc( Loc, Owner, Tag, Status, Address, City, State, Zip, Latt, fLong, Maint, Geolock, STax, InUse )
	    			VALUES( @MAXID + 1, @OwnerID, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
        			SELECT @MAXID + 1;",
	    		array(
	    			$Location[ 'Customer_Name' ],
	    			$Location[ 'Name' ],
	    			$Location[ 'Status' ],
	    			$Location[ 'Street' ],
	    			$Location[ 'City' ],
	    			$Location[ 'State' ],
	    			$Location[ 'Zip' ],
	    			$Location[ 'Latitude' ],
	    			$Location[ 'Longitude' ],
	    			is_null( $Location[ 'Maintenance' ] ) ? 0 : $Location[ 'Maintenance' ],
	    			is_null( $Location[ 'Geofence' ] ) ? 0 : $Location[ 'Geofence' ],
	    			is_null( $Location[ 'Sales_Tax' ] ) ? 0 : $Location[ 'Sales_Tax' ],
	    			is_null( $Location[ 'In_Use' ] ) ? 0 : $Location[ 'In_Use' ]
	    		)
	    	);
        	$Location[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];

        	header( 'Location: location.php?ID=' . $Location[ 'ID' ] );
    	} else {
	    	\singleton\database::getInstance( )->query(
	    		null,
	    		"	UPDATE 	Loc
	    			SET 	Loc.Tag = ?,
	                Loc.Status = ?,
	      					Loc.Address = ?,
	      					Loc.City = ?,
	      					Loc.State = ?,
	      					Loc.Zip = ?,
	      					Loc.Latt = ?,
	      					Loc.fLong = ?,
	      					Loc.Maint = ?,
	      					Loc.Owner = (
	      						SELECT 	ID
	      						FROM 	(
                            SELECT  Owner.ID,
                                    Rol.Name,
                                    Owner.Status
                            FROM    Owner
                                    LEFT JOIN Rol ON Owner.Rol = Rol.ID
                          ) AS Customer
	      						WHERE 	Customer.Name = ?
	      					)
	    			WHERE 	Loc.Loc= ?;",
	    		array(
	    			$Location[ 'Name' ],
	          $Location[ 'Status' ],
	    			$Location[ 'Street' ],
	    			$Location[ 'City' ],
	    			$Location[ 'State' ],
	    			$Location[ 'Zip' ],
	    			$Location[ 'Latitude' ],
	    			$Location[ 'Longitude' ],
	    			$Location[ 'Maintenance' ],
	    			$Location[ 'Customer_Name' ],
	    			$Location[ 'ID' ]
	    		)
	    	);
	    }
    }
?><!DOCTYPE html>
<html lang="en">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
     <style>
    	.link-page {
    		font-size : 14px;
    	}
     </style>
</head>
<body>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php');?>
        <div id="page-wrapper" class='content'>
			<div class='card card-primary border-0'>
				<div class='card-heading'>
        			<div class='row g-0 px-3 py-2'>
        				<div class='col-6'><h5><?php \singleton\fontawesome::getInstance( )->Location( 1 );?><a href='locations.php?<?php echo isset( $_SESSION[ 'Tables' ][ 'Location' ][ 0 ] ) ? http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Locations' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Locations' ][ 0 ] : array( ) ) : null;?>'>Location</a>: <span><?php echo is_null( $Location[ 'ID' ] ) ? 'New' : $Location[ 'Name' ];?></span></h5></div>
        				<div class='col-2'></div>
        				<div class='col-2'>
        					<div class='row g-0'>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='location.php';">Create</button></div>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='location.php?ID=<?php echo $Location[ 'ID' ];?>';">Refresh</button></div>
        					</div>
        				</div>
        				<div class='col-2'>
        					<div class='row g-0'>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='location.php?ID=<?php echo !is_null( $Location[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Locations' ], true )[ array_search( $Location[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Locations' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='locations.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Locations' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Locations' ][ 0 ] : array( ) );?>';">Table</button></div>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='location.php?ID=<?php echo !is_null( $Location[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Locations' ], true )[ array_search( $Location[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Locations' ], true ) ) + 1 ] : null;?>';">Next</button></div>
        					</div>
        				</div>
        			</div>
        		</div>
				<div class='card-body bg-dark text-white'>
				<div class='card-columns'>
					<?php if( !in_array( $Location[ 'Latitude' ], array( null, 0 ) ) && !in_array( $Location['Longitude' ], array( null, 0 ) ) ){
						?><div class='card card-primary'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Map( 1 );?><span>Map</span></h5></div>
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
						<div class='card-heading'>
							<div class='row g-0 px-3 py-2'>
								<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
								<div class='col-2'>&nbsp;</div>
							</div>
						</div>
						<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
							<form action='location.php?ID=<?php echo $Location[ 'ID' ];?>' method='POST'>
								<input type='hidden' name='ID' value='<?php echo isset( $Location[ 'ID' ] ) ? $Location[ 'ID' ] : null;?>' />
				                <div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?>Name:</div>
									<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Location['Name'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Customer:</div>
								    <div class='col-6'>
								    	<input type='text' autocomplete='off' class='form-control edit' name='Customer' value='<?php echo $Location[ 'Customer_Name' ];?>' />
								    	<script>
								    		$( 'input[name="Customer"]' )
										        .typeahead({
										            minLength : 4,
										            hint: true,
										            highlight: true,
										            limit : 5,
										            display : 'FieldValue',
										            source: function( query, result ){
										                $.ajax({
										                    url : 'bin/php/get/search/Customers.php',
										                    method : 'GET',
										                    data    : {
										                        search :  $('input:visible[name="Customer"]').val( )
										                    },
										                    dataType : 'json',
										                    beforeSend : function( ){
										                        abort( );
										                    },
										                    success : function( data ){
										                        result( $.map( data, function( item ){
										                            return item.FieldValue;
										                        } ) );
										                    }
										                });
										            },
										            afterSelect: function( value ){
										                $( 'input[name="Customer"]').val( value );
										                $( 'input[name="Customer"]').closest( 'form' ).submit( );
										            }
										        }
										    );
								    	</script>
								    </div>
								    <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='customer.php?ID=<?php echo $Location[ 'Customer_ID' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Address:</div>
									<div class='col-6'></div>
									<div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?Location=<?php echo $Location[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Street:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Street' value='<?php echo $Location['Street'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>City:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='City' value='<?php echo $Location['City'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>State:</div>
									<div class='col-8'><select class='form-control edit' name='State'>
										<option <?php echo $Location[ 'State' ] == 'AL' ? 'selected' : null;?> value='AL'>Alabama</option>
										<option <?php echo $Location[ 'State' ] == 'AK' ? 'selected' : null;?> value='AK'>Alaska</option>
										<option <?php echo $Location[ 'State' ] == 'AZ' ? 'selected' : null;?> value='AZ'>Arizona</option>
										<option <?php echo $Location[ 'State' ] == 'AR' ? 'selected' : null;?> value='AR'>Arkansas</option>
										<option <?php echo $Location[ 'State' ] == 'CA' ? 'selected' : null;?> value='CA'>California</option>
										<option <?php echo $Location[ 'State' ] == 'CO' ? 'selected' : null;?> value='CO'>Colorado</option>
										<option <?php echo $Location[ 'State' ] == 'CT' ? 'selected' : null;?> value='CT'>Connecticut</option>
										<option <?php echo $Location[ 'State' ] == 'DE' ? 'selected' : null;?> value='DE'>Delaware</option>
										<option <?php echo $Location[ 'State' ] == 'DC' ? 'selected' : null;?> value='DC'>District Of Columbia</option>
										<option <?php echo $Location[ 'State' ] == 'FL' ? 'selected' : null;?> value='FL'>Florida</option>
										<option <?php echo $Location[ 'State' ] == 'GA' ? 'selected' : null;?> value='GA'>Georgia</option>
										<option <?php echo $Location[ 'State' ] == 'HI' ? 'selected' : null;?> value='HI'>Hawaii</option>
										<option <?php echo $Location[ 'State' ] == 'ID' ? 'selected' : null;?> value='ID'>Idaho</option>
										<option <?php echo $Location[ 'State' ] == 'IL' ? 'selected' : null;?> value='IL'>Illinois</option>
										<option <?php echo $Location[ 'State' ] == 'IN' ? 'selected' : null;?> value='IN'>Indiana</option>
										<option <?php echo $Location[ 'State' ] == 'IA' ? 'selected' : null;?> value='IA'>Iowa</option>
										<option <?php echo $Location[ 'State' ] == 'KS' ? 'selected' : null;?> value='KS'>Kansas</option>
										<option <?php echo $Location[ 'State' ] == 'KY' ? 'selected' : null;?> value='KY'>Kentucky</option>
										<option <?php echo $Location[ 'State' ] == 'LA' ? 'selected' : null;?> value='LA'>Louisiana</option>
										<option <?php echo $Location[ 'State' ] == 'ME' ? 'selected' : null;?> value='ME'>Maine</option>
										<option <?php echo $Location[ 'State' ] == 'MD' ? 'selected' : null;?> value='MD'>Maryland</option>
										<option <?php echo $Location[ 'State' ] == 'MA' ? 'selected' : null;?> value='MA'>Massachusetts</option>
										<option <?php echo $Location[ 'State' ] == 'MI' ? 'selected' : null;?> value='MI'>Michigan</option>
										<option <?php echo $Location[ 'State' ] == 'MN' ? 'selected' : null;?> value='MN'>Minnesota</option>
										<option <?php echo $Location[ 'State' ] == 'MS' ? 'selected' : null;?> value='MS'>Mississippi</option>
										<option <?php echo $Location[ 'State' ] == 'MO' ? 'selected' : null;?> value='MO'>Missouri</option>
										<option <?php echo $Location[ 'State' ] == 'MT' ? 'selected' : null;?> value='MT'>Montana</option>
										<option <?php echo $Location[ 'State' ] == 'NE' ? 'selected' : null;?> value='NE'>Nebraska</option>
										<option <?php echo $Location[ 'State' ] == 'NV' ? 'selected' : null;?> value='NV'>Nevada</option>
										<option <?php echo $Location[ 'State' ] == 'NH' ? 'selected' : null;?> value='NH'>New Hampshire</option>
										<option <?php echo $Location[ 'State' ] == 'NJ' ? 'selected' : null;?> value='NJ'>New Jersey</option>
										<option <?php echo $Location[ 'State' ] == 'NM' ? 'selected' : null;?> value='NM'>New Mexico</option>
										<option <?php echo $Location[ 'State' ] == 'NY' ? 'selected' : null;?> value='NY'>New York</option>
										<option <?php echo $Location[ 'State' ] == 'NC' ? 'selected' : null;?> value='NC'>North Carolina</option>
										<option <?php echo $Location[ 'State' ] == 'ND' ? 'selected' : null;?> value='ND'>North Dakota</option>
										<option <?php echo $Location[ 'State' ] == 'OH' ? 'selected' : null;?> value='OH'>Ohio</option>
										<option <?php echo $Location[ 'State' ] == 'OK' ? 'selected' : null;?> value='OK'>Oklahoma</option>
										<option <?php echo $Location[ 'State' ] == 'OR' ? 'selected' : null;?> value='OR'>Oregon</option>
										<option <?php echo $Location[ 'State' ] == 'PA' ? 'selected' : null;?> value='PA'>Pennsylvania</option>
										<option <?php echo $Location[ 'State' ] == 'RI' ? 'selected' : null;?> value='RI'>Rhode Island</option>
										<option <?php echo $Location[ 'State' ] == 'SC' ? 'selected' : null;?> value='SC'>South Carolina</option>
										<option <?php echo $Location[ 'State' ] == 'SD' ? 'selected' : null;?> value='SD'>South Dakota</option>
										<option <?php echo $Location[ 'State' ] == 'TN' ? 'selected' : null;?> value='TN'>Tennessee</option>
										<option <?php echo $Location[ 'State' ] == 'TX' ? 'selected' : null;?> value='TX'>Texas</option>
										<option <?php echo $Location[ 'State' ] == 'UT' ? 'selected' : null;?> value='UT'>Utah</option>
										<option <?php echo $Location[ 'State' ] == 'VT' ? 'selected' : null;?> value='VT'>Vermont</option>
										<option <?php echo $Location[ 'State' ] == 'VA' ? 'selected' : null;?> value='VA'>Virginia</option>
										<option <?php echo $Location[ 'State' ] == 'WA' ? 'selected' : null;?> value='WA'>Washington</option>
										<option <?php echo $Location[ 'State' ] == 'WV' ? 'selected' : null;?> value='WV'>West Virginia</option>
										<option <?php echo $Location[ 'State' ] == 'WI' ? 'selected' : null;?> value='WI'>Wisconsin</option>
										<option <?php echo $Location[ 'State' ] == 'WY' ? 'selected' : null;?> value='WY'>Wyoming</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Zip:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Zip' value='<?php echo $Location['Zip'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Latitude:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Latitude' value='<?php echo $Location['Latitude'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Longitude:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Longitude' value='<?php echo $Location['Longitude'];?>' /></div>
								</div>
				           </form>
					    </div>
					</div>
					<div class='card card-primary'>
						<div class='card-heading'>
							<div class='row g-0 px-3 py-2'>
								<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Maintenance( 1 );?><span>Maintenance</span></h5></div>
								<div class='col-2 p-1 text-center rounded bg-<?php echo $Location[ 'Maintenance' ] == 1 ? 'success' : 'warning';?>'><?php echo $Location[ 'Maintenance' ] == 1 ? 'Active' : 'Inactive';?></div>
							</div>
						</div>
						<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
							<form action='location.php?ID=<?php echo $Location[ 'ID' ];?>' method='POST'>
								<input type='hidden' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' />
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Maintenance:</div>
								    <div class='col-6'>
								    	<select class='form-control edit' name='Maintenance'>
								    		<option value=''>Select</option>
								    		<option value='0' <?php echo $Location[ 'Maintenance' ] == 0 ? 'selected' : null;?>>Disabled</option>
								    		<option value='1' <?php echo $Location[ 'Maintenance' ] == 1 ? 'selected' : null;?>>Enabled</option>
								    	</select>
								    </div>
								    <div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Division(1);?> Division:</div>
								    <div class='col-6'>
								    	<input type='hidden' disabled name='Division' value='<?php echo $Location[ 'Division_ID' ];?>' />
								    	<input type='text' class='form-control edit' name='Division_Autocomplete' value='<?php echo $Location[ 'Division_Name' ];?>' />
								    </div>
								    <div class='col-2'><button class='h-100 w-100' type='button' <?php
								    	if( in_array( $Location[ 'Route_ID' ], array( null, 0, '', ' ') ) ){
								    		echo "onClick=\"document.location.href='divisions.php';\"";
								    	} else {
								    		echo "onClick=\"document.location.href='division.php?ID=" . $Location[ 'Division_ID' ] . "';\"";
								    	}
								    ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Route(1);?> Route:</div>
								    <div class='col-6'>
								    	<input type='text' autocomplete='off' class='form-control edit' name='Route' value='<?php echo $Location[ 'Route_Name' ];?>' />
								    	<script>
								    		$( 'input[name="Route"]' )
										        .typeahead({
										            minLength : 4,
										            hint: true,
										            highlight: true,
										            limit : 5,
										            display : 'FieldValue',
										            source: function( query, result ){
										                $.ajax({
										                    url : 'bin/php/get/search/Routes.php',
										                    method : 'GET',
										                    data    : {
										                        search :  $('input:visible[name="Route"]').val( )
										                    },
										                    dataType : 'json',
										                    beforeSend : function( ){
										                        abort( );
										                    },
										                    success : function( data ){
										                        result( $.map( data, function( item ){
										                            return item.FieldValue;
										                        } ) );
										                    }
										                });
										            },
										            afterSelect: function( value ){
										                $( 'input[name="Route"]').val( value );
										                $( 'input[name="Route"]').closest( 'form' ).submit( );
										            }
										        }
										    );
								    	</script>
								    </div>
								    <div class='col-2'><button class='h-100 w-100' type='button' <?php
								    	if( in_array( $Location[ 'Route_ID' ], array( null, 0, '', ' ') ) ){
								    		echo "onClick=\"document.location.href='routes.php';\"";
								    	} else {
								    		echo "onClick=\"document.location.href='route.php?ID=" . $Location[ 'Route_ID' ] . "';\"";
								    	}
								    ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
							</form>
						</div>
					</div>
					<div class='card card-primary'>
					    <div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Maintenance( 1 );?>Operations</div>
					    <div class='card-body bg-darker'>

					        <div class='row g-0'>
					            <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Resident(1);?> Resident:</div>
					            <div class='col-8'><input readonly type='text' name='Resident' value='<?php echo isset($Location['Resident_Mechanic']) && $Location['Resident_Mechanic'] != '' ? proper($Location['Resident_Mechanic']) : "No";?>' /></div>
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
					                        $Location[ 'ID' ]
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
					                        $Location[ 'ID' ]
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
					                        $Location[ 'ID' ]
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
					                        $Location[ 'ID' ]
					                    )
					                );
					                echo $r
					                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-4'><button onClick=\"linkTab('tickets');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
					                    :   0;
					            ?></div>
					        </div>
					    </div>
					</div>
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
					                ;",array($Location[ 'ID' ]));
					                echo $r
					                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-4'><button onClick=\"someFunction(this,'proposals.php?ID=" . $Location['ID'] . "');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
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
					                        $Location[ 'ID' ]
					                    )
					                );
					                echo $r
					                    ?   "<div class='row g-0'><div class='col-8'><input readonly type='text' value='" . number_format(sqlsrv_fetch_array($r)['Count']) . "' /></div><div class='col-4'><button onClick=\"someFunction(this,'proposals.php?ID=" . $Location['ID'] . "');\"><i class='fa fa-search fa-fw fa-1x'></i></button></div></div>"
					                    :   0;
					            ?></div>
					        </div>
					    </div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
</body>
</html><?php }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
