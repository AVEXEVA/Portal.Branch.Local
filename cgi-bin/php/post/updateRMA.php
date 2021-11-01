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
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $_POST['Date']     = $_POST['Date']     != "" ? date_format(date_create_from_format('m/d/Y',$_POST['Date']),    'Y-m-d 00:00:00.000') : "1900-01-01 00:00:00.000";
        $_POST['Received'] = $_POST['Received'] != "" ? date_format(date_create_from_format('m/d/Y',$_POST['Received']),'Y-m-d 00:00:00.000') : "1900-01-01 00:00:00.000";
        $_POST['Returned'] = $_POST['Returned'] != "" ? date_format(date_create_from_format('m/d/Y',$_POST['Returned']),'Y-m-d 00:00:00.000') : "1900-01-01 00:00:00.000";
        var_dump($_POST);
        sqlsrv_query($Portal,"UPDATE RMA SET Name=?, Date=?, Address=?, RMA=?, Received=?, Returned=?, Tracking=?, PO = ?, Link = ?, Description = ?, Status = ? WHERE ID=?;",array($_POST['Name'],$_POST['Date'],$_POST['Address'],$_POST['RMA'],$_POST['Received'],$_POST['Returned'],$_POST['Tracking'],$_POST['PO'],$_POST['Link'],$_POST['Description'],$_POST['Status'],$_POST['ID']));

    }
}?>