<?php
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Texas'){
        $database->query(null,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
        $r= $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = $database->query(null,"
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['Owner'] >= 4 && $My_Privileges['Unit']['Group'] >= 4 && $My_Privileges['Unit']['Other'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['Owner'] >= 4 && $My_Privileges['Unit']['Group'] >= 4){
			$r = $database->query(null,"
				SELECT Elev.Loc AS Location_ID
				FROM   Elev
				WHERE  Elev.ID = ?
			;",array($_GET['ID'] ));
			$Location_ID = sqlsrv_fetch_array($r)['Location_ID'];
            $r = $database->query(null,"
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
                $database->query(null,"
                    UPDATE ElevTItem
                    SET    ElevTItem.Value     = ?
                    WHERE  ElevTItem.Elev      = ?
                           AND ElevTItem.ElevT = 1
                           AND ElevTItem.fDesc = ?
                ;",array($value,$_GET['ID'],$key));
            }
			if(isset($_POST['Price'])){
				$database->query(null,"
					UPDATE Elev
					SET    Elev.Price = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Price'],$_GET['ID']));
			}
			if(isset($_POST['Type'])){
				$database->query(null,"
					UPDATE Elev
					SET    Elev.Type = ?
					WHERE  Elev.ID    = ?
				;",array($_POST['Type'],$_GET['ID']));
			}
        }
        $r = $database->query(null,
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
        $r2 = $database->query(null,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
  $sQuery = "SELECT Item.* FROM Device.dbo.Item WHERE Item.Device = ? AND Item.Type = ?;";
  $params = array($_GET['ID'], 'Cab');
  $r = $database->query($database_Device, $sQuery, $params);
  if($r){
    $Item = sqlsrv_fetch_array($r);
    $sQuery = "SELECT Product.* FROM Device.dbo.Product WHERE Product.ID = ?";
    $params = array($Item['Product']);
    $r = $database->query($database_Device, $sQuery, $params);
    if($r){
      $Product = sqlsrv_fetch_array($r);
      $sQuery = "SELECT Cab.* FROM Device.dbo.Cab WHERE Cab.Item = ?";
      $params = array($Item['ID']);
      $r = $database->query($database_Device, $sQuery, $params);
      if($r){
        $Cab = sqlsrv_fetch_array($r);
      }
    }
  }
?><!DOCTYPE html>
<div class="panel panel-primary">
  <div class='panel-heading' onClick="someFunction(this,'unit-cab.php?ID=<?php echo $_GET['ID'];?>');"><img src='media/images/icons/cab.png' width='auto' height='25px' /> Cab Properties</div>
	<div class='panel-body' style='margin-top:10px;'><form id='form_Cab'>
    <?php if(isset($Item)){?><input type='hidden' value='<?php echo $Item['ID'];?>' name='Item' /><?php }?>
    <div class='row'>
      <div class='col-xs-4'>Image:</div>
      <div class='col-xs-8'><input type='file' name='Image' style='color:white !important;' /></div>
    </div>
    <?php
    $r = $database->query($database_Device, "SELECT * FROM Device.dbo.Item_Image WHERE Item_Image.Item = ?",array($Item['ID']));
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
        <option <?php echo isset($Item['Condition']) && $Item['Condition'] == 'New' ? 'selected' : '';?> value='New'>New</option>
        <option <?php echo isset($Item['Condition']) && $Item['Condition'] == 'Good' ? 'selected' : '';?> value='Good'>Good</option>
        <option <?php echo isset($Item['Condition']) && $Item['Condition'] == 'Average' ? 'selected' : '';?> value='Average'>Average</option>
        <option <?php echo isset($Item['Condition']) && $Item['Condition'] == 'Bad' ? 'selected' : '';?> value='Bad'>Poor</option>
        <option <?php echo isset($Item['Condition']) && $Item['Condition'] == 'Broken' ? 'selected' : '';?> value='Broken'>Broken</option>
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
      <div class='col-xs-8'><input type='text' placeholder='Length' name='Length' value='<?php echo isset($Item['Length']) ? $Item['Length'] : '';?>' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Width:</div>
      <div class='col-xs-8'><input type='text' placeholder='Width' name='Width' value='<?php echo isset($Item['Width']) ? $Item['Width'] : '';?>' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Height:</div>
      <div class='col-xs-8'><input type='text' placeholder='Height' name='Height' value='<?php echo isset($Item['Height']) ? $Item['Height'] : '';?>' /></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Composition:</div>
      <div class='col-xs-8'><select name='Composition'>
        <option value=''>Select</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Smoke Detector:</div>
      <div class='col-xs-8'><select name='Smoke_Detector'>
        <option value=''>Select</option>
        <option <?php echo isset($Cab['Smoke_Detector']) && $Cab['Smoke_Detector'] == 0 ? 'selected' : '';?> value='0'>No</option>
        <option <?php echo isset($Cab['Smoke_Detector']) && $Cab['Smoke_Detector'] == 1 ? 'selected' : '';?> value='1'>Yes</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Hatch:</div>
      <div class='col-xs-8'><select name='Hatch'>
        <option value=''>Select</option>
        <option <?php echo isset($Cab['Hatch']) && $Cab['Hatch'] == 0 ? 'selected' : '';?> value='0'>No</option>
        <option <?php echo isset($Cab['Hatch']) && $Cab['Hatch'] == 1 ? 'selected' : '';?> value='1'>Yes</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Emergency Lighting:</div>
      <div class='col-xs-8'><select name='Emergency_Lighting'>
        <option value=''>Select</option>
        <option <?php echo isset($Cab['Emergency_Lighting']) && $Cab['Emergency_Lighting'] == 0 ? 'selected' : '';?> value='0'>No</option>
        <option <?php echo isset($Cab['Emergency_Lighting']) && $Cab['Emergency_Lighting'] == 1 ? 'selected' : '';?> value='1'>Yes</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Safety Mirror:</div>
      <div class='col-xs-8'><select name='Safety_Mirror'>
        <option value=''>Select</option>
        <option <?php echo isset($Cab['Safety_Mirror']) && $Cab['Safety_Mirror'] == 0 ? 'selected' : '';?> value='0'>No</option>
        <option <?php echo isset($Cab['Safety_Mirror']) && $Cab['Safety_Mirror'] == 1 ? 'selected' : '';?> value='1'>Yes</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Security Camera:</div>
      <div class='col-xs-8'><select name='Security_Camera'>
        <option value=''>Select</option>
        <option <?php echo isset($Cab['Security_Camera']) && $Cab['Security_Camera'] == 0 ? 'selected' : '';?> value='0'>No</option>
        <option <?php echo isset($Cab['Security_Camera']) && $Cab['Security_Camera'] == 1 ? 'selected' : '';?> value='1'>Yes</option>
      </select></div>
    </div>
    <div class='row'>
      <div class='col-xs-4'>Fan:</div>
      <div class='col-xs-8'><select name='Fan'>
        <option value=''>Select</option>
        <option <?php echo isset($Cab['Fan']) && $Cab['Fan'] == 0 ? 'selected' : '';?> value='0'>No</option>
        <option <?php echo isset($Cab['Fan']) && $Cab['Fan'] == 1 ? 'selected' : '';?> value='1'>Yes</option>
      </select></div>
    </div>
    <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
    <div class='row'><div class='col-xs-12'><button onClick='save_Cab(this);' type='button' style='width:100%;'>Save</button></div></div>
  </form></div>
  <script>
    function save_Cab(link){
      $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
      $(link).attr('disabled','disabled');
      var formElement = document.getElementById('form_Cab');
      var formData = new FormData(formElement);
      formData.append('ID', '<?php echo $_GET['ID'];?>');
      if($("#form_Cab input:invalid").length == 0){
        $.ajax({
          url:"bin/php/post/unit/cab.php",
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
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-stop_switch.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/emergency_stop_switch.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Stop Switch</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-alarm.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/alarm.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Alarm System</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-door_package.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/door_package.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Door Package</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-mirror.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/mirror.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Mirror(s)</div>
      </div>
    </div>
    <div class='row'>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-lighting.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/lighting.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Lighting</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-floor.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/cab_floor.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Floor</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-car_station.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/car_station.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Car Station</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-car-certificate_frame.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/certificate_frame.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Certificate</div>
      </div>
    </div>
  </div>
  <div class='panel-body' style='display:none;'>
    <div class='row'>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-handrails.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/handrail.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Handrail(s)</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-car_station.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/car_station.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Car Station</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-fan.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/fan.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Fan</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-security_camera.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/security_camera.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Security Camera</div>
      </div>
    </div>
    <div class='row'>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-saddle?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/saddle.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Saddle</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-hatch.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/hatch.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Hatch</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-lights.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/lightbulb.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Lights</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-position_indicator.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/position_indicator.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>Position Indicator</div>
      </div>
    </div>
    <div class='row'>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-cab-air_conditioning.php?ID=<?php echo $_GET['ID'];?>');">
        <div class='nav-icon'><img src='media/images/icons/air_conditioning.png' width='auto' height='35px' /></div>
        <div class ='nav-text'>A.C. Unit</div>
      </div>
    </div>
  </div>-->
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
