<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
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
    if(   !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Map' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Map' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
    	$Map = array(
    		'Territory_ID' 		=> isset( $_GET[ 'Territory_ID' ] ) 	? $_GET[ 'Territory_ID' ] 		: null,
    		'Territory_Name' 	=> isset( $_GET[ 'Territory_Name' ] ) 	? $_GET[ 'Territory_Name' ] 	: null,
    		'Division_ID' 		=> isset( $_GET[ 'Division_ID' ] ) 		? $_GET[ 'Division_ID' ] 		: null,
    		'Division_Name' 	=> isset( $_GET[ 'Division_Name' ] ) 	? $_GET[ 'Division_Name' ] 		: null,
    		'Route_ID' 			=> isset( $_GET[ 'Route_ID' ] ) 		? $_GET[ 'Route_ID' ] 			: null,
    		'Route_Name' 		=> isset( $_GET[ 'Route_Name' ] ) 		? $_GET[ 'Route_Name' ] 		: null,
    		'Customer_ID' 		=> isset( $_GET[ 'Customer_ID' ] ) 		? $_GET[ 'Customer_ID' ] 		: null,
    		'Customer_Name' 	=> isset( $_GET[ 'Customer_Name' ] ) 	? $_GET[ 'Customer_Name' ] 		: null,
    		'Location_ID' 		=> isset( $_GET[ 'Location_ID' ] ) 		? $_GET[ 'Location_ID' ] 		: null,
    		'Location_Name' 	=> isset( $_GET[ 'Location_Name' ] ) 	? $_GET[ 'Location_Name' ] 		: null,
    		'Supervisor_ID' 	=> isset( $_GET[ 'Supervisor_ID' ] ) 	? $_GET[ 'Supervisor_ID' ] 		: null,
    		'Supervisor_Name' 	=> isset( $_GET[ 'Supervisor_Name' ] ) 	? $_GET[ 'Supervisor_Name' ] 	: null,
    		'User_ID' 			=> isset( $_GET[ 'User_ID' ] ) 			? $_GET[ 'User_ID' ] 			: null,
    		'User_Name' 		=> isset( $_GET[ 'User_Name' ] ) 		? $_GET[ 'User_Name' ] 			: null
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
						<div class='col-6 col-lg-3'>
							<div class='row g-0'>
								<div class='col-4'>
									<button
										class='form-control rounded'
										type='submit'
									><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'>Refresh</span></button>
								</div>
								<div class='col-4'>
									<button
										class='form-control rounded'
										onClick="document.location.href='map.php?ID=<?php echo $User[ 'ID' ];?>';"
									><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'> Refresh</span></button>
								</div>
								<div class='col-4'>
									<button 
										class='form-control rounded' 
										onClick="document.location.href='customers.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';"
									><?php \singleton\fontawesome::getInstance( 1 )->Table( 1 );?><span class='desktop'> Table</span></button>
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
								if( $result ){ while( $row = sqlsrv_fetch_array( $result ) ){ $Types[ $row[ 'ID' ] ] = $row[ 'Name' ]; }}
								\singleton\bootstrap::getInstance( )->card_row_form_select( 'Type', isset( $_GET[ 'Type' ] ) ? $_GET[ 'Type' ] : null, $Types );
							?>
						</div>
					</div>
				</div>
				<div class='card-body'>
					<div id='map' style='height:450px;'>&nbsp;</div>
				</div>
			</form></div>
		</div>
	</div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
	<script type="text/javascript">
		//Statics / Helpers
		function symbol( icon, color) {
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
		      		getting[ entities ] = 1;
		      	}
		    });
		}
		function mark( entities, row ){
			if( markers[ row.Entity + '_' + row.ID ] ){
  				markers[ row.Entity + '_' + row.ID ].setPosition( 
  					new google.maps.LatLng(
  						row.Latitude, 
  						row.Longitude
  					)
  				);
  				markers[ row.Entity + '_' + row.ID ].setTitle( row.Name );
  				markers[ row.Entity + '_' + row.ID ].setIcon( symbol( 'pin', row.Color ) );
  			} else {
  				markers[ row.Entity + '_' + row.ID ] = new google.maps.Marker({
  					id    : row.Entity + '_' + row.ID,
  					map      : _map_,
  					
  					position : new google.maps.LatLng(
  						row.Latitude, 
  						row.Longitude
  					),
  					title    : row.Name,
  					icon  : symbol( 'pin', row.Color ),
  					color : row.Color
  				});
  			}
  			markers[ row.Entity + '_' + row.ID ].addListener( 
  				'dblclick', 
  				function() { document.location.href= row.Entity.toLowerCase( ) + '.php?ID=' + row.ID; }
  			);
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
						Status       : 0,
			      		Territory_ID : <?php echo isset( $_GET[ 'Territory_ID'] ) 	&& !empty( $_GET[ 'Territory_ID' ] )	? $_GET[ 'Territory_ID' ] 	: 'null';?>,
			      		Division_ID  : <?php echo isset( $_GET[ 'Division_ID'] )  	&& !empty( $_GET[ 'Division_ID' ] )		? $_GET[ 'Division_ID' ] 	: 'null';?>,
			      		Route_ID     : <?php echo isset( $_GET[ 'Route_ID' ] ) 		&& !empty( $_GET[ 'Route_ID' ] )		? $_GET[ 'Route_ID' ] 		: 'null';?>,
			      		Customer_ID  : <?php echo isset( $_GET[ 'Customer_ID' ] ) 	&& !empty( $_GET[ 'Customer_ID' ] ) 	? $_GET[ 'Customer_ID' ] 	: 'null';?>,
			      		Location_ID  : <?php echo isset( $_GET[ 'Location_ID' ] ) 	&& !empty( $_GET[ 'Location_ID' ] ) 	? $_GET[ 'Location_ID' ] 	: 'null';?>
			      	}
			    );
			}
		    /*get( 
				'Users',
				{
					Status          : 1,
					Active          : 1,
		      		Supervisor_ID 	: '<?php echo isset( $_GET[ 'Supervisor_ID']) 	? $_GET[ 'Supervisor_ID' ] 	: null;?>',
		      		User_ID  		: '<?php echo isset( $_GET[ 'User_ID']) 		? $_GET[ 'User_ID' ] 		: null;?>'
		      	}
		    );*/
		    /*get( 
				'Tickets',
				{
					start 		 : 0,
					length       : 9999999,
					draw 		 : draw_number,
					order 		 : {
						column 	 : 0,
						dir 	 : 'asc'
					},
		      		Open  : 1,
		      		Type  : <?php echo isset( $_GET[ 'Type' ] )  ? $_GET[ 'Type' ]  : 'null';?>,
		      		Level : <?php echo isset( $_GET[ 'Level' ] ) ? $_GET[ 'Level' ] : 'null';?>
		      	}
		    );*/
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
