<?php
session_start();
require('../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
  $array = sqlsrv_fetch_array($r);
  $Privileged = FALSE;
  if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Illinois'){
      $r = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE ID = ?",array($_SESSION['User']));
      $My_User = sqlsrv_fetch_array($r);
      $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
      $r = sqlsrv_query($NEI,"
          SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
          FROM   Privilege
          WHERE  User_ID = ?
      ;",array($_SESSION['User']));
      $My_Privileges = array();
      while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
      $Privileged = FALSE;
      if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 6){$Privileged = TRUE;}
  }
  if(!$Privileged
    || count($_POST) == 0
    || !isset($_POST['Location'],$_POST['Unit'],$_POST['Job'])
    || !is_numeric($_POST['Location'])
    || strlen($_POST['Date']) == 0
    || !is_numeric($_POST['Job'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
  else {
    $r = sqlsrv_query($NEI,
    " SELECT Max(Tickets.ID) AS ID
      FROM (
        (SELECT Max(ID) AS ID FROM TicketO)
        UNION ALL
        (SELECT Max(ID) AS ID FROM TicketD)
      ) AS Tickets;
    ");
    $r2 = sqlsrv_query($NEI,"SELECT * FROM Loc WHERE Loc.Loc = ?",array($_POST['Location']));
    if($r2){$Location = sqlsrv_fetch_array($r2);}
    if($r){$ID = sqlsrv_fetch_array($r)['ID'] + 1;}
    $_POST['Date'] = date("Y-m-d 00:00:00.000",strtotime($_POST['Date']));
    if(isset($ID) && is_numeric($ID) && $ID > 0 && is_array($Location)){
      $values = array_fill(0, count(array($ID, date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), $_POST['Date'], 1, $My_User['fWork'], 0, 0, NULL, $_POST['Description'], $My_User['fFirst'] . ' ' . $My_User['Last'], $My_User['fFirst'] . ' ' . $My_User['Last'], 0, $_POST['Location'], $_POST['Unit'], $Location['ID'], $Location['Tag'], 'LDesc3', 'LDesc4', 0, $_POST['Job'], 1, 'City', 'State', '00000', '0', '0', '0', '0', '0', '0', '0', '0', 1, '', '', '', 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL,$ID, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, 0)), '?');
      $values = implode(',',$values);
      $_POST['Level'] = isset($_POST['Level']) && $_POST['Level'] != '' ? $_POST['Level'] : 1;
      sqlsrv_query($NEI,"INSERT INTO TicketO(ID, CDate, DDate, EDate, Level, fWork, DWork, Type, Cat, fDesc, Who, fBy, LType, LID, LElev, LDesc1, LDesc2, LDesc3, LDesc4, Nature, Job, Assigned, City, State, Zip, Owner, Route, Terr,  Latt, fLong, CallIn, SpecType, SpecID, EN, Notes, fGroup, Source, High, Confirmed, Phone, Phone2, PriceL, Locked, Follow, Custom1, Custom2, Custom3, Custom4, Custom5, WorkOrder, TimeRoute, TimeSite, TimeComp, HandheldFieldsUpdated, BRemarks, CPhone, Custom6, Custom7, Custom8, Custom9, Custom10, SMile, EMile, idRolCustomContact, gpsStatus, ResolveSource, Comments, Internet, TFMCustom1, TFMCustom2, TFMCustom3, TFMCustom4, TFMCustom5, Est) VALUES({$values})", array($ID, date("Y-m-d H:i:s"), date("Y-m-d H:i:s"), $_POST['Date'],$_POST['Level'], $My_User['fWork'], $My_User['CallSign'], 0, 'None', $_POST['Description'], 'Who', 'fBy', 0, $_POST['Location'], $_POST['Unit'], $Location['ID'], $Location['Tag'], 'LDesc3', 'LDesc4', 0, $_POST['Job'], 1, 'City', 'NY', '00000', '0', '0', '0', '0', '0', '0', '0', '0', 1, '', '', 'Reference', 0, 0, '(', '(', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, $ID, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', 1, NULL, NULL, NULL, NULL, NULL, 0));
      if( ($errors = sqlsrv_errors() ) != null) {
          foreach( $errors as $error ) {
              echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
              echo "code: ".$error[ 'code']."<br />";
              echo "message: ".$error[ 'message']."<br />";
          }
      } else {
        echo $ID;
      }
    }
  }
}?>
