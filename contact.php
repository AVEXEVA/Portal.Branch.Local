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
        ||  !isset( $Privileges[ 'Contact' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Contact' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'contact.php'
        )
      );
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
          "	SELECT 	Top 1
                Contact.*
            FROM    (
                  SELECT 	Rol.ID 		AS ID,
                          Rol.Name    AS Name,
                          Rol.Contact AS Contact,
                          Rol.Type    AS Type,
                          Rol.Phone   AS Phone,
                          Rol.Email   AS Email,
                          Rol.Address AS Street,
                          Rol.City    AS City,
                          Rol.State   AS State,
                          Rol.Zip     AS Zip,
                          Rol.Latt 	AS Latitude,
                          Rol.fLong   AS Longitude,
                          Rol.Website AS Website,
                          Rol.Geolock AS Geofence
                FROM      Rol
                    ) AS Contact
            WHERE   	Contact.ID = ?
                OR 	Contact.Name = ?;",
          array(
            $ID,
            $Name
          )
      );
      $Contact =   (       empty( $ID )
                      &&    !empty( $Name )
                      &&    !$result
                    ) || (  empty( $ID )
                      &&    empty( $Name )
                    )  ? array(
        'ID' => null,
        'Name' => null,
        'Contact' => null,
        'Geofence' => null,
        'Type' => null,
        'Status' => null,
        'Website' => null,
        'Internet' => null,
        'Street' => null,
        'City' => null,
        'State' => null,
        'Zip' => null,
        'Latitude' => null,
        'Longitude' => null,
        'Phone'   =>  null,
        'Email'   =>  null,
        'Rolodex' => null,
        'Phone' => null,
        'Email' => null
      ) : sqlsrv_fetch_array($result);


      if( isset( $_POST ) && count( $_POST ) > 0 ){
        $Contact[ 'Name' ] 		= isset( $_POST[ 'Name' ] ) 	 ? $_POST[ 'Name' ] 	 : $Contact[ 'Name' ];
        $Contact[ 'Contact' ] 	= isset( $_POST[ 'Contact' ] ) ? $_POST[ 'Contact' ] : $Contact[ 'Contact' ];
        $Contact[ 'Phone' ] 		= isset( $_POST[ 'Phone' ] ) 	 ? $_POST[ 'Phone' ] 	 : $Contact[ 'Phone' ];
        $Contact[ 'Email' ] 		= isset( $_POST[ 'Email' ] ) 	 ? $_POST[ 'Email' ] 	 : $Contact[ 'Email' ];
        $Contact[ 'Type' ]     = isset( $_POST[ 'Type' ] ) 	   ? $_POST[ 'Type' ] 	   : $Contact[ 'Type' ];
        $Contact[ 'Website' ] 	= isset( $_POST[ 'Website' ] ) 	 ? $_POST[ 'Website' ] 	 : $Contact[ 'Website' ];
        $Contact[ 'Street' ] 	= isset( $_POST[ 'Street' ] ) 	 ? $_POST[ 'Street' ] 	 : $Contact[ 'Street' ];
        $Contact[ 'City' ] 		= isset( $_POST[ 'City' ] ) 	 ? $_POST[ 'City' ] 	 : $Contact[ 'City' ];
        $Contact[ 'State' ] 		= isset( $_POST[ 'State' ] ) 	 ? $_POST[ 'State' ] 	 : $Contact[ 'State' ];
        $Contact[ 'Zip' ] 			= isset( $_POST[ 'Zip' ] ) 		 ? $_POST[ 'Zip' ] 		 : $Contact[ 'Zip' ];
        $Contact[ 'Latitude' ] 	= isset( $_POST[ 'Latitude' ] )  ? $_POST[ 'Latitude' ]  : $Contact[ 'Latitude' ];
        $Contact[ 'Longitude' ] 	= isset( $_POST[ 'Longitude' ] ) ? $_POST[ 'Longitude' ] : $Contact[ 'Longitude' ];

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
                Phone,
                Contact,
                Email,
                Address,
                City,
                State,
                Zip,
                Latt,
                fLong,
                Geolock
              )
              VALUES( @MAXID + 1 , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
              SELECT @MAXID + 1;",
            array(
              $Contact[ 'Type' ],
              $Contact[ 'Name' ],
              $Contact[ 'Website' ],
              $Contact[ 'Phone' ],
              $Contact[ 'Contact'],
              $Contact[ 'Email' ],
              $Contact[ 'Street' ],
              $Contact[ 'City' ],
              $Contact[ 'State' ],
              $Contact[ 'Zip' ],
              $Contact[ 'Latitude' ],
              $Contact[ 'Longitude' ],
              !is_null( $Contact[ 'Geofence' ] ) ? $Contact[ 'Geofence' ] : 0
            )
          );
          sqlsrv_next_result( $result );
          $Contact[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];

          header( 'Location: contact.php?ID=' . $Contact[ 'ID' ] );
          exit;
        } else {
          \singleton\database::getInstance( )->query(
            null,
            "	UPDATE 	Rol
              SET 	Rol.Name = ?,
                  Rol.Contact = ?,
                  Rol.Type = ?,
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
              $Contact[ 'Name' ],
              $Contact[ 'Contact' ],
              $Contact[ 'Type' ],
              $Contact[ 'Website' ],
              $Contact[ 'Street' ],
              $Contact[ 'City' ],
              $Contact[ 'State' ],
              $Contact[ 'Zip' ],
              $Contact[ 'Latitude' ],
              $Contact[ 'Longitude' ],
              $Contact[ 'Phone' ],
              $Contact[ 'Email' ],
              $Contact[ 'ID' ]
            )
          );
        }
      }
