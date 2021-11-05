<?php
session_start( [ 'read_and_close' => true ] );
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
            FROM    Portal.dbo.Privilege
            WHERE   Privilege.User_ID = ?;",
        array( 
          $_SESSION[ 'User' ] 
        ) 
    );
    $Privleges = array();
    while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privleges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    $Privileged = False;
    if( isset( $Privleges[ 'Violation' ] )
        && (
            $Privleges[ 'Violation' ][ 'Other_Privilege' ] >= 4
        ||  $Privleges[ 'Violation' ][ 'Group_Privilege' ] >= 4
        ||  $Privleges[ 'Violation' ][ 'User_Privilege' ]  >= 4
      )
    ){ $Privileged = True; }
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
    $conn = $NEI;

    $_GET['iDisplayStart'] = isset($_GET['start']) ? $_GET['start'] : 0;
    $_GET['iDisplayLength'] = isset($_GET['length']) ? $_GET['length'] : '-1';
    $Start = $_GET['iDisplayStart'];
    $Length = $_GET['iDisplayLength'];
    $End = $Length == '-1' ? 999999 : intval($Start) + intval($Length);

    $conditions = array();
    $conditions = $conditions == array() ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $params[] = $User[ 'fWork' ];
    $params[] = $Start;
    $params[] = $End;
    $Columns = array(
      0 =>  'Violation.ID',
      1 =>  'Violation.Name',
      2 =>  'Violation.fDate',
      3 =>  'Violation.Status'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Violation.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Violation.ID,
                          Violation.Name,
                          Violation.fDate AS Date,
                          Violation.Status,
                          Location.Tag AS Location
                  FROM    Violation
                          LEFT JOIN (
                            SELECT    Ticket.Field,
                                      Ticket.Location
                            FROM  ( (
                              SELECT    TicketO.fWork AS Field,
                                        TicketO.LID   AS Location
                              FROM      TicketO
                              GROUP BY  TicketO.fWork,
                                        TicketO.LID
                            ) UNION ALL (
                              SELECT    TicketD.fWork AS Field,
                                        TicketD.Loc   AS Location
                              FROM      TicketD
                              GROUP BY  TicketD.fWork,
                                        TicketD.Loc
                            ) ) AS Ticket
                            GROUP BY  Ticket.Field,
                                      Ticket.Location
                          ) AS Ticket ON Violation.Loc = Ticket.Location
                          LEFT JOIN Loc AS Location ON Violation.Loc = Location.Loc
                  WHERE   {$conditions}
                          AND Violation.Status = 'Job Created'
                          AND Violation.fDate >= '2017-03-08 00:00:00.000'
                          AND Ticket.Field = ?
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN {$Start} AND {$End};";
    $rResult = sqlsrv_query(
      $conn,  
      $sQuery, 
      $params 
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "
        SELECT  Violation.ID,
                Violation.Name,
                Violation.fDate AS Date,
                Violation.Status
        FROM    Violation
                LEFT JOIN (
                  SELECT    Ticket.Field,
                            Ticket.Location
                  FROM  ( (
                    SELECT    TicketO.fWork AS Field,
                              TicketO.LID   AS Location
                    FROM      TicketO
                    GROUP BY  TicketO.fWork,
                              TicketO.LID
                  ) UNION ALL (
                    SELECT    TicketD.fWork AS Field,
                              TicketD.Loc   AS Location
                    FROM      TicketD
                    GROUP BY  TicketD.fWork,
                              TicketD.Loc
                  ) ) AS Ticket
                  GROUP BY  Ticket.Field,
                            Ticket.Location
                ) AS Ticket ON Violation.Loc = Ticket.Location
        WHERE   {$conditions}
                AND Violation.Status = 'Job Created'
                AND Violation.fDate >= '2017-03-08 00:00:00.000'
                AND Ticket.Field = ?;";

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
      $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>