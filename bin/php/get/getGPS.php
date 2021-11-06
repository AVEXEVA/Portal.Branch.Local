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
    if(isset($_GET['Employee_ID'])){
      $r = $database->query($Portal_44,
        " SELECT TOP 1
                 GPS.ID       AS ID,
                 GPS.Employee_ID AS Employee_ID,
                 GPS.Latitude AS Latitude,
                 GPS.Longitude AS Longitude,
                 GPS.Altitude AS Altitude,
                 GPS.Accuracy AS Accuracy,
                 GPS.Time_Stamp AS Time_Stamp
          FROM   Portal.dbo.GPS
          WHERE  GPS.Employee_ID = ?
          ORDER BY GPS.ID DESC
        ;",array($_GET['Employee_ID']));
      $r2 = $database->query(null,
        " SELECT  Emp.fFirst, Emp.Last, tblWork.Super AS Supervisor, Loc.fLong AS Longitude, Loc.Latt AS Latitude
          FROM    Emp
                  LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                  LEFT JOIN nei.dbo.TicketO ON Emp.fWork = TicketO.fWork AND TicketO.Assigned = 3
                  LEFT JOIN nei.dbo.Loc ON Loc.Loc = TicketO.LID
          WHERE   Emp.ID = ?
        ;",array($_GET['Employee_ID']));
      $row = array();
      if($r && $r2){
        $row  = sqlsrv_fetch_array($r);
        $row2 = sqlsrv_fetch_array($r2);
        if(!is_array($row) || !is_array($row2)){$row = array();}
        else{
          $row['First_Name'] = $row2['fFirst'];
          $row['Last_Name']  = $row2['Last'];
          $row['Supervisor'] = $row2['Super'];
          $row['Time_Stamp'] = date('Y-m-d H:i:s',strtotime('-5 hours',strtotime($row['Time_Stamp'])));
          $row['Title'] = $row['First_Name'] . " " . $row['Last_Name'] . " - " . date('m/d/Y h:i A', strtotime($row['Time_Stamp'])) . " - " . $row['Supervisor'];
          $row['Location_Latitude'] = $row2['Latitude'];
          $row['Location_Longitude'] = $row['Longitude'];
          if(isset($row2['Latitude'], $row2['Longitude'])){
            if(is_null($row2['Latitude']) || is_null($row2['Longitude']) || $row2['Latitude'] == 0 || $row2['Longitude'] == 0){
              $row['Geofence'] = true;
            } elseif(is_numeric($row2['Latitude']) && is_numeric($row2['Longitude']) && distance($row2['Latitude'],$row2['Longitude'],$row['Latitude'],$row['Longitude'], 'M') < 1){
              $row['Geofence'] = true;
            } else {
              $row['Distance'] = distance($row2['Latitude'],$row2['Longitude'],$row['Latitude'],$row['Longitude'], 'M');
              $row['Geofence'] = false;
            }
          } else {
            $row['Geofence'] = true;
          }
        }
      }
      print json_encode($row);
    } else {
      $r = $database->query($Portal_44,
      "   SELECT GPS.ID       AS GPS_ID,
                 GPS.Employee_ID AS Employee_ID,
                 GPS.Latitude AS Latitude,
                 GPS.Longitude AS Longitude,
                 GPS.Altitude AS Altitude,
                 GPS.Accuracy AS Accuracy,
                 Max(GPS.Time_Stamp) AS Time_Stamp
          FROM   Portal.dbo.GPS
          WHERE GPS.Time_Stamp >= ?
          GROUP BY GPS.ID, GPS.Employee_ID, GPS.Latitude, GPS.Longitude, GPS.Altitude, GPS.Accuracy
      ;",array(date("Y-m-d H:i:s",strtotime("-4 hours"))));
      $GPS_Data = array();
      if($r){while($row = sqlsrv_fetch_array($r)){
        $GPS_Data[$row['Employee_ID']] = $row;
      }}
      $r = $database->query(null,
        " SELECT Emp.ID,
                 Emp.fFirst AS First_Name,
                 Emp.Last AS Last_Name,
                 tblWork.Super AS Supervisor,
                 Loc.Latt AS Latitude,
                 Loc.fLong AS Longitude,
                 [Attendance].[Start] AS Attendance_Start,
                 [Attendance].[End] AS Attendance_End
          FROM   Emp
                 LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                 LEFT JOIN nei.dbo.TicketO ON TicketO.fWork = Emp.fWork
                 LEFT JOIN nei.dbo.Loc ON Loc.Loc = TicketO.LID
                 LEFT JOIN Portal.dbo.Attendance ON Emp.ID = [Attendance].[User] AND [Attendance].[End] IS NULL
          WHERE  Emp.Status = 0;");
      if($r){while($row = sqlsrv_fetch_array($r)){
        if(isset($GPS_Data[$row['ID']]) && !isset($GPS_Data[$row['ID']]['Geofence'])){
          $GPS_Data[$row['ID']]['End'] = strlen($row['Attendance_Start']) > 0 ? NULL : '-1';
          $GPS_Data[$row['ID']]['Time_Stamp'] = date('Y-m-d H:i:s',strtotime('-5 hours',strtotime($GPS_Data[$row['ID']]['Time_Stamp'])));
          $GPS_Data[$row['ID']]['First_Name'] = $row['First_Name'];
          $GPS_Data[$row['ID']]['Last_Name'] = $row['Last_Name'];
          $GPS_Data[$row['ID']]['Employee_ID'] = $Row['ID'];
          $GPS_Data[$row['ID']]['Supervisor'] = $row['Supervisor'];
          $GPS_Data[$row['ID']]['Geofence'] = (isset($row['Latitude'],$row['Longitude']) && distance($row['Latitude'],$row['Longitude'],$GPS_Data[$row['ID']]['Latitude'],$GPS_Data[$row['ID']]['Longitude'], 'M') < .5) || $row['Latitude'] == 0 || $row['Longitude'] == 0 || $row['Latitude'] == null || $row['Longitude'] == null ? true : false;
          $GPS_Data[$row['ID']]['Title'] = $row['First_Name'] . ' ' . $row['Last_Name'] . ' - ' . $GPS_Data[$row['ID']]['Time_Stamp'];
        }
      }}
      foreach($GPS_Data AS $id=>$GPS){
        if($GPS['End'] == '-1'){unset($GPS_Data[$id]);}
      }
      print json_encode($GPS_Data);
    }
  }
}?>
