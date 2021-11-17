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
        $params = array($_GET['ID'], 'Computer');
        $r = $database->query($database_Device, $sQuery, $params);
        if($r){
          $Item = sqlsrv_fetch_array($r);
          $sQuery = "SELECT Product.* FROM Device.dbo.Product WHERE Product.ID = ?";
          $params = array($Item['Product']);
          $r = $database->query($database_Device, $sQuery, $params);
          if($r){
            $Product = sqlsrv_fetch_array($r);
            $sQuery = "SELECT Computer.* FROM Device.dbo.Computer WHERE Computer.Item = ?";
            $params = array($Item['ID']);
            $r = $database->query($database_Device, $sQuery, $params);
            if($r){
              $Computer = sqlsrv_fetch_array($r);
            }
          }
        }
?><!DOCTYPE html>
			<div class="panel panel-primary">
        <div class='panel-heading' onclick="someFunction(this,'unit-computer.php?ID=<?php echo $_GET['ID'];?>');"><img src='media/images/icons/computer.png' width='auto' height='35px' /> Computer</div>
				<div class='panel-body' style='padding-top:10px;'><form id='form_Computer'>
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
            <div class='col-xs-4'>O.S.</div>
            <div class='col-xs-8'><select name='Operating_System'>
              <option value=''>Select</option>
              <option <?php echo $Computer['Operating_System'] == 'Windows' ? 'selected' : '';?> value='Windows'>Windows</option>
              <option <?php echo $Computer['Operating_System'] == 'Linux' ? 'selected' : '';?> value='Linux'>Linux</option>
              <option <?php echo $Computer['Operating_System'] == 'Windows DOS' ? 'selected' : '';?> value='Windows DOS'>Windows DOS</option>
              <option <?php echo $Computer['Operating_System'] == 'Other' ? 'selected' : '';?> value='Other'>Other</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Username:</div>
            <div class='col-xs-8'><input placeholder='Username' type='text' value='<?php echo isset($Computer['Username']) ? $Computer['Username'] : '';?>' name='Username' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Password:</div>
            <div class='col-xs-8'><input placeholder='Password' type='text' value='<?php echo isset($Computer['Password']) ? $Computer['Password'] : '';?>' name='Password' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'><div class='col-xs-12'><button onClick='save_Computer(this);' type='button' style='width:100%;height:42px;'>Save</button></div></div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
        </form></div>
        <script>
          function save_Computer(link){
            $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
            $(link).attr('disabled','disabled');
            var formElement = document.getElementById('form_Computer');
            var formData = new FormData(formElement);
            formData.append('ID', '<?php echo $_GET['ID'];?>');
            if($("#form_Computer input:invalid").length == 0){
              $.ajax({
                url:"bin/php/post/unit/computer.php",
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
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-computer-software.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/software.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Software</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-computer-keyboard.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/keyboard.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Keyboard</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-computer-mouse.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/mouse.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Mouse</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-computer-monitor.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/monitor.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Monitor</div>
            </div>
          </div>
          <div class='row'>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-computer-cables.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/cables.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Cables</div>
            </div>
          </div>
        </div>-->
			</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
