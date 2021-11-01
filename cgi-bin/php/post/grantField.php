<?php
session_start();
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $User = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($NEI,"
        SELECT Privilege.*
        FROM   Privilege
        WHERE
            User_ID='{$_SESSION['User']}'
            AND Access_Table='Admin'
            AND User_Privilege='7'
            AND Group_Privilege='7'
            AND Other_Privilege='7'
    ;");
    $Admin = sqlsrv_fetch_array($r);
    if(!isset($array['ID'])  || !is_array($Admin)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r = sqlsrv_query($NEI,"
            SELECT Privilege.*
            FROM   Privilege
            WHERE  User_ID='{$_POST['User_ID']}'
        ;");
        require('../list/Privileges/Field.php');
        $Update_Privileges = array();
        while($array = sqlsrv_fetch_array($r)){
            if(in_array($array['Access_Table'],$My_Privileges)){
                if(($key = array_search($array['Access_Table'], $My_Privileges)) !== false) {
                    unset($My_Privileges[$key]);
                    $Update_Privileges[] = $array['Access_Table'];
                }
            }
        }
        foreach($My_Privileges as $Privilege){
            sqlsrv_query($NEI,"
                INSERT INTO Privilege(User_ID,Access_Table,User_Privilege,Group_Privilege,Other_Privilege)
                VALUES({$_POST['User_ID']},'{$Privilege}',6,4,0)
            ;");
        }
        foreach($Update_Privileges as $Privilege){
            sqlsrv_query($NEI,"
                UPDATE Privilege
                SET
                    User_Privilege='6',
                    Group_Privilege='4',
                    Other_Privilege='0'
                WHERE
                    User_ID='{$_POST['User_ID']}'
                    AND Access_Table='{$Privilege}'
            ;");
        }
        //print json_encode(array('data'=>$data));
    }
}?>
