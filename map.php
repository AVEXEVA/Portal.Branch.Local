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
    if(   !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Map' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Map' ] )
    ){ ?><?php require('404.html');?><?php }
    else {?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Nouveau Elevator Portal</title>
    <?php  	$_GET[ 'Bootstrap' ] = '5.1';?>
    <?php 	require( bin_meta . 'index.php');?>
    <?php 	require( bin_css . 'index.php');?>
    <?php 	require( bin_js . 'index.php');?>
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
									<!--<button
										class='form-control rounded'
										onClick="document.location.href='customer.php?ID=<?php echo $User[ 'ID' ];?>';"
									><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'> Refresh</span></button>-->
								</div>
								<div class='col-4'>
									<!--<button
										class='form-control rounded'
										onClick="document.location.href='customer.php';"
									><?php \singleton\fontawesome::getInstance( 1 )->Add( 1 );?><span class='desktop'> New</span></button>-->
								</div>
							</div>
						</div>
						<div class='col-6 col-lg-3'>
							<div class='row g-0'>
								<div class='col-4'>
									<!--<button 
										class='form-control rounded' 
										onClick="document.location.href='customer.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) - 1 ] : null;?>';"
									><?php \singleton\fontawesome::getInstance( 1 )->Previous( 1 );?><span class='desktop'> Previous</span></button>-->
								</div>
								<div class='col-4'>
									<!--<button 
										class='form-control rounded' 
										onClick="document.location.href='customers.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';"
									><?php \singleton\fontawesome::getInstance( 1 )->Table( 1 );?><span class='desktop'> Table</span></button>-->
								</div>
								<div class='col-4'>
									<!--<button 
										class='form-control rounded' 
										onClick="document.location.href='customer.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) + 1 ] : null;?>';"
									><?php \singleton\fontawesome::getInstance( 1 )->Next( 1 );?><span class='desktop'> Next</span></button>-->
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class='card card-body bg-dark' >
					<div class=''>
					 <form name="map" action="map.php" method='get'>
					<div class='row g-0 px-3 py-2'>
						
         <?php $terr= isset($_GET['Territory']) ? $_GET['Territory']:0;
         $route= isset($_GET['route']) ? $_GET['route']:0;
         $divison= isset($_GET['divison']) ? $_GET['divison']:0; ?>
            <div class='col-3'>Territory: <select name='Territory' class="form-control">
              <option value=''>Select Territory</option>
              <?php
                $r = \singleton\database::getInstance( )->query(null,"SELECT * FROM Terr;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option value='<?php echo $row['ID'];?>' <?php if($terr == $row['ID']) echo 'selected'; ?> ><?php echo $row['Name'];?></option><?php }}
              ?>
            </select>
         
           </div>
           <div class='col-1'></div>
            <div class='col-3'>Route: <select name='route' class="form-control">
              <option value=''>Select Route</option>
              <?php
                $r = \singleton\database::getInstance( )->query(null,"SELECT * FROM Route;");
                if($r){while($row = sqlsrv_fetch_array($r)){?>
                	<option value='<?php echo $row['ID'];?>' <?php if($route==$row['ID']) echo 'selected'; ?>><?php echo $row['Name'];?></option><?php }}
              ?>
            </select>
          </div>
          <div class='col-1'></div>
           <div class='col-2'>Division<select name='divison' class="form-control">
              <option value=''>Select Divison</option>
              <?php
                $r = \singleton\database::getInstance( )->query(null,"SELECT * FROM Zone;");
                if($r){while($row = sqlsrv_fetch_array($r)){?><option value='<?php echo $row['ID'];?>' <?php if($divison==$row['ID']) echo 'selected'; ?> ><?php echo $row['Name'];?></option><?php }}
              ?>
            </select>
          </div>
         <div class='col-1'></div>
          <div class='col-1'>  search<button
										class='form-control rounded'
										type='submit'
									><?php \singleton\fontawesome::getInstance( 1 )->search( 1 );?><span class='desktop'> Search</span></button></div>
     
          </div>
           </form>
          </div>
		 </div>	
				<div class='card-body bg-dark'>

					<div class='row g-0'>
						<div class='col-4'>
							<div class='row g-0'>
								<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Locations:</div>
								<div class='col-8'>&nbsp;</div>
							</div>
							<div class='row g-0'>
			              		<div class='col-1'>&nbsp;</div>
			                  	<div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Division(1);?> Division:</div>
			                  	<div class='col-6'>
			                  	  <input placeholder='Division' type='text' autocomplete='off' class='form-control edit' name='Division' value='<?php echo isset( $_GET[ 'Division' ] ) ? $_GET[ 'Division' ] : null;?>' />
			                  	  <script>
			                  	    $( 'input[name="Division"]' )
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
			                  	                        search :  $('input:visible[name="Division"]').val( )
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
			                  	                $( 'input[name="Division"]').val( value );
			                  	                $( 'input[name="Division"]').closest( 'form' ).submit( );
			                  	            }
			                  	        }
			                  	    );
			                  	  </script>
			                  	</div>
			                  	<div class='col-2'><button class='h-100 w-100' type='button' <?php
			                  	  if( in_array( isset( $_GET[ 'Division' ] ) ? $_GET[ 'Division' ] : null, array( null, 0, '', ' ') ) ){
			                  	    echo "onClick=\"document.location.href='divisions.php';\"";
			                  	  } else {
			                  	    echo "onClick=\"document.location.href='division.php?ID=" . $_GET[ 'Division' ] . "';\"";
			                  	  }
			                  	?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
			              	</div>
							<div class='row g-0'>
			              		<div class='col-1'>&nbsp;</div>
			                  	<div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Route(1);?> Route:</div>
			                  	<div class='col-6'>
			                  	  <input placeholder='Route' type='text' autocomplete='off' class='form-control edit' name='Route' value='<?php echo isset( $_GET[ 'Route' ] ) ? $_GET[ 'Route' ] : null;?>' />
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
			                  	  if( in_array( isset( $_GET[ 'Route' ] ) ? $_GET[ 'Route' ] : null, array( null, 0, '', ' ') ) ){
			                  	    echo "onClick=\"document.location.href='routes.php';\"";
			                  	  } else {
			                  	    echo "onClick=\"document.location.href='route.php?ID=" . $_GET[ 'Route' ] . "';\"";
			                  	  }
			                  	?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
			              	</div>
							<div class='row g-0'>
								<div class='col-1'>&nbsp;</div>
			                  	<div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Customer:</div>
			                  	<div class='col-6'>
			                  	  	<input placeholder='Customer' type='text' autocomplete='off' class='form-control edit' name='Customer' value='<?php echo isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null;?>' />
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
			                  	<div class='col-2'><button class='h-100 w-100' type='button' <?php
			                  	  	if( in_array( isset( $_GET[ 'Customer' ] ) ? $_GET[ 'Customer' ] : null, array( null, 0, '', ' ') ) ){
			                  	  	  	echo "onClick=\"document.location.href='customers.php';\"";
			                  	  	} else {
			                  	  	  	echo "onClick=\"document.location.href='customer.php?ID=" . $_GET[ 'Customer' ] . "';\"";
			                  	  	}
			                  	?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
			              	</div>
							<div class='row g-0'>
								<div class='col-1'>&nbsp;</div>
			                  	<div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Location:</div>
			                  	<div class='col-6'>
			                  	  <input placeholder='Location' type='text' autocomplete='off' class='form-control edit' name='Location' value='<?php echo isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null;?>' />
			                  	  <script>
			                  	    $( 'input[name="Location"]' )
			                  	        .typeahead({
			                  	            minLength : 4,
			                  	            hint: true,
			                  	            highlight: true,
			                  	            limit : 5,
			                  	            display : 'FieldValue',
			                  	            source: function( query, result ){
			                  	                $.ajax({
			                  	                    url : 'bin/php/get/search/Locations.php',
			                  	                    method : 'GET',
			                  	                    data    : {
			                  	                        search :  $('input:visible[name="Location"]').val( ),
			                  	                        Customer : $('input:visible[name="Customer"]').val( )
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
			                  	                $( 'input[name="Location"]').val( value );
			                  	                $( 'input[name="Location"]').closest( 'form' ).submit( );
			                  	            }
			                  	        }
			                  	    );
			                  	  </script>
			                  	</div>
			                  	<div class='col-2'><button class='h-100 w-100' type='button' <?php
			                  	  if( in_array( isset( $_GET[ 'Location' ] ) ? $_GET[ 'Location' ] : null, array( null, 0, '', ' ') ) ){
			                  	    echo "onClick=\"document.location.href='locations.php';\"";
			                  	  } else {
			                  	    echo "onClick=\"document.location.href='location.php?ID=" . $_GET[ 'Location' ] . "';\"";
			                  	  }
			                  	?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
			              	</div>
						</div>
						<div class='col-4'>
							<div class='row g-0'>
								<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Users( 1 );?> Users:</div>
								<div class='col-8'>&nbsp;</div>
							</div>
							<div class='row g-0'>
                                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Supervisor:</div>
                                <div class='col-6'>
                                    <input placeholder='Supervisor' type='text' autocomplete='off' class='form-control edit' name='Supervisor' value='<?php echo isset( $_GET[ 'Supervisor' ] ) ? $_GET[ 'Supervisor' ] : null;?>' />
                                    <script>
                                        $( 'input[name="Supervisor"]' )
                                            .typeahead({
                                                    minLength : 4,
                                                    hint: true,
                                                    highlight: true,
                                                    limit : 5,
                                                    display : 'FieldValue',
                                                    source: function( query, result ){
                                                        $.ajax({
                                                            url : 'bin/php/get/search/Employees.php',
                                                            method : 'GET',
                                                            data    : {
                                                                search :  $('input:visible[name="Supervisor"]').val( )
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
                                                        $( 'input[name="Supervisor"]').val( value );
                                                        $( 'input[name="Supervisor"]').closest( 'form' ).submit( );
                                                    }
                                                }
                                            );
                                    </script>
                                </div>
                                <div class='col-2'><button class='h-100 w-100' type='button' <?php
                                    if( in_array( isset( $_GET[ 'Supervisor' ] ) ? $_GET[ 'Supervisor' ] : null, array( null, 0, '', ' ') ) ){
                                        echo "onClick=\"document.location.href='supervisors.php?';\"";
                                    } else {
                                        echo "onClick=\"document.location.href='supervisor.php?ID=" . $_GET[ 'Supervisor' ] . "';\"";
                                    }
                                    ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                            </div>
							<div class='row g-0'>
                                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->User(1);?> User:</div>
                                <div class='col-6'>
                                    <input placeholder='User' type='text' autocomplete='off' class='form-control edit' name='User' value='<?php echo isset( $_GET[ 'User' ] ) ? $_GET[ 'User' ] : null;?>' />
                                    <script>
                                        $( 'input[name="User"]' )
                                            .typeahead({
                                                    minLength : 4,
                                                    hint: true,
                                                    highlight: true,
                                                    limit : 5,
                                                    display : 'FieldValue',
                                                    source: function( query, result ){
                                                        $.ajax({
                                                            url : 'bin/php/get/search/Employees.php',
                                                            method : 'GET',
                                                            data    : {
                                                                search :  $('input:visible[name="User"]').val( )
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
                                                        $( 'input[name="User"]').val( value );
                                                        $( 'input[name="User"]').closest( 'form' ).submit( );
                                                    }
                                                }
                                            );
                                    </script>
                                </div>
                                <div class='col-2'><button class='h-100 w-100' type='button' <?php
                                    if( in_array( isset( $_GET[ 'User' ] ) ? $_GET[ 'User' ] : null, array( null, 0, '', ' ') ) ){
                                        echo "onClick=\"document.location.href='users.php?Field=1';\"";
                                    } else {
                                        echo "onClick=\"document.location.href='user.php?ID=" . $_GET[ 'User' ] . "';\"";
                                    }
                                    ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                            </div>
						</div>
						<div class='col-4'>
							<div class='row g-0'>
								<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?> Tickets:</div>
								<div class='col-8'>&nbsp;</div>
							</div>
							<div class='row g-0'>
                                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Tags:</div>
                                <div class='col-8'><select class='form-control edit' name='Tags' multiple>
                                	<option value=''>Select</option>
                                	<option value='Shutdown'>Shutdown</option>
                                	<option value='Entrapment'>Entrapment</option>
                                	<option value='Service Call'>Service Call</option>
                                	<option value='Maintenance'>Maintenance</option>
                                	<option value='Repair'>Repair</option>
                                	<option value='Modernization'>Modernization</option>
                                	<option value='Resident'>Resident</option>
                                </select></div>
                            </div>

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
		
		var marker = new Array( );
		var markers = new Array( );
		var map;
		var directionsDisplay1;
		var directionsService1;
		function renderMap(){
		  	var latlng = {
		  		lat : <?php echo isset($_GET['Latitude']) ? $_GET['Latitude'] : 40.7831;?>, 
		  		lng:<?php echo isset($_GET['Longitude']) ? $_GET['Longitude'] : -73.9712;?>
		  	};
			var myOptions = {
		  		zoom: <?php echo isset($_GET['Latitude'], $_GET['Longitude']) ? 18 : 10;?>,
		  		center: latlng
			};
			map = new google.maps.Map( 
				document.getElementById( 'map' ), 
				myOptions
			);
		  	$(document).ready(function(){
		    	getLocations( );
		    	
		  	});
		}
		var GETTING_GPS = 0;
		function pinSymbol(color) {
		    return {
		        path: 'M 0,0 C -2,-20 -10,-22 -10,-30 A 10,10 0 1,1 10,-30 C 10,-22 2,-20 0,0 z M -2,-30 a 2,2 0 1,1 4,0 2,2 0 1,1 -4,0',
		        fillColor: color,
		        fillOpacity: 1,
		        strokeColor: '#000',
		        strokeWeight: 2,
		        scale: 1,
		   };
		}
		function getLocations( ){
		  if( GETTING_GPS == 0 ){
		  
		    $.ajax({
		      url:"bin/php/get/Map.php",
		      method:"GET",
		      data:{
		                Territory:'<?php echo isset($_GET['Territory']) ? $_GET['Territory'] : 0;?>',
		                route:'<?php echo isset($_GET['route']) ? $_GET['route'] : 0;?>',
		                division:'<?php echo isset($_GET['division']) ? $_GET['division'] : 0;?>'
		              },
		      success:function(json){
		        var GPS_Data = JSON.parse(json);
		        for(i in GPS_Data){
		          if(marker[i] && marker[i]['Color'] && marker[i]['Color'] == 'black'){
		            var Color = 'black';
		          } else if(GPS_Data[i].Type=='Employee'){
		            var ClassName = 'New-GPS';
		            var Color = 'green';
		          } else {
		            var ClassName ='Dead-GPS';
		            var Color = 'brown';
		          }
		          if(marker[i]){
		            marker[i].setPosition(new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude));
		            marker[i].setTitle(GPS_Data[i].Title);
		            marker[i].setIcon(pinSymbol(Color));
		            marker[i]['Color'] = Color;
		            marker[i]['Employee_ID'] = i;
		            
		          } else {
		            marker[i] = new google.maps.Marker({
		              map: map,
		              position: new google.maps.LatLng(GPS_Data[i].Latitude, GPS_Data[i].Longitude),
		              title: GPS_Data[i].Title,
		              icon: {
		                path:pinSymbol( 'black' ),
		                fillColor:'#00CCBB',
		                fillOpacity:0,
		                strokeColor:'black',
		                strokeWeight:0
		              },
		              id:i,
		              Color:Color,
		              Employee_ID:i,
		            
		              icon:pinSymbol(Color)
		            });
		          }
		          marker[i].addListener('dblclick', function() {
		            $.ajax({
		              url:"bin/php/tooltip/GPS.php",
		              method:"GET",
		              data:{
		                ID:this['Employee_ID']
		              },
		              success:function(code){
		                $(".popup").remove();
		                $("body").append(code);
		              }
		            });
		          });
		          markers.push( i );
		        }
		        GETTING_GPS = 0;
		        if(GOT_DIRECTIONS == 0){setTimeout(function(){<?php if(isset($_GET['Latitude'],$_GET['Longitude']) && isset($_GET['Nearest'])){?>find_closest_marker(<?php echo $_GET['Latitude'];?>, <?php echo $_GET['Longitude'];?>);<?php }?>},100);GOT_DIRECTIONS = 1;}
		      }
		    });
		  }
		}
		
	
		
		/*function not used*/
			function codeAddress(address) {
		    geocoder = new google.maps.Geocoder();
		    geocoder.geocode({
		        'address': address
		    }, function(results, status) {
		        if (status == google.maps.GeocoderStatus.OK) {
		          map.setCenter(results[0].geometry.location);
		          map.setZoom(18);
		          if(LookUp_Address != null){LookUp_Address.setMap(null);}
		          LookUp_Address = new google.maps.Marker({
		            map: map,
		            position: new google.maps.LatLng(results[0].geometry.location.lat(),results[0].geometry.location.lng()),
		            icon: {
		              path:mapIcons.shapes.SQUARE_PIN,
		              fillColor:'#00CCBB',
		              fillOpacity:0,
		              strokeColor:'black',
		              strokeWeight:0
		            },
		            zIndex:99999999,
		            id:'LookUp_Address',
		            icon:flagSymbol('black')
		          });
		        }
		    });
	}

		    	function takeServiceCall(){
		  $.ajax({
		    url:"bin/php/element/map/Service_Call.php",
		    method:"GET",
		    success:function(code){
		      $("body").append(code);
		    }
		  });
		}


		function zoomUser(link){
		  var val = $(link).val();
		  for ( i in marker ){
		    if(marker[i].id == val){
		      var latlng = new google.maps.LatLng(marker[i].getPosition().lat(), marker[i].getPosition().lng());
		      map.setCenter(marker[i].getPosition());
		      map.setZoom(15);
		      if(LookUp_User != null){
		        marker[LookUp_User].setIcon(pinSymbol(marker[LookUp_User]['Color']));
		      }
		      marker[i].setIcon(pinSymbol('black'));
		      marker[i]['Color'] = 'black';
		      LookUp_User = i;
		    }
		  }
		}


		function breadcrumbUser(link){
		  var val = $(link).val();
		  document.location.href='map3.php?ID=' + val;
		}
		var toggle = 0;
		function setMapOnAll(mapped) {
		  for ( i in marker )
		    marker[i].setMap(mapped);
		  for ( i in shutdowns )
		    shutdowns[i].setMap(mapped);
		  for ( i in entrapments )
		    entrapments[i].setMap(mapped);
		  //marker = new Array();
		}

		function clearMarkers() {
		  setMapOnAll(toggle == 0 ? null : map);
		  toggle = toggle == 0 ? 1 : 0;
		  Timeline_Supervisor = ''
		  $("#Feed").html("");
		  REFRESH_DATETIME = '<?php echo date("Y-m-d H:i:s",strtotime('-300 minutes'));?>';
		  TIMELINE = new Array();
		  getTimeline();
		}
		
	

	</script>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAJwGnwOrNUvlYnmB5sdJGkXy8CQsTA46g&callback=renderMap"></script>
<!--script type='text/javascript' src='https://maps.googleapis.com/maps/api/directions/json?origin=43.65077%2C-79.378425&destination=43.63881%2C-79.42745&key=AIzaSyAJwGnwOrNUvlYnmB5sdJGkXy8CQsTA46g'></script -->

</body>
<?php
  }
} else {?><html><head><script>document.location.href='../login.php?Forward=map.php?Type=Live';</script></head></html><?php }
?>
