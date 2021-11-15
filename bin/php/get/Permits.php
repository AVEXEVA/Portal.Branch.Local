<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $User    = \singleton\database::getInstance( )->query(
        null,
      " SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?;",
    array($_SESSION['User']));
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Permit'])
        && (
			 $Privileges['Permit']['User_Privilege'] >= 4
    && $Privileges['Permit']['Group_Privilege'] >= 4
    && $Privileges['Permit']['Other_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
		$data = array();
		$r = \singleton\database::getInstance( )->query(
        null,
    "  SELECT  Permit.ID          AS ID,
    				   Permit.Name        AS Name,
    				   Permit.Type        AS Type,
    				   Permit.Description AS Description,
    				   Permit.Expiration  AS Expiration,
    				   Permit.Link        AS Link,
    				   Loc.Tag            AS Location,
    				   Loc.Loc            AS Location_ID
			 FROM    Permit
				   LEFT JOIN nei.dbo.Loc ON Permit.Location = Loc.Loc
		;",array($_GET['ID']));
		if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
		print json_encode(array('data'=>$data));
    }
}?>
