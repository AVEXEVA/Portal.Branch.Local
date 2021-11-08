<?php
session_start( [ 'read_and_close' => true ] );
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('../index.php');
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
    if( isset( $Privileges[ 'Unit' ] )
        && $Privileges[ 'Unit' ][ 'User_Privilege' ]  >= 4
    ){ $Privileged = True; }
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {

      $output = array(
          'sEcho'                 =>  intval( $_GET['draw' ] ),
          'iTotalRecords'         =>  $iTotal,
          'iTotalDisplayRecords'  =>  $iFilteredTotal,
          'aaData'                =>  array( ),
          'options'               =>  array( )
      );

    $_GET['iDisplayStart'] = isset($_GET['start']) ? $_GET['start'] : 0;
    $_GET['iDisplayLength'] = isset($_GET['length']) ? $_GET['length'] : '15';

    $Start = $_GET['iDisplayStart'];
    $Length = $_GET['iDisplayLength'];
    $End = $Length == '-1' ? 999999 : intval($Start) + intval($Length);

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Unit.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'City_ID' ] ) && !in_array( $_GET[ 'City_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['City_ID'];
      $conditions[] = "Unit.State LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Location'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Building_ID' ] ) && !in_array( $_GET[ 'Building_ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Building_ID'];
      $conditions[] = "Unit.Unit LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Type'];
      $conditions[] = "Unit.Type LIKE '%' + ? + '%'";
    } 
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Status'] ;
      $conditions[] = "Unit.Status LIKE '%' + ? + '%'";
    }
    
    /*if( $Privileges[ 'Unit' ][ 'Other_Privilege' ] < 4 ){
        $parameters [] = $User[ 'fWork' ];
        $conditions[] = "Unit.ID IN ( SELECT Ticket.Unit FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LElev AS Unit FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Elev AS Unit FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Unit)";
    }*/

    /*Search Filters*/
    //if( isset( $_GET[ 'search' ] ) ){ }
    

    /*Concatenate Filters*/
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    /*ROW NUMBER*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    $parameters[] = $End;
    $Columns = array(
      0 =>  'Unit.ID',
      1 =>  'Unit.State',
      2 =>  'Loc.Tag',
      3 =>  'Unit.Unit',
      4 =>  'Unit.Type',
      5 =>  'Unit.Status'
    );

    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Unit.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Unit.ID AS ID,
                          CASE WHEN Unit.State IN ( null, ' ', '  ' ) THEN 'Untitled' ELSE Unit.State END AS City_ID,
                          Customer.ID AS Customer_ID,
                          Customer.Name AS Customer_Name,
                          Location.Loc AS Location_ID,
                          Location.Tag AS Location_Name,
                          Unit.Unit AS Building_ID,
                          Unit.Type AS Type,
                          Unit.Status AS Status,
                          Ticket.ID AS Ticket_ID,
                          Ticket.Date AS Ticket_Date
                  FROM    Elev AS Unit
                          LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                          LEFT JOIN (
                            SELECT  Owner.ID,
                                    Rol.Name 
                            FROM    Owner 
                                    LEFT JOIN Rol ON Rol.ID = Owner.Rol
                        ) AS Customer ON Unit.Owner = Customer.ID
                        LEFT JOIN (
                          SELECT    ROW_NUMBER() OVER ( PARTITION BY TicketD.Elev ORDER BY TicketD.EDate DESC ) AS ROW_COUNT,
                                    TicketD.Elev AS Unit,
                                    TicketD.ID, 
                                    TicketD.EDate AS Date
                          FROM      TicketD
                        ) AS Ticket ON Ticket.Unit = Unit.ID AND Ticket.ROW_COUNT = 1
                  WHERE   ({$conditions}) AND ({$search})
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
    //echo $sQuery;
    //var_dump( $parameters );
    $rResult = $database->query(
      $conn,  
      $sQuery, 
      $parameters 
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "
        SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                Unit.ID AS ID,
                CASE WHEN Unit.State IN ( null, ' ', '  ' ) THEN 'Untitled' ELSE Unit.State END AS City_ID,
                Customer.ID AS Customer_ID,
                Customer.Name AS Customer_Name,
                Location.Loc AS Location_ID,
                Location.Tag AS Location_Name,
                Unit.Unit AS Building_ID,
                Unit.Type AS Type,
                Unit.Status AS Status,
                Ticket.ID AS Ticket_ID,
                Ticket.Date AS Ticket_Date
        FROM    Elev AS Unit
                LEFT JOIN Loc AS Location ON Unit.Loc = Location.Loc
                LEFT JOIN (
                  SELECT  Owner.ID,
                          Rol.Name 
                  FROM    Owner 
                          LEFT JOIN Rol ON Rol.ID = Owner.Rol
              ) AS Customer ON Unit.Owner = Customer.ID
              LEFT JOIN (
                SELECT    ROW_NUMBER() OVER ( PARTITION BY TicketD.Elev ORDER BY TicketD.EDate DESC ) AS ROW_COUNT,
                          TicketD.Elev AS Unit,
                          TicketD.ID, 
                          TicketD.EDate AS Date
                FROM      TicketD
              ) AS Ticket ON Ticket.Unit = Unit.ID AND Ticket.ROW_COUNT = 1
        WHERE   ({$conditions}) AND ({$search});";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = $database->query( $conn, $sQueryRow , $parameters, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $parameters = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT( Unit.ID )
                FROM    Elev AS Unit;";
    $rResultTotal = $database->query($conn,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    
 
    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
      $Row[ 'Ticket_Date' ] = date( 'm/d/Y h:i A', strtotime( $Row[ 'Ticket_Date' ] ) );
      $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>