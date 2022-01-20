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
    if(   !isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Location' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Location' ] )
    ){ ?><?php print json_encode( array( 'data' => array( ) ) );?><?php }
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

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Location.Loc LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Name'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer_ID' ] ) && !in_array( $_GET[ 'Customer_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer_ID'];
      $conditions[] = "Customer.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer_Name' ] ) && !in_array( $_GET[ 'Customer_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer_Name'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Type'];
      $conditions[] = "Location_Type.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Division_ID' ] ) && !in_array( $_GET[ 'Division_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Division_ID'];
      $conditions[] = "Zone.ID = ?";
    }
    if( isset($_GET[ 'Division_Name' ] ) && !in_array( $_GET[ 'Division_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Division_Name'];
      $conditions[] = "Zone.Name = ?";
    }
    if( isset($_GET[ 'Route_ID' ] ) && !in_array( $_GET[ 'Route_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Route_ID'];
      $conditions[] = "Route.ID = ?";
    }
    if( isset($_GET[ 'Route_Name' ] ) && !in_array( $_GET[ 'Route_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Route_Name'];
      $conditions[] = "Route.Name = ?";
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

    /*if( $Privileges[ 'Location' ][ 'Other' ] < 4 ){
        $parameters [] = $User[ 'fWork' ];
        $conditions[] = "Location.Loc IN ( SELECT Ticket.Location FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LID AS Location FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Loc AS Location FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Location)";
    }*/

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    /*ROW NUMBER*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

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

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          'Location'                            AS Entity,
                          Location.Loc                          AS ID,
                          Location.Tag                          AS Name,
                          Customer.ID                           AS Customer_ID,
                          Customer.Name                         AS Customer_Name,
                          Location_Type.Name                    AS Type,
                          Zone.ID                               AS Division_ID,
                          Zone.Name                             AS Division_Name,
                          Route.ID                              AS Route_ID,
                          Route.Name                            AS Route_Name,
                          Employee.ID                           AS Mechanic_ID,
                          Employee.fFirst + ' ' + Employee.Last AS Mechanic_Name,
                          Location.Address                      AS Street,
                          Location.City                         AS City,
                          Location.State                        AS State,
                          Location.Zip                          AS Zip,
                          Location.Latt                         AS Latitude,
                          Location.fLong                        AS Longitude,
                          CASE  WHEN  Location_Units.Count IS NULL THEN 0
                                ELSE  Location_Units.Count 
                          END                                   AS Units,
                          CASE  WHEN Location.Maint = 0 THEN 'Active' 
                                ELSE 'Inactive' END             AS Maintained,
                          CASE  WHEN Location.Status = 0 THEN 'Active' 
                                ELSE 'Inactive' END             AS Status,
                          CASE  WHEN Tickets_Assigned.Count IS NULL THEN 0 
                                ELSE Tickets_Assigned.Count END AS Tickets_Assigned,
                          CASE  WHEN Tickets_Active.Count IS NULL THEN 0 
                                ELSE Tickets_Active.Count END   AS Tickets_Active
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
                          LEFT JOIN (
                            SELECT    Tickets.Location,
                                      Sum( Tickets.Count ) AS Count
                            FROM      (
                                        (
                                          SELECT    TicketO.LID AS Location,
                                                    Count( TicketO.ID ) AS Count
                                          FROM      TicketO
                                          WHERE     TicketO.Assigned = 1
                                          GROUP BY  TicketO.LID
                                        )
                                      ) AS Tickets
                            GROUP BY  Tickets.Location
                          ) AS Tickets_Assigned ON Tickets_Assigned.Location = Location.Loc
                          LEFT JOIN (
                            SELECT    Tickets.Location,
                                      Sum( Tickets.Count ) AS Count
                            FROM      (
                                        (
                                          SELECT    TicketO.LID AS Location,
                                                    Count( TicketO.ID ) AS Count
                                          FROM      TicketO
                                          WHERE           TicketO.Assigned >= 2
                                                    AND   TicketO.Assigned <= 3
                                          GROUP BY  TicketO.LID
                                        )
                                      ) AS Tickets
                            GROUP BY  Tickets.Location
                          ) AS Tickets_Active ON Tickets_Active.Location = Location.Loc
                          LEFT JOIN Emp AS Employee ON Employee.fWork = Route.Mech

                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    /*Location_Revenue.Revenue AS Revenue,
    Location_Labor.Labor AS Labor,
    Location_Materials.Materials AS Materials*/
    /*LEFT JOIN (
      SELECT    Job.Loc AS Location,
                Sum( Invoice.Amount ) AS Revenue
      FROM      Invoice
                LEFT JOIN Job ON Invoice.Job = Job.ID
      GROUP BY  Job.Loc
    ) AS Location_Revenue ON Location_Revenue.Location = Location.Loc
    LEFT JOIN (
        SELECT    Job.Loc AS Location,
                  Sum(JobI.Amount) AS Labor
        FROM      JobI
                  LEFT JOIN Job ON JobI.Job = Job.ID
        WHERE     JobI.Type   = 1
                  AND JobI.Labor  = 1
                  AND (
                    (
                        (Job.Type    =   2 OR Job.Type = 3)
                        AND Job.Status  <>  0
                    )
                    OR  (Job.Type <>  2 AND Job.Type <> 3)
                  )
        GROUP BY  Job.Loc
    ) AS Location_Labor ON Location_Labor.Location = Location.Loc
    LEFT JOIN (
      SELECT    Job.Loc AS Location,
                Sum(JobI.Amount) AS Materials
      FROM      JobI
                LEFT JOIN Job ON JobI.Job = Job.ID
      WHERE     (
                  JobI.Labor <> 1
                  OR JobI.Labor = ''
                  OR JobI.Labor = 0
                  OR JobI.Labor = ' '
                  OR JobI.Labor IS NULL
                )
                AND JobI.Type = 1
                AND (
                  (
                      (Job.Type    =   2 OR Job.Type = 3)
                      AND Job.Status  <>  0
                  )
                  OR  (Job.Type <>  2 AND Job.Type <> 3)
                )
      GROUP BY  Job.Loc
    ) AS Location_Materials ON Location_Materials.Location = Location.Loc*/
    $rResult = $database->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    while ( $Row = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
      $output['aaData'][]   = $Row;
    }

    $sQueryRow = "
        SELECT Count( Tbl.ID ) AS Count
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Location.Loc         AS ID,
                          Location.Tag         AS Name,
                          Customer.Name        AS Customer,
                          Location_Type.Name   AS Type,
                          Zone.Name            AS Division,
                          Route.Name           AS Route,
                          Location.Address     AS Street,
                          Location.City        AS City,
                          Location.State       AS State,
                          Location.Zip         AS Zip,
                          CASE  WHEN  Location_Units.Count IS NULL THEN 0
                                ELSE  Location_Units.Count
                          END AS Units,
                          Location.Maint       AS Maintained,
                          Location.Status      AS Status
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

    $stmt = $database->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));
    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];

    $sQuery = " SELECT  COUNT(Elev.ID)
                FROM    Elev;";
    $rResultTotal = $database->query(null,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output[ 'iTotalRecords' ] = $iTotal;
    $output[ 'iTotalDisplayRecords' ] = $iFilteredTotal;

    echo json_encode( $output );
  }
}
?>
