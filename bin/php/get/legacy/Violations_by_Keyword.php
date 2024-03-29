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
	if( isset($My_Privileges['Violation']) 
	   	&& (
			(
					$My_Privileges['Violation']['Other'] >= 4
	  			&& 	$My_Privileges['Job']['Other'] >= 4
			)
			||
			(
					$My_Privileges['Violation']['Group'] >= 4
	  			&& 	$My_Privileges['Job']['Group'] >= 4
			)
			||
			(
					$My_Privileges['Violation']['Owner'] >= 4
	  			&& 	$My_Privileges['Job']['Owner'] >= 4
			)
		)
	){$Privileged = True;} 
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $data = array();
        $Keyword = addslashes($_GET['Keyword']);
        if($My_Privileges['Owner'] > 4 && $My_Privileges['Group'] > 4 && $My_Privileges['Other'] > 4){
            $r = $database->query(null,"
                SELECT Violation.ID     AS ID,
                       Violation.Name   AS Name,
                       Violation.fdate  AS fDate,
                       Violation.Status AS Status,
                       Loc.Tag          AS Location,
                       Elev.Unit        AS Unit
                FROM   nei.dbo.Violation
                       LEFT JOIN nei.dbo.Elev         ON Violation.Elev  = Elev.ID
                       LEFT JOIN nei.dbo.Loc          ON Elev.Loc        = Loc.Loc
                       LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Loc.Owner
                WHERE  (Violation.fdate         >= '1980-01-01 00:00:00.000'
                    		AND Violation.fdate <= '2099-01-01 00:00:00.000')
                    		AND (Violation.ID   LIKE '%{$Keyword}%' 
                       OR Violation.Name       LIKE '%{$Keyword}%'
                       OR Violation.fdate      LIKE '%{$Keyword}%'
                       OR Violation.Status     LIKE '%{$Keyword}%'
                       OR Loc.Tag              LIKE '%{$Keyword}%'
                       OR Elev.Unit            LIKE '%{$Keyword}%'
                       OR OwnerWithRol.Name    LIKE '%{$Keyword}%'
                       OR Elev.State           LIKE '%{$Keyword}%'
                       OR Elev.ID              LIKE '%{$Keyword}%'
                       OR Loc.ID               LIKE '%{$Keyword}%')
            ;");
            if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        } else {
            $SQL_Units = array();
            if($My_Privileges['Group'] >= 4){
                $r = $database->query(null,"
                    SELECT LElev AS Unit
                    FROM   nei.dbo.TicketO
                           LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
                $r = $database->query(null,"
                    SELECT Elev AS Unit
                    FROM   nei.dbo.TicketD
                           LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
            }
            if($My_Privileges['Owner'] >= 4){
                $r = $database->query(null,"
                    SELECT Elev.ID          AS Unit
                    FROM   nei.dbo.Elev
                           LEFT JOIN nei.dbo.Loc       ON Elev.Loc = Loc.Loc
                           LEFT JOIN nei.dbo.Route     ON Loc.Route = Route.ID
                           LEFT JOIN Emp       ON Route.Mech = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
            }
            $SQL_Units = array_unique($SQL_Units);
            if(count($SQL_Units) > 0){
                $SQL_Units = implode(' OR ',$SQL_Units);
                $r = $database->query(null,"
                    SELECT 
                        Violation.ID        AS ID,
                        Violation.Name      AS Name,
                        Violation.fdate     AS fDate,
                        Violation.Status    AS Status,
                        Loc.Tag             AS Location,
                        Elev.Unit           AS Unit
                    FROM 
                        nei.dbo.Violation
                        LEFT JOIN nei.dbo.Elev         ON Violation.Elev  = Elev.ID
                        LEFT JOIN nei.dbo.Loc          ON Elev.Loc        = Loc.Loc
                        LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Loc.Owner
                    WHERE
                        {$SQL_Units}
                        AND (Violation.ID    	 LIKE '%{$Keyword}%' 
                        	OR Violation.Name    LIKE '%{$Keyword}%'
                        	OR Violation.fdate   LIKE '%{$Keyword}%'
                        	OR Violation.Status  LIKE '%{$Keyword}%'
                        	OR Loc.Tag           LIKE '%{$Keyword}%'
                        	OR Elev.Unit         LIKE '%{$Keyword}%'
                        	OR OwnerWithRol.Name LIKE '%{$Keyword}%'
                        	OR Elev.State        LIKE '%{$Keyword}%'
                        	OR Elev.ID           LIKE '%{$Keyword}%'
                        	OR Loc.ID            LIKE '%{$Keyword}%')
                ;");
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
            }
        }
        print json_encode(array('data'=>$data));
    }
}?>