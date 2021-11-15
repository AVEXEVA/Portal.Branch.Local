<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
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
  //Connection
  $result = $database->query(
    null,
    " SELECT  * 
      FROM    Connection 
      WHERE       Connector = ? 
              AND Hash = ?;",
    array(
      $_SESSION['User'],
      $_SESSION['Hash']
    )
  );
  $Connection = sqlsrv_fetch_array( $result );
  //User
  $result = $database->query(
    null,
    " SELECT  *, 
              fFirst AS First_Name, 
              Last   AS Last_Name 
      FROM    Emp 
      WHERE   Emp.ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $User = sqlsrv_fetch_array($result);
  //Privileges
  $result = $database->query(
    null,
    " SELECT  Access_Table, 
              User_Privilege, Group_Privilege, Other_Privilege
      FROM    Privilege
      WHERE   User_ID = ?;",
    array(
      $_SESSION[ 'User' ]
    )
  );
  $Privileges = array();
  while($Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[$Privilege[ 'Access_Table' ] ] = $Privilege; }
  $Privileged = FALSE;
  if( isset( $Privileges[ 'Ticket' ]) && $Privileges[ 'Ticket' ][ 'User_Privilege' ] >= 6){ $Privileged = TRUE; }
  if(!isset( $Connection['ID'], $_POST['ID'])  || !$Privileged || !is_numeric( $_POST[ 'ID' ] ) ){ }
  else {

    $database->query(
      null,
      " INSERT INTO Activity([User], [Date], [Page]) 
        VALUES( ?, ?, ? );",
      array(
        $_SESSION['User'],
        date( 'Y-m-d H:i:s' ), 
        'ticket.php'
      )
    );
    $r = $database->query(
      null,
      " SELECT  * 
        FROM    TicketO 
                LEFT JOIN Emp ON Emp.fWork = TicketO.fWork 
        WHERE       TicketO.ID = ? 
                AND Emp.ID = ?;",
      array(
        $_POST[ 'ID' ],
        $_SESSION[ 'User' ]
      )
    );
    if($r && is_array(sqlsrv_fetch_array($r))){
      $r = $database->query(null,
          " SELECT  Top 1
                    GPS.*
            FROM    GPS
            WHERE   GPS.Employee_ID = ?
                    AND GPS.Time_Stamp >= ?
            ORDER BY GPS.Time_Stamp DESC
          ;",array($_SESSION['User'], date('Y-m-d H:i:s', strtotime('-30 minutes', strtotime('now')))));
      if($r){

        $row = sqlsrv_fetch_array($r);
        if(is_array($row)){
          $database->query(
            null,
            " INSERT INTO TechLocation( TicketID, TechID, ActionGroup, Action, Latitude, Longitude, Altitude, Accuracy, DateTimeRecorded ) 
              VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ? );",
            array(
              $_POST['ID'],  
              $User['fWork'], 
              'On site time', 
              'Updated on site time to ' . date('h:i A'), $row['Latitude'], $row['Longitude'], 
              0, 
              0, 
              date('Y-m-d H:i:s')
            )
          );
        } else {
          echo 'Error: No Current Active GPS Timestamp.';
          exit;
        }
      } else {
        echo 'Error: No Current Active GPS Timestamp.';
        exit;
      }
      function roundToQuarterHour($minutes) {$round = 15;return round($minutes / $round) * $round;}
      $r = $database->query(null,
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
      var_dump( sqlsrv_errors( ) );
      if($r){
        $Ticket = sqlsrv_fetch_Array($r);
        if(is_array($Ticket)){
          $En_Route = $Ticket['TimeComp'];
        }
      }
      $r = $database->query(null,
        " SELECT  Attendance.*
          FROM    Attendance
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
        $database->query(null,"UPDATE TicketO SET TicketO.TimeRoute = ?, TicketO.Assigned = 2, TicketO.EDate = ? WHERE TicketO.ID = ?;",array($En_Route,$En_Route2,$_POST['ID']));
        $database->query(null,"INSERT INTO Ticket(ID, TimeRoute) VALUES(?, ?);",array($_POST['ID'],$En_Route2));
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

      $database->query(null,"UPDATE TicketO SET TicketO.TimeSite = ?, TicketO.Assigned = 3 WHERE TicketO.ID = ?;",array($Time_Site, $_POST['ID']));
      $database->query(null,"UPDATE Ticket SET Ticket.TimeSite = ? WHERE Ticket.ID = ?;",array(date('Y-m-d H:i:s'), $_POST['ID']));
      $database->query(null,
        " INSERT INTO Timeline(Entity, [Entity_ID], [Action], Time_Stamp)
          VALUES(?, ?, ?, ?)
        ;",array('Ticket', $_POST['ID'], 'At Work', date("Y-m-d H:i:s")));
      //Check to see if in GEOFENCE
      $r = $database->query(null,"SELECT Loc.Latt AS Latitude, Loc.fLong AS Longitude FROM Loc LEFT JOIN TicketO ON Loc.Loc = TicketO.LID WHERE TicketO.ID = ?;",array($_POST['ID']));
      $Location_GPS = Null;
      if($r){$Location_GPS = sqlsrv_fetch_array($r);}
      if(is_array($Location_GPS) && is_numeric($Location_GPS['Latitude']) && is_numeric($Location_GPS['Longitude']) && $Location_GPS['Latitude'] != 0 && $Location_GPS['Longitude'] != 0){
        $r = $database->query(null,
          " SELECT  GPS.Latitude, GPS.Longitude, GPS.Time_Stamp
            FROM    GPS
            WHERE   GPS.Employee_ID = ?
                    AND GPS.Time_Stamp > ?
            ORDER BY GPS.Time_Stamp DESC
          ;",array($_SESSION['User'],date("Y-m-d H:i:s",strtotime("-20 minutes"))),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
        $row_count = sqlsrv_num_rows( $r );
        $distance = 99999;
        $best_distance = 99999;
        if($r){while( $i <= $row_count ){
          $row = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
          if(is_array($row) && $row != array()){
            $distance = distance($row['Latitude'],$row['Longitude'],$Location_GPS['Latitude'],$Location_GPS['Longitude'],'M');
            if($distance <= $best_distance){
              $best_distance = $distance;
            }
          }
          $i++;
        }}
        $database->query(null,
          " INSERT INTO Geofence(Employee_ID, Ticket_ID, Time_Stamp, Distance)
            VALUES(?, ?, ?, ?)
          ;",array($_SESSION['User'],$_POST['ID'],date("Y-m-d H:i:s"),$best_distance));
      }
    }
  }
}?>
