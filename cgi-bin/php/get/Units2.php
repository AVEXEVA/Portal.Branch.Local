<?php
session_start();
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
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array( 
          $_SESSION[ 'User' ] 
        ) 
    );
    $Privileges = array();
    while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    $Privileged = False;
    if( isset( $Privileges[ 'Unit' ] )
        && $Privileges[ 'Unit' ][ 'User_Privilege' ]  >= 4
    ){ $Privileged = True; }
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
    $conn = $NEI;

    $_GET['iDisplayStart'] = isset($_GET['start']) ? $_GET['start'] : 0;
    $_GET['iDisplayLength'] = isset($_GET['length']) ? $_GET['length'] : '15';
    $Start = $_GET['iDisplayStart'];
    $Length = $_GET['iDisplayLength'];
    $End = $Length == '-1' ? 999999 : intval($Start) + intval($Length);

    $conditions = array( );
    $search = array( );
    $params = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['ID'];
      $conditions[] = "Elev.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'City_ID' ] ) && !in_array( $_GET[ 'City_ID' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['City_ID'];
      $conditions[] = "Elev.State LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Customer'];
      $conditions[] = "OwnerWithRol.ID = ?";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Location'];
      $conditions[] = "Loc.Loc = ?";
    }
    if( isset($_GET[ 'Building_ID' ] ) && !in_array( $_GET[ 'Building_ID' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Building_ID'];
      $conditions[] = "Elev.Unit LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Type'];
      $conditions[] = "Elev.Type LIKE '%' + ? + '%'";
    } 
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Status'] ;
      $conditions[] = "Elev.Status LIKE '%' + ? + '%'";
    }
    
    if( $Privileges[ 'Unit' ][ 'Other_Privilege' ] < 4 ){
        $params [] = $User[ 'fWork' ];
        $conditions[] = "Elev.ID IN ( SELECT Ticket.Unit FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LElev AS Unit FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Elev AS Unit FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Unit)";
    }

    if( isset( $_GET[ 'Search' ] ) ){
      
      $params[] = $_GET['Search'];
      $search[] = "Elev.ID LIKE '%' + ? + '%'";
      
      $params[] = $_GET['Search'];
      $search[] = "Elev.State LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Loc.Tag LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Elev.Unit LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Elev.Type LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array() ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array() ? "NULL IS NULL" : implode( ' OR ', $search );

    $params[] = $Start;
    $params[] = $End;
    $Columns = array(
      0 =>  'Elev.ID',
      1 =>  'Elev.State',
      2 =>  'Loc.Tag',
      3 =>  'Elev.Unit',
      4 =>  'Elev.Type',
      5 =>  'Elev.Status'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Elev.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Elev.ID AS ID,
                          CASE WHEN Elev.State IN ( null, ' ', '  ' ) THEN 'Untitled' ELSE Elev.State END AS City_ID,
                          Loc.Tag AS Location,
                          Elev.Unit AS Building_ID,
                          Elev.Type AS Type,
                          Elev.Status AS Status
                  FROM    Elev
                          LEFT JOIN Loc          ON Elev.Loc = Loc.Loc
                          LEFT JOIN OwnerWithRol ON Elev.Owner    = OwnerWithRol.ID
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    //var_dump( $params );
    $rResult = sqlsrv_query(
      $conn,  
      $sQuery, 
      $params 
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "
        SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                Elev.ID AS ID,
                Elev.State AS City_ID,
                Loc.Tag AS Location,
                Elev.Unit AS Building_ID,
                Elev.Type AS Type,
                Elev.Status AS Status
        FROM    Elev
                LEFT JOIN Loc          ON Elev.Loc   = Loc.Loc
                LEFT JOIN OwnerWithRol ON Elev.Owner = OwnerWithRol.ID
        WHERE   ({$conditions}) AND ({$search});";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = sqlsrv_query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT(Elev.ID)
                FROM    Elev;";
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
      $Row['Start_Date'] = date('m/d/Y', strtotime( $Row[ 'Start_Date' ] ) );
      $Row['End_Date'] = date('m/d/Y', strtotime( $Row[ 'End_Date' ] ) );
      $Row['Escalation_Date'] = date('m/d/Y', strtotime( $Row[ 'Escalation_Date' ] ) );
      $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>