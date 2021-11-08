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
      $conditions[] = "Estimate.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Date' ] ) && !in_array( $_GET[ 'Date' ], array( '', ' ', null ) ) ){
      $params[] = date( 'Y-m-d', strtotime( $_GET['Date'] ) );
      $conditions[] = "Estimate.fDate LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Contact' ] ) && !in_array( $_GET[ 'Contact' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Contact'];
      $conditions[] = "Estimate.Name LIKE '%' + ? + '%'";
    }
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
    if( isset($_GET[ 'Title' ] ) && !in_array( $_GET[ 'Title' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Title'];
      $conditions[] = "Estimate.Name LIKE '%' + ? + '%'";
    }
    if( $Privileges[ 'Location' ][ 'Other_Privilege' ] < 4 ){
        $params [] = $User[ 'fWork' ];
        $conditions[] = "Location.Loc IN ( SELECT Ticket.Location FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LID AS Location FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Loc AS Location FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Location)";
    }

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){
      
      $params[] = $_GET['Search'];
      $search[] = "Estimate.ID LIKE '%' + ? + '%'";
      
      $params[] = $_GET['Search'];
      $search[] = "Estimate.fDate LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Job.fDesc LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Estimate.Name LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $params[] = $Start;
    $params[] = $End;
    $Columns = array(
      0 =>  'Estimate.ID',
      1 =>  'Estimate.fDate',
      2 =>  'Estimate.Name',
      3 =>  'Customer.Name',
      4 =>  'Location.Tag',
      5 =>  'Job.fDesc',
      6 =>  'Estimate.fDesc',
      7 =>  'Estimate.Cost',
      8 =>  'Estimate.Price',
      9 =>  'Estimate.Status'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Estimate.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Estimate.ID 		  AS ID,
                          Estimate.fDate    AS Date,
                          Estimate.Name	 	  AS Contact,
                          Customer.Name     AS Customer,
                          Location.Tag      AS Location,
                          Job.fDesc         AS Job,
                          Estimate.fDesc 	  AS Title,
                          Estimate.Cost     AS Cost,
                          Estimate.Price    AS Price,
                          Estimate.Status   AS Status
                  FROM    Estimate
                          LEFT JOIN Job ON Job.ID = Estimate.Job
                          LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
                          LEFT JOIN (
                              SELECT  Owner.ID,
                                      Rol.Name,
                                      Owner.Status 
                              FROM    Owner 
                                      LEFT JOIN Rol ON Owner.Rol = Rol.ID
                          ) AS Customer ON Job.Owner = Customer.ID
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
                Estimate.ID       AS ID,
                Estimate.fDate    AS Date,
                Estimate.Name     AS Contact,
                Customer.Name     AS Customer,
                Location.Tag      AS Location,
                Job.fDesc         AS Job,
                Estimate.fDesc    AS Title,
                Estimate.Cost     AS Cost,
                Estimate.Price    AS Price,
                Estimate.Status   AS Status
        FROM    Estimate
                LEFT JOIN Job ON Job.ID = Estimate.Job
                LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
                LEFT JOIN (
                    SELECT  Owner.ID,
                            Rol.Name,
                            Owner.Status 
                    FROM    Owner 
                            LEFT JOIN Rol ON Owner.Rol = Rol.ID
                ) AS Customer ON Job.Owner = Customer.ID
        WHERE   ({$conditions}) AND ({$search});";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = $database->query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT( Estimate.ID )
                FROM    Estimate;";
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
      $Row[ 'Date' ]  = date('m/d/Y', strtotime( $Row[ 'Date' ] ) );
      $Row[ 'Cost' ]  = '$' . number_format( $Row[ 'Cost' ], 2 );
      $Row[ 'Price' ] = '$' . number_format( $Row[ 'Price' ], 2 );
      $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>