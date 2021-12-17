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
        WHERE 
            User_ID='{$_SESSION['User']}'
            AND Access='Admin'
            AND Owner='7'
            AND Group='7'
            AND Other='7'
    ;");
    $Admin = sqlsrv_fetch_array($r);
    if(!isset($array['ID'])  || !is_array($Admin)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $Dispatchers = array(673,925,223,767,1137,465,371,569,418,772,254,763,273,19,232,17,1011,987,773,472,480,133,881,183,225,906);
        while($Dispatcher = array_pop($Dispatchers)){
            $r1 = $database->query(null,"
                SELECT Privilege.*
                FROM   Privilege
                WHERE
                    User_ID='{$Dispatcher}'
                    AND Owner <> 7
                    AND Group <> 7
                    AND Other <> 7
            ;");
            require('../list/Privileges/Field.php');
            $Update_Privileges = array();
            while($array = sqlsrv_fetch_array($r1)){
                if(in_array($array['Access'],$My_Privileges)){
                    if(($key = array_search($array['Access'], $My_Privileges)) !== false) {
                        unset($My_Privileges[$key]);
                        $Update_Privileges[] = $array['Access'];
                    }
                }
            }
            foreach($My_Privileges as $Privilege){
                $database->query(null,"
                    INSERT INTO Privilege(User_ID,Access,Owner,Group,Other)
                    VALUES({$Dispatcher},'{$Privilege}',6,6,4)
                ;");
            }
            foreach($Update_Privileges as $Privilege){
                $database->query(null,"
                    UPDATE Privilege
                    SET 
                        Owner='6',
                        Group='6',
                        Other='4'
                    WHERE 
                        User_ID='{$Dispatcher}'
                        AND Access='{$Privilege}'
                ;");
            }
        }
    }
}?>