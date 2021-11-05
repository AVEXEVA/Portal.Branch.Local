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
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4 && $My_Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 && $My_Privileges['Unit']['Group_Privilege'] >= 4){
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
        $params = array($_GET['ID'], 'Governor');
        $r = $database->query($database_Device, $sQuery, $params);
        if($r){
          $Item = sqlsrv_fetch_array($r);
          $sQuery = "SELECT Product.* FROM Device.dbo.Product WHERE Product.ID = ?";
          $params = array($Item['Product']);
          $r = $database->query($database_Device, $sQuery, $params);
          if($r){
            $Product = sqlsrv_fetch_array($r);
            $sQuery = "SELECT Governor.* FROM Device.dbo.Governor WHERE Governor.Item = ?";
            $params = array($Item['ID']);
            $r = $database->query($database_Device, $sQuery, $params);
            if($r){
              $Governor = sqlsrv_fetch_array($r);
            }
          }
        }
?><!DOCTYPE html>
      <style>#form_Governor div.col-xs-4 {text-align:right !important;}</style>
			<div class="panel panel-primary">
        <div class='panel-heading' onclick="someFunction(this,'unit-governor.php?ID=<?php echo $_GET['ID'];?>');"><img src='media/images/icons/governor.png' width='auto' height='35px' /> Governor</div>
				<div class='panel-body' style='padding-top:10px;'><form id='form_Governor'>
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
            <div class='col-xs-4'>Type:</div>
            <div class='col-xs-8'><select name='Type'>
              <option value=''>Select</option>
              <option <?php echo isset($Governor['Type']) && $Governor['Type'] == 'Centrifugal' ? 'selected' : '';?> value='Centrifugal'>Centrifugal</option>
              <option <?php echo isset($Governor['Type']) && $Governor['Type'] == 'Inertia' ? 'selected' : '';?> value='Inertia'>Inertia</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Cable Size:</div>
            <div class='col-xs-8'><select name='Cable_Size'>
              <option value=''>Select</option>
              <option <?php echo isset($Governor['Cable_Size']) && $Governor['Cable_Size'] == '5/16' ? 'selected' : '';?> value='5/16'>5/16</option>
              <option <?php echo isset($Governor['Cable_Size']) && $Governor['Cable_Size'] == '3/8' ? 'selected' : '';?> value='3/8'>3/8</option>
              <option <?php echo isset($Governor['Cable_Size']) && $Governor['Cable_Size'] == '1/2' ? 'selected' : '';?> value='1/2'>1/2</option>
              <option <?php echo isset($Governor['Cable_Size']) && $Governor['Cable_Size'] == 'Other' ? 'selected' : '';?> value='Bad'>Other</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Cable Length:</div>
            <div class='col-xs-8'><input type='text' name='Cable_Length' value='<?php echo isset($Governor['Cable_Length']) ? $Governor['Cable_Length'] : '';?>' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Cable Condition:</div>
            <div class='col-xs-8'><select name='Cable_Condition'>
              <option value=''>Select</option>
              <option <?php echo isset($Governor['Cable_Condition']) && $Governor['Cable_Condition'] == 'New' ? 'selected' : '';?> value='New'>New</option>
              <option <?php echo isset($Governor['Cable_Condition']) && $Governor['Cable_Condition'] == 'Good' ? 'selected' : '';?> value='Good'>Good</option>
              <option <?php echo isset($Governor['Cable_Condition']) && $Governor['Cable_Condition'] == 'Average' ? 'selected' : '';?> value='Average'>Average</option>
              <option <?php echo isset($Governor['Cable_Condition']) && $Governor['Cable_Condition'] == 'Bad' ? 'selected' : '';?> value='Bad'>Poor</option>
              <option <?php echo isset($Governor['Cable_Condition']) && $Governor['Cable_Condition'] == 'Broken' ? 'selected' : '';?> value='Broken'>Broken</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Hand:</div>
            <div class='col-xs-8'><select name='Hand'>
              <option value=''>Select</option>
              <option value='0' <?php echo isset($Governor['Hand']) && $Governor['Hand'] == '0' ? 'selected' : '';?>>Left</option>
              <option value='1' <?php echo isset($Governor['Hand']) && $Governor['Hand'] == '1' ? 'selected' : '';?>>Right</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Rated Speed:</div>
            <div class='col-xs-8'><input type='text' placeholder='Rated Speed' pattern='[0123456789]*' value='<?php echo isset($Governor['Rated_Speed']) ? $Governor['Rated_Speed'] : '';?>' name='Rated_Speed' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Trip Speed:</div>
            <div class='col-xs-8'><input placeholder='Trip Speed' type='text' pattern='[0123456789]*' value='<?php echo isset($Governor['Trip_Speed']) ? $Governor['Trip_Speed'] : '';?>' name='Trip_Speed' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>WaterTight:</div>
            <div class='col-xs-8'><select name='WaterTight'>
              <option value=''>Select</option>
              <option value='0' <?php echo isset($Governor['WaterTight']) && $Governor['WaterTight'] == 0 ? 'selected' : '';?>>No</option>
              <option value='1' <?php echo isset($Governor['WaterTight']) && $Governor['WaterTight'] == 1 ? 'selected' : '';?>>Yes</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>DustTight:</div>
            <div class='col-xs-8'><select name='DustTight'>
              <option value=''>Select</option>
              <option value='0' <?php echo isset($Governor['DustTight']) && $Governor['DustTight'] == 0 ? 'selected' : '';?>>No</option>
              <option value='1' <?php echo isset($Governor['DustTight']) && $Governor['DustTight'] == 1 ? 'selected' : '';?>>Yes</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Guard:</div>
            <div class='col-xs-8'><select name='Guard'>
              <option value=''>Select</option>
              <option value='0' <?php echo isset($Governor['Guard']) && $Governor['Guard'] == 0 ? 'selected' : '';?>>No</option>
              <option value='1' <?php echo isset($Governor['Guard']) && $Governor['Guard'] == 1 ? 'selected' : '';?>>Yes</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Pull Through:</div>
            <div class='col-xs-3'><input size='4' placeholder='min' type='text' pattern='[0123456789]*' value='<?php echo isset($Governor['Pull_Through_Minimum']) ? $Governor['Pull_Through_Minimum'] : '';?>' name='Pull_Through_Minimum' /></div>
            <div class='col-xs-2'>to</div>
            <div class='col-xs-3'><input size='4' placeholder='max' type='text' pattern='[0123456789]*' value='<?php echo isset($Governor['Pull_Through_Maximum']) ? $Governor['Pull_Through_Maximum'] : '';?>' name='Pull_Through_Maximum' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Tripper:</div>
            <div class='col-xs-8'><input placeholder='Tripper' type='text' value='<?php echo isset($Governor['Tripper']) ? $Governor['Tripper'] : '';?>' name='Tripper' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Link:</div>
            <div class='col-xs-8'><input placeholder='Link' type='text' value='<?php echo isset($Governor['Link']) ? $Governor['Link'] : '';?>' name='Link' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Spring:</div>
            <div class='col-xs-8'><input placeholder='Spring' type='text' value='<?php echo isset($Governor['Spring']) ? $Governor['Spring'] : '';?>' name='Spring' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Adjuster:</div>
            <div class='col-xs-8'><input placeholder='Adjuster' type='text' value='<?php echo isset($Governor['Adjuster']) ? $Governor['Adjuster'] : '';?>' name='Adjuster' /></div>
          </div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'><div class='col-xs-12'><button onClick='save_Governor(this);' type='button' style='width:100%;height:42px;'>Save</button></div></div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
        </form></div>
        <script>
          function save_Governor(link){
            $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
            $(link).attr('disabled','disabled');
            var formElement = document.getElementById('form_Governor');
            var formData = new FormData(formElement);
            formData.append('ID', '<?php echo $_GET['ID'];?>');
            if($("#form_Governor input:invalid").length == 0){
              $.ajax({
                url:"cgi-bin/php/post/unit/governor.php",
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
			</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
