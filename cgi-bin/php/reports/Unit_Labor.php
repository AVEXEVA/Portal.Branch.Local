<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($Portal,"
        SELECT User_Privilege, Group_Privilege, Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE User_ID = ? AND Access_Table='Job'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID']) || !is_array($My_Privileges)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4 && $My_Privileges['Other_Privilege'] >= 4){
            $Tickets = array();
            $r = sqlsrv_query($NEI,"
                    SELECT   Elev.ID AS ID
                    FROM     nei.dbo.Elev
                             LEFT JOIN nei.dbo.Loc ON Loc.Loc = Elev.Loc
					WHERE    Loc.Maint = 1
					         AND Elev.Status = 0
                    GROUP BY Elev.ID
            ;");
            $data2 = array();
            if($r){while($array = sqlsrv_fetch_array($r)){$data2[$array['ID']] = $array;}}
            $sql = array();
            foreach($data2 as $key=>$variable){$sql[] = "Elev.ID = '{$variable['ID']}'";}
            $sql = implode(" OR ",$sql);
            $r = sqlsrv_query($NEI,"
                    SELECT   Max(TicketD.EDate) AS Last_Date,
                             Elev.ID          	AS ID,
							 TicketD.ID         AS Ticket_ID
                    FROM     nei.dbo.TicketD 
                             LEFT JOIN nei.dbo.Elev ON TicketD.Elev = Elev.ID
                             LEFT JOIN nei.dbo.Job  ON TicketD.Job  = Job.ID
					WHERE    {$sql}
                    GROUP BY Elev.ID, TicketD.ID
            ;");
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
					if(isset($data2[$array['ID']])){
						$data2[$array['ID']]['Last_Date'] = $array['Last_Date'];
						$data2[$array['ID']]['Ticket_ID'] = $array['Ticket_ID'];
					}
                }
            }
            $r = sqlsrv_query($NEI,"
                SELECT Elev.ID         			   AS ID,
					   Elev.ID                     AS Unit_ID,
                       Elev.State      			   AS State, 
                       Elev.Unit       			   AS Unit,
                       Elev.Type                   AS Type,
                       Loc.Tag        			   AS Location,
					   Loc.Loc                     AS Location_ID,
                       Zone.Name       			   AS Zone,
                       Emp.fFirst + ' ' + Emp.Last AS Route,
					   OwnerWithRol.ID             AS Customer_ID,
					   OwnerWithRol.Name           AS Customer_Name
                FROM   nei.dbo.Elev
                       LEFT JOIN nei.dbo.Loc          ON Elev.Loc 	     = Loc.Loc
                       LEFT JOIN nei.dbo.Zone         ON Loc.Zone 	     = Zone.ID
                       LEFT JOIN nei.dbo.Route        ON Loc.Route       = Route.ID
                       LEFT JOIN nei.dbo.Emp          ON Route.Mech      = Emp.fWork
					   LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Loc.Owner
                WHERE  Loc.Maint = 1
                       AND ({$sql})
            ;");
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                    $array['Last_Date'] = substr($data2[$array['ID']]['Last_Date'],0,10);
					$array['Ticket_ID'] = $data2[$array['ID']]['Ticket_ID'];
                    $data[] = $array;
                }
            }
        }
        print json_encode(array('data'=>utf8ize($data)));	
    }
}?>