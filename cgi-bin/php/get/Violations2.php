<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  $r = sqlsrv_query(
    $NEI,
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
  $User = sqlsrv_query(
      $NEI,
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
  $r = sqlsrv_query(
      $NEI,
      "   SELECT  Privilege.Access_Table,
                  Privilege.User_Privilege,
                  Privilege.Group_Privilege,
                  Privilege.Other_Privilege
          FROM    Privilege
          WHERE   Privilege.User_ID = ?;",
      array( 
        $_SESSION[ 'User' ] 
      ) 
  );
  $Privileges = array();
  while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
  $Privileged = False;
  if( isset( $Privileges[ 'Violation' ] )
      && $Privileges[ 'Violation' ][ 'User_Privilege' ]  >= 4
  ){ $Privileged = True; }
  if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
  else {
    $conn = $NEI;

    $_GET['iDisplayStart'] = isset($_GET['start']) ? $_GET['start'] : 0;
    $_GET['iDisplayLength'] = isset($_GET['length']) ? $_GET['length'] : '-1';
    $Start = $_GET['iDisplayStart'];
    $Length = $_GET['iDisplayLength'];
    $End = $Length == '-1' ? 999999 : intval($Start) + intval($Length);

    $conditions = array( );
    $search = array( );
    $params = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['ID'];
      $conditions[] = "Violation.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Name'];
      $conditions[] = "Violation.Name LIKE '%' + ? + '%'";
    }
    if( isset( $_GET[ 'Date_Start' ] ) && !in_array( $_GET[ 'Date_Start' ], array( '', ' ', null ) ) ){
      $params[] = $_GET[ 'Date_Start' ];
      $conditions[] = "Violation.fDate Like >= ?";
    }
    if( isset( $_GET[ 'Date_End' ] ) && !in_array( $_GET[ 'Date_End' ], array( '', ' ', null ) ) ){
      $params[] = $_GET[ 'Date_End' ];
      $conditions[] = "Violation.fDate Like <= ?";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Location'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Status'];
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

    if( $Privileges[ 'Violation' ][ 'Other_Privilege' ] < 4 ){
        $params [] = $User[ 'fWork' ];
        $conditions[] = "Violation.Loc IN ( SELECT Ticket.Location FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LID AS Location FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Loc AS Location FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Location)";
    }

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){
      
      $params[] = $_GET['Search'];
      $search[] = "Violation.ID LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Violation.Name LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $params[] = $Start;
    $params[] = $End;
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
                          Violation.Name      AS Customer,
                          Violation.fDate     AS Date,
                          Location.Tag        AS Location,
                          Violation.Status    AS Status
                  FROM    Violation
                          LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    $rResult = sqlsrv_query(
      $conn,  
      $sQuery, 
      $params 
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "
        SELECT  Violation.ID        AS ID,
                Violation.Name      AS Customer,
                Violation.fDate     AS Date,
                Location.Tag        AS Location,
                Violation.Status    AS Status
        FROM    Violation
                LEFT JOIN Loc AS Location ON Location.Loc = Violation.Loc
        WHERE   ({$conditions}) AND ({$search});";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = sqlsrv_query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT(Violation.ID)
                FROM    Violation;";
    $rResultTotal = sqlsrv_query($conn,  $sQuery, $params ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval($_GET['sEcho']),
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