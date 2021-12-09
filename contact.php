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

    //function for get the next and previous button max and min value.
    $unitPagination = \singleton\database::getInstance( )->query(
        null,
        "SELECT MIN(ID), MAX(ID)
                    FROM      Rol
                         AS Contact;",
       []

    );
    $finalResult = sqlsrv_fetch_array($unitPagination);
    $previous = 1;
    $next = 2;

    if($finalResult && isset( $_GET[ 'ID' ] )){
        $previous = ($_GET[ 'ID' ]==1? 1  : $_GET[ 'ID' ]-1);
        $next = $_GET[ 'ID' ]+1;
    }
    //end

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
                SELECT 	Rol.ID 		  AS ID,
                        Rol.Name    AS Name,
                        Rol.Contact AS Contact,
                        Rol.Type    AS Type,
                        Rol.Phone   AS Phone,
                        Rol.Email   AS Email,
                        Rol.Address AS Street,
                        Rol.City    AS City,
                        Rol.State   AS State,
                        Rol.Zip     AS Zip,
                        Rol.Latt 	  AS Latitude,
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
                Geolock,
                Since,
                Last
              )
              VALUES( @MAXID + 1 , ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? , ? );
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
              !is_null( $Contact[ 'Geofence' ] ) ? $Contact[ 'Geofence' ] : 0,
              date("Y-m-d H:i:s"),
              date("Y-m-d H:i:s")
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
                  Rol.EMail = ?,
                  Rol.Last = ?


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
              date("Y-m-d H:i:s"),
              $Contact[ 'ID' ]
            )
          );
        }
      }
?><!DOCTYPE html>
<html lang='en'>
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
    <?php require( bin_php . 'element/navigation.php' ); ?>
    <div id="page-wrapper" class='content'>
    	<div class='card card-primary'><form action='contact.php?ID=<?php echo $Contact[ 'ID' ];?>' method='POST'>
        <input type='hidden' name='ID' value='<?php echo $Contact[ 'ID' ];?>' />
    		<?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Contact', 'Contacts', $Contact[ 'ID' ] );?>
    		<div class='card-body bg-dark text-white'>
          <div class='row g-0'>
            <div class='card card-primary my-3 col-12 col-lg-3'>
              <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' ); ?>
              <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                <?php
                  \singleton\bootstrap::getInstance( )->card_row_form_input( 'Name', 'Name', $Contact[ 'Name' ] );
                  \singleton\bootstrap::getInstance( )->card_row_form_select( 'Type', 'Type', $Contact[ 'Type' ], array( 0 => 'Customer', 4 => 'Location', 5 => 'Employee' ) );
                  \singleton\bootstrap::getInstance( )->card_row_form_input_url( 'Website', 'Website', $Contact[ 'Website' ] );
                  \singleton\bootstrap::getInstance( )->card_row_form_input( 'Contact', 'Contact', $Contact[ 'Contact' ] );
                  \singleton\bootstrap::getInstance( )->card_row_form_input_email( 'Email', 'Email', $Contact[ 'Email' ] );
                  \singleton\bootstrap::getInstance( )->card_row_form_input_tel( 'Phone', 'Phone', $Contact[ 'Phone' ] );
                  \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Address', 'Address', 'https://maps.google.com/?q=' . $Contact['Street'].' '.$Contact['City'].' '.$Contact[ 'State' ].' '.$Contact[ 'Zip' ] );
                  \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Street', 'Street', $Contact[ 'Street' ] );
                  \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'City', 'City', $Contact[ 'City' ] );
                  \singleton\bootstrap::getInstance( )->card_row_form_select_sub( 'State', 'State', $Contact[ 'State' ],  array( 'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming' ) );
                  \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Zip', 'Zip', $Contact[ 'Zip' ] );
                ?>
              </div>
            </div>
          </div>
        </div><
      </form></div>
    </div>
	</div>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=contact<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
