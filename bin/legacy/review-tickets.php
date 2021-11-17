<?php
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT *
		FROM   Connection
		WHERE  Connection.Connector = ?
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = $database->query(null,"
		SELECT *
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$Privileges = array();
	if($r){while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access']] = $Privilege;}}
    if(	!isset($My_Connection['ID']) ){require('../401.html'); }
    else {?>
<style>
div.popup {
  background-color:#1d1d1d;
  color:black;top:0;
  position:absolute;
  left:0;
  width:100%;
  height:100%;
}
div.popup div.panel-heading {
  padding-top:60px;
}
</style>
<div class='popup'>
  <div class='panel panel-primary' style='height:100%;'>
    <div class='panel-heading'>Review Tickets</div>
    <div class='panel-body' style='padding:10px;'>
      <style>
      div.tickets .row .col-xs-3 {border:1px solid white;}
      </style>
      <div class='tickets'>
        <div class='row'>
          <div class='col-xs-3'>ID</div>
          <div class='col-xs-3'>Reg</div>
          <div class='col-xs-3'>Other</div>
          <div class='col-xs-3'>Total</div>
        </div>
        <?php
          $reg_total = 0;
          $other_total = 0;
          $total = 0;
          $resource = $database->query(null,"SELECT * FROM Attendance WHERE Attendance.[User] = ? AND Attendance.[End] IS NULL;", array($_SESSION['User']));
          if($resource){
            $attendance = sqlsrv_fetch_array($resource);
            if(is_array($attendance)){
              $resource = $database->query(null,
                " SELECT  *
                  FROM    TicketO
                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                          LEFT JOIN TicketDPDA ON TicketO.ID = TicketDPDA.ID
                  WHERE   Emp.ID = ?
                          AND TicketO.EDate >= ?
                          AND TicketO.EDate < ?
                          AND TicketO.Assigned = 6
                ;",array($_SESSION['User'], $attendance['Start'], date("Y-m-d H:i:s")));
              if($resource){while($row = sqlsrv_fetch_array($resource)){
                ?><div class='row'>
                  <div class='col-xs-3'><a href='ticket2.php?ID=<?php echo $row['ID'];?>'><?php echo $row['ID'];?></a></div>
                  <div class='col-xs-3'><?php echo $row['Reg'];?></div>
                  <div class='col-xs-3'><?php echo number_format((float) ($row['OT'] + $row['DT'] + $row['NT']), 2, '.', '');?></div>
                  <div class='col-xs-3'><?php echo $row['Total'];?></div>
                </div><?php
                $reg_total += $row['Reg'];
                $other_total += $row['OT'] + $row['DT'] + $row['NT'];
                $total += $row['Total'];
              }}
              $resource = $database->query(null,
                " SELECT  *
                  FROM    TicketD
                          LEFT JOIN Emp ON TicketD.fWork = Emp.fWork
                  WHERE   Emp.ID = ?
                          AND TicketD.EDate >= ?
                          AND TicketD.EDate < ?
                ;",array($_SESSION['User'], $attendance['Start'], date("Y-m-d H:i:s")));
              if($resource){while($row = sqlsrv_fetch_array($resource)){
                ?><div class='row'>
                  <div class='col-xs-3'><a href='ticket2.php?ID=<?php echo $row['ID'];?>'><?php echo $row['ID'];?></a></div>
                  <div class='col-xs-3'><?php echo $row['Reg'];?></div>
                  <div class='col-xs-3'><?php echo number_format((float) ($row['OT'] + $row['DT'] + $row['NT']), 2, '.', '');?></div>
                  <div class='col-xs-3'><?php echo $row['Total'];?></div>
                </div><?php
                $reg_total += $row['Reg'];
                $other_total += $row['OT'] + $row['DT'] + $row['NT'];
                $total += $row['Total'];
              }}
              $resource = $database->query(null,
                " SELECT  TicketO.ID AS ID
                  FROM    TicketO
                          LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
                  WHERE   Emp.ID = ?
                          AND (TicketO.Assigned = 2 OR TicketO.Assigned = 3)
                ;",array($_SESSION['User']));
              if($resource){while($row = sqlsrv_fetch_array($resource)){
                ?><div class='row'>
                  <div class='col-xs-3'><a href='ticket2.php?ID=<?php echo $row['ID'];?>'><?php echo $row['ID'];?></a></div>
                  <div class='col-xs-9' style='border:1px solid white;'>Open</div>
                </div><?php
              }}
            }
          }
        ?>
        <div class='row'>
          <div class='col-xs-3'>&nbsp;</div>
          <div class='col-xs-3' style='font-weight:bold;'><?php echo number_format((float) ($reg_total));?></div>
          <div class='col-xs-3' style='font-weight:bold;'><?php echo number_format((float) ($other_total));?></div>
          <div class='col-xs-3' style='font-weight:bold;'><?php echo number_format((float) ($total));?></div>
        </div>
      </div>
      <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
      <div class='row'>
        <div class='col-xs-6'><button type='button' onClick='complete_clockout();' style='width:100%;height:50px;color:white !important;border:1px solid white;text-align:center;'>Finish Clocking Out</button></div>
        <div class='col-xs-6'><button type='button' onClick='close_popup();' style='width:100%;height:50px;color:white !important;border:1px solid white;text-align:center;'>Exit</button></div>
        <Script>function close_popup(){$(".popup").remove();}</script>
      </div>
    </div>
  </div>
</div><?php }
} else {?><html><head><script>document.location.href='../login.php?Forward=review-tickets.php';</script></head></html><?php }?>
