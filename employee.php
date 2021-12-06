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
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'User' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'User' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          	null,
          	" INSERT INTO Activity([User], [Date], [Page] )
            	VALUES( ?, ?, ? );",
          	array(
            	$_SESSION[ 'Connection' ][ 'User' ],
            	date('Y-m-d H:i:s'),
            	'employees.php'
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
        	"	SELECT 	Employee.ID,
        				Employee.fFirst + ' ' + Employee.Last AS Name,
        				Employee.fFirst AS First_Name,
        				Employee.Last   AS Last_Name,
        				Rolodex.Address AS Street,
        				Rolodex.City    AS City,
        				Rolodex.State   AS State,
        				Rolodex.Zip     AS Zip,
        				Rolodex.Latt    AS Latitude,
        				Rolodex.fLong   AS Longitude,
        				Rolodex.Geolock AS Geofence,
        				Rolodex.ID 		AS Rolodex,
        				Rolodex.Name    AS Name,
                        Rolodex.Phone   AS Phone,
                        Rolodex.Email   AS Email,
                        Rolodex.Contact AS Contact,
        				tblWork.Super   AS Supervisor,
        				[User].ID       AS User_ID,
        				[User].Email 	AS User_Email
        		FROM 	dbo.Emp AS Employee
        				LEFT JOIN dbo.tblWork       AS tblWork  ON 'A' + convert(varchar(10), Employee.ID) + ',' = tblWork.Members
        				LEFT JOIN dbo.Rol           AS Rolodex  ON Employee.Rol = Rolodex.ID
                        LEFT JOIN Portal.dbo.[User]             ON [User].Branch_Type = 'Employee' AND [User].Branch_ID = Employee.ID
        		WHERE 	Employee.ID = ?
        				OR Employee.fFirst + ' ' + Employee.Last = ?;",
        	array(
        		$ID,
        		$Name
        	)
        );
        //var_dump( sqlsrv_errors( ) );
        $Employee =   (       empty( $ID )
                        &&    !empty( $Name )
                        &&    !$result
                      ) || (  empty( $ID )
                        &&    empty( $Name )
                      )  ? array(
        	'ID' => null,
        	'Name' => null,
        	'First_Name' => null,
        	'Last_Name' => null,
        	'Sales' => null,
        	'Field' => null,
        	'In_Use' => null,
        	'Status' => null,
        	'Website' => null,
        	'Street' => null,
        	'City' => null,
        	'State' => null,
        	'Zip' => null,
        	'Latitude' => null,
        	'Longitude' => null,
        	'Geofence' => null,
        	'Rolodex' => null,
        	'Supervisor' => null,
        	'Name' => null,
        	'Email' => null,
        	'Phone' => null,
        	'UserEmail' => null,
        ) : sqlsrv_fetch_array($result);

        if( isset( $_POST ) && count( $_POST ) > 0 ){
        	$Employee[ 'First_Name' ]      = isset( $_POST[ 'First_Name' ] )    ? $_POST[ 'First_Name' ]    : $Employee[ 'First_Name' ];
            $Employee[ 'Last_Name' ]        = isset( $_POST[ 'Last_Name' ] )     ? $_POST[ 'Last_Name' ]     : $Employee[ 'Last_Name' ];
            $Employee[ 'Street' ]       = isset( $_POST[ 'Street' ] )    ? $_POST[ 'Street' ]    : $Employee[ 'Street' ];
            $Employee[ 'City' ]        = isset( $_POST[ 'City' ] )     ? $_POST[ 'City' ]     : $Employee[ 'City' ];
            $Employee[ 'State' ]       = isset( $_POST[ 'State' ] )    ? $_POST[ 'State' ]    : $Employee[ 'State' ];
            $Employee[ 'Zip' ]        = isset( $_POST[ 'Zip' ] )     ? $_POST[ 'Zip' ]     : $Employee[ 'Zip' ];
            $Employee[ 'Latitude' ]       = isset( $_POST[ 'Latitude' ] )    ? $_POST[ 'Latitude' ]    : $Employee[ 'Latitude' ];
            $Employee[ 'Longitude' ]        = isset( $_POST[ 'Longitude' ] )     ? $_POST[ 'Longitude' ]     : $Employee[ 'Longitude' ];
        	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
        		$result = \singleton\database::getInstance( )->query(
        			null,
        			"	DECLARE @MAXID INT;
        				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Rol ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Rol ) END ;
        				INSERT INTO Rol(
    						ID,
        					Type,
        					Name,
                            Contact,
        					Website,
        					Address,
        					City,
        					State,
        					Zip,
        					Latt,
        					fLong,
        					Geolock
        				)
        				VALUES( @MAXID + 1 , 5, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
        				SELECT @MAXID + 1;",
        			array(
        				$Employee[ 'First_Name' ] . ' ' . $Employee[ 'Last_Name' ],
                        $Employee[ 'First_Name' ] . ' ' . $Employee[ 'Last_Name' ],
        				'',
        				$Employee[ 'Street' ],
        				$Employee[ 'City' ],
        				$Employee[ 'State' ],
        				$Employee[ 'Zip' ],
        				$Employee[ 'Latitude' ],
        				$Employee[ 'Longitude' ],
        				!is_null( $Employee[ 'Geofence' ] ) ? $Employee[ 'Geofence' ] : 0
        			)
        		);
        		sqlsrv_next_result( $result );
        		$Employee[ 'Rolodex' ] = sqlsrv_fetch_array( $result )[ 0 ];

        		$result = \singleton\database::getInstance( )->query(
        			null,
        			"	DECLARE @MAXID INT;
                        DECLARE @MAXFWORK INT;
        				SET @MAXID = CASE WHEN ( SELECT Max( ID ) FROM Emp ) IS NULL THEN 0 ELSE ( SELECT Max( ID ) FROM Emp ) END ;
                        SET @MAXFWORK = CASE WHEN ( SELECT Max( fWork ) FROM Emp ) IS NULL THEN 1 ELSE ( SELECT Max( fWork ) FROM Emp ) END ;
        				INSERT INTO Emp(
        					ID,
                            fWork,
        					fFirst,
        					Last,
        					Status,
        					Sales,
        					Field,
        					InUse ,
                            Rol
        				)
        				VALUES ( @MAXID + 1, @MAXFWORK + 1, ?, ?, ?, ?, ?, ?, ? );
        				SELECT @MAXID + 1;",
        			array(
        				$Employee[ 'First_Name' ],
        				$Employee[ 'Last_Name' ],
        				$Employee[ 'Status' ],
        				!is_null( $Employee[ 'Sales' ] ) ? $Employee[ 'Sales' ] : 0,
        				!is_null( $Employee[ 'Field' ] ) ? $Employee[ 'Field' ] : 0,
        				!is_null( $Employee[ 'In_Use' ] ) ? $Employee[ 'In_Use' ] : 0,
                        $Employee[ 'Rolodex' ]
        			)
        		);
        		sqlsrv_next_result( $result );
        		$Employee[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];

        		header( 'Location: employee.php?ID=' . $Employee[ 'ID' ] );
        		exit;
        	} else {
        		\singleton\database::getInstance( )->query(
	        		null,
	        		"	UPDATE 	Emp
	        			SET 	Emp.fFirst = ?,
	        					Emp.Last = ?,
	        			WHERE 	Emp.ID = ?;",
	        		array(
	        			$Employee[ 'First_Name' ],
	        			$Employee[ 'Last_Name' ]
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
	        					Rol.fLong = ?
	        			WHERE 	Rol.ID = ?;",
	        		array(
	        			$Employee[ 'Name' ],
	        			'',
	        			$Employee[ 'Street' ],
	        			$Employee[ 'City' ],
	        			$Employee[ 'State' ],
	        			$Employee[ 'Zip' ],
	        			$Employee[ 'Latitude' ],
	        			$Employee[ 'Longitude' ],
	        			$Employee[ 'Rolodex' ]
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
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body>
    <div id="wrapper">
    <?php require(bin_php .'element/navigation.php');?>
    <div id="page-wrapper" class='content' style='height:100%;overflow-y:scroll;'>
      <div class='card card-primary'>
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-6'>
              <h5><?php \singleton\fontawesome::getInstance( )->User( 1 );?><a href='employees.php?<?php
                echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Employees' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Employees' ][ 0 ] : array( ) );
              ?>'>Employee</a>: <span><?php
                echo is_null( $Employee[ 'ID' ] )
                  ? 'New'
                  : $Employee[ 'Name' ];
              ?></span></h5>
            </div>
            <div class='col-2'></div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='employee.php';"
                  >Create</button>
                </div>
                <div class='col-4'>
                  <button
                    class='form-control rounded'
                    onClick="document.location.href='employee.php?ID=<?php echo $Employee[ 'ID' ];?>';"
                  >Refresh</button>
                </div>
              </div>
            </div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='employee.php?ID=<?php echo !is_null( $Employee[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Employees' ], true )[ array_search( $Employee[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Employees' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='employees.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Employees' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Employees' ][ 0 ] : array( ) );?>';">Table</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='employee.php?ID=<?php echo !is_null( $Employee[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Employees' ], true )[ array_search( $Employee[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Employees' ], true ) ) + 1 ] : null;?>';">Next</button></div>

              </div>
            </div>
          </div>
        </div>
        <div class='card-body bg-dark text-white'>
          <div class='card-columns'>
          	<?php if( !in_array( $Employee[ 'Latitude' ], array( null, 0 ) ) && !in_array( $Employee['Longitude' ], array( null, 0 ) ) ){
				?><div class='card card-primary my-3'>
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
			                          center: new google.maps.LatLng( <?php echo $Employee[ 'Latitude' ];?>, <?php echo $Employee[ 'Longitude' ];?> ),
			                          mapTypeId: google.maps.MapTypeId.ROADMAP
			                        }
			                    );
			                    var markers = [];
			                    markers[0] = new google.maps.Marker({
			                        position: {
			                            lat:<?php echo $Employee['Latitude'];?>,
			                            lng:<?php echo $Employee['Longitude'];?>
			                        },
			                        map: map,
			                        title: '<?php echo $Employee[ 'Name' ];?>'
			                    });
			                }
			                $(document).ready(function(){ initialize(); });
			            </script>
				</div><?php
			}?>
            <div class='card card-primary my-3'>
            	<form action='employee.php?ID=<?php echo $Employee[ 'ID' ];?>' method='POST'>
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
              </div>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                <input type='hidden' name='ID' value='<?php echo $Employee[ 'ID' ];?>' />
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>First Name:</div>
                  <div class='col-8'><input placeholder='First Name' type='text' class='form-control edit animation-focus' name='First_Name' value='<?php echo $Employee['First_Name'];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>Last Name:</div>
                  <div class='col-8'><input placeholder='Last Name' type='text' class='form-control edit animation-focus' name='Last_Name' value='<?php echo $Employee['Last_Name'];?>' /></div>
                </div>
                <div class='row g-0'>
					<div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Address:</div>
					<div class='col-6'></div>
					<div class='col-2'><button class='h-100 w-100' type='button' onClick="document.location.href='map.php?Employee=<?php echo $Employee[ 'Name' ];?>';"><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
				</div>
				<div class='row g-0'>
					<div class='col-1'>&nbsp;</div>
					<div class='col-3 border-bottom border-white my-auto'>Street:</div>
					<div class='col-8'><input placeholder='Street' type='text' class='form-control edit' name='Street' value='<?php echo $Employee['Street'];?>' /></div>
				</div>
				<div class='row g-0'>
					<div class='col-1'>&nbsp;</div>
					<div class='col-3 border-bottom border-white my-auto'>City:</div>
					<div class='col-8'><input placeholder='City' type='text' class='form-control edit' name='City' value='<?php echo $Employee['City'];?>' /></div>
				</div>
				<div class='row g-0'>
					<div class='col-1'>&nbsp;</div>
					<div class='col-3 border-bottom border-white my-auto'>State:</div>
					<div class='col-8'><select class='form-control edit' name='State'>
						<option value=''>Select</option>
						<option <?php echo $Employee[ 'State' ] == 'AL' ? 'selected' : null;?> value='AL'>Alabama</option>
						<option <?php echo $Employee[ 'State' ] == 'AK' ? 'selected' : null;?> value='AK'>Alaska</option>
						<option <?php echo $Employee[ 'State' ] == 'AZ' ? 'selected' : null;?> value='AZ'>Arizona</option>
						<option <?php echo $Employee[ 'State' ] == 'AR' ? 'selected' : null;?> value='AR'>Arkansas</option>
						<option <?php echo $Employee[ 'State' ] == 'CA' ? 'selected' : null;?> value='CA'>California</option>
						<option <?php echo $Employee[ 'State' ] == 'CO' ? 'selected' : null;?> value='CO'>Colorado</option>
						<option <?php echo $Employee[ 'State' ] == 'CT' ? 'selected' : null;?> value='CT'>Connecticut</option>
						<option <?php echo $Employee[ 'State' ] == 'DE' ? 'selected' : null;?> value='DE'>Delaware</option>
						<option <?php echo $Employee[ 'State' ] == 'DC' ? 'selected' : null;?> value='DC'>District Of Columbia</option>
						<option <?php echo $Employee[ 'State' ] == 'FL' ? 'selected' : null;?> value='FL'>Florida</option>
						<option <?php echo $Employee[ 'State' ] == 'GA' ? 'selected' : null;?> value='GA'>Georgia</option>
						<option <?php echo $Employee[ 'State' ] == 'HI' ? 'selected' : null;?> value='HI'>Hawaii</option>
						<option <?php echo $Employee[ 'State' ] == 'ID' ? 'selected' : null;?> value='ID'>Idaho</option>
						<option <?php echo $Employee[ 'State' ] == 'IL' ? 'selected' : null;?> value='IL'>Illinois</option>
						<option <?php echo $Employee[ 'State' ] == 'IN' ? 'selected' : null;?> value='IN'>Indiana</option>
						<option <?php echo $Employee[ 'State' ] == 'IA' ? 'selected' : null;?> value='IA'>Iowa</option>
						<option <?php echo $Employee[ 'State' ] == 'KS' ? 'selected' : null;?> value='KS'>Kansas</option>
						<option <?php echo $Employee[ 'State' ] == 'KY' ? 'selected' : null;?> value='KY'>Kentucky</option>
						<option <?php echo $Employee[ 'State' ] == 'LA' ? 'selected' : null;?> value='LA'>Louisiana</option>
						<option <?php echo $Employee[ 'State' ] == 'ME' ? 'selected' : null;?> value='ME'>Maine</option>
						<option <?php echo $Employee[ 'State' ] == 'MD' ? 'selected' : null;?> value='MD'>Maryland</option>
						<option <?php echo $Employee[ 'State' ] == 'MA' ? 'selected' : null;?> value='MA'>Massachusetts</option>
						<option <?php echo $Employee[ 'State' ] == 'MI' ? 'selected' : null;?> value='MI'>Michigan</option>
						<option <?php echo $Employee[ 'State' ] == 'MN' ? 'selected' : null;?> value='MN'>Minnesota</option>
						<option <?php echo $Employee[ 'State' ] == 'MS' ? 'selected' : null;?> value='MS'>Mississippi</option>
						<option <?php echo $Employee[ 'State' ] == 'MO' ? 'selected' : null;?> value='MO'>Missouri</option>
						<option <?php echo $Employee[ 'State' ] == 'MT' ? 'selected' : null;?> value='MT'>Montana</option>
						<option <?php echo $Employee[ 'State' ] == 'NE' ? 'selected' : null;?> value='NE'>Nebraska</option>
						<option <?php echo $Employee[ 'State' ] == 'NV' ? 'selected' : null;?> value='NV'>Nevada</option>
						<option <?php echo $Employee[ 'State' ] == 'NH' ? 'selected' : null;?> value='NH'>New Hampshire</option>
						<option <?php echo $Employee[ 'State' ] == 'NJ' ? 'selected' : null;?> value='NJ'>New Jersey</option>
						<option <?php echo $Employee[ 'State' ] == 'NM' ? 'selected' : null;?> value='NM'>New Mexico</option>
						<option <?php echo $Employee[ 'State' ] == 'NY' ? 'selected' : null;?> value='NY'>New York</option>
						<option <?php echo $Employee[ 'State' ] == 'NC' ? 'selected' : null;?> value='NC'>North Carolina</option>
						<option <?php echo $Employee[ 'State' ] == 'ND' ? 'selected' : null;?> value='ND'>North Dakota</option>
						<option <?php echo $Employee[ 'State' ] == 'OH' ? 'selected' : null;?> value='OH'>Ohio</option>
						<option <?php echo $Employee[ 'State' ] == 'OK' ? 'selected' : null;?> value='OK'>Oklahoma</option>
						<option <?php echo $Employee[ 'State' ] == 'OR' ? 'selected' : null;?> value='OR'>Oregon</option>
						<option <?php echo $Employee[ 'State' ] == 'PA' ? 'selected' : null;?> value='PA'>Pennsylvania</option>
						<option <?php echo $Employee[ 'State' ] == 'RI' ? 'selected' : null;?> value='RI'>Rhode Island</option>
						<option <?php echo $Employee[ 'State' ] == 'SC' ? 'selected' : null;?> value='SC'>South Carolina</option>
						<option <?php echo $Employee[ 'State' ] == 'SD' ? 'selected' : null;?> value='SD'>South Dakota</option>
						<option <?php echo $Employee[ 'State' ] == 'TN' ? 'selected' : null;?> value='TN'>Tennessee</option>
						<option <?php echo $Employee[ 'State' ] == 'TX' ? 'selected' : null;?> value='TX'>Texas</option>
						<option <?php echo $Employee[ 'State' ] == 'UT' ? 'selected' : null;?> value='UT'>Utah</option>
						<option <?php echo $Employee[ 'State' ] == 'VT' ? 'selected' : null;?> value='VT'>Vermont</option>
						<option <?php echo $Employee[ 'State' ] == 'VA' ? 'selected' : null;?> value='VA'>Virginia</option>
						<option <?php echo $Employee[ 'State' ] == 'WA' ? 'selected' : null;?> value='WA'>Washington</option>
						<option <?php echo $Employee[ 'State' ] == 'WV' ? 'selected' : null;?> value='WV'>West Virginia</option>
						<option <?php echo $Employee[ 'State' ] == 'WI' ? 'selected' : null;?> value='WI'>Wisconsin</option>
						<option <?php echo $Employee[ 'State' ] == 'WY' ? 'selected' : null;?> value='WY'>Wyoming</option>
					</select></div>
				</div>
				<div class='row g-0'>
					<div class='col-1'>&nbsp;</div>
					<div class='col-3 border-bottom border-white my-auto'>Zip:</div>
					<div class='col-8'><input placeholder='Zip' type='text' class='form-control edit' name='Zip' value='<?php echo $Employee['Zip'];?>' /></div>
				</div>
				<div class='row g-0'>
					<div class='col-1'>&nbsp;</div>
					<div class='col-3 border-bottom border-white my-auto'>Latitude:</div>
					<div class='col-8'><input placeholder='Latitude' type='text' class='form-control edit <?php echo $Employee[ 'Latitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Latitude' value='<?php echo $Employee['Latitude'];?>' /></div>
				</div>
				<div class='row g-0'>
					<div class='col-1'>&nbsp;</div>
					<div class='col-3 border-bottom border-white my-auto'>Longitude:</div>
					<div class='col-8'><input placeholder='Longitude' type='text' class='form-control edit <?php echo $Employee[ 'Longitude' ] != 0 ? 'bg-success' : 'bg-warning';?>' name='Longitude' value='<?php echo $Employee['Longitude'];?>' /></div>
				</div>
				</div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                  </div>
              </div>
        </form></div>

        <div class='card card-primary my-3'><form action='employee.php?ID=<?php echo $Employee[ 'ID' ];?>' method='POST'>

           <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->User( 1 );?><span>User</span></h5></div>
                   <div class="col-2"><button class="h-100 w-100" onclick="document.location.href='user.php?ID=<?php echo $Employee[ 'User_ID' ];?>';"><i class="fa fa-search fa-fw fa-1x" aria-hidden="true"></i></button></div>
                </div>
              </div>
          <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'User' ] ) && $_SESSION[ 'Cards' ][ 'User' ] == 0 ? "style='display:none;'" : null;?>>
            <input type='hidden' name='ID' value='<?php echo $Employee[ 'ID' ];?>' />
            <div class='row g-0'>
              <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>Email:</div>
              <div class='col-8'><input placeholder='email@domain.com' type='text' class='form-control edit animation-focus' name='First_Name' value='<?php echo $Employee['UserEmail'];?>' /></div>
            </div>
            <div class='row g-0'>
              <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?>Employee ID:</div>
              <div class='col-8'><input placeholder='000000' type='text' class='form-control edit animation-focus' name='ID' value='<?php echo $Employee['ID'];?>' /></div>
            </div>
          </div>
           <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                  </div>
              </div>
        </form>
      </div>

       <div class='card card-primary my-3'>
       	<form action='employee.php?ID=<?php echo $Employee[ 'ID' ];?>' method='POST'>
                <input type='hidden' name='ID' value='<?php echo $Employee[ 'ID' ];?>' />
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->User( 1 );?><span>Contact</span></h5></div>
                   <div class="col-2"><button class="h-100 w-100" onclick="document.location.href='contact.php?ID=<?php echo $Employee[ 'Rolodex' ];?>';"><i class="fa fa-search fa-fw fa-1x" aria-hidden="true"></i></button></div>
                </div>
              </div>
              <div class='card-body bg-dark'>
                <div class='row'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->User( 1 );?> Name:</div>
                  <div class='col-8'><input placeholder='Name' type='text' class='form-control edit' name='Name' value='<?php echo $Employee[ 'Name' ];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Phone( 1 );?> Phone:</div>
                  <div class='col-8'><input placeholder='(XXX) XXX-XXXX' type='text' class='form-control edit' name='Phone' value='<?php echo $Employee[ 'Phone' ];?>' /></div>
                </div>
                <div class='row'>
                  <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Email( 1 );?> Email:</div>
                  <div class='col-8'><input placeholder='email@domain.com' type='text' class='form-control edit' name='Email' value='<?php echo $Employee[ 'Email' ];?>' /></div>
                </div>
              </div>
              <div class='card-footer'>
                  <div class='row'>
                      <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
                  </div>
              </div>
            </form>
            </div>

            <div class='card card-primary my-3'>
       	<form action='employee.php?ID=<?php echo $Employee[ 'ID' ];?>' method='POST'>
                <input type='hidden' name='ID' value='<?php echo $Employee[ 'ID' ];?>' />
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->User( 1 );?><span>Paid Time Off</span></h5></div>
                   <div class="col-2"></div>
                </div>
              </div>
            </form>
          </div>
    </div>
  </div>
</body>
</html>
 <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=employee.php';</script></head></html><?php }?>
