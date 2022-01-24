<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  	//Connection
    $result = \singleton\database::getInstance( )->query(
      	'Portal',
      	" 	SELECT  [Connection].[ID]
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
    	" 	SELECT  Emp.fFirst  AS First_Name,
              		Emp.Last    AS Last_Name,
              		Emp.fFirst + ' ' + Emp.Last AS Name,
              		Emp.Title AS Title,
            	  	Emp.Field   AS Field
      		FROM  	Emp
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
	      	FROM    dbo.[Privilege]
	      	WHERE   Privilege.[User] = ?;",
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
    if(   !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Map' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Map' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
    	$Map = array(
    		'Territory_ID' 		 => isset( $_GET[ 'Territory_ID' ] ) 	  ? $_GET[ 'Territory_ID' ] 		: null,
    		'Territory_Name' 	 => isset( $_GET[ 'Territory_Name' ] ) 	? $_GET[ 'Territory_Name' ] 	: null,
    		'Division_ID' 		 => isset( $_GET[ 'Division_ID' ] ) 		? $_GET[ 'Division_ID' ] 		  : null,
    		'Division_Name' 	 => isset( $_GET[ 'Division_Name' ] ) 	? $_GET[ 'Division_Name' ] 		: null,
    		'Route_ID' 			   => isset( $_GET[ 'Route_ID' ] ) 		    ? $_GET[ 'Route_ID' ] 			  : null,
    		'Route_Name' 		   => isset( $_GET[ 'Route_Name' ] ) 		  ? $_GET[ 'Route_Name' ] 		  : null,
    		'Customer_ID' 		 => isset( $_GET[ 'Customer_ID' ] ) 		? $_GET[ 'Customer_ID' ] 		  : null,
    		'Customer_Name' 	 => isset( $_GET[ 'Customer_Name' ] ) 	? $_GET[ 'Customer_Name' ] 		: null,
    		'Location_ID' 		 => isset( $_GET[ 'Location_ID' ] ) 		? $_GET[ 'Location_ID' ] 		  : null,
    		'Location_Name' 	 => isset( $_GET[ 'Location_Name' ] ) 	? $_GET[ 'Location_Name' ] 		: null,
    		'Supervisor_ID' 	 => isset( $_GET[ 'Supervisor_ID' ] ) 	? $_GET[ 'Supervisor_ID' ] 		: null,
    		'Supervisor_Name'  => isset( $_GET[ 'Supervisor_Name' ] ) ? $_GET[ 'Supervisor_Name' ] 	: null,
        'Tickets_Active' 	 => isset( $_GET[ 'Tickets_Active' ] ) 	? $_GET[ 'Tickets_Active' ] 	: null,
    		'User_ID' 			   => isset( $_GET[ 'User_ID' ] ) 			  ? $_GET[ 'User_ID' ] 			    : null,
    		'User_Name' 		   => isset( $_GET[ 'User_Name' ] ) 		  ? $_GET[ 'User_Name' ] 		   	: null
    	);
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Nouveau Elevator Portal</title>
    <?php
    	$_GET[ 'Bootstrap' ] = '5.1';
    	require( bin_meta . 'index.php');
    	require( bin_css . 'index.php');
    	require( bin_js . 'index.php');
    ?>
</head>
<body>
  	<div id="wrapper">
    	<?php require( bin_php . 'element/navigation.php');?>
    	<div id="page-wrapper" class='content' >
            <div class="card card-primary text-white"><form action='map.php' method='GET'>
        		<div class='card-heading'>
					<div class='row g-0 px-3 py-2'>
						<div class='col-12 col-lg-6'>
							<h5><?php \singleton\fontawesome::getInstance( )->Map( 1 );?> Map</h5>
						</div>
						<div class='offset-lg-3 col-6 col-lg-3'>
							<div class='row g-0'>
								<div class='offset-6 col-4'>
									<button
										class='form-control rounded'
										type='submit'
									><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'>Refresh</span></button>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class='card-body bg-dark'>
					<div class='row g-0'>
						<div class='col-4'>
							<div class='row g-0'>
								<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Locations:</div>
								<div class='col-8'>&nbsp;</div>
							</div>
							<?php
								\singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Territory', 'Territories', $Map[ 'Territory_ID' ], $Map[ 'Territory_Name' ] );
								\singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Division', 'Divisions', $Map[ 'Division_ID' ], $Map[ 'Division_Name' ] );
								\singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Route', 'Routes', $Map[ 'Route_ID' ], $Map[ 'Route_Name' ] );
								\singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Customer', 'Customers', $Map[ 'Customer_ID' ], $Map[ 'Customer_Name' ] );
								\singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Location', 'Locations', $Map[ 'Location_ID' ], $Map[ 'Location_Name' ] );
							?>
						</div>
						<div class='col-4'>
							<div class='row g-0'>
								<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Users( 1 );?> Users:</div>
								<div class='col-8'>&nbsp;</div>
							</div>
							<?php
								\singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Supervisor', 'Supervisors', $Map[ 'Supervisor_ID' ], $Map[ 'Supervisor_Name' ] );
								\singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'User', 'Users', $Map[ 'User_ID' ], $Map[ 'User_Name' ] );
							?>
						</div>
						<div class='col-4'>
							<div class='row g-0'>
								<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?> Tickets:</div>
								<div class='col-8'>&nbsp;</div>
							</div>
							<?php
								$result = \singleton\database::getInstance( )->query(
									null,
									"	SELECT 	JobType.ID,
												JobType.Type AS Name
										FROM 	JobType;"
								);
								$Types = array( );
                $Active = array(  );
                $Assigned = array(  );
								if( $result ){ while( $row = sqlsrv_fetch_array( $result ) ){ $Types[ $row[ 'ID' ] ] = $row[ 'Name' ]; }}
								\singleton\bootstrap::getInstance( )->card_row_form_select( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null, $Types );
                \singleton\bootstrap::getInstance( )->card_row_form_select(  'Active',  isset( $_GET['Active'] ) ? $_GET[  'Active'  ] : null, $Active );
                \singleton\bootstrap::getInstance( )->card_row_form_select(  'Assigned',  isset( $_GET['Assigned'] ) ? $_GET[  'Assigned'  ] : null, $Assigned );
							?>
						</div>
					</div>
				</div>
				<div class='card-body'>
					<div id='map' style='height:600px;'>&nbsp;</div>
				</div>
			</form></div>
		</div>
	</div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
	<script type="text/javascript">
		//Statics / Helpers
		function circle( number ) {
			var fill = '#fff';
			switch( number ){
				case 0 :  fill = '#fff';break;
				case 1 :  fill = '#eee';break;
				case 2 :  fill = '#ddd';break;
				case 3 :  fill = '#ccc';break;
				case 4 :  fill = '#bbb';break;
				case 5 :  fill = '#aaa';break;
				case 6 :  fill = '#999';break;
				case 7 :  fill = '#888';break;
				case 8 :  fill = '#777';break;
				case 9 :  fill = '#666';break;
				case 10 : fill = '#555';break;
				case 11 : fill = '#444';break;
				case 12 : fill = '#333';break;
				case 13 : fill = '#222';break;
				case 14 : fill = '#111';break;
				default : fill = '#000';break;
			}
		  	// inline your SVG image with number variable
		  	var svg = '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="25" height="25" viewBox="0 0 25 25"> <defs> <rect id="path-1" width="25" height="25"/> <mask id="mask-2" width="25" height="25" x="0" y="0" fill="white"> <use xlink:href="#path-1"/> </mask> </defs> <g id="Page-1" fill="none" fill-rule="evenodd"> <g id="Phone-Portrait---320" transform="translate(-209 -51)"> <g id="Group" transform="translate(209 51)"> <use id="Rectangle" fill="' + fill + '" stroke="#F44336" 		stroke-width="4" mask="url(#mask-2)" xlink:href="#path-1"/> <text id="1" fill="#20539F" font-family="NunitoSans-ExtraBold, Nunito Sans" font-size="18" font-weight="600" letter-spacing=".104" text-anchor="middle" x="50%" y="16">' + number + '</text> </g> </g> </g> </svg>';
		  	// use SVG without base64 see: https://css-tricks.com/probably-dont-base64-svg/
		  	return 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(svg);
		}
		function pin( icon, color) {
			switch( icon ){
				case 'pin' :
					return {
				        path         : 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -2,-30 a 2,2 0 1,1 4,0 2,2 0 1,1 -4,0',
				        fillColor    : color,
				        fillOpacity  : 1,
				        strokeColor  : '#000',
				        strokeWeight : 2,
				        scale        : 1
			   		};
			   	default :
			   		return symbol( 'pin', color );
			};
		}
		function switchSVG( Entity, number ){
			switch( Entity ){
				case 'Location': return circle( number );
				case 'Employee': return pin( 'pin', 'green' );
				case 'Ticket': return pin( 'pin', 'black' );
			}
		}
		//Map
		var _map_ 	= null;
		var markers = new Array( );
		var getting = {
			'Locations' : 0,
			'Users'     : 0,
			'Tickets'   : 0
		};
		var draw_number = 0;
		function initialize( ){
			_map_ = new google.maps.Map(
				document.getElementById( 'map' ),
				{
			  		zoom : <?php echo isset( $_GET[ 'Latitude' ], $_GET[ 'Longitude' ] ) ? 18 : 10;?>,
			  		center: {
				  		lat : <?php echo isset( $_GET[ 'Latitude' ] )  ? $_GET['Latitude']  :  40.7831;?>,
				  		lng : <?php echo isset( $_GET[ 'Longitude' ] ) ? $_GET['Longitude'] : -73.9712;?>
				  	}
				}
			);
			gets( );
		}
		function get( entities, payload ){
			$.ajax({
		      	url    : 'bin/php/get/' + entities + '.php',
		      	method : 'GET',
		      	data : payload,
		      	success:function( json ){
		      		var rows = JSON.parse( json )[ 'aaData' ];
		      		for( index in rows){ mark( entities, rows[ index ] ); }
		      		getting[ entities ] = 0;
		      	}
		    });
		}
		function mark( entities, row ){
			//if( row.Tickets_Active == 0 ){ return; }
			if( markers[ row.Entity + '_' + row.ID ] ){
  				markers[ row.Entity + '_' + row.ID ].setPosition(
  					new google.maps.LatLng(
  						row.Latitude,
  						row.Longitude
  					)
  				);
  				markers[ row.Entity + '_' + row.ID ].setTitle( row.Name );
  				//markers[ row.Entity + '_' + row.ID ].setIcon( symbol( row.Tickets_Active ) );
  			} else {
  				markers[ row.Entity + '_' + row.ID ] = new google.maps.Marker({
  					id    	 : row.Entity + '_' + row.ID,
  					map      : _map_,
  					position : new google.maps.LatLng(
		  						row.Latitude,
		  						row.Longitude
  					),
  					title    : row.Name,
  					icon     : switchSVG( row.Entity, row.Tickets_Active )
  				});
          markers[ row.Entity + '_' + row.ID ].addListener(
    				'click',
    				function(){
              $('div.gm-style-iw.gm-style-iw-c[roll="dialog"]' ).remove();
    					switch( row.Entity ){
    						case 'Location':
  	  						new google.maps.InfoWindow({
  			  						content : 	"<div class='card card-primary text-black' style='width:200px;'>" +
  			  										"<div class='card-heading text-center border-bottom p-1'><h6>" + row.Name + "</h6></div>" +
  			  										"<div class='card-body' style='font-size:14px;'>" +
                                "<div class='card card-primary border-0'>" +
                                  "<div class='card-heading text-center border-bottom border-top p-0'><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?> Units:</div>" +
                                  "<div class='card-body '>" +
                                    "<div class='row g-0 '>" +
                                      "<div class='col-6'>All:</div>" +
                                      "<div class='col-6'><a href='units.php?Location_ID=" + row.ID + "&Location_Name=" + row.Name + "'>" + row.Units + " unit(s)</a></div>" +
                                    "</div>" +
                                  "</div>" +
                                "</div>" +
                                "<div class='card card-primary border-0'>" +
                                  "<div class='card-heading text-center border-bottom border-top p-0'><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?> Tickets:</div>" +
                                  "<div class='card-body '>" +
                                    "<div class='row g-0 '>" +
                                      "<div class='col-6'>Assigned:</div>" +
                                      "<div class='col-6'><a href='tickets.php?Location_ID=" + row.ID + "&Location_Name=" + row.Name + "&Status=1'>" + row.Tickets_Assigned + " ticket(s)</a></div>" +
                                    "</div>" +
                                    "<div class='row g-0' >" +
                                      "<div class='col-6'>Active:</div>" +
                                      "<div class='col-6'><a href='tickets.php?Location_ID=" + row.ID + "&Location_Name=" + row.Name + "&Status=3'>" + row.Tickets_Active + " ticket(s)</a></div>" +
                                    "</div>" +
                                  "</div>" +
                                "</div>" +
                                "<div class='card card-primary border-0'>" +
                                  "<div class='card-heading text-center border-bottom border-top p-0'><?php \singleton\fontawesome::getInstance( )->Violation( 1 );?> Violations:</div>" +
                                  "<div class='card-body '>" +
                                    "<div class='row g-0 '>" +
                                      "<div class='col-6'>Assigned:</div>" +
                                      "<div class='col-6'><a href='violations.php?Location_ID=" + row.ID + "&Location_Name=" + row.Name + "&Status=1'>" + row.Violations + " ticket(s)</a></div>" +
                                    "</div>" +
                                    "</div>" +
                                  "</div>" +
                                "</div>" +
                              "</div>" +
  			  									"</div>"
  			  					}).open( _map_, this );
  	  						break;
    						case 'Employee':
    							new google.maps.InfoWindow({
  			  						content : 	"<div class='card card-primary text-black' style='width:450pxpx;'>" +
  			  										"<div class='card-heading'><h6>" + row.Name + "</h6></div>" +
  			  										"<div class='card-body' style='font-size:16px;'>" +
  			  											"<div class='row g-0'>" +
  			  												"<div class='col-1'><?php \singleton\fontawesome::getInstance( )->Employee( 1 );?></div>" +
  			  												"<div class='col-5'>Tickets:</div>" +
  			  												"<div class='col-6'>&nbsp;</div>" +
  			  											"</div>" +
  			  											"<div class='row g-0'>" +
  			  												"<div class='col-2'>&nbsp;</div>" +
  			  												"<div class='col-4'>Assigned:</div>" +
  			  												"<div class='col-6'><a href='employee.php?ID=" + row.ID + "''>" + row.Tickets_Assigned + " ticket(s)</a></div>" +
  			  											"</div>" +
  			  											"<div class='row g-0' >" +
  			  												"<div class='col-2'>&nbsp;</div>" +
  			  												"<div class='col-4'>Active:</div>" +
  			  												"<div class='col-5'><a href='employee.php?ID=" + row.ID + "'>" + row.Tickets_Active + " ticket(s)</a></div>" +
  			  											"</div>" +
  			  										"</div>" +
  			  									"</div>"
  			  					}).open( _map_, this );
    							break;
    					}
    				}
    			);
    			markers[ row.Entity + '_' + row.ID ].addListener(
    				'dblclick',
    				function() { document.location.href= row.Entity.toLowerCase( ) + '.php?ID=' + row.ID; }
    			);
        }
		}
		function gets( ){
			if( getting.Locations == 0 ){
				getting.Locations = 1;
				get(
					'Locations',
					{
						start 		 : 0,
						length       : 9999999,
						draw 		 : draw_number,
						order 		 : {
							column 	 : 0,
							dir 	 : 'asc'
						},
			      		Territory_ID : <?php echo isset( $_GET[ 'Territory_ID'] ) 	&& !empty( $_GET[ 'Territory_ID' ] )	? $_GET[ 'Territory_ID' ] 	: 'null';?>,
			      		Division_ID  : <?php echo isset( $_GET[ 'Division_ID'] )  	&& !empty( $_GET[ 'Division_ID' ] )		? $_GET[ 'Division_ID' ] 	: 'null';?>,
			      		Route_ID     : <?php echo isset( $_GET[ 'Route_ID' ] ) 		  && !empty( $_GET[ 'Route_ID' ] )	   	? $_GET[ 'Route_ID' ] 		: 'null';?>,
			      		Customer_ID  : <?php echo isset( $_GET[ 'Customer_ID' ] ) 	&& !empty( $_GET[ 'Customer_ID' ] ) 	? $_GET[ 'Customer_ID' ] 	: 'null';?>,
			      		Location_ID  : <?php echo isset( $_GET[ 'Location_ID' ] ) 	&& !empty( $_GET[ 'Location_ID' ] ) 	? $_GET[ 'Location_ID' ] 	: 'null';?>
			      	}
			    );
			}
		  if( getting.Users == 0){
	    	getting.Users = 1;
	    	get(
				'Employees',
				{
					start 		 : 0,
					length       : 9999999,
					draw 		 : draw_number,
					order 		 : {
						column 	 : 0,
						dir 	 : 'asc'
					},
					Status          : 1,
					Active          : 1,
		      		Supervisor_ID 	: '<?php echo isset( $_GET[ 'Supervisor_ID']) 	? $_GET[ 'Supervisor_ID' ] 	: null;?>',
		      		User_ID  		: '<?php echo isset( $_GET[ 'User_ID']) 		? $_GET[ 'User_ID' ] 		: null;?>'
		      	}
		    );
	    }
			setTimeout( function( ){
				gets( )
			}, 2500 );
		}
	</script>
	<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAJwGnwOrNUvlYnmB5sdJGkXy8CQsTA46g&callback=initialize"></script>
</body>
<?php
  }
} else {?><html><head><script>document.location.href='../index.php?Forward=map.php';</script></head></html><?php }
?>
