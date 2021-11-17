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
  if(!$Privileged || count($_POST) == 0 || !isset($_POST['Report'], $_POST['Anonymous'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
  else {
    $Name = $_POST['Anonymous'] == 'true' ? 'Anonymous' : $My_User['fFirst'] . ' ' . $My_User['Last'];
    $database->query(null,"INSERT INTO Portal.dbo.Safety_Report(Name, Report) VALUES(?, ?);",array($Name, $_POST['Report']));
    $ID = sqlsrv_fetch_array($database->query(null,"SELECT Max(ID) AS ID FROM Portal.dbo.Safety_Report;"))['ID'];
    /*Email*/
    $_SERVER['SERVER_NAME'] = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : "Nouveau_Elevator_Portal";
    function generateMessageID()
    {
      return sprintf(
        "<%s.%s@%s>",
        base_convert(microtime(), 10, 36),
        base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36),
        $_SERVER['SERVER_NAME']
      );
    }
    $to = "psperanza@nouveauelevator.com";
    $from = "WebServices@NouveauElevator.com";
    $replyto = $from;
    $date = date("Y-m-d H:i:s");
    $subject = "Safety Report #{$ID}";
    $message = "<html>
<head>
<style>
td {padding:5px;}
tr {border-bottom:#555555;}
</style>
</head>
<body>
<table width='500px' style='background-color:#353535;color:white;'><tbody>
<tr><td colspan='2' style='font-size:18px;background-color:#252525;'><img src='https://www.nouveauelevator.com/Images/Icons/logo.png' width='25px' /> Nouveau Elevator</td></tr>
<tr><td colspan='2' style='text-decoration:underline;font-weight:bold;font-size:18px;background-color:#252525;'>Safety Report #{$ID}</td></tr>
<tr><td style='font-weight:bold;'>Name:</td><td>{$Name}</td></tr>
<tr><td style='font-weight:bold;'>Report:</td><td>{$_POST['Report']}</td></tr>
</tbody></table>
</body>
</html>";
    $Arranger = "WebServices";

    $headers = array();
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: text/html; charset=UTF-8";
    $headers[] = "Mesaage-id: " .generateMessageID();
    $headers[] = "From: 'WebServices' <$from>";
    $headers[] = "Reply-To: $Arranger <$replyto>";
    $headers[] = "Date: $date";
    $headers[] = "Return-Path: <$from>";
    $headers[] = "X-Priority: 3";//1 = High, 3 = Normal, 5 = Low
    $headers[] = "X-Mailer: PHP/" . phpversion();
    //$_SESSION['Email'] = $_POST['Email'];
    mail($to, $subject, $message, implode("\r\n", $headers));
  }
}?>
