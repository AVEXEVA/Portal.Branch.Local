<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
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
        ||  !isset( $Privileges[ 'Unit' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Unit' ] )
      ){ ?><?php print json_encode( array( 'data' => array( ) ) );?><?php }
      else {

    $conditions = array( );
    $search = array( );
    $parameters = array( );

    if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['ID'];
      $conditions[] = "Unit.ID LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Name'];
      $conditions[] = "( Unit.State LIKE '%' + ? + '%' OR Unit.State LIKE '%' + ? + '%' )";
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
      $conditions[] = "Unit.Type LIKE '%' + ? + '%'";
    }
    if( isset($_GET[ 'Status' ] ) && !in_array( $_GET[ 'Status' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Status'] ;
      $conditions[] = "Unit.Status LIKE '%' + ? + '%'";
    }

    /*if( $Privileges[ 'Unit' ][ 'Other' ] < 4 ){
        $parameters [] = $User[ 'fWork' ];
        $conditions[] = "Unit.ID IN ( SELECT Ticket.Unit FROM ( ( SELECT TicketO.fWork AS Field, TicketO.LElev AS Unit FROM TicketO ) UNION ALL ( SELECT TicketD.fWork AS Field, TicketD.Elev AS Unit FROM TicketD ) ) AS Ticket WHERE Ticket.Field = ? GROUP BY Ticket.Unit)";
    }*/
    /*Search Filters*/
    if( isset( $_GET[ 'search' ] ) ){

      $search[] = " Unit.ID LIKE '%' + ? + '%'";
      $parameters[] = $_GET[ 'search' ];

      $search[] = " Unit.State LIKE '%' + ? + '%'";
      $parameters[] = $_GET[ 'search' ];

      $search[] = " Unit.Unit LIKE '%' + ? + '%'";
      $parameters[] = $_GET[ 'search' ];

      $search[] = "Customer.Name LIKE '%' + ? + '%'";
      $parameters[] = $_GET[ 'search' ];

      $search[] = "Location.Tag LIKE '%' + ? + '%'";
      $parameters[] = $_GET[ 'search' ];

      $search[] = "Unit.Type LIKE '%' + ? + '%'";
      $parameters[] = $_GET[ 'search' ];
    }
      $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
      $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
    //    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
      //  $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;
        $parameters[] = $_GET[ 'search' ];
        $Columns = array(
      0 =>  'Unit.ID',
      1 =>  'Unit.State',
      2 =>  'Loc.Tag',
      3 =>  'Unit.Unit',
      4 =>  'Unit.Type',
      5 =>  'Unit.Status'
    );
        $Direction = 'ASC';
        $Order = isset( $_GET['order']['column'] ) ? $Columns[ $_GET['order']['column'] ] : "[Unit].[ID]";
        if(isset($_GET['order']['dir'])){
            $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
                ? $_GET['order']['dir']
                : 'ASC';
        }
      $sQuery = " SELECT 		Top 10
                tbl.ID,
                tbl.FieldName,
                tbl.FieldValue
          FROM 		(
                SELECT
                    insRowTbl.ID,
                    attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
                    attr.insRow.value('.', 'nvarchar(max)') as FieldValue
                FROM ( Select i.ID, convert(xml, (select i.* for xml raw)) as insRowCol
                       FROM ( (
                         SELECT Top 100
                               Unit.ID AS ID,
                               Unit.State AS Name
                       FROM    Elev As Unit
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
        ) ) as i
        ) as insRowTbl
      CROSS APPLY insRowTbl.insRowCol.nodes('/row/@*') as attr(insRow)
      ) AS tbl
      WHERE 		tbl.FieldValue LIKE '%' + ? + '%'
      GROUP BY 	tbl.ID, tbl.FieldName, tbl.FieldValue;";
//    echo $sQuery;
  //  var_dump( $parameters );
        $rResult = $database->query(
      null,
      $sQuery,
      $parameters
    ) or die(print_r(sqlsrv_errors()));
    $output = array( );
      while ( $Row = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
        $output[]   		= $Row;
      }
      echo json_encode( $output );
}}
?>
