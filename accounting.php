<?php
session_start();
$_GET['Type'] = isset($_GET['Type']) ? $_GET['Type'] : 'Live';
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
	   	|| !isset($My_Privileges['Admin'])
	  		|| $My_Privileges['Admin']['User_Privilege']  < 4
	  		|| $My_Privileges['Admin']['Group_Privilege'] < 4
	  	    || $My_Privileges['Admin']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "accounting.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();'>
<div id='container'>
  <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
      <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
      <?php require(PROJECT_ROOT.'php/element/loading.php');?>
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
              var Last_Ref = 0;
              function numberWithCommas(x) {
                  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
              }
              function getTimeline(){
                if(GETTING_TIMELINE == 0){
                  GETTING_TIMELINE = 1;
                  $.ajax({
                    url:"cgi-bin/php/get/Accounting.php",
                    data:{
                      Ref : Last_Ref
                    },
                    method:"GET",
                    success:function(code){
                      var jsonData = JSON.parse(code);
                      for(i in jsonData){
                        Last_Ref = i;
                        if(TIMELINE[i]){}
                        else {
                          TIMELINE[i] = jsonData[i];
                          $("#Timeline").prepend("<div class='row'>"
                            + '<div class="col-xs-1"><?php $Icons->Invoice(1);?></div>'
                            + "<div class='col-xs-1'>Invoice</div>"
                            + "<div class='col-xs-1'>#" + jsonData[i].Ref + "</div>"
                            + "<div class='col-xs-1'>$" + numberWithCommas(jsonData[i].Amount) + "</div>"
                            + "<div class='col-xs-3'>" + jsonData[i].Location_Tag + "</div>"
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
