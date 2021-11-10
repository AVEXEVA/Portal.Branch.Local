<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [
    'read_and_close' => true
  ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $result = $database->query(
      null,
      " SELECT  *
        FROM    Connection
        WHERE   Connection.Connector = ?
                AND Connection.Hash = ?;",
      array(
        $_SESSION[ 'User' ],
        $_SESSION[ 'Hash' ]
      )
    );
  $Connection = sqlsrv_fetch_array( $result );
  $result = $database->query(
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
  $User = sqlsrv_fetch_array( $result );
  $result = $database->query(
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
  while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
  $Privileged = False;
  if( isset( $Privileges[ 'Location' ] )
      && $Privileges[ 'Location' ][ 'User_Privilege' ]  >= 4
  ){ $Privileged = True; }
  if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
  else {

    $output = array(
        'sEcho'         =>  intval( $_GET[ 'draw' ] ),
        'iTotalRecords'     =>  0,
        'iTotalDisplayRecords'  =>  0,
        'aaData'        =>  array(),
        'options' => array( )
    );

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    /*if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
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
    }*/

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
    
    /*ROW NUMBER*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;
    
    $Columns = array(
      0 =>  'Location.Loc',
      1 =>  'Location.Tag'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Location.Loc";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Location.Loc           AS ID,
                          Location.Tag           AS Name
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
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    
    $rResult = $database->query(
      $conn,  
      $sQuery, 
      $parameters 
    ) or die(print_r(sqlsrv_errors()));

    while ( $Row = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
      $output['aaData'][]   = $Row;
    }

    $sQueryRow = "
        SELECT Count( Tbl.ID ) AS Count
                FROM (
                  SELECT  Location.Loc AS ID
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
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl";

    $stmt = $database->query( $conn, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));
    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];

    $sQuery = " SELECT  COUNT(Elev.ID)
                FROM    Elev;";
    $rResultTotal = $database->query($conn,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];
    
    $output[ 'iTotalRecords' ] = $iTotal;
    $output[ 'iTotalDisplayRecords' ] = $iFilteredTotal;

    echo json_encode( $output );
  }
}
?>