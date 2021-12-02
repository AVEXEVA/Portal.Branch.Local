<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
	//Connection
    $result = $database->query(
    	null, 
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
	$result = $database->query(
		null,
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
	$result = $database->query(null,
		" 	SELECT 	Privilege.*
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$Privileges = array();
	$Privileged = false;
	while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access' ] ] = $Privilege; }
	if(		isset($Privileges['Customer']) 
		&& 	$Privileges[ 'Customer' ][ 'Owner' ]  >= 4 
		&& 	$Privileges[ 'Customer' ][ 'Group' ] >= 4 
		&& 	$Privileges[ 'Customer' ][ 'Other' ] >= 4){
				$Privileged = true;}
    if(		!isset($Connection['ID'])  
    	|| 	!is_numeric($_GET['ID']) 
    	|| !$Privileged 
    ){ print json_encode( array( 'data' => array( ) ) ); }
    else {
		$r = $database->query(null,"
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
			$r2 = $database->query(null,"
				SELECT *
				FROM   ElevTItem
				WHERE  ElevTItem.ElevT    = 1
					   AND ElevTItem.Elev = ?
			;",array($Unit['ID']));
			if($r2){while($array2 = sqlsrv_fetch_array($r2,SQLSRV_FETCH_ASSOC)){$Unit[$array2['fDesc']] = $array2['Value'];}}
			$r3 = $database->query(null,"
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