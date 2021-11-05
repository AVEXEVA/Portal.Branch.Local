<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $r = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query(null,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 6){$Privileged = TRUE;}
    if(!isset($array['ID'], $_POST['ID'])  || !$Privileged || !is_numeric($_POST['ID'])){?><html><head></head></html><?php }
    else {
      $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket_time_completed.php?ID={$_POST['ID']}"));
      function roundToQuarterHour($minutes) {
          $round = 15;
          return round($minutes / $round) * $round;
      }
      $minutes = roundToQuarterHour(date("i"));
      if($minutes < 10){$minutes = "0" . $minutes;}
      $hours = $minutes == 60 ? intval(date("H")) + 1 : date("H");
      $minutes = $minutes == 60 || $minutes == 0 ? "00" : $minutes;
      if(strlen($hours) == 1){$hours = '0' . $hours;}
      $post_time = date("1899-12-30 {$hours}:{$minutes}:00.000");
      echo $post_time;
      $database->query(null,"UPDATE TicketO SET TicketO.EDate = ?, TicketO.TimeComp = ?, TicketO.Assigned = 3, TicketO.Confirmed = 1, TicketO.HandheldFieldsUpdated = 1, TicketO.ResolveSource = 'TFM-A3.60' WHERE TicketO.ID = ?;",array(date("Y-m-d H:i:s"), $post_time,$_POST['ID']));
      //$database->query(null,"")
    }
}?>
