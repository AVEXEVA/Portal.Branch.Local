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
		  	$_SESSION[ 'Connection' ][ 'User' ],
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
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Division' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Division' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'division.php'
        )
      );
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
              				<div class='col-6'><h5><?php \singleton\fontawesome::getInstance( )->Location( 1 );?><a href='locations.php?<?php echo isset( $_SESSION[ 'Tables' ][ 'Location' ][ 0 ] ) ? http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Locations' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Locations' ][ 0 ] : array( ) ) : null;?>'>Location</a>: <span><?php echo is_null( $Division[ 'ID' ] ) ? 'New' : $Division[ 'Name' ];?></span></h5></div>
              				<div class='col-2'></div>
              				<div class='col-2'>
              					<div class='row g-0'>
              						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='location.php';">Create</button></div>
              						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='location.php?ID=<?php echo $Division[ 'ID' ];?>';">Refresh</button></div>
              					</div>
              				</div>
              				<div class='col-2'>
              					<div class='row g-0'>
              						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='location.php?ID=<?php echo !is_null( $Division[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Locations' ], true )[ array_search( $Division[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Locations' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
              						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='locations.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Locations' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Locations' ][ 0 ] : array( ) );?>';">Table</button></div>
              						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='location.php?ID=<?php echo !is_null( $Division[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Locations' ], true )[ array_search( $Division[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Locations' ], true ) ) + 1 ] : null;?>';">Next</button></div>
              					</div>
              				</div>
              			</div>
              		</div>
      				<div class='card-body bg-dark text-white'>
      				<div class='card-columns'>
      					<?php if( !in_array( $Division[ 'Latitude' ], array( null, 0 ) ) && !in_array( $Division['Longitude' ], array( null, 0 ) ) ){
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
      					                          center: new google.maps.LatLng( <?php echo $Division[ 'Latitude' ];?>, <?php echo $Division[ 'Longitude' ];?> ),
      					                          mapTypeId: google.maps.MapTypeId.ROADMAP
      					                        }
      					                    );
      					                    var markers = [];
      					                    markers[0] = new google.maps.Marker({
      					                        position: {
      					                            lat:<?php echo $Division['Latitude'];?>,
      					                            lng:<?php echo $Division['Longitude'];?>
      					                        },
      					                        map: map,
      					                        title: '<?php echo $Division[ 'Name' ];?>'
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
      					<div class='card card-primary my-3'>
      						<div class='card-heading'>
      							<div class='row g-0 px-3 py-2'>
      								<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
      								<div class='col-2'>&nbsp;</div>
      							</div>
      						</div>
      						<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
      							<form action='location.php?ID=<?php echo $Division[ 'ID' ];?>' method='POST'>
      								<input type='hidden' name='ID' value='<?php echo isset( $Division[ 'ID' ] ) ? $Division[ 'ID' ] : null;?>' />
      				                <div class='row g-0'>
      									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?>Name:</div>
      									<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Division['Name'];?>' /></div>
      								</div>
      								<div class='row g-0'>
      									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Customer:</div>
      								    <div class='col-6'>
      								    	<input type='text' autocomplete='off' class='form-control edit' name='Customer' value='<?php echo $Division[ 'Customer_Name' ];?>' />
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
      								    <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='customer.php?ID=<?php echo $Division[ 'Customer_ID' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
      								</div>
      				         <div class='row g-0'>
      									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
      									<div class='col-8'><select name='Status' class='form-control edit'>
      										<option value=''>Select</option>
      										<option value='0' <?php echo $Division[ 'Status' ] == 0 ? 'selected' : null;?>>Active</option>
      										<option value='1' <?php echo $Division[ 'Status' ] == 1 ? 'selected' : null;?>>Inactive</option>
      									</select></div>
      							</div>
                    <div class='row g-0'>
                      <div class='col-1'>&nbsp;</div>
                      <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Territory(1);?> Territory:</div>
                      <div class='col-8'><input type='text' class='form-control edit' name='Territory' value='<?php echo $Division['Territory_ID'];?>' /></div>
                    </div>
      				        <div class='row g-0'>
      									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Address:</div>
      									<div class='col-6'></div>
      									<div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?Location=<?php echo $Division[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
      								</div>
      								<div class='row g-0'>
      									<div class='col-1'>&nbsp;</div>
      									<div class='col-3 border-bottom border-white my-auto'>Street:</div>
      									<div class='col-8'><input type='text' class='form-control edit' name='Street' value='<?php echo $Division['Street'];?>' /></div>
      								</div>
      								<div class='row g-0'>
      									<div class='col-1'>&nbsp;</div>
      									<div class='col-3 border-bottom border-white my-auto'>City:</div>
      									<div class='col-8'><input type='text' class='form-control edit' name='City' value='<?php echo $Division['City'];?>' /></div>
      								</div>
      								<div class='row g-0'>
      									<div class='col-1'>&nbsp;</div>
      									<div class='col-3 border-bottom border-white my-auto'>State:</div>
      									<div class='col-8'><select class='form-control edit' name='State'>
      										<option <?php echo $Division[ 'State' ] == 'AL' ? 'selected' : null;?> value='AL'>Alabama</option>
      										<option <?php echo $Division[ 'State' ] == 'AK' ? 'selected' : null;?> value='AK'>Alaska</option>
      										<option <?php echo $Division[ 'State' ] == 'AZ' ? 'selected' : null;?> value='AZ'>Arizona</option>
      										<option <?php echo $Division[ 'State' ] == 'AR' ? 'selected' : null;?> value='AR'>Arkansas</option>
      										<option <?php echo $Division[ 'State' ] == 'CA' ? 'selected' : null;?> value='CA'>California</option>
      										<option <?php echo $Division[ 'State' ] == 'CO' ? 'selected' : null;?> value='CO'>Colorado</option>
      										<option <?php echo $Division[ 'State' ] == 'CT' ? 'selected' : null;?> value='CT'>Connecticut</option>
      										<option <?php echo $Division[ 'State' ] == 'DE' ? 'selected' : null;?> value='DE'>Delaware</option>
      										<option <?php echo $Division[ 'State' ] == 'DC' ? 'selected' : null;?> value='DC'>District Of Columbia</option>
      										<option <?php echo $Division[ 'State' ] == 'FL' ? 'selected' : null;?> value='FL'>Florida</option>
      										<option <?php echo $Division[ 'State' ] == 'GA' ? 'selected' : null;?> value='GA'>Georgia</option>
      										<option <?php echo $Division[ 'State' ] == 'HI' ? 'selected' : null;?> value='HI'>Hawaii</option>
      										<option <?php echo $Division[ 'State' ] == 'ID' ? 'selected' : null;?> value='ID'>Idaho</option>
      										<option <?php echo $Division[ 'State' ] == 'IL' ? 'selected' : null;?> value='IL'>Illinois</option>
      										<option <?php echo $Division[ 'State' ] == 'IN' ? 'selected' : null;?> value='IN'>Indiana</option>
      										<option <?php echo $Division[ 'State' ] == 'IA' ? 'selected' : null;?> value='IA'>Iowa</option>
      										<option <?php echo $Division[ 'State' ] == 'KS' ? 'selected' : null;?> value='KS'>Kansas</option>
      										<option <?php echo $Division[ 'State' ] == 'KY' ? 'selected' : null;?> value='KY'>Kentucky</option>
      										<option <?php echo $Division[ 'State' ] == 'LA' ? 'selected' : null;?> value='LA'>Louisiana</option>
      										<option <?php echo $Division[ 'State' ] == 'ME' ? 'selected' : null;?> value='ME'>Maine</option>
      										<option <?php echo $Division[ 'State' ] == 'MD' ? 'selected' : null;?> value='MD'>Maryland</option>
      										<option <?php echo $Division[ 'State' ] == 'MA' ? 'selected' : null;?> value='MA'>Massachusetts</option>
      										<option <?php echo $Division[ 'State' ] == 'MI' ? 'selected' : null;?> value='MI'>Michigan</option>
      										<option <?php echo $Division[ 'State' ] == 'MN' ? 'selected' : null;?> value='MN'>Minnesota</option>
      										<option <?php echo $Division[ 'State' ] == 'MS' ? 'selected' : null;?> value='MS'>Mississippi</option>
      										<option <?php echo $Division[ 'State' ] == 'MO' ? 'selected' : null;?> value='MO'>Missouri</option>
      										<option <?php echo $Division[ 'State' ] == 'MT' ? 'selected' : null;?> value='MT'>Montana</option>
      										<option <?php echo $Division[ 'State' ] == 'NE' ? 'selected' : null;?> value='NE'>Nebraska</option>
      										<option <?php echo $Division[ 'State' ] == 'NV' ? 'selected' : null;?> value='NV'>Nevada</option>
      										<option <?php echo $Division[ 'State' ] == 'NH' ? 'selected' : null;?> value='NH'>New Hampshire</option>
      										<option <?php echo $Division[ 'State' ] == 'NJ' ? 'selected' : null;?> value='NJ'>New Jersey</option>
      										<option <?php echo $Division[ 'State' ] == 'NM' ? 'selected' : null;?> value='NM'>New Mexico</option>
      										<option <?php echo $Division[ 'State' ] == 'NY' ? 'selected' : null;?> value='NY'>New York</option>
      										<option <?php echo $Division[ 'State' ] == 'NC' ? 'selected' : null;?> value='NC'>North Carolina</option>
      										<option <?php echo $Division[ 'State' ] == 'ND' ? 'selected' : null;?> value='ND'>North Dakota</option>
      										<option <?php echo $Division[ 'State' ] == 'OH' ? 'selected' : null;?> value='OH'>Ohio</option>
      										<option <?php echo $Division[ 'State' ] == 'OK' ? 'selected' : null;?> value='OK'>Oklahoma</option>
      										<option <?php echo $Division[ 'State' ] == 'OR' ? 'selected' : null;?> value='OR'>Oregon</option>
      										<option <?php echo $Division[ 'State' ] == 'PA' ? 'selected' : null;?> value='PA'>Pennsylvania</option>
      										<option <?php echo $Division[ 'State' ] == 'RI' ? 'selected' : null;?> value='RI'>Rhode Island</option>
      										<option <?php echo $Division[ 'State' ] == 'SC' ? 'selected' : null;?> value='SC'>South Carolina</option>
      										<option <?php echo $Division[ 'State' ] == 'SD' ? 'selected' : null;?> value='SD'>South Dakota</option>
      										<option <?php echo $Division[ 'State' ] == 'TN' ? 'selected' : null;?> value='TN'>Tennessee</option>
      										<option <?php echo $Division[ 'State' ] == 'TX' ? 'selected' : null;?> value='TX'>Texas</option>
      										<option <?php echo $Division[ 'State' ] == 'UT' ? 'selected' : null;?> value='UT'>Utah</option>
      										<option <?php echo $Division[ 'State' ] == 'VT' ? 'selected' : null;?> value='VT'>Vermont</option>
      										<option <?php echo $Division[ 'State' ] == 'VA' ? 'selected' : null;?> value='VA'>Virginia</option>
      										<option <?php echo $Division[ 'State' ] == 'WA' ? 'selected' : null;?> value='WA'>Washington</option>
      										<option <?php echo $Division[ 'State' ] == 'WV' ? 'selected' : null;?> value='WV'>West Virginia</option>
      										<option <?php echo $Division[ 'State' ] == 'WI' ? 'selected' : null;?> value='WI'>Wisconsin</option>
      										<option <?php echo $Division[ 'State' ] == 'WY' ? 'selected' : null;?> value='WY'>Wyoming</option>
      									</select></div>
      								</div>
      								<div class='row g-0'>
      									<div class='col-1'>&nbsp;</div>
      									<div class='col-3 border-bottom border-white my-auto'>Zip:</div>
      									<div class='col-8'><input type='text' class='form-control edit' name='Zip' value='<?php echo $Division['Zip'];?>' /></div>
      								</div>
      								<div class='row g-0'>
      									<div class='col-1'>&nbsp;</div>
      									<div class='col-3 border-bottom border-white my-auto'>Latitude:</div>
      									<div class='col-8'><input type='text' class='form-control edit' name='Latitude' value='<?php echo $Division['Latitude'];?>' /></div>
      								</div>
      								<div class='row g-0'>
      									<div class='col-1'>&nbsp;</div>
      									<div class='col-3 border-bottom border-white my-auto'>Longitude:</div>
      									<div class='col-8'><input type='text' class='form-control edit' name='Longitude' value='<?php echo $Division['Longitude'];?>' /></div>
      								</div>
      				           </form>
      					    </div>
      					</div>
      					<div class='card card-primary my-3'>
      						<div class='card-heading'>
      							<div class='row g-0 px-3 py-2'>
      								<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Maintenance( 1 );?><span>Maintenance</span></h5></div>
      								<div class='col-2 p-1 text-center rounded bg-<?php echo $Division[ 'Maintenance' ] == 1 ? 'success' : 'warning';?>'><?php echo $Division[ 'Maintenance' ] == 1 ? 'Active' : 'Inactive';?></div>
      							</div>
      						</div>
      						<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
      							<form action='location.php?ID=<?php echo $Division[ 'ID' ];?>' method='POST'>
      								<input type='hidden' name='ID' value='<?php echo isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null;?>' />
      								<div class='row g-0'>
      									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Maintenance:</div>
      								    <div class='col-6'>
      								    	<select class='form-control edit' name='Maintenance'>
      								    		<option value=''>Select</option>
      								    		<option value='0' <?php echo $Division[ 'Maintenance' ] == 0 ? 'selected' : null;?>>Disabled</option>
      								    		<option value='1' <?php echo $Division[ 'Maintenance' ] == 1 ? 'selected' : null;?>>Enabled</option>
      								    	</select>
      								    </div>
      								    <div class='col-2'>&nbsp;</div>
      								</div>
      								<div class='row g-0'>
      									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Division(1);?> Division:</div>
      								    <div class='col-6'>
      								    	<input type='hidden' disabled name='Division' value='<?php echo $Division[ 'Division_ID' ];?>' />
      								    	<input type='text' class='form-control edit' name='Division_Autocomplete' value='<?php echo $Division[ 'Division_Name' ];?>' />
      								    </div>
      								    <div class='col-2'><button class='h-100 w-100' type='button' <?php
      								    	if( in_array( $Division[ 'Route_ID' ], array( null, 0, '', ' ') ) ){
      								    		echo "onClick=\"document.location.href='divisions.php';\"";
      								    	} else {
      								    		echo "onClick=\"document.location.href='division.php?ID=" . $Division[ 'Division_ID' ] . "';\"";
      								    	}
      								    ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
      								</div>
      								<div class='row g-0'>
      									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Route(1);?> Route:</div>
      								    <div class='col-6'>
      								    	<input type='text' autocomplete='off' class='form-control edit' name='Route' value='<?php echo $Division[ 'Route_Name' ];?>' />
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
      								    	if( in_array( $Division[ 'Route_ID' ], array( null, 0, '', ' ') ) ){
      								    		echo "onClick=\"document.location.href='routes.php';\"";
      								    	} else {
      								    		echo "onClick=\"document.location.href='route.php?ID=" . $Division[ 'Route_ID' ] . "';\"";
      								    	}
      								    ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
      								</div>
      							</form>
      						</div>
      					</div>
                <div class='card card-primary my-3'>
                  <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                      <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?><span>Tickets</span></h5></div>
                      <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Open</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Assigned</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>En Route</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>On Site</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=3';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Review</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=6';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                            $Division[ 'ID' ]
                          )
                        );
                      ?><div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Complete</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                        echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Location=<?php echo $Division[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                  </div>
                </div>
                <div class='card card-primary my-3'>
                  <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                      <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?><span>Units</span></h5></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Location=<?php echo $Division[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                          $Division[ 'ID' ]
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
                        ;",array($Division[ 'ID' ]));
                        //Selects the unit.ID as counts from Elev and adds it to $Division[ID]
                        echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Location=<?php echo $Division[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                        ;",array($Division[ 'ID' ]));
                        echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Location=<?php echo $Division[ 'Name' ];?>&Type=Escalator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <div class='row g-0'>
                      <div class='col-1'>&nbsp;</div>
                        <div class='col-3 border-bottom border-white my-auto'>Other</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
                        $r = \singleton\database::getInstance( )->query(null,
                          " SELECT 	Count( Unit.ID ) AS Units
                            FROM   	Elev AS Unit
                                LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                            WHERE  		Location.Owner = ?
                              AND Unit.Type NOT IN ( 'Elevator', 'Escalator' )
                        ;",array($Division[ 'ID' ]));
                        echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' readonly onClick="document.location.href='units.php?Location=<?php echo $Division[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                  </div>
                </div>
              </div>
            </div>
        </div>
    </body>
</html>
<?php }
} else {?><script>document.location.href='../login.php?Forward=divisions.php&<?php echo http_build_query( $_GET );?>';</script><?php }?>
