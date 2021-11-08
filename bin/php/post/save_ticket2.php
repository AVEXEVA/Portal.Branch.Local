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
          SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
          FROM   Privilege
          WHERE  User_ID = ?
      ;",array($_SESSION['User']));
      $My_Privileges = array();
      while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
      $Privileged = FALSE;
      if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 6){$Privileged = TRUE;}
  }
  if(!$Privileged || count($_POST) == 0 || !isset($_POST['ID']) || !is_numeric($_POST['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
  else {
    /*Create TicketDPDA*/
    $r = $database->query(null,"SELECT * FROM nei.dbo.TicketO LEFT JOIN Emp ON Emp.fWork = TicketO.fWork WHERE TicketO.ID = ? AND Emp.ID = ?;",array($_POST['ID'], $_SESSION['User']));
    $r2 = $database->query(null,"SELECT * FROM nei.dbo.TicketDPDA WHERE TicketDPDA.ID = ?",array($_POST['ID']));
    $ticket2 = $r2 ? sqlsrv_fetch_array($r2) : null;
    if($r && !is_array($ticket2)){
      $row = sqlsrv_fetch_array($r);
      if(is_array($row)){
        $start = date("Y-m-d H:i:s",strtotime($row['TimeRoute']));
        $end = date("Y-m-d H:i:s",strtotime($row['TimeComp']));
        $hours = substr($end,11,2) - substr($start,11,2);
        $minutes = substr($end,14,2) - substr($start,14,2);
        $total = ($hours * 1) + ($minutes / 60);

        $values = array_fill(0, count(array($_POST['ID'],$row['CDate'], $row['DDate'], $row['EDate'], $row['fWork'], $row['Job'],$row['LID'], $row['LElev'], $row['Type'], $row['fDesc'],  $row['Who'], $row['fBy'], $row['TimeRoute'], $row['TimeSite'], $row['TimeComp'], $row['AID'], 'Test Resolution', 0, 0, 0, 0, 1, 'TFM-A3.60', 0, 0, '', 0, 0 ,0 , 0, 0 ,0 ,0 ,0, date('Y-m-d 00:00:00.000'),  date('Y-m-d 00:00:00.000'), '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, '', 1, 1, 0, 'None', 0, $row['Level'])), '?');
        $values = implode(',',$values);
        $sQuery = "INSERT INTO nei.dbo.TicketDPDA(ID, CDate, DDate, EDate, fWork, Job, Loc, Elev, Type, fDesc, Who, fBy, TimeRoute, TimeSite, TimeComp, AID, DescRes, ClearCheck, ClearPR, Status, Invoice, WorkComplete, ResolveSource, Charge, downtime, Source, Total, Reg, OT, DT, TT, OtherE, SMile, EMile, StartBreak, EndBreak, TFMCustom1, TFMCustom2, TFMCustom3, TFMCustom4, TFMCustom5, idRolCustomContact, Custom6, Custom7, Custom8, Custom9, Custom10, WorkOrder, PriceL, Phase, WageC, Cat, Est, Level) VALUES(" . $values . ");";
        //echo $sQuery;
        $database->query(null,"UPDATE nei.dbo.TicketO SET TicketO.Assigned = 6 WHERE TicketO.ID = ?",array($_POST['ID']));
        $database->query(null, $sQuery, array($_POST['ID'],$row['CDate'], $row['DDate'], $row['EDate'], $row['fWork'], $row['Job'],$row['LID'], $row['LElev'], $row['Type'], $row['fDesc'],  $row['Who'], $row['fBy'], $row['TimeRoute'], $row['TimeSite'], $row['TimeComp'], $row['AID'], 'Test Resolution',0 ,0, 0, 0, 1, 'TFM-A3.60', 0, 0, '', $total, 0, 0, 0, 0, 0, 0, 0,  date('Y-m-d 00:00:00.000'),  date('Y-m-d 00:00:00.000'), '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, '', 1, 1, 0, 'None', 0, $row['Level']));
        $database->query(null,"UPDATE Portal.dbo.Ticket SET Ticket.TimeComp = ? WHERE Ticket.ID = ?;",array(date('Y-m-d H:i:s'), $_POST['ID']));
      }
    } elseif($r && is_array($ticket2)){
      $row = sqlsrv_fetch_array($r);
      if(is_array($row)){
        $start = date("Y-m-d H:i:s",strtotime($row['TimeRoute']));
        $end = date("Y-m-d H:i:s",strtotime($row['TimeComp']));
        $hours = substr($end,11,2) - substr($start,11,2);
        $minutes = substr($end,14,2) - substr($start,14,2);
        $total = ($hours * 1) + ($minutes / 60);
        $database->query(null,"UPDATE nei.dbo.TicketO SET TicketO.Assigned = 6 WHERE TicketO.ID = ?",array($_POST['ID']));
        $database->query(null,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.Total = ?, TicketDPDA.TimeComp = ? WHERE TicketDPDA.ID = ?",array($total, $row['TimeComp'],$_POST['ID']));
      }
    }
    $r = $database->query(null,"SELECT * FROM nei.dbo.TicketO LEFT JOIN Emp ON Emp.fWork = TicketO.fWork WHERE TicketO.ID = ? AND Emp.ID = ?;",array($_POST['ID'],$_SESSION['User']));
    $r2 = $database->query(null,"SELECT * FROM nei.dbo.TicketDPDA WHERE TicketDPDA.ID = ?;",array($_POST['ID']));
    if($r && is_array(sqlsrv_fetch_array($r)) && $r2 && is_array(sqlsrv_fetch_array($r2))){
      /*Complete Ticket to Review*/
      $database->query(null,"UPDATE nei.dbo.TicketO SET TicketO.Assigned = 6 WHERE TicketO.ID = ?",array($_POST['ID']));
      /*Receipt*/
      /*$r = $database->query($Portal,"SELECT * FROM Portal.dbo.[File] WHERE [File].Ticket = ?",array($_POST['ID']));
      if(is_array(sqlsrv_fetch_array($r)) && FALSE) {
        $r = $database->query($Portal,"UPDATE Portal.dbo.[File] SET [File].Name = ?, [File].[Type] = ?, [File].[Data] = ?, [File].[User] = ? WHERE [File].[Ticket] = ?",array($_FILES['Receipt']['name'], $_FILES['Receipt']['type'], base64_encode(file_get_contents($_FILES['Receipt']['tmp_name'])), $_SESSION['User'], $_POST['ID']));
      } else {
        $r = $database->query($Portal,"INSERT INTO Portal.dbo.[File](Name, [Type], [Data], [User], [Ticket]) VALUES(?, ?,  ?, ?, ?);",array($_FILES['Receipt']['name'], $_FILES['Receipt']['type'], base64_encode(file_get_contents($_FILES['Receipt']['tmp_name'])), $_SESSION['User'], $_POST['ID']));
        $r = $database->query(null,"INSERT INTO nei.dbo.TicketPic(TicketID, PicData, ModifiedOn, PictureName, PictureComments, EmailPicture) VALUES(?, ?, ?, ?, ?, ?)", array($_POST['ID'], base64_encode(file_get_contents($_FILES['Receipt']['tmp_name'])), date("Y-m-d H:i:s"), NULL, NULL, 0));
      }*/
      if(isset($_FILES['Receipt']) && count($_FILES['Receipt']) > 0){
        $Count = count($_FILES['Receipt']['tmp_name']);
        $index = 0;
        while( $index < $Count ){
          if(isset($_FILES['Receipt']['tmp_name'][ $index ]) && strlen($_FILES['Receipt']['tmp_name'][ $index ]) > 0){
            ob_start();
            $image = imagecreatefromstring(file_get_contents($_FILES['Receipt']['tmp_name'][ $index ]));
            imagejpeg($image, null, 50);
            $image = ob_get_clean();
            $image = base64_encode($image);
            $File_Name = 'nei_TCK'. $_POST['ID'] . '_' . rand(0,9999999) . '_' . $index;
            /*$r = $database->query($Portal,"SET TEXTSIZE 2147483647;INSERT INTO Portal.dbo.[File](Name, [Type], [Data], [User], [Ticket]) VALUES(?, ?,  ?, ?, ?);",array($_FILES['Receipt']['name'][ $index ], $_FILES['Receipt']['type'][ $index ], array($image, SQLSRV_PARAM_IN,
          SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),SQLSRV_SQLTYPE_VARBINARY('max')), $_SESSION['User'], $_POST['ID']));*/
            $r = $database->query(null,"SET TEXTSIZE 2147483647;INSERT INTO TicketPic(TicketID, PicData, ModifiedOn, PictureName, PictureComments, EmailPicture) VALUES(?, ?, ?, ?, ?, ?)", array($_POST['ID'], array($image, SQLSRV_PARAM_IN,
          SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),SQLSRV_SQLTYPE_VARBINARY('max')), date("Y-m-d H:i:s"), $File_Name, NULL, 0)); 
          }
          $index++;
        }
      }
      /*Time*/
      //var_dump($_FILES['Receipt']);
      if(is_numeric($_POST['Regular']) && is_numeric($_POST['Overtime']) && is_numeric($_POST['Doubletime']) && is_numeric($_POST['NightDiff']) && is_array($array) && count($array) > 0){
        $total = $_POST['Regular'] + $_POST['Overtime'] + $_POST['Doubletime'] + $_POST['NightDiff'];
        $database->query(null,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.Total = ?, TicketDPDA.Reg = ?, TicketDPDA.OT = ?, TicketDPDA.DT = ?, TicketDPDA.TT = ? WHERE TicketDPDA.ID = ?",array($total, $_POST['Regular'],$_POST['Overtime'],$_POST['Doubletime'],$_POST['NightDiff'],$_POST['ID']));
      }
      /*Expenses*/
      function toInt($str)
      {
          return preg_replace("/([^0-9\\.])/i", "", $str);
      }
      if(isset($_POST['CarExpenses'])){
        $_POST['CarExpenses'] = toInt($_POST['CarExpenses']);
        $database->query(null,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.Zone = ? WHERE TicketDPDA.ID = ?;",array($_POST['CarExpenses'],$_POST['ID']));
      }
      if(isset($_POST['OtherExpenses'])){
        $_POST['OtherExpenses'] = toInt($_POST['OtherExpenses']);
        $database->query(null,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.OtherE = ? WHERE TicketDPDA.ID = ?;",array($_POST['OtherExpenses'],$_POST['ID']));
      }
      if(isset($_POST['Chargeable']) && $_POST['Chargeable'] == 'true'){
        $database->query(null,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.Charge = ? WHERE TicketDPDA.ID = ?;",array(1,$_POST['ID']));
      }
      if(isset($_POST['Follow_Up']) && $_POST['Follow_Up'] == 'true'){
        $database->query(null,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.WorkComplete = ? WHERE TicketDPDA.ID = ?;",array(0,$_POST['ID']));
      } elseif(isset($_POST['Follow_Up'])) {
        $database->query(null,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.WorkComplete = ? WHERE TicketDPDA.ID = ?;",array(1,$_POST['ID']));
      }
      /*GPS*/
      if(isset($_POST['Latitude'],$_POST['Longitude'])){
        $database->query(null,"INSERT INTO nei.dbo.TechLocation(TicketID, TechID, ActionGroup, Action, Latitude, Longitude, Altitude, Accuracy, DateTimeRecorded) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?);",
        array($_POST['ID'],  $My_User['fWork'], "Completed time", "Updated completed time to " . date("h:i A"), $_POST['Latitude'], $_POST['Longitude'], 0, 0, date("Y-m-d H:i:s")));
      }
      /*Signature*/
      if(isset($_POST['Signature_Canvas'])){
        $img = $_POST['Signature_Canvas'];
        $img = str_replace('data:image/jpeg;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);
        file_put_contents("../../../media/images/signatures/{$_POST['ID']}.jpg",$data);
        $data = file_get_contents("../../../media/images/signatures/{$_POST['ID']}.jpg");
        $r = $database->query(null,"SELECT * FROM PDATicketSignature WHERE PDATicketSignature.PDATicketID = ?;",array($_POST['ID']));
        if($r && is_array(sqlsrv_fetch_array($r))){
          $database->query(null, "SET TEXTSIZE 10000000;UPDATE nei.dbo.PDATicketSignature SET PDATicketSignature.Signature = ? WHERE PDATicketSignature.PDATicketID = ?;",array(array($data, SQLSRV_PARAM_IN,
        SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),SQLSRV_SQLTYPE_VARBINARY('max')),$_POST['ID']));
        } else {
          $database->query(null, "SET TEXTSIZE 10000000;INSERT INTO nei.dbo.PDATicketSignature(PDATicketID, Signature, SignatureType) VALUES(?,?,'C');",array($_POST['ID'],array($data, SQLSRV_PARAM_IN,
        SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),SQLSRV_SQLTYPE_VARBINARY('max'))));
        }
        $r = $database->query(null,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.ID = ? AND TicketO.WorkOrder IS NOT NULL AND TicketO.WorkOrder <> '';",array($_POST['ID']));
        $ticket = sqlsrv_fetch_array($r);
        if(is_array($ticket) && isset($_POST['Signature_Work_Order']) && strtolower($_POST['Signature_Work_Order']) != 'false'){
          $r = $database->query(null,"SELECT * FROM nei.dbo.TicketO WHERE TicketO.WorkOrder = ?;",array($ticket['WorkOrder']));
          if($r){while($row = sqlsrv_fetch_array($r)){
            $data = file_get_contents("../../../media/images/signatures/{$_POST['ID']}.jpg");
            file_put_contents("../../../media/images/signatures/{$row['ID']}.jpg",$data);
            $r5 = $database->query(null,"SELECT * FROM PDATicketSignature WHERE PDATicketSignature.PDATicketID = ?;",array($row['ID']));
            if($r5 && is_array(sqlsrv_fetch_array($r5))){
              $database->query(null, "SET TEXTSIZE 10000000;UPDATE nei.dbo.PDATicketSignature SET PDATicketSignature.Signature = ? WHERE PDATicketSignature.PDATicketID = ?;",array(array($data, SQLSRV_PARAM_IN,
            SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),SQLSRV_SQLTYPE_VARBINARY('max')),$row['ID']));
            } else {
              $database->query(null, "SET TEXTSIZE 10000000;INSERT INTO nei.dbo.PDATicketSignature(PDATicketID, Signature, SignatureType) VALUES(?,?,'C');",array($row['ID'],array($data, SQLSRV_PARAM_IN,
            SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),SQLSRV_SQLTYPE_VARBINARY('max'))));
            }
          }}
        }
      }
      /*Resolution and SignatureText*/
      if(isset($_POST['Signature_Text'],$_POST['Resolution'])){
        $sQuery = "UPDATE nei.dbo.TicketDPDA SET TicketDPDA.DescRes = ?, TicketDPDA.SignatureText = ? WHERE TicketDPDA.ID = ?;";
        $params = array($_POST['Resolution'],$_POST['Signature_Text'], $_POST['ID']);
        $database->query(null, $sQuery, $params);
      }

      /*Email*/
      if(isset($_POST['Email']) && strlen($_POST['Email']) > 0){
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
        $r = $database->query(null,"
          SELECT  TicketDPDA.*,
                  OwnerWithRol.Name AS Customer,
                  Loc.Tag AS Location,
                  Job.fDesc AS Job,
                  Elev.Unit AS Unit,
                  Emp.fFirst + ' ' + Emp.Last AS Worker
          FROM    nei.dbo.TicketDPDA
                  LEFT JOIN nei.dbo.Job           ON TicketDPDA.Job   = Job.ID
                  LEFT JOIN nei.dbo.OwnerWithRol  ON Job.OWner        = OwnerWithRol.ID
                  LEFT JOIN nei.dbo.Loc           ON TicketDPDA.Loc   = Loc.Loc
                  LEFT JOIN nei.dbo.Elev          ON TicketDPDA.Elev  = Elev.ID
                  LEFT JOIN Emp           ON TicketDPDA.fWork = Emp.fWork
          WHERE   TicketDPDA.ID = ?
        ;",array($_POST['ID']));

        $Ticket = $r ? sqlsrv_fetch_array($r) : Null;

        $r = $database->query(null,"SELECT * FROM PDATicketSignature WHERE PDATicketSignature.PDATicketID = ?;",array($_POST['ID']));
        $signature = $r ? sqlsrv_fetch_array($r)['Signature'] : Null;
        $to = $_POST['Email'];
        $from = "WebServices@NouveauElevator.com";
        $replyto = $from;
        $date = date("Y-m-d H:i:s");
        $subject = "Assistance: Ticket #{$_POST['ID']}";
        $On_Site = date("h:i A",strtotime($Ticket['TimeSite']));
        $Completed = date("h:i A",strtotime($Ticket['TimeComp']));
        $Ticket['EDate'] = date("m/d/Y h:i A",strtotime($Ticket['EDate']));
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
<tr><td colspan='2' style='text-decoration:underline;font-weight:bold;font-size:18px;background-color:#252525;'>Ticket #{$_POST['ID']}</td></tr>
<tr><td style='font-weight:bold;'>Location:</td><td>{$Ticket['Location']}</td></tr>
<tr><td style='font-weight:bold;'>Unit:</td><td>{$Ticket['Unit']}</td></tr>
<tr><td style='font-weight:bold;'>Worker:</td><td>{$Ticket['Worker']}</td></tr>
<tr><td style='font-weight:bold;'>Description:</td><td>{$Ticket['fDesc']}</td></tr>
<tr><td style='font-weight:bold;'>Date:</td><td>{$Ticket['EDate']}</td></tr>
<tr><td style='font-weight:bold;'>Accepted:</td><td>{$On_Site}</td></tr>
<tr><td style='font-weight:bold;'>Completed:</td><td>{$Completed}</td></tr>
<tr><td style='font-weight:bold;'>Regular:</td><td>{$Ticket['Reg']}</td></tr>
<tr><td style='font-weight:bold;'>Differential:</td>{$Ticket['TT']}</td></tr>
<tr><td style='font-weight:bold;'>Overtime:</td><td>{$Ticket['OT']}</td></tr>
<tr><td style='font-weight:bold;'>Doubletime:</td><td>{$Ticket['DT']}</td></tr>
<tr><td style='font-weight:bold;'>Total</td><td>{$Ticket['Total']}</td></tr>
<tr><td style='font-weight:bold;'>Resolution:</td><td/>{$Ticket['DescRes']}</td></tr>
<tr><td style='font-weight:bold;'>Signee:</td><td>{$Ticket['SignatureText']}</td></tr>
<tr><td colspan='2' style='background-color:white;text-align:center;padding:25px;'><img style='-webkit-filter: invert(1);filter: invert(1);' src='https://www.nouveauelevator.com/portal/media/images/signatures/{$_POST['ID']}.jpg' /></td></tr>
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


        $r = $database->query($Portal,"SELECT * FROM Ticket_Email WHERE Ticket_Email.Ticket = ? AND Ticket_Email.Email = ?;",array($_POST['ID'],$_POST['Email']));
        if($r && is_array(sqlsrv_fetch_array($r))){
          $database->query($Portal,"INSERT INTO Ticket_Email(Ticket, Email) VALUES(?, ?);",array($_POST['ID'],$_POST['Email']));
        } else {
          $database->query($Portal,"INSERT INTO Ticket_Email(Ticket, Email) VALUES(?, ?);",array($_POST['ID'],$_POST['Email']));
        }
      }
    }
  }
}?>
