<?php
session_start( [ 'read_and_close' => true ] );
set_time_limit (60);
require('../index.php');
function Check_Date_Time($date_time){
 if (preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2}).(\d{3})/", $date_time)){return true;}
 else {return false;}
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
  $array = sqlsrv_fetch_array($r);
  $Privileged = FALSE;
  if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
      $r = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_SESSION['User']));
      $My_User = sqlsrv_fetch_array($r);
      $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
      $r = $database->query($Portal, "SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
          FROM   Privilege
          WHERE  User_ID = ?
      ;",array($_SESSION['User']));
      $My_Privileges = array();
      while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
      $Privileged = FALSE;
      if(isset($My_Privileges['Map']) && $My_Privileges['Map']['User_Privilege'] >= 4 && $My_Privileges['Map']['User_Privilege'] >= 4 && $My_Privileges['Map']['User_Privilege'] >= 4){$Privileged = TRUE;}
  }
  if(!$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
  else {
    //Get Timeline//
    $rows = array();

    $r = $database->query(null,
      " SELECT    Top 1000
                  Invoice.*,
                  Loc.Tag AS Location_Tag
        FROM      Invoice
                  LEFT JOIN nei.dbo.Loc ON Loc.Loc = Invoice.Loc
        WHERE     Invoice.Ref > ?
                  And Invoice.Ref <> 2805952
                  AND Invoice.Ref <> 2805950
        ORDER BY  Ref DESC
      ;",array($_GET['Ref']));
    if($r){while($row = sqlsrv_fetch_array($r)){
      $rows[$row['Ref']] = $row ;
    }}
    print json_encode($rows);
  }
}?>
