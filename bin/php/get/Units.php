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
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Location'];
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
      2 =>  'Loc.Tag',
      3 =>  'Unit.Unit',
      4 =>  'Unit.Type',
      5 =>  'Unit.Status'
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
                          Unit.ID AS ID,
                          Unit.ID AS Unit_ID,
                          Unit.State As City_ID,
                          Unit.State As Unit_City_ID,
                          Customer.ID AS Customer_ID,
                          Customer.Name AS Customer_Name,
                          Location.Loc AS Location_ID,
                          Location.Tag AS Location_Name,
                          Unit.Unit AS Building_ID,
                          Unit.Unit AS Unit_Building_ID,
                          Unit.Type AS Type,
                          Unit.State AS Name,
                          Ticket.ID AS Ticket_ID,
                          CASE  WHEN Unit.Status = 0 THEN 'Enabled' 
                                WHEN Unit.Status = 1 THEN 'Disabled'
                                WHEN Unit.Status = 2 THEN 'Demolished'
                                ELSE 'Other'
                          END AS Status
                  FROM    Elev AS Unit
                          LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                          LEFT JOIN (
                            SELECT  Owner.ID,
                                    Rol.Name
                            FROM    Owner
                                    LEFT JOIN Rol ON Rol.ID = Owner.Rol
                          ) AS Customer ON Unit.Owner = Customer.ID
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
