<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = $database->query(
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
    $User = $database->query(
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
    $r = $database->query(
        null,
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
    $Privleges = array();
    while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privleges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    $Privileged = False;
    if( isset( $Privleges[ 'Unit' ] )
        && (
            $Privleges[ 'Unit' ][ 'Group_Privilege' ] >= 4
        ||  $Privleges[ 'Unit' ][ 'User_Privilege' ]  >= 4
      )
    ){ $Privileged = True; }
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
    $conn = null;

    $_GET['iDisplayStart'] = isset($_GET['start']) ? $_GET['start'] : 0;
    $_GET['iDisplayLength'] = isset($_GET['length']) ? $_GET['length'] : '-1';
    $Start = $_GET['iDisplayStart'];
    $Length = $_GET['iDisplayLength'];
    $End = $Length == '-1' ? 999999 : intval($Start) + intval($Length);

    $conditions = array( );
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Location'];
      $conditions[] = "Elev.Loc LIKE '%' + ? + '%'";
    }

    $search = array( );
    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){
      
      $params[] = $_GET['Search'];
      $search[] = "Elev.State LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Elev.Unit LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $params[] = $Start;
    $params[] = $End;
    $Columns = array(
      0 =>  'Elev.ID',
      1 =>  'Elev.State',
      2 =>  'Elev.Unit'
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
                            Elev.ID    AS ID,
                            Elev.State AS City_ID,
                            Elev.Unit  AS Building_ID
                    FROM    Elev
                    WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";

    $rResult = $database->query(
      $conn,  
      $sQuery, 
      $params 
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "
        SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                Elev.ID    AS ID,
                Elev.State AS City_ID,
                Elev.Unit  AS Building_ID
        FROM    Elev
        WHERE   ({$conditions}) AND ({$search});";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = $database->query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT( Elev.ID )
                FROM    Elev;";
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
      $output['aaData'][]       = $Row;
    }
    echo json_encode( $output );
}}
?>