<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query($Portal,"
        SELECT Owner, Group, Other
        FROM   Portal.dbo.Privilege
        WHERE User_ID = ? AND Access='Job'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID']) || !is_array($My_Privileges)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['Owner'] >= 4 && $My_Privileges['Group'] >= 4 && $My_Privileges['Other'] >= 4){
            $Tickets = array();
            $r = $database->query(null,"
                    SELECT   Elev.ID AS ID
                    FROM     nei.dbo.Elev
					         LEFT JOIN nei.dbo.Loc ON Elev.Loc = Loc.Loc
                    WHERE    Elev.Status = 0
					         AND Loc.Maint = 1
                    GROUP BY Elev.ID

            ;");
            $data2 = array();
            if($r){while($array = sqlsrv_fetch_array($r)){$data2[$array['ID']] = $array;}}
            $sql = array();
            foreach($data2 as $key=>$variable){$sql[] = "Elev.ID = '{$variable['ID']}'";}
            $sql = implode(" OR ",$sql);
            $r = $database->query(null,"
                    SELECT   Max(TicketD.EDate) AS Last_Date,
                             Elev.ID          	AS ID
                    FROM     nei.dbo.TicketD
                             LEFT JOIN nei.dbo.Elev ON TicketD.Elev = Elev.ID
                             LEFT JOIN nei.dbo.Job  ON TicketD.Job  = Job.ID
                    WHERE    (TicketD.Level = 10 OR TicketD.Level = 1)
							 AND ({$sql})
                    GROUP BY Elev.ID

            ;");
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
        					if(isset($data2[$array['ID']])){
        						$data2[$array['ID']]['Last_Date'] = $array['Last_Date'];
        					}
                }
            }
            $r = $database->query(null,"
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
                       AND ({$sql})
            ;");
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                    $array['Last_Date'] = substr($data2[$array['ID']]['Last_Date'],0,10);
                    $data[] = $array;
                }
            }
        }
        print json_encode(array('data'=>$data));
    }
}?>
