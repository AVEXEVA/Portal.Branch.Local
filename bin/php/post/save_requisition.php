<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
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
      if(isset($My_Privileges['Requisition']) && $My_Privileges['Requisition']['Owner'] >= 6){$Privileged = TRUE;}
  }
  if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
  else {
    $_POST['Required'] = date("Y-m-d 00:00:00.000",strtotime($_POST['Required']));
    $database->query($Portal, "INSERT INTO Portal.dbo.Requisition([User], [Date], [Required], [Location], [DropOff], [Unit], [Job], [Shutdown], [ASAP], [Rush], [LSD], [FRM], [Notes]) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);",array($_SESSION['User'], date("Y-m-d H:i:s"),  $_POST['Required'], $_POST['Location'], $_POST['DropOff'], $_POST['Unit'], $_POST['Job'], $_POST['Shutdown'], $_POST['ASAP'], $_POST['Rush'], $_POST['LSD'], $_POST['FRM'], $_POST['Notes']));
    $r = $database->query($Portal,"SELECT Max(ID) AS ID FROM Portal.dbo.Requisition;");
    $Requisition_ID = sqlsrv_fetch_array($r)['ID'];
    if(isset($_POST['Item']) && is_array($_POST['Item']) && isset($Requisition_ID) && is_numeric($Requisition_ID)){
      $i = 0;
      foreach($_POST['Item'] AS $index=>$array){
        //var_dump($_FILES);
        if(isset($_FILES['Item']['tmp_name'][$i])){
          $data = base64_encode(file_get_contents($_FILES['Item']['tmp_name'][$i]));
          $database->query($Portal, "INSERT INTO Portal.dbo.Requisition_Item(Requisition, Item_Description, Quantity, Image, Image_Type) VALUES(?, ?, ?, ?, ?);", array($Requisition_ID, $array['Comments'], $array['Quantity'], $data, $_FILES['Item']['type'][$i]));
        } else {
          $database->query($Portal, "INSERT INTO Portal.dbo.Requisition_Item(Requisition, Item_Description, Quantity) VALUES(?, ?, ?);", array($Requisition_ID, $array['Comments'], $array['Quantity']));
        }
        $i++;
      }
    }
    $r = $database->query(null,"
      SELECT tblWork.Super AS Supervisor
      FROM   Emp
             LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
      WHERE  Emp.ID = ?
    ;",array($_SESSION['User']));
    $Supervisor = sqlsrv_fetch_array($r)['Supervisor'];
    $Emails = array(
      'Division 3'=>'dcristiano@nouveauelevator.com',
      'OFFICE'=>'psperanza@nouveauelevator.com'
    );
    if(isset($Emails[$Supervisor])){
      function generateMessageID()
      {
        return sprintf(
          "<%s.%s@%s>",
          base_convert(microtime(), 10, 36),
          base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36),
          $_SERVER['SERVER_NAME']
        );
      }
      //Get Location
      $r = $database->query(null,"SELECT Loc.Tag AS Location FROM nei.dbo.Loc WHERE Loc.Loc = ?",array($_POST['Location']));
      $Location = sqlsrv_fetch_array($r)['Location'];

      //Get Drop Off
      $r = $database->query(null,"SELECT Loc.Tag AS DropOff FROM nei.dbo.Loc WHERE Loc.Loc = ?",array($_POST['DropOff']));
      $DropOff = sqlsrv_fetch_array($r)['DropOff'];

      //Get Job
      $r = $database->query(null,"SELECT Job.fDesc AS DropOff FROM nei.dbo.Job WHERE Job.ID = ?",array($_POST['Job']));
      $Job = sqlsrv_fetch_array($r)['DropOff'];

      //Get Unit
      $r = $database->query(null,"SELECT Elev.State AS State, Elev.Unit AS Building_NO FROM nei.dbo.Elev WHERE Elev.ID = ?",array($_POST['Unit']));
      $array = sqlsrv_fetch_array($r);
      $State = $array['State'];
      $Building_NO = $array['Building_NO'];

      $to = $Emails[$Supervisor];
      $from = "WebServices@NouveauElevator.com";
      $replyto = $from;
      $date = date("Y-m-d H:i:s");
      $subject = "Support: Requisition #{$Requisition_ID}";
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
<tr><td colspan='2' style='text-decoration:underline;font-weight:bold;font-size:18px;background-color:#252525;'>Requisition #{$Requisition_ID}</td></tr>
<tr><td style='font-weight:bold;'>Location:</td><td>{$Location}</td></tr>
<tr><td style='font-weight:bold;'>Drop Off:</td><td>{$DropOff}</td></tr>
<tr><td style='font-weight:bold;'>Job:</td><td>{$Job}</td></tr>
<tr><td style='font-weight:bold;'>Unit:</td><td>{$State} - {$Building_NO}</td></tr>
<tr><td style='font-weight:bold;'>Notes:</td><td>{$_POST['Notes']}</td></tr>
<tr><td colspan='2' style='text-decoration:underline;font-weight:bold'><a href='https://www.nouveauelevator.com/portal/requisition.php?ID={$Requisition_ID}'>Link to Portal</a></td></tr>
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
    if(isset($Requisition_ID) && is_numeric($Requisition_ID)){
      echo $Requisition_ID;
    }
  }
}?>
