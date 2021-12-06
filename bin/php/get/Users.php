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
      $conditions[] = "[User].[ID] LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Email' ] ) && !in_array( $_GET[ 'Email' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Email'];
      $conditions[] = "[User].[Email] LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Branch_Type' ] ) && !in_array( $_GET[ 'Branch_Type' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Branch_Type'];
        $conditions[] = "[User].[Branch_Type] LIKE '%' + ? + '%'";
    }

    /*Search Filters*/
    /*NONE*/

    /*Concatenate Filters*/
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    $Columns = array(
      0 =>  '[User].[ID]',
      1 =>  '[User].[Email]',
      2 =>'[User].[Branch_Type]'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "[User].[ID]";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

        /*Perform Query*/
    $sQuery =
            " SELECT  *
              FROM  (
                      SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                              [User].[ID],
                              [User].[Email],
                              [User].[Verified],
                              [User].[Branch],
                              [User].[Branch_Type],
                              [User].[Branch_ID],
                              [User].[Picture],
                              [User].[Picture_Type]
                      FROM    [User]
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

        $sQueryRow = "SELECT  [User].[ID]
                  FROM    [User]
                  WHERE   ({$conditions}) AND ({$search});";

        $fResult = \singleton\database::getInstance( )->query(
            'Portal',
            $sQueryRow ,
            $parameters
        ) or die(print_r(sqlsrv_errors()));

        $iFilteredTotal = 0;
        $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
        $_SESSION[ 'Tables' ][ 'Users' ] = isset( $_SESSION[ 'Tables' ][ 'Users' ]  ) ? $_SESSION[ 'Tables' ][ 'Users' ] : array( );
        if( count( $_SESSION[ 'Tables' ][ 'Users' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Users' ] as &$Value ){ $Value = false; } }
        $_SESSION[ 'Tables' ][ 'Users' ][ 0 ] = $_GET;
        while( $Row = sqlsrv_fetch_array( $fResult ) ){
            $_SESSION[ 'Tables' ][ 'Users' ][ $Row[ 'ID' ] ] = true;
            $iFilteredTotal++;
        }

        $parameters = array( );
        $sQuery = " SELECT  COUNT( [User].[ID] )
                FROM    [User];";
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
      $output['aaData'][]       = $Row;
    }
    echo json_encode( $output );
}}
?>
