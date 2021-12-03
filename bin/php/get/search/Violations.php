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
      ||  !isset( $Privileges[ 'Violation' ] )
      ||  !check( privilege_read, level_group, $Privileges[ 'Violation' ] )
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
      $conditions[] = "Violation.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Name'];
      $conditions[] = "Violation.Name LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Date_Start' ] ) && !in_array( $_GET[ 'Date_Start' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'Date_Start' ];
      $conditions[] = "Violation.fDate Like >= ?";
    }
    if( isset( $_GET[ 'Date_End' ] ) && !in_array( $_GET[ 'Date_End' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'Date_End' ];
      $conditions[] = "Violation.fDate Like <= ?";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Location'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Status'];
      $conditions[] = "Violation.Status LIKE '%' + ? + '%'";
    }

    $notStatuses = "'" . implode(
      "', '",
      array(
        'Dismissed',
        'Rejected',
        'Expired',
        'Completed'
      )
    ) . "'";

    if( isset( $_GET[ 'search' ] ) && !in_array( $_GET[ 'search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['search'];
      $search[] = "Violation.ID LIKE '%' + ? + '%'";

      $parameters[] = $_GET['search'];
      $search[] = "Violation.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";
     

    }
     $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;
 $parameters[] = $_GET[ 'search' ];

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

   
    $Columns = array(
      0 =>  'Violation.ID',
      1 =>  'Violation.Name',
      2 =>  'Violation.fDate',
      3 =>  'Location.Tag',
      4 =>  'Violation.Status'
    );
    
    $Order = isset($_GET['order']['column'])?isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Violation.Status": "Violation.Status";
    $Direction = isset($_GET['order']['dir'])? in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )? $_GET['order']['dir'] : 'ASC' : 'ASC';

  
 

          $sQuery = " 
      SELECT  Top 10
              tbl.FieldName,
              tbl.FieldValue
      FROM    (
                SELECT  attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
                        attr.insRow.value('.', 'nvarchar(max)') as FieldValue
                FROM    ( Select  convert(xml, (select i.* for xml raw)) as insRowCol
                          FROM ( SELECT  *
      FROM  (
                 SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                        Violation.ID        AS ID,
                         Violation.Name      AS Name,
                      	Violation.fDate     AS Date,
                      	Location.Loc        AS Location_ID,
                        Customer.Name       AS Customer,
                        Location.Tag        AS Locations,
                        Customer.ID         AS Customer_ID,
                          Customer.Name       AS Customer_Name,
                          Units.ID        AS Unit_ID,
                        Units.fDesc        AS Units
                  FROM    Violation
                          LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                          LEFT JOIN Unit AS Units ON Units.ID = Violation.Elev
                          LEFT JOIN (
                            SELECT  Owner.ID,
                                    Rol.Name
                            FROM    Owner
                                    LEFT JOIN Rol ON Rol.ID = Owner.Rol
                        ) AS Customer ON Location.Owner = Customer.ID
                  WHERE   ({$conditions}) AND ({$search})  
                ) AS Tbl
      WHERE     Tbl.ROW_COUNT >= ?
            AND Tbl.ROW_COUNT <= ?
            )  as i
                   ) as insRowTbl
              CROSS APPLY insRowTbl.insRowCol.nodes('/row/@*') as attr(insRow)
            ) AS tbl
      WHERE     tbl.FieldValue LIKE '%' + ? + '%'
      GROUP BY  tbl.FieldName, tbl.FieldValue;";
  
   // print_r($parameters);
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
