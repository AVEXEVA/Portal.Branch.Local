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
        FROM        [Connection]
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
		  FROM          [Privilege]
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
        ||  !isset( $Privileges[ 'Contact' ] )
        || 	!check( privilege_read, level_group, $Privileges[ 'Contact' ] )
    ){ ?><?php require('404.html');?><?php }
	else {
			\singleton\database::getInstance( )->query(
        null,
        " INSERT INTO Activity([User], [Date], [Page] )
          VALUES( ?, ?, ? );",
        array(
          $_SESSION[ 'Connection' ][ 'User' ],
          date('Y-m-d H:i:s'),
          'get/contacts.php'
        )
      );
      $conditions = array( );
      $search = array( );
      $parameters = array( );

	


	    if( isset( $_GET[ 'ID' ] ) && !in_array(  $_GET[ 'ID' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['ID'];
	      $conditions[] = "Contact.ID LIKE '%' + ? + '%'";
	    }
      if( isset( $_GET[ 'Contact' ] ) && !in_array(  $_GET[ 'Contact' ], array( '', ' ', null ) ) ){
      $parameters[] = $_GET['Contact'];
      $conditions[] = "Contact.Contact LIKE '%' + ? + '%'";
      }
	    if( isset( $_GET[ 'Type' ] ) && !in_array(  $_GET[ 'Type' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Type'];
	      $conditions[] = "Contact.Type LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Name' ] ) && !in_array(  $_GET[ 'Name' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Name'];
	      $conditions[] = "Contact.Name LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Position' ] ) && !in_array( $_GET[ 'Position' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Position'];
	      $conditions[] = "Contact.Position LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Phone' ] ) && !in_array( $_GET[ 'Phone' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Phone'];
	      $conditions[] = "Contact.Phone LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Email' ] ) && !in_array( $_GET[ 'Email' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Email'];
	      $conditions[] = "Contact.Email LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Address' ] ) && !in_array( $_GET[ 'Address' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Address'];
	      $conditions[] = "Contact.Address + ' ' + Contact.City + ' ' + Contact.State + ' ' + Contact.Zip LIKE '%' + ? + '%'";
	    }

		/*Search Filters*/
		//if( isset( $_GET[ 'search' ] ) ){ }


		/*Concatenate Filters*/
		$conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    	$search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

		/*ROW NUMBER*/
		$parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] -25 : 0;
		$parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 25 : 0;

		/*Order && Direction*/
		//update columns from bin/js/tickets/table.js
		$Columns = array(
			0 =>  'Contact.ID',
			1 =>  'Contact.Contact',
      2 =>  "Contact.Type",
      3 =>  "Contact.Name",
			4 =>  "Contact.Position",
			5 =>  "Contact.[Phone]",
			6 =>  "Contact.Email",
			7 =>  "Contact.Address + ' ' + Contact.City + ' ' + Contact.State + ' ' + Contact.Zip"
	    );
	    $Order = isset( $Columns[ $_GET['order']['column'] ] )
	        ? $Columns[ $_GET['order']['column'] ]
	        : "Contact.ID";
	    $Direction = in_array( $_GET['order']['dir'], array( 'asc', 'desc', 'ASC', 'DESC' ) )
	      ? $_GET['order']['dir']
	      : 'ASC';

		/*Perform Query*/
		$Query = "SELECT 	*
			FROM 	(
				SELECT 	ROW_NUMBER() OVER (ORDER BY {$Order} {$Direction}) AS ROW_COUNT,
						Contact.ID 						AS ID,
						Contact.Name 					AS Name,
						Contact.Contact 				AS Contact,
						Contact.Position  		  		AS Position,
						Contact.Phone 					AS Phone,
						Contact.Email 					AS Email,
						Contact.Address 				AS Street,
						Contact.City  					AS City,
						Contact.State 					AS State,
						Contact.Zip 					AS Zip,
						CASE 	WHEN Contact.[Type] = 0 THEN 	'Customer'
								WHEN Contact.[Type] = 4 THEN  'Location'
								WHEN Contact.[Type] = 5 THEN  'Employee'
								ELSE 'Unknown'
						END 	AS [Type]
				FROM 	Rol AS Contact
				WHERE 	({$conditions}) AND ({$search})
			) AS Tbl
			WHERE 		Tbl.ROW_COUNT >= ?
					AND Tbl.ROW_COUNT <= ?;";
		$rResult = \singleton\database::getInstance( )->query(
			null,
			$Query,
			$parameters
		) or die(print_r(sqlsrv_errors()));

		$sQueryRow = "SELECT  [Contact].[ID]
                  FROM    Rol AS Contact
                  WHERE   ({$conditions}) AND ({$search});";

        $fResult = \singleton\database::getInstance( )->query(
            null,
            $sQueryRow ,
            $parameters
        ) or die(print_r(sqlsrv_errors()));

        $iFilteredTotal = 0;
        $_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) ? $_SESSION[ 'Tables' ] : array( );
        $_SESSION[ 'Tables' ][ 'Contacts' ] = isset( $_SESSION[ 'Tables' ][ 'Contacts' ]  ) ? $_SESSION[ 'Tables' ][ 'Contacts' ] : array( );
        if( count( $_SESSION[ 'Tables' ][ 'Contacts' ] ) > 0 ){ foreach( $_SESSION[ 'Tables' ][ 'Contacts' ] as &$Value ){ $Value = false; } }
        $_SESSION[ 'Tables' ][ 'Contacts' ][ 0 ] = $_GET;
        while( $Row = sqlsrv_fetch_array( $fResult ) ){
            $_SESSION[ 'Tables' ][ 'Contacts' ][ $Row[ 'ID' ] ] = true;
            $iFilteredTotal++;
        }

		while ( $Ticket = sqlsrv_fetch_array( $rResult, SQLSRV_FETCH_ASSOC ) ){
	      $output[ 'aaData' ][]   		= $Ticket;
	    }

		$sQueryRow = "	SELECT 	Count( Contact.ID ) AS Count
						FROM 	Rol AS Contact
						WHERE 	({$conditions}) AND ({$search})";

	    $stmt = \singleton\database::getInstance( )->query(
	    	null,
	    	$sQueryRow,
	    	$parameters
	    ) or die(print_r(sqlsrv_errors()));

	    $iFilteredTotal = sqlsrv_fetch_array( $stmt )[ 'Count' ];
	    sqlsrv_cancel( $stmt );

	    $sQuery = " SELECT  COUNT(Contact.ID)
	                FROM    Rol AS Contact;";
	    $rResultTotal = \singleton\database::getInstance( )->query(
	    	null,
	    	$sQuery,
	    	array( )
	    ) or die(print_r(sqlsrv_errors()));
	    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
	    $iTotal = $aResultTotal[0];

	    $output[ 'iTotalRecords' ] = $iTotal;
	    $output[ 'iTotalDisplayRecords' ] = $iFilteredTotal;
	    echo json_encode( $output );
  	}
}?>
