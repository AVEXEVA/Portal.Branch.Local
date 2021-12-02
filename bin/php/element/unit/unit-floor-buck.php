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
?><!DOCTYPE html>
			<div class="panel panel-primary">
        <div class='panel-heading'><img src='media/images/icons/saddle.png' width='auto' height='35px' /> Saddle</div>
				<div class='panel-body' style='margin-top:10px;'>
          <div class='row'>
            <div class='col-xs-4'>Product:</div>
            <div class='col-xs-8'><input type='text' placeholder='Product Name' value='<?php echo isset($Product['Product']) ? $Product['Product'] : '';?>' name='Product' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Length:</div>
            <div class='col-xs-8'><input type='text' placeholder='Length' value='<?php echo isset($Product['Length']) ? $Product['Length'] : '';?>' name='Product' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Width:</div>
            <div class='col-xs-8'><input type='text' placeholder='Width' value='<?php echo isset($Product['Width']) ? $Product['Width'] : '';?>' name='Product' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Height:</div>
            <div class='col-xs-8'><input type='text' placeholder='Height' value='<?php echo isset($Product['Height']) ? $Product['Height'] : '';?>' name='Product' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Opening Width:</div>
            <div class='col-xs-8'><input type='text' placeholder='Width' value='<?php echo isset($Product['Opening Width']) ? $Product['Width'] : '';?>' name='Product' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Opening Height:</div>
            <div class='col-xs-8'><input type='text' placeholder='Height' value='<?php echo isset($Product['Opening Height']) ? $Product['Height'] : '';?>' name='Product' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Type:</div>
            <div class='col-xs-8'><select name='Type'>
              <option value=''>Select</option>
              <option value='Centrifugal'>Centrifugal</option>
            </select></div>
            <div class='col-xs-4'>Rated Speed:</div>
            <div class='col-xs-8'><input type='text' placeholder='Rated Speed' value='<?php echo isset($Product['Rated_Speed']) ? $Product['Rated_Speed'] : '';?>' name='Rated_Speed' /></div>
            <div class='col-xs-4'>Weight:</div>
            <div class='col-xs-8'><input type='text' placeholder='Weight' value='<?php echo isset($Product['Weight']) ? $Product['Weight'] : '';?>' name='Weight' /></div>
            <div class='col-xs-4'>Serial:</div>
            <div class='col-xs-8'><input type='text' placeholder='Serial #' value='<?php echo isset($Product['Serial']) ? $Product['Serial'] : '';?>' name='Serial' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Trip Speed:</div>
            <div class='col-xs-8'><input placeholder='inches' type='text' value='<?php echo isset($Product['Trip_Speed']) ? $Product['Trip_Speed'] : '';?>' name='Trip_Speed' /></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>WaterTight:</div>
            <div class='col-xs-8'><select name='WaterTight'>
              <option value=''>Select</option>
              <option value='0'>No</option>
              <option value='1'>Yes</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>DustTight:</div>
            <div class='col-xs-8'><select name='DustTight'>
              <option value=''>Select</option>
              <option value='0'>No</option>
              <option value='1'>Yes</option>
            </select></div>
          </div>
          <div class='row'>
            <div class='col-xs-4'>Pull Through:</div>
            <div class='col-xs-3'><input size='4' placeholder='min' type='text' value='<?php echo isset($Product['Min_Pull_Through']) ? $Product['Min_Pull_Through'] : '';?>' name='Min_Pull_Through' /></div>
            <div class='col-xs-2'>to</div>
            <div class='col-xs-3'><input size='4' placeholder='max' type='text' value='<?php echo isset($Product['Max_Pull_Through']) ? $Product['Max_Pull_Through'] : '';?>' name='Max_Pull_Through' /></div>
          </div>
        </div>
        <div class='panel-heading'>Configuration</div>
        <div class='panel-body'>
          <div class='row'>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-governor-cable.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/cable.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Cable</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-safety_jaw.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/safety_jaw.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Safety Gear</div>
            </div>
            <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="someFunction(this,'unit-governor-sheave.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/sheave.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Sheave</div>
            </div>
          </div>
        </div>
			</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
