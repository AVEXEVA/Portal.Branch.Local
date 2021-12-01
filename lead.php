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
        ||  !isset( $Privileges[ 'Lead' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Lead' ] )
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
        	"	SELECT TOP 1
                        [Lead].ID            AS ID,
                        [Lead].fDesc         AS Name,
                     	  [Lead].RolType       AS RolType,
                        [Lead].Rol           AS Rol,
                        [Lead].Type          AS Type,
                        [Lead].Address       AS Street,
                        [Lead].City          AS City,
                        [Lead].State 		     AS State,
                        [Lead].Zip 		       AS Zip,
                        [Lead].Owner         AS Customer_ID,
                        Customer.Name        AS Customer_Name,
                        [Lead].Status 	     AS Status,
                        [Lead].Probability 	 AS Probability,
                        [Lead].Level 	       AS Level,
                        [Lead].Revenue 		   AS Revenue,
                        [Lead].Cost 		     AS Cost,
                        [Lead].Labor  		   AS Labor,
                        [Lead].Profit        AS Profit,
                        [Lead].Ratio         AS Ratio,
                        [Lead].Remarks  	   AS Remarks,
                        [Lead].Latt          AS Latitude,
                        [Lead].fLong         AS Longitude,
                        [Lead].GeoLock       AS GeoLock,
                        [Lead].Country  	   AS Country
                FROM    [Lead] AS [Lead]
                        LEFT JOIN (
                          SELECT Owner.ID,
                                 Rol.Name
                          FROM   dbo.Owner
                                 LEFT JOIN Rol ON Owner.Rol = Rol.ID
                        ) AS Customer ON Customer.ID = [Lead].Owner
                WHERE 		[Lead].ID = ?;",
            array(
            	$ID,
            	$Name
            )
        );
        $Lead = in_array( $ID, array( null, 0, '', ' ' ) ) || !$result ? array(
        	'ID' => null,
        	'Name' => null,
        	'RolType' => null,
        	'Rol' => null,
        	'Type' => null,
        	'Street' => null,
        	'City' => null,
        	'State' => null,
        	'Zip' => null,
        	'Owner' => null,
        	'Status' => null,
        	'Probability' => null,
        	'Level' => null,
        	'Revenue' => null,
        	'Cost' => null,
        	'Labor' => null,
        	'Profit' => null,
        	'Ratio' => null,
        	'Remarks' => null,
        	'Latitude' => null,
        	'Longitude' => null,
        	'GeoLock' => null,
          'Country' => null,
          'Customer_ID' => null,
          'Customer_Name' => null
        ) : sqlsrv_fetch_array( $result );


        if( isset( $_POST ) && count( $_POST ) > 0 ){
          $Lead[ 'Name' ] 	= isset( $_POST[ 'Name' ] ) 		? $_POST[ 'Name' ] 			: $Lead[ 'Name' ];
          $Lead[ 'RolType' ] 	= isset( $_POST[ 'RolType' ] ) 		? $_POST[ 'RolType' ] 			: $Lead[ 'RolType' ];
          $Lead[ 'Rol' ] 	= isset( $_POST[ 'Rol' ] ) 		? $_POST[ 'Rol' ] 			: $Lead[ 'Rol' ];
          $Lead[ 'Type' ] 	= isset( $_POST[ 'Type' ] ) 		? $_POST[ 'Type' ] 			: $Lead[ 'Type' ];
          $Lead[ 'Street' ] 	= isset( $_POST[ 'Street' ] ) 		? $_POST[ 'Street' ] 			: $Lead[ 'Street' ];
          $Lead[ 'City' ] 	= isset( $_POST[ 'City' ] ) 		? $_POST[ 'City' ] 			: $Lead[ 'City' ];
          $Lead[ 'State' ] 	= isset( $_POST[ 'State' ] ) 		? $_POST[ 'State' ] 			: $Lead[ 'State' ];
          $Lead[ 'Zip' ] 	= isset( $_POST[ 'Zip' ] ) 		? $_POST[ 'Zip' ] 			: $Lead[ 'Zip' ];
          $Lead[ 'Status' ] 	= isset( $_POST[ 'Status' ] ) 		? $_POST[ 'Status' ] 			: $Lead[ 'Status' ];
          $Lead[ 'Probability' ] 	= isset( $_POST[ 'Probability' ] ) 	? $_POST[ 'Probability' ]	: $Lead[ 'Probability' ];
          $Lead[ 'Level' ] 	= isset( $_POST[ 'Level' ] ) 		? $_POST[ 'Level' ] 			: $Lead[ 'Level' ];
          $Lead[ 'Revenue' ] 	= isset( $_POST[ 'Revenue' ] ) 		? $_POST[ 'Revenue' ] 			: $Lead[ 'Revenue' ];
          $Lead[ 'Cost' ] 	= isset( $_POST[ 'Cost' ] ) 		? $_POST[ 'Cost' ] 			: $Lead[ 'Cost' ];
          $Lead[ 'Labor' ] 	= isset( $_POST[ 'Labor' ] ) 		? $_POST[ 'Labor' ] 			: $Lead[ 'Labor' ];
        	$Lead[ 'Profit' ] 	= isset( $_POST[ 'Profit' ] ) 		? $_POST[ 'Profit' ] 		: $Lead[ 'Profit' ];
        	$Lead[ 'Ratio' ] 	= isset( $_POST[ 'Ratio' ] ) 		? $_POST[ 'Ratio' ] 		: $Lead[ 'Ratio' ];
        	$Lead[ 'Remarks' ] 	= isset( $_POST[ 'Remarks' ] ) 		? $_POST[ 'Remarks' ] 			: $Lead[ 'Remarks' ];
        	$Lead[ 'Latitude' ] 	= isset( $_POST[ 'Latitude' ] ) 		? $_POST[ 'Latitude' ] 		: $Lead[ 'Latitude' ];
        	$Lead[ 'Longitude' ] 		= isset( $_POST[ 'Longitude' ] ) 			? $_POST[ 'Longitude' ] 			: $Lead[ 'Longitude' ];
        	$Lead[ 'GeoLock'] 	= isset( $_POST[ 'GeoLock' ] )		? $_POST[ 'GeoLock' ]  	: $Lead[ 'GeoLock' ];
        	$Lead[ 'Country'] = isset( $_POST[ 'Country' ] )	? $_POST[ 'Country' ] 	: $Lead[ 'Country' ];
          $Lead[ 'Customer_Name' ] 	= isset( $_POST[ 'Customer' ] ) 		? $_POST[ 'Customer' ] 			: $Lead[ 'Customer_Name' ];

        	if( in_array( $_POST[ 'ID' ], array( null, 0, '', ' ' ) ) ){
        		$result = \singleton\database::getInstance( )->query(
    	    		null,
    	    		"	DECLARE @MAXID INT;
    	    			DECLARE @Customer INT;
            			SET @MAXID = CASE WHEN ( SELECT Max( Lead.ID ) FROM dbo.Lead ) IS NULL THEN 0 ELSE ( SELECT Max( Lead.ID ) FROM dbo.Lead ) END;
            			SET @Customer = ( SELECT Top 1 Owner.ID FROM dbo.Owner LEFT JOIN dbo.Rol ON Owner.Rol = Rol.ID WHERE Rol.Name = ? );
            			INSERT INTO dbo.Lead( ID, Owner, fDesc, RolType, Rol, Type, Address, City, Zip, Status, Probability, Level, Revenue, Cost, Labor, Profit, Ratio, Remarks, Latt, fLong, Country, GeoLock )
    	    			VALUES( @MAXID + 1, @Customer, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? );
            			SELECT @MAXID + 1;",
    	    		array(
                $Lead[ 'Customer_Name' ],
                $Lead[ 'Name' ],
                $Lead[ 'RolType' ],
                $Lead[ 'Rol' ],
                $Lead[ 'Type' ],
                $Lead[ 'Street' ],
                $Lead[ 'City' ],
    	    			$Lead[ 'Zip' ],
    	    			$Lead[ 'Status' ],
    	    			$Lead[ 'Probability' ],
    	    			$Lead[ 'Level' ],
    	    			$Lead[ 'Revenue' ],
    	    			$Lead[ 'Cost' ],
    	    			$Lead[ 'Labor' ],
                $Lead[ 'Profit' ],
                $Lead[ 'Ratio' ],
                $Lead[ 'Remarks' ],
                $Lead[ 'Latitude' ],
                $Lead[ 'Longitude' ],
                $Lead[ 'Country' ],
                !empty( $Lead[ 'GeoLock' ] ) ? $Lead[ 'GeoLock' ] : 0
    	    		)
    	    	);
            var_dump(sqlsrv_errors( ) );
            sqlsrv_next_result( $result );
            $Lead[ 'ID' ] = sqlsrv_fetch_array( $result )[ 0 ];
          //  header( 'Location: lead.php?ID=' . $Lead[ 'ID' ] );
        	} else {
    	    	\singleton\database::getInstance( )->query(
    	    		null,
    	    		"	UPDATE 	[Lead]
    	    			SET   [Lead].fDesc = ?,
    	                [Lead].RolType = ?,
    	      					[Lead].Rol = ?,
    	      					[Lead].Type = ?,
    	      					[Lead].Address = ?,
    	      					[Lead].City = ?,
    	      					[Lead].State = ?,
    	      					[Lead].Zip = ?,
    	      					[Lead].Owner = (
    	      						SELECT 	ID
    	      						FROM 	(
                                SELECT  Owner.ID,
                                        Rol.Name,
                                        Owner.Status
                                FROM    Owner
                                        LEFT JOIN Rol ON Owner.Rol = Rol.ID
                              ) AS Customer
    	      						WHERE 	Customer.Name = ?
    	      					),
                      [Lead].Status = ?,
                      [Lead].Probability = ?,
                      [Lead].Level = ?,
                      [Lead].Revenue = ?,
                      [Lead].Cost = ?,
                      [Lead].Labor = ?,
                      [Lead].Profit = ?,
                      [Lead].Ratio = ?,
                      [Lead].Remarks = ?,
                      [Lead].Latt = ?,
                      [Lead].fLong = ?,
                      [Lead].GeoLock = ?,
                      [Lead].Country = ?
    	    			WHERE 	[Lead].ID= ?;",
    	    		array(
                $Lead[ 'Name' ],
                $Lead[ 'RolType' ],
                $Lead[ 'Rol' ],
                $Lead[ 'Type' ],
    	    			$Lead[ 'Street' ],
    	    			$Lead[ 'City' ],
    	    			$Lead[ 'State' ],
    	    			$Lead[ 'Zip' ],
                $Lead[ 'Customer_Name' ],
    	    			$Lead[ 'Status' ],
    	    			$Lead[ 'Probability' ],
    	    			$Lead[ 'Level' ],
    	    			$Lead[ 'Revenue' ],
                $Lead[ 'Cost' ],
                $Lead[ 'Labor' ],
                $Lead[ 'Profit' ],
                $Lead[ 'Ratio' ],
                $Lead[ 'Remarks' ],
                $Lead[ 'Latitude' ],
                $Lead[ 'Longitude' ],
                !empty( $Lead[ 'GeoLock' ] ) ? $Lead[ 'GeoLock' ] : 0,
                $Lead[ 'Country' ],
                $Lead[ 'ID' ]
    	    		)
    	    	);
    	    }
        }
?><!DOCTYPE html>
<html lang="en" style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
     <?php  $_GET[ 'Bootstrap' ] = '5.1';?>
     <?php  $_GET[ 'Entity_CSS' ] = 1;?>
     <?php  require( bin_meta . 'index.php');?>
     <?php  require( bin_css  . 'index.php');?>
     <?php  require( bin_js   . 'index.php');?>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyCNrTryEaTEDRz-XDSg890ajL_JRPnLgzc"></script>
</head>
<body onload='finishLoadingPage();'>
  <div id="wrapper">
    <?php require( bin_php . 'element/navigation.php'); ?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'>
        <div class='card-heading'>
          <div class='row g-0 px-3 py-2'>
            <div class='col-6'><h5><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?><a href='leads.php?<?php echo isset( $_SESSION[ 'Tables' ][ 'Customer' ][ 0 ] ) ? http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Leads' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Leads' ][ 0 ] : array( ) ) : null;?>'>Lead</a>: <span><?php echo is_null( $Lead[ 'ID' ] ) ? 'New' : $Lead[ 'Name' ];?></span></h5></div>
            <div class='col-2'></div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='lead.php';">Create</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='lead.php?ID=<?php echo $Lead[ 'ID' ];?>';">Refresh</button></div>
              </div>
            </div>
            <div class='col-2'>
              <div class='row g-0'>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='lead.php?ID=<?php echo !is_null( $Lead[ 'ID' ] ) ? array_keys( $_SESSION[ 'Tables' ][ 'Leads' ], true )[ array_search( $Lead[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Leads' ], true ) ) - 1 ] : null;?>';">Previous</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='leads.php?<?php echo http_build_query( is_array( $_SESSION[ 'Tables' ][ 'Leads' ][ 0 ] ) ? $_SESSION[ 'Tables' ][ 'Leads' ][ 0 ] : array( ) );?>';">Table</button></div>
                <div class='col-4'><button class='form-control rounded' onClick="document.location.href='lead.php?ID=<?php echo !is_null( $Lead[ 'ID' ] )? array_keys( $_SESSION[ 'Tables' ][ 'Leads' ], true )[ array_search( $Lead[ 'ID' ], array_keys( $_SESSION[ 'Tables' ][ 'Leads' ], true ) ) + 1 ] : null;?>';">Next</button></div>
              </div>
            </div>
          </div>
        </div>
        <div class='card-body bg-dark text-white'>
          <div class='card-columns'>
        		<div class='card card-primary my-3'><form action='lead.php?ID=<?php echo $Lead[ 'ID' ];?>' method='POST'>
              <div class='card-heading'>
                <div class='row g-0 px-3 py-2'>
                  <div class='col-10'><h5><?php \singleton\fontawesome::getInstance( )->Info( 1 );?><span>Infomation</span></h5></div>
                  <div class='col-2'>&nbsp;</div>
                </div>
              </div>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                <input type='hidden' name='ID' value='<?php echo $Lead[ 'ID' ];?>' />
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Name:</div>
                  <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Name' value='<?php echo $Lead['Name'];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Customer:</div>
                  <div class='col-6'>
                    <input type='text' autocomplete='off' class='form-control edit' name='Customer' value='<?php echo $Lead[ 'Customer_Name' ];?>' />
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
                                      url : 'bin/php/get/search/Locations.php',
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
                  <div class='row g-0'>
                    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Customer(1);?>Type:</div>
                    <div class='col-8'><select name='Type' class='form-control edit'>
                      <option value=''>Select</option>
                      <option value='General' <?php echo $Lead[ 'Type' ] == 'General' ? 'selected' : null;?>>General</option>
                      <option value='Bank' <?php echo $Lead[ 'Type' ] == 'Bank' ? 'selected' : null;?>>Bank</option>
                      <option value='Churches' <?php echo $Lead[ 'Type' ] == 'Churches' ? 'selected' : null;?>>Churches</option>
                      <option value='Commercial' <?php echo $Lead[ 'Type' ] == 'Commercial' ? 'selected' : null;?>>Commercial</option>
                      <option value='Hospitals' <?php echo $Lead[ 'Type' ] == 'Hospitals' ? 'selected' : null;?>>General</option>
                      <option value='Property Manage' <?php echo $Lead[ 'Type' ] == 'Property Manage' ? 'selected' : null;?>>Property Manage</option>
                      <option value='Restaraunts' <?php echo $Lead[ 'Type' ] == 'General' ? 'selected' : null;?>>Restaraunts</option>
                      <option value='Schools' <?php echo $Lead[ 'Type' ] == 'Schools' ? 'selected' : null;?>>Schools</option>
                    </select></div>
                  </div>
                  <div class='row g-0'>
                    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Street:</div>
                    <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Street' value='<?php echo $Lead['Street'];?>' /></div>
                  </div>
                  <div class='row g-0'>
                    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
                    <div class='col-8'><input type='text' class='form-control edit animation-focus' name='City' value='<?php echo $Lead['City'];?>' /></div>
                  </div>
                  <div class='row g-0'>
                    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
                    <div class='col-8'><select name='Status' class='form-control edit' name='Status' value='<?php echo $Lead['City'];?>' /></div>
                      <option value=''>Select</option>
                      <option value='0' <?php echo $Lead[ 'Status' ] == 0 ? 'selected' : null;?>>Open</option>
                      <option value='1' <?php echo $Lead[ 'Status' ] == 1 ? 'selected' : null;?>>Canceled</option>
                      <option value='2' <?php echo $Lead[ 'Status' ] == 2 ? 'selected' : null;?>>Withdrawn</option>
                      <option value='3' <?php echo $Lead[ 'Status' ] == 3 ? 'selected' : null;?>>Disqualified</option>
                      <option value='4' <?php echo $Lead[ 'Status' ] == 4 ? 'selected' : null;?>>Award Successful</option>
                      <option value='5' <?php echo $Lead[ 'Status' ] == 5 ? 'selected' : null;?>>Award Competitor</option>
                    </select></div>
                  </div>
                  <div class='row g-0'>
                    <div class='col-1'>&nbsp;</div>
                    <div class='col-3 border-bottom border-white my-auto'>State:</div>
                    <div class='col-8'><select class='form-control edit' name='State'>
                      <option <?php echo $Lead[ 'State' ] == 'AL' ? 'selected' : null;?> value='AL'>Alabama</option>
                      <option <?php echo $Lead[ 'State' ] == 'AK' ? 'selected' : null;?> value='AK'>Alaska</option>
                      <option <?php echo $Lead[ 'State' ] == 'AZ' ? 'selected' : null;?> value='AZ'>Arizona</option>
                      <option <?php echo $Lead[ 'State' ] == 'AR' ? 'selected' : null;?> value='AR'>Arkansas</option>
                      <option <?php echo $Lead[ 'State' ] == 'CA' ? 'selected' : null;?> value='CA'>California</option>
                      <option <?php echo $Lead[ 'State' ] == 'CO' ? 'selected' : null;?> value='CO'>Colorado</option>
                      <option <?php echo $Lead[ 'State' ] == 'CT' ? 'selected' : null;?> value='CT'>Connecticut</option>
                      <option <?php echo $Lead[ 'State' ] == 'DE' ? 'selected' : null;?> value='DE'>Delaware</option>
                      <option <?php echo $Lead[ 'State' ] == 'DC' ? 'selected' : null;?> value='DC'>District Of Columbia</option>
                      <option <?php echo $Lead[ 'State' ] == 'FL' ? 'selected' : null;?> value='FL'>Florida</option>
                      <option <?php echo $Lead[ 'State' ] == 'GA' ? 'selected' : null;?> value='GA'>Georgia</option>
                      <option <?php echo $Lead[ 'State' ] == 'HI' ? 'selected' : null;?> value='HI'>Hawaii</option>
                      <option <?php echo $Lead[ 'State' ] == 'ID' ? 'selected' : null;?> value='ID'>Idaho</option>
                      <option <?php echo $Lead[ 'State' ] == 'IL' ? 'selected' : null;?> value='IL'>Illinois</option>
                      <option <?php echo $Lead[ 'State' ] == 'IN' ? 'selected' : null;?> value='IN'>Indiana</option>
                      <option <?php echo $Lead[ 'State' ] == 'IA' ? 'selected' : null;?> value='IA'>Iowa</option>
                      <option <?php echo $Lead[ 'State' ] == 'KS' ? 'selected' : null;?> value='KS'>Kansas</option>
                      <option <?php echo $Lead[ 'State' ] == 'KY' ? 'selected' : null;?> value='KY'>Kentucky</option>
                      <option <?php echo $Lead[ 'State' ] == 'LA' ? 'selected' : null;?> value='LA'>Louisiana</option>
                      <option <?php echo $Lead[ 'State' ] == 'ME' ? 'selected' : null;?> value='ME'>Maine</option>
                      <option <?php echo $Lead[ 'State' ] == 'MD' ? 'selected' : null;?> value='MD'>Maryland</option>
                      <option <?php echo $Lead[ 'State' ] == 'MA' ? 'selected' : null;?> value='MA'>Massachusetts</option>
                      <option <?php echo $Lead[ 'State' ] == 'MI' ? 'selected' : null;?> value='MI'>Michigan</option>
                      <option <?php echo $Lead[ 'State' ] == 'MN' ? 'selected' : null;?> value='MN'>Minnesota</option>
                      <option <?php echo $Lead[ 'State' ] == 'MS' ? 'selected' : null;?> value='MS'>Mississippi</option>
                      <option <?php echo $Lead[ 'State' ] == 'MO' ? 'selected' : null;?> value='MO'>Missouri</option>
                      <option <?php echo $Lead[ 'State' ] == 'MT' ? 'selected' : null;?> value='MT'>Montana</option>
                      <option <?php echo $Lead[ 'State' ] == 'NE' ? 'selected' : null;?> value='NE'>Nebraska</option>
                      <option <?php echo $Lead[ 'State' ] == 'NV' ? 'selected' : null;?> value='NV'>Nevada</option>
                      <option <?php echo $Lead[ 'State' ] == 'NH' ? 'selected' : null;?> value='NH'>New Hampshire</option>
                      <option <?php echo $Lead[ 'State' ] == 'NJ' ? 'selected' : null;?> value='NJ'>New Jersey</option>
                      <option <?php echo $Lead[ 'State' ] == 'NM' ? 'selected' : null;?> value='NM'>New Mexico</option>
                      <option <?php echo $Lead[ 'State' ] == 'NY' ? 'selected' : null;?> value='NY'>New York</option>
                      <option <?php echo $Lead[ 'State' ] == 'NC' ? 'selected' : null;?> value='NC'>North Carolina</option>
                      <option <?php echo $Lead[ 'State' ] == 'ND' ? 'selected' : null;?> value='ND'>North Dakota</option>
                      <option <?php echo $Lead[ 'State' ] == 'OH' ? 'selected' : null;?> value='OH'>Ohio</option>
                      <option <?php echo $Lead[ 'State' ] == 'OK' ? 'selected' : null;?> value='OK'>Oklahoma</option>
                      <option <?php echo $Lead[ 'State' ] == 'OR' ? 'selected' : null;?> value='OR'>Oregon</option>
                      <option <?php echo $Lead[ 'State' ] == 'PA' ? 'selected' : null;?> value='PA'>Pennsylvania</option>
                      <option <?php echo $Lead[ 'State' ] == 'RI' ? 'selected' : null;?> value='RI'>Rhode Island</option>
                      <option <?php echo $Lead[ 'State' ] == 'SC' ? 'selected' : null;?> value='SC'>South Carolina</option>
                      <option <?php echo $Lead[ 'State' ] == 'SD' ? 'selected' : null;?> value='SD'>South Dakota</option>
                      <option <?php echo $Lead[ 'State' ] == 'TN' ? 'selected' : null;?> value='TN'>Tennessee</option>
                      <option <?php echo $Lead[ 'State' ] == 'TX' ? 'selected' : null;?> value='TX'>Texas</option>
                      <option <?php echo $Lead[ 'State' ] == 'UT' ? 'selected' : null;?> value='UT'>Utah</option>
                      <option <?php echo $Lead[ 'State' ] == 'VT' ? 'selected' : null;?> value='VT'>Vermont</option>
                      <option <?php echo $Lead[ 'State' ] == 'VA' ? 'selected' : null;?> value='VA'>Virginia</option>
                      <option <?php echo $Lead[ 'State' ] == 'WA' ? 'selected' : null;?> value='WA'>Washington</option>
                      <option <?php echo $Lead[ 'State' ] == 'WV' ? 'selected' : null;?> value='WV'>West Virginia</option>
                      <option <?php echo $Lead[ 'State' ] == 'WI' ? 'selected' : null;?> value='WI'>Wisconsin</option>
                      <option <?php echo $Lead[ 'State' ] == 'WY' ? 'selected' : null;?> value='WY'>Wyoming</option>
                    </select></div>
                  </div>
                  <div class='row g-0'>
                    <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
                    <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Zip' value='<?php echo $Lead['Zip'];?>' /></div>
                  </div>
                  <div class='col-2'><button class='h-100 w-100' type='button' <?php
                    if( in_array( $Lead[ 'Customer_ID' ], array( null, 0, '', ' ') ) ){
                      echo "onClick=\"document.location.href='customers.php';\"";
                    } else {
                      echo "onClick=\"document.location.href='customer.php?ID=" . $Lead[ 'Customer_ID' ] . "';\"";
                    }
                  ?>><?php \singleton\fontawesome::getInstance( )->Search( 1 );?></button></div>
                <div class='card-footer'>
                    <div class='row'>
                        <div class='col-12'><button class='form-control' type='submit'>Save</button></div>
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
</body>
</html>
<?php
    }
}?>
