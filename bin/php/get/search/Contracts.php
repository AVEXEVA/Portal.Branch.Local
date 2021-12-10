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
        ||  !isset( $Privileges[ 'Contracts' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Contracts' ] )
    ){ ?><?php require('404.html');?><?php }
  else {
    $output = array(
          'sEcho'             => isset( $_GET[ 'draw' ] ) ? intval( $_GET[ 'draw' ] ) : 1,
          'iTotalRecords'       =>  0,
          'iTotalDisplayRecords'  =>  0,
          'aaData'            =>  array(),
          'options'         => array( )
      );

    /*Parse GET*/
    /*None*/

    $conditions = array( );
    $search   = array( );

    /*Default Filters*/
    /*NONE*/

    if( isset( $_GET[ 'ID' ] ) && !in_array(  $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Contract.ID LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Person'];
      $conditions[] = "Contract.Contact LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Location' ] ) && !in_array(  $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Location'];
      $conditions[] = "Contract.Location LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Job' ] ) && !in_array(  $_GET[ 'Job' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Job'];
      $conditions[] = "Contract.Job LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Start_Date' ] ) && !in_array(  $_GET[ 'Start_Date' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Start_Date'];
      $conditions[] = "Contract.BStart LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'End_Date' ] ) && !in_array( $_GET[ 'End_Date' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['End_Date'];
      $conditions[] = "Contract.BFinish LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Length' ] ) && !in_array( $_GET[ 'Length' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Length'];
      $conditions[] = "Contract.BLenght LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Amount' ] ) && !in_array(  $_GET[ 'Amount' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Amount'];
      $conditions[] = "Contract.BAmt LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Escalation_Factor' ] ) && !in_array(  $_GET[ 'Escalation_Factor' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Escalation_Factor'];
      $conditions[] = "Contract.BEscFact LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Escalation_Date' ] ) && !in_array(  $_GET[ 'Escalation_Date' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Escalation_Date'];
      $conditions[] = "Contract.EscLast LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Link' ] ) && !in_array(  $_GET[ 'Link' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Link'];
      $conditions[] = "Contract.Custom15 LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Remarks' ] ) && !in_array(  $_GET[ 'Remarks' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Remarks'];
      $conditions[] = "Contract.Remarks LIKE '%' + ? + '%'";
    }

    /*Search Filters*/
    if( isset( $_GET[ 'search' ] ) ){

      $parameters[ ] = $_GET[ 'search' ];
      $search[ ] = "Contract.Name LIKE '%' + ? + '%'";

      $parameters[ ] = $_GET[ 'search' ];
      $search[ ] = "Contract.Contract LIKE '%' + ? + '%'";
    }


    /*Concatenate Filters*/
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
      $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    /*ROW NUMBER*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] -25 : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 25 : 25;

    /*Order && Direction*/
    //update columns from bin/js/tickets/table.js
    $Columns = array(
      0 =>  'Contract.ID',
      1 =>  'Contract.Contract',
      2 =>  'Contract.Location',
      3 =>  'Contract.Job',
      4 =>  "Contract.Start_Date",
      5 =>  "Contract.End_Date",
      6 =>  "Contract.Length",
      7 =>  "Contract.Amount",
      8 =>  "Contract.Cycle",
      9 =>  "Contract.Escalation_Factor",
      10 =>  "Contract.Escalation_Date",
      11 =>  "Contract.Link",
      12 =>  "Contract.Remarks",
    );
    $Order = isset( $_GET[ 'order' ] ) && isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Contract.ID";
    $Direction = isset( $_GET[ 'order' ] ) && in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $parameters[ ] = $_GET[ 'search' ];
    /*Perform Query*/
    $sQuery =
      " SELECT  Top 10
              tbl.FieldName,
              tbl.FieldValue
        FROM    (
                SELECT  attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
                        attr.insRow.value('.', 'nvarchar(max)') as FieldValue
                FROM    ( Select  convert(xml, (select i.* for xml raw)) as insRowCol
                          FROM ( SELECT  *
        FROM  (
              SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                      Contract.ID              AS ID,
                      Contract.Name            AS Contract,
                      Contract.Type            AS Type,
                      Contract.Contract         AS Name,
                      Contract.Position        AS Position,
                      Contract.Phone           AS Phone,
                      Contract.Email           AS Email,
                      Contract.Address         AS Street,
                      Contract.City            AS City,
                      Contract.State           AS State,
                      Contract.Zip             AS Zip,
                      CASE  WHEN Contract.[Type] = 0 THEN  'Customer'
                          WHEN Contract.[Type] = 4 THEN  'Location'
                          WHEN Contract.[Type] = 5 THEN  'Employee'
                          ELSE 'Unknown'
                      END   AS [Type]
              FROM  Rol AS Contract
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
