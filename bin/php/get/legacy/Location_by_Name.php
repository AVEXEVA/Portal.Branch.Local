<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
        SELECT * 
        FROM   Connection 
        WHERE  Connection.Connector = ? 
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = $database->query(null,"
        SELECT Emp.*, 
               Emp.fFirst AS First_Name, 
               Emp.Last   AS Last_Name 
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query($Portal,"
        SELECT Privilege.Access_Table, 
               Privilege.User_Privilege, 
               Privilege.Group_Privilege, 
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Location']) 
        && (
				$My_Privileges['Location']['Other_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
		$data = array();
        $r = $database->query(null,"
            SELECT Loc.Loc     AS ID,
                   Loc.ID      AS Name,
                   Loc.Tag     AS Tag,
                   Loc.Address AS Street,
                   Loc.City    AS City,
                   Loc.State   AS State,
                   Loc.Zip     AS Zip,
				   Loc.fLong   AS Longitude,
				   Loc.Latt    AS Latitude,
				   Loc.Maint   AS Maintenance,
                   Zone.Name   AS Division,
				   Route.Name  AS Route,
				   Terr.Name   AS Territory
            FROM   nei.dbo.Loc
			       LEFT JOIN nei.dbo.Zone  ON Zone.ID  = Loc.Zone
				   LEFT JOIN nei.dbo.Route ON Route.ID = Loc.Route
				   LEFT JOIN nei.dbo.Terr  ON Terr.ID  = Loc.Terr
            WHERE  Loc.Tag = ?
        ;",array($_GET['Name']));
		if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));  }
}?>