<?php 
session_start( [ 'read_and_close' => true ] );
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $User = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = $database->query(null,"
        SELECT Privilege.*
        FROM   Privilege
        WHERE  User_ID=?
               AND Access_Table    = 'Admin'
               AND User_Privilege  = 7
               AND Group_Privilege = 7
               AND Other_Privilege = 7
    ;",array($_SESSION['User']));
    $Admin = sqlsrv_fetch_array($r);
    if(!isset($array['ID'])  || !is_array($Admin) || !is_numeric($_POST['User_ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {$r = $database->query(null,"DELETE FROM Privilege WHERE User_ID='{$_POST['User_ID']}';");  }
}?>