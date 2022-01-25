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
        FROM        [Connection]
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
        FROM    [Privilege]
        WHERE   Privilege.[User] = ?;",
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
        ||  !isset( $Privileges[ 'Division' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Division' ] )
    ){ ?><?php require('404.html');?><?php }
  else {
    $output = array(
        'sEcho'             => isset( $_GET[ 'draw' ] ) ? intval( $_GET[ 'draw' ] ) : 1,
        'iTotalRecords'       =>  0,
        'iTotalDisplayRecords'  =>  0,
        'aaData'            =>  array(),
        'options'         => array( )
    );

    /*Parse GET*/
    /*None*/

    $conditions = array( );
    $search   = array( );

    /*Default Filters*/
    /*NONE*/

    if( isset( $_GET[ 'ID' ] ) && !in_array(  $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Division.ID LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Name' ] ) && !in_array(  $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Contact'];
      $conditions[] = "Division.Name LIKE '%' + ? + '%'";
    }

    /*Search Filters*/
    //if( isset( $_GET[ 'search' ] ) ){ }

    /*Concatenate Filters*/
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    /*ROW NUMBER*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] -25 : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 25 : 0;

    /*Order && Direction*/
    //update columns from bin/js/tickets/table.js
    $Columns = array(
      0 =>  'Division.ID',
      1 =>  'Division.Name'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
      ? $Columns[ $_GET['order']['column'] ]
      : "Division.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    /*Perform Query*/
    $Query = "SELECT  *
              FROM  (
                      SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                              Division.ID                AS ID,
                              Division.Name              AS Name,
                              Locations.Count            AS Locations,
                              CASE  WHEN Units_Elevators.Count IS NULL THEN 0
                                    ELSE Units_Elevators.Count END AS Units_Elevators,
                              CASE  WHEN Units_Escalators.Count IS NULL THEN 0
                                    ELSE Units_Escalators.Count END AS Units_Escalators,
                              CASE  WHEN Units_Others.Count IS NULL THEN 0
                                    ELSE Units_Others.Count END AS Units_Others,
                              CASE  WHEN Violations_Office.Count IS NULL THEN 0
                                    ELSE Violations_Office.Count END AS Violations_Office,
                              CASE  WHEN Violations_Field.Count IS NULL THEN 0
                                    ELSE Violations_Field.Count END AS Violations_Field,
                              CASE  WHEN Tickets_Assigned.Count IS NULL THEN 0
                                    ELSE Tickets_Assigned.Count END AS Tickets_Assigned,
                              CASE  WHEN Tickets_Active.Count IS NULL THEN 0
                                    ELSE Tickets_Active.Count END AS Tickets_Active
                      FROM    Zone AS Division
                              LEFT JOIN (
                                SELECT    Loc.Zone AS Division,
                                          Count( Loc.Loc ) AS Count
                                FROM      Loc 
                                GROUP BY  Loc.Zone
                              ) AS Locations ON Locations.Division = Division.ID
                              LEFT JOIN (
                                SELECT    Loc.Zone AS Division,
                                          Count( Elev.ID ) AS Count 
                                FROM      Elev 
                                          LEFT JOIN Loc ON Elev.Loc = Loc.Loc
                                WHERE     Elev.Type IN ( 'Elevator', 'Roped Hydro', 'Hydraulic' )
                                GROUP BY  Loc.Zone
                              ) AS Units_Elevators ON Units_Elevators.Division = Division.ID
                              LEFT JOIN (
                                SELECT    Loc.Zone AS Division,
                                          Count( Elev.ID ) AS Count 
                                FROM      Elev 
                                          LEFT JOIN Loc ON Elev.Loc = Loc.Loc
                                WHERE     Elev.Type IN ( 'Escalator' )
                                GROUP BY  Loc.Zone
                              ) AS Units_Escalators ON Units_Escalators.Division = Division.ID
                              LEFT JOIN (
                                SELECT    Loc.Zone AS Division,
                                          Count( Elev.ID ) AS Count 
                                FROM      Elev 
                                          LEFT JOIN Loc ON Elev.Loc = Loc.Loc
                                WHERE     Elev.Type NOT IN ( 'Elevator', 'Roped Hydro', 'Hydraulic', 'Escalator' )
                                GROUP BY  Loc.Zone
                              ) AS Units_Others ON Units_Others.Division = Division.ID
                              LEFT JOIN (
                                SELECT    Loc.Zone AS Division,
                                          Count( Violation.ID ) AS Count 
                                FROM      Violation 
                                          LEFT JOIN Loc ON Violation.Loc = Loc.Loc
                                WHERE     Violation.Status = 'Preliminary Report'
                                GROUP BY  Loc.Zone
                              ) AS Violations_Office ON Violations_Office.Division = Division.ID
                              LEFT JOIN (
                                SELECT    Loc.Zone AS Division,
                                          Count( Violation.ID ) AS Count 
                                FROM      Violation 
                                          LEFT JOIN Loc ON Violation.Loc = Loc.Loc
                                WHERE     Violation.Status = 'Job Created'
                                GROUP BY  Loc.Zone
                              ) AS Violations_Field ON Violations_Field.Division = Division.ID
                              LEFT JOIN (
                                SELECT    Loc.Zone AS Division,
                                          Count( TicketO.ID ) AS Count 
                                FROM      TicketO
                                          LEFT JOIN Loc ON TicketO.LID = Loc.Loc
                                WHERE     TicketO.Assigned = 1
                                GROUP BY  Loc.Zone
                              ) AS Tickets_Assigned ON Tickets_Assigned.Division = Division.ID
                              LEFT JOIN (
                                SELECT    Loc.Zone AS Division,
                                          Count( TicketO.ID ) AS Count 
                                FROM      TicketO
                                          LEFT JOIN Loc ON TicketO.LID = Loc.Loc
                                WHERE         TicketO.Assigned >= 2
                                          AND TicketO.Assigned <= 3
                                GROUP BY  Loc.Zone
                              ) AS Tickets_Active ON Tickets_Active.Division = Division.ID
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

    $sQueryRow = "  SELECT  Count( Territory.ID ) AS Count
            FROM  Terr AS Territory
            WHERE   ({$conditions}) AND ({$search})";

      $stmt = \singleton\database::getInstance( )->query(
        null,
        $sQueryRow,
        $parameters
      ) or die(print_r(sqlsrv_errors()));

      $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];
      sqlsrv_cancel( $stmt );

      $sQuery = " SELECT  COUNT(Territory.ID)
                  FROM    Terr AS Territory;";
      $rResultTotal = \singleton\database::getInstance( )->query(
        null,
        $sQuery,
        array( )
      ) or die(print_r(sqlsrv_errors()));
      $aResultTotal = sqlsrv_fetch_array($rResultTotal);
      $iTotal = $aResultTotal[0];

      $output[ 'iTotalRecords' ] = $iTotal;
      $output[ 'iTotalDisplayRecords' ] = $iFilteredTotal;
      echo json_encode( $output );
    }
}?>
