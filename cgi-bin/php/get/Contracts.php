<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
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
    if( isset( $Privleges[ 'Contract' ] )
        && (
            $Privleges[ 'Contract' ][ 'Other_Privilege' ] >= 4
        ||  $Privleges[ 'Contract' ][ 'Group_Privilege' ] >= 4
        ||  $Privleges[ 'Contract' ][ 'User_Privilege' ]  >= 4
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

    $conditions = array();
    if( isset($_GET[ 'ID' ] ) && !in_array(  $_GET[ 'ID' ] ) ){
      $params[] = $_GET['ID'];
      $conditions[] = "Contract.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Location'];
      $conditions[] = "Loc.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Job' ] ) && !in_array( $_GET[ 'Job' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Job'];
      $conditions[] = "Contract.Job LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Start_Date_Start' ] ) && !in_array( $_GET[ 'Start_Date_Start' ], array( '', ' ', null ) ) ){
      $params[] = date('Y-m-d', strtotime( $_GET['Start_Date_Start'] ) );
      $conditions[] = "Contract.BStart >= ?";
    }
    if( isset($_GET[ 'Start_Date_End' ] ) && !in_array( $_GET[ 'Start_Date_End' ], array( '', ' ', null ) ) ){
      $params[] = date('Y-m-d', strtotime( $_GET['Start_Date_End'] ) );
      $conditions[] = "Contract.BStart <= ?";
    }
    if( isset($_GET[ 'Amount_Start' ] ) && !in_array( $_GET[ 'Amount_Start' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Amount_Start'];
      $conditions[] = "Contract.BAmt >= ?";
    }
    if( isset($_GET[ 'Amount_End' ] ) && !in_array( $_GET[ 'Amount_End' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Amount_End'];
      $conditions[] = "Contract.BAmt <= ?";
    }
    if( isset($_GET[ 'Length' ] ) && !in_array( $_GET[ 'Length' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Length'];
      $conditions[] = "Contract.BLenght LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'End_Date_Start' ] ) && !in_array( $_GET[ 'End_Date_Start' ], array( '', ' ', null ) ) ){
      $params[] = date('Y-m-d', strtotime( $_GET['End_Date_Start'] ) );
      $conditions[] = "Contract.BFinish >= ?";
    }
    if( isset($_GET[ 'End_Date_End' ] ) && !in_array( $_GET[ 'End_Date_End' ], array( '', ' ', null ) ) ){
      $params[] = date('Y-m-d', strtotime( $_GET['End_Date_End'] ) );
      $conditions[] = "Contract.BFinish <= ?";
    }
    if( isset($_GET[ 'Cycle' ] ) && !in_array( $_GET[ 'Cycle' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Cycle'];
      $conditions[] = "Contract.BCycle LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Escalation_Factor' ] ) && !in_array( $_GET[ 'Escalation_Factor' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Escalation_Factor'];
      $conditions[] = "Contract.BEscFact LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Escalation_Date' ] ) && !in_array( $_GET[ 'Escalation_Date' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Escalation_Date'];
      $conditions[] = "Contract.EscLast >= ?";
    }
    if( isset($_GET[ 'Escalation_Type' ] ) && !in_array( $_GET[ 'Escalation_Type' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Escalation_Type'];
      $conditions[] = "Contract.BEscType LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Escalation_Cycle' ] ) && !in_array( $_GET[ 'Escalation_Cycle' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Escalation_Cycle'];
      $conditions[] = "Contract.BEscCycle LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Link' ] ) && !in_array( $_GET[ 'Link' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Link'];
      $conditions[] = "Job.Custom15 LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Remarks' ] ) && !in_array( $_GET[ 'Remarks' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Remarks'];
      $conditions[] = "Job.Remarks LIKE '%' + ? + '%'";
    }
    $conditions = $conditions == array() ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $params[] = $Start;
    $params[] = $End;
    $Columns = array(
      0 =>  'Contract.ID',
      1 =>  'Customer.Name',
      2 =>  'Loc.Tag',
      3 =>  'Job.ID',
      4 =>  'Contract.BStart',
      5 =>  'Contract.BAmt',
      6 =>  'Contract.BLenght',
      7 =>  'Contract.BCycle',
      8 =>  'Contract.BFinish',
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
                            Customer.Name       AS Customer,
                            Loc.Tag             AS Location,
                            Contract.Job        AS Job,
                            Contract.BStart     AS Start_Date,
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
                            Contract.BFinish    AS End_Date,
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
                    WHERE   {$conditions}
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";

    $rResult = $database->query(
      $conn,  
      $sQuery, 
      $params 
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "
        SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                Contract.ID         AS ID,
                Customer.Name       AS Customer,
                Loc.Tag             AS Location,
                Contract.Job        AS Job,
                Contract.BStart     AS Start_Date,
                Contract.BAmt       AS Amount,
                Contract.BLenght    AS Length,
                Cycle.Name          AS Cycle,
                Contract.BFinish    AS End_Date,
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
                LEFT JOIN (
                    SELECT  ROW_NUMBER( ) OVER( ORDER BY ( SELECT 0 ) ) AS ID,
                            BCycle.Field AS Name
                    FROM    BCycle
                ) AS Cycle ON Contract.BCycle = Cycle.ID
        WHERE   {$conditions};";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = $database->query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT(Contract.ID)
                FROM    Contract;";
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
      $Row[ 'Start_Date' ]      = date( 'm/d/Y', strtotime( $Row[ 'Start_Date' ] ) );
      $Row[ 'End_Date' ]        = date( 'm/d/Y', strtotime( $Row[ 'End_Date' ] ) );
      $Row[ 'Escalation_Date' ] = date( 'm/d/Y', strtotime( $Row[ 'Escalation_Date' ] ) );
      $Row[ 'Amount' ]          = '$' . number_format( $Row[ 'Amount' ], 2 );
      $output['aaData'][]       = $Row;
    }
    echo json_encode( $output );
}}
?>