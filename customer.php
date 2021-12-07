<?php
// Session set for the root index page
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection for the user and the hash
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
  // This selects the User and Hash from the Dbo
    $Connection = sqlsrv_fetch_array($result);
    //Sets $result into $Connection
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
// gets Employee first/last/employee ID/ Title/Field and sets to $User
	//Privileges
	$Access = 0;
	$Hex = 0;
  // Defaults Privileges to Zero
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
		  	$_SESSION[ 'Connection' ][ 'User' ],
		)
	);
// Selects $User Privilege and appends to $_SESSION user array
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
// Checks $User Privilege and appends to $_SESSION user array
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Customer' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Customer' ] )
    ){ ?><?php require('404.html');?><?php }
    //If privleges dont check, 404s out
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'customer.php'
        )
      );
  // If privleges check, Timestamp $_SESSION user and show customer.php
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
  // sets $ID, $Name Variable and Posts ID and Name into $result
        $result = \singleton\database::getInstance( )->query(
        	null,
            "	SELECT 	Top 1
            			Customer.*
            	FROM    (
            				SELECT 	Owner.ID    AS ID,
            						Owner.Type      AS Type,
            						Rolodex.ID 		  AS Rolodex,
                                    Rolodex.Name    AS Name,
                                    Rolodex.Phone   AS Phone,
                                    Rolodex.Email   AS Email,
                                    Rolodex.Contact AS Contact,
                                    Rolodex.Address AS Street,
                                    Rolodex.City    AS City,
                                    Rolodex.State   AS State,
                                    Rolodex.Zip     AS Zip,
                                    Rolodex.Latt 	  AS Latitude,
                                    Rolodex.fLong   AS Longitude,
                                    Owner.Status    AS Status,
          							Rolodex.Website AS Website,
          							Owner.Internet  AS Internet,
          							Owner.fLogin    AS Login,
          							Owner.Password  AS Password,
          							Rolodex.Geolock AS Geofence
							 FROM   Owner
									LEFT JOIN Rol AS Rolodex ON Owner.Rol = Rolodex.ID
            		) AS Customer
            	WHERE   	Customer.ID = ?
            			OR 	Customer.Name = ?;",
            array(
            	$ID,
            	$Name
                  )
              );
        $Customer = (  empty( $ID )
                       &&  !empty( $Name )
                       &&  !$result
                    )    || (empty( $ID )
                       &&  empty( $Name )
                    )    ? array(
        	'ID'        => null,
        	'Name'      => null,
        	'Login'     => null,
        	'Password'  => null,
        	'Geofence'  => null,
        	'Type'      => null,
        	'Status'    => null,
        	'Website'   => null,
        	'Internet'  => null,
        	'Street'    => null,
        	'City'      => null,
        	'State'     => null,
        	'Zip'       => null,
        	'Latitude'  => null,
        	'Longitude' => null,
          'Phone'     =>  null,
          'Email'     =>  null,
        	'Rolodex'   => null,
          'Phone'     => null,
          'Email'     => null
        ) : sqlsrv_fetch_array($result);
