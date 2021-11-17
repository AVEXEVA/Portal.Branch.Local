<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && "OFFICE" != $User['Title']) ? True : False;
    $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$My_Privileges[$array2['Access']] = $array2;}
    $Privileged = false;
    if(isset($My_Privileges['Admin']) && $My_Privileges['Admin']['Owner'] >= 4){$Privileged = true;}
    if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
       $r = $database->query(null,"
			SELECT   Loc.Loc    AS Location_ID,
					 Loc.Tag    AS Location_Name,
					 Route.ID   AS Route_ID,
					 Route.Name AS Route_Name,
					 Zone.Name  AS Division
			FROM     nei.dbo.Loc 
					 LEFT JOIN nei.dbo.Route ON Loc.Route = Route.ID
					 LEFT JOIN nei.dbo.Zone  ON Loc.Zone  = Zone.ID
			WHERE    Loc.Owner = ?
			GROUP BY Loc.Loc,
					 Loc.Tag,
					 Route.ID,
					 Route.Name,
					 Zone.Name
		;",array($_GET['ID']));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}?>