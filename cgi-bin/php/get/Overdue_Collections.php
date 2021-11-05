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
		$now = date("Y-m-d H:i:s");
        $r = $database->query(null,"
            SELECT OpenAR.Ref        AS  Invoice,
                   OpenAR.fDate      AS  Dated,
                   OpenAR.Due        AS  Due,
                   OpenAR.fDesc      AS  Description,
                   OpenAR.Original   AS  Original,
                   OpenAR.Balance    AS  Balance,
                   OwnerWithRol.Name AS  Customer,
                   Loc.Tag           AS  Location
            FROM   nei.dbo.OpenAR
                   LEFT JOIN nei.dbo.Loc 		  ON OpenAR.Loc = Loc.Loc
                   LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner 	= OwnerWithRol.ID
			WHERE  OpenAR.Due <= ?
			ORDER BY OpenAR.Due ASC
        ;",array($now));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   
	}
}?>