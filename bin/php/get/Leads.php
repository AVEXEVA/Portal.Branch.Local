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
    if( 	!isset( $Connection[ 'ID' ] )
        ||  !isset( $Privileges[ 'Customer' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Customer' ] )
    ){ ?><?php require('404.html');?><?php }
    else {
    	$conditions = array( );
	    $search = array( );
	    $parameters = array( );

        if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['ID'];
			$conditions[] = "Lead.ID LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Customer'];
			$conditions[] = "Customer.Name LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'Type' ] ) && !in_array( $_GET[ 'Type' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Type'];
			$conditions[] = "Lead.Type LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'Description' ] ) && !in_array( $_GET[ 'Description' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Description'];
			$conditions[] = "Lead.fDesc LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'Street' ] ) && !in_array( $_GET[ 'Street' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Street'];
			$conditions[] = "Lead.Address LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'City' ] ) && !in_array( $_GET[ 'City' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['City'];
			$conditions[] = "Lead.City LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'State' ] ) && !in_array( $_GET[ 'State' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['State'];
			$conditions[] = "Lead.State LIKE '%' + ? + '%'";
		}
        if( isset($_GET[ 'Zip' ] ) && !in_array( $_GET[ 'Zip' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Zip'];
			$conditions[] = "Lead.Zip LIKE '%' + ? + '%'";
		}

		$conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
	    $search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );
	    $parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] : 0;
	    $parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 10 : 25;

	    $Columns = array(
	      0 =>  'Lead.ID',
	      1 =>  'Lead.fDesc',
	      2 =>  'Customer_Name',
	      3 =>  'Lead.Type',
	      4 =>  'Lead.Address',
	      5 =>  'Lead.City',
	      6 =>  'Lead.State',
	      7 =>  'Lead.Zip',
	    );
	    $Order = isset( $Columns[ $_GET['order']['column'] ] )
	        ? $Columns[ $_GET['order']['column'] ]
	        : "Lead.ID";
	    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
	      ? $_GET['order']['dir']
	      : 'ASC';

        $sQuery = "	SELECT *
		            FROM 	(
			                 	SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
			            	            Lead.ID           AS ID,
								        Lead.fDesc        AS Name,
								        Customer.ID       AS Customer_ID,
								        Customer.Name 	  AS Customer_Name,
								        Lead.Type 		  AS Type,
								        Lead.Address      AS Street,
								        Lead.City         AS City,
								        Lead.State        AS State,
								        Lead.Zip          AS Zip
			                  	FROM    Lead
			                  			LEFT JOIN (
					                        SELECT  Owner.ID,
					                                Rol.Name,
					                                Owner.Status 
					                        FROM    Owner 
					                                LEFT JOIN Rol ON Owner.Rol = Rol.ID
					                    ) AS Customer ON Lead.Owner = Customer.ID
			                  	WHERE   ({$conditions}) AND ({$search})
		             		) AS Tbl
		            WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;";
		$rResult = \singleton\database::getInstance( )->query(
	      null,
	      $sQuery,
	      $parameters
	    ) or die(print_r(sqlsrv_errors()));

	    $sQueryRow = "	SELECT  ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
	                            Lead.ID           AS ID,
						        Lead.fDesc        AS Name,
						        Customer.ID       AS Customer_ID,
						        Customer.Name 	  AS Customer_Name,
						        Lead.Type 		  AS Type,
						        Lead.Address      AS Street,
						        Lead.City         AS City,
						        Lead.State        AS State,
						        Lead.Zip          AS Zip
	                  	FROM    Lead
	                  			LEFT JOIN (
			                        SELECT  Owner.ID,
			                                Rol.Name,
			                                Owner.Status 
			                        FROM    Owner 
			                                LEFT JOIN Rol ON Owner.Rol = Rol.ID
			                    ) AS Customer ON Lead.Owner = Customer.ID
	                  WHERE   ({$conditions}) AND ({$search});";
	    $fResult = \singleton\database::getInstance( )->query( null, $sQueryRow , $parameters ) or die(print_r(sqlsrv_errors()));

	    $iFilteredTotal = 0;
	    $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
      	$_SESSION[ 'Tables' ][ 'Leads' ] = isset( $_SESSION[ 'Tables' ][ 'Leads' ]  ) ? $_SESSION[ 'Tables' ][ 'Leads' ] : array( );
	    if( count( $_SESSION[ 'Tables' ][ 'Leads' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Leads' ] as &$Value ){ $Value = false; } }
	    $_SESSION[ 'Tables' ][ 'Leads' ][ 0 ] = $_GET;
	    while( $Row = sqlsrv_fetch_array( $fResult ) ){
	        $_SESSION[ 'Tables' ][ 'Leads' ][ $Row[ 'ID' ] ] = true;
	        $iFilteredTotal++;
	    }

	    $parameters = array( );
	    $sQuery = " SELECT  COUNT( Lead.ID )
	                FROM    Lead;";
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
