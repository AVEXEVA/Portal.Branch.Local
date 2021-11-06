<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php"));
        $r = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Ticket']['Group_Privilege'] >= 4){
            $r = $database->query(  null,"SELECT LID FROM nei.dbo.TicketO WHERE TicketO.ID='{$_POST['ID']}'");
            $r2 = $database->query( null,"SELECT Loc FROM nei.dbo.TicketD WHERE TicketD.ID='{$_POST['ID']}'");
            $r3 = $database->query( null,"SELECT Loc FROM nei.dbo.TicketDArchive WHERE TicketDArchive.ID='{$_POST['ID']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
            $r3 = sqlsrv_fetch_array($r3);
            $Location = NULL;
            if(is_array($r)){$Location = $r['LID'];}
            elseif(is_array($r2)){$Location = $r2['Loc'];}
            elseif(is_array($r3)){$Location = $r3['Loc'];}
            if(!is_null($Location)){
                $r = $database->query(  null,"SELECT ID FROM nei.dbo.TicketO WHERE TicketO.LID='{$Location}' AND fWork='{$My_User['fWork']}'");
                $r2 = $database->query( null,"SELECT ID FROM nei.dbo.TicketD WHERE TicketD.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
                $r3 = $database->query( null,"SELECT ID FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
                if($r || $r2 || $r3){
                    if($r){$a = sqlsrv_fetch_array($r);}
                    if($r2){$a2 = sqlsrv_fetch_array($r2);}
                    if($r3){$a3 = sqlsrv_fetch_array($r3);}
                    if($a || $a2 || $a3){
                        $Privileged = true;
                    }
                }
            }
            if(!$Privileged){
                if($My_Privileges['Ticket']['User_Privilege'] >= 4 && is_numeric($_POST['ID'])){
                    $r = $database->query(  null,"SELECT ID FROM nei.dbo.TicketO WHERE TicketO.ID='{$_POST['ID']}' AND fWork='{$User['fWork']}'");
                    $r2 = $database->query( null,"SELECT ID FROM nei.dbo.TicketD WHERE TicketD.ID='{$_POST['ID']}' AND fWork='{$User['fWork']}'");
                    $r3 = $database->query( null,"SELECT ID FROM nei.dbo.TicketDArchive WHERE TicketDArchive.ID='{$_POST['ID']}' AND fWork='{$User['fWork']}'");
                    if($r || $r2 || $r3){
                        if($r){$a = sqlsrv_fetch_array($r);}
                        if($r2){$a2 = sqlsrv_fetch_array($r2);}
                        if($r3){$a3 = sqlsrv_fetch_array($r3);}
                        if($a || $a2 || $a3){
                            $Privileged = true;
                        }
                    }
                }
            }
        }
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_POST['ID'])){
            $r  = $database->query( null,"SELECT Loc.Loc FROM nei.dbo.TicketO        LEFT JOIN nei.dbo.Loc ON TicketO.LID        = Loc.Loc WHERE TicketO.ID=?        AND Loc.Owner = ?;",array($_POST['ID'],$_SESSION['Branch_ID']));
            $r2 = $database->query( null,"SELECT Loc.Loc FROM nei.dbo.TicketD        LEFT JOIN nei.dbo.Loc ON TicketD.Loc        = Loc.Loc WHERE TicketD.ID=?        AND Loc.Owner = ?;",array($_POST['ID'],$_SESSION['Branch_ID']));
            $r3 = $database->query( null,"SELECT Loc.Loc FROM nei.dbo.TicketDArchive LEFT JOIN nei.dbo.Loc ON TicketDArchive.Loc = Loc.Loc WHERE TicketDArchive.ID=? AND Loc.Owner = ?;",array($_POST['ID'],$_SESSION['Branch_ID']));
            if($r || $r2 || $r3){
                if($r){$a = sqlsrv_fetch_array($r);}else{$a = false;}
                if($r2){$a2 = sqlsrv_fetch_array($r2);}else{$a2 = false;}
                if($r3){$a3 = sqlsrv_fetch_array($r3);}else{$a3 = false;}
                if($a || $a2 || $a3){
                    $Privileged = true;
                }
            }
    }
    if(!isset($array['ID'],$_POST['ID'])  || !$Privileged && is_numeric($_POST['ID'])){?><html><head></head></html><?php }
    else {
      $database->query(null,"UPDATE nei.dbo.TicketO SET TicketO.Assigned = 6 WHERE TicketO.ID = ?",array($_POST['ID']));
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
        $On_Site = date("H:i A",strtotime($Ticket['TimeSite']));
        $Completed = date("H:i A",strtotime($Ticket['TimeComp']));
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
      }
    }
}?>
