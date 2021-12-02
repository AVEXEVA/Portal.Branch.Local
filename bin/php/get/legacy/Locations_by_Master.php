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
        SELECT Privilege.Access, 
               Privilege.Owner, 
               Privilege.Group, 
               Privilege.Other
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Location'], $My_Privileges['Customer']) 
        && (
				$My_Privileges['Location']['Other'] >= 4
			||	$My_Privileges['Customer']['Other'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
		$data = array();
        $r = $database->query(null,"
            SELECT Loc.Loc      AS ID,
                   Loc.ID       AS Name,
                   Loc.Tag      AS Tag,
                   Loc.Address  AS Street,
                   Loc.City     AS City,
                   Loc.State    AS State,
                   Loc.Zip      AS Zip,
                   Route.Name   AS Route,
                   Zone.Name    AS Division,
                   Loc.Maint    AS Maintenance,
				   Terr.Name    AS Territory,
				   Loc.sTax     AS Sales_Tax,
				   Rol.Contact  AS Contact_Name,
				   Rol.Phone    AS Contact_Phone,
				   Rol.Fax      AS Contact_Fax,
				   Rol.Cellular AS Contact_Cellular,
				   Rol.Email    AS Contact_Email,
				   Rol.Website  AS Contact_Website,
				   Loc.fLong    AS Longitude,
				   Loc.Latt     AS Latitude,
				   Loc.Custom1  AS Collector
				   
            FROM   nei.dbo.Loc
			       LEFT JOIN nei.dbo.Zone ON Zone.ID = Loc.Zone
				   LEFT JOIN nei.dbo.Route ON Route.ID = Loc.Route
				   LEFT JOIN nei.dbo.Terr ON Terr.ID = Loc.Terr
				   LEFT JOIN nei.dbo.Rol ON Loc.Rol = Rol.ID
            WHERE  Loc.Owner = ?
        ;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>utf8ize($data)));  
	}
}?>
		$data = array();
        $r = $database->query(null,"
            SELECT Loc.Loc     AS  ID,
                   Loc.ID      AS  Name,
                   Loc.Tag     AS  Tag,
                   Loc.Address AS  Street,
                   Loc.City    AS  City,
                   Loc.State   AS  State,
                   Loc.Zip     AS  Zip,
                   Loc.Route   AS  Route,
                   Loc.Zone    AS  Zone,
                   Loc.Maint   AS  Maintenance
            FROM   nei.dbo.Loc
                   LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
            WHERE  Master_Account.Master = ?
        ;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>utf8ize($data)));  }
}?>