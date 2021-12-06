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
    if(     !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Unit' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'User' ] )
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
          " SELECT 	Violation.ID        AS ID,
                    Violation.Name      AS Name,
                    Customer.ID         AS Customer_ID,
                    Customer.Name       AS Customer_Name,
                    Location.Loc        AS Location_ID,
                    Location.Tag        AS Location_Name,
                    Location.Address    AS Location_Street,
                    Location.City       AS Location_City,
                    Location.State      AS Location_State,
                    Location.Zip        AS Location_Zip,
                    Location.Latt       AS Location_Latitude,
                    Location.fLong      AS Location_Longitude,
                    Unit.ID             AS Unit_ID,
                    Unit.State          AS Unit_Name,
                    Unit.State          AS Unit_City_ID,
                    Unit.Unit           AS Unit_Building_ID,
                    Violation.fdate     AS Date,
                    Violation.Status    AS Status,
                    Violation.Quote     AS Quote,
                    Violation.Ticket    AS Ticket,
                    Violation.Remarks   AS Note,
                    Violation.Estimate  AS Estimate,
                    Violation.Price     AS Price
			FROM    Violation
                    LEFT JOIN Job              ON Violation.Job = Job.ID
                    LEFT JOIN (
                      SELECT  Owner.ID, 
                              Rol.Name
                      FROM    Owner 
                              LEFT JOIN Rol    ON Owner.Rol = Rol.ID
                    ) AS Customer              ON Customer.ID = Job.Owner
                    LEFT JOIN Loc  AS Location ON Violation.Loc = Location.Loc
                    LEFT JOIN Elev AS Unit     ON Violation.Elev = Unit.ID
          	WHERE   	Violation.ID = ?
          			OR 	Violation.Name = ?;",
          array(
          	$ID,
          	$Name
          )
      );
      $Violation =   (  empty( $ID )
                   &&  !empty( $Name )
                   &&  !$result
              )    || (empty( $ID )
                   &&  empty( $Name )
              )    ? array(
      	'ID' => null,
        'Name' => null,
        'Customer_ID' => null,
        'Customer_Name' => null,
        'Location_ID' => null,
        'Location_Name' => null,
        'Location_Street' => null,
        'Location_City' => null,
        'Location_State' => null,
        'Location_Zip' => null,
        'Location_Latitude' => null,
        'Location_Longitude' => null,
        'Unit_ID' => null,
        'Unit_Name' => null,
        'Proposal_ID' => null,
        'Proposal_Name' => null,
        'Quote_ID' => null,
        'Quote_Name' => null,
        'Job_ID' => null,
        'Job_Name' => null,
        'Ticket_ID' => null,
      	'Date' => null,
      	'Status' => null,
      	'Note' => null,
      	'Price' => null,
        'Address' => null,
        'Phone' => null,
      	'Contact' => null
      ) : sqlsrv_fetch_array($result);

      if( isset( $_POST ) && count( $_POST ) > 0 ){
      	$Violation[ 'Name' ] 		       = isset( $_POST[ 'Name' ] ) 	       ? $_POST[ 'Name' ] 	      : $Violation[ 'Name' ];
	      $Violation[ 'Contact' ] 	     = isset( $_POST[ 'Contact' ] )      ? $_POST[ 'Contact' ]      : $Violation[ 'Contact' ];
      	$Violation[ 'Phone' ] 		     = isset( $_POST[ 'Phone' ] ) 	     ? $_POST[ 'Phone' ] 	      : $Violation[ 'Phone' ];
      	$Violation[ 'Email' ] 		     = isset( $_POST[ 'Email' ] ) 	     ? $_POST[ 'Email' ] 	      : $Violation[ 'Email' ];
      	$Violation[ 'Login' ] 		     = isset( $_POST[ 'Login' ] ) 	     ? $_POST[ 'Login' ] 	      : $Violation[ 'Login' ];
      	$Violation[ 'Password' ]       = isset( $_POST[ 'Password' ] )     ? $_POST[ 'Password' ]     : $Violation[ 'Password' ];
      	$Violation[ 'Geofence' ]       = isset( $_POST[ 'Geofence' ] )     ? $_POST[ 'Geofence' ]     : $Violation[ 'Geofence' ];
      	$Violation[ 'Status' ] 	       = isset( $_POST[ 'Status' ] ) 	     ? $_POST[ 'Status' ] 	    : $Violation[ 'Status' ];
      	$Violation[ 'Website' ] 	     = isset( $_POST[ 'Website' ] ) 	   ? $_POST[ 'Website' ] 	    : $Violation[ 'Website' ];
      	$Violation[ 'Internet' ]       = isset( $_POST[ 'Internet' ] )     ? $_POST[ 'Internet' ]     : $Violation[ 'Internet' ];
        $Violation[ 'Street' ] 	       = isset( $_POST[ 'Street' ] ) 	     ? $_POST[ 'Street' ] 	    : $Violation[ 'Street' ];
      	$Violation[ 'City' ] 		       = isset( $_POST[ 'City' ] ) 	       ? $_POST[ 'City' ] 	      : $Violation[ 'City' ];
      	$Violation[ 'State' ] 		     = isset( $_POST[ 'State' ] ) 	     ? $_POST[ 'State' ] 	      : $Violation[ 'State' ];
      	$Violation[ 'Zip' ] 			     = isset( $_POST[ 'Zip' ] ) 		     ? $_POST[ 'Zip' ] 		      : $Violation[ 'Zip' ];
        $Violation[ 'Location_ID' ]    = isset( $_POST[ 'Location_ID' ] )  ? $_POST[ 'Location_ID' ]  : $Violation[ 'Location_ID' ];
        $Violation[ 'Location_Name' ]  = isset( $_POST[ 'Location' ] )     ? $_POST[ 'Location' ]     : $Violation[ 'Location_Name' ];
      	$Violation[ 'Latitude' ] 	     = isset( $_POST[ 'Latitude' ] )     ? $_POST[ 'Latitude' ]     : $Violation[ 'Latitude' ];
      	$Violation[ 'Longitude' ] 	   = isset( $_POST[ 'Longitude' ] )    ? $_POST[ 'Longitude' ]    : $Violation[ 'Longitude' ];

      	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
      		$result = \singleton\database::getInstance( )->query(
      			null,
      			"	DECLARE @MAXID INT;
      				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Violation ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Violation ) END ;
      				INSERT INTO Violation(
                ID,
                Job,
      					Loc,
      					Elev,
                Ticket,
                fDate,
                Status,
                Quote,
      					Remarks,
                Price,
      					Geolock
      				)
      				VALUES( 
                @MAXID + 1 , 
                (
                  SELECT  Job.ID 
                  FROM    Job 
                  WHERE   Job.fDesc = ?
                ),(
                  SELECT  Loc.Loc 
                  FROM    Loc 
                  WHERE   Loc.Tag = ?
                ),(
                  SELECT  Elev.ID 
                  FROM    Elev 
                  WHERE   Elev.State = ?
                ),
                ?, 
                ?,
                ?, 
                ?, 
                ?, 
                ?, 
                ?
              );
      				SELECT @MAXID + 1;",
      			array(
      				$Violation[ 'Job_Name' ],
              $Violation[ 'Location_Name' ],
              $Violation[ 'Unit_Name' ],
              $Violation[ 'Ticket_ID' ],
              $Violation[ 'Date' ],
              $Violation[ 'Status' ],
              $Violation[ 'Quote' ],
              $Violation[ 'Note' ],
              $Violation[ 'Price' ],
      				isset( $Violation[ 'Geofence' ] ) ? $Violation[ 'Geofence' ] : 0
      			)
      		);
      		sqlsrv_next_result( $result );
      		$Violation[ 'Rolodex' ] = sqlsrv_fetch_array( $result )[ 0 ];
      		sqlsrv_next_result( $result );
      		$Violation[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
      		header( 'Location: violation.php?ID=' . $Violation[ 'ID' ] );
      		exit;
      	} else {
      		\singleton\database::getInstance( )->query(
        		null,
        		"	UPDATE 	Violation
        			SET     Violation.Job = (
                        SELECT  Job.ID 
                        FROM    Job 
                        WHERE   Job.fDesc = ?
                      ),
        					    Violation.Loc = (
                        SELECT  Loc.Loc 
                        FROM    Loc 
                        WHERE   Loc.Tag = ?
                      ),
        					    Violation.Elev = (
                        SELECT  Elev.ID 
                        FROM    Elev 
                        WHERE   Elev.State = ?
                      ),
                      Violation.Ticket = ?,
        					    Violation.fdate = ?,
                      Violation.Status = ?,
                      Violation.Quote = ?,
                      Violation.Remarks = ?,
                      Violation.Price = ?
        			WHERE   Violation.ID = ?;",
        		array(
					$Violation[ 'Job_Name' ],
					$Violation[ 'Location_Name' ],
					$Violation[ 'Unit_Name' ],
					$Violation[ 'Ticket_ID' ],
					$Violation[ 'Date' ],
					$Violation[ 'Status' ],
					$Violation[ 'Quote' ],
					$Violation[ 'Note' ],
					$Violation[ 'Price' ],
      				$Violation[ 'ID' ]
        		)
        	);
      	}
    }
