<?php
session_start();
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Texas'){
        sqlsrv_query($NEI,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($NEI,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
			$r = sqlsrv_query($NEI,"
				SELECT Elev.Loc AS Location_ID
				FROM   Elev
				WHERE  Elev.ID = ?
			;",array($_GET['ID'] ));
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
            $r = sqlsrv_query($NEI,"
			SELECT Tickets.*
			FROM
			(
				(
					SELECT TicketO.ID
					FROM   TicketO
						   LEFT JOIN Loc  ON TicketO.LID   = Loc.Loc
						   LEFT JOIN Elev ON Loc.Loc       = Elev.Loc
						   LEFT JOIN Emp  ON TicketO.fWork = Emp.fWork
					WHERE  Emp.ID      = ?
						   AND Loc.Loc = ?
				)
				UNION ALL
				(
					SELECT TicketD.ID
					FROM   TicketD
						   LEFT JOIN Loc  ON TicketD.Loc   = Loc.Loc
						   LEFT JOIN Elev ON Loc.Loc       = Elev.Loc
						   LEFT JOIN Emp  ON TicketD.fWork = Emp.fWork
					WHERE  Emp.ID      = ?
						   AND Loc.Loc = ?
				)

			) AS Tickets
           	;", array($_SESSION['User'], $Location_ID, $_SESSION['User'], $Location_ID, $_SESSION['User'], $Location_ID));
            $r = sqlsrv_fetch_array($r);
            $Privileged = is_array($r) ? TRUE : FALSE;
        }
    }
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        if(count($_POST) > 0){
            fixArrayKey($_POST);
            foreach($_POST as $key=>$value){
				if($key == 'Price'){continue;}
				if($key == 'Type'){continue;}
                sqlsrv_query($NEI,"
                    UPDATE ElevTItem
                    SET    ElevTItem.Value     = ?
                    WHERE  ElevTItem.Elev      = ?
                           AND ElevTItem.ElevT = 1
                           AND ElevTItem.fDesc = ?
                ;",array($value,$_GET['ID'],$key));
            }
			if(isset($_POST['Price'])){
				sqlsrv_query($NEI,"
					UPDATE Elev
					SET    Elev.Price = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Price'],$_GET['ID']));
			}
			if(isset($_POST['Type'])){
				sqlsrv_query($NEI,"
					UPDATE Elev
					SET    Elev.Type = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Type'],$_GET['ID']));
			}
        }
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                Elev.ID,
                Elev.Unit           AS Unit,
                Elev.State          AS State,
                Elev.Cat            AS Category,
                Elev.Type           AS Type,
                Elev.Building       AS Building,
                Elev.Since          AS Since,
                Elev.Last           AS Last,
                Elev.Price          AS Price,
                Elev.fDesc          AS Description,
                Loc.Loc             AS Location_ID,
                Loc.ID              AS Name,
                Loc.Tag             AS Tag,
                Loc.Tag             AS Location_Tag,
                Loc.Address         AS Street,
                Loc.City            AS City,
                Loc.State           AS Location_State,
                Loc.Zip             AS Zip,
                Loc.Route           AS Route,
                Zone.Name           AS Zone,
                OwnerWithRol.Name   AS Customer_Name,
                OwnerWithRol.ID     AS Customer_ID,
				OwnerWithRol.Contact AS Customer_Contact,
				OwnerWithRol.Address AS Customer_Street,
				OwnerWithRol.City 	AS Customer_City,
				OwnerWithRol.State 	AS Customer_State,
                Emp.ID AS Route_Mechanic_ID,
                Emp.fFirst AS Route_Mechanic_First_Name,
                Emp.Last AS Route_Mechanic_Last_Name
            FROM
                Elev
                LEFT JOIN Loc           ON Elev.Loc = Loc.Loc
                LEFT JOIN Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN Route ON Loc.Route = Route.ID
                LEFT JOIN Emp ON Route.Mech = Emp.fWork
            WHERE
                Elev.ID = ?
		;",array($_GET['ID']));
        $Unit = sqlsrv_fetch_array($r);
        $unit = $Unit;
        $data = $Unit;
        $r2 = sqlsrv_query($NEI,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
        $sQuery = "SELECT Item.* FROM Device.dbo.Item WHERE Item.Device = ? AND Item.Type = ?;";
        $params = array($_GET['ID'], 'Machine');
        $r = sqlsrv_query($database_Device, $sQuery, $params);
        if($r){
          $Item = sqlsrv_fetch_array($r);
          $sQuery = "SELECT Product.* FROM Device.dbo.Product WHERE Product.ID = ?";
          $params = array($Item['Product']);
          $r = sqlsrv_query($database_Device, $sQuery, $params);
          if($r){
            $Product = sqlsrv_fetch_array($r);
            $sQuery = "SELECT Machine.* FROM Device.dbo.Machine WHERE Machine.Item = ?";
            $params = array($Item['ID']);
            $r = sqlsrv_query($database_Device, $sQuery, $params);
            if($r){
              $Machine = sqlsrv_fetch_array($r);
            }
          }
        }
?><!DOCTYPE html>
<style>#form_Machine div.col-xs-4 {text-align:right !important;}</style>
<div class="panel panel-primary">
  <div class='panel-heading' onclick="someFunction(this,'unit-machine.php?ID=<?php echo $_GET['ID'];?>');"><img src='media/images/icons/machine.png' width='auto' height='35px' /> Machine</div>
	<div class='panel-body' style='padding-top:10px;'><form id='form_Machine'>
    <?php if(isset($Item)){?><input type='hidden' value='<?php echo $Item['ID'];?>' name='Item' /><?php }?>
    <div class='row'>
      <div class='col-xs-4'>Location:</div>
      <div class='col-xs-8' onclick="someFunction(this,'unit-machine_room.php?ID=<?php echo $_GET['ID'];?>');"><img src='media/images/icons/machine_room.png' width='auto' height='25px' /> Machine Room</div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Image:</div>
      <div class='col-xs-8'><input type='file' name='Image' style='color:white !important;' /></div>
    </div>
    <?php
    $r = sqlsrv_query($database_Device, "SELECT * FROM Device.dbo.Item_Image WHERE Item_Image.Item = ?",array($Item['ID']));
    if($r){while($row = sqlsrv_fetch_array($r)){?>
      <div class='row'>
        <div class='col-xs-4'>&nbsp;</div>
        <div class='col-xs-8'><?php ?><img width='100%' src="<?php print "data:" . $row['Image_Type'] . ";base64, " . $row['Image'];?>" /></div>
      </div>
    <?php }}?>
    <div class='row'>
      <div class='col-xs-4'>Condition:</div>
      <div class='col-xs-8'><select name='Condition'>
        <option value=''>Select</option>
        <option <?php echo $Item['Condition'] == 'New' ? 'selected' : '';?> value='New'>New</option>
        <option <?php echo $Item['Condition'] == 'Good' ? 'selected' : '';?> value='Good'>Good</option>
        <option <?php echo $Item['Condition'] == 'Average' ? 'selected' : '';?> value='Average'>Average</option>
        <option <?php echo $Item['Condition'] == 'Bad' ? 'selected' : '';?> value='Bad'>Poor</option>
        <option <?php echo $Item['Condition'] == 'Broken' ? 'selected' : '';?> value='Broken'>Broken</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Product:</div>
      <div class='col-xs-8'><input type='text' placeholder='Product' value='<?php echo isset($Product['Name']) ? $Product['Name'] : '';?>' name='Product' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Manufacturer:</div>
      <div class='col-xs-8'><input type='text' placeholder='Manufacturer' value='<?php echo isset($Product['Manufacturer']) ? $Product['Manufacturer'] : '';?>' name='Manufacturer' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Serial:</div>
      <div class='col-xs-8'><input type='text' placeholder='Serial' value='<?php echo isset($Item['Serial']) ? $Item['Serial'] : '';?>' name='Serial' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Vendor P.O.:</div>
      <div class='col-xs-8'><input type='text' placeholder='012345' name='Vendor_Purchase_Order' value='<?php echo isset($Item['Vendor_Purchase_Order']) ? $Item['Vendor_Purchase_Order'] : '';?>' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Blueprint (URL):</div>
      <div class='col-xs-8'><input type='text' placeholder='https://drive.google.com/' name='Blueprint' value='<?php echo isset($Item['Blueprint']) ? $Item['Blueprint'] : '';?>' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Notes:</div>
      <div class='col-xs-8'><textarea name='Notes' style='width:100%;' rows='5'><?php echo isset($Item['Notes']) ? $Item['Notes'] : '';?></textarea></div>
    </div>

    <div class='row'>
      <div class='col-xs-4'>Length:</div>
      <div class='col-xs-8'><input placeholder='Length' pattern='[0123456789]*' type='text' value='<?php echo isset($Item['Length']) ? $Item['Length'] : '';?>' name='Length' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Width:</div>
      <div class='col-xs-8'><input placeholder='Width' pattern='[0123456789]*' type='text' value='<?php echo isset($Item['Width']) ? $Item['Width'] : '';?>' name='Width' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Height:</div>
      <div class='col-xs-8'><input placeholder='Height' pattern='[0123456789]*'  type='text' value='<?php echo isset($Item['Height']) ? $Item['Height'] : '';?>' name='Height' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Mounting Pad</div>
      <div class='col-xs-8'><select name='Mounting_Pad' style='color:black;'>
        <option value=''>Select</option>
        <option <?php echo $Machine['Mounting_Pad'] == 0 ? 'selected' : '';?> value='0'>No</option>
        <option <?php echo $Machine['Mounting_Pad'] == 1 ? 'selected' : '';?> value='1'>Yes</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Cable Size:</div>
      <div class='col-xs-8'><select name='Cable_Size'>
        <option value=''>Select</option>
        <option <?php echo isset($Machine['Cable_Size']) && $Machine['Cable_Size'] == '5/16' ? 'selected' : '';?> value='5/16'>5/16</option>
        <option <?php echo isset($Machine['Cable_Size']) && $Machine['Cable_Size'] == '3/8' ? 'selected' : '';?> value='3/8'>3/8</option>
        <option <?php echo isset($Machine['Cable_Size']) && $Machine['Cable_Size'] == '1/2' ? 'selected' : '';?> value='1/2'>1/2</option>
        <option <?php echo isset($Machine['Cable_Size']) && $Machine['Cable_Size'] == 'Other' ? 'selected' : '';?> value='Bad'>Other</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Cable Quantity:</div>
      <div class='col-xs-8'><select name='Cable_Quantity'>
        <option value=''>Select</option>
        <option <?php echo isset($Machine['Cable_Quantity']) && $Machine['Cable_Quantity'] == '1' ? 'selected' : '';?> value='1'>1</option>
        <option <?php echo isset($Machine['Cable_Quantity']) && $Machine['Cable_Quantity'] == '2' ? 'selected' : '';?> value='2'>2</option>
        <option <?php echo isset($Machine['Cable_Quantity']) && $Machine['Cable_Quantity'] == '3' ? 'selected' : '';?> value='3'>3</option>
        <option <?php echo isset($Machine['Cable_Quantity']) && $Machine['Cable_Quantity'] == '4' ? 'selected' : '';?> value='4'>4</option>
        <option <?php echo isset($Machine['Cable_Quantity']) && $Machine['Cable_Quantity'] == '5' ? 'selected' : '';?> value='5'>5</option>
        <option <?php echo isset($Machine['Cable_Quantity']) && $Machine['Cable_Quantity'] == '6' ? 'selected' : '';?> value='6'>6</option>
        <option <?php echo isset($Machine['Cable_Quantity']) && $Machine['Cable_Quantity'] == '7' ? 'selected' : '';?> value='7'>7</option>
        <option <?php echo isset($Machine['Cable_Quantity']) && $Machine['Cable_Quantity'] == '8' ? 'selected' : '';?> value='8'>8</option>
        <option <?php echo isset($Machine['Cable_Quantity']) && $Machine['Cable_Quantity'] == '9' ? 'selected' : '';?> value='9'>9</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Cable Length:</div>
      <div class='col-xs-8'><input type='text' name='Cable_Length' value='<?php echo isset($Machine['Cable_Length']) ? $Machine['Cable_Length'] : '';?>' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Cable Condition:</div>
      <div class='col-xs-8'><select name='Cable_Condition'>
        <option value=''>Select</option>
        <option <?php echo isset($Machine['Cable_Condition']) && $Machine['Cable_Condition'] == 'New' ? 'selected' : '';?> value='New'>New</option>
        <option <?php echo isset($Machine['Cable_Condition']) && $Machine['Cable_Condition'] == 'Good' ? 'selected' : '';?> value='Good'>Good</option>
        <option <?php echo isset($Machine['Cable_Condition']) && $Machine['Cable_Condition'] == 'Average' ? 'selected' : '';?> value='Average'>Average</option>
        <option <?php echo isset($Machine['Cable_Condition']) && $Machine['Cable_Condition'] == 'Bad' ? 'selected' : '';?> value='Bad'>Poor</option>
        <option <?php echo isset($Machine['Cable_Condition']) && $Machine['Cable_Condition'] == 'Broken' ? 'selected' : '';?> value='Broken'>Broken</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Roping:</div>
      <div class='col-xs-8'><select name='Roping'>
        <option value=''>Select</option>
        <option <?php echo $Machine['Roping'] == '1:1' ? 'selected' : '';?> value='1:1'>1:1</option>
        <option <?php echo $Machine['Roping'] == '2:1' ? 'selected' : '';?> value='2:1'>2:1</option>
        <option <?php echo $Machine['Roping'] == '3:1' ? 'selected' : '';?> value='3:1'>3:1</option>
        <option <?php echo $Machine['Roping'] == '4:1' ? 'selected' : '';?> value='4:1'>4:1</option>
        <option <?php echo $Machine['Roping'] == '5:1' ? 'selected' : '';?> value='5:1'>5:1</option>
        <option <?php echo $Machine['Roping'] == '6:1' ? 'selected' : '';?> value='6:1'>6:1</option>
        <option <?php echo $Machine['Roping'] == 'Other' ? 'selected' : '';?> value='Other'>Other</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Drum Size:</div>
      <div class='col-xs-8'><input placeholder='Drum Size' type='text' value='<?php echo isset($Machine['Drum_Size']) ? $Machine['Drum_Size'] : '';?>' name='Drum_Size' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Geared:</div>
      <div class='col-xs-8'><select name='Geared' style='color:black;'>
        <option value=''>Select</option>
        <option <?php echo $Machine['Geared'] == 0 ? 'selected' : '';?> value='0'>Gearless</option>
        <option <?php echo $Machine['Geared'] == 1 ? 'selected' : '';?> value='1'>Geared</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>MRL:</div>
      <div class='col-xs-8'><select name='MRL'>
        <option value=''>Select</option>
        <option <?php echo $Machine['MRL'] == 0 ? 'selected' : '';?> value='0'>No</option>
        <option <?php echo $Machine['MRL'] == 1 ? 'selected' : '';?> value='1'>Yes</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Power Type:</div>
      <div class='col-xs-8'><select name='Power_Type' style='color:black;'>
        <option value=''>Select</option>
        <option <?php echo $Machine['Power_Type'] == 'Alternating Current (A.C.)' ? 'selected' : '';?> value='Alternating Current (A.C.)'>Alternating Current (A.C.)</option>
        <option <?php echo $Machine['Power_Type'] == 'Direct Current (D.C.)' ? 'selected' : '';?> value='Direct Current (D.C.)'>Direct Current (D.C.)</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Input Voltage:</div>
      <div class='col-xs-8'><input placeholder='Input Voltage' pattern='[0123456789]*' type='text' value='<?php echo isset($Machine['Input_Voltage']) ? $Machine['Input_Voltage'] : '';?>' name='Input_Voltage' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Input Amperage:</div>
      <div class='col-xs-8'><input placeholder='Input Amperage' pattern='[0123456789]*' type='text' value='<?php echo isset($Machine['Input_Amperage']) ? $Machine['Input_Amperage'] : '';?>' name='Input_Amperage' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>RPM:</div>
      <div class='col-xs-8'><input placeholder='RPM' type='text' pattern='[0123456789]*' value='<?php echo isset($Machine['RPM']) ? $Machine['RPM'] : '';?>' name='RPM' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Horsepower:</div>
      <div class='col-xs-8'><input placeholder='Horsepower' type='text' pattern='[0123456789]*' value='<?php echo isset($Machine['Horsepower']) ? $Machine['Horsepower'] : '';?>' name='Horsepower' /></div>
    </div>
    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
    <div class='row'><div class='col-xs-12'><button onClick='save_Machine(this);' type='button' style='width:100%;height:42px;'>Save</button></div></div>
    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
  </form></div>
  <script>
  function save_Machine(link){
    $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
    $(link).attr('disabled','disabled');
    var formElement = document.getElementById('form_Machine');
    var formData = new FormData(formElement);
    formData.append('ID', '<?php echo $_GET['ID'];?>');
    if($("#form_Machine input:invalid").length == 0){
      $.ajax({
        url:"cgi-bin/php/post/unit/machine.php",
        data:formData,
        processData: false,
        contentType: false,
        timeout:15000,
        error:function(XMLHttpRequest, textStatus, errorThrown){
          alert('Your ticket did not save. Please check your internet.')
          $(link).html("Save");
          $(link).prop('disabled',false);
        },
        method:"POST",
        success:function(code){
          var dat = new Date();
          $(link).html("Saved " + dat.toLocaleString());
          $(link).prop('disabled',false);
        }
      });
    } else {
      $(link).html('Please validate form');
      setTimeout(function(){
          $(link).html('Save');
          $(link).prop('disabled',false);
      },500);

    }
  }
  </script>
  <!--<div class='panel-heading'>Configuration</div>
  <div class='panel-body'>
    <div class='row'>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-machine-armature.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/armature.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Armature</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-machine-worm.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/worm.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Worm</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-machine-motor.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/motor.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Motor</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-machine-encoder.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/encoder.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Encoder</div>
      </div>
    </div>
    <div class='row'>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-machine-brake.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/brake.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Brakes</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-machine-cables.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/cable.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Cables</div>
      </div>
    </div>
  </div>
  <div class='panel-heading'>Related Items</div>
  <div class='panel-body'>
    <div class='row' style='height:75px;'>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-car_top_box.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/car_top_inspection_station.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Car Top Box</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-hall_station.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/hall_station.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Hall Button Riser</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-main_line.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/main_line.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Main Line</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-drive.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/drive.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Drive</div>
      </div>
    </div>
    <div clas='row'>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-computer.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/computer.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Computer</div>
      </div>
    </div>
	</div>-->
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
