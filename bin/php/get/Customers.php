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
        FROM    [Connection]
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
          FROM      [Privilege]
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
        ||  !isset( $Privileges[ 'Customer' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Customer' ] )
    ){ ?><?php print json_encode( array( 'data' => array( ) ) ); ?><?php }
    else {

        $conditions = array( );
        $search = array( );
        $parameters = array( );

        if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
          $parameters[] = $_GET['ID'];
          $conditions[] = "Customer.ID LIKE '%' + ? + '%'";
        }
        if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
          $parameters[] = $_GET['Name'];
          $conditions[] = "Customer.Name LIKE '%' + ? + '%'";
        }
        if( isset($_GET[ 'Location' ] ) && !in_array( $_GET[ 'Location' ], array( '', ' ', null ) ) ){
          $parameters[] = $_GET['Location'];
          $conditions[] = "Customer.Location LIKE '%' + ? + '%'";
        }
        if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
          $parameters[] = $_GET['Status'];
          $conditions[] = "Customer.Status LIKE '%' + ? + '%'";
        }
        if( isset($_GET[ 'Units' ] ) && !in_array( $_GET[ 'Units' ], array( '', ' ', null ) ) ){
          $parameters[] = $_GET['Units'];
          $conditions[] = "Customer.Unit LIKE '%' + ? + '%'";
        }
        if( isset($_GET[ 'Jobs' ] ) && !in_array( $_GET[ 'Jobs' ], array( '', ' ', null ) ) ){
          $parameters[] = $_GET['Jobs'];
          $conditions[] = "Customer.Job LIKE '%' + ? + '%'";
        }
        if( isset($_GET[ 'Tickets' ] ) && !in_array( $_GET[ 'Tickets' ], array( '', ' ', null ) ) ){
          $parameters[] = $_GET['Tickets'];
          $conditions[] = "Customer.Ticket LIKE '%' + ? + '%'";
        }
        if( isset($_GET[ 'Violations' ] ) && !in_array( $_GET[ 'Violations' ], array( '', ' ', null ) ) ){
          $parameters[] = $_GET['Violations'];
          $conditions[] = "Customer.Violation LIKE '%' + ? + '%'";
        }
        if( isset($_GET[ 'Invoices' ] ) && !in_array( $_GET[ 'Invoices' ], array( '', ' ', null ) ) ){
          $parameters[] = $_GET['Invoices'];
          $conditions[] = "Customer.Invoice LIKE '%' + ? + '%'";
        }
        if( isset( $_GET[ 'Search' ] ) && !in_array( $_GET[ 'Search' ], array( '', ' ', null ) )  ){

          $parameters[] = $_GET['Search'];
          $search[] = "Customer.ID LIKE '%' + ? + '%'";

          $parameters[] = $_GET['Search'];
          $search[] = "Customer.Name LIKE '%' + ? + '%'";

          $parameters[] = $_GET['Search'];
          $search[] = "Customer.Status LIKE '%' + ? + '%'";

        }

        $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
        $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

        $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
        $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

        $Columns = array(
          0 =>  'Customer.ID',
          1 =>  'Customer.Name',
          2 =>  'Customer.Status',
          3 =>  'Locations.Count',
          4 =>  'Units.Count',
          5 =>  'Jobs.Count',
          6 =>  'Tickets.Count',
          7 =>  'Violations.Count',
          8 =>  'Invoices.Count'
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
                                Customer.ID,
                                Customer.Name,
                                CASE    WHEN Customer.Status = 0 THEN 'Enabled'
                                        WHEN Customer.Status = 1 THEN 'Disabled'
                                        ELSE 'Error' END AS Status,
                                CASE    WHEN Locations.Count IS NULL THEN 0
                                        ELSE Locations.Count END AS Locations,
                                CASE    WHEN Units.Count IS NULL THEN 0
                                        ELSE Units.Count END AS Units,
                                CASE    WHEN Jobs.Count IS NULL THEN 0
                                        ELSE Jobs.Count END AS Jobs,
                                CASE    WHEN Tickets.Count IS NULL THEN 0
                                        ELSE Tickets.Count END AS Tickets,
                                CASE    WHEN Violations.Count IS NULL THEN 0
                                        ELSE Violations.Count END AS Violations,
                                CASE    WHEN Invoices.Count IS NULL THEN 0
                                        ELSE Invoices.Count END AS Invoices
                        FROM    (
                                    SELECT  Owner.ID,
                                            Rol.Name,
                                            Owner.Status
                                    FROM    Owner
                                            LEFT JOIN Rol ON Owner.Rol = Rol.ID
                                ) AS Customer
                                LEFT JOIN (
                                    SELECT      Location.Owner  AS Customer,
                                                COUNT( Location.Loc ) AS Count
                                    FROM        Loc AS Location
                                    GROUP BY    Location.Owner
                                ) AS Locations ON Locations.Customer = Customer.ID
                                LEFT JOIN (
                                    SELECT      Unit.Owner  AS Customer,
                                                COUNT( Unit.ID ) AS Count
                                    FROM        Elev AS Unit
                                    GROUP BY    Unit.Owner
                                ) AS Units ON Units.Customer = Customer.ID
                                LEFT JOIN (
                                    SELECT      Job.Owner  AS Customer,
                                                COUNT( Job.ID ) AS Count
                                    FROM        Job AS Job
                                    GROUP BY    Job.Owner
                                ) AS Jobs ON Jobs.Customer = Customer.ID
                                LEFT JOIN (
                                    SELECT      Job.Owner  AS Customer,
                                                COUNT( Ticket.ID ) AS Count
                                    FROM        TicketD AS Ticket
                                                LEFT JOIN Job ON Ticket.Job = Job.ID
                                    GROUP BY    Job.Owner
                                ) AS Tickets ON Tickets.Customer = Customer.ID
                                LEFT JOIN (
                                    SELECT      Job.Owner  AS Customer,
                                                COUNT( Violation.ID ) AS Count
                                    FROM        Violation AS Violation
                                                LEFT JOIN Job ON Violation.Job = Job.ID
                                    GROUP BY    Job.Owner
                                ) AS Violations ON Violations.Customer = Customer.ID
                                LEFT JOIN (
                                    SELECT      Job.Owner  AS Customer,
                                                COUNT( Invoice.Ref ) AS Count
                                    FROM        Invoice AS Invoice
                                                LEFT JOIN Job ON Invoice.Job = Job.ID
                                    GROUP BY    Job.Owner
                                ) AS Invoices ON Invoices.Customer = Customer.ID
                        WHERE   {$conditions}
                    ) AS Tbl
                    WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";

        $rResult = \singleton\database::getInstance( )->query(
          null,
          $sQuery,
          $parameters
        ) or die(print_r(sqlsrv_errors()));

        $sQueryRow =
          "  SELECT   Customer.ID
              FROM    (
                        SELECT  Owner.ID,
                                Rol.Name,
                                Owner.Status
                        FROM    Owner
                                LEFT JOIN Rol ON Owner.Rol = Rol.ID
                    ) AS Customer
                    LEFT JOIN (
                        SELECT      Location.Owner  AS Customer,
                                    COUNT( Location.Loc ) AS Count
                        FROM        Loc AS Location
                        GROUP BY    Location.Owner
                    ) AS Locations ON Locations.Customer = Customer.ID
                    LEFT JOIN (
                        SELECT      Unit.Owner  AS Customer,
                                    COUNT( Unit.ID ) AS Count
                        FROM        Elev AS Unit
                        GROUP BY    Unit.Owner
                    ) AS Units ON Units.Customer = Customer.ID
                    LEFT JOIN (
                        SELECT      Job.Owner  AS Customer,
                                    COUNT( Job.ID ) AS Count
                        FROM        Job AS Job
                        GROUP BY    Job.Owner
                    ) AS Jobs ON Jobs.Customer = Customer.ID
                    LEFT JOIN (
                        SELECT      Job.Owner  AS Customer,
                                    COUNT( Ticket.ID ) AS Count
                        FROM        TicketD AS Ticket
                                    LEFT JOIN Job ON Ticket.Job = Job.ID
                        GROUP BY    Job.Owner
                    ) AS Tickets ON Tickets.Customer = Customer.ID
                    LEFT JOIN (
                        SELECT      Job.Owner  AS Customer,
                                    COUNT( Violation.ID ) AS Count
                        FROM        Violation AS Violation
                                    LEFT JOIN Job ON Violation.Job = Job.ID
                        GROUP BY    Job.Owner
                    ) AS Violations ON Violations.Customer = Customer.ID
                    LEFT JOIN (
                        SELECT      Job.Owner  AS Customer,
                                    COUNT( Invoice.Ref ) AS Count
                        FROM        Invoice AS Invoice
                                    LEFT JOIN Job ON Invoice.Job = Job.ID
                        GROUP BY    Job.Owner
                    ) AS Invoices ON Invoices.Customer = Customer.ID
            WHERE   {$conditions};";

        $fResult = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));


        $iFilteredTotal = 0;
        $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
        $_SESSION[ 'Tables' ][ 'Customers' ] = isset( $_SESSION[ 'Tables' ][ 'Customers' ]  ) ? $_SESSION[ 'Tables' ][ 'Customers' ] : array( );
        if( count( $_SESSION[ 'Tables' ][ 'Customers' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Customers' ] as &$Value ){ $Value = false; } }
        $_SESSION[ 'Tables' ][ 'Customers' ][ 0 ] = $_GET;
        while( $Row = sqlsrv_fetch_array( $fResult ) ){
            $_SESSION[ 'Tables' ][ 'Customers' ][ $Row[ 'ID' ] ] = true;
            $iFilteredTotal++;
        }

        $parameters = array( );
        $sQuery = " SELECT  COUNT(Owner.ID)
                    FROM    Owner;";
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
}
?>
