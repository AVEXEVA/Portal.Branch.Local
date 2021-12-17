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
        $r = $database->query(null,"
            SELECT ID
            FROM Emp
            WHERE 
                Status='0'
                AND Field='1'
                AND Title <> 'OFFICE'
        ;");

        while($User = sqlsrv_fetch_array($r)){
            require('../list/Privileges/Field.php');
            $r1 = $database->query(null,"
                SELECT Privileges.* 
                FROM   Privileges
                WHERE
                    User_ID='{$User['ID']}'
                    AND Owner <> 7
                    AND Group <> 7
                    AND Other <> 7
            ;");
            
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
                    VALUES({$User['ID']},'{$Privilege}',6,4,0)
                ;");
            }
            foreach($Update_Privileges as $Privilege){
                $database->query(null,"
                    UPDATE Privileges
                    SET 
                        Owner='6',
                        Group='4',
                        Other='0'
                    WHERE 
                        User_ID='{$User['ID']}'
                        AND Access='{$Privilege}'
                ;");
            }
            unset($My_Privileges);
        }
    }
}?>