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
    // sets $ID, $Name Variable and Posts ID and Name into $result
          $result = \singleton\database::getInstance( )->query(
          	null,
              " SELECT 	Violation.ID        AS ID,
            						Unit.ID             AS Elev,
                        Customer.ID 	      AS Customer_ID,
                    		Customer.Name       AS Customer_Name,
                        Loc.Loc 				    AS Location_ID,
                        Loc.Tag 				    AS Location_Name,
            						Violation.fdate     AS Date,
                    		Violation.Status    AS Status,
                        Violation.Quote     AS Quote,
                        Violation.Ticket    AS Ticket,
                        Violation.Remarks   AS Remarks,
                    		Violation.Estimate  AS Estimate,
  	                    Violation.Price     AS Price,
                        Rol.Phone           AS Phone,
                        Rol.Email           AS Email,
                        Rol.Contact         AS Contact,
                    		Rol.Address         AS Street,
  	                    Rol.City            AS City,
  	                    Rol.State           AS State,
  	                    Rol.Zip             AS Zip,
  	                    Rol.Latt 	          AS Latitude,
  	                    Rol.fLong           AS Longitude,
					      FROM    Violation
                        LEFT JOIN (
                          SELECT  Owner.ID, 
                                  Rol.Name
                          FROM    Owner 
                                  LEFT JOIN Rol    ON Owner.Rol = Rol.ID
                        ) AS Customer              ON Customer.ID = Job.Owner
                        LEFT JOIN Loc  AS Location ON Invoice.Loc = Loc.Loc
                        LEFT JOIN Job              ON Invoice.Job = Job.ID
                        LEFT JOIN Elev AS Unit     ON Violation.Elev = Unit.ID
              	WHERE   	  Violation.ID = ?
              			    OR 	Customer.Name = ?;",
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
          	'Elev' => null,
          	'Date' => null,
          	'Job' => null,
          	'Status' => null,
          	'Quote' => null,
          	'Ticket' => null,
          	'Remarks' => null,
            'Estimate' => null,
          	'Price' => null,
            'Address' => null,
            'Phone' => null,
          	'Contact' => null,
            'Street' => null,
            'City' => null,
            'State' => null,
          	'Zip' => null,
          	'Latitude' => null,
          	'Longitude' => null,
            'Location_ID' => null,
            'Location_Name' => null
          ) : sqlsrv_fetch_array($result);
  //Binds $ID, $Name, $Violation and query values into the $result variable

          if( isset( $_POST ) && count( $_POST ) > 0 ){
            // if the $_Post is set and the count is null, select if available
          	$Violation[ 'Name' ] 		= isset( $_POST[ 'Name' ] ) 	 ? $_POST[ 'Name' ] 	 : $Violation[ 'Name' ];
    	      $Violation[ 'Contact' ] 	= isset( $_POST[ 'Contact' ] ) ? $_POST[ 'Contact' ] : $Violation[ 'Contact' ];
          	$Violation[ 'Phone' ] 		= isset( $_POST[ 'Phone' ] ) 	 ? $_POST[ 'Phone' ] 	 : $Violation[ 'Phone' ];
          	$Violation[ 'Email' ] 		= isset( $_POST[ 'Email' ] ) 	 ? $_POST[ 'Email' ] 	 : $Violation[ 'Email' ];
          	$Violation[ 'Login' ] 		= isset( $_POST[ 'Login' ] ) 	 ? $_POST[ 'Login' ] 	 : $Violation[ 'Login' ];
          	$Violation[ 'Password' ] = isset( $_POST[ 'Password' ] )  ? $_POST[ 'Password' ]  : $Violation[ 'Password' ];
          	$Violation[ 'Geofence' ] = isset( $_POST[ 'Geofence' ] )  ? $_POST[ 'Geofence' ]  : $Violation[ 'Geofence' ];
          	$Violation[ 'Type' ]     = isset( $_POST[ 'Type' ] ) 	   ? $_POST[ 'Type' ] 	   : $Violation[ 'Type' ];
          	$Violation[ 'Status' ] 	= isset( $_POST[ 'Status' ] ) 	 ? $_POST[ 'Status' ] 	 : $Violation[ 'Status' ];
          	$Violation[ 'Website' ] 	= isset( $_POST[ 'Website' ] ) 	 ? $_POST[ 'Website' ] 	 : $Violation[ 'Website' ];
          	$Violation[ 'Internet' ] = isset( $_POST[ 'Internet' ] )  ? $_POST[ 'Internet' ]  : $Violation[ 'Internet' ];
          	$Violation[ 'Address' ] 	= isset( $_POST[ 'Address' ] ) 	 ? $_POST[ 'Address' ] 	 : $Violation[ 'Address' ];
            $Violation[ 'Street' ] 	= isset( $_POST[ 'Street' ] ) 	 ? $_POST[ 'Street' ] 	 : $Violation[ 'Street' ];
          	$Violation[ 'City' ] 		= isset( $_POST[ 'City' ] ) 	 ? $_POST[ 'City' ] 	 : $Violation[ 'City' ];
          	$Violation[ 'State' ] 		= isset( $_POST[ 'State' ] ) 	 ? $_POST[ 'State' ] 	 : $Violation[ 'State' ];
          	$Violation[ 'Zip' ] 			= isset( $_POST[ 'Zip' ] ) 		 ? $_POST[ 'Zip' ] 		 : $Violation[ 'Zip' ];
            $Violation[ 'Location_ID' ]    = isset( $_POST[ 'Location_ID' ] )  ? $_POST[ 'Location_ID' ]  : $Violation[ 'Location_ID' ];
            $Violation[ 'Location_Name' ] 	= isset( $_POST[ 'Location' ] )  ? $_POST[ 'Location' ]  : $Violation[ 'Location_Name' ];
          	$Violation[ 'Latitude' ] 	= isset( $_POST[ 'Latitude' ] )  ? $_POST[ 'Latitude' ]  : $Violation[ 'Latitude' ];
          	$Violation[ 'Longitude' ] 	= isset( $_POST[ 'Longitude' ] ) ? $_POST[ 'Longitude' ] : $Violation[ 'Longitude' ];

          	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
          		$result = \singleton\database::getInstance( )->query(
          			null,
          			"	DECLARE @MAXID INT;
          				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Violation ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Violation ) END ;
          				INSERT INTO Violation(
                    ID,
          					Locs,
          					Elev,
                    fDate,
                    Status,
                    Quote,
                    Job,
                    Ticket
          					Remarks,
                    Price,
          					Address,
          					City,
          					State,
          					Zip,
          					Latt,
          					fLong,
          					Geolock
          				)
          				VALUES( @MAXID + 1 , 0, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
          				SELECT @MAXID + 1;",
          			array(
          				$Violation[ 'ID' ],
                  $Violation[ 'Locs' ],
                  $Violation[ 'Elev' ],
                  $Violation[ 'fDate' ],
                  $Violation[ 'Status' ],
                  $Violation[ 'Quote' ],
                  $Violation[ 'Job' ],
                  $Violation[ 'Ticket' ],
                  $Violation[ 'Remarks' ],
                  $Violation[ 'Price' ],
                  $Violation[ 'Address' ],
          				$Violation[ 'Street' ],
          				$Violation[ 'City' ],
          				$Violation[ 'State' ],
          				$Violation[ 'Zip' ],
          				$Violation[ 'Latitude' ],
          				$Violation[ 'Longitude' ],
          				isset( $Violation[ 'Geofence' ] ) ? $Violation[ 'Geofence' ] : 0
          			)
          		);
          		sqlsrv_next_result( $result );
      //Update query to fill values for $Violation and appends to $result for any updated colums
          		$Violation[ 'Rolodex' ] = sqlsrv_fetch_array( $result )[ 0 ];
  // finds any result with the value of 0/ null
  // query that inserts values into the $Violation [rolodex] variable datatable and appends it to the $result variable
          		sqlsrv_next_result( $result );
          		$Violation[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
  // Checks the $Violation[ID] for any fields that are null, if none exit,
          		header( 'Location: violation.php?ID=' . $Violation[ 'ID' ] );
          		exit;
          	} else {
          		\singleton\database::getInstance( )->query(
  	        		null,
  	        		"	UPDATE 	Violation
  	        			SET Violation.ID = ?,
  	        					Violation.Locs = ?,
  	        					Violation.Elev = ?,
  	        					Violation.fdate = ?,
  	        					Violation.Type = ?,
                      Violation.Status = ?,
                      Violation.Quote = ?,
                      Violation.Job = ?,
                      Violation.Ticket = ?,
                      Violation.Remarks = ?,
                      Violation.Price = ?,
                      Violation.Address = ?,
                      Violation.City = ?,
                      Violation.Zip = ?,
  	        			WHERE 	Owner.ID = ?;",
  	        		array(
                  $Violation[ 'ID' ],
                  $Violation[ 'Location' ],
                  $Violation[ 'Elev' ],
                  $Violation[ 'Date' ],
                  $Violation[ 'Status' ],
                  $Violation[ 'Quote' ],
                  $Violation[ 'Job' ],
                  $Violation[ 'Ticket' ],
                  $Violation[ 'Remarks' ],
                  $Violation[ 'Price' ],
                  $Violation[ 'Address' ],
          				$Violation[ 'Street' ],
          				$Violation[ 'City' ],
          				$Violation[ 'State' ],
          				$Violation[ 'Zip' ],
          				$Violation[ 'Latitude' ],
          				$Violation[ 'Longitude' ]
  	        		)
  	        	);
  	        	\singleton\database::getInstance( )->query(
  	        		null,
  	        		"	UPDATE 	Rol
  	        			SET 	Rol.Name = ?,
              					Rol.Website = ?,
              					Rol.Address = ?,
                        Rol.Street = ?,
              					Rol.City = ?,
              					Rol.State = ?,
              					Rol.Zip = ?,
              					Rol.Latt = ?,
              					Rol.fLong = ?,
                        Rol.Phone = ?,
                        Rol.EMail = ?

  	        			WHERE 	Rol.ID = ?;",
  	        		array(
  	        			$Violation[ 'Name' ],
  	        			$Violation[ 'Website' ],
  	        			$Violation[ 'Street' ],
                  $Violation[ 'Address' ],
  	        			$Violation[ 'City' ],
  	        			$Violation[ 'State' ],
  	        			$Violation[ 'Zip' ],
  	        			$Violation[ 'Latitude' ],
  	        			$Violation[ 'Longitude' ],
                  $Violation[ 'Phone' ],
                  $Violation[ 'Email' ],
  	        			$Violation[ 'Rolodex' ]
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
</head>
<body>
  <div id="wrapper">
    <?php require(bin_php .'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'>
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-2'>
              <h5><?php \singleton\fontawesome::getInstance( )->User( 1 );?><a href='violations.php?<?php
                echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Violations' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Violations' ][ 0 ] : array( ) );
              ?>'>Violations</a>: <span><?php
                echo is_null( $User[ 'ID' ] )
                  ? 'New'
                  : $User[ 'Email' ];
              ?></span></h5>
              </div>
              <div class='col-2'></div>
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
            <div class='card card-primary my-3'><form action='customer.php?ID=<?php echo $Customer[ 'ID' ];?>' method='POST'>
              <input type='hidden' name='ID' value='<?php echo $Customer[ 'ID' ];?>' />
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
              </div>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?>Name:</div>
                  <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Violation['Name'];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Calendar( 1 );?> Date:</div>
                  <div class='col-8'><input type='date' class='form-control edit' name='Date' value='<?php echo $Violation['Date'];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Note( 1 );?> Remarks:</div>
                  <div class='col-8'><textarea type='text' class='form-control edit' name='Remarks' value='<?php echo $Violation['Remarks'];?>'></textarea></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar( 1 );?> Price:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Price' value='<?php echo $Violation['Price'];?>' /></div>
                </div>
              </div>
            </form></div>
            <div class='card card-primary my-3'><form action='violation.php?ID=<?php echo $Violation[ 'ID' ];?>' method='POST'>
              <input type='hidden' value='<?php echo $User[ 'ID' ];?>' name='ID' />
              <div class='card-heading position-relative'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Location</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
              </div>
              <div class='card-body bg-dark text-white' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
                <input type='hidden' name='ID' value='<?php echo $Violation[ 'ID' ];?>' />
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
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Address:</div>
                <div class='col-6'></div>
                <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?Location=<?php echo $Location[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Street:</div>
                <div class='col-8'><input type='text' class='form-control edit' name='Street' value='<?php echo $Violation[ 'Street' ];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
                <div class='col-8'><input type='text' class='form-control edit' name='City' value='<?php echo $Violation[ 'City' ];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
                <div class='col-8'><input type='text' class='form-control edit' name='Zip' value='<?php echo $Violation[ 'Zip' ];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Latitude:</div>
                <div class='col-8'><input type='text' class='form-control edit <?php echo $Violation[ 'Latitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Latitude' value='<?php echo $Violation['Latitude'];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Longitude:</div>
                <div class='col-8'><input type='text' class='form-control edit <?php echo $Violation[ 'Longitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Longitude' value='<?php echo $Violation['Longitude'];?>' /></div>
              </div>
            </div>
          </div>
          <div class='card card-primary my-3'><form action='customer.php?ID=<?php echo $Violation[ 'ID' ];?>' method='POST'>
            <input type='hidden' name='ID' value='<?php echo $Violation[ 'ID' ];?>' />
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?><span>Proposal</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
              </div>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
                
              </div>
            </div>
            <div class='card card-primary my-3'>
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Job( 1 );?><span>Jobs</span></h5></div>
                  <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Violation[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                    ;",array($Violation[ 'ID' ]));
                  echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;
                  ?>' /></div>
                  <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Violation[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                    ;",array($Violation[ 'ID' ]));
                  echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;
                  ?>' /></div>
                  <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Violation[ 'Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                    ;",array($Violation[ 'ID' ]));
                  echo $r ? sqlsrv_fetch_array($r)['Jobs'] : 0;
                  ?>' /></div>
                  <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='jobs.php?Customer=<?php echo $Violation[ 'Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
              </div>
            </div>
            <?php
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
                      $Violation[ 'ID' ]
                    )
                  );?>
                  <div class='row g-0'>
                    <div class='card card-primary my-3'>
                      <div class='card-heading'>
                        <div class='row g-0 px-3 py-2'>
                      <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?><span>Units</span></h5></div>
                    <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Violation[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                        $Violation[ 'ID' ]
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
                      ;",array($Violation[ 'ID' ]));
                      //Selects the unit.ID as counts from Elev and adds it to $Violation[ID]
                      echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
                    ?>' /></div>
                    <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Violation[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                      ;",array($Violation[ 'ID' ]));
                      echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
                    ?>' /></div>
                    <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Violation[ 'Name' ];?>&Type=Escalator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
                      ;",array($Violation[ 'ID' ]));
                      echo $r ? sqlsrv_fetch_array($r)['Units'] : 0;
                    ?>' /></div>
                    <div class='col-2'><button class='h-100 w-100' readonly onClick="document.location.href='units.php?Customer=<?php echo $Violation[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
                <div class='card-footer'>
                   <div class='row'>
                     <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                   </div>
                </div>
              </div>
            </div>
                <div class='card card-primary my-3'>
    							<div class='card-heading'>
    								<div class='row g-0 px-3 py-2'>
    									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?><span>Tickets</span></h5></div>
    									<div class='col-2'></div>
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
    												$Violation[ 'ID' ]
    											)
    										);
    									?><div class='col-1'>&nbsp;</div>
    								    <div class='col-3 border-bottom border-white my-auto'>Open</div>
    								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
    										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
    									?>' /></div>
    									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Violation[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
    												$Violation[ 'ID' ]
    											)
    										);
    									?><div class='col-1'>&nbsp;</div>
    								    <div class='col-3 border-bottom border-white my-auto'>Assigned</div>
    								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
    										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
    									?>' /></div>
    									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Violation[ 'Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
    												$Violation[ 'ID' ]
    											)
    										);
    									?><div class='col-1'>&nbsp;</div>
    								    <div class='col-3 border-bottom border-white my-auto'>En Route</div>
    								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
    										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
    									?>' /></div>
    									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Violation[ 'Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
    												$Violation[ 'ID' ]
    											)
    										);
    									?><div class='col-1'>&nbsp;</div>
    								    <div class='col-3 border-bottom border-white my-auto'>On Site</div>
    								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
    										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
    									?>' /></div>
    									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Violation[ 'Name' ];?>&Status=3';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
    												$Violation[ 'ID' ]
    											)
    										);
    									?><div class='col-1'>&nbsp;</div>
    								    <div class='col-3 border-bottom border-white my-auto'>Review</div>
    								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
    										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
    									?>' /></div>
    									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Violation[ 'Name' ];?>&Status=6';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
    												$Violation[ 'ID' ]
    											)
    										);
    									?><div class='col-1'>&nbsp;</div>
    								    <div class='col-3 border-bottom border-white my-auto'>Complete</div>
    								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
    										echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
    									?>' /></div>
    									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Violation[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
    								</div>
    							</div>
    						</div>
              </div>
            </div>
          </div>
        </div>
      </form></div>
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
  } else {?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
