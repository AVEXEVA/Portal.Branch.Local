<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = \singleton\database::getInstance( )->query(
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
    $conditions = array( );
    $search = array( );
    $parameters = array( );

    	$_GET[ 'Date' ]	 	= isset( $_GET[ 'Date' ] )  		&& !in_array( $_GET[ 'Date' ], array( '', ' ', null ) ) 		? DateTime::createFromFormat( 'm/d/Y', $_GET['Date'] )->format( 'Y-m-d 00:00:00.000' ) 		: null;

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Estimate.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Date' ] ) && !in_array( $_GET[ 'Date' ], array( '', ' ', null ) ) ){
      $parameters[] = date( 'Y-m-d', strtotime( $_GET['Date'] ) );
      $conditions[] = "Estimate.fDate LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Contact' ] ) && !in_array( $_GET[ 'Contact' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Contact'];
      $conditions[] = "Estimate.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Location'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Job' ] ) && !in_array( $_GET[ 'Job' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Job'];
      $conditions[] = "Job.fDesc LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Status'];
      $conditions[] = "Estimate.Status LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Title' ] ) && !in_array( $_GET[ 'Title' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Title'];
      $conditions[] = "Estimate.Name LIKE '%' + ? + '%'";
    }
    if( $Privileges[ 'Location' ][ 'Other_Privilege' ] < 4 ){
        $parameters [] = $User[ 'fWork' ];
        $conditions[] = "Location.Loc IN ( SELECT Ticket.Location FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LID AS Location FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Loc AS Location FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Location)";
    }
    /*if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['Search'];
      $search[] = "Estimate.ID LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Estimate.fDate LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Job.fDesc LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Estimate.Name LIKE '%' + ? + '%'";

    }*/

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;
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
                          CASE  WHEN Estimate.Status = 0 THEN 'Open'
                                WHEN Estimate.Status = 1 THEN 'Canceled'
                                WHEN Estimate.Status = 2 THEN 'Withdrawn'
                                WHEN Estimate.Status = 3 THEN 'Disqualified'
                                WHEN Estimate.Status = 4 THEN 'Award Successful'
                                ELSE 'Unknown Status'
                          END AS Status,
                          Contact.ID        AS Contact_ID,
                          Contact.Name      AS Contact_Name,
                          Contact.EMail     AS Contact_Email,
                          Contact.Cellular  AS Contact_Phone,
                          Contact.Address   AS Contact_Street,
                          Contact.City      AS Contact_City,
                          Contact.State     AS Contact_State,
                          Contact.Zip       AS Contact_Zip,
                          Customer.Name     AS Customer,
                          Location.Tag      AS Location,
                          Job.fDesc         AS Job,
                          Estimate.fDesc 	  AS Title,
                          Estimate.Cost     AS Cost,
                          Estimate.Price    AS Price,
                          Territory.ID      AS Territory_ID,
                          Territory.Name    AS Territory_Name
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
                          LEFT JOIN Rol AS Contact ON Contact.ID = Estimate.RolID
                          LEFT JOIN Terr AS Territory ON Territory.ID = Location.Terr
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    $rResult = \singleton\database::getInstance( )->query(
      $conn,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "  SELECT  Count( Estimate.ID ) AS Count
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

    $stmt = \singleton\database::getInstance( )->query( $conn, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];

    $sQuery = " SELECT  COUNT( Estimate.ID )
                FROM    Estimate;";
    $rResultTotal = \singleton\database::getInstance( )->query($conn,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
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
