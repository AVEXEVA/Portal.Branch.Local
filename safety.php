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
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID'])
	   	|| !isset($My_Privileges['Unit'])
	  		|| $My_Privileges['Unit']['User_Privilege']  < 4
	  		|| $My_Privileges['Unit']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		/*$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "safety.php"));*/
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
      <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
      <?php require( bin_php . 'element/loading.php');?>
      <div id="page-wrapper" class='content'>
  			<div class="panel panel-primary">
  				<div class='panel-heading'>Safety Form</div>
  				<div class="panel-body"><form id='form-safety'>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'>
              <div class='col-xs-3'>Name:</div>
              <div class='col-xs-9'><input name='Name' type='text' value='<?php echo $My_User['First_Name'] . ' ' . $My_User['Last_Name'];?>' /></div>
            </div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'>
              <div class='col-xs-3'>Anonymous:</div>
              <div class='col-xs-9'><input type='checkbox' name='Anonymous' style='width:25px;height:25px;' onChange='toggleAnonymity();' /></div>
            </div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'>
              <div class='col-xs-12'>Report:</div>
              <div class='col-xs-12'><textarea style='width:100%;' rows='9'></textarea></div>
            </div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'><div class='col-xs-12'><button onClick='submitSafetyForm(this);' style='width:100%;height:42px;'>Submit</button></div></div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          </form></div>
        </div>
      </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    
    
    <script>
      function toggleAnonymity(){
        var toggle = $("input[name='Anonymous']").prop('checked');
        if(toggle){
          $("input[name='Name']").val("ANONYMOUS");
        } else {
          $("input[name='Name']").val("<?php echo $My_User['First_Name'] . ' ' . $My_User['Last_Name'];?>");
        }
      }
      function submitSafetyForm(link){
        $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
        $(link).attr('disabled','disabled');
        var formData = new FormData();
        formData.append("Report",$("textarea").val());
        formData.append("Anonymous",$("input[name='Anonymous']").prop('checked'));
        $.ajax({
          cache: false,
          processData: false,
          contentType: false,
          timeout:15000,
          error:function(XMLHttpRequest, textStatus, errorThrown){
            alert('Your ticket did not save. Please check your internet.')
            $(link).html("Save");
            $(link).prop('disabled',false);
          },
          url:"bin/php/post/safety_form.php",
          method:"POST",
          data:formData,
          success:function(code){
            alert("Success");
            $(link).prop("disabled",false);
          }
        });
      }
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
