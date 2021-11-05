<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = $database->query(
        null,
        "   SELECT  *
            FROM    Connection
            WHERE       Connection.Connector = ?
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
    $Privileges = array();
    while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    $Privileged = False;
    if( isset( $Privileges[ 'Location' ] )
        && $Privileges[ 'Location' ][ 'User_Privilege' ]  >= 4
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
      $conditions[] = "Job.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Name'];
      $conditions[] = "Job.fDesc LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Location'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    } 
    if( isset($_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Type'];
      $conditions[] = "Job_Type.Type LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Status'];
      $conditions[] = "Job.Status LIKE '%' + ? + '%'";
    }
    if( $Privileges[ 'Job' ][ 'Other_Privilege' ] < 4 ){
        $params [] = $User[ 'fWork' ];
        $conditions[] = "Location.Loc IN ( SELECT Ticket.Location FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LID AS Location FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Loc AS Location FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Location)";
    }

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){
      
      $params[] = $_GET['Search'];
      $search[] = "Job.ID LIKE '%' + ? + '%'";
      
      $params[] = $_GET['Search'];
      $search[] = "Job.fDesc LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Job_Type.Type LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Job.Status LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $params[] = $Start;
    $params[] = $End;
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
                          Rolodex.Name         AS Customer,
                          Location.Tag          AS Location,
                          Job_Type.Type         AS Type,
                          Job.Status            AS Status
                  FROM    Job
                          LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
                          LEFT JOIN OwnerWithRol AS Customer ON Job.Owner = Customer.ID
                          LEFT JOIN Rol AS Rolodex ON Customer.Rol = Rolodex.ID
                          LEFT JOIN JobType AS Job_Type ON Job_Type.ID = Job.Type
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    $rResult = $database->query(
      $conn,  
      $sQuery, 
      $params 
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "
        SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                Job.ID                AS ID,
                Job.fDesc             AS Name,
                Rolodex.Name              AS Customer,
                Location.Tag          AS Location,
                Job_Type.Type         AS Type,
                Job.Status            AS Status
        FROM    Job
                LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
                LEFT JOIN OwnerWithRol AS Customer ON Job.Owner = Customer.ID
                LEFT JOIN Rol AS Rolodex ON Customer.Rol = Rolodex.ID
                LEFT JOIN JobType AS Job_Type ON Job_Type.ID = Job.Type
        WHERE   ({$conditions}) AND ({$search})";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = $database->query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT(Job.ID)
                FROM    Job;";
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
      $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>