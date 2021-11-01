<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    if(!isset($array['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
            SELECT TOP 1 ID FROM 
                (SELECT TOP 1 ID FROM nei.dbo.TicketO WHERE TicketO.fWork = '{$User['fWork']}' AND TicketO.Assigned = '3'
                 UNION 
                 SELECT TOP 1 ID FROM nei.dbo.TicketO WHERE TicketO.fWork = '{$User['fWork']}' AND TicketO.Assigned = '2'
                 UNION 
                 SELECT TOP 1 ID FROM 
                    (SELECT ID, EDate FROM nei.dbo.TicketO WHERE TicketO.fWork = '{$User['fWork']}' AND TicketO.Assigned = '4' ORDER BY EDate DESC
                     UNION 
                     SELECT ID, EDate FROM nei.dbo.TicketD WHERE TicketO.fWork = '{$User['fWork']}' ORDER BY EDate DESC)
                     ORDER BY EDate DESC)
        ;")
        $ID = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
        $ID = $ID['ID'];
        $array = null;
        $r = sqlsrv_query($NEI,"
            SELECT TOP 1
                TicketO.*,
                Loc.Tag                     AS Tag, 
                Loc.Address                 AS Address, 
                Loc.City                    AS City, 
                Loc.State                   AS State, 
                Loc.Zip                     AS Zip, 
                Job.ID                      AS Job_ID, 
                Job.fDesc                   AS Job_Description, 
                OwnerWithRol.ID             AS Owner_ID, 
                OwnerWithRol.Name           AS Customer, 
                JobType.Type                AS Job_Type,
                Elev.ID                     AS Unit_ID,
                Elev.Unit                   AS Unit_Label, 
                Elev.State                  AS Unit_State, 
                Elev.Type                   AS Unit_Type,
                Zone.Name                   AS Division, 
                TicketPic.PicData           AS PicData,
                TickOStatus.Type            AS Status, 
                Emp.ID                      AS Employee_ID, 
                Emp.fFirst                  AS First_Name, 
                Emp.Last                    AS Last_Name 
            FROM
                nei.dbo.TicketO 
                LEFT JOIN nei.dbo.Loc             ON TicketO.LID = Loc.Loc
                LEFT JOIN nei.dbo.Job             ON TicketO.Job = Job.ID 
                LEFT JOIN nei.dbo.OwnerWithRol    ON TicketO.Owner = OwnerWithRol.ID
                LEFT JOIN nei.dbo.JobType         ON Job.Type = JobType.ID
                LEFT JOIN nei.dbo.Elev            ON TicketO.LElev = Elev.ID
                LEFT JOIN nei.dbo.Zone            ON Loc.Zone = Zone.ID
                LEFT JOIN nei.dbo.TickOStatus     ON TicketO.Assigned = TickOStatus.Ref
                LEFT JOIN nei.dbo.Emp             ON TicketO.fWork = Emp.fWork
                LEFT JOIN nei.dbo.TicketPic       ON TicketO.ID = TicketPic.TicketID 
            WHERE
                TicketO.ID = '{$ID}'
        ;");
        if($r){$array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);}
        if(!is_array($array)){
            $r = sqlsrv_query($NEI,"
                SELECT TOP 1
                    TicketD.*,
                    Loc.Tag                     AS Tag, 
                    Loc.Address                 AS Address, 
                    Loc.City                    AS City, 
                    Loc.State                   AS State, 
                    Loc.Zip                     AS Zip, 
                    Job.ID                      AS Job_ID, 
                    Job.fDesc                   AS Job_Description, 
                    OwnerWithRol.ID             AS Owner_ID, 
                    OwnerWithRol.Name           AS Customer, 
                    JobType.Type                AS Job_Type,
                    Elev.ID                     AS Unit_ID,
                    Elev.Unit                   AS Unit_Label, 
                    Elev.State                  AS Unit_State, 
                    Elev.Type                   AS Unit_Type,
                    Zone.Name                   AS Division, 
                    TicketPic.PicData           AS PicData,
                    Emp.ID                      AS Employee_ID, 
                    Emp.fFirst                  AS First_Name, 
                    Emp.Last                    AS Last_Name 
                FROM
                    TicketD 
                    LEFT JOIN nei.dbo.Loc     		  ON TicketD.LID = Loc.Loc
                    LEFT JOIN nei.dbo.Job             ON TicketD.Job = Job.ID
                    LEFT JOIN nei.dbo.OwnerWithRol    ON TicketD.Owner = OwnerWithRol.ID
                    LEFT JOIN nei.dbo.JobType         ON Job.Type = JobType.ID
                    LEFT JOIN nei.dbo.Elev            ON TicketD.LElev = Elev.ID
                    LEFT JOIN nei.dbo.Zone            ON Loc.Zone = Zone.ID
                    LEFT JOIN nei.dbo.Emp             ON TicketD.fWork = Emp.fWork
                    LEFT JOIN nei.dbo.TicketPic       ON TicketD.ID = TicketPic.TicketID 
                WHERE
                    TicketD.ID = '{$ID}'
            ;");
            if($r){$array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);}
        }
        print json_encode(array('data'=>$array));    
    }
}

