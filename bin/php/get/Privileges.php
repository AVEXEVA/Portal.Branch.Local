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
      \singleton\database::getInstance( )->query(
        null,
        " INSERT INTO Activity([User], [Date], [Page] )
          VALUES( ?, ?, ? );",
        array(
          $_SESSION[ 'Connection' ][ 'User' ],
          date('Y-m-d H:i:s'),
          'get/users.php'
        )
      );
      $conditions = array( );
      $search = array( );
      $parameters = array( );

    /*Filter $_GET Columns to SQL*/
    if( isset( $_GET[ 'ID' ] ) && !in_array(  $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "[Privilege].[ID] LIKE '%' + ? + '%'";
    }

    /*Search Filters*/
    /*NONE*/

    /*Concatenate Filters*/
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    $Columns = array(
      0 =>  '[Privilege].[ID]',
      1 =>  '[Privilege].[Access]'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "[Privilege].[ID]";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

        /*Perform Query*/
    $sQuery =
            " SELECT  *
              FROM  (
                      SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                              [Privilege].[ID],
                              [Privilege].[Access],
                              [Privilege].[Owner],
                              [Privilege].[Group],
                              [Privilege].[Department],
                              [Privilege].[Database],
                              [Privilege].[Server],
                              [Privilege].[Other],
                              [Privilege].[Token],
                              [Privilege].[Internet]
                      FROM    [Privilege]
                      WHERE   ({$conditions}) AND ({$search})
                    ) AS Tbl
              WHERE     Tbl.ROW_COUNT >= ?
                    AND Tbl.ROW_COUNT <= ?;";
        //echo $sQuery;
      $rResult = $database->query(
        'Portal',
        $sQuery,
        $parameters
      ) or die(print_r(sqlsrv_errors()));

        $sQueryRow = "SELECT  [Privilege].[ID]
                      FROM    [Privilege]
                      WHERE   ({$conditions}) AND ({$search});";

        $fResult = \singleton\database::getInstance( )->query(
            'Portal',
            $sQueryRow ,
            $parameters
        ) or die(print_r(sqlsrv_errors()));

        $iFilteredTotal = 0;
        $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
        $_SESSION[ 'Tables' ][ 'Privileges' ] = isset( $_SESSION[ 'Tables' ][ 'Privileges' ]  ) ? $_SESSION[ 'Tables' ][ 'Privileges' ] : array( );
        if( count( $_SESSION[ 'Tables' ][ 'Privileges' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Privileges' ] as &$Value ){ $Value = false; } }
        $_SESSION[ 'Tables' ][ 'Privileges' ][ 0 ] = $_GET;
        while( $Row = sqlsrv_fetch_array( $fResult ) ){
            $_SESSION[ 'Tables' ][ 'Privileges' ][ $Row[ 'ID' ] ] = true;
            $iFilteredTotal++;
        }

        $parameters = array( );
        $sQuery = " SELECT  COUNT( [Privilege].[ID] )
                    FROM    [Privilege];";
        $rResultTotal = \singleton\database::getInstance( )->query(
            'Portal',
            $sQuery,
            $parameters
        ) or die(print_r(sqlsrv_errors()));
        $aResultTotal = sqlsrv_fetch_array($rResultTotal);
        $iTotal = $aResultTotal[0];

        $output = array(
            'sEcho'         =>  intval( $_GET[ 'draw' ] ),
            'iTotalRecords'     =>  $iTotal,
            'iTotalDisplayRecords'  =>  $iFilteredTotal,
            'aaData'        =>  array()
        );
    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
      $Row[ 'dechex' ] = implode( '', array(
          dechex( $Row[ 'Owner' ] ),
          dechex( $Row[ 'Group' ] ),
          dechex( $Row[ 'Department' ] ),
          dechex( $Row[ 'Database' ] ),
          dechex( $Row[ 'Server' ] ),
          dechex( $Row[ 'Other' ] ),
          dechex( $Row[ 'Token' ] ),
          dechex( $Row[ 'Internet' ] )
      ) );
      $Row[ 'Owner_Read' ]      = check( privilege_read, level_owner, $Row[ 'dechex' ] );
      $Row[ 'Owner_Write' ]     = check( privilege_write, level_owner, $Row[ 'dechex' ] );
      $Row[ 'Owner_Execute' ]   = check( privilege_execute, level_owner, $Row[ 'dechex' ] );
      $Row[ 'Owner_Delete' ]    = check( privilege_delete, level_owner, $Row[ 'dechex' ] );
      $Row[ 'Group_Read' ]      = check( privilege_read, level_group, $Row[ 'dechex' ] );
      $Row[ 'Group_Write' ]     = check( privilege_write, level_group, $Row[ 'dechex' ] );
      $Row[ 'Group_Execute' ]   = check( privilege_execute, level_group, $Row[ 'dechex' ] );
      $Row[ 'Group_Delete' ]    = check( privilege_delete, level_group, $Row[ 'dechex' ] );
      $Row[ 'Department_Read' ]      = check( privilege_read, level_department, $Row[ 'dechex' ] );
      $Row[ 'Department_Write' ]     = check( privilege_write, level_department, $Row[ 'dechex' ] );
      $Row[ 'Department_Execute' ]   = check( privilege_execute, level_department, $Row[ 'dechex' ] );
      $Row[ 'Department_Delete' ]    = check( privilege_delete, level_department, $Row[ 'dechex' ] );
      $Row[ 'Database_Read' ]      = check( privilege_read, level_database, $Row[ 'dechex' ] );
      $Row[ 'Database_Write' ]     = check( privilege_write, level_database, $Row[ 'dechex' ] );
      $Row[ 'Database_Execute' ]   = check( privilege_execute, level_database, $Row[ 'dechex' ] );
      $Row[ 'Database_Delete' ]    = check( privilege_delete, level_database, $Row[ 'dechex' ] );
      $Row[ 'Server_Read' ]      = check( privilege_read, level_server, $Row[ 'dechex' ] );
      $Row[ 'Server_Write' ]     = check( privilege_write, level_server, $Row[ 'dechex' ] );
      $Row[ 'Server_Execute' ]   = check( privilege_execute, level_server, $Row[ 'dechex' ] );
      $Row[ 'Server_Delete' ]    = check( privilege_delete, level_server, $Row[ 'dechex' ] );

      $output['aaData'][]       = $Row;
    }
    echo json_encode( $output );
}}
?>
