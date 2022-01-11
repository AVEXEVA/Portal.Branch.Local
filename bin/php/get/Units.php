<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start();
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

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Unit.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'City_ID' ] ) && !in_array( $_GET[ 'City_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'City_ID' ];
      $conditions[] = "Unit.State LIKE '%' + ? + '%' ";
    }
    if( isset($_GET[ 'Building_ID' ] ) && !in_array( $_GET[ 'Building_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'Building_ID' ];
      $conditions[] = "Unit.Unit LIKE '%' + ? + '%' ";
    }
    if( isset($_GET[ 'Territory_ID' ] ) && !in_array( $_GET[ 'Territory_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Territory_ID'];
      $conditions[] = "Territory.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Territory_Name' ] ) && !in_array( $_GET[ 'Territory_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Territory_Name'];
      $conditions[] = "Territory.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Division_ID' ] ) && !in_array( $_GET[ 'Division_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Division_ID'];
      $conditions[] = "Division.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Division_Name' ] ) && !in_array( $_GET[ 'Division_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Division_Name'];
      $conditions[] = "Division.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Route_ID' ] ) && !in_array( $_GET[ 'Route_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Route_ID'];
      $conditions[] = "Route.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Route_Name' ] ) && !in_array( $_GET[ 'Route_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Route_Name'];
      $conditions[] = "Route.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer_Name' ] ) && !in_array( $_GET[ 'Customer_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer_Name'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer_ID' ] ) && !in_array( $_GET[ 'Customer_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer_ID'];
      $conditions[] = "Customer.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer_Name' ] ) && !in_array( $_GET[ 'Customer_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer_Name'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location_ID' ] ) && !in_array( $_GET[ 'Location_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Location_ID'];
      $conditions[] = "Location.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location_Name' ] ) && !in_array( $_GET[ 'Location_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Location_Name'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Type'];
      $conditions[] = "Unit.Type LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Status'] ;
      $conditions[] = "Unit.Status LIKE '%' + ? + '%'";
    }

    /*if( $Privileges[ 'Unit' ][ 'Other' ] < 4 ){
        $parameters [] = $User[ 'fWork' ];
        $conditions[] = "Unit.ID IN ( SELECT Ticket.Unit FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LElev AS Unit FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Elev AS Unit FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Unit)";
    }*/

    /*Search Filters*/
    //if( isset( $_GET[ 'search' ] ) ){ }


    /*Concatenate Filters*/
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    $Columns = array(
      0 =>  'Unit.ID',
      1 =>  'Unit.State',
      2 =>  'Unit.Unit',
      3 =>  'Territory.Name',
      4 =>  'Division.Name',
      5 =>  'Route.Name',
      6 =>  'Customer.Name',
      7 =>  'Loc.Tag',
      8 =>  'Unit.Type',
      9 =>  'Unit.Status',
      10 =>  'Ticket.ID'
    );

    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Unit.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Unit.ID                   AS ID,
                          Unit.State                As City_ID,
                          Unit.Unit                 AS Building_ID,
                          Territory.ID              AS Territory_ID,
                          Territory.Name            AS Territory_Name,
                          Division.ID               AS Division_ID,
                          Division.Name             AS Division_Name,
                          Route.ID                  AS Route_ID,
                          Route.Name                AS Route_Name,
                          Customer.ID               AS Customer_ID,
                          Customer.Name             AS Customer_Name,
                          Location.Loc              AS Location_ID,
                          Location.Tag              AS Location_Name,
                          Location.Address          AS Location_Street,
                          Location.City             AS Location_City,
                          Location.State            AS Location_State,
                          Location.Zip              AS Location_Zip,
                          Unit.fDesc                AS Description,
                          CASE  WHEN Unit.Type =  0 THEN 'Elevator'
                                WHEN Unit.Type =  1 THEN 'Escalator'
                                WHEN Unit.Type =  2 THEN 'Moving-Walk'
                          END AS Type,
                          Tickets.Count             AS Tickets,
                          Ticket.ID                 AS Ticket_ID,
                          CASE  WHEN Unit.Status = 0 THEN 'Enabled'
                                WHEN Unit.Status = 1 THEN 'Disabled'
                                WHEN Unit.Status = 2 THEN 'Demolished'
                                ELSE 'Other'
                          END AS Status
                  FROM    Elev AS Unit
                          LEFT JOIN Loc   AS Location   ON Unit.Loc = Location.Loc
                          LEFT JOIN Terr  AS Territory  ON Territory.ID = Location.Terr
                          LEFT JOIN Zone  AS Division   ON Division.ID = Location.Zone
                          LEFT JOIN Route               ON Route.ID = Location.Route
                          LEFT JOIN (
                            SELECT  Owner.ID,
                                    Rol.Name
                            FROM    Owner
                                    LEFT JOIN Rol ON Rol.ID = Owner.Rol
                          ) AS Customer ON Unit.Owner = Customer.ID
                          LEFT JOIN (
                            SELECT    Tickets.Unit_ID,
                                      Sum( Tickets.Count ) AS Count
                            FROM      (
                                        (
                                          SELECT    TicketO.LElev AS Unit_ID,
                                                    Count( TicketO.ID ) AS Count
                                          FROM      TicketO
                                          WHERE     TicketO.Assigned >= 2
                                                    AND TicketO.Assigned <= 3
                                          GROUP BY  TicketO.LElev
                                        )
                                      ) AS Tickets
                            GROUP BY  Tickets.Unit_ID
                          ) AS Tickets ON Tickets.Unit_ID = Unit.ID
                          LEFT JOIN (
                            SELECT    ROW_NUMBER() OVER ( PARTITION BY TicketD.Elev ORDER BY TicketD.EDate DESC ) AS ROW_COUNT,
                                      TicketD.Elev AS Unit,
                                      TicketD.ID ,
                                      TicketD.EDate AS Date
                            FROM      TicketD
                          ) AS Ticket ON Ticket.Unit = Unit.ID AND Ticket.ROW_COUNT = 1
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";

      $rResult = $database->query(
        null,
        $sQuery,
        $parameters
      ) or die(print_r(sqlsrv_errors()));

      $sQueryRow = "SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                            Unit.ID AS ID,
                            Unit.State AS City_ID,
                            Customer.ID AS Customer_ID,
                            Customer.Name AS Customer_Name,
                            Location.Loc AS Location_ID,
                            Location.Tag AS Location_Name,
                            Unit.Unit AS Building_ID,
                            Unit.Type AS Type,
                            Ticket.ID AS Ticket_ID,
                            Ticket.Date AS Ticket_Date,
                            Unit.Status AS Status
                    FROM    Elev AS Unit
                            LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                            LEFT JOIN Terr  AS Territory  ON Territory.ID = Location.Terr
                            LEFT JOIN Zone AS Division ON Division.ID = Location.Zone
                            LEFT JOIN Route               ON Route.ID = Location.Route
                            LEFT JOIN (
                              SELECT  Owner.ID,
                                      Rol.Name
                              FROM    Owner
                                      LEFT JOIN Rol ON Rol.ID = Owner.Rol
                          ) AS Customer ON Unit.Owner = Customer.ID
                           LEFT JOIN (
                            SELECT    ROW_NUMBER() OVER ( PARTITION BY TicketD.Elev ORDER BY TicketD.EDate DESC ) AS ROW_COUNT,
                                      TicketD.Elev AS Unit,
                                      TicketD.ID,
                                      TicketD.EDate AS Date
                            FROM      TicketD
                          ) AS Ticket ON Ticket.Unit = Unit.ID AND Ticket.ROW_COUNT = 1
                    WHERE   ({$conditions}) AND ({$search});";

      $fResult = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));

      $iFilteredTotal = 0;
      $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
      $_SESSION[ 'Tables' ][ 'Units' ] = isset( $_SESSION[ 'Tables' ][ 'Units' ]  ) ? $_SESSION[ 'Tables' ][ 'Units' ] : array( );
      if( count( $_SESSION[ 'Tables' ][ 'Units' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Units' ] as &$Value ){ $Value = false; } }
      $_SESSION[ 'Tables' ][ 'Units' ][ 0 ] = $_GET;
      while( $Row = sqlsrv_fetch_array( $fResult ) ){
          $_SESSION[ 'Tables' ][ 'Units' ][ $Row[ 'ID' ] ] = true;
          $iFilteredTotal++;
      }

      $parameters = array( );
      $sQuery = " SELECT  COUNT(Elev.ID)
                  FROM    Elev;";
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
          //$Row[ 'Ticket_Date' ] = !is_null( $Row[ 'Ticket_Date' ] ) ? date( 'm/d/Y h:i A', strtotime( $Row[ 'Ticket_Date' ] ) ) : null;
          $output['aaData'][]       = $Row;
      }
      echo json_encode( $output );
}}
?>
