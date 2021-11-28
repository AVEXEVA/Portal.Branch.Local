<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'Connection' ][ 'User' ], $_SESSION[ 'Connection' ][ 'Hash' ] ) ){
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
    $User = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  Emp.*,
                    Emp.fFirst AS First_Name,
                    Emp.Last   AS Last_Name
            FROM    Emp
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION[ 'Connection' ][ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $User );
    $r = \singleton\database::getInstance( )->query(
        null,
        "   SELECT  Privilege.Access,
                    Privilege.Owner,
                    Privilege.Group,
                    Privilege.Other
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'Connection' ][ 'User' ]
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
        ||  !isset( $Privileges[ 'Route' ] )
        ||  !check( privilege_read, level_group, $Privileges[ 'Route' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
        $conditions = array( );
        $search = array( );
        $parameters = array( );
    /*Search Filters*/
    if( isset( $_GET[ 'search' ] ) ){

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

    /*Concatenate Filters*/
    $conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

    /*ROW NUMBER*/
    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] -25 : 0;
    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 25 : 0;

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

      $sQuery = " SELECT 		Top 10
                tbl.FieldName,
                tbl.FieldValue
          FROM 		(

                SELECT
                    attr.insRow.value('local-name(.)', 'nvarchar(128)') as FieldName,
                    attr.insRow.value('.', 'nvarchar(max)') as FieldValue
                FROM ( Select
                          convert(xml, (select i.* for xml raw)) as insRowCol
                       FROM (

                     ( SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
                             Unit.ID AS ID,
                             Unit.State + ' - ' + Unit.Unit AS Name,
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
        ) ) as i
        ) as insRowTbl
      CROSS APPLY insRowTbl.insRowCol.nodes('/row/@*') as attr(insRow)
      ) AS tbl
      WHERE 		tbl.FieldValue LIKE '%' + ? + '%'
      GROUP BY 	tbl.FieldName, tbl.FieldValue;";
    //echo $sQuery;
    //var_dump( $parameters );
    $rResult = \singleton\databse::getInstance()->query(
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
