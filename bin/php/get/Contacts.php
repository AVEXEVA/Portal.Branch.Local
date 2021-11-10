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
    	"	SELECT 	Top 1
    				*
			FROM   	Connection
			WHERE  		Connection.Connector 	= ?
				   	AND Connection.Hash 		= ?;",
		array(
			$_SESSION['User'],
			$_SESSION['Hash']
		)
	);
    $Connection = sqlsrv_fetch_array($Connection);
	$User    = \singleton\database::getInstance( )->query(
		null,
		"	SELECT 	Top 1
					Emp.*,
				   	Emp.fFirst AS First_Name,
			   		Emp.Last   AS Last_Name
			FROM   	Emp
			WHERE  	Emp.ID = ?;",
		array(
			$_SESSION['User']
		)
	);
	$User = sqlsrv_fetch_array( $User );
	$r = \singleton\database::getInstance( )->query(
		null,
		"	SELECT 	Privilege.Access_Table,
				   	Privilege.User_Privilege,
			   		Privilege.Group_Privilege,
			   		Privilege.Other_Privilege
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;",
		array(
			$_SESSION['User']
		)
	);
	$Privileges = array();
	while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
	$Privileged = False;
	if( isset($Privileges['Contact'])
        && (
				$Privileges['Contact']['User_Privilege'] >= 4
			||	$Privileges['Contact']['Group_Privilege'] >= 4
			||	$Privileges['Contact']['Other_Privilege'] >= 4)){
            		$Privileged = True;
    }
    if( !isset( $Connection[ 'ID' ] ) || !$Privileged ){ print json_encode( array( 'data' => array( ) ) );}
	else {
		$output = array(
	        'sEcho'         		=> isset( $_GET[ 'draw' ] ) ? intval( $_GET[ 'draw' ] ) : 1,
	        'iTotalRecords'     	=>  0,
	        'iTotalDisplayRecords'  =>  0,
	        'aaData'        		=>  array(),
	        'options' 				=> array( )
	    );

		/*Parse GET*/
		/*None*/

		$conditions = array( );
		$search 	= array( );

		/*Default Filters*/
		/*NONE*/


	    if( isset( $_GET[ 'ID' ] ) && !in_array(  $_GET[ 'ID' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['ID'];
	      $conditions[] = "Contact.ID LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Type' ] ) && !in_array(  $_GET[ 'Type' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Type'];
	      $conditions[] = "Contact.Type LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Entity' ] ) && !in_array(  $_GET[ 'Entity' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Entity'];
	      $conditions[] = "Contact.Name LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Person'];
	      $conditions[] = "Contact.Contact LIKE '%' + ? + '%'";
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
	    if( isset( $_GET[ 'Street' ] ) && !in_array( $_GET[ 'Street' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Street'];
	      $conditions[] = "Contact.Street LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'City' ] ) && !in_array( $_GET[ 'City' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['City'];
	      $conditions[] = "Contact.City LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'State' ] ) && !in_array( $_GET[ 'State' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['State'];
	      $conditions[] = "Contact.State LIKE '%' + ? + '%'";
	    }
	    if( isset( $_GET[ 'Zip' ] ) && !in_array( $_GET[ 'Zip' ], array( '', ' ', null ) ) ){
	      $parameters[] = $_GET['Zip'];
	      $conditions[] = "Contact.Zip LIKE '%' + ? + '%'";
	    }

		/*Search Filters*/
		//if( isset( $_GET[ 'search' ] ) ){ }


		/*Concatenate Filters*/
		$conditions = $conditions == array( ) ? "NULL IS NULL" : implode( ' AND ', $conditions );
    	$search     = $search     == array( ) ? "NULL IS NULL" : implode( ' OR ', $search );

		/*ROW NUMBER*/
<<<<<<< HEAD
		$parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] - 25 : 0;
		$parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 25 : 25;
=======
		$parameters[] = isset( $_GET[ 'start' ] ) && is_numeric( $_GET[ 'start' ] ) ? $_GET[ 'start' ] -25 : 0;
		$parameters[] = isset( $_GET[ 'length' ] ) && is_numeric( $_GET[ 'length' ] ) && $_GET[ 'length' ] != -1 ? $_GET[ 'start' ] + $_GET[ 'length' ] + 25 : 0;
>>>>>>> 46bac5b11075b4003a1c0fe8b63c5782c74e39bb

		/*Order && Direction*/
		//update columns from bin/js/tickets/table.js
		$Columns = array(
			0 =>  'Contact.ID',
			1 =>  'Contact.Contact',
			2 =>  "Contact.Position",
			2 =>  "Contact.Phone",
			2 =>  "Contact.Email",
			2 =>  "Contact.Street",
			2 =>  "Contact.City",
			2 =>  "Contact.State",
			2 =>  "Contact.Zip"
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
						Contact.Name 					AS Entity,
						Contact.Contact 				AS Name,
						Contact.Position  				AS Position,
						Contact.Phone 					AS Phone,
						Contact.Email 					AS Email,
						Contact.Address 				AS Street,
						Contact.City  					AS City,
						Contact.State 					AS State,
						Contact.Zip 					AS Zip,
						CASE 	WHEN Contact.[Type] = 0 THEN 	'Customer'
								WHEN Contact.[Type] = 4 THEN  'Location'
								WHEN Contact.[Type] = 5 THEN  'User'
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
	    	array( $User[ 'ID' ] )
	    ) or die(print_r(sqlsrv_errors()));
	    $aResultTotal = sqlsrv_fetch_array($rResultTotal);
	    $iTotal = $aResultTotal[0];

	    $output[ 'iTotalRecords' ] = $iTotal;
	    $output[ 'iTotalDisplayRecords' ] = $iFilteredTotal;
	    echo json_encode( $output );
  	}
}?>
