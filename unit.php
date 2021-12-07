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
        ||  !isset( $Privileges[ 'Unit' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Unit' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        $ID = isset( $_GET[ 'ID' ] )
            ? $_GET[ 'ID' ]
            : (
            isset( $_POST[ 'ID' ] )
                ? $_POST[ 'ID' ]
                : null
            );
        $City_ID = isset( $_GET[ 'City_ID' ] )
            ? $_GET[ 'City_ID' ]
            : (
            isset( $_POST[ 'City_ID' ] )
                ? $_POST[ 'City_ID' ]
                : null
            );
		$result = \singleton\database::getInstance( )->query(
		    null,
		    " 	SELECT  TOP 1
	                    Unit.ID,
	                    Unit.Unit        		AS Building_ID,
	                    Unit.State              AS City_ID,
	                    Customer.ID             AS Customer_ID,
	                    Customer.Name           AS Customer_Name,
	                    Location.ID             AS Location_ID,
	                    Location.Tag  			AS Location_Name,
	                    Unit.fDesc              AS Description,
	                    Unit.fGroup             AS Bank,
	                    Unit.Remarks            AS Note,
	                    Unit.Type               AS Type,
	                    Unit.Cat                AS Category,
	                    Unit.Building           AS Building,
	                    Unit.Manuf              AS Manufacturer,
	                    Unit.Install            AS Installation,
	                    Unit.InstallBy          AS Installer,
	                    Unit.Since              AS Created,
	                    Unit.Last               AS Maintained,
	                    Unit.Price              AS Price,
	                    Unit.Serial             AS Serial,
	                    Unit.Template           AS Template,
	                    Unit.Status             AS Status,
	                    Unit.TFMID              AS TFMID,
	                    Unit.TFMSource          AS TFMSource,
	                    Location.Latt 			AS Latitude,
	                    Location.fLong 			AS Longitude
	            FROM    Elev AS Unit
	                    LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
	                    LEFT JOIN (
	                       	SELECT  Owner.ID,
	                               	Rol.Name
	                       	FROM    Owner
	                               LEFT JOIN Rol ON Rol.ID = Owner.Rol
	                   	) AS Customer ON Unit.Owner = Customer.ID
	            WHERE      Unit.ID = ?
	                    OR Unit.State = ?;",
            array(
                isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null,
                isset( $_GET[ 'City_ID' ] ) ? $_GET[ 'City_ID' ] : null
            )
		);

		$Unit =   (  empty( $ID )
		    &&  !empty( $City_ID )
		    &&  !$result
		)    || (empty( $ID )
		    &&  empty( $City_ID )
		)    ? array(
		    'ID' => null,
		    'Building_ID' => null,
		    'City_ID' => null,
		    'Customer_ID' => null,
		    'Customer_Name' => null,
		    'Location_ID' => null,
		    'Location_Name' => null,
		    'Description' => null,
		    'Bank' => null,
		    'Note' => null,
		    'Type' => null,
		    'Category' => null,
		    'Building' => null,
		    'Manufacturer' => null,
		    'Installation' => null,
		    'Installer'   =>  null,
		    'Created' => null,
		    'Maintained'   =>  null,
		    'Price' => null,
		    'Serial' => null,
		    'Template' => null,
		    'Status' => null,
		    'TFMID' => null,
		    'TFMSource' => null,
		    'Latitude' => null,
		    'Longitude' => null
		) : sqlsrv_fetch_array($result);

		if( isset( $_POST ) && count( $_POST ) > 0 ){

		  $Unit[ 'Building_ID' ] = isset( $_POST[ 'Building_ID' ] ) 	 ? $_POST[ 'Building_ID' ] 	 : $Unit[ 'Building_ID' ];
		  $Unit[ 'City_ID' ] = isset( $_POST[ 'City_ID' ] ) 	 ? $_POST[ 'City_ID' ] 	 : $Unit[ 'City_ID' ];
			$Unit[ 'Customer_Name' ] = isset( $_POST[ 'Customer' ] ) 	 ? $_POST[ 'Customer' ] 	 : $Unit[ 'Customer_Name' ];
			$Unit[ 'Location_Name' ] = isset( $_POST[ 'Location' ] ) 	 ? $_POST[ 'Location' ] 	 : $Unit[ 'Location_Name' ];
			$Unit[ 'Description' ] = isset( $_POST[ 'Description' ] ) 	 ? $_POST[ 'Description' ] 	 : $Unit[ 'Description' ];
			$Unit[ 'Bank' ] = isset( $_POST[ 'Bank' ] ) 	 ? $_POST[ 'Bank' ] 	 : $Unit[ 'Bank' ];
			$Unit[ 'Note' ] =  isset( $_POST[ 'Note' ] ) ? $_POST[ 'Note' ] : $Unit[ 'Note' ];
			$Unit[ 'Type' ] = isset( $_POST[ 'Type' ] ) 	 ? $_POST[ 'Type' ] 	 : $Unit[ 'Type' ];
			$Unit[ 'Category' ] = isset( $_POST[ 'Category' ] ) 	 ? $_POST[ 'Category' ] 	 : $Unit[ 'Category' ];
		  $Unit[ 'Building' ] = isset( $_POST[ 'Building' ] ) ? $_POST[ 'Building' ] : $Unit[ 'Building' ];
		  $Unit[ 'Manufacturer' ] = isset( $_POST[ 'Manufacturer' ] ) 	 ? $_POST[ 'Manufacturer' ] 	 : $Unit[ 'Manufacturer' ];
		  $Unit[ 'Installation' ] = isset( $_POST[ 'Installation' ] ) 	 ? $_POST[ 'Installation' ] 	 : $Unit[ 'Installation' ];
		  $Unit[ 'Installer' ] = isset( $_POST[ 'Installer' ] ) 	 ? $_POST[ 'Installer' ] 	 : $Unit[ 'Installer' ];
		  $Unit[ 'Maintained' ] = isset( $_POST[ 'Maintained' ] ) 	 ? $_POST[ 'Maintained' ] 	 : $Unit[ 'Maintained' ];
		  $Unit[ 'Price' ] = isset( $_POST[ 'Price' ] ) 	 ? $_POST[ 'Price' ] 	 : $Unit[ 'Price' ];
		  $Unit[ 'Serial' ] = isset( $_POST[ 'Serial' ] ) 	 ? $_POST[ 'Serial' ] 	 : $Unit[ 'Serial' ];
		  $Unit[ 'Template' ]  = isset( $_POST[ 'Template' ] ) 	 ? $_POST[ 'Template' ] 	 : $Unit[ 'Template' ];

		  if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
		      $result = \singleton\database::getInstance( )->query(
            null,
            "	DECLARE @MAXID INT;
						  SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Unit ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Unit ) END ;
						  INSERT INTO Unit(
							         ID,
		                   Unit,
		                   State,
		                   fDesc,
		                   fGroup,
		                   Remarks,
		                   Type,
		                   Cat,
		                   Building,
		                   Manuf,
		                   Install,
		                   InstallBy,
		                   Since,
		                   Last,
		                   Price,
		                   Serial,
		                   Template,
		                   Status,
		                   TFMID,
		                   TFMSource
						  )
						  VALUES( @MAXID + 1 , ?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,? );
						  SELECT @MAXID + 1;",
		        array(
	                $Unit[ 'Building_ID' ],
	                $Unit[ 'City_ID' ],
	                $Unit[ 'Description' ],
	                $Unit[ 'Bank' ],
	                $Unit[ 'Note' ],
	                $Unit[ 'Type' ],
	                $Unit[ 'Category' ],
	                $Unit[ 'Building' ],
	                $Unit[ 'Manufacturer' ],
	                $Unit[ 'Installation' ],
	                $Unit[ 'Installer' ],
	                $Unit[ 'Created' ],
	                $Unit[ 'Maintained' ],
	                $Unit[ 'Price' ],
	                $Unit[ 'Serial' ],
	                $Unit[ 'Template' ],
	                $Unit[ 'Status' ],
	                $Unit[ 'TFMID' ],
	                $Unit[ 'TFMSource' ]
	            )
	        );
	        sqlsrv_next_result( $result );
	        $Unit[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
	        header( 'Location: unit.php?ID=' . $Unit[ 'ID' ] );
	        exit;
		    }
		    else{
	        \singleton\database::getInstance( )->query(
            null,
            "	UPDATE 	Unit
    			    SET 	  Unit.Building_ID = ?,
						          Unit.City_ID = ?,
    					        Unit.fDesc  = ?,
						          Unit.Remarks = ?,
						          Unit.Cat = ?,
						          Unit.Type = ?,
						          Unit.Building = ?,
						          Unit.Manuf = ?,
						          Unit.Install = ?,
						          Unit.InstallBy = ?,
						          Unit.Since = ?,
						          Unit.Last = ?,
						          Unit.Price = ?,
						          Unit.Loc = ?,
						          Unit.Owner = ?,
						          Unit.fGroup = ?,
						          Unit.Serial = ?,
						          Unit.Template = ?,
						          Unit.Status = ?,
						          Unit.TFMID = ?,
						          Unit.TFMSource  = ?
    			    WHERE 	Unit.ID = ?;",
            array(
              $Unit[ 'Building_ID' ],
              $Unit[ 'City_ID' ],
              $Unit[ 'Description' ],
              $Unit[ 'Note' ],
              $Unit[ 'Category' ],
              $Unit[ 'Type' ],
              $Unit[ 'Building' ],
              $Unit[ 'Manufacturer' ],
              $Unit[ 'Installation' ],
              $Unit[ 'Installer' ],
              $Unit[ 'Created' ],
              $Unit[ 'Maintained' ],
              $Unit[ 'Price' ],
              $Unit[ 'Location' ],
              $Unit[ 'Customer_Name' ],
              $Unit[ 'Bank' ],
              $Unit[ 'Serial' ],
              $Unit[ 'Template' ],
              $Unit[ 'Status' ],
              $Unit[ 'TFMID' ],
              $Unit[ 'TFMSource' ],
              $Unit[ 'ID' ]
            )
	        );
	        header( 'Location: unit.php?ID=' . $Unit[ 'ID' ] );
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
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php'); ?>
		<div id="page-wrapper" class='content'>
      <div class="card card-primary"><form action='unit.php?ID=<?php echo $Unit[ 'ID' ];?>' method='POST'>
        <input type='hidden' name='ID' value='<?php echo $Unit[ 'ID' ];?>' />
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-12 col-lg-6'>
                <h5><?php \singleton\fontawesome::getInstance( )->Location( 1 );?><a href='units.php?<?php
                  echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Units' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Units' ][ 0 ] : array( ) );
                ?>'>Territories</a>: <span><?php
                  echo is_null( $Unit[ 'ID' ] )
                      ? 'New'
                      : '#' . $Unit[ 'ID' ];
                ?></span></h5>
            </div>
            <div class='col-6 col-lg-3'>
                <div class='row g-0'>
                  <div class='col-4'>
                    <button
                        class='form-control rounded'
                        onClick="document.location.href='unit.php';"
                      ><?php \singleton\fontawesome::getInstance( 1 )->Save( 1 );?><span class='desktop'> Save</span></button>
                  </div>
                  <div class='col-4'>
                      <button
                        class='form-control rounded'
                        onClick="document.location.href='Unit.php?ID=<?php echo $User[ 'ID' ];?>';"
                      ><?php \singleton\fontawesome::getInstance( 1 )->Refresh( 1 );?><span class='desktop'> Refresh</span></button>
                  </div>
                  <div class='col-4'>
                      <button
                        class='form-control rounded'
                        onClick="document.location.href='unit.php';"
                      ><?php \singleton\fontawesome::getInstance( 1 )->Add( 1 );?><span class='desktop'> New</span></button>
                  </div>
              </div>
            </div>
            <div class='col-6 col-lg-3'>
                <div class='row g-0'>
                  <div class='col-4'><button class='form-control rounded' onClick="document.location.href='unit.php?ID=<?php echo !is_null( $User[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) - 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Previous( 1 );?><span class='desktop'> Previous</span></button></div>
                  <div class='col-4'><button class='form-control rounded' onClick="document.location.href='units.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] : array( ) );?>';"><?php \singleton\fontawesome::getInstance( 1 )->Table( 1 );?><span class='desktop'> Table</span></button></div>
                  <div class='col-4'><button class='form-control rounded' onClick="document.location.href='unit.php?ID=<?php echo !is_null( $User[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true )[ array_search( $User[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Users' ], true ) ) + 1 ] : null;?>';"><?php \singleton\fontawesome::getInstance( 1 )->Next( 1 );?><span class='desktop'> Next</span></button></div>
                </div>
            </div>
          </div>
        </div>
        <div class="card-body bg-dark text-white">
          <div class="card-columns">
            <?php if( !in_array( $Unit[ 'Latitude' ], array( null, 0 ) ) && !in_array( $Unit['Longitude' ], array( null, 0 ) ) ){?><div class='card card-primary'>
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
                      document.getElementById( 'unit_map' ),
                      {
                        zoom: 10,
                        center: new google.maps.LatLng( <?php echo $Unit[ 'Latitude' ];?>, <?php echo $Unit[ 'Longitude' ];?> ),
                        mapTypeId: google.maps.MapTypeId.ROADMAP
                      }
                    );
                    var markers = [];
                    markers[0] = new google.maps.Marker({
                      position: {
                        lat:<?php echo $Unit['Latitude'];?>,
                        lng:<?php echo $Unit['Longitude'];?>
                      },
                      map: map,
                      title: '<?php echo $Unit[ 'Name' ];?>'
                    });
                  }
                  $(document).ready(function(){ initialize(); });
                </script>
				        <div class='card-body'>
				        	<div id='unit_map' class='map'>&nbsp;</div>
				        </div>
							</div>
						</div><?php }?>
            <div class='card card-primary my-3'>
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                </div>
              </div>
              <div class='card-body bg-dark text-white'>
                <input type='hidden' name='ID' value='<?php echo $Unit[ 'ID' ];?>' />
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> City ID:</div>
                  <div class='col-8'><input type="text" class="edit form-control" name="City_ID" value="<?php echo isset( $Unit['City_ID'] ) ? $Unit[ 'City_ID' ] : null;  ?>" required></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Building ID:</div>
                  <div class='col-8'><input type="text" class="edit form-control" name="Building_ID" value="<?php echo isset( $Unit[ 'Building_ID' ] ) ? $Unit[ 'Building_ID' ] : null;  ?>" required></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Customer:</div>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='form-control edit' name='Customer' value='<?php echo $Unit[ 'Customer_Name' ];?>' />
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
                  <div class='col-2'><button type='button' class='h-100 w-100' type='button' <?php
                  if( in_array( $Unit[ 'Customer_ID' ], array( null, 0, '', ' ') ) ){
                    echo "onClick=\"document.location.href='customers.php';\"";
                  } else {
                    echo "onClick=\"document.location.href='customer.php?ID=" . $Unit[ 'Customer_ID' ] . "';\"";
                  }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Location:</div>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='edit form-control edit' name='Location' value='<?php echo $Unit[ 'Location_Name' ];?>' />
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
              	<div class='col-2'><button type='button' class='h-100 w-100' type='button' <?php
                	if( in_array( $Unit[ 'Location_ID' ], array( null, 0, '', ' ') ) ){
                  		echo "onClick=\"document.location.href='locations.php';\"";
                	} else {
                  		echo "onClick=\"document.location.href='location.php?ID=" . $Unit[ 'Location_ID' ] . "';\"";
                	}
              	?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
          	   </div>
            	<div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Status:</div>
                          <div class='col-8'>
                              <select class="edit form-control" name="Status" required><option value="">Select</option>
                                  <option value='0' <?php echo $Unit[ 'Status' ] == 0 ? 'selected' : null?>>Active</option>
                                  <option value='1' <?php echo $Unit[ 'Status' ] == 1 ? 'selected' : null;?>>Inactive</option>
                                  <option value='2' <?php echo $Unit[ 'Status' ] == 2 ? 'selected' : null;?>>Demolished</option>
                              </select>
                          </div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Description:</div>
                          <div class='col-8'>
                              <textarea rows='8' class="edit form-control" name="Description" required><?php echo $Unit['Description'];  ?></textarea>
                          </div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Note:</div>
                          <div class='col-8'>
                              <textarea rows='8' class="edit form-control" name="Note" required><?php echo $Unit['Note'];  ?></textarea>
                          </div>
                      </div>
                  </div>
                  <div class="card-footer">
                      <div class="row">
                          <div class="col-12"><button class="form-control" type="submit">Save</button></div>
                      </div>
                  </div>
              </div>
              <div class='card card-primary my-3'>
                  <div class='card-heading'>
                      <div class='row g-0 px-3 py-2'>
                          <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Engineering</span></h5></div>
                      </div>
                  </div>
                  <div class='card-body bg-dark text-white'>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Manufacturer:</div>
                          <div class='col-8'><input type="text" class="edit form-control" name="Manufacturer" value="<?php echo isset( $Unit[ 'Manufacturer' ] ) ? $Unit[ 'Manufacturer' ] : null;?>" required></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Installation:</div>
                          <div class='col-8'><input type="text" class="date edit form-control" name="Installation" value="<?php echo isset( $Unit['Installation'] ) ? $Unit[ 'Installation' ] : null;  ?>" required></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Installer:</div>
                          <div class='col-8'><input type="text" class="edit form-control" name="Installer" value="<?php echo isset( $Unit['Installer'] ) ? $Unit[ 'Installer' ] : null;  ?>" required></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Maintained:</div>
                          <div class='col-8'><input type="text" class="edit form-control" name="Last" value="<?php echo $Unit['Maintained'];  ?>" required></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Serial:</div>
                          <div class='col-8'><input type="text" class="edit form-control" name="Serial" value="<?php echo isset( $Unit[ 'Serial' ] ) ? $Unit[ 'Serial' ] : null;?>" required></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Type:</div>
                          <div class='col-8'>
                              <select class="form-control" name="Type" >
                                  <option value="">Select</option>
                                  <option value="Elevator" <?php echo $Unit[ 'Type' ] == 'Elevator' ? 'selected' : null;?>>Elevator</option>
                                  <option value="Escalator" <?php echo $Unit[ 'Type' ] == 'Escalator' ? 'selected' : null;?>>Escalator</option>
                                  <option value="Moving Walk" <?php echo $Unit[ 'Type' ] == 'Moving Walk' ? 'selected' : null;?>>Moving Walk</option>
                                  <option value="Dumbwaiter" <?php echo $Unit[ 'Type' ] == 'Dumbwaiter' ? 'selected' : null;?>>Dumbwaiter</option>
                              </select>
                          </div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Calendar( 1 );?> Installation:</div>
                          <div class='col-8'><input type="text" class="edit date form-control" name="Install" value="<?php echo $Unit['Installation'];  ?>" required ></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Calendar( 1 );?> Created:</div>
                          <div class='col-8'><input type="text" class="edit form-control" name="Created" value="<?php echo $Unit['Created'];  ?>" required></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Calendar( 1 );?> Maintained:</div>
                          <div class='col-8'><input type="text" class="edit form-control" name="Maintained" value="<?php echo $Unit['Maintained'];  ?>" required></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar( 1 );?> Price:</div>
                          <div class='col-8'><input type="text" class="edit form-control" name="Price"  value="<?php echo $Unit['Price'];  ?>" required></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Bank:</div>
                          <div class='col-8'><input type="text" class="edit form-control" name="Bank" value="<?php echo $Unit['Bank'];  ?>" required></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Template:</div>
                          <div class='col-8'><select class="edit form-control" name="Template"><option value="">Select</option></select></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Unit Category:</div>
                          <div class='col-8'>
                              <select class="edit form-control" name="Category" required><option value="">Select</option>
                                  <option value="Consultant" <?php echo $Unit[ 'Category' ] == 'Consultant' ? 'selected' : '';?>>Consultant</option>
                                  <option value="Other" <?php echo $Unit[ 'Category' ] == 'Other' ? 'selected' : '';?>>Other</option>
                                  <option value="Public" <?php echo $Unit[ 'Category' ] == 'Public' ? 'selected' : '';?>>Public</option>
                                  <option value="N/A" <?php echo $Unit[ 'Category' ] == 'N/A' ? 'selected' : '';?>>N/A</option>
                                  <option value="Service" <?php echo $Unit[ 'Category' ] == 'Service' ? 'selected' : '';?>>Service</option>
                                  <option value="Private" <?php echo $Unit[ 'Category' ] == 'Private' ? 'selected' : '';?>>Private</option>
                              </select>
                          </div>
                      </div>
                      <!--
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> TFMID:</div>
                          <div class='col-8'>
                              <input type="text" class="form-control" name="TFMID" value="<?php echo $Unit['TFMID'];  ?>">

                          </div>
                      </div>
                  	-->
                      <!--
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> Building:</div>
                          <div class='col-8'>
                              <select class="form-control" name="Location_Category" required><option value="">Select</option>
                                  <option value="Government" <?php echo $Unit[ 'Building' ] == 'Government' ? 'selected' : '';?>>Government</option>
                                  <option value="Hospital" <?php echo $Unit[ 'Building' ] == 'Hospital' ? 'selected' : '';?>>Hospital</option>
                                  <option value="School" <?php echo $Unit[ 'Building' ] == 'School' ? 'selected' : '';?>>School</option>
                                  <option value="Commercial" <?php echo $Unit[ 'Building' ] == 'Commercial' ? 'selected' : '';?>>Commercial</option>
                                  <option value="Residence" <?php echo $Unit[ 'Building' ] == 'Residence' ? 'selected' : '';?>>Residence</option>
                                  <option value="Funeral Homes" <?php echo $Unit[ 'Building' ] == 'Funeral Homes' ? 'selected' : '';?>>Funeral Homes</option>
                                  <option value="Utility-Powerplants" <?php echo $Unit[ 'Building' ] == 'Utility-Powerplants' ? 'selected' : '';?>>Utility-Powerplants</option>
                                  <option value="Other" <?php echo $Unit[ 'Building' ] == 'Other' ? 'selected' : '';?>>Other</option>
                                  <option value="Catering Hall" <?php echo $Unit[ 'Building' ] == 'Catering Hall' ? 'selected' : '';?>>Catering Hall</option>
                                  <option value="Apartment / Residence" <?php echo $Unit[ 'Building' ] == 'Apartment / Residence' ? 'selected' : '';?>>Apartment / Residence</option>
                                  <option value="Office / Commercial" <?php echo $Unit[ 'Building' ] == 'Office / Commercial' ? 'selected' : '';?>>Office / Commercial</option>
                                  <option value="Warehouse" <?php echo $Unit[ 'Building' ] == 'Warehouse' ? 'selected' : '';?>>Warehouse</option>
                                  <option value="Store / Retail" <?php echo $Unit[ 'Building' ] == 'Store / Retail' ? 'selected' : '';?>>Store / Retail</option>
                                  <option value="Bank" <?php echo $Unit[ 'Building' ] == 'Bank' ? 'selected' : '';?>>Bank</option>
                                  <option value="Parking Structure" <?php echo $Unit[ 'Building' ] == 'Parking Structure' ? 'selected' : '';?>>Parking Structure</option>
                                  <option value="Club/Museum" <?php echo $Unit[ 'Building' ] == 'Club/Museum' ? 'selected' : '';?>>Club/Museum</option>
                                  <option value="Hospital" <?php echo $Unit[ 'Building' ] == 'Hospital' ? 'selected' : '';?>>Hospital</option>
                                  <option value="Nursing Home" <?php echo $Unit[ 'Building' ] == 'Nursing Home' ? 'selected' : '';?>>Nursing Home</option>
                                  <option value="Airport" <?php echo $Unit[ 'Building' ] == 'Airport' ? 'selected' : '';?>>Airport</option>
                                  <option value="Church" <?php echo $Unit[ 'Building' ] == 'Church' ? 'selected' : '';?>>Church</option>
                                  <option value="Hotel" <?php echo $Unit[ 'Building' ] == 'Hotel' ? 'selected' : '';?>>Hotel</option>
                                  <option value="Post Office" <?php echo $Unit[ 'Building' ] == 'Post Office' ? 'selected' : '';?>>Post Office</option>
                                  <option value="Mission" <?php echo $Unit[ 'Building' ] == 'Mission' ? 'selected' : '';?>>Mission</option>
                              </select>
                          </div>
                      </div>
                  	-->
                      <!--
                      <div class='row g-0'>
                          <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank( 1 );?> TFMSource:</div>
                          <div class='col-8'><input type="text" class="form-control" name="TFMSource" value="<?php echo $Unit['TFMSource'];  ?>"></div>
                      </div>
                  	-->
                  </div>
                  <div class="card-footer">
                      <div class="row">
                          <div class="col-12"><button class="form-control" type="submit">Save</button></div>
                      </div>
                  </div>
              </div>
              <div class='card card-primary my-3'>
                  <div class='card-heading'>
                      <div class='row g-0 px-3 py-2'>
                          <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?><span>Tickets</span></h5></div>
                          <div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='tickets.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
      								WHERE  		TicketO.LElev = ?
      										AND TicketO.Assigned = 0
      							)
      						) AS Tickets;",
                              array(
                                  $Unit[ 'ID' ]
                              )
                          );
                          ?><div class='col-1'>&nbsp;</div>
                          <div class='col-3 border-bottom border-white my-auto'>Open</div>
                          <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                              echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                              ?>' /></div>
                          <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>&Status=0';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
      								WHERE  		TicketO.LElev = ?
      										AND TicketO.Assigned = 1
      							)
      						) AS Tickets;",
                              array(
                                  $Unit[ 'ID' ]
                              )
                          );
                          ?><div class='col-1'>&nbsp;</div>
                          <div class='col-3 border-bottom border-white my-auto'>Assigned</div>
                          <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                              echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                              ?>' /></div>
                          <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>&Status=1';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
      								WHERE  		TicketO.LElev = ?
      										AND TicketO.Assigned = 2
      							)
      						) AS Tickets;",
                              array(
                                  $Unit[ 'ID' ]
                              )
                          );
                          ?><div class='col-1'>&nbsp;</div>
                          <div class='col-3 border-bottom border-white my-auto'>En Route</div>
                          <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                              echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                              ?>' /></div>
                          <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>&Status=2';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
      								WHERE  		TicketO.LElev = ?
      										AND TicketO.Assigned = 3
      							)
      						) AS Tickets;",
                              array(
                                  $Unit[ 'ID' ]
                              )
                          );
                          ?><div class='col-1'>&nbsp;</div>
                          <div class='col-3 border-bottom border-white my-auto'>On Site</div>
                          <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                              echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                              ?>' /></div>
                          <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>&Status=3';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
      								WHERE  		TicketO.LElev = ?
      										AND TicketO.Assigned = 6
      							)
      						) AS Tickets;",
                              array(
                                  $Unit[ 'ID' ]
                              )
                          );
                          ?><div class='col-1'>&nbsp;</div>
                          <div class='col-3 border-bottom border-white my-auto'>Review</div>
                          <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                              echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                              ?>' /></div>
                          <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>&Status=6';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
      								WHERE  		TicketO.LElev = ?
      										AND TicketO.Assigned = 4
      							)
      						) AS Tickets;",
                              array(
                                  $Unit[ 'ID' ]
                              )
                          );
                          ?><div class='col-1'>&nbsp;</div>
                          <div class='col-3 border-bottom border-white my-auto'>Complete</div>
                          <div class='col-6'><input class='form-control' type='text' readonly name='Tickets' value='<?php
                              echo $r ? sqlsrv_fetch_array($r)[ 'Tickets' ] : 0;
                              ?>' /></div>
                          <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='tickets.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>&Status=4';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                      </div>
                  </div>
              </div>
              <div class="row"></div>
                  <div class='card card-primary my-3'>
                  <div class='card-heading'>
                      <div class='row g-0 px-3 py-2'>
                          <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Violation( 1 );?><span>Violations</span></h5></div>
                          <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
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
      			WHERE  Violation.Elev = ?
      					AND Violation.Status = 'Preliminary Report'
      		;",array($Unit[ 'ID' ]));
                              echo $r ? sqlsrv_fetch_array($r)['Violations'] : 0;
                              ?>' /></div>
                          <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>&Status=Preliminary Report';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                      </div>
                      <div class='row g-0'>
                          <div class='col-1'>&nbsp;</div>
                          <div class='col-3 border-bottom border-white my-auto'>Job Created</div>
                          <div class='col-6'><input class='form-control' type='text' readonly name='Violations' value='<?php
                              $r = \singleton\database::getInstance( )->query(null,"
      			SELECT Count( Violation.ID ) AS Violations
      			FROM   Violation
      				   LEFT JOIN Loc AS Location ON Violation.Loc = Location.Loc
      			WHERE  Violation.Elev = ?
      					AND Violation.Status = 'Job Created'
      		;",array($Unit[ 'ID' ]));
                              echo $r ? sqlsrv_fetch_array($r)['Violations'] : 0;
                              ?>' /></div>
                          <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='violations.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>&Status=Preliminary Report';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                      </div>
                  </div>
              </div>
              <div class='card card-primary my-3'>
                  <div class='card-heading'>
                      <div class='row g-0 px-3 py-2'>
                        <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?><span>Invoices</span></h5></div>
                        <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='invoices.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                      </div>
                  </div>
                  <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Invoices' ] ) && $_SESSION[ 'Cards' ][ 'Invoices' ] == 0 ? "style='display:none;'" : null;?>>
                      <div class='row g-0'>
                        <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice( 1 );?> Invoices</div>
                        <div class='col-6'>&nbsp;</div>
                        <div class='col-2'>&nbsp;</div>
                      </div>
                      <?php if(isset($Privileges['Invoice'])) {?>
                        <div class='row g-0'>
                          <div class='col-1'>&nbsp;</div>
                          <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Open</div>
                          <div class='col-6'><input class='form-control' type='text' readonly name='Collections' value='<?php
                              $r = \singleton\database::getInstance( )->query(
                                null,
                                " SELECT  Count( OpenAR.Ref ) AS Count
                            			FROM    OpenAR
                                          LEFT JOIN Job ON OpenAR.Job = Job.ID
                            			WHERE   Job.Elev = ?;",
                                array(
                                  $Unit[ 'ID' ]
                                )
                              );
                              $Count = $r ? sqlsrv_fetch_array($r)[ 'Count' ] : 0;
                              echo $Count;
                          ?>' /></div>
                          <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Customer=<?php echo $Customer[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                        </div>
                      <?php }?>
                      <?php if(isset($Privileges['Invoice']) ) {?><div class='row g-0'>
                              <div class='col-1'>&nbsp;</div>
                              <div class='col-3 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Closed</div>
                              <div class='col-6'><input class='form-control' type='text' readonly name='Collections' value='<?php
                                  $r = \singleton\database::getInstance( )->query(
                                    null,
                                    " SELECT 	Count( Invoice.Ref ) AS Count
                                			FROM   	Invoice
                                				   	  LEFT JOIN Job ON OpenAR.Job = Job.ID
                                			WHERE  	   Job.Elev = ?
                                					   AND Invoice.Ref NOT IN ( SELECT Ref FROM OpenAR );",
                                    array(
                                      $Unit[ 'ID' ]
                                    )
                                  );
                                  $Count = $r ? sqlsrv_fetch_array($r)['Count'] : 0;
                                  echo $Count
                                  ?>' /></div>
                              <div class='col-2'><button class='h-100 w-100' onClick="document.location.href='collections.php?Customer=<?php echo $Unit[ 'Customer_Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                    </div><?php }?>
                  </div>
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
