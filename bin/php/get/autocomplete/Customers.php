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
        ||  !isset( $Privileges[ 'Customer' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Customer' ] )
    ){ ?><?php print json_encode( array( 'data' => array( ) ) );?><?php }
    else {

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset( $_GET[ 'search'] ) ){
      $parameters[ ] = $_GET[ 'search' ];
      $search[ ] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'ID'] ) ){
      $parameters[ ] = $_GET[ 'ID' ];
      $search[ ] = "Customer.ID LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Location'] ) ){
      $parameters[ ] = $_GET[ 'Location' ];
      $search[ ] = "Customer.Location LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Status'] ) ){
      $parameters[ ] = $_GET[ 'Status' ];
      $search[ ] = "Customer.Status LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Units'] ) ){
      $parameters[ ] = $_GET[ 'Units' ];
      $search[ ] = "Customer.Unit LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Jobs'] ) ){
      $parameters[ ] = $_GET[ 'Jobs' ];
      $search[ ] = "Customer.Job LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Tickets'] ) ){
      $parameters[ ] = $_GET[ 'Tickets' ];
      $search[ ] = "Customer.Ticket LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Violations'] ) ){
      $parameters[ ] = $_GET[ 'Violations' ];
      $search[ ] = "Customer.Violation LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Invoices'] ) ){
      $parameters[ ] = $_GET[ 'Invoices' ];
      $search[ ] = "Customer.Invoice LIKE '%' + ? + '%'";
    }
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[ ] = $_GET[ 'search' ];

    $sQuery =
      " SELECT  Top 10
              tbl.ID,
              tbl.FieldName,
              tbl.FieldValue
        FROM    (
                SELECT  attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
                        attr.insRow.value('.', 'nvarchar(max)') as FieldValue
                FROM    ( Select i.ID  convert(xml, (select i.* for xml raw)) as insRowCol
                          FROM ( (
                            SELECT  Customer.ID,
                                    Customer.Name
                        FROM    (
                                    SELECT  Owner.ID,
                                            Rol.Name,
                                            Owner.Status
                                    FROM    Owner
                                            LEFT JOIN Rol ON Owner.Rol = Rol.ID
                                ) AS Customer
                            WHERE   ({$conditions}) AND ({$search})
                          ) ) as i
                   ) as insRowTbl
              CROSS APPLY insRowTbl.insRowCol.nodes('/row/@*') as attr(insRow)
            ) AS tbl
      WHERE     tbl.FieldValue LIKE '%' + ? + '%'
      GROUP BY tbl.ID,  tbl.FieldName, tbl.FieldValue;;";

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
