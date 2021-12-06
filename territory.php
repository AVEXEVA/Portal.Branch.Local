<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
    $resultesult = \singleton\database::getInstance( )->query(
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
    $Connection = sqlsrv_fetch_array($resultesult);
    //User
    $resultesult = \singleton\database::getInstance( )->query(
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
    $User   = sqlsrv_fetch_array( $resultesult );
    //Privileges
    $Access = 0;
    $Hex = 0;
    $resultesult = \singleton\database::getInstance( )->query(
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
    if( $resultesult ){while( $Privilege = sqlsrv_fetch_array( $resultesult, SQLSRV_FETCH_ASSOC ) ){

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
        $Email = isset( $_GET[ 'Email' ] )
            ? $_GET[ 'Email' ]
            : (
                isset( $_POST[ 'Email' ] )
                    ? $_POST[ 'Email' ]
                    : null
            );
        $resultesult = $database->query(
            'Portal',
            "   SELECT  Top 1
                        *
                FROM    dbo.[User]
                WHERE   [User].[ID] = ?;",
          array(
            $ID,
            $Email
          )
        );
        $User =   (       empty( $ID )
                        &&    !empty( $Name )
                        &&    !$resultesult
                      ) || (  empty( $ID )
                        &&    empty( $Name )
                      )  ? array(
            'ID' => null,
            'Email' => null
        ) : sqlsrv_fetch_array( $resultesult );
        if( isset( $_POST ) && count( $_POST ) > 0 ){
            $User[ 'Email' ] = isset( $_POST[ 'Email' ] ) ? $_POST[ 'Email' ] : $User[ 'Email' ];
            if( empty( $_POST[ 'ID' ] ) ){
                $resultesult = \singleton\database::getInstance( )->query(
                  'Portal',
                  " INSERT INTO dbo.[User]( Email )
                    VALUES( ? );
                    SELECT Max( ID ) FROM dbo.[User];",
                    array(
                        $_POST[ 'Email' ]
                    )
                );
                sqlsrv_next_result( $resultesult );
                $User[ 'ID' ] = sqlsrv_fetch_array( $resultesult )[ 0 ];
                header( 'Location: violation.php?ID=' . $User[ 'ID' ] );
                exit;
            } else {
                \singleton\database::getInstance( )->query(
                    'Portal',
                    "   UPDATE  dbo.[User]
                        SET     [User].[Email] = ?,
                        WHERE   [User].[ID] = ?;",
                    array(
                        $User[ 'Email' ],
                        $User[ 'ID' ]
                    )
                );
            }
        }
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
    // sets $ID, $Name Variable and Posts ID and Name into $resultesult
          $resultesult = \singleton\database::getInstance( )->query(
          	null,
              "	SELECT 	Top 1
              			Violation.*
              	FROM    (
              				SELECT 	Violation.ID        AS ID,
                  						Violation.Elev      AS Elev,
                  						Violation.fdate     AS 'Date',
                          		Violation.Status    AS Status,
                              Violation.Quote     AS Quote,
                              Violation.Ticket    AS Ticket,
                              Violation.Remarks   AS Remarks,
                          		Violation.Estimate  AS Estimate,
        	                    Violation.Price     AS Price,
                              Loc.Loc 				    AS Location_ID,
                              Loc.Tag 				    AS Location_Name,
                              Rol.ID 	            AS Rolodex,
                          		Rol.Name            AS Name,
                              Rol.Phone           AS Phone,
                              Rol.Email           AS Email,
                              Rol.Contact         AS Contact,
                          		Rol.Address         AS Street,
        	                    Rol.City            AS City,
        	                    Rol.State           AS State,
        	                    Rol.Zip             AS Zip,
        	                    Rol.Latt 	          AS Latitude,
        	                    Rol.fLong           AS Longitude,
  							    FROM    Owner
  									        LEFT JOIN Rol ON Owner.Rol = Rol.ID
                            LEFT JOIN Loc ON Invoice.Loc = Loc.Loc
                            LEFT JOIN Job ON Invoice.Job = Job.ID
              		) AS violation
              	WHERE   	Violation.ID = ?
              			OR 	Customer.Name = ?;",
              array(
              	$ID,
              	$Name
                    )
                );
                $Territory =   (  empty( $ID )
                             &&  !empty( $Name )
                             &&  !$resultesult
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
          ) : sqlsrv_fetch_array($resultesult);
  //Binds $ID, $Name, $Territory and query values into the $resultesult variable

          if( isset( $_POST ) && count( $_POST ) > 0 ){
            // if the $_Post is set and the count is null, select if available
          	$Territory[ 'Name' ] 		= isset( $_POST[ 'Name' ] ) 	 ? $_POST[ 'Name' ] 	 : $Territory[ 'Name' ];
    	      $Territory[ 'Contact' ] 	= isset( $_POST[ 'Contact' ] ) ? $_POST[ 'Contact' ] : $Territory[ 'Contact' ];
          	$Territory[ 'Phone' ] 		= isset( $_POST[ 'Phone' ] ) 	 ? $_POST[ 'Phone' ] 	 : $Territory[ 'Phone' ];
          	$Territory[ 'Email' ] 		= isset( $_POST[ 'Email' ] ) 	 ? $_POST[ 'Email' ] 	 : $Territory[ 'Email' ];
          	$Territory[ 'Login' ] 		= isset( $_POST[ 'Login' ] ) 	 ? $_POST[ 'Login' ] 	 : $Territory[ 'Login' ];
          	$Territory[ 'Password' ] = isset( $_POST[ 'Password' ] )  ? $_POST[ 'Password' ]  : $Territory[ 'Password' ];
          	$Territory[ 'Geofence' ] = isset( $_POST[ 'Geofence' ] )  ? $_POST[ 'Geofence' ]  : $Territory[ 'Geofence' ];
          	$Territory[ 'Type' ]     = isset( $_POST[ 'Type' ] ) 	   ? $_POST[ 'Type' ] 	   : $Territory[ 'Type' ];
          	$Territory[ 'Status' ] 	= isset( $_POST[ 'Status' ] ) 	 ? $_POST[ 'Status' ] 	 : $Territory[ 'Status' ];
          	$Territory[ 'Website' ] 	= isset( $_POST[ 'Website' ] ) 	 ? $_POST[ 'Website' ] 	 : $Territory[ 'Website' ];
          	$Territory[ 'Internet' ] = isset( $_POST[ 'Internet' ] )  ? $_POST[ 'Internet' ]  : $Territory[ 'Internet' ];
          	$Territory[ 'Address' ] 	= isset( $_POST[ 'Address' ] ) 	 ? $_POST[ 'Address' ] 	 : $Territory[ 'Address' ];
            $Territory[ 'Street' ] 	= isset( $_POST[ 'Street' ] ) 	 ? $_POST[ 'Street' ] 	 : $Territory[ 'Street' ];
          	$Territory[ 'City' ] 		= isset( $_POST[ 'City' ] ) 	 ? $_POST[ 'City' ] 	 : $Territory[ 'City' ];
          	$Territory[ 'State' ] 		= isset( $_POST[ 'State' ] ) 	 ? $_POST[ 'State' ] 	 : $Territory[ 'State' ];
          	$Territory[ 'Zip' ] 			= isset( $_POST[ 'Zip' ] ) 		 ? $_POST[ 'Zip' ] 		 : $Territory[ 'Zip' ];
            $Territory[ 'Location_ID' ]    = isset( $_POST[ 'Location_ID' ] )  ? $_POST[ 'Location_ID' ]  : $Territory[ 'Location_ID' ];
            $Territory[ 'Location_Name' ] 	= isset( $_POST[ 'Location' ] )  ? $_POST[ 'Location' ]  : $Territory[ 'Location_Name' ];
          	$Territory[ 'Latitude' ] 	= isset( $_POST[ 'Latitude' ] )  ? $_POST[ 'Latitude' ]  : $Territory[ 'Latitude' ];
          	$Territory[ 'Longitude' ] 	= isset( $_POST[ 'Longitude' ] ) ? $_POST[ 'Longitude' ] : $Territory[ 'Longitude' ];

          	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
          		$resultesult = \singleton\database::getInstance( )->query(
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
          				$Territory[ 'ID' ],
                  $Territory[ 'Locs' ],
                  $Territory[ 'Elev' ],
                  $Territory[ 'fDate' ],
                  $Territory[ 'Status' ],
                  $Territory[ 'Quote' ],
                  $Territory[ 'Job' ],
                  $Territory[ 'Ticket' ],
                  $Territory[ 'Remarks' ],
                  $Territory[ 'Price' ],
                  $Territory[ 'Address' ],
          				$Territory[ 'Street' ],
          				$Territory[ 'City' ],
          				$Territory[ 'State' ],
          				$Territory[ 'Zip' ],
          				$Territory[ 'Latitude' ],
          				$Territory[ 'Longitude' ],
          				isset( $Territory[ 'Geofence' ] ) ? $Territory[ 'Geofence' ] : 0
          			)
          		);
          		sqlsrv_next_result( $resultesult );
      //Update query to fill values for $Territory and appends to $resultesult for any updated colums
          		$Territory[ 'Rolodex' ] = sqlsrv_fetch_array( $resultesult )[ 0 ];
  // finds any result with the value of 0/ null
  // query that inserts values into the $Territory [rolodex] variable datatable and appends it to the $resultesult variable
          		sqlsrv_next_result( $resultesult );
          		$Territory[ 'ID' ] = sqlsrv_fetch_array( $resultesult )[ 0 ];
  // Checks the $Territory[ID] for any fields that are null, if none exit,
          		header( 'Location: violation.php?ID=' . $Territory[ 'ID' ] );
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
                  $Territory[ 'ID' ],
                  $Territory[ 'Location' ],
                  $Territory[ 'Elev' ],
                  $Territory[ 'Date' ],
                  $Territory[ 'Status' ],
                  $Territory[ 'Quote' ],
                  $Territory[ 'Job' ],
                  $Territory[ 'Ticket' ],
                  $Territory[ 'Remarks' ],
                  $Territory[ 'Price' ],
                  $Territory[ 'Address' ],
          				$Territory[ 'Street' ],
          				$Territory[ 'City' ],
          				$Territory[ 'State' ],
          				$Territory[ 'Zip' ],
          				$Territory[ 'Latitude' ],
          				$Territory[ 'Longitude' ]
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
  	        			$Territory[ 'Name' ],
  	        			$Territory[ 'Website' ],
  	        			$Territory[ 'Street' ],
                  $Territory[ 'Address' ],
  	        			$Territory[ 'City' ],
  	        			$Territory[ 'State' ],
  	        			$Territory[ 'Zip' ],
  	        			$Territory[ 'Latitude' ],
  	        			$Territory[ 'Longitude' ],
                  $Territory[ 'Phone' ],
                  $Territory[ 'Email' ],
  	        			$Territory[ 'Rolodex' ]
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
<body onload='finishkLoadingPage();'>
  <div id="wrapper">
    <?php require(bin_php .'element/navigation.php');?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'>
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-6'>
              <h5><?php \singleton\fontawesome::getInstance( )->User( 1 );?><a href='territories.php?<?php
                echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Territories' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Territories' ][ 0 ] : array( ) );
              ?>'>Territories</a>: <span><?php
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
                      onClick="document.location.href='territory.php';"
                    >Create</button>
                  </div>
                  <div class='col-4'>
                    <button
                      class='form-control rounded'
                      onClick="document.location.href='territory.php?ID=<?php echo $User[ 'ID' ];?>';"
                    >Refresh</button>
                  </div>
                </div>
              </div>
              <div class='col-2'>
                <div class='row g-0'>
                  <div class='col-4'><button class='form-control rounded' onClick="document.location.href='territory.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                  <div class='col-4'><button class='form-control rounded' onClick="document.location.href='Territories.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';">Table</button></div>
                  <div class='col-4'><button class='form-control rounded' onClick="document.location.href='territory.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) + 1 ] : null;?>';">Next</button></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class='card-body bg-dark text-white'>
          <div class='card-columns'>
          <div class='card card-primary my-3'><form action='violation.php?ID=<?php echo $Territory[ 'ID' ];?>' method='POST'>
            <div class='card-heading position-relative' style='z-index:1;'>
              <div class='row g-0 px-3 py-2'>
                <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Location</span></h5></div>
                <div class='col-2'>&nbsp;</div>
                <input type='hidden' value='<?php echo $User[ 'ID' ];?>' name='ID' />
              </div>
            </div>
          <!-- Second card headding that holds vio.php information and fontawesome icon, the POST call retrieves information from $Territory ID    -->
          <div class='card-body bg-dark text-white' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Violations' ] == 0 ? "style='display:none;'" : null;?>>
            <input type='hidden' name='ID' value='<?php echo $Territory[ 'ID' ];?>' />
            <!-- Selector for status that has echos the Customer Status and checks the value 0/1 and assignes a color -Warning or -Success  -->
            <div class='row g-0'>
              <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->User(1);?> Location:</div>
              <div class='col-6'>
                <input type='text' autocomplete='off' class='form-control edit' name='Location' value='<?php echo $Territory[ 'Location_Name' ];?>' />
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
                if( in_array( $Territory[ 'ID' ], array( null, 0, '', ' ') ) ){
                  echo "onClick=\"document.location.href='locations.php?Field=1';\"";
                } else {
                  echo "onClick=\"document.location.href='location.php?ID=" . $Territory[ 'Location_ID' ] . "';\"";
                }
              ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
          </div>
          <div class='row g-0'>
            <div class='col-4 border-bottom border-white my-auto'>Address:</div>
            <div class='col-8'><input type='text' class='form-control edit' name='Address' value='<?php echo $Territory['Address'];?>' /></div>
          </div>
          <div class='row g-0'>
            <div class='col-4 border-bottom border-white my-auto'>City:</div>
            <div class='col-8'><input type='text' class='form-control edit' name='City' value='<?php echo $Territory['City'];?>' /></div>
          </div>
            <div class='row g-0'>
              <div class='col-4 border-bottom border-white my-auto'>Zip:</div>
              <div class='col-8'><input type='text' class='form-control edit' name='Zip' value='<?php echo $Territory['Zip'];?>' /></div>
            </div>
            <div class='row g-0'>
              <div class='col-4 border-bottom border-white my-auto'>Latitude:</div>
              <div class='col-8'><input type='text' class='form-control edit <?php echo $Territory[ 'Latitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Latitude' value='<?php echo $Territory['Latitude'];?>' /></div>
            </div>
            <div class='row g-0'>
              <div class='col-4 border-bottom border-white my-auto'>Longitude:</div>
              <div class='col-8'><input type='text' class='form-control edit <?php echo $Territory[ 'Longitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Longitude' value='<?php echo $Territory['Longitude'];?>' /></div>
            </div>
            <div class='col-2'>&nbsp;</div>
          </div>
        </div>
            <div class='card card-primary my-3'><form action='proposal.php?ID=<?php echo $Territory[ 'ID' ];?>' method='POST'>
                <input type='hidden' name='ID' value='<?php echo $Territory[ 'ID' ];?>' />
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?><span>Proposal</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
              </div>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Proposals' ] ) && $_SESSION[ 'Cards' ][ 'Proposals' ] == 0 ? "style='display:none;'" : null;?>>
            <div class='row g-0'>
              <div class='col-4 border-bottom border-white my-auto'>Date:</div>
              <div class='col-8'><input type='date' class='form-control edit' name='Date' value='<?php echo $Territory['Date'];?>' /></div>
            </div>
            <div class='row g-0'>
              <div class='col-4 border-bottom border-white my-auto'>Remarks:</div>
              <div class='col-8'><textarea type='text' class='form-control edit' name='Remarks' value='<?php echo $Territory['Remarks'];?>' /></textarea>
            </div>
          </div>
            <div class='row g-0'>
              <div class='col-4 border-bottom border-white my-auto'>Price:</div>
              <div class='col-8'><input type='text' class='form-control edit' name='Price' value='<?php echo $Territory['Price'];?>' /></div>
            </div>
          </div>
        </div>
              <div class='row g-0'>
                <div class='card card-primary my-3'>
                  <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Unit( 1 );?><span>Units</span></h5></div>
                <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Territory=<?php echo $Territory[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
            </div>
            <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Units' ] ) && $_SESSION[ 'Cards' ][ 'Units' ] == 0 ? "style='display:none;'" : null;?>>
              <?php
                $result = \singleton\database::getInstance( )->query(
                  null,
                  "	SELECT 	Count( Unit.ID ) AS Units
                    FROM   	Elev AS Unit
                          LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                    WHERE  	Location.Terr = ? ;",
                  array(
                    $Territory[ 'ID' ]
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
                  $result = \singleton\database::getInstance( )->query(
                    null,
                    "	SELECT 	Count( Unit.ID ) AS Units
                      FROM   	Elev AS Unit
                            LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                      WHERE  		Location.Terr = ?
                          AND Unit.Type = 'Elevator'
                  ;",array($Territory[ 'ID' ]));
                  //Selects the unit.ID as counts from Elev and adds it to $Territory[ID]
                  echo $result ? sqlsrv_fetch_array($result)['Units'] : 0;
                ?>' /></div>
                <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Territory[ 'Name' ];?>&Type=Elevator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                  <div class='col-3 border-bottom border-white my-auto'>Escalators</div>
                  <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
                  $result = \singleton\database::getInstance( )->query(null,
                    " SELECT 	Count( Unit.ID ) AS Units
                      FROM   	Elev AS Unit
                          LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                      WHERE  		Location.Terr = ?
                      AND Unit.Type = 'Escalator'
                  ;",array($Territory[ 'ID' ]));
                  echo $result ? sqlsrv_fetch_array($result)['Units'] : 0;
                ?>' /></div>
                <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='units.php?Customer=<?php echo $Territory[ 'Name' ];?>&Type=Escalator';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
              </div>
              <div class='row g-0'>
                <div class='col-1'>&nbsp;</div>
                  <div class='col-3 border-bottom border-white my-auto'>Other</div>
                  <div class='col-6'><input class='form-control' type='text' readonly name='Units' value='<?php
                  $result = \singleton\database::getInstance( )->query(null,
                    " SELECT 	Count( Unit.ID ) AS Units
                      FROM   	Elev AS Unit
                          LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                      WHERE  		Location.Terr = ?
                        AND Unit.Type NOT IN ( 'Elevator', 'Escalator' )
                  ;",array($Territory[ 'ID' ]));
                  echo $result ? sqlsrv_fetch_array($result)['Units'] : 0;
                ?>' /></div>
                <div class='col-2'><button class='h-100 w-100' readonly onClick="document.location.href='units.php?Customer=<?php echo $Territory[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
								<div class='row'>
								    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Statuses</div>
								    <div class='col-6'>&nbsp;</div>
									<div class='col-2'>&nbsp;</div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Terr = ?
																		AND TicketO.Assigned = 0
															)
														) AS Tickets;",
											array(
												$Territory[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Open</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
											null,
											"	SELECT Count( Tickets.ID ) AS Tickets
												FROM   (
															(
																SELECT 	TicketO.ID AS ID
																FROM   	TicketO
																	   	LEFT JOIN Loc AS Location ON TicketO.LID = Location.Loc
																WHERE  		Location.Terr = ?
																		AND TicketO.Assigned = 1
															)
														) AS Tickets;",
											array(
												$Territory[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Assigned</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
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
												$Territory[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>En Route</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
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
												$Territory[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>On Site</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=3';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
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
												$Territory[ 'ID' ]
											)
										);
									?>
                  <div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Review</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=6';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'><?php
										$result = \singleton\database::getInstance( )->query(
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
												$Territory[ 'ID' ]
											)
										);
									?><div class='col-1'>&nbsp;</div>
								    <div class='col-3 border-bottom border-white my-auto'>Complete</div>
								    <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
										echo $result ? sqlsrv_fetch_array($result)[ 'Tickets' ] : 0;
									?>' /></div>
									<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Territory[ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
              </div>
                </div>
                <div class='card card-primary my-3'>
                  <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                      <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Collection( 1 );?><span>Collections</span></h5></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Location=<?php echo $Territory [ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                  </div>
                  <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Collections' ] ) && $_SESSION[ 'Cards' ][ 'Collections' ] == 0 ? "style='display:none;'" : null;?>>
                    <div class='row g-0'>
                        <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Balance</div>
                        <div class='col-6'><input class='form-control' type='text' readonly name='Balance' value='<?php
                        $r = \singleton\database::getInstance( )->query(null,"
                          SELECT Sum( OpenAR.Balance ) AS Balance
                          FROM   OpenAR
                               LEFT JOIN Loc AS Location ON OpenAR.Loc = Location.Loc
                          WHERE  Location.Owner = ?
                        ;",array($Territory [ 'ID' ]));
                        $Balance = $r ? sqlsrv_fetch_array($r)['Balance'] : 0;
                        echo money_format('%(n',$Balance);
                      ?>' /></div>
                      <div class='col-2'>&nbsp;</div>
                    </div>
                  </div>
                </div>
                <div class='card card-primary my-3'>
                  <div class='card-heading'>
                    <div class='row g-0 px-3 py-2'>
                      <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?><span>Invoices</span></h5></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='invoices.php?Location=<?php echo $Territory [ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                  </div>
                  <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Admins' ] ) && $_SESSION[ 'Cards' ][ 'Admins' ] == 0 ? "style='display:none;'" : null;?>>
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
                        ;",array($Territory [ 'ID' ]));
                        $Count = $r ? sqlsrv_fetch_array($r)['Count'] : 0;
                        echo $Count
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Location=<?php echo $Territory [ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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

                        ;",array($Territory [ 'ID' ]));
                        $Count = $r ? sqlsrv_fetch_array($r)['Count'] : 0;
                        echo $Count
                      ?>' /></div>
                      <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Location=<?php echo $Territory [ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div>
                    <?php }?>
                  </div>
                </div>
                  <div class='card card-primary my-3'>
          					<div class='card-heading'>
          						<div class='row g-0 px-3 py-2'>
          							<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Proposal( 1 );?><span>Proposals</span></h5></div>
          							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Territory [ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
          								$result = \singleton\database::getInstance( )->query(null,"
          									SELECT 	Count(Estimate.ID) AS Proposals
          									FROM   	Estimate
          										   	LEFT JOIN Loc AS Location ON Estimate.LocID = Location.Loc
          									WHERE  		Location.Owner = ?
          											AND Estimate.Status = 0
          								;",array($Territory [ 'ID' ]));
          								echo $result ? sqlsrv_fetch_array($result)['Proposals'] : 0;
          							?>' /></div>
          							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Territory [ 'Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
          						</div>
          						<div class='row g-0'>
          							<div class='col-1'>&nbsp;</div>
          						    <div class='col-3 border-bottom border-white my-auto'>Awarded</div>
          						    <div class='col-6'><input class='form-control' type='text' readonly name='Proposals' value='<?php
          								$result = \singleton\database::getInstance( )->query(null,"
          									SELECT 	Count(Estimate.ID) AS Proposals
          									FROM   	Estimate
          										   	LEFT JOIN Loc AS Location ON Estimate.LocID = Location.Loc
          									WHERE  		Location.Owner = ?
          											AND Estimate.Status = 4
          								;",array($Territory [ 'ID' ]));
          								echo $result ? sqlsrv_fetch_array($result)['Proposals'] : 0;
          							?>' /></div>
          							<div class='col-2'><button class='h-100 w-100' onClick="document.location.href='proposals.php?Customer=<?php echo $Territory [ 'Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
          						</div>
          					</div>
          				</div>
              <div class='card card-primary my-3'><form action='customer.php?ID=<?php echo $Territory[ 'ID' ];?>' method='POST'>
                  <input type='hidden' name='ID' value='<?php echo $Territory[ 'ID' ];?>' />
                <div class='card-heading'>
                  <div class='row g-0 px-3 py-2'>
                    <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?><span>Leads</span></h5></div>
                    <div class='col-2'>&nbsp;</div>
                  </div>
                </div>
                <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Leads' ] ) && $_SESSION[ 'Cards' ][ 'Leads' ] == 0 ? "style='display:none;'" : null;?>>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'>Date:</div>
                <div class='col-8'><input type='date' class='form-control edit' name='Date' value='<?php echo $Territory['Date'];?>' /></div>
              </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'>Remarks:</div>
                <div class='col-8'><textarea type='text' class='form-control edit' name='Remarks' value='<?php echo $Territory['Remarks'];?>' /></textarea>
              </div>
            </div>
              <div class='row g-0'>
                <div class='col-4 border-bottom border-white my-auto'>Price:</div>
                <div class='col-8'><input type='text' class='form-control edit' name='Price' value='<?php echo $Territory['Price'];?>' /></div>
            </div>
          </div>
        </div>
		 </div>
   </div>
 </div>
</form>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