//Binds $ID, $Name, $Customer and query values into the $result variable

        if( isset( $_POST ) && count( $_POST ) > 0 ){
          // if the $_Post is set and the count is null, select if available
        	$Customer[ 'Name' ] 		= isset( $_POST[ 'Name' ] ) 	 ? $_POST[ 'Name' ] 	 : $Customer[ 'Name' ];
          $Customer[ 'Contact' ] 	    = isset( $_POST[ 'Contact' ] ) ? $_POST[ 'Contact' ]     : $Customer[ 'Contact' ];
        	$Customer[ 'Phone' ] 		= isset( $_POST[ 'Phone' ] ) 	 ? $_POST[ 'Phone' ] 	 : $Customer[ 'Phone' ];
        	$Customer[ 'Email' ] 		= isset( $_POST[ 'Email' ] ) 	 ? $_POST[ 'Email' ] 	 : $Customer[ 'Email' ];
        	$Customer[ 'Login' ] 		= isset( $_POST[ 'Login' ] ) 	 ? $_POST[ 'Login' ] 	 : $Customer[ 'Login' ];
        	$Customer[ 'Password' ]     = isset( $_POST[ 'Password' ] )  ? $_POST[ 'Password' ]  : $Customer[ 'Password' ];
        	$Customer[ 'Geofence' ]     = isset( $_POST[ 'Geofence' ] )  ? $_POST[ 'Geofence' ]  : $Customer[ 'Geofence' ];
        	$Customer[ 'Type' ]         = isset( $_POST[ 'Type' ] ) 	 ? $_POST[ 'Type' ] 	 : $Customer[ 'Type' ];
        	$Customer[ 'Status' ] 	    = isset( $_POST[ 'Status' ] ) 	 ? $_POST[ 'Status' ] 	 : $Customer[ 'Status' ];
        	$Customer[ 'Website' ] 	    = isset( $_POST[ 'Website' ] ) 	 ? $_POST[ 'Website' ] 	 : $Customer[ 'Website' ];
        	$Customer[ 'Internet' ]     = isset( $_POST[ 'Internet' ] )  ? $_POST[ 'Internet' ]  : $Customer[ 'Internet' ];
        	$Customer[ 'Street' ] 	    = isset( $_POST[ 'Street' ] ) 	 ? $_POST[ 'Street' ] 	 : $Customer[ 'Street' ];
        	$Customer[ 'City' ] 		= isset( $_POST[ 'City' ] ) 	 ? $_POST[ 'City' ] 	 : $Customer[ 'City' ];
        	$Customer[ 'State' ] 		= isset( $_POST[ 'State' ] ) 	 ? $_POST[ 'State' ] 	 : $Customer[ 'State' ];
        	$Customer[ 'Zip' ] 			= isset( $_POST[ 'Zip' ] ) 		 ? $_POST[ 'Zip' ] 		 : $Customer[ 'Zip' ];
        	$Customer[ 'Latitude' ] 	= isset( $_POST[ 'Latitude' ] )  ? $_POST[ 'Latitude' ]  : $Customer[ 'Latitude' ];
        	$Customer[ 'Longitude' ] 	= isset( $_POST[ 'Longitude' ] ) ? $_POST[ 'Longitude' ] : $Customer[ 'Longitude' ];

        	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
        		$result = \singleton\database::getInstance( )->query(
        			null,
        			"	DECLARE @MAXID INT;
        				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Rol ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Rol ) END ;
        				INSERT INTO Rol(
    						  ID,
        					Type,
        					Name,
        					Website,
        					Address,
        					City,
        					State,
        					Zip,
        					Latt,
        					fLong,
        					Geolock
        				)
        				VALUES( @MAXID + 1 , 0, ?, ?, ?, ?, ?, ?, ?, ?, ? );
        				SELECT @MAXID + 1;",
        			array(
        				$Customer[ 'Name' ],
        				$Customer[ 'Website' ],
        				$Customer[ 'Street' ],
        				$Customer[ 'City' ],
        				$Customer[ 'State' ],
        				$Customer[ 'Zip' ],
        				$Customer[ 'Latitude' ],
        				$Customer[ 'Longitude' ],
        				isset( $Customer[ 'Geofence' ] ) ? $Customer[ 'Geofence' ] : 0
        			)
        		);
        		sqlsrv_next_result( $result );
    //Update query to fill values for $Customer and appends to $result for any updated colums
        		$Customer[ 'Rolodex' ] = sqlsrv_fetch_array( $result )[ 0 ];
