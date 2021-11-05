  <?php 
session_start( [ 'read_and_close' => true ] );
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Financials']) && $My_Privileges['Financials']['User_Privilege'] >= 4 && $My_Privileges['Financials']['Group_Privilege'] >= 4 && $My_Privileges['Financials']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }
    else {unset($_SESSION['Financials']);}
} else {?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }?>