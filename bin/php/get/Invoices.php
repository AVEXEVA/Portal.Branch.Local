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
        ||  !isset( $Privileges[ 'Invoice' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Invoice' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
    $conditions = array( );
    $search = array( );
    $parameters = array( );
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
    /*if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['Search'];
      $search[] = "Invoice.Ref LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Invoice.fDesc LIKE '%' + ? + '%'";

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

    /*ROW NUMBER*/
	$parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] - 25 : 0;
	$parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 25 : 25;

    $Columns = array(
        0 =>  'Invoice.Ref',
        1 =>  'Customer.Name',
        2 =>  'Location.Tag',
        3 =>  'Job.fDesc',
        4 =>  'JobType.Type',
        5 =>  'Invoice.fDate',
        6 =>  'OpenAR.Due',
        7 =>  'Invoice.Amount',
        8 =>  'OpenAR.Balance',
        9 =>  'Invoice.fDesc'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Invoice.Ref";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                    SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                            Invoice.Ref AS ID,
                            Customer.ID AS Customer_ID,
                            Customer.Name AS Customer_Name,
                            Location.Loc AS Location_ID,
                            Location.Tag AS Location_Name,
                            Location.Address AS Location_Street,
                            Location.City AS Location_City,
                            Location.State AS Location_State,
                            Location.Zip AS Location_Zip,
                            Job.ID AS Job_ID,
                            Job.fDesc AS Job_Name,
                            JobType.Type AS Type,
                            Invoice.fDate AS Date,
                            OpenAR.Due AS Due,
                            Invoice.Amount AS Original,
                            OpenAR.Balance AS Balance,
                            Invoice.fDesc AS Description
                    FROM    Invoice
                            LEFT JOIN OpenAR ON OpenAR.Ref           = Invoice.Ref
                            LEFT JOIN Loc AS Location ON Invoice.Loc = Location.Loc
                            LEFT JOIN (
                                SELECT  Owner.ID AS ID,
                                        Rol.Name AS Name
                                FROM    Owner
                                        LEFT JOIN Rol ON Owner.Rol  = Rol.ID
                            ) AS Customer ON Location.Owner         = Customer.ID
                            LEFT JOIN Job          ON Invoice.Job   = Job.ID
                            LEFT JOIN JobType      ON Job.Type      = JobType.ID
                    WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    $rResult = \singleton\database::getInstance( )->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "SELECT  Invoice.Ref AS ID
                  FROM    Invoice
                          LEFT JOIN OpenAR ON OpenAR.Ref  = Invoice.Ref
                          LEFT JOIN Loc AS Location ON Invoice.Loc  = Location.Loc
                          LEFT JOIN (
                              SELECT  Owner.ID AS ID,
                                      Rol.Name AS Name
                              FROM    Owner
                                      LEFT JOIN Rol ON Owner.Rol = Rol.ID
                          ) AS Customer ON Location.Owner   = Customer.ID
                          LEFT JOIN Job          ON Invoice.Job = Job.ID
                          LEFT JOIN JobType      ON Job.Type    = JobType.ID
                  WHERE   ({$conditions}) AND ({$search});";
    $fResult = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = 0;
    if( count( $_SESSION[ 'Tables' ][ 'Customers' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Customers' ] as &$Value ){ $Value = false; } }
    $_SESSION[ 'Tables' ][ 'Customers' ][ 0 ] = $_GET;
    while( $Row = sqlsrv_fetch_array( $fResult ) ){
        $_SESSION[ 'Tables' ][ 'Customers' ][ $Row[ 'ID' ] ] = true;
        $iFilteredTotal++;
    }

    $parameters = array( );
    $sQuery = " SELECT  COUNT( Invoice.Ref )
                FROM    Invoice;";
    $rResultTotal = \singleton\database::getInstance( )->query(null,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval( $_GET[ 'draw' ] ),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array()
    );

    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
      $output['aaData'][]       = $Row;
    }
    echo json_encode( $output );
}}
?>
