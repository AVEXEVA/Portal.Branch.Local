<?php 
session_start( [ 'read_and_close' => true ] );
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $Privileged = FALSE;
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
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        if(strlen($_POST['Submitted']) > 0){$_POST['Submitted'] = substr($_POST['Submitted'],6,4) . '-' . substr($_POST['Submitted'],0,2) . '-' . substr($_POST['Submitted'],3,2);}
        else {$_POST['Submitted'] = "1900-01-01";}
        if(strlen($_POST['Returned']) > 0){$_POST['Returned'] = substr($_POST['Returned'],6,4) . '-' . substr($_POST['Returned'],0,2) . '-' . substr($_POST['Returned'],3,2);}
        else {$_POST['Returned'] = "1900-01-01";}
        $database->query($Portal,"
            UPDATE Mod_Correspondence
            SET 
                Mod_Correspondence.Recipient = ?,
                Mod_Correspondence.Sender = ?,
                Mod_Correspondence.Notes = ?,
                Mod_Correspondence.Status = ?,
                Mod_Correspondence.Submitted = ?,
                Mod_Correspondence.Returned = ?
            WHERE 
                Mod_Correspondence.ID = ?
        ;",array($_POST['Recipient'],$_POST['Sender'],$_POST['Notes'],$_POST['Status'],$_POST['Submitted'],$_POST['Returned'],$_POST['ID']));
    }
}?>