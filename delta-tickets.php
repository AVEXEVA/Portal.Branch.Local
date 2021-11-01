<?php
session_start();
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Connection
		WHERE  Connection.Connector = ?
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
		SELECT *
		FROM   Privilege
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID'])
	   	|| !isset($My_Privileges['Customer'])
	  		|| $My_Privileges['Customer']['User_Privilege']  < 4
	  		|| $My_Privileges['Customer']['Group_Privilege'] < 4
	  		|| $My_Privileges['Customer']['Other_Privilege'] < 4){
				?><?php require('../401.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "units.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
    <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
    <?php require(PROJECT_ROOT.'php/element/loading.php');?>
    <div id="page-wrapper" class='content'>
	    <div class="panel panel-primary">
  			<div class="panel-heading"><h3><?php $Icons->Ticket();?> Newark Delta Tickets</h3></div>
  			<div class="panel-body">
          <div class='row'>
          <div class='col-xs-2'>Start:</div>
          <div class='col-xs-10'><input type='text' name='Start' /></div>
          <div class='col-xs-2'>End:</div>
          <div class='col-xs-10'><input type='text' name='End' /></div>
          <div class='col-xs-3'><button onClick='getTickets();'>Get Tickets</button></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <style>
  #page-wrapper .panel-primary {
    background-color:white !important;
    color:black;
  }
  </style>
  <!-- Bootstrap Core JavaScript -->
  <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

  <!-- JQUERY UI Javascript -->
  <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
  <script>
  function getTickets(){
    document.location.href='delta-tickets.php?Start=' + $("input[name='Start']").val() + "&End=" + $("input[name='End']").val();
  }
  $(document).ready(function(){
    $("input[name='Start']").datepicker();
    $("input[name='End']").datepicker();
  });
  <?php
    $_GET['Start'] = date("Y-m-d 00:00:00.000",strtotime($_GET['Start']));
    $_GET['End'] = date("Y-m-d 00:00:00.000",strtotime($_GET['End']));
    $r = sqlsrv_query($NEI,
      " SELECT  Tickets.ID AS ID
        FROM    ((SELECT * FROM nei.dbo.TicketD)) AS Tickets
                LEFT JOIN nei.dbo.Job ON Tickets.Job = Job.ID
        WHERE   Tickets.EDate >= ?
                AND Tickets.EDate < ?
                AND (Job.Loc = 8438 OR Job.ID = 96856)
                AND (Job.Type = 0 OR Job.Type = 6)
        ORDER BY Tickets.EDate ASC;
      ",array($_GET['Start'], $_GET['End']));
      if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
    if($r){
      while($row = sqlsrv_fetch_array($r)){
        ?>$(document).ready(function(){
          $.ajax({
            url:"short-ticket.php?ID=<?php echo $row['ID'];?>",
            method:"GET",
            success:function(code){$("#page-wrapper").append(code);}
          })
        });<?php
      }
    }
  ?>
  </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=delta-tickets.php';</script></head></html><?php }?>
