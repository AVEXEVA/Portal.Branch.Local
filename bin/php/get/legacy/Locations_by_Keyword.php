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
    if( isset($My_Privileges['Location']) 
        && (
				$My_Privileges['Location']['Other'] >= 4
			||	$My_Privileges['Location']['Group_Privlege'] >= 4
			||  $My_Privileges['Location']['Owner'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $data = array();
        $Keyword = addslashes($_GET['Keyword']);
        if($My_Privileges['Owner'] > 4 && $My_Privileges['Group'] > 4 && $My_Privileges['Other'] > 4){
            $r = $database->query(null,"
                SELECT DISTINCT
                    Loc.Loc     AS ID,
                    Loc.ID      AS Name,
                    Loc.Tag     AS Tag,
                    Loc.Address AS Street,
                    Loc.City    AS City,
                    Loc.State   AS State,
                    Loc.Zip     AS Zip,
                    Loc.Route   AS Route,
                    Loc.Zone    AS Zone,
                    Loc.Maint   AS Maintenance
                FROM 
                    nei.dbo.Loc
                    LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Loc.Owner
                    LEFT JOIN nei.dbo.Elev         ON Loc.Loc         = Elev.Loc
                WHERE
                    Elev.ID              LIKE '%{$Keyword}%'
                    OR Elev.State        LIKE '%{$Keyword}%'
                    OR Elev.Unit         LIKE '%{$Keyword}%'
                    OR Elev.Type         LIKE '%{$Keyword}%'
                    OR Elev.Loc          LIKE '%{$Keyword}%'
                    OR Loc.Tag           LIKE '%{$Keyword}%'
                    OR OwnerWithRol.Name LIKE '%{$Keyword}%'
            ;");
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}
            }
        } else {
            $SQL_Locations = array();
            if($My_Privileges['Group'] >= 4){
                $r = $database->query(null,"
                    SELECT LID AS Location
                    FROM   nei.dbo.TicketO
                           LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']);
                
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
                $r = $database->query(null,"
                    SELECT Loc AS Location
                    FROM   nei.dbo.TicketD
                           LEFT JOIN   Emp     ON TicketD.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']);
                
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
            }
            if($My_Privileges['Owner'] >= 4){
                $r = $database->query(null,"
                    SELECT Loc.Loc          AS Location
                    FROM   nei.dbo.Loc
                           LEFT JOIN nei.dbo.Route     ON Loc.Route = Route.ID
                           LEFT JOIN Emp       ON Route.Mech = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']);
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
            }
            $SQL_Locations = array_unique($SQL_Locations);
            if(count($SQL_Locations) > 0){
                $SQL_Locations = implode(' OR ',$SQL_Locations);
                $r = $database->query(null,"
                    SELECT Loc.Loc     AS ID,
                           Loc.ID      AS Name,
                           Loc.Tag     AS Tag,
                           Loc.Address AS Street,
                           Loc.City    AS City,
                           Loc.State   AS State,
                           Loc.Zip     AS Zip,
                           Loc.Route   AS Route,
                           Loc.Zone    AS Zone,
                           Loc.Maint   AS Maintenance
                    FROM   nei.dbo.Loc
                           LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Loc.Owner)
                           LEFT JOIN nei.dbo.Elev ON Loc.Loc = Elev.Loc
                    WHERE  (Elev.ID        LIKE '%{$Keyword}%'
                             OR Elev.State LIKE '%{$Keyword}%'
                             OR Elev.Unit  LIKE '%{$Keyword}%'
                             OR Elev.Type  LIKE '%{$Keyword}%'
                             OR Elev.Loc   LIKE '%{$Keyword}%'
                             OR Loc.Tag    LIKE '%{$Keyword}%'
                             OR OwnerWithRol.Name LIKE '%{$Keyword}%')
                           AND ({$SQL_Locations})
                ;");
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}
            }
        }
        print json_encode(array('data'=>$data));  }
}