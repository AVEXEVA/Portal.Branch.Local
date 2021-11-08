<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = $database->query(
      null,
      " SELECT  * 
        FROM    Connection 
        WHERE   Connection.Connector = ? 
                AND Connection.Hash = ?;",
      array(
        $_SESSION[ 'User' ],
        $_SESSION[ 'Hash' ]
      )
    );
    $Connection = sqlsrv_fetch_array( $result );
    $result = $database->query(
      null,
      " SELECT  *, 
                fFirst AS First_Name, 
                Last as Last_Name 
        FROM    Emp 
        WHERE   ID = ?",
      array(
        $_SESSION[ 'User' ]
      )
    );
    $User = sqlsrv_fetch_array( $result );
    $result = $database->query(
      null,
      " SELECT  Access_Table, 
                User_Privilege, 
                Group_Privilege, 
                Other_Privilege
        FROM    Privilege
        WHERE   User_ID = ?;",
      array(
        $_SESSION[ 'User' ]
      )
    );
    $Privileges = array();
    $Privileged = false;
    if( $result ){ while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }

    if(     isset( $Privileges[ 'Time' ] ) 
        &&  $Privileges[ 'Time' ][ 'User_Privilege' ] >= 4 
        &&  $Privileges[ 'Time' ][ 'Group_Privilege' ] >= 4 
        &&  $Privileges[ 'Time' ][ 'Other_Privilege' ] >= 4){ 
              $Privileged = TRUE; }
    if(     !isset($Connection['ID']) 
        ||  !$Privileged){ ?><script>document.location.href='../login.php';</script><?php }
    else {
		  $database->query(
        null,
        " INSERT INTO Activity( [User], [Date], [Page] ) 
          VALUES( ?, ?, ? );",
        array(
          $_SESSION[ 'User' ],
          date( 'Y-m-d H:i:s' ), 
          'scheduler.php'
        )
      );
?><!DOCTYPE html>
<html lang="en"style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require( bin_meta . 'index.php');?>
    <!--<meta http-equiv="refresh" content="300">-->
	<title>Nouveau Elevator Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
    <link rel='stylesheet' href='bin/libraries/timepicker/jquery.timepicker.min.css' />
    <script src='bin/libraries/timepicker/jquery.timepicker.min.js'></script>
	<style>
		.panel {background-color:transparent !important;}
		.panel > div.panel-body.white-background {background-color:rgba(255,255,255,.7) !important;}
		.nav-tabs > li:not(.active) {background-color:rgba(255,255,255,.6) !important;}
		.panel-heading {font-family: 'BankGothic' !important;}
		.shadow {box-shadow:0px 5px 5px 0px;}
		<?php if(isMobile()){?>
		.panel-body {padding:0px !important;}
		<?php }?>

			div#wrapper {
				overflow:scroll;
			}
		@media print {
			div#wrapper {overflow:visible;}
		}
	</style>
