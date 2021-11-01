<?php
session_start();
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
  $array = sqlsrv_fetch_array($r);
  if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Illinois'){
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php?ID=New"));
    $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($NEI,"SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege FROM Privilege WHERE User_ID = ?;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 6){$Privileged = TRUE;}
  }
  if(!isset($array['ID'])  || !$Privileged){require("401.html");}
  else {?><!DOCTYPE html>
<html lang="en">
<head>
  <title>Nouveau Texas | Portal</title>
  <?php require(PROJECT_ROOT.'php/meta.php');?>
  <?php require(PROJECT_ROOT."css/index.php");?>
  <style>
  .popup {
    z-index:999999999;
    position:absolute;
    margin-top:50px;
    top:0;
    left:0;
    background-color:#1d1d1d;
    height:100%;
    width:100%;
  }
  </style>
  <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload="finishLoadingPage();" style='background-color:#1d1d1d;'>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
    <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
    <?php require(PROJECT_ROOT.'php/element/loading.php');?>
    <div id="page-wrapper" class='content' style='<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
      <div class='panel-primary'>
        <div class='panel-heading' onClick='document.location.href="work.php";'><h4 style=''><?php $Icons->Ticket();?> Ticket: New</h4></div>
        <div class='panel-body'>
  				<div class='row'>
  					<div class='col-xs-12'>&nbsp;</div>
          </div>
          <div class='row'>
  					<div class='col-xs-4'><?php $Icons->User(1);?> Worker:</div>
  					<div class='col-xs-8'><?php echo $My_User['First_Name'] . " " . $My_User['Last_Name'];?></div>
          </div>
          <div class='row'>
  					<div class='col-xs-4'><?php $Icons->Calendar(1);?> Date:</div>
  					<div class='col-xs-8'><input name='Date' value='<?php echo isset($_GET['Date']) ? $_GET['Date'] : date('m/d/Y');?>'/></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
  					<div class='col-xs-4'><?php $Icons->Location(1);?> Location:</div>
  					<div class='col-xs-8'><button type='button' onClick='selectLocations(this);' style='width:100%;height:50px;'><?php
            $pass = false;
            if(isset($_GET['Location']) && is_numeric($_GET['Location'])){
              $r = sqlsrv_query($NEI,"SELECT * FROM Loc WHERE Loc.Loc = ?;",array($_GET['Location']));
              if($r){
                $row = sqlsrv_fetch_array($r);
                if(is_array($row)){
                  $pass = True;
                  echo $row['Tag'];
                }
              }
            }
            if(!$pass){?>Select Location<?php }?></button></div>
            <script>
              function selectLocations(link){
                $.ajax({
                  url:"cgi-bin/php/element/ticket/selectLocations.php",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row'>
            <div class='col-xs-4'><?php $Icons->Unit(1);?> Unit:</div>
            <div class='col-xs-8'><button type='button' onClick='selectUnits(this);' style='width:100%;height:50px;'><?php
            $pass = false;
            if(isset($_GET['Unit']) && is_numeric($_GET['Unit'])){
              $r = sqlsrv_query($NEI,"SELECT * FROM Elev WHERE Elev.ID = ?;",array($_GET['Unit']));
              if($r){
                $row = sqlsrv_fetch_array($r);
                if(is_array($row)){
                  $pass = True;
                  echo isset($row['State']) && strlen($row['State']) > 0 ? $row['State'] . ' - ' . $row['Unit'] : $row['Unit'];
                }
              }
            }
            if(!$pass){?>Select Unit<?php }?></button></div>
            <script>
              function selectUnits(link){
                $.ajax({
                  url:"cgi-bin/php/element/ticket/selectUnits.php?Location=<?php echo $_GET['Location'];?>",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row'>
            <div class='col-xs-4'><?php $Icons->Job(1);?> Job:</div>
            <div class='col-xs-8'><button type='button' onClick='selectJobs(this);' style='width:100%;height:50px;'><?php
            $pass = false;
            if(isset($_GET['Job']) && is_numeric($_GET['Job'])){
              $r = sqlsrv_query($NEI,"SELECT * FROM Job WHERE Job.ID = ?;",array($_GET['Job']));
              if($r){
                $row = sqlsrv_fetch_array($r);
                if(is_array($row)){
                  $pass = True;
                  echo $row['fDesc'];
                }
              }
            }
            if(!$pass){?>Select Job<?php }?></button></div>
            <script>
              function selectJobs(link){
                $.ajax({
                  url:"cgi-bin/php/element/ticket/selectJobs.php?Location=<?php echo $_GET['Location'];?>&Unit=<?php echo $_GET['Unit'];?>",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-4'><?php $Icons->Blank(1);?> Level:</div>
            <div class='col-xs-8'><select name='Level'>
              <option value=''>Select</option>
              <option value='1'>Service Call</option>
              <option value='2'>Trucking</option>
              <option value='3'>Modernization</option>
              <option value='4'>Violations</option>
              <option value='5'>Door Lock Monitoring</option>
              <option value='6'>Repair</option>
              <option value='7'>Annual Test</option>
              <option value='10'>Preventative Maintenance</option>
              <option value='11'>Survey</option>
              <option value='12'>Engineering</option>
              <option value='13'>Support</option>
              <option value='14'>M&R</option>'
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-12'>&nbsp;</div>
          </div>
        </div>
        <div class='panel-heading'><?php $Icons->Description(1);?> Description:</div>
        <div class='panel-body'>
          <div class='row'>
  					<div class='col-xs-12'><textarea style='width:100%;' rows='8' name='Description'></textarea></div>
          </div>
        </div>
        <div class='panel-body'>
          <div class='row'>
            <div class='col-xs-12'>&nbsp;</div>
          </div>
  				<div class='row'>
  					<div class='col-xs-12'><button onClick='saveTicket(this);' style='width:100%;height:50px;' >Save</button></div>
            <script>
            function saveTicket(link){
              $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
              var ticketData = new FormData();
              ticketData.append('Date',$("input[name='Date']").val());
              ticketData.append('Location','<?php echo isset($_GET['Location']) ? $_GET['Location'] : '';?>');
              ticketData.append('Unit','<?php echo isset($_GET['Unit']) ? $_GET['Unit'] : '';?>');
              ticketData.append('Job','<?php echo isset($_GET['Job']) ? $_GET['Job'] : '';?>');
              ticketData.append('Description',$("textarea[name='Description']").val());
              ticketData.append('Level',$("select[name='Level']").val());
              if(ticketData.get('Date') == '' || ticketData.get('Location') == '' || ticketData.get('Job') == '' || ticketData.get('Description') == ''){
                alert('Please fill out the necessasry information in order to continue.');
              } else {
                $.ajax({
                  url:"cgi-bin/php/post/save_new_ticket.php",
                  cache: false,
                  processData: false,
                  contentType: false,
                  method:"POST",
                  data: ticketData,
                  success:function(code){document.location.href='ticket.php?ID=' + code;}
                });
              }
            }
            </script>
  				</div>
          <div class='row'>
            <div class='col-xs-12'>&nbsp;</div>
          </div>
    		</div>
    	</div>
    </div>
  </div>
	<style>
	  .ui-autocomplete {
		max-height: 100px;
		overflow-y: auto;
		/* prevent horizontal scrollbar */
		overflow-x: hidden;
	  }
	  /* IE 6 doesn't support max-height
	   * we use height instead, but this forces the menu to always be this tall
	   */
	  * html .ui-autocomplete {
		height: 100px;
	  }
	  </style>
	  <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <?php require(PROJECT_ROOT.'js/datatables.php');?>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

	<script>
	$(document).ready(function(){$("input[name='Date']").datepicker();});
	function closePopup(link){$(".popup").remove();}
	</script>
</body>
</html>
<?php }
}?>
