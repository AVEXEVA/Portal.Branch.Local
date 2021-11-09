<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = \singleton\database::getInstance( )->query(
        null,
        " SELECT  *
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
    $Privileges = array();
    while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    $Privileged = False;
    if( isset( $Privileges[ 'User' ] )
        && $Privileges[ 'User' ][ 'Group_Privilege' ]  >= 4
    ){ $Privileged = True; }
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
    $conn = null;

    $_GET['iDisplayStart'] = isset($_GET['start']) ? $_GET['start'] : 0;
    $_GET['iDisplayLength'] = isset($_GET['length']) ? $_GET['length'] : '15';
    $Start = $_GET['iDisplayStart'];
    $Length = $_GET['iDisplayLength'];
    $End = $Length == '-1' ? 999999 : intval($Start) + intval($Length);

    $conditions = array( );
    $search = array( );
    $params = array( );

    if( isset($_GET[ 'Supervisor' ] ) && !in_array( $_GET[ 'Supervisor' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Supervisor'];
      $conditions[] = "tblWork.Super LIKE '%' + ? + '%'";
    }

    $params[] = 0;
    $conditions[] = "Emp.Status = ?";

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $params[] = $_GET['Search'];
      $search[] = "Emp.fFirst LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Emp.Last LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Emp.fFirst + ' ' + Emp.Last LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Portal.Email LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $params[] = $Start;
    $params[] = $End;
    $Columns = array(
      0 =>  'Emp.ID',
      1 =>  'Emp.fFirst',
      2 =>  'Emp.Last',
      3 =>  'Portal.Email',
      4 =>  'tblWork.Super'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Emp.Last";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery =
                " SELECT *
                  FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Emp.ID AS Branch_ID,
                          Emp.fFirst AS First_Name,
                          Emp.Last AS Last_Name,
                          Emp.fFirst + ' ' + Emp.Last AS Full_Name,
                          Portal.Email AS Email,
                          tblWork.Super AS Supervisor
                  FROM    Emp
                          LEFT JOIN (
                            SELECT    Portal.Branch,
                                      Portal.Branch_ID,
                                      Portal.Email
                            FROM      Portal.dbo.Portal
                            GROUP BY  Portal.Branch,
                                      Portal.Branch_ID,
                                      Portal.Email
                          ) AS Portal ON Portal.Branch_ID = Emp.ID AND Portal.Branch = ?
                          LEFT JOIN tblWork ON CONVERT( INT, SUBSTRING( tblWork.Members, 2, LEN( tblWork.Members ) - 2 ) ) = Emp.ID
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    $rResult = $database->query(
      $conn,
      $sQuery,
      array_mege( array( $_SESSION[ 'Conneciton' ][ 'Branch' ] ), $params  )
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                            Emp.ID AS Branch_ID,
                            Emp.fFirst AS First_Name,
                            Emp.Last AS Last_Name,
                            Emp.fFirst + ' ' + Emp.Last AS Full_Name,
                            Portal.Email AS Email,
                            tblWork.Super AS Supervisor
                    FROM    Emp
                            LEFT JOIN (
                            SELECT    Portal.Branch,
                                      Portal.Branch_ID,
                                      Portal.Email
                            FROM      Portal.dbo.Portal
                            GROUP BY  Portal.Branch,
                                      Portal.Branch_ID,
                                      Portal.Email
                          ) AS Portal ON Portal.Branch_ID = Emp.ID AND Portal.Branch = 'Nouveau Elevator'
                            LEFT JOIN tblWork ON CONVERT( INT, SUBSTRING( tblWork.Members, 2, LEN( tblWork.Members ) - 2 ) ) = Emp.ID
                    WHERE ({$conditions}) AND ({$search});";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = $database->query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT( Emp.ID )
                FROM    Emp;";
    $rResultTotal = $database->query($conn,  $sQuery, $params ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval($_GET['sEcho']),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array()
    );

    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
      $Row['Start_Date'] = date('m/d/Y', strtotime( $Row[ 'Start_Date' ] ) );
      $Row['End_Date'] = date('m/d/Y', strtotime( $Row[ 'End_Date' ] ) );
      $Row['Escalation_Date'] = date('m/d/Y', strtotime( $Row[ 'Escalation_Date' ] ) );
      $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>
