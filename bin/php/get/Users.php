<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
    'read_and_close' => true
  ] );
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $Connection = \singleton\database::getInstance( )->query(
      null,
    " SELECT  Top 1
              *
      FROM    Connection
      WHERE     Connection.Connector  = ?
            AND Connection.Hash     = ?;",
    array(
      $_SESSION['User'],
      $_SESSION['Hash']
    )
  );
  $Connection = sqlsrv_fetch_array($Connection);
  $User    = \singleton\database::getInstance( )->query(
      null,
    " SELECT  Top 1
              Emp.*,
              Emp.fFirst AS First_Name,
              Emp.Last   AS Last_Name
      FROM    Emp
      WHERE   Emp.ID = ?;",
    array(
      $_SESSION['User']
    )
  );
  $User = sqlsrv_fetch_array( $User );
  $r = \singleton\database::getInstance( )->query(
      null,
    " SELECT  Privilege.Access_Table,
              Privilege.User_Privilege,
              Privilege.Group_Privilege,
              Privilege.Other_Privilege
      FROM    Privilege
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION['User']
    )
  );
  $Privileges = array();
  while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
  $Privileged = False;
  if( isset($Privileges['User'])
        && (
          $Privileges['User']['User_Privilege'] >= 4
      ||  $Privileges['User']['Group_Privilege'] >= 4
      ||  $Privileges['User']['Other_Privilege'] >= 4)){
            $Privileged = True;
    }
    if( !isset( $Connection[ 'ID' ] ) || !$Privileged ){ print json_encode( array( 'data' => array( ) ) );}
  else {
    /*Construct Output Array*/
    $output = array(
          'sEcho'         => isset( $_GET[ 'draw' ] ) ? intval( $_GET[ 'draw' ] ) : 1,
          'iTotalRecords'     =>  0,
          'iTotalDisplayRecords'  =>  0,
          'aaData'        =>  array(),
          'options' => array( )
      );

    /*Parse GET*/
    $conditions = array( );
    $search   = array( );

    /*Filter $_GET Columns to SQL*/
      if( isset( $_GET[ 'ID' ] ) && !in_array(  $_GET[ 'ID' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['ID'];
        $conditions[] = "Employee.ID LIKE '%' + ? + '%'";
      }
      if( isset( $_GET[ 'First_Name' ] ) && !in_array( $_GET[ 'First_Name' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Person'];
        $conditions[] = "Employee.fFirst LIKE '%' + ? + '%'";
      }
      if( isset( $_GET[ 'Last_Name' ] ) && !in_array( $_GET[ 'Last_Name' ], array( '', ' ', null ) ) ){
        $parameters[] = $_GET['Last_Name'];
        $conditions[] = "Employee.Last LIKE '%' + ? + '%'";
      }

    /*Search Filters*/
    /*NONE*/

    /*Concatenate Filters*/
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
      $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    /*Row Number*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    /*Order && Direction*/
    //update columns from bin/js/tickets/table.js
    $Columns = array(
      0 =>  'Employee.ID',
      1 =>  'Employee.fFirst',
      2 =>  'Employee.Last'
      );
      $Order = isset( $Columns[ $_GET['order']['column'] ] )
          ? $Columns[ $_GET['order']['column'] ]
          : "Employee.ID";
      $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
        ? $_GET['order']['dir']
        : 'ASC';

    /*Perform Query*/
    $Query =
    " SELECT  *
      FROM  (
              SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                      Employee.ID AS ID,
                      Employee.fFirst AS First_Name,
                      Employee.Last AS Last_Name
              FROM    Emp AS Employee
              WHERE   ({$conditions}) AND ({$search})
            ) AS Tbl
      WHERE     Tbl.ROW_COUNT >= ?
            AND Tbl.ROW_COUNT <= ?;";
    $rResult = \singleton\database::getInstance( )->query(
        null,
      $Query,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    while ( $Ticket = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
        $output[ 'aaData' ][]       = $Ticket;
      }


      /*GET TOTAL NUMBER OF FILTERED ROWS*/
    $sQueryRow =
    " SELECT  Count( Employee.ID )
      FROM    Emp AS Employee
      WHERE   ({$conditions}) AND ({$search});";

    $stmt = \singleton\database::getInstance( )->query(
        null,
      $sQueryRow,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];
    sqlsrv_cancel( $stmt );

    /*GET TOTAL NUMBER OF ROWS IN TABLE*/
    $sQuery = " SELECT  COUNT(Emp.ID)
                FROM    Emp;";
    $rResultTotal = \singleton\database::getInstance( )->query(
        null,  
      $sQuery,
      array( $User[ 'ID' ] )
    ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];


    /*SET OUTPUT*/
    $output[ 'iTotalRecords' ] = $iTotal;
    $output[ 'iTotalDisplayRecords' ] = $iFilteredTotal;
    /*SET OPTIONS*/
    /*NONE*/

    /*PRINT JSON*/
    echo json_encode( $output );
  }
}?>
