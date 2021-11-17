<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  *
          FROM    Connection
          WHERE   Connection.Connector = ?
                  AND Connection.Hash = ?;",
        array(
          $_SESSION[ 'User' ],
          $_SESSION[ 'Hash' ]
        )
      );
    $Connection = sqlsrv_fetch_array( $r );
    $User = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  Emp.*,
                    Emp.fFirst AS First_Name,
                    Emp.Last   AS Last_Name
            FROM    Emp
            WHERE   Emp.ID = ?;",
        array(
          $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $User );
    $r = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  Privilege.Access,
                    Privilege.Owner,
                    Privilege.Group,
                    Privilege.Other
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
          $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access' ] ] = $Privilege; }
    $Privileged = False;
    if( isset( $Privileges[ 'Location' ] )
        && $Privileges[ 'Location' ][ 'Owner' ]  >= 4
    ){ $Privileged = True; }
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
    $conn = null;

    $_GET['iDisplayStart'] = isset($_GET['start']) ? $_GET['start'] : 0;
    $_GET['iDisplayLength'] = isset($_GET['length']) ? $_GET['length'] : '-1';
    $Start = $_GET['iDisplayStart'];
    $Length = $_GET['iDisplayLength'];
    $End = $Length == '-1' ? 999999 : intval($Start) + intval($Length);

    $conditions = array( );
    $search = array( );
    $params = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['ID'];
      $conditions[] = "Route.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Name'];
      $conditions[] = "Route.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Employee' ] ) && !in_array( $_GET[ 'Employee' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Employee'];
      $conditions[] = "Employee.fFirst + ' ' + Employee.Last LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $params[] = $_GET['Search'];
      $search[] = "Route.ID LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Route.Name LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Employee.fFirst + ' ' + Emp.Last LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $params[] = $Start;
    $params[] = $End;
    $Columns = array(
      0 =>  'Route.ID',
      1 =>  'Route.Name',
      2 =>  "Employee.fFirst + ' ' + Employee.Last",
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Route.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Route.ID 		  AS ID,
                          Route.Name 	 	  AS Name,
                          Employee.fFirst + ' ' + Employee.Last AS Employee,
                          Locations.Count AS Locations,
                          Units.Count AS Units
                  FROM    Route
                          LEFT JOIN Emp AS Employee ON Route.Mech = Employee.fWork
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
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    $rResult = $database->query(
      $conn,
      $sQuery,
      $params
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Route.ID      AS ID,
                          Route.Name      AS Name,
                          Employee.fFirst + ' ' + Employee.Last AS Employee,
                          Locations.Count AS Locations,
                          Units.Count AS Units
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

    $options =  array( 'Scrollable' => SQLSRV_CURSOR_KEYSET );
    $stmt = $database->query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT( Route.ID )
                FROM    Route;";
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
      $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>
