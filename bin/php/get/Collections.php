<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
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
    if( isset( $Privileges[ 'Invoice' ] )
        && $Privileges[ 'Invoice' ][ 'Other_Privilege' ]  >= 4
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
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
        $params[] = $_GET['Customer'];
        $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
        $params[] = $_GET['Location'];
        $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    } 
    if( isset($_GET[ 'Job' ] ) && !in_array( $_GET[ 'Job' ], array( '', ' ', null ) ) ){
        $params[] = $_GET['Job'];
        $conditions[] = "Job.fDesc LIKE '%' + ? + '%'";
    } 
    /*if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){
      
      $params[] = $_GET['Search'];
      $search[] = "OpenAR.Ref LIKE '%' + ? + '%'";
      
      $params[] = $_GET['Search'];
      $search[] = "OpenAR.fDesc LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

      $params[ ] = $_GET[ 'Search' ];
      $search[ ] = "Job.ID LIKE '%' + ? + '%'";

      $params[ ] = $_GET[ 'Search' ];
      $search[ ] = "Job.fDesc LIKE '%' + ? + '%'";
      
      $params[ ] = $_GET[ 'Search' ];
      $search[ ] = "JobType.Type LIKE '%' + ? + '%'";
    }*/

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $params[] = $Start;
    $params[] = $End;
    $Columns = array(
        0 =>  'OpenAR.Ref',
        1 =>  'Customer.Name',
        2 =>  'Location.Tag',
        3 =>  'Job.fDesc',
        4 =>  'JobType.Type',
        5 =>  'OpenAR.fDate',
        6 =>  'OpenAR.Due',
        7 =>  'OpenAR.Original',
        8 =>  'OpenAR.Balance',
        9 =>  'OpenAR.fDesc'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "OpenAR.Ref";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                    SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                            OpenAR.Ref AS ID,
                            Customer.ID AS Customer_ID,
                            Customer.Name AS Customer_Name,
                            Location.Loc AS Location_ID,
                            Location.Tag AS Location_Name,
                            Job.ID AS Job_ID,
                            Job.fDesc AS Job_Name,
                            JobType.Type AS Type,
                            OpenAR.fDate AS Date,
                            OpenAR.Due AS Due,
                            OpenAR.Original AS Original,
                            OpenAR.Balance AS Balance,
                            OpenAR.fDesc AS Description
                    FROM    OpenAR 
                            LEFT JOIN Invoice      ON OpenAR.Ref  = Invoice.Ref
                            LEFT JOIN Loc AS Location ON OpenAR.Loc  = Location.Loc
                            LEFT JOIN (
                                SELECT  Owner.ID AS ID,
                                        Rol.Name AS Name
                                FROM    Owner 
                                        LEFT JOIN Rol ON Owner.Rol = Rol.ID
                            ) AS Customer ON Location.Owner   = Customer.ID
                            LEFT JOIN Job          ON Invoice.Job = Job.ID
                            LEFT JOIN JobType      ON Job.Type    = JobType.ID      
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
                OpenAR.Ref AS ID,
                Customer.Name AS Customer,
                Location.Tag AS Location,
                Job.fDesc AS Job,
                JobType.Type AS Type,
                OpenAR.fDate AS Date,
                OpenAR.Due AS Due,
                OpenAR.Original AS Original,
                OpenAR.Balance AS Balance,
                OpenAR.fDesc AS Description
        FROM    OpenAR 
                LEFT JOIN Invoice      ON OpenAR.Ref  = Invoice.Ref
                LEFT JOIN Loc AS Location ON OpenAR.Loc  = Location.Loc
                LEFT JOIN (
                    SELECT  Owner.ID AS ID,
                            Rol.Name AS Name
                    FROM    Owner 
                            LEFT JOIN Rol ON Owner.Rol = Rol.ID
                ) AS Customer ON Location.Owner   = Customer.ID
                LEFT JOIN Job          ON Invoice.Job = Job.ID
                LEFT JOIN JobType      ON Job.Type    = JobType.ID      
        WHERE   ({$conditions}) AND ({$search});";
    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = $database->query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT( OpenAR.Ref )
                FROM    OpenAR;";
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
        $Row[ 'Date' ] = date( 'm/d/Y', strtotime( $Row[ 'Date' ] ) );
        $Row[ 'Due' ] = date( 'm/d/Y', strtotime( $Row[ 'Due' ] ) );
        $Row[ 'Original' ] = '$' . number_format( $Row[ 'Original' ], 2);
        $Row[ 'Balance' ] = '$' . number_format( $Row[ 'Balance' ], 2);
        $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>