?><!DOCTYPE html>
<html lang='en'>
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php
    	$_GET[ 'Bootstrap' ] = '5.1';
      $_GET[ 'Entity_CSS' ] = 1;
      	require( bin_meta . 'index.php');
      	require( bin_css  . 'index.php');
      	require( bin_js   . 'index.php');
    ?>
</head>
<body>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php' ); ?>
        <div id="page-wrapper" class='content'>
        	<div class='card card-primary'>
        		<div class='card-heading'>
        			<div class='row g-0 px-3 py-2'>
        				<div class='col-6'><h5><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?><a href='contacts.php?<?php echo isset( $_SESSION[ 'Tables' ][ 'Contacts' ][ 0 ] ) ? http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Contacts' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Contacts' ][ 0 ] : array( ) ) : null;?>'>Contact</a>: <span><?php echo is_null( $Contact[ 'ID' ] ) ? 'New' : $Contact[ 'Name' ];?></span></h5></div>
        				<div class='col-2'></div>
        				<div class='col-2'>
        					<div class='row g-0'>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='contact.php';">Create</button></div>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='contact.php?ID=<?php echo $Contact[ 'ID' ];?>';">Refresh</button></div>
        					</div>
        				</div>
        				<div class='col-2'>
        					<div class='row g-0'>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='contact.php?ID=<?php echo !is_null( $Contact[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Contacts' ], true )[ array_search( $Contact[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Contacts' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='contacts.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Contacts' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Contacts' ][ 0 ] : array( ) );?>';">Table</button></div>
        						<div class='col-4'><button class='form-control rounded' onClick="document.location.href='contact.php?ID=<?php echo !is_null( $Contact[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Contacts' ], true )[ array_search( $Contact[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Contacts' ], true ) ) + 1 ] : null;?>';">Next</button></div>
        					</div>
        				</div>
        			</div>
        		</div>
        		<div class='card-body bg-dark text-white'>
					<div class='card-columns'>
						<div class='card card-primary my-3'><form action='contact.php?ID=<?php echo $Contact[ 'ID' ];?>' method='POST'>
							<div class='card-heading'>
								<div class='row g-0 px-3 py-2'>
									<div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
									<div class='col-2'>&nbsp;</div>
								</div>
							</div>
						 	<div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
						 		<input type='hidden' name='ID' value='<?php echo $Contact[ 'ID' ];?>' />
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Name:</div>
									<div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Contact['Name'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Type:</div>
									<div class='col-8'><select name='Type' class='form-control edit'>
										<option value=''>Select</option>
										<option value='0' <?php echo $Contact[ 'Type' ] == 0 ? 'selected' : null;?>>Customer</option>
										<option value='4' <?php echo $Contact[ 'Type' ] == 4 ? 'selected' : null;?>>Location</option>
                    <option value='5' <?php echo $Contact[ 'Type' ] == 5 ? 'selected' : null;?>>Employee</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
									<div class='col-8'><select name='Status' class='form-control edit <?php echo $Contact[ 'Status' ] == 1 ? 'bg-warning' : 'bg-success';?>'>
										<option value=''>Select</option>
										<option value='0' <?php echo $Contact[ 'Status' ] == 1 ? 'selected' : null;?>>Inactive</option>
										<option value='1' <?php echo $Contact[ 'Status' ] == 0 ? 'selected' : null;?>>Active</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Web(1);?> Website:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Website' value='<?php echo strlen($Contact['Website']) > 0 ?  $Contact['Website'] : "&nbsp;";?>' /></div>
								</div>
                <div class='row'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->User( 1 );?> Contact:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Contact' value='<?php echo $Contact[ 'Contact' ];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-4 border-bottom border-white my-auto' ><?php \singleton\fontawesome::getInstance( )->Phone( 1 );?> Phone:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Phone' value='<?php echo $Contact[ 'Phone' ];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Email( 1 );?> Email:</div>
                  <div class='col-8'><input type='text' class='form-control edit' name='Email' value='<?php echo $Contact[ 'Email' ];?>' /></div>
                </div>
								<div class='row g-0'>
									<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Address:</div>
									<div class='col-6'></div>
									<div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?contact=<?php echo $Contact[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Street:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Street' value='<?php echo $Contact['Street'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>City:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='City' value='<?php echo $Contact['City'];?>' /></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>State:</div>
									<div class='col-8'><select class='form-control edit' name='State'>
										<option value=''>Select</option>
										<option <?php echo $Contact[ 'State' ] == 'AL' ? 'selected' : null;?> value='AL'>Alabama</option>
										<option <?php echo $Contact[ 'State' ] == 'AK' ? 'selected' : null;?> value='AK'>Alaska</option>
										<option <?php echo $Contact[ 'State' ] == 'AZ' ? 'selected' : null;?> value='AZ'>Arizona</option>
										<option <?php echo $Contact[ 'State' ] == 'AR' ? 'selected' : null;?> value='AR'>Arkansas</option>
										<option <?php echo $Contact[ 'State' ] == 'CA' ? 'selected' : null;?> value='CA'>California</option>
										<option <?php echo $Contact[ 'State' ] == 'CO' ? 'selected' : null;?> value='CO'>Colorado</option>
										<option <?php echo $Contact[ 'State' ] == 'CT' ? 'selected' : null;?> value='CT'>Connecticut</option>
										<option <?php echo $Contact[ 'State' ] == 'DE' ? 'selected' : null;?> value='DE'>Delaware</option>
										<option <?php echo $Contact[ 'State' ] == 'DC' ? 'selected' : null;?> value='DC'>District Of Columbia</option>
										<option <?php echo $Contact[ 'State' ] == 'FL' ? 'selected' : null;?> value='FL'>Florida</option>
										<option <?php echo $Contact[ 'State' ] == 'GA' ? 'selected' : null;?> value='GA'>Georgia</option>
										<option <?php echo $Contact[ 'State' ] == 'HI' ? 'selected' : null;?> value='HI'>Hawaii</option>
										<option <?php echo $Contact[ 'State' ] == 'ID' ? 'selected' : null;?> value='ID'>Idaho</option>
										<option <?php echo $Contact[ 'State' ] == 'IL' ? 'selected' : null;?> value='IL'>Illinois</option>
										<option <?php echo $Contact[ 'State' ] == 'IN' ? 'selected' : null;?> value='IN'>Indiana</option>
										<option <?php echo $Contact[ 'State' ] == 'IA' ? 'selected' : null;?> value='IA'>Iowa</option>
										<option <?php echo $Contact[ 'State' ] == 'KS' ? 'selected' : null;?> value='KS'>Kansas</option>
										<option <?php echo $Contact[ 'State' ] == 'KY' ? 'selected' : null;?> value='KY'>Kentucky</option>
										<option <?php echo $Contact[ 'State' ] == 'LA' ? 'selected' : null;?> value='LA'>Louisiana</option>
										<option <?php echo $Contact[ 'State' ] == 'ME' ? 'selected' : null;?> value='ME'>Maine</option>
										<option <?php echo $Contact[ 'State' ] == 'MD' ? 'selected' : null;?> value='MD'>Maryland</option>
										<option <?php echo $Contact[ 'State' ] == 'MA' ? 'selected' : null;?> value='MA'>Massachusetts</option>
										<option <?php echo $Contact[ 'State' ] == 'MI' ? 'selected' : null;?> value='MI'>Michigan</option>
										<option <?php echo $Contact[ 'State' ] == 'MN' ? 'selected' : null;?> value='MN'>Minnesota</option>
										<option <?php echo $Contact[ 'State' ] == 'MS' ? 'selected' : null;?> value='MS'>Mississippi</option>
										<option <?php echo $Contact[ 'State' ] == 'MO' ? 'selected' : null;?> value='MO'>Missouri</option>
										<option <?php echo $Contact[ 'State' ] == 'MT' ? 'selected' : null;?> value='MT'>Montana</option>
										<option <?php echo $Contact[ 'State' ] == 'NE' ? 'selected' : null;?> value='NE'>Nebraska</option>
										<option <?php echo $Contact[ 'State' ] == 'NV' ? 'selected' : null;?> value='NV'>Nevada</option>
										<option <?php echo $Contact[ 'State' ] == 'NH' ? 'selected' : null;?> value='NH'>New Hampshire</option>
										<option <?php echo $Contact[ 'State' ] == 'NJ' ? 'selected' : null;?> value='NJ'>New Jersey</option>
										<option <?php echo $Contact[ 'State' ] == 'NM' ? 'selected' : null;?> value='NM'>New Mexico</option>
										<option <?php echo $Contact[ 'State' ] == 'NY' ? 'selected' : null;?> value='NY'>New York</option>
										<option <?php echo $Contact[ 'State' ] == 'NC' ? 'selected' : null;?> value='NC'>North Carolina</option>
										<option <?php echo $Contact[ 'State' ] == 'ND' ? 'selected' : null;?> value='ND'>North Dakota</option>
										<option <?php echo $Contact[ 'State' ] == 'OH' ? 'selected' : null;?> value='OH'>Ohio</option>
										<option <?php echo $Contact[ 'State' ] == 'OK' ? 'selected' : null;?> value='OK'>Oklahoma</option>
										<option <?php echo $Contact[ 'State' ] == 'OR' ? 'selected' : null;?> value='OR'>Oregon</option>
										<option <?php echo $Contact[ 'State' ] == 'PA' ? 'selected' : null;?> value='PA'>Pennsylvania</option>
										<option <?php echo $Contact[ 'State' ] == 'RI' ? 'selected' : null;?> value='RI'>Rhode Island</option>
										<option <?php echo $Contact[ 'State' ] == 'SC' ? 'selected' : null;?> value='SC'>South Carolina</option>
										<option <?php echo $Contact[ 'State' ] == 'SD' ? 'selected' : null;?> value='SD'>South Dakota</option>
										<option <?php echo $Contact[ 'State' ] == 'TN' ? 'selected' : null;?> value='TN'>Tennessee</option>
										<option <?php echo $Contact[ 'State' ] == 'TX' ? 'selected' : null;?> value='TX'>Texas</option>
										<option <?php echo $Contact[ 'State' ] == 'UT' ? 'selected' : null;?> value='UT'>Utah</option>
										<option <?php echo $Contact[ 'State' ] == 'VT' ? 'selected' : null;?> value='VT'>Vermont</option>
										<option <?php echo $Contact[ 'State' ] == 'VA' ? 'selected' : null;?> value='VA'>Virginia</option>
										<option <?php echo $Contact[ 'State' ] == 'WA' ? 'selected' : null;?> value='WA'>Washington</option>
										<option <?php echo $Contact[ 'State' ] == 'WV' ? 'selected' : null;?> value='WV'>West Virginia</option>
										<option <?php echo $Contact[ 'State' ] == 'WI' ? 'selected' : null;?> value='WI'>Wisconsin</option>
										<option <?php echo $Contact[ 'State' ] == 'WY' ? 'selected' : null;?> value='WY'>Wyoming</option>
									</select></div>
								</div>
								<div class='row g-0'>
									<div class='col-1'>&nbsp;</div>
									<div class='col-3 border-bottom border-white my-auto'>Zip:</div>
									<div class='col-8'><input type='text' class='form-control edit' name='Zip' value='<?php echo $Contact['Zip'];?>' /></div>
								</div>
							</div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                  </div>
              </div>
						</form></div>
            </div>
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
} else {?><html><head><script>document.location.href="../login.php?Forward=contact<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
