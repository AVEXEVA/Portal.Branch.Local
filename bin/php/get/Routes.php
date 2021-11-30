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
    if(     !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Route' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Route' ] )
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

      if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Name'];
        $conditions[] = "Route.Name LIKE '%' + ? + '%'";
      }

      if( isset($_GET[ 'Person' ] ) && !in_array( $_GET[ 'Person' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Person'];
        $conditions[] = "Employee.fFirst + ' ' + Employee.Last LIKE '%' + ? + '%'";
      }      

      /*if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

        $parameters[] = $_GET['Search'];
        $search[] = "Route.ID LIKE '%' + ? + '%'";

        $parameters[] = $_GET['Search'];
        $search[] = "Route.Name LIKE '%' + ? + '%'";

      }*/

      $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
      $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

      $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
      $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

      $Columns = array(
        0 =>  'Route.ID',
        1 =>  'Route.Name',
        2 =>  'Route.Status',
        3 =>  'Locations.Count',
        4 =>  'Units.Count'
      );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Route.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

      $sQuery = " SELECT *
                  FROM (
                    SELECT  ROW_NUMBER() OVER (ORDER BY Route.Name asc) AS ROW_COUNT,
                            Route.ID      AS ID,
                            Route.Name      AS Name,
                            Employee.fFirst + ' ' + Employee.Last AS Person,
                            Locations.Count AS Locations,
                            Units.Count AS Units,
                            Violation.Count As Violation,
                            Ticket.Assigned,
                            Attendance.[End] ,
                            Attendance.[Start]
                    FROM    Route
                            LEFT JOIN Emp AS Employee ON Route.Mech = Employee.fWork
                            LEFT JOIN TicketO AS Ticket ON Ticket.fWork = Employee.fWork
                              LEFT JOIN Attendance ON Attendance.[User] = Employee.ID
                            LEFT JOIN (
                              SELECT    Loc.Route,
                                        Count( Loc.Loc ) AS Count,
                                        Sum( Units.Count ) AS Unit
                              FROM       Loc
                              LEFT JOIN (
                                          SELECT    Elev.Loc,
                                                    Count( Elev.ID ) AS Count
                                          FROM      Elev
                                          GROUP BY  Elev.Loc
                                        ) AS Units ON Loc.Loc = Units.Loc
                              GROUP BY  Loc.Route
                            ) AS Locations ON Route.ID = Locations.Route
                            LEFT JOIN (
                              SELECT    Loc.Route,
                                        Sum( Units.Count ) AS Count
                              FROM      Loc
                                        LEFT JOIN (
                                          SELECT    Elev.Loc,
                                                    Count( Elev.ID ) AS Count
                                          FROM      Elev
                                          GROUP BY  Elev.Loc
                                        ) AS Units ON Loc.Loc = Units.Loc
                              GROUP BY  Loc.Route
                            ) AS Units ON Route.ID = Locations.Route
                            LEFT JOIN (
                             SELECT Loc.Route,  COUNT( Violation.ID ) AS Count 
                  FROM Violation 
              LEFT JOIN Loc ON Violation.Loc = Loc.ID GROUP BY Loc.Route
                            ) AS Violation ON Route.ID = Locations.Route
                          WHERE   ({$conditions}) AND ({$search})
                               ) AS Tbl
                          WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
  //    echo $sQuery;
      $rResult = $database->query(
        null,
        $sQuery,
        $parameters
      ) or die(print_r(sqlsrv_errors()));

      $sQueryRow = "SELECT  Route.ID
                    FROM    Route
                            LEFT JOIN Emp AS Employee ON Route.Mech = Employee.fWork
                            LEFT JOIN (
                              SELECT    Loc.Route,
                                        Count( Loc.Loc ) AS Count,
                                        Sum( Units.Count ) AS Unit
                              FROM      Loc
                                        LEFT JOIN (
                                          SELECT    Elev.Loc,
                                                    Count( Elev.ID ) AS Count
                                          FROM      Elev
                                          GROUP BY  Elev.Loc
                                        ) AS Units ON Loc.Loc = Units.Loc
                              GROUP BY  Loc.Route
                            ) AS Locations ON Route.ID = Locations.Route
                            LEFT JOIN (
                              SELECT    Loc.Route,
                                        Sum( Units.Count ) AS Count
                              FROM      Loc
                                        LEFT JOIN (
                                          SELECT    Elev.Loc,
                                                    Count( Elev.ID ) AS Count
                                          FROM      Elev
                                          GROUP BY  Elev.Loc
                                        ) AS Units ON Loc.Loc = Units.Loc
                              GROUP BY  Loc.Route
                            ) AS Units ON Route.ID = Locations.Route
                    WHERE   ({$conditions}) AND ({$search})";

      $fResult = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));

      $iFilteredTotal = 0;
      $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
      $_SESSION[ 'Tables' ][ 'Routes' ] = isset( $_SESSION[ 'Tables' ][ 'Routes' ]  ) ? $_SESSION[ 'Tables' ][ 'Routes' ] : array( );
      if( count( $_SESSION[ 'Tables' ][ 'Routes' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Routes' ] as &$Value ){ $Value = false; } }
      $_SESSION[ 'Tables' ][ 'Routes' ][ 0 ] = $_GET;
      while( $Row = sqlsrv_fetch_array( $fResult ) ){
          $_SESSION[ 'Tables' ][ 'Routes' ][ $Row[ 'ID' ] ] = true;
          $iFilteredTotal++;
      }

      $parameters = array( );
      $sQuery = " SELECT  COUNT(Route.ID)
                  FROM    Route;";
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