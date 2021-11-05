<?php
session_start( [ 'read_and_close' => true ] );
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
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Connecticut'){
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
        if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 6){$Privileged = TRUE;}
    }
    if(!isset($array['ID'], $_POST['ID'])  || !$Privileged || !is_numeric($_POST['ID'])){?><html><head></head></html><?php }
    else {
      $r = sqlsrv_query($NEI,"SELECT * FROM TicketO LEFT JOIN Emp ON Emp.fWork = TicketO.fWork WHERE TicketO.ID = ? AND Emp.ID = ?;",array($_POST['ID'],$_SESSION['User']));
      if($r && is_array(sqlsrv_fetch_array($r))){
        /*GPS*/
        if(isset($_POST['Latitude'],$_POST['Longitude'])){
          sqlsrv_query($NEI,"INSERT INTO TechLocation(TicketID, TechID, ActionGroup, Action, Latitude, Longitude, Altitude, Accuracy, DateTimeRecorded) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?);",
          array($_POST['ID'],  $My_User['fWork'], "On site time", "Updated on site time to " . date("h:i A"), $_POST['Latitude'], $_POST['Longitude'], 0, 0, date("Y-m-d H:i:s")));
        }
        $r = sqlsrv_query($NEI,
        "   SELECT *
            FROM  TicketO
            WHERE TicketO.ID = ?
                  AND TicketO.fWork = ?
                  AND ( TicketO.TimeRoute = '1899-12-30 00:00:00.000'
                        OR TicketO.TimeRoute IS NULL)
        ;",array($_POST['ID'],$_SESSION['User']));
        if(!$r || ($r && !is_array(sqlsrv_fetch_array($r)))){
          function roundToQuarterHour($minutes) {$round = 15;return round($minutes / $round) * $round;}
          $r = sqlsrv_query($NEI,
            " SELECT  Tickets.*
              FROM    ((
                          SELECT  TicketO.TimeComp
                          FROM    TicketO
                                  LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                                  LEFT JOIN TicketDPDA ON TicketDPDA.ID = TicketO.ID
                          WHERE   Emp.ID = ?
                                  AND TicketO.EDate >= ?
                                  AND TicketO.EDate < ?
                                  AND TicketO.TimeComp <> '1899-12-30 00:00:00.000'
                                  AND TicketO.TimeComp IS NOT NULL
                      )
                      UNION ALL
                      (
                          SELECT  TicketD.TimeComp
                          FROM    TicketD
                                  LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                          WHERE   Emp.ID = ?
                                  AND TicketD.EDate >= ?
                                  AND TicketD.EDate < ?
                                  AND TicketD.TimeComp <> '1899-12-30 00:00:00.000'
                                  AND TicketD.TimeComp IS NOT NULL
                                  AND (ISNULL(TicketD.DescRes,'some_records_have_no_value')) NOT LIKE '%void%'
                                  AND (ISNULL(TicketD.DescRes,'some_records_have_no_value')) NOT LIKE '%dupe%'
                      )) AS Tickets
              ORDER BY Tickets.TimeComp DESC
            ;", array($_SESSION['User'],  date("Y-m-d 00:00:00.000"), date("Y-m-d 00:00:00.000",strtotime('tomorrow')),
                      $_SESSION['User'],  date("Y-m-d 00:00:00.000"), date("Y-m-d 00:00:00.000",strtotime('tomorrow'))));
          if($r){
            $Ticket = sqlsrv_fetch_Array($r);
            if(is_array($Ticket)){
              $En_Route = $Ticket['TimeComp'];
            }
          }
          $r = sqlsrv_query($NEI,
            " SELECT  Attendance.*
              FROM    Portal.dbo.Attendance
              WHERE   Attendance.[Start] IS NOT NULL
                      AND Attendance.[End] IS NULL
                      AND Attendance.[User] = ?
            ;",array($_SESSION['User']));

          if($r){
            $row = sqlsrv_fetch_array($r);
            if(is_array($row)){
              if(!isset($En_Route)){
                $En_Route = $row['Start'];
              } elseif(date("1899-12-30 H:i:s",strtotime($row['Start'])) >= date("1899-12-30 H:i:s",strtotime($En_Route) + (15 * 60))){
                $En_Route = $row['Start'];
              }
            }
          }
          $minutes = roundToQuarterHour(date("i",strtotime($En_Route)));
          if($minutes < 10){$minutes = "0" . $minutes;}
          $hours = $minutes == 60 ? date("H",strtotime($En_Route)) + 1 : date("H",strtotime($En_Route));
          $minutes = $minutes == 60 || $minutes == 0 ? "00" : $minutes;

          $En_Route = "1899-12-30 {$hours}:{$minutes}:00.000";
          $En_Route2 = date("Y-m-d {$hours}:{$minutes}:00.000");

          if(isset($En_Route)){
            sqlsrv_query($NEI,"UPDATE TicketO SET TicketO.TimeRoute = ?, TicketO.Assigned = 2, TicketO.EDate = ? WHERE TicketO.ID = ?;",array($En_Route,$En_Route2,$_POST['ID']));
            sqlsrv_query($NEI,"INSERT INTO Portal.dbo.Ticket(ID, TimeRoute) VALUES(?, ?);",array($_POST['ID'],$En_Route2));
          }
        }
        $Time_Site = isset($Time_Site) ? $Time_Site : date('1899-12-30 H:i:s');

        $minutes = roundToQuarterHour(date("i",strtotime($Time_Site)));
        if($minutes < 10){$minutes = "0" . $minutes;}
        $hours = $minutes == 60 ? date("H",strtotime($Time_Site)) + 1 : date("H",strtotime($Time_Site));
        $minutes = $minutes == 60 || $minutes == 0 ? "00" : $minutes;

        if($hours == "00" && $minutes == "00"){
          $minutes = "01";
        }

        $Time_Site = "1899-12-30 {$hours}:{$minutes}:00.000";

        sqlsrv_query($NEI,"UPDATE TicketO SET TicketO.TimeSite = ?, TicketO.Assigned = 3 WHERE TicketO.ID = ?;",array($Time_Site, $_POST['ID']));
        sqlsrv_query($NEI,"UPDATE Portal.dbo.Ticket SET Ticket.TimeSite = ? WHERE Ticket.ID = ?;",array(date('Y-m-d H:i:s'), $_POST['ID']));
        sqlsrv_query($Portal_44,
          " INSERT INTO Portal.dbo.Timeline(Entity, [Entity_ID], [Action], Time_Stamp)
            VALUES(?, ?, ?, ?)
          ;",array('Ticket', $_POST['ID'], 'At Work', date("Y-m-d H:i:s")));
        $r = sqlsrv_query($NEI,"SELECT Loc.Latt AS Latitude, Loc.fLong AS Longitude FROM Loc LEFT JOIN TicketO ON Loc.Loc = TicketO.LID WHERE TicketO.ID = ?;",array($_POST['ID']));
        $Location_GPS = Null;
        if($r){$Location_GPS = sqlsrv_fetch_array($r);}
        if(is_array($Location_GPS) && is_numeric($Location_GPS['Latitude']) && is_numeric($Location_GPS['Longitude']) && $Location_GPS['Latitude'] != 0 && $Location_GPS['Longitude'] != 0){
          $r = sqlsrv_query($Portal_44,
            " SELECT  *
              FROM    Portal.dbo.GPS
              WHERE   GPS.Employee_ID = ?
            ;",array($_SESSION['User']));
          $Check = FALSE;
          if($r){while($row = sqlsrv_fetch_array($r)){
            if(distance($row['Latitude'],$row['Longitude'],$Location_GPS['Latitude'],$Location_GPS['Longitude'],'M') <= .5 && date("Y-m-d H:i:s",strtotime("-15 minutes")) <= $row['Time_Stamp'] && $row['Time_Stamp'] <= date("Y-m-d H:i:s")){
              $Check = True;
              break;
            }
          }}
          if(!$Check){
            sqlsrv_query($Portal_44,
              " INSERT INTO Portal.dbo.Geofence_Alert(Employee_ID, Ticket_ID, Time_Stamp)
                VALUES(?, ?, ?)
              ;",array($_SESSION['User'],$_POST['ID'],date("Y-m-d H:i:s")));
          }
        }
      }
    }
}?>
