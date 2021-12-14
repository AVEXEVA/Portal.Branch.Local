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
        ||  !isset( $Privileges[ 'Route' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Route' ] )
    ){ ?><?php require('404.html');?><?php }
    else {

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset( $_GET[ 'search'] ) ){

      $search[ ] = "[User].[Email]  LIKE '%' + ? + '%'";
      $parameters[] = $_GET[ 'search' ];
	 $search[ ] = "[User].[ID]  LIKE '%' + ? + '%'";
	 $parameters[] = $_GET[ 'search' ];
   $search[ ] = "[User].[Branch_ID]  LIKE '%' + ? + '%'";
   $parameters[] = $_GET[ 'search' ];
      $search[ ] = "[User].[Branch]  LIKE '%' + ? + '%'";
$parameters[] = $_GET[ 'search' ];

        $search[] = "[User].[Branch_Type] LIKE '%' + ? + '%'";
        $parameters[] = $_GET[ 'search' ];
    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

   $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;
  $parameters[] = $_GET[ 'search' ];

     $Columns = array(
      0 =>  '[User].[ID]',
      1 =>  '[User].[Email]',
      2 =>'[User].[Branch_Type]'
    );

    $Direction = 'ASC';
$Order = isset( $_GET['order']['column'] ) ? $Columns[ $_GET['order']['column'] ] : "[User].[ID]";
if(isset($_GET['order']['dir'])){
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';
      }
     $sQuery =
      "  SELECT  Top 10
                  tbl.ID,
                  tbl.FieldName,
                  tbl.FieldValue
          FROM    (
                    SELECT  attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
                            attr.insRow.value('.', 'nvarchar(max)') as FieldValue
                    FROM    (i.ID,  Select  convert(xml, (select i.* for xml raw)) as insRowCol
                              FROM ( SELECT  *
          FROM  (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          [User].[ID] AS ID,
                          [User].[Name] AS Name,
                          [User].[Email] AS Email,
                  FROM    [User]
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
      'Portal',
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
