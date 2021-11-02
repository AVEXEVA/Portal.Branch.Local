<?php
session_start();
require('cgi-bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != 'OFFICE') ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Time']) && $My_Privileges['Time']['User_Privilege'] >= 4 && $My_Privileges['Time']['Group_Privilege'] >= 4 && $My_Privileges['Time']['Other_Privilege'] >= 4){
        	$Privileged = TRUE;
		    }
    }
    //
    if(!isset($array['ID']) || !$Privileged){require('401.html');}
    else {
      if(isset($_POST) && count($_POST) > 0){
        $r = sqlsrv_query($NEI,"SELECT Max(ID) AS ID FROM nei.dbo.Unavailable;");
        $ID = sqlsrv_fetch_array($r)['ID'] + 1;
        $r = sqlsrv_query($NEI,"INSERT INTO nei.dbo.Unavailable(ID, fDate, Worker, fDesc, AllDay, StartTime, EndTime, Remarks) VALUES(?, ?, ?, ?, ?, ?, ?, ?);",array($ID, $_POST['fDate'], $_POST['fWork'], $_POST['fDesc'], $_POST['AllDay'], date("H:i",strtotime($_POST['StartTime'])), date("H:i",strtotime($_POST['EndTime'])), $_POST['Remarks']));
        if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
      }
		sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "attendance2.php"));
