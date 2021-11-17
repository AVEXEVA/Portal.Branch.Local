<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $r = \singleton\database::getInstance( )->query(
      null,
    "   SELECT  *
      FROM    Connection
      WHERE   Connection.Connector = ?
              AND Connection.Hash = ?;",
    array(
      $_SESSION[ 'User' ],
      $_SESSION[ 'Hash' ]
    )
  );
  $Connection = sqlsrv_fetch_array( $r );
  $User = \singleton\database::getInstance( )->query(
      null,
      "   SELECT  Emp.*,
                  Emp.fFirst AS First_Name,
                  Emp.Last   AS Last_Name
          FROM    Emp
          WHERE   Emp.ID = ?;",
      array(
        $_SESSION[ 'User' ]
      )
  );
  $User = sqlsrv_fetch_array( $User );
  $r = \singleton\database::getInstance( )->query(
      null,
      "   SELECT  Privilege.Access,
                  Privilege.Owner,
                  Privilege.Group,
                  Privilege.Other
          FROM    Privilege
          WHERE   Privilege.User_ID = ?;",
      array(
        $_SESSION[ 'User' ]
      )
  );
  $Privileges = array();
  while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access' ] ] = $Privilege; }
  $Privileged = False;
  if( isset( $Privileges[ 'Violation' ] )
      && $Privileges[ 'Violation' ][ 'Owner' ]  >= 4
  ){ $Privileged = True; }
  if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
  else {

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
    $conditions[] = "Violation.Status NOT IN ({$notStatuses})";

    if( $Privileges[ 'Violation' ][ 'Other' ] < 4 ){
        $parameters [] = $User[ 'fWork' ];
        $conditions[] = "Violation.Loc IN ( SELECT Ticket.Location FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LID AS Location FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Loc AS Location FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Location)";
    }

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['Search'];
      $search[] = "Violation.ID LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Violation.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    /*ROW NUMBER*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] - 25 : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 25 : 25;

    $Columns = array(
      0 =>  'Violation.ID',
      1 =>  'Violation.Name',
      2 =>  'Violation.fDate',
      3 =>  'Location.Tag',
      4 =>  'Violation.Status'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Violation.Status";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Violation.ID        AS ID,
                          Violation.Name      AS Name,
                          Violation.fDate     AS Date,
                          Customer.ID         AS Customer_ID,
                          Customer.Name       AS Customer_Name,
                          Location.Loc        AS Location_ID,
                          Location.Tag        AS Location_Name,
                          Location.Address    AS Location_Street,
                          Location.City       AS Location_City,
                          Location.State      AS Location_State,
                          Location.Zip        AS Location_Zip,
                          Violation.Status    AS Status
                  FROM    Violation
                          LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                          LEFT JOIN (
                            SELECT  Owner.ID,
                                    Rol.Name 
                            FROM    Owner 
                                    LEFT JOIN Rol ON Rol.ID = Owner.Rol
                        ) AS Customer ON Location.Owner = Customer.ID
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    $rResult = $database->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow =
      " SELECT  Count( Violation.ID ) AS Count
        FROM    Violation
                LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
        WHERE   ({$conditions}) AND ({$search});";

    $stmt = $database->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];

    $parameters = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT(Violation.ID)
                FROM    Violation;";
    $rResultTotal = $database->query( null,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval($_GET['draw']),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array()
    );

    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
      $Row['Date'] = date( 'm/d/Y', strtotime( $Row[ 'Date' ] ) );
      $output['aaData'][] = $Row;
    }

    echo json_encode( $output );
  }
}
?>
