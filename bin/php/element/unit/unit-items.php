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
			<div class="panel panel-primary" style='background-color:#151515 !important;'>
        <style>
          .panel-body-locations, .panel-body-objects {display:none;}
          .panel-body-locations.active, .panel-body-objects.active {display:block;}
        </style>
        <script>
        var title = "<?php echo !isset($_SESSION['Elevator_Menu_Swap']) || $_SESSION['Elevator_Menu_Swap'] == 0 ? 'Locations' : 'Objects';?>";
        function swap_menu(link){
            $(".panel-body-locations").toggleClass('active');
            $(".panel-body-objects").toggleClass('active');
            if(title == "Locations"){title = "Objects";}
            else{title = "Locations"}
            $("h4[rel='swap_title']").html(title);
            $.ajax({url:"bin/php/post/elevator_menu_swap.php"});
        }
        </script>
        <?php /*<div class='panel-heading'>
          <div class='row'>
            <div class='col-xs-2'><img src='media/images/icons/shaftway.png' width='auto' height='35px' /></div>
            <div class='col-xs-4'><h4 rel='swap_title'> <?php echo !isset($_SESSION['Elevator_Menu_Swap']) || $_SESSION['Elevator_Menu_Swap'] == 0 ? 'Locations' : 'Objects';?></h4></div>
            <div class='col-xs-2'>&nbsp;</div>
            <div class='col-xs-4'><button onClick='swap_menu(this);' style='width:100%;color:black;'>Swap</button></div>
          </div>
        </div>*/?>
				<div class='panel-body panel-body-locations <?php echo FALSE ? 'active' : '';?>' style='margin-top:10px;background-color:#151515 !important;'>
          <div class='row' style='height:75px;'>
            <div class='col-xs-3' onclick="someFunction(this,'unit-cab.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/cab.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Cab</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-car_top.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/car_top.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Car Top</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-floors.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/floor.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Floors</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-machine_room.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/machine_room.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Machine Room</div>
            </div>
          </div>
          <div class='row'>
            <div class='col-xs-3' onclick="someFunction(this,'unit-pit.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/pit.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Pit</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-platform.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/platform.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Platform</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-secondary.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/secondary.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Secondary</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-shaftway.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/shaftway.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Shaftway</div>
            </div>
          </div>
        </div>
        <div class='panel-body panel-body-objects <?php echo (!isset($_SESSION['Elevator_Menu_Swap']) || $_SESSION['Elevator_Menu_Swap'] == 0) || (isset($_SESSION['Elevator_Menu_Swap']) && $_SESSION['Elevator_Menu_Swap'] == 1) ? 'active' : '';?>' style='margin-top:10px;background-color:#151515 !important;'>
          <div class='row' style='height:75px;'>
            <div class='col-xs-3' onclick="someFunction(this,'unit-buffer.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/buffer.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Buffer</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-cab.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/cab.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Cab</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-car_station.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/car_station.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Car Station</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-car_top.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/car_top.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Car Top</div>
            </div>


          </div>
          <div class='row'>
            <div class='col-xs-3' onclick="someFunction(this,'unit-computer.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/computer.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Computer</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-controller.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/controller.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Controller</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-counterweight.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/counterweight_frame.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Counter-Weight</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-destination_dispatch.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/destination_dispatch.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Destination Dispatch</div>
            </div>



          </div>
          <div class='row'>
            <div class='col-xs-3' onclick="someFunction(this,'unit-door_operator.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/door_operator.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Door Operator</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-drive.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/drive.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Drive</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-edge.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/edge.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Edge</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-generator.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/generator.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Generator</div>
            </div>
          </div>
          <div class='row'>
            <div class='col-xs-3' onclick="someFunction(this,'unit-hall_lanterns.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/hall_lantern.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Hall Lanterns</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-governor.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/governor.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Governor</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-machine.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/machine.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Machine</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-main_line.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/main_line.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Main Line</div>
            </div>



          </div>
          <div class='row'>
            <div class='col-xs-3' onclick="someFunction(this,'unit-rope_gripper.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/rope_gripper.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Rope Gripper</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-saddle.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/saddle.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Saddle</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-selector.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/selector.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Selector</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-starter.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/starter.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Starter</div>
            </div>
          </div>
          <?php /*<div class='row'>

            <div class='col-xs-3' onclick="someFunction(this,'unit-traveler.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/traveler.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Traveler</div>
            </div>

            <div class='col-xs-3' onclick="someFunction(this,'unit-car_top_station.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/car_top_inspection_station.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Car Top Station</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-center_junction_box.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/center_junction_box.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Center Junction Box</div>
            </div>


            <div class='col-xs-3' onclick="someFunction(this,'unit-hall_laterns.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/hall_lantern.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Hall Lanterns</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-main_line.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/main_line.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Main Line</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-railings.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/railing.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Railings</div>
            </div>

            <div class='col-xs-3' onclick="someFunction(this,'unit-sling.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/sling.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Sling</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-toe_guard.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/toe_guard.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Toe Guard</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-door_package.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/door_package.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Door Packages</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-encoder.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/encoder.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Encoder</div>
            </div>
            <div class='col-xs-3' onclick="someFunction(this,'unit-tachometer.php?ID=<?php echo $_GET['ID'];?>');">
              <div class='nav-icon'><img src='media/images/icons/tachometer.png' width='auto' height='35px' /></div>
              <div class ='nav-text'>Tachometer</div>
            </div>
          </div>*/?>
				</div>
			</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
