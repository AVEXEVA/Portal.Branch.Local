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
                        [Lead].Remarks  	   AS Notes,
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
        	'Notes' => null,
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
        	$Lead[ 'Notes' ] 	= isset( $_POST[ 'Notes' ] ) 		? $_POST[ 'Notes' ] 			: $Lead[ 'Notes' ];
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
    	    			!empty( $Lead[ 'Level' ] ) ? $Lead[ 'Level' ] : 0,
    	    			!empty( $Lead[ 'Revenue' ] ) ? $Lead[ 'Revenue' ] : 0,
    	    			!empty( $Lead[ 'Cost' ] ) ? $Lead[ 'Cost' ] : 0,
    	    			!empty( $Lead[ 'Labor' ] ) ? $Lead[ 'Labor' ] : 0,
                !empty( $Lead[ 'Profit' ] ) ? $Lead[ 'Profit' ] : 0,
                $Lead[ 'Ratio' ],
                $Lead[ 'Notes' ],
                $Lead[ 'Latitude' ],
                $Lead[ 'Longitude' ],
                $Lead[ 'Country' ],
                !empty( $Lead[ 'GeoLock' ] ) ? $Lead[ 'GeoLock' ] : 0
    	    		)
    	    	);
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
                $Lead[ 'Notes' ],
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
<body>
  <div id="wrapper">
    <?php require( bin_php . 'element/navigation.php'); ?>
    <div id="page-wrapper" class='content'>
      <div class='card card-primary'>
        <form action='leads.php?ID=<?php echo $Lead[ 'ID' ];?>' method='POST'>
            <input type='hidden' name='ID' value='<?php echo $Lead[ 'ID' ];?>' />
            <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Lead', 'Leads', $Lead[ 'ID' ] );?>
            <div class='card-body bg-dark text-white'>
                <div class='row g-0' data-masonry='{"percentPosition": true }'>
              <div class='card card-primary my-3 col-12 col-lg-3'>
                <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' ); ?>
                <?php
                    \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', $Lead[ 'Name' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_autocomplete( 'Customer', 'Customers', $Lead[ 'Customer_ID' ], $Lead[ 'Customer_Name' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_select( 'Type', $Lead[ 'Type' ], array(
                        'General' => 'General',
                        'Bank' => 'Bank',
                        'Churches' => 'Churches',
                        'Hospitals' => 'Hospitals',
                        'Property Manage' => 'Property Manage',
                        'Restaraunts' => 'Restaraunts',
                        'Schools' => 'Schools'
                    ) );
                    \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Address', 'https://maps.google.com/?q=' . $Lead['Street'].' '.$Lead['City'].' '.$Lead[ 'State' ].' '.$Lead[ 'Zip' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Street', $Lead[ 'Street' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'City', $Lead[ 'City' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_select_sub( 'State', $Lead[ 'State' ],  array( 'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming' ) );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Zip', $Lead[ 'Zip' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub_number( 'Latitude',  $Lead[ 'Latitude' ] );
                    \singleton\bootstrap::getInstance( )->card_row_form_input_sub_number( 'Longitude',  $Lead[ 'Longitude' ] );
                ?>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Revenue:</div>
                  <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Revenue' value='<?php echo $Lead['Revenue'];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Cost:</div>
                  <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Cost' value='<?php echo $Lead['Cost'];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Labor:</div>
                  <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Labor' value='<?php echo $Lead['Labor'];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Dollar(1);?> Profit:</div>
                  <div class='col-8'><input type='text' class='form-control edit animation-focus' name='Profit' value='<?php echo $Lead['Profit'];?>' /></div>
                </div>
                <div class='row g-0'>
                  <div class='col-4 border-bottom border-white my-auto'><?php \singleton\fontawesome::getInstance( )->Note(1);?> Notes:</div>
                  <div class='col-8'><textarea rows='8' type='text' class='form-control edit animation-focus' name='Notes'><?php echo $Lead['Notes'];?></textarea></div>
                </div>
              </div>
              <div class='card-footer'><button class='form-control' type='submit'>Save</button></div>
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
