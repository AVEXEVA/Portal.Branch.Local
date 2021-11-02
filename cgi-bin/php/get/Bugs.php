<?php
session_start();
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
    if( isset( $Privileges[ 'Admin' ] )
        && $Privileges[ 'Admin' ][ 'User_Privilege' ]   >= 7
        && $Privileges[ 'Admin' ][ 'Group_Privilege' ]  >= 7
        && $Privileges[ 'Admin' ][ 'Other_Privilege' ]  >= 7
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
      $conditions[] = "Bug.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Name'];
      $conditions[] = "Bug.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Description' ] ) && !in_array( $_GET[ 'Description' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Description'];
      $conditions[] = "Bug.Description LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Severity' ] ) && !in_array( $_GET[ 'Severity' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Severity'];
      $conditions[] = "Severity.Name LIKE '%' + ? + '%'";
    }

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){
      
      $params[] = $_GET['Search'];
      $search[] = "Bug.ID LIKE '%' + ? + '%'";
      
      $params[] = $_GET['Search'];
      $search[] = "Bug.Name LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Bug.Description LIKE '%' + ? + '%'";

      $params[] = $_GET['Severity'];
      $search[] = "Severity.Name LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $params[] = $Start;
    $params[] = $End;
    $Columns = array(
      0 =>  'Bug.ID',
      1 =>  'Bug.Name',
      2 =>  'Bug.Description',
      3 =>  'Severity.ID',
      4 =>  'Bug.Suggestion',
      5 =>  'Bug.Resolution',
      6 =>  'Bug.Fixed'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Bug.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Bug.ID 		        AS ID,
                          Bug.Name 	 	      AS Name,
                          Bug.Description 	AS Description,
                          Severity.Name     AS Severity,
                          Bug.Suggestion    AS Suggestion,
                          Bug.Resolution 	  AS Resolution,
                          Bug.Fixed 	      AS Fixed
                  FROM    Portal.dbo.Bug
                          LEFT JOIN Portal.dbo.Severity ON Bug.Severity = Severity.ID
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
        SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                Bug.ID            AS ID,
                Bug.Name          AS Name,
                Bug.Description   AS Description,
                Severity.Name     AS Severity,
                Bug.Suggestion    AS Suggestion,
                Bug.Resolution    AS Resolution,
                Bug.Fixed         AS Fixed
        FROM    Portal.dbo.Bug
                LEFT JOIN Portal.dbo.Severity ON Bug.Severity = Severity.ID
        WHERE   ({$conditions}) AND ({$search});";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = sqlsrv_query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT(Bug.ID)
                FROM    Portal.dbo.Bug;";
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