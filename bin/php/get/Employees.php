<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
  //Connection
  $result = \singleton\database::getInstance( )->query(
    'Portal',
    " SELECT  [Connection].[ID]
      FROM    dbo.[Connection]
      WHERE       [Connection].[User] = ?
              AND [Connection].[Hash] = ?;",
    array(
      $_SESSION[ 'Connection' ][ 'User' ],
      $_SESSION[ 'Connection' ][ 'Hash' ]
    )
  );
  $Connection = sqlsrv_fetch_array($result);
  //User
  $result = \singleton\database::getInstance( )->query(
      null,
      " SELECT  Emp.fFirst  AS First_Name,
                Emp.Last    AS Last_Name,
                Emp.fFirst + ' ' + Emp.Last AS Name,
                Emp.Title AS Title,
                Emp.Field   AS Field
        FROM  Emp
        WHERE   Emp.ID = ?;",
      array(
          $_SESSION[ 'Connection' ][ 'User' ]
      )
  );
  $User   = sqlsrv_fetch_array( $result );
  //Privileges
  $Access = 0;
  $Hex = 0;
  $result = \singleton\database::getInstance( )->query(
      'Portal',
      "   SELECT  [Privilege].[Access],
                  [Privilege].[Owner],
                  [Privilege].[Group],
                  [Privilege].[Department],
                  [Privilege].[Database],
                  [Privilege].[Server],
                  [Privilege].[Other],
                  [Privilege].[Token],
                  [Privilege].[Internet]
        FROM      dbo.[Privilege]
        WHERE     Privilege.[User] = ?;",
      array(
          $_SESSION[ 'Connection' ][ 'User' ],
      )
  );
  $Privileges = array();
  if( $result ){while( $Privilege = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC ) ){

      $key = $Privilege['Access'];
      unset( $Privilege[ 'Access' ] );
      $Privileges[ $key ] = implode( '', array(
          dechex( $Privilege[ 'Owner' ] ),
          dechex( $Privilege[ 'Group' ] ),
          dechex( $Privilege[ 'Department' ] ),
          dechex( $Privilege[ 'Database' ] ),
          dechex( $Privilege[ 'Server' ] ),
          dechex( $Privilege[ 'Other' ] ),
          dechex( $Privilege[ 'Token' ] ),
          dechex( $Privilege[ 'Internet' ] )
      ) );
  }}
  if(     !isset( $Connection[ 'ID' ] )
      ||  !isset( $Privileges[ 'User' ] )
      ||  !check( privilege_read, level_group, $Privileges[ 'User' ] )
  ){ ?><?php print json_encode( array( 'data' => array( ) ) ); ?><?php }
  else {
    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Employee.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'First_Name' ] ) && !in_array( $_GET[ 'First_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['First_Name'];
      $conditions[] = "Employee.fFirst LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Last_Name' ] ) && !in_array( $_GET[ 'Last_Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Last_Name'];
      $conditions[] = "Employee.Last LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Supervisor' ] ) && !in_array( $_GET[ 'Supervisor' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Supervisor'];
      $conditions[] = "Employee.Super LIKE '%' + ? + '%'";
    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    $Columns = array(
      0 =>  'Employee.ID',
      1 =>  'Employee.fFirst',
      2 =>  'Employee.Last',
      3 =>  'tblWork.Super',
      4 =>  "tblWork.Latt + ', ' + tblWork.fLong"
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Employee.ID";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                    SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                            'Employee'                            AS Entity,
                            Employee.ID                           AS ID,
                            Employee.fFirst + ' ' + Employee.Last AS Name,
                            Employee.fFirst                       AS First_Name,
                            Employee.Last                         AS Last_Name,
                            tblWork.Super                         AS Supervisor,
                            tblWork.Latt                          AS Latitude,
                            tblWork.fLong                         AS Longitude,
                            CASE  WHEN Tickets_Assigned.Count IS NULL THEN 0 
                                  ELSE Tickets_Assigned.Count END       AS Tickets_Assigned,
                            CASE  WHEN Tickets_Active.Count IS NULL THEN 0 
                                  ELSE Tickets_Active.Count END         AS Tickets_Active
                    FROM    dbo.Emp AS Employee
                            LEFT JOIN dbo.tblWork ON 'A' + convert(varchar(10), Employee.ID) + ',' = tblWork.Members
                            LEFT JOIN (
                              SELECT    Tickets.Field_ID,
                                        Sum( Tickets.Count ) AS Count
                              FROM      (
                                          (
                                            SELECT    TicketO.fWork AS Field_ID,
                                                      Count( TicketO.ID ) AS Count
                                            FROM      TicketO
                                            WHERE     TicketO.Assigned = 1
                                            GROUP BY  TicketO.fWork
                                          )
                                        ) AS Tickets
                              GROUP BY  Tickets.Field_ID
                            ) AS Tickets_Assigned ON Tickets_Assigned.Field_ID = Employee.fWork
                            LEFT JOIN (
                              SELECT    Tickets.Field_ID,
                                        Sum( Tickets.Count ) AS Count
                              FROM      (
                                          (
                                            SELECT    TicketO.fWork AS Field_ID,
                                                      Count( TicketO.ID ) AS Count
                                            FROM      TicketO
                                            WHERE           TicketO.Assigned >= 2
                                                      AND   TicketO.Assigned <= 3
                                            GROUP BY  TicketO.fWork
                                          )
                                        ) AS Tickets
                              GROUP BY  Tickets.Field_ID
                            ) AS Tickets_Active ON Tickets_Active.Field_ID = Employee.fWork
                    WHERE   {$conditions}
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";

    $rResult = \singleton\database::getInstance( )->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "
       SELECT  Employee.ID
      FROM     dbo.Emp AS Employee
               LEFT JOIN dbo.tblWork ON 'A' + convert(varchar(10), Employee.ID) + ',' = tblWork.Members
      WHERE    {$conditions};";

    $fResult = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));

    $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
    $_SESSION[ 'Tables' ][ 'Employees' ] = isset( $_SESSION[ 'Tables' ][ 'Employees' ]  ) ? $_SESSION[ 'Tables' ][ 'Employees' ] : array( );
    $iFilteredTotal = 0;
    if( count( $_SESSION[ 'Tables' ][ 'Employees' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Employees' ] as &$Value ){ $Value = false; } }
    $_SESSION[ 'Tables' ][ 'Employees' ][ 0 ] = $_GET;
    while( $Row = sqlsrv_fetch_array( $fResult ) ){
        $_SESSION[ 'Tables' ][ 'Employees' ][ $Row[ 'ID' ] ] = true;
        $iFilteredTotal++;
    }

    $parameters = array( );
    $sQuery = " SELECT  COUNT(Emp.ID)
                FROM    Emp;";
    $rResultTotal = \singleton\database::getInstance( )->query(null,  $sQuery, $parameters ) or die(print_r(sqlsrv_errors()));
    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
    $iTotal = $aResultTotal[0];

    $output = array(
        'sEcho'         =>  intval( $_GET[ 'draw' ] ),
        'iTotalRecords'     =>  $iTotal,
        'iTotalDisplayRecords'  =>  $iFilteredTotal,
        'aaData'        =>  array()
    );

    while ( $Row = sqlsrv_fetch_array( $rResult ) ){
      $output['aaData'][]       = $Row;
    }
    echo json_encode( $output );
  }
}?>
