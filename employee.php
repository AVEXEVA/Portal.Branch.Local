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
        	"	SELECT 	Employee.ID                           AS ID,
        				Employee.fFirst + ' ' + Employee.Last AS Name,
        				Employee.fFirst                       AS First_Name,
        				Employee.Last                         AS Last_Name,
                        Employee.Title                        AS Title,
        				Rolodex.Address                       AS Street,
        				Rolodex.City                          AS City,
        				Rolodex.State                         AS State,
        				Rolodex.Zip                           AS Zip,
        				Rolodex.Latt                          AS Latitude,
        				Rolodex.fLong                         AS Longitude,
        				Rolodex.Geolock                       AS Geofence,
        				Rolodex.ID 		                      AS Rolodex,
        				Rolodex.Name                          AS Name,
                        Rolodex.Phone                         AS Phone,
                        Rolodex.Email                         AS Email,
                        Rolodex.Contact                       AS Contact,
        				tblWork.Super                         AS Supervisor,
        				[User].ID                             AS User_ID,
        				[User].Email 	                      AS User_Email,
                        CASE    WHEN Tickets.Unassigned IS NULL THEN 0
                                ELSE Tickets.Unassigned END AS Tickets_Open,
                        CASE    WHEN Tickets.Assigned IS NULL THEN 0
                                ELSE Tickets.Assigned END AS Tickets_Assigned,
                        CASE    WHEN Tickets.En_Route IS NULL THEN 0
                                ELSE Tickets.En_Route END AS Tickets_En_Route,
                        CASE    WHEN Tickets.On_Site IS NULL THEN 0
                                ELSE Tickets.On_Site END AS Tickets_On_Site,
                        CASE    WHEN Tickets.Reviewing IS NULL THEN 0
                                ELSE Tickets.Reviewing END AS Tickets_Reviewing
        		FROM 	dbo.Emp AS Employee
        				LEFT JOIN dbo.tblWork       AS tblWork  ON 'A' + convert(varchar(10), Employee.ID) + ',' = tblWork.Members
        				LEFT JOIN dbo.Rol           AS Rolodex  ON Employee.Rol = Rolodex.ID
                        LEFT JOIN Portal.dbo.[User]             ON [User].Branch_Type = 'Employee' AND [User].Branch_ID = Employee.ID
                        LEFT JOIN (
                            SELECT  Employee.ID AS Employee,
                                    Unassigned.Count AS Unassigned,
                                    Assigned.Count AS Assigned,
                                    En_Route.Count AS En_Route,
                                    On_Site.Count AS On_Site,
                                    Reviewing.Count AS Reviewing
                            FROM    Emp AS Employee
                                    LEFT JOIN (
                                      SELECT    TicketO.fWork AS Work_ID,
                                                Count( TicketO.ID ) AS Count
                                      FROM      TicketO
                                      WHERE     TicketO.Assigned = 0
                                      GROUP BY  TicketO.fWork
                                    ) AS Unassigned ON Unassigned.Work_ID = Employee.fWork
                                    LEFT JOIN (
                                      SELECT    TicketO.fWork AS Work_ID,
                                                Count( TicketO.ID ) AS Count
                                      FROM      TicketO
                                      WHERE     TicketO.Assigned = 1
                                      GROUP BY  TicketO.fWork
                                    ) AS Assigned ON Assigned.Work_ID = Employee.fWork
                                    LEFT JOIN (
                                      SELECT    TicketO.fWork AS Work_ID,
                                                Count( TicketO.ID ) AS Count
                                      FROM      TicketO
                                      WHERE     TicketO.Assigned = 2
                                      GROUP BY  TicketO.fWork
                                    ) AS En_Route ON En_Route.Work_ID = Employee.fWork
                                    LEFT JOIN (
                                      SELECT    TicketO.fWork AS Work_ID,
                                                Count( TicketO.ID ) AS Count
                                      FROM      TicketO
                                      WHERE     TicketO.Assigned = 3
                                      GROUP BY  TicketO.fWork
                                    ) AS On_Site ON On_Site.Work_ID = Employee.fWork
                                    LEFT JOIN (
                                      SELECT    TicketO.fWork AS Work_ID,
                                                Count( TicketO.ID ) AS Count
                                      FROM      TicketO
                                      WHERE     TicketO.Assigned = 6
                                      GROUP BY  TicketO.fWork
                                    ) AS Reviewing ON Reviewing.Work_ID = Employee.fWork
                        ) AS Tickets ON Tickets.Employee = Employee.ID
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
            'Title' => null,
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
        	'Phone' => null
        ) : sqlsrv_fetch_array($result);

        if( isset( $_POST ) && count( $_POST ) > 0 ){
        	$Employee[ 'First_Name' ]      = isset( $_POST[ 'First_Name' ] )    ? $_POST[ 'First_Name' ]    : $Employee[ 'First_Name' ];
            $Employee[ 'Last_Name' ]        = isset( $_POST[ 'Last_Name' ] )     ? $_POST[ 'Last_Name' ]     : $Employee[ 'Last_Name' ];
            $Employee[ 'Title' ]        = isset( $_POST[ 'Title' ] )     ? $_POST[ 'Title' ]     : $Employee[ 'Title' ];
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
                  Title,
        					Status,
        					Sales,
        					Field,
        					InUse ,
                  Rol
        				)
        				VALUES ( @MAXID + 1, @MAXFWORK + 1, ?, ?, ?, ?, ?, ?, ?, ? );
        				SELECT @MAXID + 1;",
        			array(
        				$Employee[ 'First_Name' ],
        				$Employee[ 'Last_Name' ],
                $Employee[ 'Title' ],
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
                      Emp.Title = ?
                WHERE 	Emp.ID = ?;",
	        		array(
	        			$Employee[ 'First_Name' ],
	        			$Employee[ 'Last_Name' ],
                $Employee[ 'Title' ],
                $Employee[ 'ID' ]
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
        <form action='job.php?ID=<?php echo $Employee[ 'ID' ];?>' method='POST'>
            <input type='hidden' name='ID' value='<?php echo $Employee[ 'ID' ];?>' />
            <?php \singleton\bootstrap::getInstance( )->primary_card_header( 'Employee', 'Employees', $Employee[ 'ID' ] );?>
            <div class='card-body bg-dark text-white'>
                <div class='row g-0' data-masonry='{"percentPosition": true }'>
                    <?php \singleton\bootstrap::getInstance( )->card_map( 'employee_map', $Employee[ 'Name' ], $Employee[ 'Latitude' ], $Employee[ 'Longitude' ] );?>
                    <div class='card card-primary my-3 col-12 col-lg-3'>
                        <?php \singleton\bootstrap::getInstance( )->card_header( 'Information' );?>
                        <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Infomation' ] ) && $_SESSION[ 'Cards' ][ 'Infomation' ] == 0 ? "style='display:none;'" : null;?>>
                            <?php 
                                \singleton\bootstrap::getInstance( )->card_row_form_input( 'First_Name', $Employee[ 'First_Name' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_input( 'Last_Name', $Employee[ 'Last_Name' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_input( 'Title', $Employee[ 'Title' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_input( 'Supervisor', $Employee[ 'Supervisor' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_input_email( 'Email', $Employee[ 'Email' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_input_tel( 'Phone', $Employee[ 'Phone' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Address', 'https://maps.google.com/?q=' . $Employee['Street'].' '.$Employee['City'].' '.$Employee[ 'State' ].' '.$Employee[ 'Zip' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Street', $Employee[ 'Street' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'City', $Employee[ 'City' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_select_sub( 'State', $Employee[ 'State' ],  array( 'AL'=>'Alabama', 'AK'=>'Alaska', 'AZ'=>'Arizona', 'AR'=>'Arkansas', 'CA'=>'California', 'CO'=>'Colorado', 'CT'=>'Connecticut', 'DE'=>'Delaware', 'DC'=>'District of Columbia', 'FL'=>'Florida', 'GA'=>'Georgia', 'HI'=>'Hawaii', 'ID'=>'Idaho', 'IL'=>'Illinois', 'IN'=>'Indiana', 'IA'=>'Iowa', 'KS'=>'Kansas', 'KY'=>'Kentucky', 'LA'=>'Louisiana', 'ME'=>'Maine', 'MD'=>'Maryland', 'MA'=>'Massachusetts', 'MI'=>'Michigan', 'MN'=>'Minnesota', 'MS'=>'Mississippi', 'MO'=>'Missouri', 'MT'=>'Montana', 'NE'=>'Nebraska', 'NV'=>'Nevada', 'NH'=>'New Hampshire', 'NJ'=>'New Jersey', 'NM'=>'New Mexico', 'NY'=>'New York', 'NC'=>'North Carolina', 'ND'=>'North Dakota', 'OH'=>'Ohio', 'OK'=>'Oklahoma', 'OR'=>'Oregon', 'PA'=>'Pennsylvania', 'RI'=>'Rhode Island', 'SC'=>'South Carolina', 'SD'=>'South Dakota', 'TN'=>'Tennessee', 'TX'=>'Texas', 'UT'=>'Utah', 'VT'=>'Vermont', 'VA'=>'Virginia', 'WA'=>'Washington', 'WV'=>'West Virginia', 'WI'=>'Wisconsin', 'WY'=>'Wyoming' ) );
                                \singleton\bootstrap::getInstance( )->card_row_form_input_sub( 'Zip', $Employee[ 'Zip' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_input_sub_number( 'Latitude',  $Employee[ 'Latitude' ] );
                                \singleton\bootstrap::getInstance( )->card_row_form_input_sub_number( 'Longitude',  $Employee[ 'Longitude' ] );
                            ?>
                        </div>
                    </div>
                    <div class='card card-primary my-3 col-12 col-lg-3'>
                        <?php \singleton\bootstrap::getInstance( )->card_header( 'Tickets', 'Ticket', 'Tickets', 'Employee', $Employee[ 'ID' ] );?>
                        <div class='card-body bg-dark' <?php echo isset( $_SESSION[ 'Cards' ][ 'Tickets' ] ) && $_SESSION[ 'Cards' ][ 'Tickets' ] == 0 ? "style='display:none;'" : null;?>>
                            <?php \singleton\bootstrap::getInstance( )->card_row_form_aggregated( 'Statuses', 'tickets.php?Employee=' . $Employee[ 'ID' ] );?>
                            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Open', $Employee[ 'Tickets_Open' ], true, true, 'tickets.php?Employee=' . $Employee[ 'ID' ] . '&Status=0');?>
                            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Assigned', $Employee[ 'Tickets_Assigned' ], true, true, 'tickets.php?Employee=' . $Employee[ 'ID' ] ) . '&Status=1';?>
                            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'En Route', $Employee[ 'Tickets_En_Route' ], true, true, 'tickets.php?Employee=' . $Employee[ 'ID' ] ) . '&Status=2';?>
                            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'On Site', $Employee[ 'Tickets_On_Site' ], true, true, 'tickets.php?Employee=' . $Employee[ 'ID' ] ) . '&Status=3';?>
                            <?php \singleton\bootstrap::getInstance( )->card_row_form_input( 'Reviewing', $Employee[ 'Tickets_Reviewing' ], true, true, 'tickets.php?Employee=' . $Employee[ 'ID' ] ) . '&Status=6';?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
 <?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=employee.php';</script></head></html><?php }?>
