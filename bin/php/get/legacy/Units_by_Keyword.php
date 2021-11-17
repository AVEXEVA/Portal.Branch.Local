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
    if( isset($My_Privileges['Unit']) 
        && (
				$My_Privileges['Unit']['Other'] >= 4
			||	$My_Privileges['Unit']['Group_Privlege'] >= 4
			||  $My_Privileges['Unit']['Owner'] >= 4
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
                    Elev.ID    AS  ID,
                    Elev.State AS  State, 
                    Elev.Unit  AS  Unit,
                    Elev.Type  AS  Type,
                    Loc.Tag    AS  Location
                FROM 
                    nei.dbo.Elev
                    LEFT JOIN nei.dbo.Loc          ON Elev.Loc  = Loc.Loc
                    LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner = OwnerWithRol.ID
                WHERE
                    Elev.ID              LIKE '%{$Keyword}%'
                    OR Elev.State        LIKE '%{$Keyword}%'
                    OR Elev.Unit         LIKE '%{$Keyword}%'
                    OR Elev.Type         LIKE '%{$Keyword}%'
                    OR Elev.Loc          LIKE '%{$Keyword}%'
                    OR Loc.Tag           LIKE '%{$Keyword}%'
                    OR OwnerWithRol.Name LIKE '%{$Keyword}%'
            ;");
            while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}
        } else {
            $SQL_Units = array();
            if($My_Privileges['Group'] >= 4){
                $r = $database->query(null,"
                    SELECT LElev AS Unit
                    FROM   TicketO
                           LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Elev.ID='{$array['Unit']}'";}}
                $r = $database->query(null,"
                    SELECT Elev AS Unit
                    FROM   nei.dbo.TicketD
                           LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Elev.ID='{$array['Unit']}'";}}
            }
            if($My_Privileges['Owner'] >= 4){
                $r = $database->query(null,"
                    SELECT Elev.ID AS Unit
                    FROM   nei.dbo.Elev
                           LEFT JOIN nei.dbo.Loc   ON Elev.Loc   = Loc.Loc
                           LEFT JOIN nei.dbo.Route ON Loc.Route  = Route.ID
                           LEFT JOIN Emp   ON Route.Mech = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']);
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Elev.ID='{$array['Unit']}'";}}
            }
            $SQL_Units = array_unique($SQL_Units);
            if(count($SQL_Units) > 0){
                $SQL_Units = implode(' OR ',$SQL_Units);
                $r = $database->query(null,"
                    SELECT DISTINCT
                        Elev.ID         AS  ID,
                        Elev.State      AS  State, 
                        Elev.Unit       AS  Unit,
                        Elev.Type       AS  Type,
                        Loc.Tag         AS  Location
                    FROM 
                        nei.dbo.Elev
                        LEFT JOIN nei.dbo.Loc   ON  Elev.Loc = Loc.Loc
                        LEFT JOIN nei.dbo.OwnerWithRol ON Loc.Owner = OwnerWithRol.ID
                    WHERE
                        (Elev.ID                 LIKE '%{$Keyword}%'
                        	OR Elev.State        LIKE '%{$Keyword}%'
                        	OR Elev.Unit         LIKE '%{$Keyword}%'
                        	OR Elev.Type         LIKE '%{$Keyword}%'
                        	OR Elev.Loc          LIKE '%{$Keyword}%'
                        	OR Loc.Tag           LIKE '%{$Keyword}%'
                        	OR OwnerWithRol.Name LIKE '%{$Keyword}%')
                        AND {$SQL_Units}
                ;");
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
            }
        }
        print json_encode(array('data'=>$data));   }
}?>