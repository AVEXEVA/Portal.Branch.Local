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
    if(!isset($array['ID']) || !is_array($My_Privileges) && is_numeric($_GET['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4 && $My_Privileges['Other_Privilege'] >= 4){
            $Tickets = array();
            $r = sqlsrv_query($NEI,"
                    SELECT   Elev.ID AS ID
                    FROM     nei.dbo.TicketO
                             LEFT JOIN nei.dbo.Elev ON  TicketO.LElev = Elev.ID
                             LEFT JOIN nei.dbo.Job  ON  TicketO.Job = Job.ID
							 LEFT JOIN nei.dbo.Loc  ON  Loc.Loc = TicketO.LID
                    WHERE    TicketO.Type      = 0
                             AND TicketO.Level = 10
							 AND Loc.Zone = ?
                    GROUP BY Elev.ID
            ;",array($_GET['ID']));
            $data2 = array();
            if($r){while($array = sqlsrv_fetch_array($r)){$data2[$array['ID']] = $array;}}
            $sql = array();
            foreach($data2 as $key=>$variable){$sql[] = "Elev.ID = '{$variable['ID']}'";}
            $sql = implode(" OR ",$sql);
            $r = sqlsrv_query($NEI,"
                    SELECT   Max(TicketD.EDate) AS Last_Date,
                             Elev.ID          	AS ID
                    FROM     nei.dbo.TicketD
                             LEFT JOIN nei.dbo.Elev ON TicketD.Elev = Elev.ID
                             LEFT JOIN nei.dbo.Job  ON TicketD.Job  = Job.ID
							 LEFT JOIN nei.dbo.Loc  ON Elev.Loc     = Loc.Loc
                    WHERE    Job.Type = 0
							 AND Loc.Zone = ?
                    GROUP BY Elev.ID
            ;",array($_GET['ID']));
            if($r){
                $date = date('m');
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                    if(intval(substr($array['Last_Date'],5,2)) != $date){
                        if(isset($data2[$array['ID']])){
                            $data2[$array['ID']]['Last_Date'] = $array['Last_Date'];
                        }
                    }
                }
            }
            $r = sqlsrv_query($NEI,"
                SELECT Elev.ID         			   AS ID,
                       Elev.State      			   AS State,
                       Elev.Unit       			   AS Unit,
                       Elev.Type                   AS Type,
                       Loc.Tag        			   AS Location,
                       Zone.Name       			   AS Zone,
                       Emp.fFirst + ' ' + Emp.Last AS Route
                FROM   nei.dbo.Elev
                       LEFT JOIN nei.dbo.Loc   ON Elev.Loc 	 = Loc.Loc
                       LEFT JOIN nei.dbo.Zone  ON Loc.Zone 	 = Zone.ID
                       LEFT JOIN nei.dbo.Route ON Loc.Route  = Route.ID
                       LEFT JOIN nei.dbo.Emp   ON Route.Mech = Emp.fWork
                WHERE  Loc.Maint = 1
					   AND Elev.Status = 0
                       AND ({$sql})
            ;",array($_GET['ID']));
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                    $array['Last_Date'] = substr($data2[$array['ID']]['Last_Date'],0,10);
                    $data[] = $array;
                }
            }
        }
        print json_encode(array('data'=>utf8ize($data)));
    }
}?>
