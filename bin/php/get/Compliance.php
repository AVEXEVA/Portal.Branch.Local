<?php
session_start( [ 'read_and_close' => true ] );
$_GET['Type'] = isset($_GET['Type']) ? $_GET['Type'] : 'Live';
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
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID'])
	   	|| !isset($My_Privileges['Admin'])
	  		|| $My_Privileges['Admin']['User_Privilege']  < 4
	  		|| $My_Privileges['Admin']['Group_Privilege'] < 4
	  	    || $My_Privileges['Admin']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "accounting.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Elevator Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
<div id='container'>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
      <?php require( bin_php . 'element/navigation/index.php');?>
      <?php require( bin_php . 'element/loading.php');?>
      <div id="page-wrapper" class='content' style='margin-right:0px !important;'>
        <div class='panel-panel-primary'>
          <div class='panel-heading'>Accounting Department</div>
          <div class='panel-body'>
            <div class='row'>
              <div class='col-xs-12' id='Timeline'>

              </div>
              <script>
              var TIMELINE = new Array();
              var GETTING_TIMELINE = 0;
              var Last_ID = 0;
              function numberWithCommas(x) {
                  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
              }
              function getTimeline(){
                if(GETTING_TIMELINE == 0){
                  GETTING_TIMELINE = 1;
                  $.ajax({
                    url:"bin/php/get/Compliance.php",
                    data:{
                      ID : Last_ID
                    },
                    method:"GET",
                    success:function(code){
                      var jsonData = JSON.parse(code);
                      for(i in jsonData){
                        Last_ID = i;
                        if(TIMELINE[i]){} 
                        else {
                          TIMELINE[i] = jsonData[i];
                          $("#Timeline").prepend("<div class='row'>"
                            + '<div class="col-xs-1"><?php \singleton\fontawesome::getInstance( )->Violation(1);?></div>'
                            + "<div class='col-xs-3'>" + jsonData[i].Location_Tag + "</div>"
                            + "<div class='col-xs-1'>Violation #" + jsonData[i].ID + "</div>"
                            + "<div class='col-xs-1'>" + jsonData[i].Action + "</div>"
                            //+ "<div class='col-xs-1'>$" + numberWithCommas(jsonData[i].Amount) + "</div>"
                          + "</div>");
                        }
                      }
                      GETTING_TIMELINE = 0;
                    }
                  });
                }
              }
              $(document).ready(function(){
                getTimeline();
                setInterval(getTimeline, 5000);
              });
              </script>
            </div>
          </div>
        </div>
      </div>
  </div>
</div>
</body>
</html>
<?php
  }
} ?>
