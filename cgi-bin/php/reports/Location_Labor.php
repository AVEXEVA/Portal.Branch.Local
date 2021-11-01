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
                    SELECT   Loc.Loc AS ID
                    FROM     nei.dbo.Loc
					WHERE    Loc.Maint = 1
                    GROUP BY Loc.Loc
            ;");
            $data2 = array();
            if($r){while($array = sqlsrv_fetch_array($r)){$data2[$array['ID']] = $array;}}
            $sql = array();
            foreach($data2 as $key=>$variable){$sql[] = "Loc.Loc = '{$variable['ID']}'";}
            $sql = implode(" OR ",$sql);
            $r = sqlsrv_query($NEI,"
                    SELECT   Max(TicketD.EDate) AS Last_Date,
                             TicketD.Elev      	AS Unit_ID,
							 TicketD.ID         AS Ticket_ID,
							 Loc.Loc            AS ID
                    FROM     nei.dbo.TicketD 
							 LEFT JOIN nei.dbo.Loc ON TicketD.Loc = Loc.Loc
					WHERE    {$sql}
                    GROUP BY TicketD.Elev, TicketD.ID, Loc.Loc
            ;");
			if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
					if(isset($data2[$array['ID']])){
						$data2[$array['ID']]['Last_Date'] = $array['Last_Date'];
						$data2[$array['ID']]['Ticket_ID'] = $array['Ticket_ID'];
						$data2[$array['ID']]['Unit_ID']   = $array['Unit_ID'];
					}
                }
            }
            $r = sqlsrv_query($NEI,"
                SELECT Loc.Loc                     AS ID,
					   Loc.Tag        			   AS Location,
					   Loc.Loc                     AS Location_ID,
                       Zone.Name       			   AS Zone,
                       Emp.fFirst + ' ' + Emp.Last AS Route,
					   OwnerWithRol.ID             AS Customer_ID,
					   OwnerWithRol.Name           AS Customer_Name
                FROM   nei.dbo.Loc
                       LEFT JOIN nei.dbo.Zone         ON Loc.Zone 	     = Zone.ID
                       LEFT JOIN nei.dbo.Route        ON Loc.Route       = Route.ID
                       LEFT JOIN nei.dbo.Emp          ON Route.Mech      = Emp.fWork
					   LEFT JOIN nei.dbo.OwnerWithRol ON OwnerWithRol.ID = Loc.Owner
                WHERE  Loc.Maint = 1
                       AND ({$sql})
            ;");
			if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
            if($r){
                while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
                    $array['Last_Date'] = substr($data2[$array['ID']]['Last_Date'],0,10);
					$array['Ticket_ID'] = $data2[$array['ID']]['Ticket_ID'];
					$array['Unit_ID'] = $data2[$array['ID']]['Unit_ID'];
                    $data[] = $array;
                }
            }
        }
        print json_encode(array('data'=>utf8ize($data)));	
    }
}?>