// finds any result with the value of 0/ null
        		$result = \singleton\database::getInstance( )->query(
        			null,
        			"	DECLARE @MAXID INT;
        				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Owner ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Owner ) END ;
        				INSERT INTO Owner(
        					ID,
                  Status,
        					Locs,
        					Elevs,
        					Balance,
        					Type,
        					Billing,
        					Central,
        					Rol,
        					Internet,
        					TicketO,
        					TicketD,
        					Ledger,
        					Request,
        					Password,
        					fLogin,
        					Statement,
        					Approve,
        					InvoiceO,
        					Quote,
        					QuoteX,
        					Dispatch,
        					Service,
        					Pay,
        					Safety,
        					TicketEmail,
        					TFMID,
        					TFMSource,
        					QuoteEmail
        				)
        				VALUES ( @MAXID + 1, ?, 0, 0, 0, ?, 0, null, ?, ?, 0, 0, 0, 0, null, null, 0, 0, 0, 0, 0, 0, 0, 0, 0, null, '', '', null );
        				SELECT @MAXID + 1;",
        			array(
        				$Customer[ 'Status' ],
        				$Customer[ 'Type' ],
        				$Customer[ 'Rolodex' ],
        				$Customer[ 'Internet' ]
        			)
        		);
// query that inserts values into the $Customer [rolodex] variable datatable and appends it to the $result variable
        		sqlsrv_next_result( $result );
        		$Customer[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
// Checks the $Customer[ID] for any fields that are null, if none exit,
        		header( 'Location: customer.php?ID=' . $Customer[ 'ID' ] );
        		exit;
        	} else {
        		\singleton\database::getInstance( )->query(
	        		null,
	        		"	UPDATE 	Owner
	        			SET Owner.Status = ?,
	        					Owner.Internet = ?,
	        					Owner.fLogin = ?,
	        					Owner.Password = ?,
	        					Owner.Type = ?
	        			WHERE 	Owner.ID = ?;",
	        		array(
	        			$Customer[ 'Status' ],
	        			$Customer[ 'Internet' ],
	        			$Customer[ 'Login' ],
	        			$Customer[ 'Password' ],
	        			$Customer[ 'Type' ],
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
	        					Rol.fLong = ?,
                    Rol.Phone = ?,
                    Rol.EMail = ?

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
                $Customer[ 'Phone' ],
                $Customer[ 'Email' ],
	        			$Customer[ 'Rolodex' ]
	        		)
	        	);
        	}
        }
    // if any fields are 0/null, attempt to update said colums from owner/rol ID
