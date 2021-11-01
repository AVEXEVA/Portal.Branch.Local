<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if( isset($_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $Connection = sqlsrv_query(
    	$NEI,
    	"	SELECT *
			FROM   Connection
			WHERE  Connection.Connector = ?
				   AND Connection.Hash = ?;", 
		array(
			$_SESSION[ 'User' ],
			$_SESSION[ 'Hash' ]
		)
	);
    $Connection = sqlsrv_fetch_array($Connection);
	$User    = sqlsrv_query(
		$NEI,
		"	SELECT 	Emp.*,
			   		Emp.fFirst AS First_Name,
			   		Emp.Last   AS Last_Name
			FROM   	Emp
			WHERE  	Emp.ID = ?
	;", array($_SESSION['User']));
	$User = sqlsrv_fetch_array( $User );
	$r = sqlsrv_query(
		$NEI,
		"	SELECT Privilege.Access_Table,
				   Privilege.User_Privilege,
				   Privilege.Group_Privilege,
				   Privilege.Other_Privilege
			FROM   Privilege
			WHERE  Privilege.User_ID = ?;", 
		array(
			$_SESSION['User']
		)
	);
	$Privileges = array( );
	while($Privilege = sqlsrv_fetch_array( $r ) ){$Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege;}
	$Privileged = False;
	if( isset( $Privileges[ 'Location' ], $Privileges[ 'Unit' ] )
	   	&& $Privileges[ 'Unit' ][ 'Other_Privilege' ] >= 4
	  	&& $Privileges[ 'Location' ][ 'Other_Privilege' ] >= 4){
			$Privileged = True; }
	elseif( isset($Privileges[ 'Location' ], $Privileges[ 'Unit' ] )
		&& $Privileges[ 'Unit' ][ 'Group_Privilege' ] >= 4
		&& $Privileges[ 'Location' ][ 'Group_Privilege' ] >= 4
		&& is_numeric( $_GET[ 'Location' ] ) ){
			$Location_ID = $_GET[ 'Location' ];
			$r = sqlsrv_query(
				$NEI,
				"	SELECT Tickets.ID
					FROM
					(
						(
							SELECT TicketO.ID
							FROM   TicketO
							WHERE  TicketO.LID       = ?
							       AND TicketO.fWork = ?
						)
						UNION ALL
						(
							SELECT TicketD.ID
							FROM   TicketD
							WHERE  TicketD.Loc       = ?
							       AND TicketD.fWork = ?
						)
					) AS Tickets;", 
				array(
					$Location_ID, 
					$User[ 'fWork' ],
					$Location_ID, 
					$User[ 'fWork' ],
					$Location_ID, 
					$User[ 'fWork' ] 
				) 
			);
			$Privileged = is_array(sqlsrv_fetch_array($r)) ? True : False;}
	elseif(isset($Privileges['Location'], $Privileges['Unit'])
		&& $Privileges['Unit']['User_Privilege'] >= 4
		&& $Privileges['Location']['User_Privilege'] >= 4
		&& is_numeric($_GET['ID'])){
			$Location_ID = $_GET['ID'];
			$r = sqlsrv_query($NEI,"
				SELECT Tickets.ID
				FROM
				(
					(
						SELECT TicketO.ID
						FROM   TicketO
						WHERE  TicketO.LID       = ?
						       AND TicketO.fWork = ?
					)
					UNION ALL
					(
						SELECT TicketD.ID
						FROM   TicketD
						WHERE  TicketD.Loc       = ?
						       AND TicketD.fWork = ?
					)
				) AS Tickets
			;", array(
				$_GET[ 'Location' ], 
				$User[ 'fWork' ],
				$_GET[ 'Location' ], 
				$User[ 'fWork' ],
				$_GET[ 'Location' ], 
				$User[ 'fWork' ]
			)
		);
			var_dump( sqlsrv_errors( ) );
		$Privileged = is_array( sqlsrv_fetch_array( $r ) ) ? True : False; 
	}
  	if( !isset($Connection['ID'])  || !is_numeric( $_GET[ 'Location' ] ) || !$Privileged ){
    	?><html><head><script>document.location.href="../login.php?Forward=forbidden.php";?>";</script></head></html><?php }
  	else {
		$data = array();
		$r = sqlsrv_query($NEI,
			"	SELECT 	Elev.ID     AS ID,
					   	Elev.State  AS State,
					   	Elev.Unit   AS Unit,
					   	Elev.Type   AS Type,
					   	Loc.Tag     AS Location,
					   	Elev.Status AS Status,
					   	Elev.fDesc  AS Description,
					   	Elev.Building AS Building
				FROM   	Elev
				   		LEFT JOIN Loc ON Loc.Loc = Elev.Loc
				WHERE  	Loc.Loc = ?;",
				array(
					$_GET[ 'Location' ] 
				) 
			);
		$data = array();
		if( $r ){ while( $array = sqlsrv_fetch_array( $r, SQLSRV_FETCH_ASSOC ) ){
			$Unit = $array;
			$r2 = sqlsrv_query(
				$NEI,
				"	SELECT 	*
					FROM   	ElevTItem
					WHERE  	ElevTItem.ElevT    = 1
					   AND 	ElevTItem.Elev = ?;",
				array(
					$Unit['ID']
				)
			);
			if( $r2 ){ while( $array2 = sqlsrv_fetch_array( $r2, SQLSRV_FETCH_ASSOC ) ){ $Unit[ $array2[ 'fDesc' ] ] = $array2[ 'Value' ]; } }
			$r3 = sqlsrv_query(
				$NEI,
				"	SELECT *
					FROM   ElevTItem
					WHERE  ElevTItem.ElevT    = 1
					   AND ElevTItem.Elev = ?;",
				array( 0 ) 
			);
			if( $r3 ){ while( $array3 = sqlsrv_fetch_array( $r3, SQLSRV_FETCH_ASSOC ) ){
				if( !isset( $Unit[ $array3[ 'fDesc' ] ] ) ){ $Unit[ $array3[ 'fDesc' ] ] = ''; } 
			} }
			$data[] = $Unit;
		}}
		print json_encode(array('data'=>$data));
  }
}?>
