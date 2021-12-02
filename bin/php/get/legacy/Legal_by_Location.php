<<<<<<< HEAD
<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
        SELECT * 
        FROM   Connection 
        WHERE  Connection.Connector = ? 
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = $database->query(null,"
        SELECT Emp.*, 
               Emp.fFirst AS First_Name, 
               Emp.Last   AS Last_Name 
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query($Portal,"
        SELECT Privilege.Access, 
               Privilege.Owner, 
               Privilege.Group, 
               Privilege.Other
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Legal']) 
        && (
				$My_Privileges['Legal']['Other'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $r = $database->query(null,"
            SELECT 
                Job.ID            AS  ID,
                Job.fDesc         AS  Name,
                JobType.Type      AS  Type,
                Job.fDate         AS  Finished_Date,
                Job_Status.Status AS  Status,
                Loc.Tag           AS  Location
            FROM 
                Job
                LEFT JOIN nei.dbo.JobType    ON  Job.Type = JobType.ID
                LEFT JOIN nei.dbo.Loc        ON  Job.Loc = Loc.Loc
                LEFT JOIN nei.dbo.Job_Status ON  Job.Status + 1 = Job_Status.ID
            WHERE 
                Job.Loc       = ?
                AND (Job.Type = 9 
                 OR Job.Type  = 12)
        ;",array($_GET['ID']));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}
sqlsrv_close(null);
sqlsrv_close($Portal);
=======
<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT * 
		FROM Connection 
		WHERE Connector = ? 
		AND   Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Legal']) && $My_Privileges['Legal']['Owner'] >= 4 && $My_Privileges['Legal']['Group'] >= 4 && $My_Privileges['Legal']['Other'] >= 4){$Privileged = TRUE;}
        elseif(isset($_GET['ID']) && $My_Privileges['Job']['Group'] >= 4 && is_numeric($_GET['ID'])){
            $r = $database->query(  null,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.LID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
            $r2 = $database->query( null,"SELECT * FROM nei.dbo.TicketD WHERE TicketD.Loc='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
            $r = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
            $r2 = sqlsrv_fetch_array($r2);
            $Privileged = (is_array($r) || is_array($r2)) ? TRUE : FALSE;
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
    }
    if(!isset($array['ID'],$_GET['ID']) || !is_numeric($_GET['ID']) || !$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = $database->query(null,"
            SELECT 
                Job.ID            AS  ID,
                Job.fDesc         AS  Name,
                JobType.Type      AS  Type,
                Job.fDate         AS  Finished_Date,
                Job_Status.Status AS  Status,
                Loc.Tag           AS  Location
            FROM 
                Job
                LEFT JOIN nei.dbo.JobType    ON  Job.Type = JobType.ID
                LEFT JOIN nei.dbo.Loc        ON  Job.Loc = Loc.Loc
                LEFT JOIN nei.dbo.Job_Status ON  Job.Status + 1 = Job_Status.ID
            WHERE 
                Job.Loc       = ?
                AND (Job.Type = 9 
                 OR Job.Type  = 12)
        ;",array($_GET['ID']));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
    }
}
sqlsrv_close(null);
sqlsrv_close($Portal);
>>>>>>> the-portal/master
?>