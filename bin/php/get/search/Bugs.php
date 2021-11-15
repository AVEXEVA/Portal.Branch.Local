<?php
/****************************NEEDS TO BE CONFIGURED*****************************/
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
    'read_and_close' => true
  ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
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
        "   SELECT  Privilege.Access_Table,
                    Privilege.User_Privilege,
                    Privilege.Group_Privilege,
                    Privilege.Other_Privilege
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
          $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    $Privileged = False;
    if( isset( $Privileges[ 'Admin' ] )
        && $Privileges[ 'Admin' ][ 'User_Privilege' ]  >= 4
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
    $parameters = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Location.Loc LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Name'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Type'];
      $conditions[] = "Location_Type.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Division' ] ) && !in_array( $_GET[ 'Division' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Division'];
      $conditions[] = "Zone.ID = ?";
    }
    if( isset($_GET[ 'Route' ] ) && !in_array( $_GET[ 'Route' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Route'];
      $conditions[] = "Route.ID = ?";
    }
    if( isset($_GET[ 'Street' ] ) && !in_array( $_GET[ 'Street' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Street'];
      $conditions[] = "Location.Address LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'City' ] ) && !in_array( $_GET[ 'City' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['City'];
      $conditions[] = "Location.City LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'State' ] ) && !in_array( $_GET[ 'State' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['State'];
      $conditions[] = "Location.State LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Zip' ] ) && !in_array( $_GET[ 'Zip' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Zip'];
      $conditions[] = "Location.Zip LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Maintained' ] ) && !in_array( $_GET[ 'Maintained' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Maintained'];
      $conditions[] = "Location.Maint LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Status'];
      $conditions[] = "Location.Status LIKE '%' + ? + '%'";
    }

    if( $Privileges[ 'Location' ][ 'Other_Privilege' ] < 4 ){
        $parameters [] = $User[ 'fWork' ];
        $conditions[] = "Location.Loc IN ( SELECT Ticket.Location FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LID AS Location FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Loc AS Location FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Location)";
    }

    if( isset( $_GET[ 'search' ] ) && !in_array( $_GET[ 'search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

      $parameters[] = $_GET['search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['search'];
      $search[] = "Location_Type.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['search'];
      $search[] = "Zone.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['search'];
      $search[] = "Route.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['search'];
      $search[] = "Location.Address LIKE '%' + ? + '%'";

      $parameters[] = $_GET['search'];
      $search[] = "Location.City LIKE '%' + ? + '%'";

      $parameters[] = $_GET['search'];
      $search[] = "Location.State LIKE '%' + ? + '%'";

      $parameters[] = $_GET['search'];
      $search[] = "Location.Zip LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] -25 : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 25 : 0;

    $Columns = array(
      0 =>  'Location.Loc',
      1 =>  'Location.Tag',
      2 =>  'OwnerWithRol.Name',
      3 =>  'Location.Address',
      4 =>  'Location.City',
      5 =>  'Location.State',
      6 =>  'Location.Zip'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Location.Loc";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
        ? $_GET['order']['dir']
        : 'ASC';

      $parameters[ ] = $_GET[ 'search' ];

      $Query =  "  SELECT    Top 10
                  tbl.FieldName,
                  tbl.FieldValue
        FROM    (

                SELECT
                    attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
                    attr.insRow.value('.', 'nvarchar(max)') as FieldValue
                FROM ( Select
                          convert(xml, (select i.* for xml raw)) as insRowCol
                       FROM (

                     (
                        SELECT  Top 10
                          Location.Loc         AS ID,
                          Location.Tag         AS Name,
                          Customer.ID           AS Customer_ID,
                          Customer.Name           AS Customer_Name,
                          Location_Type.Name   AS Type,
                          Zone.Name            AS Division_ID,
                          Zone.Name            AS Division_Name,
                          Route.ID           AS Route_ID,
                          Route.Name           AS Route_Name,
                          Employee.ID          AS Mechanic_ID,
                          Employee.fFirst + ' ' + Employee.Last AS Mechanic_Name,
                          Location.Address     AS Street,
                          Location.City        AS City,
                          Location.State       AS State,
                          Location.Zip         AS Zip,
                          CASE  WHEN  Location_Units.Count IS NULL THEN 0
                                ELSE  Location_Units.Count
                          END AS Units,
                          CASE  WHEN Location.Maint = 0 THEN 'Active'
                                ELSE 'Inactive' END AS Maintained,
                          CASE  WHEN Location.Status = 0 THEN 'Active'
                                ELSE 'Inactive' END AS Status
                  FROM    Loc AS Location
                          LEFT JOIN (
                              SELECT  Owner.ID,
                                      Rol.Name,
                                      Owner.Status
                              FROM    Owner
                                      LEFT JOIN Rol ON Owner.Rol = Rol.ID
                          ) AS Customer ON Location.Owner = Customer.ID
                          LEFT JOIN (
                            SELECT    Elev.Loc AS Location,
                                      Max( Elev.Building ) AS Name
                            FROM      Elev
                            GROUP BY  Elev.Loc
                          ) AS Location_Type ON Location_Type.Location = Location.Loc
                          LEFT JOIN Zone ON Location.Zone = Zone.ID
                          LEFT JOIN Route ON Location.Route = Route.ID
                          LEFT JOIN (
                            SELECT    Elev.Loc AS Location,
                                      Count( Elev.ID ) AS Count
                            FROM      Elev
                            GROUP BY  Elev.Loc
                          ) AS Location_Units ON Location_Units.Location = Location.Loc
                          LEFT JOIN Emp AS Employee ON Employee.fWork = Route.Mech
                  WHERE   ({$conditions}) AND ({$search})
                     ) ) as i
                     ) as insRowTbl
                CROSS APPLY insRowTbl.insRowCol.nodes('/row/@*') as attr(insRow)
              ) AS tbl
        WHERE     tbl.FieldValue LIKE '%' + ? + '%'
        GROUP BY  tbl.FieldName, tbl.FieldValue;";

    $rResult = \singleton\database::getInstance( )->query(
        null,
      $Query,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $output = array( );
      while ( $Row = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
        $output[]       = $Row;
      }
      echo json_encode( $output );
}}
?>
