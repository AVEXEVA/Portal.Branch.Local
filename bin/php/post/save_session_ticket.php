<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
  $array = sqlsrv_fetch_array($r);
  $Privileged = FALSE;
  if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
      $r = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_SESSION['User']));
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
      if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['Owner'] >= 6){$Privileged = TRUE;}
  }
  if(!$Privileged || count($_POST) == 0 || !isset($_POST['ID']) || !is_numeric($_POST['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
  else {
    $_SESSION['Session_Ticket'] = $_POST;
    $_SESSION['Session_Ticket_Files'] = $_FILES;
  }
}?>
