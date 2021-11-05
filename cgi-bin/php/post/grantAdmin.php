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
        WHERE  User_ID='{$_SESSION['User']}'
               AND Access_Table='Admin'
               AND User_Privilege='7'
               AND Group_Privilege='7'
               AND Other_Privilege='7'
    ;");
    $Admin = sqlsrv_fetch_array($r);
    if(!isset($array['ID'])  || !is_array($Admin)){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $r1 = $database->query(null,"
                SELECT Privilege.*
                FROM   Privilege
                WHERE  User_ID='{$_POST['User_ID']}'
            ;");
            $My_Privileges = array('Modernization','Maintenance','Admin','Beta','Customer','Archive','Directory','Requisition','Delivery','Safety_Report','Personnel_Request','Ticket','Unit','Job','Violation','Location','Route','Map','Invoice','Proposal','Route','Time','Finances','Legal','Executive','Survey_Sheet','Collection','Item','Requisition','Purchase_Order','User','Connection','Privilege','Lead','Contract','Dispatch','Testing','Timeline','Log','Contact','Territory');
            $Update_Privileges = array();
            while($array = sqlsrv_fetch_array($r1)){
                if(in_array($array['Access_Table'],$My_Privileges)){
                    if(($key = array_search($array['Access_Table'], $My_Privileges)) !== false) {
                        unset($My_Privileges[$key]);
                        $Update_Privileges[] = $array['Access_Table'];
                    }
                }
            }
            foreach($My_Privileges as $Privilege){
                $database->query(null,"
                    INSERT INTO Privilege(User_ID,Access_Table,User_Privilege,Group_Privilege,Other_Privilege)
                    VALUES(?,?,7,7,7)
                ;",array($_POST['User_ID'],$Privilege));
            }
            foreach($Update_Privileges as $Privilege){
                $database->query(null,"
                    UPDATE Privilege
                    SET    User_Privilege='7',
                           Group_Privilege='7',
                           Other_Privilege='7'
                    WHERE  User_ID='{$_POST['User_ID']}'
                           AND Access_Table='{$Privilege}'
                ;");
            }
        //print json_encode(array('data'=>$data));
    }
}?>