?><!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php	require( bin_meta . 'index.php');?>
     <?php	require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<!-- required files from other locations, such as css, js, bootstrap and, Entity files  -->
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php'); ?>
        <div id="page-wrapper" class='content'>
        	<div class='card card-primary'><form action='customer.php?ID=<?php echo $Customer[ 'ID' ];?>' method='POST'>
                <input type='hidden' name='ID' value='<?php echo $Customer[ 'ID' ];?>' />
        		<div class='card-heading'>
              <div class='row g-0 px-3 py-2'>
                <div class='col-12 col-lg-6'>
                    <h5><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?><a href='customers.php?<?php
                      echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Customers' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Customers' ][ 0 ] : array( ) );
                    ?>'>Customers</a>: <span><?php
                      echo is_null( $Customer[ 'ID' ] )
                          ? 'New'
                          : '#' . $Customer[ 'ID' ];
                    ?></span></h5>
                </div>
                <div class='col-6 col-lg-3'>
                    <div class='row g-0'>
                      <div class='col-4'>
                        <button
                            class='form-control rounded'
                            onClick="document.location.href='customer.php';"
                          ><?php \singleton\fontawesome::getInstance( 1 )->Save( 1 );?><span class='desktop'> Save</span></button>
                      </div>
                      <div class='col-4'>
                          <button
                            class='form-control rounded'
                            onClick="document.location.href='customer.php?ID=<?php echo $User[ 'ID' ];?>';"
                          ><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'> Refresh</span></button>
                      </div>
                      <div class='col-4'>
                          <button
                            class='form-control rounded'
                            onClick="document.location.href='customer.php';"
                          ><?php \singleton\fontawesome::getInstance( 1 )->Add( 1 );?><span class='desktop'> New</span></button>
                      </div>
                  </div>
                </div>
                <div class='col-6 col-lg-3'>
                    <div class='row g-0'>
                      <div class='col-4'><button class='form-control rounded' onClick="document.location.href='customer.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) - 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Previous( 1 );?><span class='desktop'> Previous</span></button></div>
                      <div class='col-4'><button class='form-control rounded' onClick="document.location.href='customers.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';"><?php \singleton\fontawesome::getInstance( 1 )->Table( 1 );?><span class='desktop'> Table</span></button></div>
                      <div class='col-4'><button class='form-control rounded' onClick="document.location.href='customer.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) + 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Next( 1 );?><span class='desktop'> Next</span></button></div>
                    </div>
                </div>
              </div>
            </div>
        		<div class='card-body bg-dark text-white'>
					<div class='card-columns'>
						<?php if( !in_array( $Customer[ 'Latitude' ], array( null, 0 ) ) && !in_array( $Customer['Longitude' ], array( null, 0 ) ) ){ ?><div class='card card-primary my-3'>
							<div class='card-heading position-relative' style='z-index:1;'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Map</span></h5></div>
									<div class='col-2'>&nbsp;</div>
								</div>
							</div>
							<div id='customer_map' class='card-body p-0 bg-dark position-relative overflow-hidden' style='width:100%;height:350px;z-index:0;<?php echo isset( $_SESSION[ 'Cards' ][ 'Map' ] ) && $_SESSION[ 'Cards' ][ 'Map' ] == 0 ? 'display:none;' : null;?>'></div>
							<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyB05GymhObM_JJaRCC3F4WeFn3KxIOdwEU"></script>
								<script type="text/javascript">
					                var map;
					                function initialize() {
					                     map = new google.maps.Map(
					                        document.getElementById( 'customer_map' ),
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
						</div><?php }?>
						<div class='card card-primary my-3'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
									<div class='col-2'>&nbsp;</div>
								</div>
							</div>
						 	<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Name:</div>
									<div class='col-8'><input placeholder='Name' type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Customer['Name'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Type:</div>
									<div class='col-8'><select name='Type' class='form-control edit'>
										<option value=''>Select</option>
										<option value='General' <?php echo $Customer[ 'Type' ] == 'General' ? 'selected' : null;?>>General</option>
										<option value='Bank' <?php echo $Customer[ 'Type' ] == 'Bank' ? 'selected' : null;?>>Bank</option>
										<option value='Churches' <?php echo $Customer[ 'Type' ] == 'Churches' ? 'selected' : null;?>>Churches</option>
										<option value='Commercial' <?php echo $Customer[ 'Type' ] == 'Commercial' ? 'selected' : null;?>>Commercial</option>
										<option value='Hospitals' <?php echo $Customer[ 'Type' ] == 'Hospitals' ? 'selected' : null;?>>General</option>
										<option value='Property Manage' <?php echo $Customer[ 'Type' ] == 'Property Manage' ? 'selected' : null;?>>Property Manage</option>
										<option value='Restaraunts' <?php echo $Customer[ 'Type' ] == 'General' ? 'selected' : null;?>>Restaraunts</option>
										<option value='Schools' <?php echo $Customer[ 'Type' ] == 'Schools' ? 'selected' : null;?>>Schools</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
									<div class='col-8'><select name='Status' class='form-control edit <?php echo $Customer[ 'Status' ] == 1 ? 'bg-warning' : 'bg-success';?>'>
										<option value=''>Select</option>
										<option value='0' <?php echo $Customer[ 'Status' ] == 1 ? 'selected' : null;?>>Inactive</option>
										<option value='1' <?php echo $Customer[ 'Status' ] == 0 ? 'selected' : null;?>>Active</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Web(1);?> Website:</div>
									<div class='col-8'><input placeholder='https://www.website.com' type='text' class='form-control edit' name='Website' value='<?php echo strlen($Customer['Website']) > 0 ?  $Customer['Website'] : "&nbsp;";?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Address:</div>
									<div class='col-6'></div>
									<div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Street:</div>
									<div class='col-8'><input placeholder='Street' type='text' class='form-control edit' name='Street' value='<?php echo $Customer['Street'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>City:</div>
									<div class='col-8'><input placeholder='City' type='text' class='form-control edit' name='City' value='<?php echo $Customer['City'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>State:</div>
									<div class='col-8'><select class='form-control edit' name='State'>
										<option value=''>Select</option>
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
									<div class='col-8'><input placeholder='Zip' type='text' class='form-control edit' name='Zip' value='<?php echo $Customer['Zip'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Latitude:</div>
									<div class='col-8'><input placeholder='Latitude' type='text' class='form-control edit <?php echo $Customer[ 'Latitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Latitude' value='<?php echo $Customer['Latitude'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Longitude:</div>
									<div class='col-8'><input placeholder='Longitude' type='text' class='form-control edit <?php echo $Customer[ 'Longitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Longitude' value='<?php echo $Customer['Longitude'];?>' /></div>
								</div>
							</div>
		      </div>
          <!-- End of customer inforation card, ending with card-footer div class with a button for save  -->
            <div class='card card-primary my-3'>
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  	<div class='col-8'><h5><?php \singleton\fontawesome::getInstance( )->Users( 1 );?><span>Contacts</span></h5></div>
					<div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='contact.php?Name=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Add( 1 );?></button></div>
					<div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='contacts.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
              </div>
              <!-- Card hedding, that holds customer contacts, with a post call that gets customer contact information based on $Customer ID  -->
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Contacts' ] ) && $_SESSION[ 'Cards' ][ 'Contacts' ] == 0 ? "style='display:none;'" : null;?>>
                <div class='row'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->User( 1 );?> Contact:</div>
                  <div class='col-8'><input placeholder='Name' type='text' class='form-control edit' name='Name' value='<?php echo $Customer[ 'Contact' ];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Phone( 1 );?> Phone:</div>
                  <div class='col-8'><input placeholder='(XXX) XXX-XXXX' type='text' class='form-control edit' name='Phone' value='<?php echo $Customer[ 'Phone' ];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Email( 1 );?> Email:</div>
                  <div class='col-8'><input placeholder='email@domain.com' type='text' class='form-control edit' name='Email' value='<?php echo $Customer[ 'Email' ];?>' /></div>
                </div>
              </div>
            </div>
        	<!-- End of customer contact information card, ending with customer card-footer and a submit button-->
        	<div class='card card-primary my-3'>
				<div class='card-heading'>
					<div class='row g-0 px-3 py-2'>
						<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Privilege( 1 );?><span>Portal</span></h5></div>
						<div class='col-2'>&nbsp;</div>
					</div>
				</div>
  				<!-- Start of a new card Using a post method to fill data based on $Customer ID -->
				<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Portal' ] ) && $_SESSION[ 'Cards' ][ 'Portal' ] == 0 ? "style='display:none;'" : null;?>>
			 		<div class='row g-0'>
						<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Privilege(1);?> Login:</div>
						<div class='col-6'></div>
						<div class='col-2'>&nbsp;</div>
					</div>
					<div class='row g-0'>
						<div class='col-1'>&nbsp;</div>
			 			<div class='col-3'>Portal:</div>
			 			<div class='col-8'><select 
                            <?php echo check( privilege_execute, level_server, isset( $Privileges[ 'Customer' ] ) ? $Privileges[ 'Customer' ] : 0 ) ? null : 'disabled';?>
                            class='form-control edit' 
                            name='Internet' >
    			 				<option value=''>Select</option>
    			 				<option value='0' <?php echo $Customer[ 'Internet' ] == 0 ? 'selected' : null;?>>Disabled</option>
    			 				<option value='1' <?php echo $Customer[ 'Internet' ] == 1 ? 'selected' : null;?>>Enabled</option>
			 			</select></div>
			 		</div>
					<div class='row g-0' <?php echo $Customer[ 'Internet' ] == 0 ? "style='display:none;'" : null;?>>
						<div class='col-1'>&nbsp;</div>
			 			<div class='col-3'>Username:</div>
			 			<div class='col-8'><input type='text' class='form-control edit' name='Login' value='<?php echo $Customer[ 'Login' ];?>' /></div>
			 		</div>
			 		<div class='row g-0' <?php echo $Customer[ 'Internet' ] == 0 ? "style='display:none;'" : null;?>>
			 			<div class='col-1'>&nbsp;</div>
			 			<div class='col-3'>Password:</div>
			 			<div class='col-8'><input type='password' class='form-control edit' name='Login' value='<?php echo $Customer[ 'Login' ];?>' name='Password' value='<?php echo $Customer[ 'Password' ];?>' /></div>
			 		</div>
			 		<div class='row g-0'>
			 			<div class='col-1'>&nbsp;</div>
			 			<div class='col-3'>Geofence:</div>
			 			<div class='col-8'><select 
                            <?php echo check( privilege_execute, level_server, isset( $Privileges[ 'Customer' ] ) ? $Privileges[ 'Customer' ] : 0 ) ? null : 'disabled';?>
                            class='form-control edit' 
                            name='Geofence' >
    			 				<option value=''>Select</option>
    			 				<option value='0' <?php echo $Customer[ 'Geofence' ] == 0 ? 'selected' : null;?>>Disabled</option>
    			 				<option value='1' <?php echo $Customer[ 'Geofence' ] == 1 ? 'selected' : null;?>>Enabled</option>
			 			</select></div>
			 		</div>
				</div>
			</div>
			<div class='card card-primary my-3'>
				<div class='card-heading'>
					<div class='row g-0 px-3 py-2'>
						<div class='col-8'><h5><?php \singleton\fontawesome::getInstance( )->Location( 1 );?><span>Locations</span></h5></div>
						<div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='location.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Add( 1 );?></button></div>
						<div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='locations.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
					</div>
				</div>
				<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Locations' ] ) && $_SESSION[ 'Cards' ][ 'Locations' ] == 0 ? "style='display:none;'" : null;?>>
					<?php
						$result = \singleton\database::getInstance( )->query(
							null,
							"	SELECT 		Count( Location.ID ) AS Count,
											    Location.Maint AS Maintenance
								FROM   		Loc AS Location
								WHERE  		Location.Owner = ?
								GROUP BY 	Location.Maint
								ORDER BY 	Location.Maint DESC;",
							array(
								$Customer[ 'ID' ]
							)
						);
                  		// new card with sql select, getting Location ID, and Location Maintenance records and storing them in the $Customer[ID] array
						$Locations = array( );
                  		// creats a new Variable called $Locations which is a blank array
						if( $result ){ while( $row = sqlsrv_fetch_array( $result ) ){ $Locations[ $row[ 'Maintenance' ] ] = $row[ 'Count' ]; } }
                  		// not sure how this works exactly
					?><div class='row g-0'>
						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Maintained</div>
						    <div class='col-6'>&nbsp;</div>
							<div class='col-2'>&nbsp;</div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Yes</div>
						    <div class='col-6'><input class='form-control' type='text' readonly name='Locations' value='<?php
								echo isset( $Locations[ 1 ] ) ? $Locations[ 1 ] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>No</div>
						    <div class='col-6'><input class='form-control' type='text' readonly name='Locations' value='<?php
								echo isset( $Locations[ 0 ] ) ? $Locations[ 0 ] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
				</div>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-8'><h5><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?><span>Units</span></h5></div>
							<div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='unit.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Add( 1 );?></button></div>
							<div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
									$Customer[ 'ID' ]
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
								;",array($Customer[ 'ID' ]));
                    			//Selects the unit.ID as counts from Elev and adds it to $Customer[ID]
								echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Escalators</div>
						    <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
								$r = \singleton\database::getInstance( )->query(null,
              						" SELECT 	Count( Unit.ID ) AS Units
									  FROM   	Elev AS Unit
										   	LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
									  WHERE  		Location.Owner = ?
									  AND Unit.Type = 'Escalator'
								;",array($Customer[ 'ID' ]));
								echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>&Type=Escalator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'>Other</div>
						    <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
								$r = \singleton\database::getInstance( )->query(
									null,
									" 	SELECT 	Count( Unit.ID ) AS Units
										FROM   	Elev AS Unit
											   	LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
										WHERE  		Location.Owner = ?
												AND Unit.Type NOT IN ( 'Elevator', 'Escalator' );",
									array(
										$Customer[ 'ID' ]
									)
								);
								echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
								?>
							' /></div>
							<div class='col-2'><button class='h-100 w-100' readonly onClick="document.location.href='units.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
				</div>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-8'><h5><?php \singleton\fontawesome::getInstance( )->Job( 1 );?><span>Jobs</span></h5></div>
							<div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='job.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Add( 1 );?></button></div>
							<div class='col-2'><button type='button' class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
								;",array($Customer[ 'ID' ]));
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
								;",array($Customer[ 'ID' ]));
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
								;",array($Customer[ 'ID' ]));
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
							<div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='tickets.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
										$Customer[ 'ID' ]
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
										$Customer[ 'ID' ]
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
										$Customer[ 'ID' ]
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
										$Customer[ 'ID' ]
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
										$Customer[ 'ID' ]
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
										$Customer[ 'ID' ]
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
							<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Violation( 1 );?><span>Violations</span></h5></div>
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
								;",array($Customer[ 'ID' ]));
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
								;",array($Customer[ 'ID' ]));
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
								;",array($Customer[ 'ID' ]));
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
								;",array($Customer[ 'ID' ]));
								echo $r ? sqlsrv_fetch_array($r)['Proposals'] : 0;
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Customer[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
				</div>
				<div class='card card-primary my-3'>
					<div class='card-heading'>
						<div class='row g-0 px-3 py-2'>
							<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Invoices( 1 );?><span>Invoices</span></h5></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='invoices.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
					</div>
					<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
						<div class='row g-0'>
						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Invoices</div>
						    <div class='col-6'>&nbsp;</div>
							<div class='col-2'>&nbsp;</div>
						</div>
						<?php if(isset($Privileges['Invoice']) ) {?>
						<div class='row g-0'>
							<div class='col-1'>&nbsp;</div>
						    <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Open</div>
						    <div class='col-6'><input class='form-control' type='text' readonly name='Collections' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT Count( OpenAR.Ref ) AS Count
									FROM   OpenAR
										   LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
									WHERE  Location.Owner = ?
								;",array($Customer[ 'ID' ]));
								$Count = $r ? sqlsrv_fetch_array($r)['Count'] : 0;
								echo $Count
							?>' /></div>
							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
						</div>
						<?php }?>
						<?php if(isset($Privileges['Invoice']) ) {?>
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

								;",array($Customer[ 'ID' ]));
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
						<?php if(isset($Privileges['Collection']) && $Privileges['Collection']['Customer'] >= 4) {?>
						<div class='row g-0'>
						    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Balance</div>
						    <div class='col-6'><input class='form-control' type='text' readonly name='Balance' value='<?php
								$r = \singleton\database::getInstance( )->query(null,"
									SELECT Sum( OpenAR.Balance ) AS Balance
									FROM   OpenAR
										   LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
									WHERE  Location.Owner = ?
								;",array($Customer[ 'ID' ]));
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
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($Customer[ 'ID' ]) || !is_numeric($Customer[ 'ID' ])) ? "s.php" : ".php?ID={$Customer[ 'ID' ]}";?>";</script></head></html><?php }?>
