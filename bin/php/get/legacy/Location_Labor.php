<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query($Portal,"
        SELECT Owner, Group, Other
        FROM   Privilege
        WHERE User_ID = ? AND Access='Job'
    ;",array($_SESSION['User']));
    $My_Privileges = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($array['ID']) || !is_array($My_Privileges)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        if($My_Privileges['Owner'] >= 4 && $My_Privileges['Group'] >= 4 && $My_Privileges['Other'] >= 4){
            $Tickets = array();
            $r = $database->query(null,"
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
            $r = $database->query(null,"
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
            $r = $database->query(null,"
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
                       LEFT JOIN Emp          ON Route.Mech      = Emp.fWork
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