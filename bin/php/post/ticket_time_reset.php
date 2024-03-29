<?php
session_start( [ 'read_and_close' => true ] );
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Illinois'){
        $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php"));
        $r = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query(null,"
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['Owner'] >= 4 && $My_Privileges['Ticket']['Group'] >= 4 && $My_Privileges['Location']['Other'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Ticket']['Group'] >= 4){
            $r = $database->query(  null,"SELECT LID FROM TicketO WHERE TicketO.ID='{$_POST['ID']}'");
            $r2 = $database->query( null,"SELECT Loc FROM TicketD WHERE TicketD.ID='{$_POST['ID']}'");
            $r3 = $database->query( null,"SELECT Loc FROM TicketDArchive WHERE TicketDArchive.ID='{$_POST['ID']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
            $r3 = sqlsrv_fetch_array($r3);
            $Location = NULL;
            if(is_array($r)){$Location = $r['LID'];}
            elseif(is_array($r2)){$Location = $r2['Loc'];}
            elseif(is_array($r3)){$Location = $r3['Loc'];}
            if(!is_null($Location)){
                $r = $database->query(  null,"SELECT ID FROM TicketO WHERE TicketO.LID='{$Location}' AND fWork='{$My_User['fWork']}'");
                $r2 = $database->query( null,"SELECT ID FROM TicketD WHERE TicketD.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
                $r3 = $database->query( null,"SELECT ID FROM TicketDArchive WHERE TicketDArchive.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
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
                if($My_Privileges['Ticket']['Owner'] >= 4 && is_numeric($_POST['ID'])){
                    $r = $database->query(  null,"SELECT ID FROM TicketO WHERE TicketO.ID='{$_POST['ID']}' AND fWork='{$User['fWork']}'");
                    $r2 = $database->query( null,"SELECT ID FROM TicketD WHERE TicketD.ID='{$_POST['ID']}' AND fWork='{$User['fWork']}'");
                    $r3 = $database->query( null,"SELECT ID FROM TicketDArchive WHERE TicketDArchive.ID='{$_POST['ID']}' AND fWork='{$User['fWork']}'");
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
            $r  = $database->query( null,"SELECT Loc.Loc FROM TicketO        LEFT JOIN Loc ON TicketO.LID        = Loc.Loc WHERE TicketO.ID=?        AND Loc.Owner = ?;",array($_POST['ID'],$_SESSION['Branch_ID']));
            $r2 = $database->query( null,"SELECT Loc.Loc FROM TicketD        LEFT JOIN Loc ON TicketD.Loc        = Loc.Loc WHERE TicketD.ID=?        AND Loc.Owner = ?;",array($_POST['ID'],$_SESSION['Branch_ID']));
            $r3 = $database->query( null,"SELECT Loc.Loc FROM TicketDArchive LEFT JOIN Loc ON TicketDArchive.Loc = Loc.Loc WHERE TicketDArchive.ID=? AND Loc.Owner = ?;",array($_POST['ID'],$_SESSION['Branch_ID']));
            if($r || $r2 || $r3){
                if($r){$a = sqlsrv_fetch_array($r);}else{$a = false;}
                if($r2){$a2 = sqlsrv_fetch_array($r2);}else{$a2 = false;}
                if($r3){$a3 = sqlsrv_fetch_array($r3);}else{$a3 = false;}
                if($a || $a2 || $a3){
                    $Privileged = true;
                }
            }
    }
    if(!isset($array['ID'], $_POST['ID']) || !$Privileged || !is_numeric($_POST['ID'])){?><html><head></head></html><?php }
    else {
      $r = $database->query(null,"SELECT * FROM TicketO LEFT JOIN Emp ON TicketO.fWork = Emp.fWork WHERE Emp.ID = ? AND TicketO.ID = ?",array($_SESSION['User'],$_POST['ID']));
      if($r && is_array(sqlsrv_fetch_array($r))){
        $database->query(null,"UPDATE TicketO SET TicketO.TimeRoute = ?, TicketO.TimeSite = ?, TicketO.TimeComp = ?, TicketO.Assigned = 1 WHERE TicketO.ID = ?;",array("1899-12-30 00:00:00.000","1899-12-30 00:00:00.000","1899-12-30 00:00:00.000", $_POST['ID']));
      }
      //$database->query(null,"")
      /*$database->query($Portal_44,
        " INSERT INTO Portal.dbo.Timeline(Entity, [Entity_ID], [Action], Time_Stamp)
          VALUES(?, ?, ?, ?)
        ;",array('Ticket', $_POST['ID'], 'Reset Work', date("Y-m-d H:i:s")));*/
    }
}?>
