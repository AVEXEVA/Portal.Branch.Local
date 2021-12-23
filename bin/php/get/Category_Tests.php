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
    if(   !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Collection' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Collection' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'Category-Tests.php'
        )
      );

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset($_GET[ 'ID' ] ) && !empty(  $_GET[ 'ID' ] ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "TestCategory.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !empty( $_GET[ 'Name' ] ) ){
      $parameters[] = $_GET['Name'];
      $conditions[] = "TestCategory.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Descr' ] ) && !empty( $_GET[ 'Descr' ] ) ){
      $parameters[] = $_GET['Descr'];
      $conditions[] = "TestCategory.Descr LIKE '%' + ? + '%'";
    }
    

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['Search'];
      $search[] = "TestCategory.ID  LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "TestCategory.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "TestCategory.Descr LIKE '%' + ? + '%'";


    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    $Columns = array(
      0 =>  'TestCategory.ID',
      1 =>  'TestCategory.Name',
      
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Contract.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                    SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                            TestCategory.ID         AS ID,
                            TestCategory.ID         AS TestCategory_ID,
                            TestCategory.Name       AS Name,
                           Deficiency.Count         AS Deficiency,
                             Deficiency.DEF_ID         AS Deficiency_ID
                    FROM    TestCategory
                            LEFT JOIN (
                              SELECT  Deficiency.TestCategoryID,  max(Deficiency.ID) as DEF_ID
                                     , COUNT( Deficiency.ID ) AS Count 
                              FROM Deficiency 
                              LEFT JOIN TestCategory ON TestCategory.ID = Deficiency.TestCategoryID 
                              GROUP BY Deficiency.TestCategoryID
                            ) AS Deficiency ON TestCategory.ID = Deficiency.TestCategoryID
                            
                    WHERE   ({$conditions})  AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";

    $rResult = \singleton\database::getInstance( )->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "SELECT  TestCategory.ID  AS ID
                   ,Deficiency.Count AS Count
                    FROM    TestCategory
                            LEFT JOIN (
                              SELECT  Deficiency.TestCategoryID,  
                                      COUNT( Deficiency.ID ) AS Count 
                              FROM Deficiency 
                              LEFT JOIN TestCategory ON TestCategory.ID = Deficiency.TestCategoryID GROUP BY Deficiency.TestCategoryID
                            ) AS Deficiency ON TestCategory.ID = Deficiency.TestCategoryID
                            
                  WHERE   ({$conditions})  AND ({$search})";

    $fResult = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));


    $iFilteredTotal = 0;
    $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
    $_SESSION[ 'Tables' ][ 'TestCategories' ] = isset( $_SESSION[ 'Tables' ][ 'TestCategories' ]  ) ? $_SESSION[ 'Tables' ][ 'TestCategories' ] : array( );
    if( count( $_SESSION[ 'Tables' ][ 'TestCategories' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'TestCategories' ] as &$Value ){ $Value = false; } }
    $_SESSION[ 'Tables' ][ 'TestCategories' ][ 0 ] = $_GET;
    while( $Row = sqlsrv_fetch_array( $fResult ) ){
        $_SESSION[ 'Tables' ][ 'TestCategories' ][ $Row[ 'ID' ] ] = true;
        $iFilteredTotal++;
    }

    $parameters = array( );
    $sQuery = " SELECT  COUNT( TestCategory.ID)
                FROM    TestCategory;";
    $rResultTotal = \singleton\database::getInstance( )->query(null,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval( $_GET[ 'draw' ] ),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array(),
        'options'       => array( )
    );
    while ( $Row = sqlsrv_fetch_array( $rResult ) ){

      $output['aaData'][]       = $Row;
    }
  
    echo json_encode( $output );
}}
?>
