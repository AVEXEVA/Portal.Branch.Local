<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $r = $database->query(
        null,
        "   SELECT  *
          FROM    Connection
          WHERE   Connection.Connector = ?
                  AND Connection.Hash = ?;",
        array(
          $_SESSION[ 'User' ],
          $_SESSION[ 'Hash' ]
        )
      );
    $Connection = sqlsrv_fetch_array( $r );
    $User = $database->query(
        null,
        "   SELECT  Emp.*,
                    Emp.fFirst AS First_Name,
                    Emp.Last   AS Last_Name
            FROM    Emp
            WHERE   Emp.ID = ?;",
        array(
          $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $User );
    $r = $database->query(
        null,
        "   SELECT  Privilege.Access_Table,
                    Privilege.User_Privilege,
                    Privilege.Group_Privilege,
                    Privilege.Other_Privilege
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array( 
          $_SESSION[ 'User' ] 
        ) 
    );
    $Privileges = array();
    while( $Privilege = sqlsrv_fetch_array( $r ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    $Privileged = False;
    if( isset( $Privileges[ 'Time' ] )
        && $Privileges[ 'Time' ][ 'Other_Privilege' ]  >= 4
    ){ $Privileged = True; }
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
      $result = $database->query(
        null,
        " SELECT  Ticket.ID                       AS ID,
                  Ticket.Date                     AS Date,
                  Customer.Name                   AS Customer,
                  Location.Tag                    AS Location,
                  Job.ID                          AS Job,
                  Unit.State + ' - ' + Unit.Unit  AS Unit,
                  Ticket.Regular AS Regular,
                  Ticket.Overtime AS Overtime,
                  Ticket.Hours                    AS Hours,
                  Ticket.Zone                    AS Zone,
                  Ticket.OtherE                    AS OtherE,
                  Ticket.Level                    AS Level,
                  Ticket.Status                   AS Status,
                  Ticket.Payroll                  AS Payroll
          FROM    (
                    (
                      SELECT  TicketD.ID      AS ID,
                              TicketD.EDate   AS Date,
                              TicketD.fWork   AS Field,
                              TicketD.Loc   AS Location,
                              TicketD.Job   AS Job,
                              TicketD.Elev  AS Unit,
                              TicketD.Reg AS Regular,
                              TicketD.OT AS Overtime,
                              TicketD.Total   AS Hours,
                              TicketD.Level   AS Level,
                              TicketD.Zone AS Zone,
                              TicketD.OtherE AS OtherE,
                              4       AS Status,
                              TicketD.ClearPR AS Payroll
                      FROM    TicketD 
                    )
                  ) AS Ticket
                  LEFT JOIN Emp        AS Employee ON Ticket.Field    = Employee.fWork
                  LEFT JOIN Loc          AS Location ON Ticket.Location = Location.Loc
                  LEFT JOIN Job          AS Job      ON Ticket.Job      = Job.ID
                  LEFT JOIN (
                      SELECT  Owner.ID,
                              Rol.Name,
                              Owner.Status 
                      FROM    Owner 
                              LEFT JOIN Rol ON Owner.Rol = Rol.ID
                  ) AS Customer ON Job.Owner = Customer.ID
                  LEFT JOIN Elev         AS Unit     ON Ticket.Unit     = Unit.ID
          WHERE         Ticket.Date >= ?
                  AND   Ticket.Date < dateadd( day, 7, ? )
                  AND   Employee.ID = ?;",
        array(
          $_GET[ 'Date' ],
          $_GET[ 'Date' ],
          $_GET[ 'User' ]
        )
      );
      $data = array( );
      $regular = 0;
      $overtime = 0;
      $total = 0;
      $html = "<tr class='tickets' style='background-color:black !important;color:white !important;'><td colspan='20'>Tickets for the Week of " . date( 'm/d/Y', strtotime( $_GET[ 'Date' ] ) ) . "</td></tr>";
      $html = $html . "<tr class='tickets-header'><td colspan='2'>&nbsp;</td><td>ID</td><td>Date</td><td>Customer</td><td>Location</td><td>Job</td><td>Regular</td><td>Overtime</td><td>Hours</td><td>Zone</td><td>OtherE</td><td colspan='9'>&nbsp;</td></tr>";
      if( $result ){ while( $row = sqlsrv_fetch_array( $result ) ){ 
        $regular += $row['Regular'];
        $overtime += $row['Overtime'];
        $total += $row['Hours'];
        $html = $html . "<tr class='ticket' rel='" . $row[ 'ID' ] . "'><td></td><td></td><td>" . $row[ 'ID' ] . "</td><td>" . date( 'm/d/Y', strtotime( $row[ 'Date' ] ) ) . "</td><td>" . $row[ 'Customer' ] . "</td><td>" . $row[ 'Location' ] . "</td><td>" . $row[ 'Job' ] . "</td><td>" . $row['Regular'] . "</td><td>" . $row['Overtime'] ."</td><td>" . $row[ 'Hours' ] . "</td><td>" . $row['Zone' ] . "</td><td>" . $row['OtherE'] . "</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
        $data[ ] = $row;  
      } }
      $html = $html . "<tr class='ticket' rel='" . $row[ 'ID' ] . "'><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td>" . $regular . "</td><td>" . $overtime ."</td><td>" . $total . "</td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
      $html = $html . "<tr class='tickets-footer' style='background-color:black !important;color:white !important;height:3px !important;padding:0px !important;'><td colspan='23' style='background-color:black !important;color:white !important;height:5px !important;padding:0px !important;'>&nbsp;</td></tr>";
      echo $html;
}}
?>