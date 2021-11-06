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
	   	|| !isset($My_Privileges['Ticket'])
	  		|| $My_Privileges['Ticket']['User_Privilege']  < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
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
    <meta name="author" content="Peter D. Speranza">
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
	<!--  base structure css  -->
	<link href="css/ufd-base.css" rel="stylesheet" type="text/css" />

	<!--  plain css skin  -->
	<link href="css/plain.css" rel="stylesheet" type="text/css" />

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
</head>
<body onload='finishLoadingPage();' style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary">
				<div class="panel-heading"><h3 style='margin:0px;'><?php \singleton\fontawesome::getInstance( )->Requisition();?> Requisition</h3></div>
        <div class='panel-body'>
          <!--<div class='row'>
            <div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Type:</div>
            <div class='colx-s-8'><select name='Type' onChange='changeType(this);' style='color:black;'>
              <option value='Regular'>Regular</option>
              <option value='Gear'>Gear</option>
              <option value='Rigging'>Rigging</option>
              <option value='Electrical'>Electrical</option>
            </select></div>
            <div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
          </div>-->
          <style>
            .Requisition-Regular.active, .Requisition-Rigging.active, .Requisition-Electrical.active {
              display:block !important;
            }
            .Requisition-Regular, .Requisition-Rigging,  .Requisition-Electrical {
              display:none !important;
            }
          </style>

        </div>
				<div class="panel-body" style=''>
					<div class='row'><div class='col-xs-12'>&nbsp;</div></div>
					<div class="row">
						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Calendar(1);?> Date:</div>
						<div class='col-xs-8'><input disabled type='text' name='Date' size='15' value='<?php echo date("m/d/Y");?>'/></div>
					</div>
					<div class="row">
						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Required</div>
						<div class='col-xs-8'><input type='text' name='Required' size='15' value='<?php echo isset($_GET['Required']) ? $_GET['Required'] : Null;?>' /></div>
					</div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
  					<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Location:</div>
  					<div class='col-xs-8'><button type='button' onClick='selectLocations(this);' style='width:100%;height:50px;'><?php
            $pass = false;
            if(isset($_GET['Location']) && is_numeric($_GET['Location'])){
              $r = $database->query(null,"SELECT * FROM nei.dbo.Loc WHERE Loc.Loc = ?;",array($_GET['Location']));
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
                  url:"bin/php/element/requisition/selectLocations.php",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row'>
  					<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Drop Off:</div>
  					<div class='col-xs-8'><button type='button' onClick='selectDropOffs(this);' style='width:100%;height:50px;'><?php
            $pass = false;
            if(isset($_GET['DropOff']) && is_numeric($_GET['DropOff'])){
              $r = $database->query(null,"SELECT * FROM nei.dbo.Loc WHERE Loc.Loc = ?;",array($_GET['DropOff']));
              if($r){
                $row = sqlsrv_fetch_array($r);
                if(is_array($row)){
                  $pass = True;
                  echo $row['Tag'];
                }
              }
            }
            if(!$pass){?>Select Drop Off<?php }?></button></div>
            <script>
              function selectDropOffs(link){
                $.ajax({
                  url:"bin/php/element/requisition/selectDropOffs.php?Location=<?php echo $_GET['Location'];?>",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row'>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Unit:</div>
            <div class='col-xs-8'><button type='button' onClick='selectUnits(this);' style='width:100%;height:50px;'><?php
            $pass = false;
            if(isset($_GET['Unit']) && is_numeric($_GET['Unit'])){
              $r = $database->query(null,"SELECT * FROM nei.dbo.Elev WHERE Elev.ID = ?;",array($_GET['Unit']));
              if($r){
                $row = sqlsrv_fetch_array($r);
                if(is_array($row)){
                  $pass = True;
                  echo isset($row['State']) ? $row['State'] . ' - ' . $row['Unit'] : $row['Unit'];
                }
              }
            }
            if(!$pass){?>Select Unit<?php }?></button></div>
            <script>
              function selectUnits(link){
                $.ajax({
                  url:"bin/php/element/requisition/selectUnits.php?Location=<?php echo $_GET['Location'];?>&DropOff=<?php echo $_GET['DropOff'];?>",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row'>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Job:</div>
            <div class='col-xs-8'><button type='button' onClick='selectJobs(this);' style='width:100%;height:50px;'><?php
            $pass = false;
            if(isset($_GET['Job']) && is_numeric($_GET['Job'])){
              $r = $database->query(null,"SELECT * FROM nei.dbo.Job WHERE Job.ID = ?;",array($_GET['Unit']));
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
                  url:"bin/php/element/requisition/selectJobs.php?Location=<?php echo $_GET['Location'];?>&DropOff=<?php echo $_GET['DropOff'];?>&Unit=<?php echo $_GET['Unit'];?>",
                  method:"GET",
                  success:function(code){
                    $("body").append(code);
                  }
                });
              }
            </script>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
        </div>
        <div class='panel-heading Requisition-Regular active'><h3><?php \singleton\fontawesome::getInstance( )->Description(1);?> Details</h3></div>
        <div class='panel-body Requisition-Regular active'>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
					<div class='row Labels' >
						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Shutdown:</div>
						<div class='col-xs-8'><input type='checkbox' name='Shutdown'  /></div>
					</div>
					<div class='row Labels' >
						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> A.S.A.P.:</div>
						<div class='col-xs-8'><input type='checkbox' name='ASAP'  /></div>
					</div>
          <div class='row Labels' >
						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Rush:</div>
						<div class='col-xs-8'><input type='checkbox' name='Rush'  /></div>
					</div>
          <div class='row Labels' >
						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> L/S/D.:</div>
						<div class='col-xs-8'><input type='checkbox' name='LSD'  /></div>
					</div>
          <div class='row Labels' >
						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> F.R.M.:</div>
						<div class='col-xs-8'><input type='checkbox' name='FRM'  /></div>
					</div>
          <div class='row Labels' >
            <div class='col-xs-12'><?php \singleton\fontawesome::getInstance( )->Paragraph(1);?> Notes:</div>
            <div class='col-xs-12'><textarea name='Notes' style='width:100%;' rows='9'></textarea></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
        </div>
        <div class='panel-heading '><h3><?php \singleton\fontawesome::getInstance( )->Purchase();?> Items</h3></div>
        <div class='panel-body Requisition-Electrical'>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-6'>Tyraps</div>
            <div class='col-xs-3'>5.6"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Tyraps_5.6' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>8.0"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Tyraps_8.0' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>11.4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Tyraps_11.4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>14.5"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Tyraps_14.5' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Tymount"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Tyraps_Tymount' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-6'>Wire Nuts</div>
            <div class='col-xs-3'>Blue</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Nuts_Blue' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Orange</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Nuts_Orange' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Yellow</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Nuts_Yellow' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Red</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Nuts_Red' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-6'>Stakeons</div>
            <div class='col-xs-3'>#6 Fork 18 AWG</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Stakeons_#6_Fork_18_AWG' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#8 Fork 18 AWG</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Stakeons_#8_Fork_18_AWG' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#10 Fork 18 AWG</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Stakeons_#10_Fork_18_AWG' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#6 Fork 14 AWG</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Stakeons_#6_Fork_14_AWG' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#8 Fork 14 AWG</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Stakeons_#8_Fork_14_AWG' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#10 Fork 14 AWG</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Stakeons_#10_Fork_14_AWG' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div clas='row'>
            <div class='col-xs-6'>Butt Slices</div>
            <div class='col-xs-3'>Red 16-22 AWG</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Butt_Slices_Red_16-22_AWG' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Blue 16-14 AWG</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Butt_Slices_Blue16-14_AWG' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Yellow 14-10 AWG</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Butt_Slices_Yellow_14-10_AWG' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-12'>Mechanical Lugs</div>
            <div class='col-xs-12'>&nbsp;</div>
            <div class='col-xs-3'>AWG Wire</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Mechanical_Lugs_1_AWG_Wire' /></div>
            <div class='col-xs-3'>Quantity</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Mechanical_Lugs_1_Quantity' /></div>
            <div class='col-xs-3'>AWG Wire</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Mechanical_Lugs_2_AWG_Wire' /></div>
            <div class='col-xs-3'>Quantity</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Mechanical_Lugs_2_Quantity' /></div>
            <div class='col-xs-3'>AWG Wire</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Mechanical_Lugs_3_AWG_Wire' /></div>
            <div class='col-xs-3'>Quantity</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Mechanical_Lugs_3_Quantity' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-12'>Bug Nuts</div>
            <div class='col-xs-12'>&nbsp;</div>
            <div class='col-xs-3'>AWG Wire</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Bug_Nuts_1_AWG_Wire' /></div>
            <div class='col-xs-3'>Quantity</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Bug_Nuts_1_Quantity' /></div>
            <div class='col-xs-3'>AWG Wire</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Bug_Nuts_2_AWG_Wire' /></div>
            <div class='col-xs-3'>Quantity</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Bug_Nuts_2_Quantity' /></div>
            <div class='col-xs-3'>AWG Wire</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Bug_Nuts_3_AWG_Wire' /></div>
            <div class='col-xs-3'>Quantity</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Bug_Nuts_3_Quantity' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-6'>Tape</div>
            <div class='col-xs-3'>Electrical</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Tape_Electrical' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Rubber</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Tape_Rubber' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Friction</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Tape_Friction' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-6'>Wire Markers</div>
            <div class='col-xs-3'>#1-33</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Markers_#1-33' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#34-66</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Markers_#34-66' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#67-99</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Markers_67-99' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#100-124</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Markers_100-124' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#125-149</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Markers_125-149' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#150-174</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Markers_150-174' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>#175-199</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Wire_Markers_174-199' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Rough Service Bulbs</div>
            <div class='col-xs-3'>75 Watt</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Rough_Service_Bulbs_75_Watt' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>100 Watt</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Rough_Service_Bulbs_100_Watt' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Drop Light</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Rough_Service_Bulbs_Drop_Light' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Marathon Terminals</div>
            <div class='col-xs-3'>6' Track</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Marathon_Terminals_6_Track' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Clips</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Marathon_Terminals_Clips_Track' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Ends</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Marathon_Terminals_Ends_Track' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Light Fixture w/ Outlet & Metal Cage</div>
            <div class='col-xs-6'><input style='width:100%;' type='text' name='Light_Fixture_w/_Outlet_and_Metal_Cage' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Alarm_Bells' /></div>
            <div class='col-xs-3'>Alarm Bells</div>
            <div class='col-xs-3'>Volts</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Alarm_Bells_Volts_Type' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Size</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Alarm_Bells_Size' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Light Switch</div>
            <div class='col-xs-6'><input style='width:100%;' type='text' name='Light_Switch' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Single Receptacle</div>
            <div class='col-xs-6'><input style='width:100%;' type='text' name='Single_Receptacle' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Duplex Receptacle</div>
            <div class='col-xs-6'><input style='width:100%;' type='text' name='Duplex_Receptacle' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>GFI</div>
            <div class='col-xs-6'><input style='width:100%;' type='text' name='GFI' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Male Plug</div>
            <div class='col-xs-6'><input style='width:100%;' type='text' name='Male_Plug' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Female Plug</div>
            <div class='col-xs-6'><input style='width:100%;' type='text' name='Female_Plug' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Pig Tail</div>
            <div class='col-xs-6'><input style='width:100%;' type='text' name='Pig_Tail' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Laundry Tags</div>
            <div class='col-xs-6'><input style='width:100%;' type='text' name='Laundry_Tags' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Handy_Box' /></div>
            <div class='col-xs-3'>Handy Box</div>
            <div class='col-xs-3'>Blank Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Handy_Box_Blank_Cover' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Switch Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Handy_Box_Switch_Cover' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Duplex Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Handy_Box_Duplex_Cover' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>GFI</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Handy_Box_GFI' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='1900_Box' /></div>
            <div class='col-xs-3'>1900 Box</div>
            <div class='col-xs-3'>Blank Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='1900_Box_Blank_Cover' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Switch Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='1900_Box_Switch_Cover' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Duplex Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='1900_Box_Duplex_Cover' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>GFI</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='1900_Box_GFI' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Octagon_Box' /></div>
            <div class='col-xs-3'>Octagon Box</div>
            <div class='col-xs-3'>Blank Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Octagon_Box_Blank_Cover' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Switch Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Octagon_Box_Switch_Cover' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Duplex Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Octagon_Box_Duplex_Cover' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>GFI</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Octagon_Box_GFI' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Square_Box' /></div>
            <div class='col-xs-3'>5" Square Box Box</div>
            <div class='col-xs-3'>Blank Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Square_Box_Blank_Cover' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Switch Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Square_Box_Switch_Cover' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Duplex Cover</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Square_Box_Duplex_Cover' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>GFI</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Square_Box_GFI' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Screw Cover Box</div>
            <div class='col-xs-3'>6x6x4</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Screw_Cover_Box_6x6x4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>6x8x4</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Screw_Cover_Box_6x8x4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>8x8x4</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Screw_Cover_Box_8x8x4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>12x12x4</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Screw_Cover_Box_12x12x4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>12x12x4</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Screw_Cover_Box_12x12x4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>18x18x4</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Screw_Cover_Box_18x18x4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>18x24x4</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Screw_Cover_Box_18x24x4' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Kindorf Channels</div>
            <div class='col-xs-3'>B-905 - 1 1/2" x 1 1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Channels_B-905' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>B-907 - 1 1/2" x 3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Channels_B-907' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Handy Angle</div>
            <div class='col-xs-3'>RA225 - 2 3/8" x 1 5/8"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Channels_RA225' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>RA300 - 3 1/8" x 1 5/8"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Channels_RA300' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Kindorf Spring Nuts - B911</div>
            <div class='col-xs-3'>1/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Spring_Nuts_1/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>5/16"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Spring_Nuts_5/16' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>3/8"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Spring_Nuts_3/8' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Spring_Nuts_1/2' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Kindorf Connectors</div>
            <div class='col-xs-3'>B-917 5 Hole Angle</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Connectors_B-917' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>B-922 "T" Opposite Side Angle</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Connectors_B-922' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Kindorff Straps (EMT) - C-106</div>
            <div class='col-xs-3'>1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Straps_C-106_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Straps_C-106_3/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Straps_C-106_1_1/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Straps_C-106_1_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Straps_C-106_2' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Kindorff Straps (Rigid) - C-105</div>
            <div class='col-xs-3'>1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Straps_C-105_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Straps_C-105_3/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Straps_C-105_1_1/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Straps_C-105_1_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Kindorf_Straps_C-105_2' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-12'>Greenfield</div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Straight Connector</div>
            <div class='col-xs-3'>3/8"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straight_Connector_3/8' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straight_Connector_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straight_Connector_3/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straight_Connector_1' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straight_Connector_1_1/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straight_Connector_1_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straight_Connector_1_3/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straight_Connector_2' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>90 Degree Connector</div>
            <div class='col-xs-3'>3/8"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_90_Degree_Connector_3/8' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_90_Degree_Connector_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_90_Degree_Connector_3/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_90_Degree_Connector_1' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_90_Degree_Connector_1_1/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_90_Degree_Connector_1_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_90_Degree_Connector_1_3/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_90_Degree_Connector_2' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>45 Degree Connector</div>
            <div class='col-xs-3'>3/8"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_45_Degree_Connector_3/8' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_45_Degree_Connector_1/2' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Straps</div>
            <div class='col-xs-3'>3/8"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straps_3/8' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straps_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straps_3/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straps_1' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straps_1_1/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straps_1_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straps_1_3/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Straps_2' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-6'>Combo Coup.</div>
            <div class='col-xs-3'>1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Combo_Coup_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Combo_Coup_3/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Combo_Coup_1' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Combo_Coup_1_1/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 1/2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Combo_Coup_1_1/2' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>1 3/4"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Combo_Coup_1_3/4' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>2"</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Greenfield_Combo_Coup_2' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
        </div>
        <div class='panel-body Requisition-Rigging'>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Hanging_Blocks' /></div>
            <div class='col-xs-9'>Hanging Blocks</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Landing_Blocks' /></div>
            <div class='col-xs-9'>Landing Blocks</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Uprite_Sets' /></div>
            <div class='col-xs-9'>Uprite Sets</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='I_Beam' /></div>
            <div class='col-xs-3'>I Beam</div>
            <div class='col-xs-3'>Length</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='I_Beam_Length' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='I_Beam2' /></div>
            <div class='col-xs-3'>I Beam</div>
            <div class='col-xs-3'>Length</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='I_Beam_Length' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Trolley' /></div>
            <div class='col-xs-3'>Trolley</div>
            <div class='col-xs-3'>Tons</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Trolley_Tons' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Beam_Clamp' /></div>
            <div class='col-xs-3'>Beam Clamp</div>
            <div class='col-xs-3'>Tons</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Beam_Clamp_Tons' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Tuggit' /></div>
            <div class='col-xs-3'>Tuggit</div>
            <div class='col-xs-3'>Tons</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Tuggit_Tons' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Chainfall' /></div>
            <div class='col-xs-3'>Chainfall</div>
            <div class='col-xs-3'>Tons</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Chainfall_Tons' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Cable_Slings' /></div>
            <div class='col-xs-3'>Cable Slings</div>
            <div class='col-xs-3'>Size</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Cable_Slings_Size' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Length</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Cable_Slings_Length' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Nylon_Slings' /></div>
            <div class='col-xs-3'>Nylon Slings</div>
            <div class='col-xs-3'>Size</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Nylon_Slings_Size' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Length</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Nylon_Slings_Length' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='5/8_Manila_Rope' /></div>
            <div class='col-xs-9'>5/8 Manila Rope</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Section_Ladders' /></div>
            <div class='col-xs-9'>Section Ladders</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Sets_Cable_Horses_w/_Pipe' /></div>
            <div class='col-xs-9'>Sets Cable Horses w/ Pipe</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Propane_20#_Tank_w/_Torch_and_Hose' /></div>
            <div class='col-xs-9'>Propane 20# Tank w/ Torch n Hose</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Mapp_Gas_Cylinder_w/_Torch' /></div>
            <div class='col-xs-9'>Mapp Gas Cylinder w/ Torch</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Babbit' /></div>
            <div class='col-xs-9'>Babbit</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Binding_Wire' /></div>
            <div class='col-xs-9'>Binding Wire</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Cable_Spreaders' /></div>
            <div class='col-xs-9'>Cable Spreaders</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Laddle' /></div>
            <div class='col-xs-9'>Laddle</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Flat_Wagon' /></div>
            <div class='col-xs-9'>Flat Wagon</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='V_Wagon' /></div>
            <div class='col-xs-9'>V Wagon</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Barricades' /></div>
            <div class='col-xs-9'>Barricades</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='5/8_Crosby_Clamp' /></div>
            <div class='col-xs-9'>5/8 Crosby Clamp</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='1/2_Crosby_Clamp' /></div>
            <div class='col-xs-9'>1/2 Crosby Clamp</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='5/8_Ropling_Clamp' /></div>
            <div class='col-xs-9'>5/8 Ropling Clamp</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='1/2_Ropling_Clamp' /></div>
            <div class='col-xs-9'>1/2 Ropling Clamp</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Oxygen_and_Acetylene_Tanks_Cart_w/_Gauges_Tips_and_Hoses' /></div>
            <div class='col-xs-9'>Oxygen & Acetylene Tanks Cart w/ Gauges Tips & Hoses</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Cart_w/_Gauges_Tips_and_Hoses' /></div>
            <div class='col-xs-9'>Cart w/ Gauges Tips & Hoses</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Welder' /></div>
            <div class='col-xs-3'>Welder</div>
            <div class='col-xs-3'>Type</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Welder_Type' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Volts</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Welder_Volts' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Welder_Rod' /></div>
            <div class='col-xs-3'>Welder Rod </div>
            <div class='col-xs-3'>#</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Welder_Rod_#' /></div>
            <div class='col-xs-6'>&nbsp;</div>
            <div class='col-xs-3'>Size</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Welder_Rod_Size' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Welder_Blanket' /></div>
            <div class='col-xs-9'>Welder Blanket</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Welder_Jacket' /></div>
            <div class='col-xs-9'>Welder Jacket</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Welder_Gloves' /></div>
            <div class='col-xs-9'>Welder Gloves</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Welder_Mask' /></div>
            <div class='col-xs-9'>Welder Mask</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Champion_Brute_Mini_Magnetic_Drill' /></div>
            <div class='col-xs-9'>Champion Brute Mini Magnetic Drill</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='2"_Magnetic Drill_Bits_for_Brute_Mini_Mag_Size(s)' /></div>
            <div class='col-xs-9'>2" Magnetic Drill Bits for Brute Mini Mag Size(s)</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='1"_Magnetic Drill_Bits_for_Brute_Mini_Mag_Size(s)' /></div>
            <div class='col-xs-9'>1" Magnetic Drill Bits for Brute Mini Mag Size(s)</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Pilot_Bits_for_Magnetic_Drill_Bits' /></div>
            <div class='col-xs-9'>Pilot Bits for Magnetic Drill Bits</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='4x4_Wood_Post' /></div>
            <div class='col-xs-3'>4x4 Wood Post</div>
            <div class='col-xs-3'>Length</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='4x4_Wood_Post_Length' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='6x6_Wood_Post' /></div>
            <div class='col-xs-3'>6x6 Wood Post</div>
            <div class='col-xs-3'>Length</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='6x6_Wood_Post_Length' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='2x4x10_Wood_Post' /></div>
            <div class='col-xs-9'>2x4x10'</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Planking' /></div>
            <div class='col-xs-3'>Planking</div>
            <div class='col-xs-3'>Length</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Planking_Length' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='OSHA_Planking' /></div>
            <div class='col-xs-3'>OSHA Planking</div>
            <div class='col-xs-3'>Length</div>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='OSHA_Planking_Length' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Masonite' /></div>
            <div class='col-xs-9'>Masonite'</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='1/2_Plywood' /></div>
            <div class='col-xs-9'>1/2" Plywood'</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Sandpaper_Fine' /></div>
            <div class='col-xs-9'>Sandpaper (Fine)'</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Sandpaper_Medium' /></div>
            <div class='col-xs-9'>Sandpaper (Medium)'</div>
          </div>
          <div class='row'>
            <div class='col-xs-3'><input style='width:100%;' type='text' name='Sandpaper_Coarse' /></div>
            <div class='col-xs-9'>Sandpaper (Coarse)'</div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
        </div>
        <div class='panel-body Requisition-Regular active'>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='col-xs-6'><button onClick='newItem();'>New Item</button></div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
					<div class='row'>
						<div class='col-xs-12' style='padding:0px;margin:0px;'>
							<div class='row Item-Header'>
								<div class='col-xs-1'><b><i>#</i></b></div>
								<div class='col-xs-2'><b><i>Quantity</i></b></div>
								<div class='col-xs-6'><b><i>Description</i></b></div>
                <div class='col-xs-34'><b><i>Image</i></b></div>
							</div>
							<div class='row Item'>
								<div class='col-xs-1'>1</div>
								<div class='col-xs-2'><input type='text' name='Quantity' style='width:100%;' /></div>
								<div class='col-xs-6'><input type='text' name='Comments' style='width:100%;' /></div>
                <div class='col-xs-3'><form id='file1'><input type="file" name='Image' id="selectedFile1" style="display: none;" /><input type="button" value="Browse..." onclick="document.getElementById('selectedFile1').click();" /></form></div>
							</div>
							<div class='row Item'>
								<div class='col-xs-1'>2</div>
								<div class='col-xs-2'><input type='text' name='Quantity' style='width:100%;' /></div>
								<div class='col-xs-6'><input type='text' name='Comments' style='width:100%;' /></div>
                <div class='col-xs-3'><form id='file2'><input type="file" name='Image' id="selectedFile2" style="display: none;" /><input type="button" value="Browse..." onclick="document.getElementById('selectedFile2').click();" /></form></div>
							</div>
							<div class='row Item'>
								<div class='col-xs-1'>3</div>
								<div class='col-xs-2'><input type='text' name='Quantity' style='width:100%;' /></div>
								<div class='col-xs-6'><input type='text' name='Comments' style='width:100%;' /></div>
                <div class='col-xs-3'><form id='file3'><input type="file" name='Image' id="selectedFile3" style="display: none;" /><input type="button" value="Browse..." onclick="document.getElementById('selectedFile3').click();" /></form></div>
							</div>
							<div class='row Item'>
								<div class='col-xs-1'>4</div>
								<div class='col-xs-2'><input type='text' name='Quantity' style='width:100%;' /></div>
								<div class='col-xs-6'><input type='text' name='Comments' style='width:100%;' /></div>
                <div class='col-xs-3'><form id='file4'><input type="file" name='Image' id="selectedFile4" style="display: none;" /><input type="button" value="Browse..." onclick="document.getElementById('selectedFile4').click();" /></form></div>
							</div>
							<div class='row New-Item'>
								<div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
                <div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
								<div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
                <div class='col-xs-12'><button onClick='saveRequisition(this);' style='width:100%;height:50px;'>Save</button></div>
                <div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
                <div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
                <div class='col-xs-12'><div class='col-xs-12'>&nbsp;</div></div>
							</div>
						</div>
					</div>
				</div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
    

    <!-- Custom Date Filters-->
    

	<!--  if you want iE6 not to poke select boxes thru your dropdowns, you need ... -->
	<script type="text/javascript" src="js/jquery.bgiframe.min.js"></script>

	<!-- Plugin source development location, distribution location: only 1 of 2 is there..	 -->
	<script type="text/javascript" src="js/jquery.ui.ufd.js"></script>
    <script>
    function changeType(link){
      $(".active").removeClass('active');
      $(".Requisition-" + $(link).val()).addClass('active');
    }
    </script>
    <script>
	var Item_Count = 5;
	function newItem(){
		$(".New-Item").before("<div class='row Item'><div class='col-xs-1'>" + Item_Count.toString() + "</div><div class='col-xs-2'><input type='text' name='Quantity' style='width:100%;' /></div><div class='col-xs-6'><input type='text' name='Comments' style='width:100%;' /></div> <div class='col-xs-3'><form id='file" + Item_Count.toString() + "'><input type='file' id='selectedFile" + Item_Count.toString() + "' style='display: none;' name='Image' /><input type='button' value='Browse...' onclick='document.getElementById('selectedFile" + Item_Count.toString() + "').click();' /></form></div></div>");
		Item_Count = Item_Count + 1;
	}
	$(document).ready(function(){
		$("input[name='Date']").datepicker();
		$("input[name='Required']").datepicker();
		$("input[name='Date']").datepicker("setDate",new Date());
	});
	/*$(document).ready(function(){
		$("select[name='Location']").ufd({log:true});
	});*/
  function closePopup(link){$(".popup").remove();}
  function saveRequisition(link){
    var requisitionData = new FormData();
    requisitionData.append("Required",$("input[name='Required']").val());
    requisitionData.append('Location','<?php echo isset($_GET['Location']) ? $_GET['Location'] : '';?>');
    requisitionData.append('DropOff','<?php echo isset($_GET['DropOff']) ? $_GET['DropOff'] : '';?>');
    requisitionData.append('Unit','<?php echo isset($_GET['Unit']) ? $_GET['Unit'] : '';?>');
    requisitionData.append('Job','<?php echo isset($_GET['Job']) ? $_GET['Job'] : '';?>');
    requisitionData.append("Shutdown",$("input[name='Shutdown']").prop('checked'));
    requisitionData.append("ASAP",$("input[name='ASAP']").prop('checked'));
    requisitionData.append("Rush",$("input[name='Rush']").prop('checked'));
    requisitionData.append("LSD",$("input[name='LSD']").prop('checked'));
    requisitionData.append("FRM",$("input[name='FRM']").prop('checked'));
    requisitionData.append("Notes",$("textarea[name='Notes']").val());
    var itemArray = [];
    var count = 0;
    var index = 1;
    $(".row.Item").each(function(){
      if($(this).find("input[name='Quantity']").val() == ''){}
      else {
        requisitionData.append("Item[" + count + "][Quantity]",$(this).find("input[name='Quantity']").val());
        requisitionData.append("Item[" + count + "][Comments]",$(this).find("input[name='Comments']").val());
        var image = document.getElementById('selectedFile' + index);
        if(image.files[0] === null || image.files[0] === undefined){}
        else {requisitionData.append("Item[" + count + "]",image.files[0]);}
      }
      count++;
      index++;
    });
    $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
    $(link).attr('disabled','disabled');
    $.ajax({
      url:"bin/php/post/save_requisition.php",
      cache: false,
      processData: false,
      contentType: false,
      data: requisitionData,
      timeout:15000,
      error:function(XMLHttpRequest, textStatus, errorThrown){
        alert('Your ticket did not save. Please check your internet.')
        $(link).html("Save");
        $(link).prop('disabled',false);
      },
      method:"POST",
      success:function(code){
        document.location.href='requisition.php?ID=' + code;
      }
    });
  }
	</script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=units.php';</script></head></html><?php }?>
