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
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "units.php"));
		if(count($_POST) > 0){
			$_POST['Registration'] = isset($_POST['Registration']) ? $_POST['Registration'] : 0;
			$_POST['Safety_Equipment'] = isset($_POST['Safety_Equipment']) ? $_POST['Safety_Equipment'] : 0;
			$_POST['Tire_Pressure'] = isset($_POST['Tire_Pressure']) ? $_POST['Tire_Pressure'] : 0;
			$_POST['Oil'] = isset($_POST['Oil']) ? $_POST['Oil'] : 0;
			$_POST['All_Fluids'] = isset($_POST['All_Fluids']) ? $_POST['All_Fluids'] : 0;
			$_POST['Liftgate_and_Handheld'] = isset($_POST['Liftgate_and_Handheld']) ? $_POST['Liftgate_and_Handheld'] : 0;
			$_POST['Inspection_Sticker'] = isset($_POST['Inspection_Sticker']) ? $_POST['Inspection_Sticker'] : 0;
			$_POST['Lights_and_Blinkers'] = isset($_POST['Lights_and_Blinkers']) ? $_POST['Lights_and_Blinkers'] : 0;
			$_POST['Leaf_Springs'] = isset($_POST['Leaf_Springs']) ? $_POST['Leaf_Springs'] : 0;
			$_POST['Mirrors'] = isset($_POST['Mirrors']) ? $_POST['Mirrors'] : 0;
			$_POST['Backup_Beeper'] = isset($_POST['Backup_Beeper']) ? $_POST['Backup_Beeper'] : 0;
			$_POST['Cleaned'] = isset($_POST['Cleaned']) ? $_POST['Cleaned'] : 0;
			$_POST['Floor_Empty'] = isset($_POST['Floor_Empty']) ? $_POST['Floor_Empty'] : 0;
			$database->query(null,"
				INSERT INTO Portal.dbo.Vehicle_Inspection([User], Registration, Safety_Equipment, Tire_Pressure, Oil, All_Fluids, Liftgate_and_Handheld, Inspection_Sticker, Lights_and_Blinkers, Leaf_Springs, Mirrors, Backup_Beeper, Cleaned, Floor_Empty)
				VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)
			;", array($_SESSION['User'], $_POST['Registration'], $_POST['Safety_Equipment'], $_POST['Tire_Pressure'], $_POST['Oil'], $_POST['All_Fluids'], $_POST['Liftgate_and_Handheld'], $_POST['Inspection_Sticker'], $_POST['Lights_and_Blinkers'], $_POST['Leaf_Springs'], $_POST['Mirrors'], $_POST['Backup_Beeper'], $_POST['Cleaned'], $_POST['Floor_Empty']));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h4><?php \singleton\fontawesome::getInstance( )->Back();?>  Vehicle Inspection</h4></div>
				<div class="panel-body" style='background-color:rgba(255,255,255,.5);'>
					<form action='#' method='POST'>
						<div class='row' style='padding:25px;'>
							<?php
							function generateMessageID()
							{
							  return sprintf(
								"<%s.%s@%s>",
								base_convert(microtime(), 10, 36),
								base_convert(bin2hex(openssl_random_pseudo_bytes(8)), 16, 36),
								$_SERVER['SERVER_NAME']
							  );
							}
							$to = 'psperanza@NouveauElevator.com';
							$from = "WebServices@NouveauElevator.com";
							$replyto = $from;
							$date = date("Y-m-d H:i:s");
							$subject = "Vehicle Inspection: " . $My_User['First_Name'] . " " . $My_User['Last_Name'];
							$message = "<div>This message contains details for the Vehicle Inspection filled out by {$My_User['First_Name']} {$My_User['Last_Name']}.</div><table><tr><td>";
							$message .= isset($_POST['Registration']) && $_POST['Registration'] == 1 ? "Vehicle Registration</td><td>Valid\n" : "<b>Vehicle Registration</td><td>Expired</b>\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Inspection_Sticker']) && $_POST['Inspection_Sticker'] == 1 ? "Inspection Sticker</td><td>Valid\n" : "Inspection Sticker</td><td>Expired\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Tire_Pressure']) && $_POST['Tire_Pressure'] == 1 ? "Tire Pressure</td><td>Valid\n" : "Vehicle Registration</td><td>Expired\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Safety_Equipment']) && $_POST['Safety_Equipment'] == 1 ? "Safety Equipment</td><td>In Vehicle\n" : "Safety Equipement</td><td>Missing\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Oil']) && $_POST['Oil'] == 1 ? "Oil</td><td>Operable\n" : "Oil</td><td>Inoperable\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Tire_Pressure']) && $_POST['Tire_Pressure'] == 1 ? "Tire Pressure</td><td>Operable\n" : "Tire Pressure</td><td>Inoperable\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['All_Fluids']) && $_POST['All_Fluids'] == 1 ? "All_Fluids</td><td>Operabled\n" : "All_Fluids</td><td>Inoperabled\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Liftgate_and_Handheld']) && $_POST['Liftgate_and_Handheld'] == 1 ? "Liftgate and Handheld</td><td>Operable\n" : "Liftgate and Handheld</td><td>Inoperable\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Lights_and_Blinkers']) && $_POST['Lights_and_Blinkers'] == 1 ? "Light and Blinkers</td><td>Operable\n" : "Light and Blinkers</td><td>Inoperable\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Leaf_Springs']) && $_POST['Leaf_Springs'] == 1 ? "Leaf Springs</td><td>Operable\n" : "Leaf Springs</td><td>Inoperable\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Backup_Beeper']) && $_POST['Backup_Beeper'] == 1 ? "Backup Beeper</td><td>Operable\n" : "Backup Beeper</td><td>Inoperable\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Mirrors']) && $_POST['Mirrors'] == 1 ? "Mirrors</td><td>Operable\n" : "Mirrors</td><td>Inoperable\n";
							$message .= "</td></tr><tr><td>";
							$message .= isset($_POST['Cleaned']) && $_POST['Cleaned'] == 1 ? "Vehicle</td><td>Cleaned\n" : "Vehicle</td><td>Dirty\n";
							$message .= "</tr>";
							$message .= "</ol>";
							$Arranger = "WebServices";
							$headers = array();
							$headers[] = "MIME-Version: 1.0";
							$headers[] = "Content-type: text/html; charset=iso-8859-1";
							$headers[] = "Mesaage-id: " .generateMessageID();
							$headers[] = "From: 'WebServices' <$from>";
							$headers[] = "Reply-To: $Arranger <$replyto>"; 
							$headers[] = "Date: $date";
							$headers[] = "Return-Path: <$from>";
							$headers[] = "X-Priority: 3";//1 = High, 3 = Normal, 5 = Low
							$headers[] = "X-Mailer: PHP/" . phpversion();
							mail($to, $subject, $message, implode("\r\n", $headers))
							?>
							</br>
							Thank you for submitting your Vehicle Inspection. You will be redirected to the home page.
							<script>
							$(document).ready(function(){
								setTimeout(function(){document.location.href='index.php';},2500);	
							});
							</script>
						</div>
					</form>
				</div>
            </div>
        </div>
    </div>
    
        
    
    
