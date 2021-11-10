<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
    'read_and_close' => true
  ] );
  require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
  //Connection
  $result = \singleton\database::getInstance( )->query(
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
  $Connection = sqlsrv_fetch_array( $result );

  //User
  $result = \singleton\database::getInstance( )->query(
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
  $User = sqlsrv_fetch_array( $result );

  //Privileges
  $result = \singleton\database::getInstance( )->query(
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
  while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
  $Privileged = False;
  if(     isset( $Privileges[ 'Job' ] )
      &&  $Privileges[ 'Job' ][ 'User_Privilege' ]  >= 4
      &&  $Privileges[ 'Job' ][ 'Group_Privilege' ]  >= 4
      &&  $Privileges[ 'Job' ][ 'Other_Privilege' ]  >= 4
  ){        $Privileged = True; }
  if( !isset($Connection['ID']) || !$Privileged ){print json_encode( array( 'data' => array( ) ) );}
  else {

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Job.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Name'];
      $conditions[] = "Job.fDesc LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Location'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Type'];
      $conditions[] = "Job_Type.Type LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Status'];
      $conditions[] = "Job.Status LIKE '%' + ? + '%'";
    }
    /*if( $Privileges[ 'Job' ][ 'Other_Privilege' ] < 4 ){
        $parameters [] = $User[ 'fWork' ];
        $conditions[] = "Location.Loc IN ( SELECT Ticket.Location FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LID AS Location FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Loc AS Location FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Location)";
    }*/

    /*if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $parameters[] = $_GET['Search'];
      $search[] = "Job.ID LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Job.fDesc LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Job_Type.Type LIKE '%' + ? + '%'";

      $parameters[] = $_GET['Search'];
      $search[] = "Job.Status LIKE '%' + ? + '%'";

    }*/

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    /*ROW NUMBER*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

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
                          Job.fDate             AS Date,
                          Customer.ID           AS Customer_ID,
                          Customer.Name         AS Customer_Name,
                          Location.Loc          AS Location_ID,
                          Location.Tag          AS Location_Name,
                          Location.Address      AS Location_Street,
                          Location.City         AS Location_City,
                          Location.State        AS Location_State,
                          Location.Zip          AS Location_Zip,
                          Job_Type.Type         AS Type,
                          Job.Status            AS Status,
                          Job_Tickets.Count     AS Tickets,
                          Job_Invoices.Count    AS Invoices
                  FROM    Job
                          LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
                          LEFT JOIN (
                              SELECT  Owner.ID,
                                      Rol.Name
                              FROM    Owner
                                      LEFT JOIN Rol ON Rol.ID = Owner.Rol
                          ) AS Customer ON Job.Owner = Customer.ID
                          LEFT JOIN JobType AS Job_Type ON Job_Type.ID = Job.Type
                          LEFT JOIN (
                            SELECT    TicketD.Job,
                                      Count( TicketD.ID ) AS Count
                            FROM      TicketD
                            GROUP BY  TicketD.Job
                          ) AS Job_Tickets ON Job_Tickets.Job = Job.ID
                          LEFT JOIN (
                            SELECT    Invoice.Job,
                                      Count( Invoice.Ref ) AS Count
                            FROM      Invoice
                            GROUP BY  Invoice.Job
                          ) AS Job_Invoices ON Job_Invoices.Job = Job.ID
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    $rResult = \singleton\database::getInstance( )->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow =
      " SELECT  Count( Job.ID ) AS Count
        FROM    Job
                LEFT JOIN Loc AS Location ON Job.Loc = Location.Loc
                LEFT JOIN (
                    SELECT  Owner.ID,
                            Rol.Name
                    FROM    Owner
                            LEFT JOIN Rol ON Rol.ID = Owner.Rol
                ) AS Customer ON Job.Owner = Customer.ID
                LEFT JOIN JobType AS Job_Type ON Job_Type.ID = Job.Type
        WHERE   ({$conditions}) AND ({$search})";

    $stmt = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));
    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];

    $sQuery = " SELECT  COUNT( Job.ID )
                FROM    Job;";
    $rResultTotal = \singleton\database::getInstance( )->query( null,  $sQuery ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval( $_GET[ 'draw' ] ),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array()
    );

    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
      $Row[ 'Date' ] = date( 'm/d/Y', strtotime( $Row[ 'Date' ] ) );
      $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
  }
}
?>
