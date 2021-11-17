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
        FROM    dbo.Emp
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
  $Check = check( 
      privilege_read, 
      level_server, 
      isset( $Privileges[ 'User' ] ) 
          ? $Privileges[ 'User' ] 
          : 0
  );

  if( !isset($Connection['ID'])  || !$Check ){?><html><head><script>document.location.href='../login.php?Forward=users.php';</script></head></html><?php }
  else {
    /*Construct Output Array*/
    $output = array(
          'sEcho' => isset( $_GET[ 'draw' ] ) ? intval( $_GET[ 'draw' ] ) : 1,
          'iTotalRecords' =>  0,
          'iTotalDisplayRecords' =>  0,
          'aaData' =>  array(),
          'options' => array( )
      );

    /*Parse GET*/
    $conditions = array( );
    $search   = array( );

    /*Filter $_GET Columns to SQL*/
      if( isset( $_GET[ 'ID' ] ) && !in_array(  $_GET[ 'ID' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['ID'];
        $conditions[] = "Employee.ID LIKE '%' + ? + '%'";
      }
      if( isset( $_GET[ 'First_Name' ] ) && !in_array( $_GET[ 'First_Name' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Person'];
        $conditions[] = "Employee.fFirst LIKE '%' + ? + '%'";
      }
      if( isset( $_GET[ 'Last_Name' ] ) && !in_array( $_GET[ 'Last_Name' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Last_Name'];
        $conditions[] = "Employee.Last LIKE '%' + ? + '%'";
      }

    /*Search Filters*/
    /*NONE*/

    /*Concatenate Filters*/
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
      $search   = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    /*Row Number*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    /*Order && Direction*/
    //update columns from bin/js/tickets/table.js
    $Columns = array(
      0 =>  'Employee.ID',
      1 =>  'Employee.fFirst',
      2 =>  'Employee.Last'
      );
      $Order = isset( $Columns[ $_GET['order']['column'] ] )
          ? $Columns[ $_GET['order']['column'] ]
          : "Employee.ID";
      $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
        ? $_GET['order']['dir']
        : 'ASC';

    /*Perform Query*/
    $Query =
    " SELECT  *
      FROM  (
              SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                      Employee.ID AS ID,
                      Employee.fFirst AS First_Name,
                      Employee.Last AS Last_Name,
                      tblWork.Super AS Supervisor
              FROM    Emp AS Employee
                      LEFT JOIN tblWork ON CONVERT( INT, SUBSTRING( tblWork.Members, 2, LEN( tblWork.Members ) - 2 ) ) = Emp.ID
              WHERE   ({$conditions}) AND ({$search})
            ) AS Tbl
      WHERE     Tbl.ROW_COUNT >= ?
            AND Tbl.ROW_COUNT <= ?;";
    $rResult = \singleton\database::getInstance( )->query(
        null,
      $Query,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    while ( $Ticket = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
        $output[ 'aaData' ][]       = $Ticket;
      }


      /*GET TOTAL NUMBER OF FILTERED ROWS*/
    $sQueryRow =
    " SELECT  Count( Employee.ID ) AS Count
      FROM    Emp AS Employee
      WHERE   ({$conditions}) AND ({$search});";

    $stmt = \singleton\database::getInstance( )->query(
        null,
      $sQueryRow,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];
    sqlsrv_cancel( $stmt );

    /*GET TOTAL NUMBER OF ROWS IN TABLE*/
    $sQuery = " SELECT  COUNT(Emp.ID)
                FROM    Emp;";
    $rResultTotal = \singleton\database::getInstance( )->query(
        null,  
      $sQuery,
      array( $User[ 'ID' ] )
    ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];


    /*SET OUTPUT*/
    $output[ 'iTotalRecords' ] = $iTotal;
    $output[ 'iTotalDisplayRecords' ] = $iFilteredTotal;
    /*SET OPTIONS*/
    /*NONE*/

    /*PRINT JSON*/
    echo json_encode( $output );
  }
}?>
