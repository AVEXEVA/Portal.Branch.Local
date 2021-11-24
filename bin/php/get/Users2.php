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
		  	$_SESSION[ 'Connection' ][ 'User' ]
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
    if(!isset( $Connection[ 'ID' ] ) ){ ?><?php require('404.html');?><?php }
    else {

    $conditions = array( );
    $search = array( );
    $params = array( );

    if( isset($_GET[ 'Supervisor' ] ) && !in_array( $_GET[ 'Supervisor' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Supervisor'];
      $conditions[] = "tblWork.Super LIKE '%' + ? + '%'";
    }

    $params[] = 0;
    $conditions[] = "Emp.Status = ?";

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $params[] = $_GET['Search'];
      $search[] = "Emp.fFirst LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Emp.Last LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Emp.fFirst + ' ' + Emp.Last LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Portal.Email LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;
    
    $Columns = array(
      0 =>  'Emp.ID',
      1 =>  'Emp.fFirst',
      2 =>  'Emp.Last',
      3 =>  'Portal.Email',
      4 =>  'tblWork.Super'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Emp.Last";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery =
                " SELECT *
                  FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Emp.ID AS Branch_ID,
                          Emp.fFirst AS First_Name,
                          Emp.Last AS Last_Name,
                          Emp.fFirst + ' ' + Emp.Last AS Full_Name,
                          Portal.Email AS Email,
                          tblWork.Super AS Supervisor
                  FROM    Emp
                          LEFT JOIN (
                            SELECT    Portal.Branch,
                                      Portal.Branch_ID,
                                      Portal.Email
                            FROM      Portal.dbo.Portal
                            GROUP BY  Portal.Branch,
                                      Portal.Branch_ID,
                                      Portal.Email
                          ) AS Portal ON Portal.Branch_ID = Emp.ID AND Portal.Branch = ?
                          LEFT JOIN tblWork ON CONVERT( INT, SUBSTRING( tblWork.Members, 2, LEN( tblWork.Members ) - 2 ) ) = Emp.ID
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    $rResult = $database->query(
      $conn,
      $sQuery,
      array_mege( array( $_SESSION[ 'Conneciton' ][ 'Branch' ] ), $params  )
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                            Emp.ID AS Branch_ID,
                            Emp.fFirst AS First_Name,
                            Emp.Last AS Last_Name,
                            Emp.fFirst + ' ' + Emp.Last AS Full_Name,
                            Portal.Email AS Email,
                            tblWork.Super AS Supervisor
                    FROM    Emp
                            LEFT JOIN (
                            SELECT    Portal.Branch,
                                      Portal.Branch_ID,
                                      Portal.Email
                            FROM      Portal.dbo.Portal
                            GROUP BY  Portal.Branch,
                                      Portal.Branch_ID,
                                      Portal.Email
                          ) AS Portal ON Portal.Branch_ID = Emp.ID AND Portal.Branch = 'Nouveau Elevator'
                            LEFT JOIN tblWork ON CONVERT( INT, SUBSTRING( tblWork.Members, 2, LEN( tblWork.Members ) - 2 ) ) = Emp.ID
                    WHERE ({$conditions}) AND ({$search});";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = $database->query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT( Emp.ID )
                FROM    Emp;";
    $rResultTotal = $database->query($conn,  $sQuery, $params ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval($_GET['sEcho']),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array()
    );

    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
      $Row['Start_Date'] = date('m/d/Y', strtotime( $Row[ 'Start_Date' ] ) );
      $Row['End_Date'] = date('m/d/Y', strtotime( $Row[ 'End_Date' ] ) );
      $Row['Escalation_Date'] = date('m/d/Y', strtotime( $Row[ 'Escalation_Date' ] ) );
      $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>
