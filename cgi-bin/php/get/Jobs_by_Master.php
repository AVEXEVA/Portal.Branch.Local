<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($_GET['ID']) && $My_Privileges['Job']['Group_Privilege'] >= 4 && is_numeric($_GET['ID'])){
            $r = $database->query(  null,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.LID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
            $r2 = $database->query( null,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Loc='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
            $r = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
            $r2 = sqlsrv_fetch_array($r2);
            $Privileged = (is_array($r) || is_array($r2)) ? TRUE : FALSE;
        }
    } elseif(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer' && $_SESSION['Branch_ID'] == $_GET['ID']) {$Privileged = TRUE;}
    if(!isset($array['ID'],$_GET['ID']) || !is_numeric($_GET['ID']) || !$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
    
        $r = $database->query(null,"
            SELECT Job.ID            AS ID,
                   Job.fDesc         AS Name,
                   JobType.Type      AS Type,
                   Job.fDate         AS Finished_Date,
                   Job_Status.Status AS Status,
                   Loc.Tag           AS Location
            FROM   nei.dbo.Job
                   LEFT JOIN nei.dbo.JobType           ON Job.Type                = JobType.ID
                   LEFT JOIN nei.dbo.Loc               ON Job.Loc                 = Loc.Loc
                   LEFT JOIN nei.dbo.Job_Status        ON Job.Status + 1          = Job_Status.ID
                   LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
            WHERE  Master_Account.Master   =   ?
                   AND Job.Type <> 9 
                   AND Job.Type <> 12
        ;",array($_GET['ID']));

        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}

        print json_encode(array('data'=>$data));
    }
}?>