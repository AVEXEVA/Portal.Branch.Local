<?php 
session_start();
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $Privileged = FALSE;
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    }var_dump($_POST);
    if(!$Privileged || count($_POST) == 0){?><?php }
    else {
        if(strlen($_POST['Submitted']) > 0){$_POST['Submitted'] = date_format(date_create_from_format('m/d/Y',$_POST['Submitted']),'Y-m-d 00:00:00.000');}
        if(strlen($_POST['Purchased']) > 0){$_POST['Purchased'] = date_format(date_create_from_format('m/d/Y',$_POST['Purchased']),'Y-m-d 00:00:00.000');}
        if(strlen($_POST['Drawings_Received']) > 0){$_POST['Drawings_Received'] = date_format(date_create_from_format('m/d/Y',$_POST['Drawings_Received']),'Y-m-d 00:00:00.000');}
        if(strlen($_POST['Drawings_Reviewed']) > 0){$_POST['Drawings_Reviewed'] = date_format(date_create_from_format('m/d/Y',$_POST['Drawings_Reviewed']),'Y-m-d 00:00:00.000');}
        if(strlen($_POST['Warehoused']) > 0){$_POST['Warehoused'] = date_format(date_create_from_format('m/d/Y',$_POST['Warehoused']),'Y-m-d 00:00:00.000');}
        $_POST['Parent'] = 0;
        var_dump($_POST);
        sqlsrv_query($Portal,"
            UPDATE Mod_Equipment
            SET 
                Mod_Equipment.Parent='{$_POST['Parent']}',
                Mod_Equipment.Version='{$_POST['Version']}',
                Mod_Equipment.Equipment='{$_POST['Equipment']}',
                Mod_Equipment.Description='{$_POST['Description']}',
                Mod_Equipment.In_Care_Of='{$_POST['In_Care_Of']}',
                Mod_Equipment.Subcontractor='{$_POST['Subcontractor']}',
                Mod_Equipment.Quantity='{$_POST['Quantity']}',
                Mod_Equipment.Submitted='{$_POST['Submitted']}',
                Mod_Equipment.Purchased='{$_POST['Purchased']}',
                Mod_Equipment.PO='{$_POST['PO']}',
                Mod_Equipment.Drawings_Received='{$_POST['Drawings_Received']}',
                Mod_Equipment.Drawings_Reviewed='{$_POST['Drawings_Reviewed']}',
                Mod_Equipment.Notes='{$_POST['Notes']}',
                Mod_Equipment.Warehoused='{$_POST['Warehoused']}',
                Mod_Equipment.Status='{$_POST['Status']}'
            WHERE 
                Mod_Equipment.ID='{$_POST['ID']}'
        ;");

    }
}?>