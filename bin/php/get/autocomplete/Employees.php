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
        ||  !isset( $Privileges[ 'Employee' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Employee' ] )
    ){ ?><?php print json_encode( array( 'data' => array( ) ) );?><?php }
    else {
      $output = array(
            'sEcho'             => isset( $_GET[ 'draw' ] ) ? intval( $_GET[ 'draw' ] ) : 1,
            'iTotalRecords'       =>  0,
            'iTotalDisplayRecords'  =>  0,
            'aaData'            =>  array(),
            'options'         => array( )
        );

      $conditions = array( );
      $search   = array( );

      if( isset( $_GET[ 'First_Name' ] ) && !in_array( $_GET[ 'First_Name' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['First_Name'];
        $conditions[] = "Employee.fFirst LIKE '%' + ? + '%'";
      }
      if( isset( $_GET[ 'Last_Name' ] ) && !in_array(  $_GET[ 'Last_Name' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Last_Name'];
        $conditions[] = "Employee.Last LIKE '%' + ? + '%'";
      }
      if( isset( $_GET[ 'search' ] ) ){
        $parameters[ ] = $_GET[ 'search' ];
        $search[ ] = "Employee.fFirst + ' ' + Employee.Last LIKE '%' + ? + '%'";
      }


      $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
        $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

      /*ROW NUMBER*/
      //$parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] -25 : 0;
      //$parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 25 : 25;

      /*Order && Direction*/
      //update columns from bin/js/tickets/table.js
      $Columns = array(
        0 =>  'Employee.ID'
      );
      $Order = isset( $_GET[ 'order' ] ) && isset( $Columns[ $_GET['order']['column'] ] )
          ? $Columns[ $_GET['order']['column'] ]
          : "Employee.ID";
      $Direction = isset( $_GET[ 'order' ] ) && in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
        ? $_GET['order']['dir']
        : 'ASC';

      $parameters[ ] = $_GET[ 'search' ];

    $sQuery =
      "   SELECT  Top 10
              tbl.ID,
              tbl.FieldName,
              tbl.FieldValue
          FROM    (
                SELECT  insRowTbl.ID,
                        attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
                        attr.insRow.value('.', 'nvarchar(max)') as FieldValue
                FROM    ( Select i.ID,  convert(xml, (select i.* for xml raw)) as insRowCol
                          FROM ( (
                            SELECT Employee.ID   AS ID,
                                   Employee.fFirst + ' ' + Employee.Last AS Name
                            FROM   Emp AS Employee
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
