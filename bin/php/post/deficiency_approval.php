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
    if(isset($_POST['Approval']) && in_array($_POST['Approval'],array(
      "Building",
      "Code",
      "Executive",
      "Contract"
    ))){
      $r = $database->query(null,
        " SELECT  Deficiency.ID AS ID,
                  Deficiency.Elevator_Part AS Part,
                  Deficiency.Violation AS Condition,
                  Deficiency.Remedy AS Remedy,
                  Job.Elev      AS Unit,
                  Loc.Tag AS Location
          FROM    Portal.dbo.Deficiency
                  LEFT JOIN nei.dbo.Job ON Deficiency.Job = Job.ID
                  LEFT JOIN nei.dbo.Loc ON Loc.Loc = Job.Loc
          WHERE   Deficiency.ID = ?
        ;",array($_POST['ID']));
      if($r){
        $row = sqlsrv_fetch_array($r);
        if($_POST['Approval'] == 'Contract' && is_array($row)){
          $database->query(null,"INSERT INTO Portal.dbo.Contract_Category_Item(Unit, Elevator_Part, Condition, Remedy, Covered) VALUES(?, ?, ?, ?, ?);",array($row['Unit'],$row['Part'],$row['Condition'],$row['Remedy'],1));
          $database->query(null,"INSERT INTO Portal.dbo.Reviewed_Category_Item(Deficiency, [User], Approval, Responsibility) VALUES(?, ?, ?, ?);",array($_POST['ID'],$_SESSION['User'],1,'Contract'));
        } elseif($_POST['Approval'] == 'Executive' && is_array($row)){
          $database->query(null,"INSERT INTO Portal.dbo.Reviewed_Category_Item(Deficiency, [User], Approval, Responsibility) VALUES(?, ?, ?, ?);",array($_POST['ID'],$_SESSION['User'],1,'Executive'));
        } elseif($_POST['Approval'] == 'Code' && is_array($row)){
          $Code_Division_Heads = array(
            'DIVISION #1' => 1306,
            'DIVISION #2' => 1136,
            'DIVISION #3' => 1187,
            'DIVISION #4' => 1272
          );
          $resource = $database->query(null,"SELECT Zone.Name FROM nei.dbo.Job LEFT JOIN nei.dbo.Loc ON Job.Loc = Loc.Loc LEFT JOIN nei.dbo.Zone ON Loc.Zone = Zone.ID WHERE Job.ID = ?",array($_POST['ID']));
          if($resource){
            $Code_Division_Head = $Code_Division_Heads[sqlsrv_fetch_array($resource)['Name']];
            $database->query(null,"INSERT INTO Portal.dbo.Reviewed_Category_Item(Deficiency, [User], Approval, Responsibility) VALUES(?, ?, ?, ?);",array($_POST['ID'],$_SESSION['User'],0,'Code'));
          }
        } elseif($_POST['Approval'] == 'Building' && is_array($row)){
          $resource = $database->query(null,"SELECT Phone.Email FROM nei.dbo.Phone LEFT JOIN nei.dbo.Rol ON Phone.Rol = Rol.ID WHERE Rol.Name = ? AND Phone.fDesc LIKE '%E-Filing%';",array($row['Location']));
          $Responsibility = 'Building';
          $database->query(null,"INSERT INTO Portal.dbo.Reviewed_Category_Item(Deficiency, [User], Approval, Responsibility) VALUES(?, ?, ?, ?);",array($_POST['ID'],$_SESSION['User'],0,$Responsibility));
        } elseif($_POST['Approval'] == '' && is_array($row)){
          $database->query(null,"INSERT INTO Portal.dbo.Reviewed_Category_Item(Deficiency, [User], Approval, Responsibility) VALUES(?, ?, ?, ?);",array($_POST['ID'],$_SESSION['User'],0,''));
        }
      }
    }
  }
}?>
