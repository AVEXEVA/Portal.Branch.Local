<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
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
        ||  !isset( $Privileges[ 'Collection' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Collection' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'contracts.php'
        )
      );

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset($_GET[ 'ID' ] ) && !empty(  $_GET[ 'ID' ] ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Contract.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !empty( $_GET[ 'Customer' ] ) ){
      $parameters[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !empty( $_GET[ 'Location' ] ) ){
      $parameters[] = $_GET['Location'];
      $conditions[] = "Loc.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Job' ] ) && !empty( $_GET[ 'Job' ] ) ){
      $parameters[] = $_GET['Job'];
      $conditions[] = "Contract.Job LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Start_Date' ] ) && !empty( $_GET[ 'Start_Date' ] ) ){
      $parameters[] = date('Y-m-d', strtotime( $_GET['Start_Date'] ) );
      $conditions[] = "Contract.BStart >= ?";
    }
    if( isset( $_GET[ 'End_Date' ] ) && !in_array( $_GET[ 'End_Date' ], array( '', ' ', null ) ) ){
      $parameters[] = date('Y-m-d', strtotime( $_GET['End_Date'] ) );
      $conditions[] = "Contract.BFinish <= ?";
    }
    if( isset($_GET[ 'Cycle' ] ) && !empty( $_GET[ 'Cycle' ] ) ){
      $parameters[] = $_GET['Cycle'];
      $conditions[] = "Contract.BCycle LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Amount_Start' ] ) && !in_array( $_GET[ 'Amount_Start' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Amount_Start'];
      $conditions[] = "Contract.BAmt >= ?";
    }
    if( isset($_GET[ 'Amount_End' ] ) && !in_array( $_GET[ 'Amount_End' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Amount_End'];
      $conditions[] = "Contract.BAmt <= ?";
    }
    if( isset($_GET[ 'Length' ] ) && !in_array( $_GET[ 'Length' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Length'];
      $conditions[] = "Contract.BLenght LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Escalation_Factor' ] ) && !in_array( $_GET[ 'Escalation_Factor' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Escalation_Factor'];
      $conditions[] = "Contract.BEscFact LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Escalation_Date' ] ) && !in_array( $_GET[ 'Escalation_Date' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Escalation_Date'];
      $conditions[] = "Contract.EscLast >= ?";
    }
    if( isset($_GET[ 'Escalation_Type' ] ) && !in_array( $_GET[ 'Escalation_Type' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Escalation_Type'];
      $conditions[] = "Contract.BEscType LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Escalation_Cycle' ] ) && !in_array( $_GET[ 'Escalation_Cycle' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Escalation_Cycle'];
      $conditions[] = "Contract.BEscCycle LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Link' ] ) && !in_array( $_GET[ 'Link' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Link'];
      $conditions[] = "Job.Custom15 LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Remarks' ] ) && !in_array( $_GET[ 'Remarks' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Remarks'];
      $conditions[] = "Job.Remarks LIKE '%' + ? + '%'";
    }

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['Search'];
      $search[] = "Contract.ID LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Loc.Tag LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Job.fDesc LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    $Columns = array(
      0 =>  'Contract.ID',
      1 =>  'Customer.Name',
      2 =>  'Loc.Tag',
      3 =>  'Job.fDesc',
      4 =>  'Contract.BStart',
      5 =>  'Contract.BFinish',
      6 =>  'Contract.BAmt',
      7 =>  'Contract.BLenght',
      8 =>  'Contract.BCycle',
      9 =>  'Contract.BEscFact',
      10 => 'Contract.EscLast',
      11 => 'Contract.BEscType',
      12 => 'Contract.BEscCycle',
      13 => 'Job.Custom15',
      14 => 'Job.Remarks'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Contract.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                    SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                            Contract.ID         AS ID,
                            Customer.ID         AS Customer_ID,
                            Customer.Name       AS Customer_Name,
                            Loc.Loc             AS Location_ID,
                            Loc.Tag             AS Location_Name,
                            Loc.Address         AS Location_Street,
                            Loc.City            AS Location_City,
                            Loc.State           AS Location_State,
                            Loc.Zip             AS Location_Zip,
                            Job.ID              AS Job_ID,
                            Job.fDesc           AS Job_Name,
                            Contract.BStart     AS Start_Date,
                            Contract.BFinish    AS End_Date,
                            Contract.BAmt       AS Amount,
                            Contract.BLenght    AS Length,
                            CASE    WHEN Contract.BCycle = 0 THEN 'Monthly'
                                    WHEN Contract.BCycle = 1 THEN 'Bi-Monthly'
                                    WHEN Contract.BCycle = 2 THEN 'Quarterly'
                                    WHEN Contract.BCycle = 3 THEN 'Trimester'
                                    WHEN Contract.BCycle = 4 THEN 'Semi-Annually'
                                    WHEN Contract.BCycle = 5 THEN 'Annually'
                                    WHEN Contract.BCycle = 6 THEN 'Never'
                                    ELSE 'Error'
                            END AS Cycle,
                            Contract.BEscFact   AS Escalation_Factor,
                            Contract.EscLast    AS Escalation_Date,
                            Contract.BEscType   AS Escalation_Type,
                            Contract.BEscCycle  AS Escalation_Cycle,
                            Job.Custom15        AS Link,
                            Job.Remarks         AS Remarks
                    FROM    Contract
                            LEFT JOIN Loc          ON Contract.Loc = Loc.Loc
                            LEFT JOIN (
                                SELECT  Owner.ID,
                                        Rol.Name
                                FROM    Owner
                                        LEFT JOIN Rol ON Rol.ID = Owner.Rol
                            ) AS Customer ON Loc.Owner = Customer.ID
                            LEFT JOIN Job          ON Contract.Job = Job.ID
                    WHERE   ({$conditions})  AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";

    $rResult = \singleton\database::getInstance( )->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "SELECT  Contract.ID         AS ID
                  FROM    Contract
                          LEFT JOIN Loc          ON Contract.Loc = Loc.Loc
                          LEFT JOIN (
                              SELECT  Owner.ID,
                                      Rol.Name
                              FROM    Owner
                                      LEFT JOIN Rol ON Rol.ID = Owner.Rol
                          ) AS Customer ON Loc.Owner = Customer.ID
                          LEFT JOIN Job          ON Contract.Job = Job.ID
                  WHERE   ({$conditions})  AND ({$search})";

    $fResult = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));


    $iFilteredTotal = 0;
    $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
    $_SESSION[ 'Tables' ][ 'Contracts' ] = isset( $_SESSION[ 'Tables' ][ 'Contracts' ]  ) ? $_SESSION[ 'Tables' ][ 'Contracts' ] : array( );
    if( count( $_SESSION[ 'Tables' ][ 'Contracts' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Contracts' ] as &$Value ){ $Value = false; } }
    $_SESSION[ 'Tables' ][ 'Contracts' ][ 0 ] = $_GET;
    while( $Row = sqlsrv_fetch_array( $fResult ) ){
        $_SESSION[ 'Tables' ][ 'Contracts' ][ $Row[ 'ID' ] ] = true;
        $iFilteredTotal++;
    }

    $parameters = array( );
    $sQuery = " SELECT  COUNT( Contract.ID)
                FROM    Contract;";
    $rResultTotal = \singleton\database::getInstance( )->query(null,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval( $_GET[ 'draw' ] ),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array(),
        'options'       => array( )
    );
    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
      $Row[ 'Start_Date' ]      = is_null( $Row[ 'Start_Date' ] )      || $Row[ 'Start_Date' ]      == '1969-12-31 00:00:00.000' ? null : date( 'm/d/Y', strtotime( $Row[ 'Start_Date' ] ) );
      $Row[ 'End_Date' ]        = is_null( $Row[ 'End_Date' ] )        || $Row[ 'End_Date' ]        == '1969-12-31 00:00:00.000' ? null : date( 'm/d/Y', strtotime( $Row[ 'End_Date' ] ) );
      $Row[ 'Escalation_Date' ] = is_null( $Row[ 'Escalation_Date' ] ) || $Row[ 'Escalation_Date' ] == '1969-12-31 00:00:00.000' ? null : date( 'm/d/Y', strtotime( $Row[ 'Escalation_Date' ] ) );
      $Row[ 'Amount' ]          = '$' . number_format( $Row[ 'Amount' ], 2 );
      //preg_match('(https:[/][/]bit[.]ly[/][a-zA-Z0-9]*)', $Row[ 'Remarks' ], $matches );
      //$Row[ 'Link' ]            = $matches[ 0 ];
      $output['aaData'][]       = $Row;
    }
    $output[ 'options' ][ 'Cycle' ] = array(
      array(
        'label' => 'Monthly',
        'value' => 0
      ),
      array(
        'label' => 'Bi-Monthly',
        'value' => 1
      ),
      array(
        'label' => 'Quarterly',
        'value' => 2
      ),
      array(
        'label' => 'Trimester',
        'value' => 3
      ),
      array(
        'label' => 'Semi-Annually',
        'value' => 4
      ),
      array(
        'label' => 'Annually',
        'value' => 5
      ),
      array(
        'label' => 'Never',
        'value' => 6
      )
    );
    echo json_encode( $output );
}}
?>
