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
      $sQuery = "SELECT *
       FROM nei.dbo.TicketDPDA
            LEFT JOIN Emp ON TicketDPDA.fWork = Emp.fWork
       WHERE Emp.ID = ?
             AND TicketDPDA.ID = ?;";
      $params = array($_SESSION['User'],intval($_POST['ID']));
      $r = $database->query(null, $sQuery, $params);
      $array = sqlsrv_fetch_array($r);
      //var_dump($array);
      if(isset($_POST['Resolution_Code'])){
        $resolutions = array($_POST['Resolution_Code']);
      } else {
        $resolutions = array();
      }
      if(isset($_POST['Custom_Resolution']) && strlen($_POST['Custom_Resolution']) > 0){
        //$resolution = $_POST['Custom_Resolution'];
        array_unshift($resolutions,$_POST['Custom_Resolution']);
      } else {
      }
      $Signature = isset($_POST['Signature']) && strlen($_POST['Signature']) > 0 ? $_POST['Signature'] : '';

      $sQuery = "UPDATE nei.dbo.TicketDPDA SET TicketDPDA.DescRes = ?, TicketDPDA.SignatureText = ? WHERE TicketDPDA.ID = ?;";
      //if(isset($resolution)){
      $database->query(null, $sQuery,array(implode(', ',$resolutions),$Signature, $_POST['ID']));
      //} else {
        //$database->query(null, $sQuery,array(implode(', ',$resolutions),$Signature, $_POST['ID']));
      //}
      //echo $sQuery;
      if( ($errors = sqlsrv_errors() ) != null) {
          foreach( $errors as $error ) {
              echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
              echo "code: ".$error[ 'code']."<br />";
              echo "message: ".$error[ 'message']."<br />";
          }
      }
    }
}?>
