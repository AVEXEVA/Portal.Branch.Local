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
      ||  !isset( $Privileges[ 'Customer' ] )
      ||  !check( privilege_read, level_group, $Privileges[ 'Customer' ] )
  ){ ?><?php print json_encode( array( 'data' => array( ) ) ); ?><?php }
  else {
    $conditions = array( );
    $search = array( );
    $parameters = array( );

      $_GET[ 'Date' ]   = isset( $_GET[ 'Date' ] )      && !in_array( $_GET[ 'Date' ], array( '', ' ', null ) )     ? DateTime::createFromFormat( 'm/d/Y', $_GET['Date'] )->format( 'Y-m-d 00:00:00.000' )    : null;

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Estimate.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Date' ] ) && !in_array( $_GET[ 'Date' ], array( '', ' ', null ) ) ){
      $parameters[] = date( 'Y-m-d', strtotime( $_GET['Date'] ) );
      $conditions[] = "Estimate.fDate LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Contact' ] ) && !in_array( $_GET[ 'Contact' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Contact'];
      $conditions[] = "Estimate.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Location'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Job' ] ) && !in_array( $_GET[ 'Job' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Job'];
      $conditions[] = "Job.fDesc LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Status'];
      $conditions[] = "Estimate.Status LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Title' ] ) && !in_array( $_GET[ 'Title' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Title'];
      $conditions[] = "Estimate.Name LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['Search'];
      $search[] = "Estimate.ID LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Estimate.fDate LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Job.fDesc LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Estimate.Name LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;
    $Columns = array(
      0 =>  'Estimate.ID',
      1 =>  'Estimate.fDate',
      2 =>  'Estimate.Name',
      3 =>  'Customer.Name',
      4 =>  'Location.Tag',
      5 =>  'Job.fDesc',
      6 =>  'Estimate.fDesc',
      7 =>  'Estimate.Cost',
      8 =>  'Estimate.Price',
      9 =>  'Estimate.Status'
    );
    $Order = isset( $_GET[ 'order' ] ) && isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Estimate.ID";
    $Direction = isset( $_GET[ 'order' ] ) && in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';
    $sQuery =
      "  SELECT  Top 10
                tbl.ID,
                tbl.FieldName,
                tbl.FieldValue
      FROM    (
                SELECT  attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
                        attr.insRow.value('.', 'nvarchar(max)') as FieldValue
                FROM    ( Select i.ID, convert(xml, (select i.* for xml raw)) as insRowCol
                          FROM ( SELECT  *
      FROM  (
              SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                      Estimate.ID       AS ID,
                      Estimate.fDate    AS Date,
                      CASE  WHEN Estimate.Status = 0 THEN 'Open'
                            WHEN Estimate.Status = 1 THEN 'Canceled'
                            WHEN Estimate.Status = 2 THEN 'Withdrawn'
                            WHEN Estimate.Status = 3 THEN 'Disqualified'
                            WHEN Estimate.Status = 4 THEN 'Award Successful'
                            ELSE 'Unknown Status'
                      END AS Status,
                      Contact.ID        AS Contact_ID,
                      Contact.Name      AS Contact_Name,
                      Territory.ID      AS Territory_ID,
                      Territory.Name    AS Territory_Name
              FROM    Estimate
                      LEFT JOIN Job ON Job.ID = Estimate.Job
                      LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
                      LEFT JOIN (
                          SELECT  Owner.ID,
                                  Rol.Name,
                                  Owner.Status
                          FROM    Owner
                                  LEFT JOIN Rol ON Owner.Rol = Rol.ID
                      ) AS Customer ON Job.Owner = Customer.ID
                      LEFT JOIN Rol AS Contact ON Contact.ID = Estimate.RolID
                      LEFT JOIN Terr AS Territory ON Territory.ID = Location.Terr
              WHERE   ({$conditions}) AND ({$search})
            ) AS Tbl
      WHERE     Tbl.ROW_COUNT >= ?
            AND Tbl.ROW_COUNT <= ?
            )  as i
                   ) as insRowTbl
              CROSS APPLY insRowTbl.insRowCol.nodes('/row/@*') as attr(insRow)
            ) AS tbl
      WHERE     tbl.FieldValue LIKE '%' + ? + '%'
      GROUP BY tbl.ID, tbl.FieldName, tbl.FieldValue;";
    $rResult = $database->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors());

    $output = array( );
      while ( $Row = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
        $output[]       = $Row;
      }
      echo json_encode( $output );
    }
}?>
