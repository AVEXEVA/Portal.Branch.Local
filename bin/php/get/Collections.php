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
        ||  !isset( $Privileges[ 'Collection' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Collection' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'customers.php'
        )
      );

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Customer'];
        $conditions[] = "OpenAR.Ref LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Territory' ] ) && !in_array( $_GET[ 'Territory' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Territory'];
        $conditions[] = "Territory.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Customer'];
        $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Location'];
        $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Job' ] ) && !in_array( $_GET[ 'Job' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Job'];
        $conditions[] = "Job.fDesc LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Type'];
        $conditions[] = "JobType.Type LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Date_Start' ] ) && !in_array( $_GET[ 'Date_Start' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Date_Start'];
        $conditions[] = "OpenAR.fDate >= ?";
    }
    if( isset($_GET[ 'Date_End' ] ) && !in_array( $_GET[ 'Date_End' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Date_End'];
        $conditions[] = "OpenAR.fDate < ?";
    }
    if( isset($_GET[ 'Due_Start' ] ) && !in_array( $_GET[ 'Due_Start' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Due_Start'];
        $conditions[] = "OpenAR.Due >= ?";
    }
    if( isset($_GET[ 'Due_End' ] ) && !in_array( $_GET[ 'Due_End' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Due_End'];
        $conditions[] = "OpenAR.fDate < ?";
    }
    if( isset($_GET[ 'Original_Start' ] ) && !in_array( $_GET[ 'Original_Start' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Original_Start'];
        $conditions[] = "OpenAR.Original >= ?";
    }
    if( isset($_GET[ 'Original_End' ] ) && !in_array( $_GET[ 'Original_End' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Original_End'];
        $conditions[] = "OpenAR.Original < ?";
    }
    if( isset($_GET[ 'Balance_Start' ] ) && !in_array( $_GET[ 'Balance_Start' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Balance_Start'];
        $conditions[] = "OpenAR.Balance >= ?";
    }
    if( isset($_GET[ 'Balance_End' ] ) && !in_array( $_GET[ 'Balance_End' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Balance_End'];
        $conditions[] = "OpenAR.Balance < ?";
    }
    if( isset($_GET[ 'Description' ] ) && !in_array( $_GET[ 'Description' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Description'];
        $conditions[] = "OpenAR.fDesc < ?";
    }
    /*if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['Search'];
      $search[] = "OpenAR.Ref LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "OpenAR.fDesc LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

      $parameters[ ] = $_GET[ 'Search' ];
      $search[ ] = "Job.ID LIKE '%' + ? + '%'";

      $parameters[ ] = $_GET[ 'Search' ];
      $search[ ] = "Job.fDesc LIKE '%' + ? + '%'";

      $parameters[ ] = $_GET[ 'Search' ];
      $search[ ] = "JobType.Type LIKE '%' + ? + '%'";
    }*/

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    $Columns = array(
        0 =>  'OpenAR.Ref',
        1 =>  'Territory.Name',
        2 =>  'Customer.Name',
        3 =>  'Location.Tag',
        4 =>  'Job.fDesc',
        5 =>  'JobType.Type',
        6 =>  'OpenAR.fDate',
        7 =>  'OpenAR.Due',
        8 =>  'OpenAR.Original',
        9 =>  'OpenAR.Balance',
        10 => 'OpenAR.fDesc'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "OpenAR.Ref";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                    SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                            OpenAR.Ref        AS ID,
                            Customer.ID       AS Customer_ID,
                            Customer.Name     AS Customer_Name,
                            Location.Loc      AS Location_ID,
                            Location.Tag      AS Location_Name,
                            Location.Address  AS Location_Street,
                            Location.City     AS Location_City,
                            Location.State    AS Location_State,
                            Location.Zip      AS Location_Zip,
                            Job.ID            AS Job_ID,
                            Job.fDesc         AS Job_Name,
                            JobType.Type      AS Type,
                            OpenAR.fDate      AS Date,
                            OpenAR.Due        AS Due,
                            OpenAR.Original   AS Original,
                            OpenAR.Balance    AS Balance,
                            OpenAR.fDesc      AS Description,
                            Territory.ID      AS Territory_ID,
                            Territory.Name    AS Territory_Name
                    FROM    OpenAR
                            LEFT JOIN Invoice      ON OpenAR.Ref  = Invoice.Ref
                            LEFT JOIN Loc AS Location ON OpenAR.Loc  = Location.Loc
                            LEFT JOIN (
                                SELECT  Owner.ID AS ID,
                                        Rol.Name AS Name
                                FROM    Owner
                                        LEFT JOIN Rol ON Owner.Rol = Rol.ID
                            ) AS Customer ON Location.Owner   = Customer.ID
                            LEFT JOIN Job          ON Invoice.Job = Job.ID
                            LEFT JOIN JobType      ON Job.Type    = JobType.ID
                            LEFT JOIN Terr AS Territory ON Territory.ID = Location.Terr
                    WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    $rResult = \singleton\database::getInstance( )->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "
        SELECT  Count( OpenAR.Ref ) AS Count
        FROM    OpenAR
                LEFT JOIN Invoice      ON OpenAR.Ref  = Invoice.Ref
                LEFT JOIN Loc AS Location ON OpenAR.Loc  = Location.Loc
                LEFT JOIN (
                    SELECT  Owner.ID AS ID,
                            Rol.Name AS Name
                    FROM    Owner
                            LEFT JOIN Rol ON Owner.Rol = Rol.ID
                ) AS Customer ON Location.Owner   = Customer.ID
                LEFT JOIN Job          ON Invoice.Job = Job.ID
                LEFT JOIN JobType      ON Job.Type    = JobType.ID
        WHERE   ({$conditions}) AND ({$search});";

    $stmt = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];

    $sQuery = " SELECT  COUNT( OpenAR.Ref )
                FROM    OpenAR;";
    $rResultTotal = \singleton\database::getInstance( )->query( null,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval( $_GET[ 'draw' ] ),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array()
    );

    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
        $Row[ 'Date' ] = date( 'm/d/Y', strtotime( $Row[ 'Date' ] ) );
        $Row[ 'Due' ] = date( 'm/d/Y', strtotime( $Row[ 'Due' ] ) );
        $Row[ 'Original' ] = number_format( $Row[ 'Original' ], 2);
        $Row[ 'Balance' ] = number_format( $Row[ 'Balance' ], 2);
        $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>
