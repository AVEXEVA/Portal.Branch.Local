<?php
session_start( [ 'read_and_close' => true ] );
set_time_limit (30);
require('../index.php');
function distance($lat1, $lon1, $lat2, $lon2, $unit) {
  if (($lat1 == $lat2) && ($lon1 == $lon2)) {
    return 0;
  }
  else {
    $theta = $lon1 - $lon2;
    $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
    $dist = acos($dist);
    $dist = rad2deg($dist);
    $miles = $dist * 60 * 1.1515;
    $unit = strtoupper($unit);

    if ($unit == "K") {
      return ($miles * 1.609344);
    } else if ($unit == "N") {
      return ($miles * 0.8684);
    } else {
      return $miles;
    }
  }
}
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
      if(isset($My_Privileges['Map']) && $My_Privileges['Map']['User_Privilege'] >= 4 && $My_Privileges['Map']['User_Privilege'] >= 4 && $My_Privileges['Map']['User_Privilege'] >= 4){$Privileged = TRUE;}
  }
  if(!$Privileged){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
  else {
    $r = $database->query(null,
      " SELECT  TicketO.ID AS ID,
                TicketO.fDesc AS Description,
                TicketDPDA.TimeRoute,
                TicketDPDA.TimeSite,
                TicketDPDA.TimeComp,
                TicketDPDA.Reg AS Regular,
                TicketDPDA.OT AS Overtime,
                TicketDPDA.DT AS Doubletime,
                TicketDPDA.NT AS Night_Differential,
                TicketDPDA.TT AS Travel_Time,
                Loc.Tag AS Location_Tag,
                Elev.State AS Unit_State,
                Emp.fFirst + ' ' + Emp.Last AS Full_Name,
                Emp.ID AS Employee_ID
        FROM    nei.dbo.TicketO
                LEFT JOIN nei.dbo.TicketDPDA ON TicketDPDA.ID = TicketO.ID
                LEFT JOIN nei.dbo.Loc ON Loc.Loc = TicketO.LID
                LEFT JOIN nei.dbo.Elev ON Elev.ID = TicketO.LElev
                LEFT JOIN Emp ON Emp.fWork = TicketO.fWork
        WHERE   TicketO.Assigned > 0
      ;",array());
    $rows = array();
    if($r){while($row = sqlsrv_fetch_array($r)){
      $rows[$row['Employee_ID']] = isset($rows[$row['Employee_ID']]) && is_array($rows[$row['Employee_ID']]) ? $rows[$row['Employee_ID']] : array();
      $rows[$row['Employee_ID']][$row['ID']] = $row;
    }}
    print json_encode($rows);
  }
}?>
