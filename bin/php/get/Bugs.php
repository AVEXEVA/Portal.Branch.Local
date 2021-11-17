<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = \singleton\database::getInstance( )->query(
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
    $Connection = sqlsrv_fetch_array( $result );
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
    $resultesult = \singleton\database::getInstance( )->query(
      'Portal',
      "   SELECT  [Privilege].[Access],
                  [Privilege].[Owner],
                  [Privilege].[Group],
                  [Privilege].[Other]
        FROM      dbo.[Privilege]
        WHERE     Privilege.[User] = ?;",
      array(
        $_SESSION[ 'Connection' ][ 'User' ]
      )
    );
    $Privileges = array();
    while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    $Privileged = False;
    if( isset( $Privileges[ 'Admin' ] )
        && $Privileges[ 'Admin' ][ 'User_Privilege' ]   >= 7
        && $Privileges[ 'Admin' ][ 'Group_Privilege' ]  >= 7
        && $Privileges[ 'Admin' ][ 'Other_Privilege' ]  >= 7
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
      $conditions[] = "Severity.ID LIKE '%' + ? + '%'";
    }

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $params[] = $_GET['Search'];
      $search[] = "Bug.ID LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Bug.Name LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Bug.Description LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Severity.Name LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;
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
    $resultResult = \singleton\database::getInstance( )->query(
      $conn,
      $sQuery,
      $params
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "  SELECT  Count( Bug.ID ) AS Count
        FROM    Portal.dbo.Bug
                LEFT JOIN Portal.dbo.Severity ON Bug.Severity = Severity.ID
        WHERE   ({$conditions}) AND ({$search});";
    $stmt = \singleton\database::getInstance( )->query( $conn, $sQueryRow , $params ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_fetch_array( $stmt )['Count'];
    $sQuery = " SELECT  COUNT(Bug.ID)
                FROM    Portal.dbo.Bug;";
    $resultResultTotal = \singleton\database::getInstance( )->query($conn,  $sQuery, $params ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($resultResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval($_GET['sEcho']),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array()
    );

    while ( $resultow = sqlsrv_fetch_array( $resultResult ) ){
      $output['aaData'][]   = $resultow;
    }
    echo json_encode( $output );
}}
?>