</body>
</html>
<?php
		} else {
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h4 onClick="document.location.href='index.php';"><?php \singleton\fontawesome::getInstance( )->Back();?>  Vehicle Inspection</h4></div>
				<div class="panel-body" style='background-color:rgba(255,255,255,.5);'>
					<form action='#' method='POST'>
						<div class='row'>
							<style>
							input {
								width:25px;
								height:25px;
								border-radius:5px;
								border:2px solid #555;
							}
								b { font-size:18px;}
							</style>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>Vehicle Registration</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Registration' size='25' /> Valid &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Registration' size='25' /> Expired</div>
								</div>
							</div>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>Vehicle Inspection</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Inspection_Sticker' size='25' /> Valid &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Inspection_Sticker' size='25' /> Expired</div>
								</div>
							</div>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>Safety Equipment</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Safety_Equipment' size='25' /> In Truck &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Safety_Equipment' size='25' /> Missing</div>
								</div>
							</div>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>Tire Pressure</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Tire_Pressure' size='25' /> Operable &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Tire_Pressure' size='25' /> Inoperable</div>
								</div>
							</div>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>Oil</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Oil' size='25' /> Operable&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Oil' size='25' /> Inoperable</div>
								</div>
							</div>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>All Fluids Satisfactory</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='All_Fluids' size='25' /> Operable &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='All_Fluids' size='25' /> Inoperable</div>
								</div>
							</div>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>Liftgate & Handheld</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Liftgate_and_Handheld' size='25' /> Operable &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Liftgate_and_Handheld' size='25' /> Inoperable</div>
								</div>
							</div>
							
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>All Lights and Blinkers</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Lights_and_Blinkers' size='25' /> Operable &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Lights_and_Blinkers' size='25' /> Inoperable</div>
								</div>
							</div>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>Leaf Springs</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Leaf_Springs' size='25' /> Operable &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Leaf_Springs' size='25' /> Inopreable</div>
								</div>
							</div>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>Backup Beeper</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Backup_Beeper' size='25' /> Operable &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Backup_Beeper' size='25' /> Inoperable</div>
								</div>
							</div>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>Mirrors</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Mirrors' size='25' /> Operable &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Mirrors' size='25' /> Inoperable</div>
								</div>
							</div>
							<div class='col-xs-12' style='margin:0px;padding:0px;'>
								<div style='background-color:#3d3d3d;color:white;padding:15px;'><b>Cleaned Truck Internally</b></div>
								<div style='text-align:center;padding:10px;'>
									<div><input type='radio' value='1' name='Cleaned' size='25' /> Yes &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='radio' value='0' name='Cleaned' size='25' /> No</div>
								</div>
							</div>
							<!--<div class='col-xs-12'><input type='checkbox' value='1' name='Safety_Equipment' /> Check Safety Equipment in Truck</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='Tire_Pressure' /> Check Tire Pressure</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='Oil' /> Check Oil</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='All_Fluids' /> Check All Fluids</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='Liftgate_and_Handheld' /> Check Liftgate & Handheld</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='Inspection_Sticker' /> Check Vehicle Inspection</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='Lights_and_Blinkers' /> Check All Lights & Blinkers</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='Leaf_Springs' /> Check Leaf Springs</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='Mirrors' /> Check Mirrors</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='Backup_Beeper' /> Check Backup Beeper</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='Cleaned' /> Truck is Clean Inside</div>
							<div class='col-xs-12'><input type='checkbox' value='1' name='Floor_Empty' /> No Items on Floor or Items are Mounted</div>-->
							<div class='col-xs-12' style='border-bottom:5px solid #3d3d3d;'>&nbsp;</div>
							<div class='col-xs-12'>&nbsp;</div>
							<div class='col-xs-12' style='text-align:center;'><input type='submit' value='Submit Inspection' style='width:auto !important;'/></div>
							<div class='col-xs-12'>&nbsp;</div>
							<div class='col-xs-12'>&nbsp;</div>
						</div>
						
					</form>
				</div>
            </div>
        </div>
    </div>
    
        
    
    
</body>
</html>
<?php
		}
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>