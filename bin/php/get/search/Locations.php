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

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset( $_GET[ 'search'] ) ){
      $parameters[ ] = $_GET[ 'search' ];
      $search[ ] = "Location.Tag LIKE '%' + ? + '%'";
    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[ ] = $_GET[ 'search' ];

    $sQuery = " 
      SELECT  Top 10
              tbl.FieldName,
              tbl.FieldValue
      FROM    (
                SELECT  attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
                        attr.insRow.value('.', 'nvarchar(max)') as FieldValue
                FROM    ( Select  convert(xml, (select i.* for xml raw)) as insRowCol
                          FROM ( (
                            SELECT  Top 100
                                    Location.Tag           AS Name,
                                    Customer.Name           AS Customer_Name,
                                    Location_Type.Name   AS Type,
                                    Zone.Name            AS Division_Name,
                                    Route.Name           AS Route_Name,
                                    Employee.fFirst + ' ' + Employee.Last AS Mechanic_Name,
                                    Location.Address     AS Street,
                                    Location.City          AS City,
                                    Location.State         AS State,
                                    Location.Zip           AS Zip,
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
      GROUP BY  tbl.FieldName, tbl.FieldValue;;";

    $rResult = $database->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $output = array( );
      while ( $Row = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
        $output[]       = $Row;
      }
      echo json_encode( $output );
    }
}?>
