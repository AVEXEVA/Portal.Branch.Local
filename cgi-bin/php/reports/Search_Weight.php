<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $SQL_Result_Privileges = sqlsrv_query($Portal,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Portal.dbo.Privilege
        WHERE User_ID='{$_SESSION['User']}'
    ;");
    $My_Privileges = array();
    if($SQL_Result_Privileges){while($Privilege = sqlsrv_fetch_array($SQL_Result_Privileges)){$My_Privileges[$Privilege['Access_Table']] = $Privilege;}}
    if(!isset($array['ID']) || !isset($_GET['Keyword']) || trim($_GET['Keyword']) == ''){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $data = array();
        $Keyword = addslashes($_GET['Keyword']);
        $Keywords = explode(" ",$Keyword);
        $Objects = array("Location","Job","Ticket","Proposal","Invoice","Unit","Customer");
        if(isset($My_Privileges['Location']) && (empty(array_intersect(array_map("strtolower",$Keywords),array_map("strtolower",$Objects))) || in_array("location",array_map("strtolower",$Keywords)))){
            $Location_ID = "Loc.Loc LIKE '%" . str_replace(" ","%' OR Loc.Loc LIKE '%",$Keyword) . "%'";
            $Location_Tag = "Loc.Tag LIKE '%" . str_replace(" ","%' OR Loc.Tag LIKE '%",$Keyword) . "%'";
            $Location_Address = "Loc.Address LIKE '%" . str_replace(" ","%' OR Loc.Address LIKE '%",$Keyword) . "%'";
            $Location_City = "Loc.City LIKE '%" . str_replace(" ","%' OR Loc.City LIKE '%",$Keyword) . "%'";
            $Location_State = "Loc.State LIKE '%" . str_replace(" ","%' OR Loc.State LIKE '%",$Keyword) . "%'";
            $Location_Zip = "Loc.Zip LIKE '%" . str_replace(" ","%' OR Loc.Zip LIKE '%",$Keyword) . "%'";
            if($My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){
                /*$SQL_Result_Locations = sqlsrv_query($NEI,"
                    SELECT DISTINCT
                        Loc     AS ID,
                        'Location'  AS Object,
                        Tag     AS Name,
                        Address AS Description,
                        Zone    AS Other,
                        Sum(rnk)    AS weightRank
                    FROM 
                        (
                            SELECT 
                                Loc
                                'Location'  AS Object,
                                Tag     AS Name,
                                Address AS Description,
                                Zone    AS Other,
                                Rank * 2.0  AS rnk
                            FROM freetexttable(Loc,Tag,'{$Keyword}')
                        union all
                            SELECT 
                                Loc
                                'Location'  AS Object,
                                Tag     AS Name,
                                Address AS Description,
                                Zone    AS Other,
                                Rank * 2.0  AS rnk
                            FROM freetexttable(Loc,Address,'{$Keyword}')
                        ) AS t
                    GROUP BY [Loc]
                ;");*/
                $SQL_Result_Locations = sqlsrv_query($NEI,"
                    SELECT 
                        ftt.RANK as Description,
                        Loc.Loc     as ID,
                        Loc.Tag     as Name,
                        'Location' as Object,
                        Zone    as Other
                    FROM Loc
                    INNER JOIN 
                    FREETEXTTABLE(Loc, Tag, '1411') as ftt
                    ON
                    ftt.[Loc]=Loc.Loc
                    ORDER BY ftt.RANK DESC
                ;");
                if($SQL_Result_Locations){while($Location = sqlsrv_fetch_array($SQL_Result_Locations)){$data[] = $Location;}}
            } else {
                $SQL_Locations = array();
                if($My_Privileges['Location']['Group_Privilege'] >= 4){
                    $r = sqlsrv_query($NEI,"
                        SELECT 
                            LID                 AS Location
                        FROM 
                                        TicketO
                            LEFT JOIN   Emp     ON TicketO.fWork = Emp.fWork
                        WHERE 
                            Emp.ID = '{$_SESSION['User']}'
                    ;");
                    
                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
                    $r = sqlsrv_query($NEI,"
                        SELECT 
                            Loc                 AS Location
                        FROM 
                                        TicketD
                            LEFT JOIN   Emp     ON TicketD.fWork = Emp.fWork
                        WHERE 
                            Emp.ID = '{$_SESSION['User']}'
                    ;");
                    
                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
                }
                if($My_Privileges['Location']['User_Privilege'] >= 4){
                    $r = sqlsrv_query($NEI,"
                        SELECT Loc.Loc          AS Location
                        FROM 
                            (Loc
                            LEFT JOIN nei.dbo.Route     ON Loc.Route = Route.ID)
                            LEFT JOIN nei.dbo.Emp       ON Route.Mech = Emp.fWork
                        WHERE
                            Emp.ID = '{$_SESSION['User']}'
                    ;");
                    while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$SQL_Locations[] = "Loc.Loc='{$array['Location']}'";}
                }
                $SQL_Locations = array_unique($SQL_Locations);
                if(count($SQL_Locations) > 0){
                    $SQL_Locations = implode(' OR ',$SQL_Locations);
                    $SQL_Result_Locations = sqlsrv_query($NEI,"
                        SELECT DISTINCT
                            Loc.Loc     AS ID,
                            'Location'  AS Object,
                            Loc.Tag     AS Name,
                            Loc.Address AS Description,
                            Loc.Zone    AS Other
                        FROM 
                            Loc
                        WHERE
                            (   {$Location_ID}
                                OR {$Location_Tag}
                                OR {$Location_Address}
                                OR {$Location_City}
                                OR {$Location_State}
                                OR {$Location_Zip})
                            AND ({$SQL_Locations})
                    ;");
                    while($Location = sqlsrv_fetch_array($SQL_Result_Locations)){$data[] = $Location;}
                }
            }
        }
        print json_encode(array('data'=>$data));   
    }
}