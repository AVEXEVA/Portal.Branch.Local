<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = $database->query(null,"
		SELECT * 
		FROM   Privilege 
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[] = $My_Privilege;}}
    if(!isset($My_Connection['ID']) ){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = $database->query(null,"
            SELECT Loc.Tag AS Location,
			       OwnerWithRol.Contact AS Contact,
				   OwnerWithRol.Name    AS Customer,
				   Count(Elev.ID)       AS Units,
				   Zone.Name            AS Division
			FROM   nei.dbo.Loc
			       LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner = OwnerWithRol.ID
				   LEFT JOIN nei.dbo.Elev         ON Elev.Loc  = Loc.Loc
				   LEFT JOIN nei.dbo.Zone         ON Zone.ID   = Loc.Zone
			WHERE  Loc.Custom4 = 'SALES 1'
			GROUP BY Loc.Tag, OwnerWithRol.Contact, OwnerWithRol.Name, Zone.Name
		;");
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
			$data[] = $array;
		}}
        print json_encode(array('data'=>$data));   
	}
}?>