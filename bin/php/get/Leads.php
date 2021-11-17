<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [
		'read_and_close' => true
	] );
   	require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
	//Connection
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?;",
    array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    //User
    $User    = $database->query(
    	null,
		" 	SELECT 	Emp.*,
		       		Emp.fFirst AS First_Name,
		       		Emp.Last   AS Last_Name
			FROM   	Emp
			WHERE  	Emp.ID = ?;", 
		array(
			$_SESSION[ 'User' ] 
		) 
	);
    $User = sqlsrv_fetch_array($User);
    //Privileges
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT Privilege.Access,
               Privilege.Owner,
               Privilege.Group,
               Privilege.Other
        FROM   Privilege
        WHERE  Privilege.User_ID = ?;",
      array($_SESSION[ 'User' ] ) );
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2[ 'Access' ]] = $array2;}
    $Privileged = False;
    if( isset($Privileges[ 'Lead' ])
        && (
				$Privileges[ 'Lead' ][ 'Other' ] >= 4
		)
	 ){
            $Privileged = True;}
    if(		!isset($Connection['ID']) 
    	|| 	!$Privileged){
    			print json_encode(array('data'=>array()));
    } else {
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

        $result = \singleton\database::getInstance( )->query(
            null,
          	"SELECT *
             FROM (
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
             WHERE Tbl.ROW_COUNT BETWEEN ? AND ?;",
      		$parameters
      	);
		$output = array(
	        'sEcho'         =>  intval($_GET['sEcho']),
	        'iTotalRecords'     =>  $iTotal,
	        'iTotalDisplayRecords'  =>  $iFilteredTotal,
	        'aaData'        =>  array()
	    );

	    while ( $Row = sqlsrv_fetch_array( $result ) ){
	      $output['aaData'][]   = $Row;
	    }
	    echo json_encode( $output );
	}
}?>
