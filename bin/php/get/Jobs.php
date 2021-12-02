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
        ||  !isset( $Privileges[ 'Job' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Job' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        \singleton\database::getInstance( )->query(
          null,
          " INSERT INTO Activity([User], [Date], [Page] )
            VALUES( ?, ?, ? );",
          array(
            $_SESSION[ 'Connection' ][ 'User' ],
            date('Y-m-d H:i:s'),
            'jobs.php'
        )
      );

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Job.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Name'];
      $conditions[] = "Job.fDesc LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Location'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Type'];
      $conditions[] = "Job_Type.Type LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Status'];
      $conditions[] = "Job.Status LIKE '%' + ? + '%'";
    }

    /*if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['Search'];
      $search[] = "Job.ID LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Job.fDesc LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Job_Type.Type LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Job.Status LIKE '%' + ? + '%'";

    }*/

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    /*ROW NUMBER*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    $Columns = array(
      0 =>  'Job.ID',
      1 =>  'Job.fDesc',
      2 =>  'Customer.Name',
      3 =>  'Location.Tag',
      4 =>  'Job_Type.Type',
      5 =>  'Job.Status'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Job.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Job.ID                AS ID,
                          Job.fDesc             AS Name,
                          Job.fDate             AS Date,
                          Customer.ID           AS Customer_ID,
                          Customer.Name         AS Customer_Name,
                          Location.Loc          AS Location_ID,
                          Location.Tag          AS Location_Name,
                          Location.Address      AS Location_Street,
                          Location.City         AS Location_City,
                          Location.State        AS Location_State,
                          Location.Zip          AS Location_Zip,
                          Job_Type.Type         AS Type,
                          Job.Status            AS Status,
                          Job_Tickets.Count     AS Tickets,
                          Job_Invoices.Count    AS Invoices
                  FROM    Job
                          LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
                          LEFT JOIN (
                              SELECT  Owner.ID,
                                      Rol.Name
                              FROM    Owner
                                      LEFT JOIN Rol ON Rol.ID = Owner.Rol
                          ) AS Customer ON Job.Owner = Customer.ID
                          LEFT JOIN JobType AS Job_Type ON Job_Type.ID = Job.Type
                          LEFT JOIN (
                            SELECT    TicketD.Job,
                                      Count( TicketD.ID ) AS Count
                            FROM      TicketD
                            GROUP BY  TicketD.Job
                          ) AS Job_Tickets ON Job_Tickets.Job = Job.ID
                          LEFT JOIN (
                            SELECT    Invoice.Job,
                                      Count( Invoice.Ref ) AS Count
                            FROM      Invoice
                            GROUP BY  Invoice.Job
                          ) AS Job_Invoices ON Job_Invoices.Job = Job.ID
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";

    $rResult = \singleton\database::getInstance( )->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow =
      " SELECT  Count( Job.ID ) AS Count
        FROM    Job
                LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
                LEFT JOIN (
                    SELECT  Owner.ID,
                            Rol.Name
                    FROM    Owner
                            LEFT JOIN Rol ON Rol.ID = Owner.Rol
                ) AS Customer ON Job.Owner = Customer.ID
                LEFT JOIN JobType AS Job_Type ON Job_Type.ID = Job.Type
                LEFT JOIN (
                  SELECT    TicketD.Job,
                            Count( TicketD.ID ) AS Count
                  FROM      TicketD
                  GROUP BY  TicketD.Job
                ) AS Job_Tickets ON Job_Tickets.Job = Job.ID
                LEFT JOIN (
                  SELECT    Invoice.Job,
                            Count( Invoice.Ref ) AS Count
                  FROM      Invoice
                  GROUP BY  Invoice.Job
                ) AS Job_Invoices ON Job_Invoices.Job = Job.ID
        WHERE   ({$conditions}) AND ({$search})";

    $stmt = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));
    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];

    $sQuery = " SELECT  COUNT( Job.ID )
                FROM    Job;";
    $rResultTotal = \singleton\database::getInstance( )->query( null,  $sQuery ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval( $_GET[ 'draw' ] ),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array()
    );

    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
      $Row[ 'Date' ] = date( 'm/d/Y', strtotime( $Row[ 'Date' ] ) );
      $output['aaData'][] = $Row;
    }
    echo json_encode( $output );
  }
}
?>
