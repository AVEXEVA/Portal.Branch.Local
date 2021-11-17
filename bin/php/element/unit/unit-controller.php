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
        $params = array($_GET['ID'], 'Controller');
        $r = $database->query($database_Device, $sQuery, $params);
        if($r){
          $Item = sqlsrv_fetch_array($r);
          $sQuery = "SELECT Product.* FROM Device.dbo.Product WHERE Product.ID = ?";
          $params = array($Item['Product']);
          $r = $database->query($database_Device, $sQuery, $params);
          if($r){
            $Product = sqlsrv_fetch_array($r);
            $sQuery = "SELECT Controller.* FROM Device.dbo.Controller WHERE Controller.Item = ?";
            $params = array($Item['ID']);
            $r = $database->query($database_Device, $sQuery, $params);
            if($r){
              $Controller = sqlsrv_fetch_array($r);
            }
          }
        }
?><!DOCTYPE html>
			<div class="panel panel-primary">
        <div class='panel-heading' onclick="someFunction(this,'unit-controller.php?ID=<?php echo $_GET['ID'];?>');"><img src='media/images/icons/controller.png' width='auto' height='25px' /> Controller Properties</div>
				<div class='panel-body' style='padding-top:10px;'><form id='form_Controller'>
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
            <div class='col-xs-4'>Installed:</div>
            <div class='col-xs-8'><input placeholder='Installed' type='text' value='<?php echo isset($Controller['Installed']) ? $Controller['Installed'] : '';?>' name='Installed' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>MRL:</div>
            <div class='col-xs-8'><select name='MRL'>
              <option value=''>Select</option>
              <option value='0' <?php echo $Controller['MRL'] == 0 ? 'selected' : '';?>>No</option>
              <option value='1' <?php echo $Controller['MRL'] == 1 ? 'selected' : '';?>>Yes</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Motor Overload:</div>
            <div class='col-xs-8'><select name='Motor_Overload'>
              <option value=''>Select</option>
              <option value='0' <?php echo $Controller['Motor_Overload'] == 0 ? 'selected' : '';?>>No</option>
              <option value='1' <?php echo $Controller['Motor_Overload'] == 1 ? 'selected' : '';?>>Yes</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Type:</div>
            <div class='col-xs-8'><select name='Type'>
              <option value=''>Select</option>
              <option <?php echo $Controller['Type'] == 'Solid State' ? 'selected' : '';?> value='Solid State'>Solid State</option>
              <option <?php echo $Controller['Type'] == 'Relay Logic' ? 'selected' : '';?> value='Relay Logic'>Relay Logic</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Software:</div>
            <div class='col-xs-8'><input placeholder='Software' type='text' value='<?php echo isset($Controller['Software']) ? $Controller['Software'] : '';?>' name='Software' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>User Name:</div>
            <div class='col-xs-8'><input placeholder='User Name' type='text' value='<?php echo isset($Controller['User_Name']) ? $Controller['User_Name'] : '';?>' name='User_Name' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Password:</div>
            <div class='col-xs-8'><input placeholder='Password' type='text' value='<?php echo isset($Controller['Password']) ? $Controller['Password'] : '';?>' name='Password' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Grouping:</div>
            <div class='col-xs-8'><input placeholder='Grouping' type='text' value='<?php echo isset($Controller['Grouping']) ? $Controller['Grouping'] : '';?>' name='Grouping' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Length:</div>
            <div class='col-xs-8'><input pattern='[0123456789]*' placeholder='Length' type='text' value='<?php echo isset($Item['Length']) ? $Item['Length'] : '';?>' name='Length' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Width:</div>
            <div class='col-xs-8'><input pattern='[0123456789]*' placeholder='Width' type='text' value='<?php echo isset($Item['Width']) ? $Item['Width'] : '';?>' name='Width' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Height:</div>
            <div class='col-xs-8'><input pattern='[0123456789]*' placeholder='Height' type='text' value='<?php echo isset($Item['Height']) ? $Item['Height'] : '';?>' name='Height' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Mounting Pad</div>
            <div class='col-xs-8'><select name='Mounting_Pad'>
              <option value=''>Select</option>
              <option value='0' <?php echo $Controller['Mounting_Pad'] == 0 ? 'selected' : '';?>>No</option>
              <option value='1' <?php echo $Controller['Mounting_Pad'] == 1 ? 'selected' : '';?>>Yes</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Landings:</div>
            <div class='col-xs-8'><input pattern='[0123456789]*' placeholder='Landings' type='text' value='<?php echo isset($Controller['Landings']) ? $Controller['Landings'] : '';?>' name='Landings' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Maximum Car Speed:</div>
            <div class='col-xs-8'><input pattern='[0123456789]*' placeholder='Max Car Speed' type='text' value='<?php echo isset($Controller['Maximum_Car_Speed']) ? $Controller['Maximum_Car_Speed'] : '';?>' name='Maximum_Car_Speed' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'><div class='col-xs-12'><button onClick='save_Controller(this);' type='button' style='width:100%;height:42px;'>Save</button></div></div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
        </form></div>

        <script>
          function save_Controller(link){
            $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
            $(link).attr('disabled','disabled');
            var formElement = document.getElementById('form_Controller');
            var formData = new FormData(formElement);
            formData.append('ID', '<?php echo $_GET['ID'];?>');
            if($("#form_Controller input:invalid").length == 0){
              $.ajax({
                url:"bin/php/post/unit/controller.php",
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
        <?php if($_SESSION['User'] == 895 && FALSE){?><div class='panel-heading'>Buttons</div>
        <div class='panel-body'>
          <div class='row'>
            <div class='col-xs-6'><button style='width:100%;height:42px;' onClick="inspect_Controller(this);"><img src='https://www.nouveauelevator.com/image/black-icon/inspect.png' width='25px' height='25px' /> Inspect</div>
            <div class='col-xs-6'><button style='width:100%;height:42px;' onClick="observe_Controller(this);"><img src='https://www.nouveauelevator.com/image/black-icon/observe.png' width='25px' height='25px' /> Observe</div>
          </div>
          <div class='row' style='height:5px;'><div class='col-xs-12' style='height:5px;'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-6'><button style='width:100%;height:42px;' onClick="clean_Controller(this);"><img src='https://www.nouveauelevator.com/image/black-icon/clean.png' width='25px' height='25px' /> Clean</div>
            <div class='col-xs-6'><button style='width:100%;height:42px;' onClick="maintain_Controller(this);"><img src='https://www.nouveauelevator.com/image/black-icon/maintenance.png' width='25px' height='25px' /> Maintain</div>
          </div>
          <div class='row' style='height:5px;'><div class='col-xs-12' style='height:5px;'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-6'><button style='width:100%;height:42px;' onClick="repair_Controller(this);"><img src='https://www.nouveauelevator.com/image/black-icon/repair2.png' width='25px' height='25px' /> Repair</div>
            <div class='col-xs-6'><button style='width:100%;height:42px;' onClick="test_Controller(this);"><img src='https://www.nouveauelevator.com/image/black-icon/test.png' width='25px' height='25px' /> Test</div>
          </div>
          <div class='row' style='height:5px;'><div class='col-xs-12' style='height:5px;'>&nbsp;</div></div>
          <div class='row'>
            <div class='col-xs-6'><button style='width:100%;height:42px;' onClick="modernize_Controller(this);"><img src='https://www.nouveauelevator.com/image/black-icon/modernize.png' width='25px' height='25px' /> Modernize</div>
            <div class='col-xs-6'><button style='width:100%;height:42px;' onClick="troubleshoot_Controller(this);"><img src='https://www.nouveauelevator.com/image/black-icon/troubleshoot.png' width='25px' height='25px' /> Troubleshoot</div>
          </div>
          <script>
          function inspect_Controller(link){
            confirm("Are you inspecting the controller?");
          }
          function observe_Controller(link){
            confirm("Are you observing the controller?");
          }
          function clean_Controller(link){
            confirm("Are you cleaning the controller?");
          }
          function maintain_Controller(link){
            confirm("Are you maintaining the controller?");
          }
          function repair_Controller(link){
            confirm("Are you repairing the controller?");
          }
          function test_Controller(link){
            confirm("Are you testing the controller?");
          }
          function modernize_Controller(link){
            confirm("Are you modernizing the controller?");
          }
          function troubleshoot_Controller(link){
            confirm("Are you troubleshooting the controller?");
          }
          </script>
        </div><?php }?>
        <!--<div class='panel-heading'>Configuration</div>
        <div class='panel-body'>
          <div class='row'>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-faults.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/fault.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Faults</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-terminals.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/terminal.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Terminals</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-boards.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/board.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Boards</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-relays.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/relay.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Relays</div>
            </div>
          </div>
          <div class='row'>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-semi_conductors.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/semi_conductor.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Semi-Conductors</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-contacts.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/contact.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Contacts</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-rectifiers.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/rectifier.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Rectifier</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-transformers.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/transformer.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Transformers</div>
            </div>
          </div>
          <div class='row'>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-capacitors.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/capacitor.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Capacitors</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-resistors.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/resistor.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Resistors</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-coils.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/coil.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Coils</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-controller-scrs.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/scr.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>SCRs</div>
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
