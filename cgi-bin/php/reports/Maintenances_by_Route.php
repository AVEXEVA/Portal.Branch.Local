<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
        SELECT User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE User_ID = ? AND Access_Table='Job'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID']) || !is_array($My_Privileges)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['User_Privilege'] >= 4 && $My_Privileges['Group_Privilege'] >= 4){
            $Tickets = array();
            $r = sqlsrv_query($NEI,"
                    SELECT   Elev.ID AS ID
                    FROM     Elev 
					         LEFT JOIN Loc   ON Elev.Loc   = Loc.Loc
							 LEFT JOIN Route ON Loc.Route  = Route.ID
							 LEFT JOIN Emp   ON Route.Mech = Emp.ID
                    WHERE    Elev.Status = 0
					         AND Loc.Maint = 1
							 AND Route.ID = ?
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
                    FROM     TicketD 
                             LEFT JOIN Elev ON TicketD.Elev = Elev.ID
                             LEFT JOIN Job  ON TicketD.Job  = Job.ID
                    WHERE    Job.Type = 0
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
            $r = sqlsrv_query($NEI,"
                SELECT Elev.ID         			   AS ID,
                       Elev.State      			   AS State, 
                       Elev.Unit       			   AS Unit,
                       Elev.Type                   AS Type,
                       Loc.Tag        			   AS Location,
                       Zone.Name       			   AS Zone,
                       Emp.fFirst + ' ' + Emp.Last AS Route
                FROM   Elev
                       LEFT JOIN Loc   ON Elev.Loc 	 = Loc.Loc
                       LEFT JOIN Zone  ON Loc.Zone 	 = Zone.ID
                       LEFT JOIN Route ON Loc.Route  = Route.ID
                       LEFT JOIN Emp   ON Route.Mech = Emp.fWork
                WHERE  Loc.Maint = 1
                       AND ({$sql})
            ;");
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                    $array['Last_Date'] = substr($data2[$array['ID']]['Last_Date'],0,10);
					if($array['Last_Date'] >= date("Y-m-01")){continue;}
                    $data[] = $array;
                }
            }
        }
        print json_encode(array('data'=>utf8ize($data)));	
    }
}?>