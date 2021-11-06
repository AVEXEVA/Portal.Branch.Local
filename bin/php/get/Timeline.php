<?php
session_start( [ 'read_and_close' => true ] );
set_time_limit (60);
require('../index.php');
function Check_Date_Time($date_time){
 if (preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2}).(\d{3})/", $date_time)){return true;}
 else {return false;}
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
    /*$r = $database->query(null,
      " SELECT *
        FROM (
          (SELECT  TicketO.ID AS ID,
                TicketO.CDate,
                TicketO.EDate,
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
                  AND (TicketO.CDate >= ?
                  OR TicketO.EDate >= ?)
        )
      ) AS Tickets
      ;",array(date("Y-m-d 00:00:00.000",strtotime($_GET['REFRESH_DATETIME'])),date("Y-m-d 00:00:00.000",strtotime($_GET['REFRESH_DATETIME'])), date("Y-m-d 00:00:00.000",strtotime($_GET['REFRESH_DATETIME'])),date("Y-m-d 00:00:00.000",strtotime($_GET['REFRESH_DATETIME']))));
    $rows = array();
    if($r){while($row = sqlsrv_fetch_array($r)){
      $rows[$row['ID']] = $row;
    }}
    $Ticket_ID = 0;
    //Created Action
    $pSQL = sqlsrv_prepare($Portal_44,
      " SELECT  Top 1
                Timeline.ID
        FROM    Portal.dbo.Timeline
        WHERE   Timeline.[Entity] = ?
                AND Timeline.[Entity_ID] = ?
                AND Timeline.[Action] = ?
      ;",array('Ticket', &$Ticket_ID, 'Created'));
    if(count($rows) > 0){foreach($rows AS $Ticket_ID=>$Ticket_Data){
      sqlsrv_execute($pSQL);
      if(!$pSQL || !is_array(sqlsrv_fetch_array($pSQL))){
        if(isset($Ticket_Data['CDate']) && Check_Date_Time($Ticket_Data['CDate'])){
          $database->query($Portal_44,
            " INSERT INTO Portal.dbo.Timeline(Entity, [Entity_ID], [Action], Time_Stamp, Description)
              VALUES(?, ?, ?, ?, ?)
            ;",array('Ticket', $Ticket_ID, 'Created', $Ticket_Data['CDate'], '<i class="fa fa-building fa-fw fa-1x"></i>' . " " . $Ticket_Data['Location_Tag']));
        }
      }
    }}
    //Accepted Work Action
    $Ticket_ID = 0;
    $pSQL = sqlsrv_prepare($Portal_44,
      " SELECT  Top 1
                Timeline.ID
        FROM    Portal.dbo.Timeline
        WHERE   Timeline.[Entity] = ?
                AND Timeline.[Entity_ID] = ?
                AND Timeline.[Action] = ?
      ;",array('Ticket', &$Ticket_ID, 'Accepted Work'));
    if(count($rows) > 0){foreach($rows AS $Ticket_ID=>$Ticket_Data){
      sqlsrv_execute($pSQL);
      if(!$pSQL || !is_array(sqlsrv_fetch_array($pSQL))){
        if(isset($Ticket_Data['TimeRoute']) && Check_Date_Time($Ticket_Data['TimeRoute']) && $Ticket_Data['TimeRoute'] != '1899-12-30 00:00:00.000'){
          $database->query($Portal_44,
            " INSERT INTO Portal.dbo.Timeline(Entity, [Entity_ID], [Action], Time_Stamp, Description)
              VALUES(?, ?, ?, ?, ?)
            ;",array('Ticket', $Ticket_ID, 'Accepted Work', date("Y-m-d",strtotime($Ticket_Data['EDate'])) . ' ' . date("H:i:s",strtotime($Ticket_Data['TimeRoute'])), '<i class="fa fa-building fa-fw fa-1x"></i>' . " " . $Ticket_Data['Location_Tag']));
        }
      }
    }}
    //Accepted Work Action
    $Ticket_ID = 0;
    $pSQL = sqlsrv_prepare($Portal_44,
      " SELECT  Top 1
                Timeline.ID
        FROM    Portal.dbo.Timeline
        WHERE   Timeline.[Entity] = ?
                AND Timeline.[Entity_ID] = ?
                AND Timeline.[Action] = ?
      ;",array('Ticket', &$Ticket_ID, 'At Work'));
    if(count($rows) > 0){foreach($rows AS $Ticket_ID=>$Ticket_Data){
      sqlsrv_execute($pSQL);
      if(!$pSQL || !is_array(sqlsrv_fetch_array($pSQL))){
        if(isset($Ticket_Data['TimeSite']) && Check_Date_Time($Ticket_Data['TimeSite']) && $Ticket_Data['TimeSite'] != '1899-12-30 00:00:00.000'){
          $database->query($Portal_44,
            " INSERT INTO Portal.dbo.Timeline(Entity, [Entity_ID], [Action], Time_Stamp, Description)
              VALUES(?, ?, ?, ?, ?)
            ;",array('Ticket', $Ticket_ID, 'At Work', date("Y-m-d",strtotime($Ticket_Data['EDate'])) . ' ' . date("H:i:s",strtotime($Ticket_Data['TimeSite'])), '<i class="fa fa-building fa-fw fa-1x"></i>' . " " . $Ticket_Data['Location_Tag']));
        }
      }
    }}
    //Accepted Work Action
    $pSQL = sqlsrv_prepare($Portal_44,
      " SELECT  Top 1
                Timeline.ID
        FROM    Portal.dbo.Timeline
        WHERE   Timeline.[Entity] = ?
                AND Timeline.[Entity_ID] = ?
                AND Timeline.[Action] = ?
      ;",array('Ticket', &$Ticket_ID, 'Completed Work'));
    if(count($rows) > 0){foreach($rows AS $Ticket_ID=>$Ticket_Data){
      sqlsrv_execute($pSQL);
      if(!$pSQL || !is_array(sqlsrv_fetch_array($pSQL))){
        if(isset($Ticket_Data['TimeComp']) && Check_Date_Time($Ticket_Data['TimeComp']) && $Ticket_Data['TimeComp'] != '1899-12-30 00:00:00.000'){
          $database->query($Portal_44,
            " INSERT INTO Portal.dbo.Timeline(Entity, [Entity_ID], [Action], Time_Stamp, Description)
              VALUES(?, ?, ?, ?, ?)
            ;",array('Ticket', $Ticket_ID, 'Completed Work', date("Y-m-d",strtotime($Ticket_Data['EDate'])) . ' ' . date("H:i:s",strtotime($Ticket_Data['TimeComp'])), '<i class="fa fa-building fa-fw fa-1x"></i>' . " " . $Ticket_Data['Location_Tag']));
        }
      }
    }}*/

    //Get Timeline//
    $rows = array();
    $Ticket_ID = 0;

    if(isset($_GET['REFRESH_DATETIME'])){
      $r = $database->query($Portal_44,
        " SELECT  *
          FROM    Portal.dbo.Timeline
          WHERE   Timeline.Time_Stamp > ?
                  AND Timeline.Time_stamp <= ?
          ORDER BY Timeline.Time_Stamp ASC
        ;",array(date("Y-m-d H:i:s",strtotime("-15 minutes",strtotime($_GET['REFRESH_DATETIME']))),date("Y-m-d H:i:s",strtotime('+15 minutes'))));
      $pSQL = sqlsrv_prepare(null,
        " SELECT  Emp.fFirst + ' ' + Emp.Last AS Employee_Name,
                  Ticket.ID,
                  Loc.Tag AS Location_Tag
          FROM    (
            (
              SELECT  TicketO.fWork,
                      TicketO.ID,
                      TicketO.LID AS Loc
              FROM    nei.dbo.TicketO
              WHERE   TicketO.ID = ?
            )
            UNION ALL
            (
              SELECT  TicketD.fWork,
                      TicketD.ID,
                      TicketD.Loc AS Loc
              FROM    nei.dbo.TicketD
              WHERE   TicketD.ID = ?
            )
          ) AS Ticket
                  LEFT JOIN Emp ON Emp.fWork = Ticket.fWork
                  LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                  LEFT JOIN nei.dbo.Loc ON Ticket.Loc = Loc.Loc
          WHERE   tblWork.Super LIKE '%' + ? + '%' OR ? IS NULL
        ;",array(&$Ticket_ID, &$Ticket_ID, &$_GET['Supervisor'], &$_GET['Supervisor']));
      if($r){while($row = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
        if($row['Entity'] == 'Ticket'){
          $Ticket_ID = $row['Entity_ID'];
          sqlsrv_execute($pSQL);
          $pRow = sqlsrv_fetch_array($pSQL);
          if($pSQL && is_array($pRow)){
            $row['Time_Stamp'] = date("m/d/Y h:i A",strtotime($row['Time_Stamp']));
            $row['Employee_Name'] = $pRow['Employee_Name'];
            $row['Location_Tag'] = $pRow['Location_Tag'];
            $rows[$row['ID']] = $row;
          }
        } else {
          $row['Time_Stamp'] = date("m/d/Y h:i A",strtotime($row['Time_Stamp']));
          $rows[$row['ID']] = $row;
        }
      }}
    }
    print json_encode($rows);
  }
}?>
