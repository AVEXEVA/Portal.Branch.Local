<?php
session_start( [ 'read_and_close' => true ] );
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
?><!DOCTYPE html>
			<div class="panel panel-primary">
        <div class='panel-heading'><img src='media/images/icons/sheave.png' width='auto' height='35px' /> Sheave</div>
				<div class='panel-body' style='margin-top:10px;'>
          <div class='row'>
            <div class='col-xs-4'>Product:</div>
            <div class='col-xs-8'><input type='text' placeholder='Product Name' value='<?php echo isset($Product['Product']) ? $Product['Product'] : '';?>' name='Product' /></div>
            <div class='col-xs-4'>Serial:</div>
            <div class='col-xs-8'><input type='text' placeholder='Serial #' value='<?php echo isset($Product['Serial']) ? $Product['Serial'] : '';?>' name='Serial' /></div>
            <div class='col-xs-4'>Rim Width:</div>
            <div class='col-xs-8'><input type='text' placeholder='Rim Width' name='Rim_Width' value='<?php echo isset($Product['Rim_Width']) ? $Product['Rim_Width'] : '';?>' /></div>
            <div class='col-xs-4'>Hub Width:</div>
            <div class='col-xs-8'><input type='text' placeholder='Hub_Width' value='<?php echo isset($Product['Hub_Width']) ? $Product['Hub_Width'] : '';?>' name='Hub_Width' /></div>
            <div class='col-xs-4'>Weight:</div>
            <div class='col-xs-8'><input type='text' placeholder='Weight' value='<?php echo isset($Product['Weight']) ? $Product['Weight'] : '';?>' name='Weight' /></div>
            <div class='col-xs-4'>Grooves:</div>
            <div class='col-xs-8'><input type='text' placeholder='Grooves' value='<?php echo isset($Product['Grooves']) ? $Product['Grooves'] : '';?>' name='Grooves' /></div>
            <div class='col-xs-4'>Groove Diameter:</div>
            <div class='col-xs-8'><input type='text' placeholder='Groove_Diameter' value='<?php echo isset($Product['Groove_Diameter']) ? $Product['Groove_Diameter'] : '';?>' name='Groove_Diameter' /></div>
            <div class='col-xs-4'>Groove Type:</div>
            <div class='col-xs-8'><input type='text' placeholder='Groove_Type' value='<?php echo isset($Product['Groove_Type']) ? $Product['Groove_Type'] : '';?>' name='Groove_Type' /></div>
            <div class='col-xs-4'>Composition:</div>
            <div class='col-xs-8'><input type='text' placeholder='Composition' value='<?php echo isset($Product['Composition']) ? $Product['Composition'] : '';?>' name='Composition' /></div>
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
