<?php
session_start();
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php"));
        $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Ticket']['Group_Privilege'] >= 4){
            $r = sqlsrv_query(  $NEI,"SELECT LID FROM nei.dbo.TicketO WHERE TicketO.ID='{$_POST['ID']}'");
            $r2 = sqlsrv_query( $NEI,"SELECT Loc FROM nei.dbo.TicketD WHERE TicketD.ID='{$_POST['ID']}'");
            $r3 = sqlsrv_query( $NEI,"SELECT Loc FROM nei.dbo.TicketDArchive WHERE TicketDArchive.ID='{$_POST['ID']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
            $r3 = sqlsrv_fetch_array($r3);
            $Location = NULL;
            if(is_array($r)){$Location = $r['LID'];}
            elseif(is_array($r2)){$Location = $r2['Loc'];}
            elseif(is_array($r3)){$Location = $r3['Loc'];}
            if(!is_null($Location)){
                $r = sqlsrv_query(  $NEI,"SELECT ID FROM nei.dbo.TicketO WHERE TicketO.LID='{$Location}' AND fWork='{$My_User['fWork']}'");
                $r2 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketD WHERE TicketD.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
                $r3 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
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
                    $r = sqlsrv_query(  $NEI,"SELECT ID FROM nei.dbo.TicketO WHERE TicketO.ID='{$_POST['ID']}' AND fWork='{$User['fWork']}'");
                    $r2 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketD WHERE TicketD.ID='{$_POST['ID']}' AND fWork='{$User['fWork']}'");
                    $r3 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketDArchive WHERE TicketDArchive.ID='{$_POST['ID']}' AND fWork='{$User['fWork']}'");
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
            $r  = sqlsrv_query( $NEI,"SELECT Loc.Loc FROM nei.dbo.TicketO        LEFT JOIN nei.dbo.Loc ON TicketO.LID        = Loc.Loc WHERE TicketO.ID=?        AND Loc.Owner = ?;",array($_POST['ID'],$_SESSION['Branch_ID']));
            $r2 = sqlsrv_query( $NEI,"SELECT Loc.Loc FROM nei.dbo.TicketD        LEFT JOIN nei.dbo.Loc ON TicketD.Loc        = Loc.Loc WHERE TicketD.ID=?        AND Loc.Owner = ?;",array($_POST['ID'],$_SESSION['Branch_ID']));
            $r3 = sqlsrv_query( $NEI,"SELECT Loc.Loc FROM nei.dbo.TicketDArchive LEFT JOIN nei.dbo.Loc ON TicketDArchive.Loc = Loc.Loc WHERE TicketDArchive.ID=? AND Loc.Owner = ?;",array($_POST['ID'],$_SESSION['Branch_ID']));
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
      $sQuery = "SELECT *
       FROM nei.dbo.TicketDPDA
            LEFT JOIN Emp ON TicketDPDA.fWork = Emp.fWork
       WHERE Emp.ID = ?
             AND TicketDPDA.ID = ?;";
      $params = array($_SESSION['User'],intval($_POST['ID']));
      //echo $sQuery;
      //var_dump($params);
      $r = sqlsrv_query($NEI, $sQuery, $params);
      $array = sqlsrv_fetch_array($r);
      //echo 'there';
      //var_dump($array);
      if(is_numeric($_POST['Regular']) && is_numeric($_POST['Overtime']) && is_numeric($_POST['Doubletime']) && is_numeric($_POST['NightDiff']) && is_array($array) && count($array) > 0){
        $total = $_POST['Regular'] + $_POST['Overtime'] + $_POST['Doubletime'] + $_POST['NightDiff'];
        sqlsrv_query($NEI,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.Total = ?, TicketDPDA.Reg = ?, TicketDPDA.OT = ?, TicketDPDA.DT = ? WHERE TicketDPDA.ID = ?",array($total, $_POST['Regular'],$_POST['Overtime'],$_POST['Doubletime'],$_POST['ID']));
      }
      if(isset($_POST['CarExpenses'])){
        sqlsrv_query($NEI,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.Zone = ? WHERE TicketDPDA.ID = ?;",array($_POST['CarExpenses'],$_POST['ID']));
      }
      if(isset($_POST['OtherExpenses'])){
        sqlsrv_query($NEI,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.OtherE = ? WHERE TicketDPDA.ID = ?;",array($_POST['OtherExpenses'],$_POST['ID']));
      }
      if(isset($_POST['Chargeable']) && $_POST['Chargeable'] == True){
        sqlsrv_query($NEI,"UPDATE nei.dbo.TicketDPDA SET TicketDPDA.Charge = ? WHERE TicketDPDA.ID = ?;",array(1,$_POST['ID']));
      }
      if(isset($_POST['Latitude'],$_POST['Longitude'])){
        sqlsrv_query($NEI,"INSERT INTO nei.dbo.TechLocation(TicketID, TechID, ActionGroup, Action, Latitude, Longitude, Altitude, Accuracy, DateTimeRecorded) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?);",
        array($_POST['ID'],  $My_User['fWork'], "Completed time", "Updated completed time to " . date("h:i A"), $_POST['Latitude'], $_POST['Longitude'], 0, 0, date("Y-m-d H:i:s")));
      }
    }
}?>
