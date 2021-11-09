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
      $conditions[] = "Location.Loc LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Name'];
      $conditions[] = "Location.Tag LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Customer'];
      $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Street' ] ) && !in_array( $_GET[ 'Street' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Street'];
      $conditions[] = "Location.Address LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'City' ] ) && !in_array( $_GET[ 'City' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['City'];
      $conditions[] = "Location.City LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'State' ] ) && !in_array( $_GET[ 'State' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['State'];
      $conditions[] = "Location.State LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Zip' ] ) && !in_array( $_GET[ 'Zip' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Zip'];
      $conditions[] = "Location.Zip LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Maintained' ] ) && !in_array( $_GET[ 'Maintained' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Maintained'];
      $conditions[] = "Location.Maint LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $params[] = $_GET['Status'];
      $conditions[] = "Location.Status LIKE '%' + ? + '%'";
    }

    if( $Privileges[ 'Location' ][ 'Other_Privilege' ] < 4 ){
        $params [] = $User[ 'fWork' ];
        $conditions[] = "Location.Loc IN ( SELECT Ticket.Location FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LID AS Location FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Loc AS Location FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Location)";
    }

    if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

      $params[] = $_GET['Search'];
      $search[] = "Location.Loc LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Location.Tag LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Customer.Name LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Location.Address LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Location.City LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Location.State LIKE '%' + ? + '%'";

      $params[] = $_GET['Search'];
      $search[] = "Location.Zip LIKE '%' + ? + '%'";

    }

    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    $Columns = array(
      0 =>  'Location.Loc',
      1 =>  'Location.Tag',
      2 =>  'OwnerWithRol.Name',
      3 =>  'Location.Address',
      4 =>  'Location.City',
      5 =>  'Location.State',
      6 =>  'Location.Zip'
    );
    $Order = isset( $Columns[ $_GET['order']['column'] ] )
        ? $Columns[ $_GET['order']['column'] ]
        : "Location.Loc";
    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
      ? $_GET['order']['dir']
      : 'ASC';

    $sQuery = " SELECT *
                FROM (
                  SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                          Location.Loc 		  AS ID,
                          Location.Tag 	 	  AS Name,
                          Customer.Name 	  AS Customer,
                          Location.Address  AS Street,
                          Location.City 	  AS City,
                          Location.State 	  AS State,
                          Location.Zip 		  AS Zip,
                          Location.Maint 	  AS Maintained,
			    		            Location.Status 	AS Status
                  FROM    Loc AS Location
                          LEFT JOIN (
                              SELECT  Owner.ID,
                                      Rol.Name,
                                      Owner.Status
                              FROM    Owner
                                      LEFT JOIN Rol ON Owner.Rol = Rol.ID
                          ) AS Customer ON Location.Owner = Customer.ID
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
        SELECT  Location.Loc 		AS ID,
			    Location.Tag 	 	AS Name,
			    Customer.Name 		AS Customer,
			    Location.Address  	AS Street,
			    Location.City 		AS City,
			    Location.State 		AS State,
			    Location.Zip 		AS Zip,
			    Location.Maint 		AS Maintained,
			    Location.Status 	AS Status
		FROM    Loc AS Location
		        LEFT JOIN (
                SELECT  Owner.ID,
                        Rol.Name,
                        Owner.Status
                FROM    Owner
                        LEFT JOIN Rol ON Owner.Rol = Rol.ID
            ) AS Customer ON Location.Owner = Customer.ID
		WHERE   ({$conditions}) AND ({$search})";

    $options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );
    $stmt = $database->query( $conn, $sQueryRow , $params, $options ) or die(print_r(sqlsrv_errors()));

    $iFilteredTotal = sqlsrv_num_rows( $stmt );

    $params = array(
      $DateStart,
      $DateEnd
    );
    $sQuery = " SELECT  COUNT(Elev.ID)
                FROM    Elev;";
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
      $Row['Start_Date'] = date('m/d/Y', strtotime( $Row[ 'Start_Date' ] ) );
      $Row['End_Date'] = date('m/d/Y', strtotime( $Row[ 'End_Date' ] ) );
      $Row['Escalation_Date'] = date('m/d/Y', strtotime( $Row[ 'Escalation_Date' ] ) );
      $output['aaData'][]   = $Row;
    }
    echo json_encode( $output );
}}
?>
