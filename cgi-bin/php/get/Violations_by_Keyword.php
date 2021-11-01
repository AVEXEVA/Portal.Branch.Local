<?php 
session_start();
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
			   AND Connection.Hash = ?
	;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
	$My_User    = sqlsrv_query($NEI,"
		SELECT Emp.*, 
			   Emp.fFirst AS First_Name, 
			   Emp.Last   AS Last_Name 
		FROM   Emp
		WHERE  Emp.ID = ?
	;", array($_SESSION['User']));
	$My_User = sqlsrv_fetch_array($My_User); 
	$My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
	$r = sqlsrv_query($Portal,"
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
	if( isset($My_Privileges['Violation']) 
	   	&& (
			(
					$My_Privileges['Violation']['Other_Privilege'] >= 4
	  			&& 	$My_Privileges['Job']['Other_Privilege'] >= 4
			)
			||
			(
					$My_Privileges['Violation']['Group_Privilege'] >= 4
	  			&& 	$My_Privileges['Job']['Group_Privilege'] >= 4
			)
			||
			(
					$My_Privileges['Violation']['User_Privilege'] >= 4
	  			&& 	$My_Privileges['Job']['User_Privilege'] >= 4
			)
		)
	){$Privileged = True;} 
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $data = array();
        $Keyword = addslashes($_GET['Keyword']);
        if($My_Privileges['User_Privilege'] > 4 && $My_Privileges['Group_Privilege'] > 4 && $My_Privileges['Other_Privilege'] > 4){
            $r = sqlsrv_query($NEI,"
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
            if($My_Privileges['Group_Privilege'] >= 4){
                $r = sqlsrv_query($NEI,"
                    SELECT LElev AS Unit
                    FROM   nei.dbo.TicketO
                           LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
                $r = sqlsrv_query($NEI,"
                    SELECT Elev AS Unit
                    FROM   nei.dbo.TicketD
                           LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                    WHERE  Emp.ID = ?
                ;",array($_SESSION['User']));
                if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Units[] = "Violation.Elev='{$array['Unit']}'";}}
            }
            if($My_Privileges['User_Privilege'] >= 4){
                $r = sqlsrv_query($NEI,"
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
                $r = sqlsrv_query($NEI,"
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