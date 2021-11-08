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
        $r = $database->query($Portal,"SELECT Modernization.ID FROM Modernization WHERE Modernization.Job = ? AND Modernization.Unit = ?",array($_POST['Job'],$_POST['Unit']));
        if($r){
            $modernization = sqlsrv_fetch_array($r)['ID'];
            var_dump($modernization);
            $database->query($Portal,"
                INSERT INTO Mod_Equipment(Modernization, Parent, Version, Equipment, Description, In_Care_Of, Subcontractor, Quantity, Submitted, Purchased, PO, Status, Drawings_Received, Drawings_Reviewed, Notes, Warehoused)
                VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ;",array($modernization,0,0,$_POST['Equipment'],$_POST['Description'],$_POST['In_Care_Of'],$_POST['Subcontractor'],$_POST['Quantity'],$_POST['Submitted'],$_POST['Purchased'],$_POST['PO'],$_POST['Status'],$_POST['Drawings_Received'],$_POST['Drawings_Reviewed'],$_POST['Notes'],$_POST['Warehoused']));

        }
    }
}?>