</head>
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);height:100%;color:black !important;">
    <div id='container' style='min-height:100%;height:100%;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;background-color:white;'>
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div class='panel panel-primary'>
            <div class='panel-heading'>
              <h4>Attendance</h4>
            </div>
            <div class='panel-body'>
              <div class='row'>
              <form action='scheduler.php?<?php echo isset($_GET['Year'],$_GET['Month']) ? "Year={$_GET['Year']}&Month={$_GET['Month']}" : '';?>'>
                <?php if(isset($_GET['Year'],$_GET['Month'])){?>
                  <input type='hidden' name='Year' value='<?php echo $_GET['Year'];?>' />
                  <input type='hidden' name='Month' value='<?php echo $_GET['Month'];?>' />
                <?php }?>
                <div class='col-xs-12'>Supervisor: <select name='Supervisor'><?php
                  $r = $database->query(null,"SELECT tblWork.Super FROM tblWork GROUP BY tblWork.Super ORDER BY tblWork.Super ASC;");
                  if($r){while($row = sqlsrv_fetch_array($r)){
                    ?><option value='<?php echo $row['Super'];?>' <?php if(isset($_GET['Supervisor']) && $_GET['Supervisor'] == $row['Super']){?>selected<?php }?>><?php echo $row['Super'];?></option><?php
                  }}
                ?></select></div>
                <div class='col-xs-12'><input type='submit' value='Search' /></div>
              </form>
              </div>
            </div>
          </div>
          <div class='panel-body'>
            <div class='row'>
              <div class='col-xs-4'>&nbsp;</div>
              <div class='col-xs-4'><h3><a href='scheduler.php?Year=<?php echo date("Y",strtotime("now"));?>&Month=<?php echo date("m",strtotime("now"));?><?php echo isset($_GET['Supervisor']) ? '&Supervisor=' . $_GET['Supervisor'] : '';?>'><?php echo date("F",strtotime("now"));?></a></h3></h3></div>
              <div class='col-xs-4'><h3><a href='scheduler.php?Year=<?php echo date("Y",strtotime("+1 months"));?>&Month=<?php echo date("m",strtotime("+1 months"));?><?php echo isset($_GET['Supervisor']) ? '&Supervisor=' . $_GET['Supervisor'] : '';?>'><?php echo date("F",strtotime("+1 months"));?></a></h3></div>
            </div>
          </div>
          <div class='panel-body'>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'>
              <div class='col-xs-1'>&nbsp;</div>
              <div class='col-xs-1' style='background-color:gold;'>Clocked In</div>
              <div class='col-xs-1' style='background-color:green;color:white;'>Worked Day</div>
              <div class='col-xs-1' style='background-color:orange;color:white;'>Worked Under 7.75 Hours</div>
              <div class='col-xs-1' style='background-color:red;'>PTO</div>
              <div class='col-xs-1' style='background-color:#282828;color:white;'>Weekend</div>
              <div class='col-xs-1' style='background-color:blue;color:white;'>Scheduled</div>
              <div class='col-xs-1' style='background-color:purple;color:white;'>Worked on Schedule</div>
            </div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          </div>
          <div class='panel-body'>
            <div class='row'>
              <div class='col-xs-9'>
                <table id='attendance' style=''>
                  <thead><tr>
                    <th>Attendance Sheet - <?php echo isset($_GET['Supervisor']) ? $_GET['Supervisor'] : 'All';?></th>
                    <?php $i = 1;
                    while($i <= cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'))){?><th><?php echo $i;?></th><?php $i++;}?></tr></thead>
                    <thead><tr>
                      <th>Name</th>
                      <?php $i = 1;
                      while($i <= cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'))){?><th><?php echo $i > 10 ? date("D",strtotime(date("Y-m-{$i} 00:00:00.000"))) : date("D",strtotime(date("Y-m-0{$i} 00:00:00.000")));?></th><?php $i++;}?></tr></thead>
                    <tbody style=''>
                      <?php
                      
                      if(isset($_GET['Supervisor'])  && strlen($_GET['Supervisor']) > 0) {
                        $result = $database->query(
                          null,
                          " SELECT    Emp.ID AS ID,
                                      Emp.fWork AS fWork,
                                      Emp.fFirst,
                                      Emp.Last
                            FROM      Emp
                                      LEFT JOIN tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                            WHERE         tblWork.Super = ?
                                      AND Emp.Status = 0
                            ORDER BY  Emp.Last ASC;",
                          array(
                            $_GET[ 'Supervisor' ]
                          )
                        );
                      } else {
                        $_GET[ 'Start' ] = date( 'Y-m-d H:i:s', strtotime( $_GET[ 'Start' ] ) );
                        $_GET[ 'End' ]   = date( 'Y-m-d H:i:s', strtotime( $_GET[ 'End' ] ) );
                        $result = $database->query(
                          null,
                          " SELECT    Top 25 
                                      Emp.ID AS ID,
                                      Emp.fWork AS fWork,
                                      Emp.fFirst,
                                      Emp.Last
                            FROM      Emp
                            ORDER BY  Emp.Last ASC;",
                          array( )
                        );
                      }
                      $data = array( );
                      $sQuery = " SELECT    Attendance.[Start], 
                                            Attendance.[End] 
                                  FROM      Attendance 
                                  WHERE     Attendance.[User] = ? 
                                  ORDER BY  Attendance.[ID] DESC;";
                      if( $result ){ while($row = sqlsrv_fetch_array( $result ) ){
                        $r2 = $database->query(null, $sQuery, array( $row[ 'ID' ]));
                        if($r2){
                          $row2 = sqlsrv_fetch_array($r2);
                          $row2 = is_array($row2) ? $row2 : array('Start'=>'1899-12-30 00:00:00.000', 'End'=>'1899-12-30 00:00:00.000');
                        }
                        $row['Start'] = $row2['Start'] == '1899-12-30 00:00:00.000' ? '' : date("m/d/Y H:i A",strtotime($row2['Start']));
                        $row['End'] = $row2['End'] == '1899-12-30 00:00:00.000' ? '' : date("m/d/Y H:i A",strtotime($row2['End']));
                        $data[] = $row;
                      }}
                      foreach($data as $user){?><tr>
                        <td style='border:1px solid black;text-align:right;'><a href='user.php?ID=<?php echo $user['ID'];?>'><?php echo $user['Last'] . ", " . $user['fFirst'];?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></td><?php
                        $i = 1;
                        while($i <= cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'))){
                          if(isset($_GET['Year'],$_GET['Month'])){
                            $today = $i < 10 ? date("{$_GET['Year']}-{$_GET['Month']}-0{$i} 00:00:00.000") : date("{$_GET['Year']}-{$_GET['Month']}-{$i} 00:00:00.000");
                          } else {
                            $today = $i < 10 ? date("Y-m-0{$i} 00:00:00.000") : date("Y-m-{$i} 00:00:00.000");
                          }
                          if($i + 1 < 10){
                            $i2 = $i + 1;
                            if(isset($_GET['Year'],$_GET['Month'])){
                              $tomorrow = date("{$_GET['Year']}-{$_GET['Month']}-0{$i2} 00:00:00.000");
                            } else {
                              $tomorrow = date("Y-m-0{$i2} 00:00:00.000");
                            }
                          } elseif($i == cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'))){
                            if(isset($_GET['Year'],$_GET['Month'])){
                              if($_GET['Month'] == 12){
                                $year = $_GET['Year'] + 1;
                                $tomorrow = date("{$year}-01-01 00:00:00.000");
                              } else {
                                $month = $_GET['Month'] + 1;
                                $tomorrow = date("{$_GET['Year']}-{$month}-01 00:00:00.000");
                              }
                            } else {
                              if(date("m") == 12){
                                $year = date("Y") + 1;
                                $tomorrow = date("{$year}-01-01 00:00:00.000");
                              } else {
                                $month = date("m") + 1;
                                $tomorrow = date("Y-{$month}-01 00:00:00.000");
                              }
                            }

                          } else {
                            $i2 = $i + 1;
                            if(isset($_GET['Year'],$_GET['Month'])){
                              $tomorrow = date("{$_GET['Year']}-{$_GET['Month']}-{$i2} 00:00:00.000");
                            } else {
                              $tomorrow = date("Y-m-{$i2} 00:00:00.000");
                            }
                          }
                          $Type = Null;
                          $r = $database->query(null,"SELECT Top 1 * FROM Attendance WHERE Attendance.[User] = ? AND Attendance.[Start] >= ? AND Attendance.[Start] < ? AND (Attendance.[End] < ? OR Attendance.[End] < ? OR Attendance.[End] IS NULL) ORDER BY DATEDIFF(MINUTE, Attendance.[Start], Attendance.[End]) DESC;",array($user['ID'],$today, $tomorrow, $tomorrow,date("Y-m-d H:i:s",strtotime("tomorrow",strtotime($tomorrow)))));
                          if($r){$row = sqlsrv_fetch_array($r);}
                          if(is_array($row)){
                            $Type = 'Ticket';
                            if(isset($row['End']) && !is_null($row['End']) && date("N",strtotime($today)) >= 6){
                              $t1 = strtotime($row['Start']);
                              $t2 = strtotime($row['End']);
                              $diff = $t1 - $t2;
                              $diff = $diff / ( 60 * 60 );
                              if($diff < 7.75){
                                $color = 'orange';
                              } else {
                                $color = 'darkgreen';
                              }
                            } elseif(isset($row['End']) && !is_null($row['End'])){
                              $t2 = strtotime($row['Start']);
                              $t1 = strtotime($row['End']);
                              $diff = $t1 - $t2;
                              $diff = $diff / ( 60 * 60 );
                              if($diff < 7.75){
                                $color = 'orange';
                              } else {
                                $color = 'green';
                              }
                            } else {
                              $r = $database->query(null,
                                " SELECT  *
                                  FROM    Employee_Schedule
                                  WHERE   Employee_Schedule.Employee = ?
                                          AND Employee_Schedule.Start >= ?
                                          AND Employee_Schedule.Start < ?
                                ;",array($user['ID'], $today, $tomorrow));
                              if($r){$row6 = sqlsrv_fetch_array($r);}
                              if(is_array($row6)){
                                $color = 'purple';
                              } else {
                                $color = 'yellow';
                              }
                              $Type = 'Schedule';
                            }
                          } else {
                            $r = $database->query(null,"SELECT Top 1 * FROM Unavailable WHERE Unavailable.Worker = ? AND Unavailable.fDate >= ? AND Unavailable.fDate < ?",array($user['fWork'], $today, $tomorrow));
                            if($r){$row3 = sqlsrv_fetch_array($r);}
                            if(is_array($row3)){
                              $row = $row3;
                              $color = 'red';
                              $Type = 'PTO';
                              $row['Start'] = date("Y-m-d",strtotime($row['fDate'])) . " " . date("H:i:s",strtotime($row['StartTime']));
                              $row['End'] = date("Y-m-d",strtotime($row['fDate'])) . " " . date("H:i:s",strtotime($row['EndTime']));
                            } elseif(intval(date("d")) != $i) {
                              if(date("d") > $i){
                                if(date("N",strtotime($today)) >= 6){
                                  $color = '#282828';
                                } else {
                                  $color = 'gray';
                                }
                                $Type = 'Past';
                              } else {
                                if(date("N",strtotime($today)) >= 6){
                                  $color = '#282828';
                                } else {
                                  $r = $database->query(null,
                                    " SELECT  *
                                      FROM    Employee_Schedule
                                      WHERE   Employee_Schedule.Employee = ?
                                              AND Employee_Schedule.Start >= ?
                                              AND Employee_Schedule.Start < ?
                                    ;",array($user['ID'], $today, $tomorrow));
                                  if($r){$row5 = sqlsrv_fetch_array($r);}
                                  if(is_array($row5)){
                                    $color = 'blue';
                                  } else {
                                    $color = 'white';
                                  }
                                  $Type = 'Schedule';
                                }
                              }
                            } else {
                              $color = 'white';
                            }
                          }
                          ?><td onClick="scheduler('<?php echo $Type;?>','<?php echo $today;?>','<?php echo $user['fWork'];?>','<?php echo $user['fFirst'];?>','<?php echo $user['Last'];?>','<?php echo isset($row['Start']) ? $row['Start'] : null;?>','<?php echo isset($row['End']) ? $row['End'] : null;?>','<?php echo isset($row2['Start']) ? $row2['Start'] : '';?>','<?php echo isset($row2['End']) ? $row2['End'] : '';?>');" style="border:1px solid black;background-color:<?php echo $color;?>;width:35px !important;<?php echo isset($border) ? $border : '';?>">&nbsp;</td><?php
                          $i++;
                        }
                      }
                      ?></tr>
                    </tbody>
                </table>
              </div>
            </div>
            <style>
            .popup input, .popup select {
              color:black !important;
            }
            </style>
            <script src="https://momentjs.com/downloads/moment.js"></script>
            <script>
            $("div#container").on('click',function(e){
            	if($(e.target).closest('.popup').length === 0 && $(e.target).closest('td').length === 0){
            		$('.popup').fadeOut(300);
            		$('.popup').remove();
            	}
            });
            function toggleScheduleType(link){
              if($(link).val() == 'Work'){
                $("#ScheduleWork").show();
                $("#SchedulePTO").hide();
                $("#ScheduleTicket").hide();
              } else if($(link).val() == 'PTO') {
                $("#ScheduleWork").hide();
                $("#ScheduleTicket").hide();
                $("#SchedulePTO").show();
              } else {
                $("#ScheduleWork").hide();
                $("#ScheduleTicket").show();
                $("#SchedulePTO").hide();
              }
            }
              function scheduler(Type, fDate, fWork, fFirst, Last, Start, End, Start2, End2){
                $(".popup").remove();
                if(Type == 'Past'){return;}
                //StartDate
                var StartDate = moment(Start, 'YYYY-MM-DD HH:mm:SSS')
                if(StartDate.isValid()){StartDate = StartDate.format("MM/DD/YYYY");}
                else{StartDate = "";}
                //StartTime
                var StartTime = moment(Start, 'YYYY-MM-DD HH:mm:SSS');
                if(StartTime.isValid()){StartTime = StartTime.format("hh:mm a");}
                else{StartTime = "";}
                //EndDate
                var EndDate = moment(End, 'YYYY-MM-DD HH:mm:SSS');
                if(EndDate.isValid()){EndDate = EndDate.format("MM/DD/YYYY");}
                else{EndDate = "";}
                //EndTime
                var EndTime = moment(End, 'YYYY-MM-DD HH:mm:SSS');
                if(EndTime.isValid()){EndTime = EndTime.format("hh:mm a");}
                else{EndTime = "";}

                var StartDate2 = moment(Start2, 'YYYY-MM-DD HH:mm:SSS')
                if(StartDate2.isValid()){StartDate2 = StartDate2.format("MM/DD/YYYY");}
                else{StartDate2 = "";}
                //StartTime
                var StartTime2 = moment(Start2, 'YYYY-MM-DD HH:mm:SSS');
                if(StartTime2.isValid()){StartTime2 = StartTime2.format("hh:mm a");}
                else{StartTime2 = "";}
                //EndDate
                var EndDate2 = moment(End2, 'YYYY-MM-DD HH:mm:SSS');
                if(EndDate2.isValid()){EndDate2 = EndDate2.format("MM/DD/YYYY");}
                else{EndDate2 = "";}
                //EndTime
                var EndTime2 = moment(End2, 'YYYY-MM-DD HH:mm:SSS');
                if(EndTime2.isValid()){EndTime2 = EndTime2.format("hh:mm a");}
                else{EndTime2 = "";}

                var Schedule = "style='display:none;'";
                var PTO = "style='display:none;'";
                var Ticket = "style='display:none;'";
                var ScheduleSelect = "";
                var PTOSelect = "";
                var TicketSelect = "";
                var SelectDisabled = '';
                if(Type == 'Ticket'){
                  Ticket = '';
                  TicketSelect = 'selected';
                  SelectDisabled = 'disabled';
                  //SelectDisabled = '';
                }
                else if(Type == 'PTO'){
                  PTO = '';
                  PTOSelect = 'selected';
                  //SelectDisabled = '';
                }
                else if(Type == 'Schedule'){
                  Schedule = '';
                  ScheduleSelect = 'selected';
                }


                var pto = "<div class='popup' style=''> <div class='panel panel-primary'> <div class='panel-heading'>Schedule</div> <div class='panel-body' style='padding:10px;'> <div class='row'> <div class='col-xs-4'>Schedule/PTO</div> <div class='col-xs-8'><select name='Type' onChange='toggleScheduleType(this);' " + SelectDisabled + "> <option value='Work' " + ScheduleSelect + ">Schedule</option> <option value='PTO' " + PTOSelect + ">Paid Time Off</option> <option value='Ticket' " + TicketSelect + ">Ticket</option> </select></div> </div> <div class='row'><div class='col-xs-12'>&nbsp;</div></div> <div class='row' id='ScheduleTicket' " + Ticket + "> <div class='col-xs-4'>First Name:</div> <div class='col-xs-8'>" + fFirst + "</div> <div class='col-xs-4'>Last Name:</div> <div class='col-xs-8'>" + Last + "</div> <input type='hidden' value='" + fWork + "' name='fWork' /><input type='hidden' value='" + fDate + "' name='fDate' /> <div class='col-xs-4'>Start:</div> <div class='col-xs-8'><input type='text' name='StartDate' value='" + StartDate + "' /></div> <div class='col-xs-4'>&nbsp;</div> <div class='col-xs-8'><input type='text' name='StartTime' value='" + StartTime + "' /></div> <div class='col-xs-4'>End</div> <div class='col-xs-8'><input type='text' name='EndDate' value='" + EndDate + "' /></div> <div class='col-xs-4'>&nbsp;</div> <div class='col-xs-8'><input type='text' name='EndTime' value='" + EndTime + "' /></div> <div class='col-xs-4'>&nbsp;</div> <div class='col-xs-12'><div class='row' id='ScheduleTickets'> </div></div> <script> $(document).ready(function(){ $.ajax({ url:" + '"get_schedule_tickets.php"' + ", method:" + '"POST"' + ", data:{ 'Start' : " + '"' + Start + '"' + ", 'End' : " + '"' + End + '"' + ", 'fWork' : " + '"' + fWork + '"' + " }, success:function(code){ $('#ScheduleTickets').html(code); } }); }); <\/script> </div> <div class='row' id='ScheduleWork' " + Schedule + "><form action='#' method='POST' id='form_Schedule_Work'> <input type='hidden' name='Type' value='Work' /> <div class='col-xs-4'>First Name:</div> <div class='col-xs-8'>" + fFirst + "</div> <div class='col-xs-4'>Last Name:</div> <div class='col-xs-8'>" + Last + "</div> <input type='hidden' value='" + fWork + "' name='fWork' /><input type='hidden' value='" + fDate + "' name='fDate' /> <div class='col-xs-4'>Start:</div> <div class='col-xs-8'><input type='text' name='StartDate' value='" + StartDate2 + "' /></div> <div class='col-xs-4'>&nbsp;</div> <div class='col-xs-8'><input type='text' name='StartTime' value='" + StartTime2 + "' /></div> <div class='col-xs-4'>End</div> <div class='col-xs-8'><input type='text' name='EndDate' value='" + EndDate2 + "' /></div> <div class='col-xs-4'>&nbsp;</div> <div class='col-xs-8'><input type='text' name='EndTime' value='" + EndTime2 + "' /></div> <div class='col-xs-4'>&nbsp;</div> <div class='col-xs-8'><input type='submit' value='Submit' /></div> </form></div> <div class='row' id='SchedulePTO' " + PTO + "><form action='#' method='POST' id=' id='form_Schedule_PTO'> <input type='hidden' name='Type' value='PTO' /> <div class='col-xs-4'>First Name:</div> <div class='col-xs-8'>" + fFirst + "</div> <div class='col-xs-4'>Last Name:</div> <div class='col-xs-8'>" + Last + "</div> <input type='hidden' value='" + fWork + "' name='fWork' /><input type='hidden' value='" + fDate + "' name='fDate' /> <div class='col-xs-4'>All Day</div> <div class='col-xs-8'> <select name='AllDay'> <option value='Yes'>Yes</option> <option value='No'>No</option> </select> </div> <div class='col-xs-4'>Date:</div> <div class='col-xs-8'><input type='text' value='" + StartDate + "' name='nothing' /></div> <div class='col-xs-4'>Start Time:</div> <div class='col-xs-8'><input type='text' name='StartTime' value='" + StartTime + "'/></div> <div class='col-xs-4'>End Time</div> <div class='col-xs-8'><input type='text' name='EndTime' value='" + EndTime + "' /></div> <div class='col-xs-4'>Description</div> <div class='col-xs-8'> <input type='hidden' name='Remarks' value='OUT' /> <select name='fDesc'> <option value='Sick'>Sick</option> <option value='Vacation'>Vacation</option> <option value='No Pay'>No Pay</option> <option value='Personal Day'>Personal Day</option> <option value='En Lieu'>En Lieu</option> <option value='Medical Day'>Medical Day</option> <option value='Other'>Other</option> </select> </div> <div class='col-xs-4'>&nbsp;</div> <div class='col-xs-8'><input type='submit' value='Submit' /></div> </form></div> </div> </div></div>";
                $("body").append(pto);
                $("input[name='StartTime']").timepicker();
                $("input[name='EndTime']").timepicker();
                $("input[name='StartDate']").datepicker();
                $("input[name='EndDate']").datepicker();

              }
            </script>
            <style>
              .popup {
                position:absolute;
                z-index:99;
                left:20%;
                right:20%;
                top:20%;
                /*bottom:20%;*/
                /*height:60%;*/
                width:60%;
                background-color:#2d2d2d !important;
                padding:0px;
                max-height:600px;
                overflow-y:scroll;
              }
            </style>
          </div>
        </div>
    </div>
	</div>
    
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    <link href="bin/libraries/fixedHeader.css" rel="stylesheet" type="text/css" media="screen">
    <script src="bin/libraries/fixedHeader.js"></script>
    <script>
      $(document).ready(function(){
        $('#attendance').fixedHeaderTable({height: '650', width:'1500' });
        $(".fht-table-wrapper").css("height","100%");
      });
    </script>
	<style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    div#map * {overflow:visible;}
    </style>
    <script>
	$(document).ready(function(){
		$("a[tab='overview-pills']").click();
	});
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>
  document.location.href="../login.php?Forward=scheduler.php?<?php if(count($_GET) > 0){$variables = array();foreach($_GET AS $key=>$value){$variables[] = "{$key}={$value}";}echo implode('&',$variables);}?>";
</script></head></html><?php }?>
