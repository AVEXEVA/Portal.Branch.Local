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
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['Owner'] >= 4 && $My_Privileges['Job']['Group'] >= 4 && $My_Privileges['Job']['Other'] >= 4){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        if(isset($_POST['Supervisor'])){
            $database->query($Portal,"
                UPDATE Modernization 
                SET 
                    Modernization.Supervisor = ?,
                    Modernization.Actual_Return = ?,
                    Modernization.EBN = ?,
                    Modernization.Date_Removed = ?,
                    Modernization.Budget_Hours = ?,
                    Modernization.Hyperlink = ?,
                    Modernization.Package = ?
                WHERE 
                    Modernization.ID='{$_POST['ID']}'
            ;",array($_POST['Supervisor'],$_POST['Returned'],$_POST['EBN'],$_POST['Removed'],$_POST['Budget_Hours'],$_POST['Hyperlink'],$_POST['Package']));
            var_dump($_POST);

            $Timestamp = date("Y-m-d H:i:s");
            $r = $database->query($Portal,"
                    INSERT INTO Mod_Tracker(Modernization,Status,Author,Time_Stamp) 
                    VALUES('{$_POST['ID']}','{$_POST['Status']}','{$_SESSION['User']}','{$Timestamp}')
                ;");
        } else {
            if(count($_POST) > 0){
                function fixArrayKey(&$arr)
                {
                    $arr=array_combine(array_map(function($str){return str_replace("_"," ",$str);},array_keys($arr)),array_values($arr));
                    foreach($arr as $key=>$val)
                    {
                        if(is_array($val)) fixArrayKey($arr[$key]);
                    }
                }
                fixArrayKey($_POST);
                var_dump($_POST);
                foreach($_POST as $key=>$value){
                    $value = str_replace('"',"''",$value);
                    $value = trim($value);
                    $database->query(null,"
                        UPDATE ElevTItem
                        SET
                            ElevTItem.Value = ?
                        WHERE 
                            ElevTItem.Elev = ?
                            AND ElevTItem.ElevT = 1
                            AND ElevTItem.fDesc = '{$key}'
                    ;",array($value,$_POST['Unit']));    
                }
            }
        }
    }
}?>