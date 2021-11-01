<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
	//Connection
    $result = sqlsrv_query(
    	$NEI, 
    	"	SELECT 	* 
    		FROM 	Connection 
    		WHERE 		Connector = ? 
    				AND Hash = ?;",
    	array(
    		$_SESSION['User'],
    		$_SESSION['Hash']
    	)
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = sqlsrv_query(
		$NEI,
		"	SELECT 	*, 
					fFirst AS First_Name, 
					Last as Last_Name 
			FROM 	Emp 
			WHERE 	ID= ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$result = sqlsrv_query($NEI,
		" 	SELECT 	Privilege.*
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$Privileges = array();
	$Privileged = false;
	while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
	if(		isset($Privileges['Customer']) 
		&& 	$Privileges[ 'Customer' ][ 'User_Privilege' ]  >= 4 
		&& 	$Privileges[ 'Customer' ][ 'Group_Privilege' ] >= 4 
		&& 	$Privileges[ 'Customer' ][ 'Other_Privilege' ] >= 4){
				$Privileged = true;}
    if(		!isset($Connection['ID'])  
    	|| 	!is_numeric($_GET['ID']) 
    	|| !$Privileged 
    ){ print json_encode( array( 'data' => array( ) ) ); }
    else {
		$r = sqlsrv_query($NEI,"
			SELECT Elev.ID     AS ID,
				   Elev.State  AS State, 
				   Elev.Unit   AS Unit,
				   Elev.Type   AS Type,
				   Loc.Tag     AS Location,
				   Elev.Status AS Status,
				   Elev.fDesc  AS Description
			FROM   Elev
				   LEFT JOIN Loc ON Loc.Loc = Elev.Loc
			WHERE  Loc.Owner = ?
		;",array($_GET['ID']));
		$data = array();
		if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
			$Unit = $array;
			$r2 = sqlsrv_query($NEI,"
				SELECT *
				FROM   ElevTItem
				WHERE  ElevTItem.ElevT    = 1
					   AND ElevTItem.Elev = ?
			;",array($Unit['ID']));
			if($r2){while($array2 = sqlsrv_fetch_array($r2,SQLSRV_FETCH_ASSOC)){$Unit[$array2['fDesc']] = $array2['Value'];}}
			$r3 = sqlsrv_query($NEI,"
				SELECT *
				FROM   ElevTItem
				WHERE  ElevTItem.ElevT    = 1
					   AND ElevTItem.Elev = ?
			;",array(0));
			if($r3){while($array3 = sqlsrv_fetch_array($r3,SQLSRV_FETCH_ASSOC)){if(!isset($Unit[$array3['fDesc']])){$Unit[$array3['fDesc']] = '';}}}
			$data[] = $Unit;
		}}
		print json_encode(array('data'=>$data));   	
	}
}