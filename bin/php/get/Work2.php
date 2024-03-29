<?php
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  //Establish Connection
  $r = \singleton\database::getInstance( )->query(
      null,
    " SELECT  *
      FROM    Connection
      WHERE   Connection.Connector = ?
              AND Connection.Hash = ?;",
    array(
      $_SESSION['User'],
      $_SESSION['Hash']
    )
  );
  $Connection = sqlsrv_fetch_array($r);

  //Estasblish User
  $User    = \singleton\database::getInstance( )->query(
      null,
    " SELECT  Emp.*,
              Emp.fFirst AS First_Name,
              Emp.Last   AS Last_Name
      FROM    Emp
      WHERE   Emp.ID = ?
  ;", array($_SESSION['User']));
  $User = sqlsrv_fetch_array($My_User);

  //Establish Privileges
  $r = \singleton\database::getInstance( )->query(
      null,
    " SELECT  Privilege.Access,
              Privilege.Owner,
              Privilege.Group,
              Privilege.Other
      FROM    Privilege
      WHERE   Privilege.User_ID = ?;",
    array(
      $_SESSION['User']
    )
  );
  $Privileges = array();
  while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access']] = $Privilege;}
  $Privileged = False;
   if( isset($Privileges['Ticket'])
        && (
          $Privileges['Ticket']['Owner'] >= 4
      ||  $Privileges['Ticket']['Group'] >= 4
      ||  $Privileges['Ticket']['Other'] >= 4)){ $Privileged = True; }
    if(!isset($Connection['ID']) || !$Privileged){ print json_encode( array( 'data' => array( ) ) ); }
  else {
      $r = \singleton\database::getInstance( )->query(
          null,
        " SELECT  Tickets.*,
                  Loc.ID            AS Account,
                  Loc.Tag           AS Tag,
                  Loc.Tag           AS Location,
                  Loc.Address       AS Address,
                  Loc.Address       AS Street,
                  Loc.City          AS City,
                  Loc.State         AS State,
                  Loc.Zip           AS Zip,
                  Job.ID            AS Job_ID,
                  Job.fDesc         AS Job_Description,
                  OwnerWithRol.ID   AS Owner_ID,
                  OwnerWithRol.Name AS Customer,
                  Elev.Unit         AS Unit_Label,
                  Elev.State        AS Unit_State,
                  Emp.fFirst        AS Worker_First_Name,
                  Emp.Last          AS Worker_Last_Name,
                  'Unknown'         AS ClearPR,
                  JobType.Type      AS Job_Type,
                  Tickets.Level     AS Level
          FROM (
            (SELECT TicketO.ID        AS ID,
                    TicketO.fDesc     AS Description,
                    ''                AS Resolution,
                    TicketO.CDate     AS Created,
                    TicketO.DDate     AS Dispatched,
                    TicketO.EDate     AS Date,
                    TicketO.TimeSite  AS On_Site,
                    TicketO.TimeComp  AS Completed,
                    TicketO.Who       AS Caller,
                    TicketO.fBy       AS Reciever,
                    TicketO.Level     AS Level,
                    TicketO.Cat       AS Category,
                    TicketO.LID       AS Location,
                    TicketO.Job       AS Job,
                    TicketO.LElev     AS Unit,
                    TicketO.Owner     AS Owner,
                    TicketO.fWork     AS Mechanic,
                    TickOStatus.Type  AS Status,
                    0                 AS Total,
                    0                 AS Regular,
                    0                 AS Overtime,
                    0                 AS Doubletime,
                    TicketO.Assigned  AS Assigned,
                    TicketO.High      AS Priority,
                    TicketDPDA.Comments AS Comments
             FROM   TicketO
                    LEFT JOIN TickOStatus ON TicketO.Assigned = TickOStatus.Ref
                    LEFT JOIN TicketDPDA  ON TicketO.ID       = TicketDPDA.ID
                    LEFT JOIN Emp ON Emp.fWork = TicketO.fWork
             WHERE  Emp.ID = ?
                    AND ((TicketO.Assigned >= 1
                    AND TicketO.Assigned <= 4)
                    OR (TicketO.EDate >= ?
                    AND TicketO.EDate < ?)
                    OR (TicketO.Assigned = 6))
            )
          ) AS Tickets
          LEFT JOIN Loc          ON Tickets.Location = Loc.Loc
          LEFT JOIN Job          ON Tickets.Job      = Job.ID
          LEFT JOIN Elev         ON Tickets.Unit     = Elev.ID
          LEFT JOIN OwnerWithRol ON Tickets.Owner    = OwnerWithRol.ID
          LEFT JOIN Emp          ON Tickets.Mechanic = Emp.fWork
          LEFT JOIN JobType      ON Job.Type         = JobType.ID
        ORDER BY Tickets.Assigned ASC
      ",array(
        $_SESSION['User'],
        date("Y-m-d 00:00:00.000"),
        date(
          "Y-m-d 00:00:00.000",
          strtotime('tomorrow')
        )
      ),
      array( "Scrollable"=>SQLSRV_CURSOR_KEYSET)
    );
    $data = array();
    $row_count = sqlsrv_num_rows( $r );
    if($r){
      while($i < $row_count){
        $Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
        if(is_array($Ticket) && $Ticket != array()){
          $levels = array(
            0=>'',
            1=>'Service Call',
            2=>'Trucking',
            3=>'Modernization',
            4=>'Violations',
            5=>'Level 5',
            6=>'Repair',
            7=>'Annual',
            8=>'Escalator',
            9=>'Email',
            10=>'Maintenance',
            11=>'Survey',
            12=>'Engineering',
            13=>'Support',
            14=>"M/R"
          );
          $Ticket['Level'] = isset($Ticket['Level']) && isset($levels[$Ticket['Level']]) ? $levels[$Ticket['Level']] : 'Unknown';
          $Ticket['Status'] = strlen($Ticket['Status']) > 0
            ? $Ticket['Status']
            : 'Reviewing';
          switch( $Ticket[ 'Status' ] ){
            case 'Reviewing':
              $Ticket['Status'] = $Ticket['Assigned'] > 3 && !is_null( $Ticket[ 'Comments' ]) && $Ticket[ 'Comments' ] != ''
                ? $Ticket['Status']
                : 'Signed';
              break;
            default:
              $Ticket['Status'];
              break;
          }
          $data[] = $Ticket;
        } else {

        }
        $i++;
      }
    }
    $r = $database->query(null,
      "  SELECT               Tickets.*,
         Loc.ID            AS Account,
         Loc.Tag           AS Tag,
         Loc.Tag           AS Location,
         Loc.Address       AS Address,
         Loc.Address       AS Street,
         Loc.City          AS City,
         Loc.State         AS State,
         Loc.Zip           AS Zip,
         Job.ID            AS Job_ID,
         Job.fDesc         AS Job_Description,
         Elev.Unit         AS Unit_Label,
         Elev.State        AS Unit_State,
         Emp.fFirst        AS Worker_First_Name,
         Emp.Last          AS Worker_Last_Name,
         'Unknown'         AS ClearPR,
         JobType.Type      AS Job_Type,
         Tickets.Level AS Level
    FROM (
        (SELECT TicketD.ID       AS ID,
            TicketD.fDesc    AS Description,
            ''               AS Resolution,
            TicketD.CDate    AS Created,
            TicketD.DDate    AS Dispatched,
            TicketD.EDate    AS Date,
            TicketD.TimeSite AS On_Site,
            TicketD.TimeComp AS Completed,
            TicketD.Who      AS Caller,
            TicketD.fBy      AS Reciever,
            TicketD.Level    AS Level,
            TicketD.Cat      AS Category,
            TicketD.Loc      AS Location,
            TicketD.Job      AS Job,
            TicketD.Elev     AS Unit,
            TicketD.fWork    AS Mechanic,
            'Completed'      AS Status,
            0                AS Total,
            0                AS Regular,
            0                AS Overtime,
            0                AS Doubletime
         FROM   TicketD
                LEFT JOIN Emp ON Emp.fWork = TicketD.fWork
         WHERE  Emp.ID = ?
                AND TicketD.EDate >= ?
                AND TicketD.EDate < ?
        )
      ) AS Tickets
      LEFT JOIN Loc          ON Tickets.Location = Loc.Loc
      LEFT JOIN Job          ON Tickets.Job      = Job.ID
      LEFT JOIN Elev         ON Tickets.Unit     = Elev.ID
      LEFT JOIN Emp          ON Tickets.Mechanic = Emp.fWork
      LEFT JOIN JobType      ON Job.Type         = JobType.ID",array($_SESSION['User'],date("Y-m-d 00:00:00.000"),date("Y-m-d 00:00:00.000",strtotime('tomorrow'))),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
      $row_count = sqlsrv_num_rows( $r );
      $i = 0;
      if($r){
        while($i < $row_count){
          $Ticket = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
          if(is_array($Ticket) && $Ticket != array()){
            $levels = array(
              0=>'',
              1=>'Service Call',
              2=>'Trucking',
              3=>'Modernization',
              4=>'Violations',
              5=>'Level 5',
              6=>'Repair',
              7=>'Annual',
              8=>'Escalator',
              9=>'Email',
              10=>'Maintenance',
              11=>'Survey',
              12=>'Engineering',
              13=>'Support',
              14=>"M/R"
            );
            $Ticket['Level'] = isset($Ticket['Level']) && isset($levels[$Ticket['Level']]) ? $levels[$Ticket['Level']] : 'Unknown';
            $Ticket['Status'] = strlen($Ticket['Status']) > 0 ? $Ticket['Status'] : 'Reviewing';
            $data[] = $Ticket;
            $i++;
          } else {

          }

        }
      }
    print json_encode(array('data'=>$data));
  }
}