?><!DOCTYPE html>
<html lang="en"style="min-height:100%;height:100%;webkit-background-size: cover;-moz-background-size: cover;-o-background-size: cover;background-size: cover;height:100%;">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
    <meta http-equiv="refresh" content="300">
	<title>Nouveau Illinois Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
    <link rel='stylesheet' href='cgi-bin/libraries/timepicker/jquery.timepicker.min.css' />
    <script src='cgi-bin/libraries/timepicker/jquery.timepicker.min.js'></script>
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
<body onload='finishLoadingPage();' style="min-height:100%;background-size:cover;background-color:rgba(255,255,255,.7);height:100%;">
    <div id='container' style='min-height:100%;height:100%;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>" style='height:100%;'>
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
          <div class='panel'>
            <div class='panel-heading'>
              <h4>Attendance</h4>
            </div>
            <div class='panel-body'>
              <div class='row'>
              <form action='attendance2.php'>
                <!--<div class='col-xs-12'>Start: <input name='Start' value='<?php echo isset($_GET['Start']) ? $_GET['Start'] : '';?>' /></div>
                <div class='col-xs-12'>End: <input name='End'  value='<?php echo isset($_GET['End']) ? $_GET['End'] : '';?>'   /></div>
                <div class='col-xs-12'><input type='submit' value='Search' /></div>-->
                <div class='col-xs-12'>Supervisor: <select name='Supervisor'><?php
                  $r = sqlsrv_query($NEI,"SELECT tblWork.Super FROM nei.dbo.tblWork GROUP BY tblWork.Super ORDER BY tblWork.Super ASC;");
                  if($r){while($row = sqlsrv_fetch_array($r)){
                    ?><option value='<?php echo $row['Super'];?>' <?php if(isset($_GET['Supervisor']) && $_GET['Supervisor'] == $row['Super']){?>selected<?php }?>><?php echo $row['Super'];?></option><?php
                  }}
                ?></select>
                <div class='col-xs-12'><input type='submit' value='Search' /></div>
              </form>
              </div>
            </div>
          </div>
          <div class='panel-body'>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'>
              <div class='col-xs-4'>&nbsp;</div>
              <div class='col-xs-1' style='background-color:gold;'>Clocked In</div>
              <div class='col-xs-1' style='background-color:green;color:white;'>Worked Day</div>
              <div class='col-xs-1' style='background-color:red;'>PTO</div>
              <div class='col-xs-1' style='background-color:#282828;color:white;'>Weekend</div>
            </div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          </div>
          <div class='panel-body'>
            <div style='margin-left:100px;'>
            <table id='attendance' style=''>
              <!--<?php $i = 1;?><colgroup><?php while($i < 32){?><col style='<?php if($i == intval(date("d"))){?>border:5px solid black !important;<?php }?>'></col><?php $i++;}?></colgroup>-->
              <thead><tr>
                <th>Attendance Sheet - <?php echo isset($_GET['Supervisor']) ? $_GET['Supervisor'] : 'All';?></th>
                <?php $i = 1;
                while($i < 32){?><th><?php echo $i;?></th><?php $i++;}?></tr></thead>
                <thead><tr>
                  <th>Name</th>
                  <?php $i = 1;
                  while($i < 32){?><th><?php echo $i > 10 ? date("D",strtotime(date("Y-m-{$i} 00:00:00.000"))) : date("D",strtotime(date("Y-m-0{$i} 00:00:00.000")));?></th><?php $i++;}?></tr></thead>
                <tbody style=''>
                  <?php
                  if(isset($_GET['Supervisor'])  && strlen($_GET['Supervisor']) > 0) {
                    //$_GET['Start'] = date('Y-m-d H:i:s',strtotime($_GET['Start']));
                    //$_GET['End'] = date('Y-m-d H:i:s',strtotime($_GET['End']));
                    $r = sqlsrv_query($Portal,"
                      SELECT Emp.ID AS ID,
                             Emp.fWork AS fWork,
                             Emp.fFirst,
                             Emp.Last
                      FROM   Emp
                             LEFT JOIN nei.dbo.tblWork ON 'A' + convert(varchar(10),Emp.ID) + ',' = tblWork.Members
                      WHERE  tblWork.Super = ?
                             AND Emp.Status = 0
                      ORDER BY Emp.Last ASC
                    ;",array($_GET['Supervisor']));
                  } else {
                    $_GET['Start'] = date('Y-m-d H:i:s',strtotime($_GET['Start']));
                    $_GET['End'] = date('Y-m-d H:i:s',strtotime($_GET['End']));
                    $r = sqlsrv_query($Portal,"
                      SELECT Top 25 Emp.ID AS ID,
                             Emp.fWork AS fWork,
                             Emp.fFirst,
                             Emp.Last
                      FROM   Emp
                      ORDER BY Emp.Last ASC
                    ;",array());
                  }
                  $data = array();
                  $sQuery = "SELECT Attendance.[Start], Attendance.[End] FROM Portal.dbo.Attendance WHERE Attendance.[User] = ? ORDER BY Attendance.[ID] DESC;";
                  if($r){while($row = sqlsrv_fetch_array($r)){
                    $r2 = sqlsrv_query($Portal, $sQuery, array($row['ID']));
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
                    while($i < 32){
                      $today = $i < 10 ? date("Y-m-0{$i} 00:00:00.000") : date("Y-m-{$i} 00:00:00.000");
                      if($i + 1 < 10){
                        $i2 = $i + 1;
                        $tomorrow = date("Y-m-0{$i2} 00:00:00.000");
                      } elseif($i == 30){
                        $month = date("m") + 1;
                        $tomorrow = date("Y-{$month}-01 00:00:00.000");
                      } else {
                        $i2 = $i + 1;
                        $tomorrow = date("Y-m-{$i2} 00:00:00.000");
                      }
                      $r = sqlsrv_query($NEI,"SELECT Top 1 * FROM Portal.dbo.Attendance WHERE Attendance.[User] = ? AND Attendance.[Start] >= ? AND Attendance.[Start] < ? AND (Attendance.[End] < ? OR Attendance.[End] IS NULL) ORDER BY Attendance.ID DESC;",array($user['ID'],$today, $tomorrow, $tomorrow, $tomorrow));
                      if($r){$row = sqlsrv_fetch_array($r);}
                      if(is_array($row)){
                        if(isset($row['End']) && !is_null($row['End']) && date("N",strtotime($today)) >= 6){
                          $color = 'darkgreen';
                        } elseif(isset($row['End']) && !is_null($row['End'])){
                          $color = 'green';
                        } else {
                          $color = 'yellow';
                        }
                      } else {
                        $r = sqlsrv_query($NEI,"SELECT Top 1 * FROM nei.dbo.Unavailable WHERE Unavailable.Worker = ? AND Unavailable.fDate >= ? AND Unavailable.fDate < ?",array($user['fWork'], $today, $tomorrow));
                        if($r){$row = sqlsrv_fetch_array($r);}
                        if(is_array($row)){
                          $color = 'red';
                        } elseif(intval(date("d")) != $i) {
                          if(date("d") > $i){
                            if(date("N",strtotime($today)) >= 6){
                              $color = '#282828';
                            } else {
                              $color = 'gray';
                            }
                          } else {
                            if(date("N",strtotime($today)) >= 6){
                              $color = '#282828';
                            } else {
                              $color = 'lightgray';
                            }
                          }
                        } else {
                          $color = 'white';
                        }
                      }
                      ?><td onClick="<?php if($color != 'green' && $color !='yellow' && $color != 'darkgreen' && $today >= date("Y-m-d 00:00:00.000",strtotime('yesterday'))){?>schedule_pto('<?php echo $today;?>','<?php echo $user['fWork'];?>','<?php echo $user['fFirst'];?>','<?php echo $user['Last'];?>');<?php }?>" style="border:1px solid black;background-color:<?php echo $color;?>;width:35px !important;<?php echo isset($border) ? $border : '';?>">&nbsp;</td><?php
                      $i++;
                    }
                  }
                  ?></tr>
                </tbody>
            </table>
            </div>
            <style>
            .popup input, .popup select {
              color:black !important;
            }
            </style>
            <script>
            $("div#container").on('click',function(e){
            	if($(e.target).closest('.popup').length === 0 && $(e.target).closest('td').length === 0){
            		$('.popup').fadeOut(300);
            		$('.popup').remove();
            	}
            });
              function schedule_pto(fDate, fWork, fFirst, Last){
                var pto = "<div class='popup' style=''><form action='#' method='POST'><div class='panel panel-primary'><div class='panel-heading'>Schedule Paid Time Off</div><div class='panel-body' style='padding:10px;'><div class='row'><div class='col-xs-4'>First Name:</div><div class='col-xs-8'>" + fFirst + "</div><div class='col-xs-4'>Last Name:</div><div class='col-xs-8'>" + Last + "</div><input type='hidden' value='" + fWork + "' name='fWork' /><input type='hidden' value='" + fDate + "' name='fDate' /><div class='col-xs-4'>All Day</div><div class='col-xs-8'><select name='AllDay'><option value='Yes'>Yes</option><option value='No'>No</option></select></div><div class='col-xs-4'>Start Time:</div><div class='col-xs-8'><input type='text' name='StartTime' /></div><div class='col-xs-4'>End Time</div><div class='col-xs-8'><input type='text' name='EndTime' /></div><div class='col-xs-4'>Description</div><div class='col-xs-8'><input type='hidden' name='Remarks' value='OUT' /><select name='fDesc'><option value='Sick'>Sick</option><option value='Vacation'>Vacation</option><option value='No Pay'>No Pay</option><option value='Personal Day'>Personal Day</option><option value='En Lieu'>En Lieu</option><option value='Medical Day'>Medical Day</option><option value='Other'>Other</option></select></div><div class='col-xs-4'>&nbsp;</div><div class='col-xs-8'><input type='submit' value='Submit' /></div></div></div></div></form></div>";
                $("body").append(pto);
                $("input[name='StartTime']").timepicker();
                $("input[name='EndTime']").timepicker();

              }
            </script>
            <style>
              .popup {
                position:absolute;
                z-index:99;
                left:20%;
                right:20%;
                top:20%;
                bottom:20%;
                height:60%;
                width:60%;
                background-color:white;
                padding:0px;
              }
            </style>
          </div>
        </div>
    </div>
	</div>
    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>


    <link href="cgi-bin/libraries/fixedHeader.css" rel="stylesheet" type="text/css" media="screen">
    <script src="cgi-bin/libraries/fixedHeader.js"></script>
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
  document.location.href="../login.php?Forward=attendance2.php?<?php if(count($_GET) > 0){$variables = array();foreach($_GET AS $key=>$value){$variables[] = "{$key}={$value}";}echo implode('&',$variables);}?>";
</script></head></html><?php }?>
