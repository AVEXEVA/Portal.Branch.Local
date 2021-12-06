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
      ||  !isset( $Privileges[ 'Requisition' ] )
      ||  !check( privilege_read, level_group, $Privileges[ 'Requisition' ] )
  ){ ?><?php print json_encode( array( 'data' => array( ) ) ); ?><?php }
  else {
    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'ID' ];
      $conditions[] = "Requisition.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Employee' ] ) && !in_array( $_GET[ 'Employee' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'Employee' ];
      $conditions[] = "Employee.fFirst + ' ' + Employee.Last LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Date_Start' ] ) && !in_array( $_GET[ 'Date_Start' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Date_Start'];
      $conditions[] = "Requisition.[Date] >= ?";
    }
    if( isset($_GET[ 'Date_End' ] ) && !in_array( $_GET[ 'Date_End' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Date_End'];
      $conditions[] = "Requisition.[Date] < ?";
    }
    if( isset($_GET[ 'Required_Start' ] ) && !in_array( $_GET[ 'Required_Start' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Required_Start'];
      $conditions[] = "Requisition.[Required] >= ?";
    }
    if( isset($_GET[ 'Required_End' ] ) && !in_array( $_GET[ 'Required_End' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Required_End'];
      $conditions[] = "Requisition.[Required] < ?";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'Customer' ];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'Location' ];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Dropoff' ] ) && !in_array( $_GET[ 'Dropoff' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'Dropoff' ];
      $conditions[] = "Dropoff.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Unit' ] ) && !in_array( $_GET[ 'Unit' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'Unit' ];
      $conditions[] = "Unit.State + ' - ' + Unit.Unit LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Job' ] ) && !in_array( $_GET[ 'Job' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET[ 'Job' ];
      $conditions[] = "Job.fDesc LIKE '%' + ? + '%'";
    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

    $Columns = array(
      0 =>  "Requisition.ID",
      1 =>  "Employee.fFirst + ' ' + Employee.Last",
      2 =>  'Requisition.Date',
      3 =>  'Requisition.Required',
      4 =>  'Customer.Name',
      5 =>  'Location.Tag',
      6 =>  'Dropoff.Tag',
      7 =>  "Unit.State + ' ' + Unit.Unit"
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
                            Requisition.ID              AS ID,
                            Employee.fFirst + ' ' + Employee.Last AS Employee,
                            Requisition.[Date]          AS Date,
                            Requisition.[Required]      AS Required,
                            Location.Tag                AS Location,
                            DropOff.Tag                 AS DropOff,
                            Unit.State                  AS Unit,
                            Job.fDesc                   AS Job
                    FROM    Requisition
                            LEFT JOIN Loc   AS Location ON Requisition.Location = Location.Loc
                            LEFT JOIN Loc   AS DropOff  ON Requisition.DropOff = DropOff.Loc
                            LEFT JOIN Elev  AS Unit     ON Requisition.Unit = Unit.ID
                            LEFT JOIN Job   AS Job      ON Requisition.Job = Job.ID
                            LEFT JOIN Emp   AS Employee ON Employee.ID = Requisition.[User]
                    WHERE   {$conditions}
                ) AS Tbl
                WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";

    $rResult = \singleton\database::getInstance( )->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));

    $sQueryRow = "  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                            Requisition.ID              AS ID,
                            Employee.fFirst + ' ' + Employee.Last AS Employee,
                            Requisition.[Date]          AS Date,
                            Requisition.[Required]      AS Required,
                            Location.Tag                AS Location,
                            DropOff.Tag                 AS DropOff,
                            Unit.State                  AS Unit,
                            Job.fDesc                   AS Job
                    FROM    Requisition
                            LEFT JOIN Loc   AS Location ON Requisition.Location = Location.Loc
                            LEFT JOIN Loc   AS DropOff  ON Requisition.DropOff = DropOff.Loc
                            LEFT JOIN Elev  AS Unit     ON Requisition.Unit = Unit.ID
                            LEFT JOIN Job   AS Job      ON Requisition.Job = Job.ID
                            LEFT JOIN Emp   AS Employee ON Employee.ID = Requisition.[User]
                    WHERE   {$conditions};";

    $fResult = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));


    $iFilteredTotal = 0;
    $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
    $_SESSION[ 'Tables' ][ 'Requisitions' ] = isset( $_SESSION[ 'Tables' ][ 'Requisitions' ]  ) ? $_SESSION[ 'Tables' ][ 'Requisitions' ] : array( );
    if( count( $_SESSION[ 'Tables' ][ 'Requisitions' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Requisitions' ] as &$Value ){ $Value = false; } }
    $_SESSION[ 'Tables' ][ 'Requisitions' ][ 0 ] = $_GET;
    while( $Row = sqlsrv_fetch_array( $fResult ) ){
        $_SESSION[ 'Tables' ][ 'Requisitions' ][ $Row[ 'ID' ] ] = true;
        $iFilteredTotal++;
    }

    $parameters = array( );
    $sQuery = " SELECT  COUNT( Requisition.ID )
                FROM    Requisition;";
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
      $Row[ 'Date ']          = date( "m/d/Y h:i A", strtotime( $Row[ 'Date' ] ) );
      $Row[ 'Required' ]      = date( "m/d/Y",       strtotime( $Row[ 'Required' ] ) );
      $output[ 'aaData' ][ ]  = $Row;
    }
    echo json_encode( $output );
  }
}?>