?><!DOCTYPE html>
<html lang="en">
<head>
  	<title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php  	$_GET[ 'Bootstrap' ] = '5.1';?>
    <?php  	$_GET[ 'Entity_CSS' ] = 1;?>
    <?php	require( bin_meta . 'index.php');?>
    <?php	require( bin_css  . 'index.php');?>
    <?php 	require( bin_js   . 'index.php');?>
</head>
<body>
  	<div id="wrapper">
	    <?php require(bin_php .'element/navigation.php');?>
	    <div id="page-wrapper" class='content'>
	      	<div class='card card-primary'><form action='violation.php?ID=<?php echo $Violation[ 'ID' ];?>' method='POST'>
		        <input type='hidden' name='ID' value='<?php echo $Violation[ 'ID' ];?>' />
		        <div class='card-heading'>
		          	<div class='row g-0 px-3 py-2'>
		            	<div class='col-4'>
			              	<h5><?php \singleton\fontawesome::getInstance( )->Violation( 1 );?><a href='violations.php?<?php
			                	echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Violations' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Violations' ][ 0 ] : array( ) );
			              	?>'>Violation</a>: <span><?php
			                	echo is_null( $User[ 'ID' ] )
			                  		? 'New'
			                  		: $User[ 'Email' ];
			              	?></span></h5>
		            	</div>
		            	<div class='col-4'></div>
		            	<div class='col-2'>
		              		<div class='row g-0'>
		                		<div class='col-4'>
				                 	<button
				                    	class='form-control rounded'
				                    	onClick="document.location.href='violation.php';"
				                  	>Save</button>
				                </div>
				                <div class='col-4'>
				                  	<button
				                    	class='form-control rounded'
				                    	onClick="document.location.href='violation.php?ID=<?php echo $User[ 'ID' ];?>';"
				                  	>Refresh</button>
				                </div>
				                <div class='col-4'>
				                  	<button
				                    	class='form-control rounded'
				                    	onClick="document.location.href='violation.php';"
				                  	>New</button>
				                </div>
				            </div>
		            	</div>
		            	<div class='col-2'>
		              		<div class='row g-0'>
		                		<div class='col-4'><button class='form-control rounded' onClick="document.location.href='violation.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
		                		<div class='col-4'><button class='form-control rounded' onClick="document.location.href='violations.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';">Table</button></div>
		                		<div class='col-4'><button class='form-control rounded' onClick="document.location.href='violation.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) + 1 ] : null;?>';">Next</button></div>
		              		</div>
		            	</div>
		          	</div>
	        	</div>
	        	<div class='card-body bg-dark text-white'>
	          		<div class='card-columns'>
			            <!--<?php if( !in_array( $Violation[ 'Location_Latitude' ], array( null, 0 ) ) && !in_array( $Violation['Location_Longitude' ], array( null, 0 ) ) ){?><div class='card card-primary my-3'>
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
			                                    center: new google.maps.LatLng( <?php echo $Violation[ 'Location_Latitude' ];?>, <?php echo $Violation[ 'Location_Longitude' ];?> ),
			                                    mapTypeId: google.maps.MapTypeId.ROADMAP
			                                  }
			                              );
			                              var markers = [];
			                              markers[0] = new google.maps.Marker({
			                                  position: {
			                                      lat:<?php echo $Violation['Location_Latitude'];?>,
			                                      lng:<?php echo $Violation['Location_Longitude'];?>
			                                  },
			                                  map: map,
			                                  title: '<?php echo $Violation[ 'Name' ];?>'
			                              });
			                          }
			                          $(document).ready(function(){ initialize(); });
			                      </script>
			                    <div class='card-body'>
			                      <div id='location_map' class='map'>&nbsp;</div>
			                    </div>
			              </div>
			            </div><?php }?>-->
	            		<div class='card card-primary my-3'>
		              		<div class='card-heading'>
		                		<div class='row g-0 px-3 py-2'>
		                  			<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
		                  			<div class='col-2'>&nbsp;</div>
		                		</div>
		              		</div>
		              		<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
		                		<div class='row g-0'>
		                  			<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?>Name:</div>
		                  			<div class='col-8'><input type='text' class='form-control edit' name='Name' value='<?php echo $Violation[ 'Name' ];?>' /></div>
			                	</div>
			                	<div class='row g-0'>
			                  		<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
			                  		<div class='col-8'><select name='Status' class='form-control edit'>
			                    		<option value=''>Select</option>
			                    		<option value='0' <?php echo $Violation[ 'Status' ] == 0 ? 'selected' : null;?>>Active</option>
			                    		<option value='1' <?php echo $Violation[ 'Status' ] == 1 ? 'selected' : null;?>>Inactive</option>
			                  		</select></div>
			                	</div>
			                	<div class='row g-0'>
				                  	<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Calendar( 1 );?> Date:</div>
				                  	<div class='col-8'><input type='date' class='form-control edit date' name='Date' value='<?php echo $Violation['Date'];?>' /></div>
			                	</div>
			                	<div class='row g-0'>
			                		<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar( 1 );?> Price:</div>
			                		<div class='col-8'><input type='text' class='form-control edit' name='Price' value='<?php echo $Violation[ 'Price' ];?>' /></div>
			                	</div>
				                <div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Note( 1 );?> Note:</div>
									<div class='col-8'><textarea type='text' class='form-control edit' name='Note' value='<?php echo $Violation[ 'Note' ];?>' rows='8'></textarea></div>
				                </div>
			              	</div>
			            </div>
			            <div class='card card-primary my-3'>
			              	<div class='card-heading position-relative'>
			                	<div class='row g-0 px-3 py-2'>
			                  		<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Location( 1 );?><span>Location</span></h5></div>
			                  		<div class='col-2'>&nbsp;</div>
			                	</div>
			              	</div>
			              	<div class='card-body bg-dark text-white' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
			                	<div class='row g-0'>
			                  		<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->User(1);?> Location:</div>
			                  		<div class='col-6'>
			                    		<input type='text' autocomplete='off' class='form-control edit' name='Location' value='<?php echo $Violation[ 'Location_Name' ];?>' />
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
			                                          			search :  $('input:visible[name="Location"]').val( )
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
			                    		if( in_array( $Violation[ 'ID' ], array( null, 0, '', ' ') ) ){
			                    			echo "onClick=\"document.location.href='locations.php?';\"";
			                    		} else {
			                    			echo "onClick=\"document.location.href='location.php?ID=" . $Violation[ 'Location_ID' ] . "';\"";
			                    		}
			                    	?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
			              		</div>
			              		<div class='row g-0'>
			                		<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address( 1 );?> Address:</div>
			                		<div class='col-6'></div>
			                		<div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?Location=<?php echo $Location[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
			                	</div>
			                	<div class='row g-0'>
			                		<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Street:</div>
			                		<div class='col-8'><input type='text' class='form-control edit' name='Street' value='<?php echo $Violation[ 'Location_Street' ];?>' /></div>
			                	</div>
			                	<div class='row g-0'>
			                		<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> City:</div>
			                		<div class='col-8'><input type='text' class='form-control edit' name='City' value='<?php echo $Violation[ 'Location_City' ];?>' /></div>
			                	</div>
			                	<div class='row g-0'>
			                		<div class='col-1'>&nbsp;</div>
			                		<div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> State:</div>
									<div class='col-8'><select readonly class='form-control' name='Location_State'>
										<option value=''>Select</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'AL' ? 'selected' : null;?> value='AL'>Alabama</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'AK' ? 'selected' : null;?> value='AK'>Alaska</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'AZ' ? 'selected' : null;?> value='AZ'>Arizona</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'AR' ? 'selected' : null;?> value='AR'>Arkansas</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'CA' ? 'selected' : null;?> value='CA'>California</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'CO' ? 'selected' : null;?> value='CO'>Colorado</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'CT' ? 'selected' : null;?> value='CT'>Connecticut</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'DE' ? 'selected' : null;?> value='DE'>Delaware</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'DC' ? 'selected' : null;?> value='DC'>District Of Columbia</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'FL' ? 'selected' : null;?> value='FL'>Florida</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'GA' ? 'selected' : null;?> value='GA'>Georgia</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'HI' ? 'selected' : null;?> value='HI'>Hawaii</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'ID' ? 'selected' : null;?> value='ID'>Idaho</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'IL' ? 'selected' : null;?> value='IL'>Illinois</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'IN' ? 'selected' : null;?> value='IN'>Indiana</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'IA' ? 'selected' : null;?> value='IA'>Iowa</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'KS' ? 'selected' : null;?> value='KS'>Kansas</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'KY' ? 'selected' : null;?> value='KY'>Kentucky</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'LA' ? 'selected' : null;?> value='LA'>Louisiana</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'ME' ? 'selected' : null;?> value='ME'>Maine</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'MD' ? 'selected' : null;?> value='MD'>Maryland</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'MA' ? 'selected' : null;?> value='MA'>Massachusetts</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'MI' ? 'selected' : null;?> value='MI'>Michigan</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'MN' ? 'selected' : null;?> value='MN'>Minnesota</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'MS' ? 'selected' : null;?> value='MS'>Mississippi</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'MO' ? 'selected' : null;?> value='MO'>Missouri</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'MT' ? 'selected' : null;?> value='MT'>Montana</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'NE' ? 'selected' : null;?> value='NE'>Nebraska</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'NV' ? 'selected' : null;?> value='NV'>Nevada</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'NH' ? 'selected' : null;?> value='NH'>New Hampshire</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'NJ' ? 'selected' : null;?> value='NJ'>New Jersey</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'NM' ? 'selected' : null;?> value='NM'>New Mexico</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'NY' ? 'selected' : null;?> value='NY'>New York</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'NC' ? 'selected' : null;?> value='NC'>North Carolina</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'ND' ? 'selected' : null;?> value='ND'>North Dakota</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'OH' ? 'selected' : null;?> value='OH'>Ohio</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'OK' ? 'selected' : null;?> value='OK'>Oklahoma</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'OR' ? 'selected' : null;?> value='OR'>Oregon</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'PA' ? 'selected' : null;?> value='PA'>Pennsylvania</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'RI' ? 'selected' : null;?> value='RI'>Rhode Island</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'SC' ? 'selected' : null;?> value='SC'>South Carolina</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'SD' ? 'selected' : null;?> value='SD'>South Dakota</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'TN' ? 'selected' : null;?> value='TN'>Tennessee</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'TX' ? 'selected' : null;?> value='TX'>Texas</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'UT' ? 'selected' : null;?> value='UT'>Utah</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'VT' ? 'selected' : null;?> value='VT'>Vermont</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'VA' ? 'selected' : null;?> value='VA'>Virginia</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'WA' ? 'selected' : null;?> value='WA'>Washington</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'WV' ? 'selected' : null;?> value='WV'>West Virginia</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'WI' ? 'selected' : null;?> value='WI'>Wisconsin</option>
										<option <?php echo $Violation[ 'Location_State' ] == 'WY' ? 'selected' : null;?> value='WY'>Wyoming</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Zip' value='<?php echo $Violation[ 'Location_Zip' ];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Latitude:</div>
									<div class='col-8'><input type='text' class='form-control edit <?php echo $Violation[ 'Location_Latitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Latitude' value='<?php echo $Violation['Location_Latitude'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Longitude:</div>
									<div class='col-8'><input type='text' class='form-control edit <?php echo $Violation[ 'Location_Longitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Longitude' value='<?php echo $Violation['Location_Longitude'];?>' /></div>
								</div>
							</div>
						</div>
					</div>
				</div><
			</form></div>
		</div>
	</div>
</body>
</html><?php
      }
  } else {?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
