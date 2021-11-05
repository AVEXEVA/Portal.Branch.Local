<?php
session_start( [ 'read_and_close' => true ] );
require('../../index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = $database->query(null,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
  $array = sqlsrv_fetch_array($r);
  if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
      $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
      $r= $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
      $My_User = sqlsrv_fetch_array($r);
      $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
      $r = $database->query($Portal,"
          SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
          FROM   Portal.dbo.Privilege
          WHERE  User_ID = ?
      ;",array($_SESSION['User']));
      $My_Privileges = array();
      while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
      $Privileged = FALSE;
      if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 6 && $My_Privileges['Unit']['Group_Privilege'] >= 4){$Privileged = TRUE;}
  }
  if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html>
  <?php } else {
    if(isset($_POST['ID']) && !isset($_POST['Item'])){
      //INSERT BLANK RECORD INTO Item, Product & Machine
      //Insert into Product
      $sQuery =
        " INSERT INTO Device.dbo.Product(Name, Manufacturer)
          VALUES(?, ?);";
      $params = array(
        $_POST['Product'],
        $_POST['Manufacturer']
      );
      $r = $database->query($database_Device, $sQuery, $params);

      //Select ID from Product
      $sQuery =
        " SELECT Max(Product.ID) AS ID
          FROM Device.dbo.Product;";
      $r = $database->query($database_Device, $sQuery);
      if($r){$Product_ID = sqlsrv_fetch_array($r)['ID'];}
      else {return;}
      //Insert into Item
      if(isset($_FILES['Image'])){
        $sQuery =
          " INSERT INTO Device.dbo.Item(Type, Product, Device, [Serial], [Condition], Notes, Image, Image_Type, Length, Width, Height, Vendor_Purchase_Order, Blueprint)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $params = array(
          'Machine',
          $Product_ID,
          $_POST['ID'],
          $_POST['Serial'],
          $_POST['Condition'],
          $_POST['Notes'],
          base64_encode(file_get_contents($_FILES['Image']['tmp_name'])),
          $_FILES['Image']['type'],
          $_POST['Length'],
          $_POST['Width'],
          $_POST['Height'],
          $_POST['Vendor_Purchase_Order'],
          $_POST['Blueprint']
        );
      } else {
        $sQuery =
          " INSERT INTO Device.dbo.Item(Type, Product, Device, [Serial], [Condition], Notes, Length, Width, Height, Vendor_Purchase_Order, Blueprint)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $params = array(
          'Machine',
          $Product_ID,
          $_POST['ID'],
          $_POST['Serial'],
          $_POST['Condition'],
          $_POST['Notes'],
          $_POST['Length'],
          $_POST['Width'],
          $_POST['Height'],
          $_POST['Vendor_Purchase_Order'],
          $_POST['Blueprint']
        );
      }
      $r = $database->query($database_Device, $sQuery, $params);
      //SELECT ID from Item
      $sQuery =
        " SELECT Max(Item.ID) AS ID
          FROM Device.dbo.Item;";
      $r = $database->query($database_Device, $sQuery);
      if($r){$Item_ID = sqlsrv_fetch_array($r)['ID'];}
      else {return;}
      //Insert into Machine
      $sQuery =
        " INSERT INTO Device.dbo.[Machine](Item, [Power_Type], [Input_Voltage], [Input_Amperage], [RPM], [Horsepower], [Roping], [Drum_Size], [Geared], [MRL], [Mounting_Pad], [Cable_Size], [Cable_Quantity], [Cable_Length], [Cable_Condition])
          VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
      $params = array(
        $Item_ID,
        $_POST['Power_Type'],
        $_POST['Input_Voltage'],
        $_POST['Input_Amperage'],
        $_POST['RPM'],
        $_POST['Horsepower'],
        $_POST['Roping'],
        $_POST['Drum_Size'],
        $_POST['Geared'],
        $_POST['MRL'],
        $_POST['Mounting_Pad'],
        $_POST['Cable_Size'],
        $_POST['Cable_Quantity'],
        $_POST['Cable_Length'],
        $_POST['Cable_Condition']
      );
      $r = $database->query($database_Device, $sQuery, $params);

      //Insert into Image
      if(isset($_FILES['Image']) && file_exists($_FILES['Image']['tmp_name'])){
        $sQuery =
          " INSERT INTO Device.dbo.Item_Image(Item, Image, Image_Type)
            VALUES(?, ?, ?);";
        $params = array(
          $Item_ID,
          base64_encode(file_get_contents($_FILES['Image']['tmp_name'])),
          $_FILES['Image']['type']
        );
        $r = $database->query($database_Device, $sQuery, $params);
      }
    } elseif(isset($_POST['ID'],$_POST['Item'])){
      //SELECT Product BY Item ID
      $sQuery =
        " SELECT  Item.Product AS Product_ID
          FROM    Device.dbo.Item
          WHERE   Item.ID = ?;";
      $params = array($_POST['Item']);
      $r = $database->query($database_Device, $sQuery, $params);
      if($r){$Item = sqlsrv_fetch_array($r);}
      else{return;}
      //UPDATE Product
      //echo $Item['Product_ID'];
      $sQuery =
        " UPDATE  Device.dbo.Product
          SET     Product.Name = ?,
                  Product.Manufacturer = ?
          WHERE   Product.ID = ?;";
      $params = array($_POST['Product'], $_POST['Manufacturer'], $Item['Product_ID']);
      $r = $database->query($database_Device, $sQuery, $params);
      //Update Item
      if(isset($_FILES['Image'])){
        $sQuery =
          " UPDATE  Device.dbo.Item
            SET     Item.Type = ?,
                    Item.Product = ?,
                    Item.Device = ?,
                    Item.Serial = ?,
                    Item.Condition = ?,
                    Item.Notes = ?,
                    Item.Image = ?,
                    Item.Image_Type = ?,
                    Item.Length = ?,
                    Item.Width = ?,
                    Item.Height = ?,
                    Item.Vendor_Purchase_Order = ?,
                    Item.Blueprint = ?
            WHERE   Item.ID = ?;";
        $params = array(
          'Machine',
          $Item['Product_ID'],
          $_POST['ID'],
          $_POST['Serial'],
          $_POST['Condition'],
          $_POST['Notes'],
          base64_encode(file_get_contents($_FILES['Image']['tmp_name'])),
          $_FILES['Image']['type'],
          $_POST['Length'],
          $_POST['Width'],
          $_POST['Height'],
          $_POST['Vendor_Purchase_Order'],
          $_POST['Blueprint'],
          $_POST['Item']
        );
      } else {
        $sQuery =
          " UPDATE  Device.dbo.Item
            SET     Item.Type = ?,
                    Item.Product = ?,
                    Item.Device = ?,
                    Item.Serial = ?,
                    Item.Condition = ?,
                    Item.Notes = ?,
                    Item.Length = ?,
                    Item.Width = ?,
                    Item.Height = ?,
                    Item.Vendor_Purchase_Order = ?,
                    Item.Blueprint = ?
            WHERE   Item.ID = ?;";
        $params = array(
          'Machine',
          $Item['Product_ID'],
          $_POST['ID'],
          $_POST['Serial'],
          $_POST['Condition'],
          $_POST['Notes'],
          $_POST['Length'],
          $_POST['Width'],
          $_POST['Height'],
          $_POST['Item'],
          $_POST['Vendor_Purchase_Order'],
          $_POST['Blueprint']
        );
      }
      $r = $database->query($database_Device, $sQuery, $params);
      //Update Machine
      $sQuery =
        " UPDATE  Device.dbo.Machine
          SET     Machine.Power_Type = ?,
                  Machine.Input_Voltage = ?,
                  Machine.Input_Amperage = ?,
                  Machine.RPM = ?,
                  Machine.Horsepower = ?,
                  Machine.Roping = ?,
                  Machine.Drum_Size = ?,
                  Machine.Geared = ?,
                  Machine.MRL = ?,
                  Machine.Mounting_Pad = ?,
                  Machine.Cable_Size = ?,
                  Machine.Cable_Quantity = ?,
                  Machine.Cable_Length = ?,
                  Machine.Cable_Condition = ?
          WHERE   Machine.Item = ?;";
      $params = array(
        $_POST['Power_Type'],
        $_POST['Input_Voltage'],
        $_POST['Input_Amperage'],
        $_POST['RPM'],
        $_POST['Horsepower'],
        $_POST['Roping'],
        $_POST['Drum_Size'],
        $_POST['Geared'],
        $_POST['MRL'],
        $_POST['Mounting_Pad'],
        $_POST['Cable_Size'],
        $_POST['Cable_Quantity'],
        $_POST['Cable_Length'],
        $_POST['Cable_Condition'],
        $_POST['Item']
      );
      $r = sqlsrv_Query($database_Device, $sQuery, $params);

      //Insert into Image
      if(isset($_FILES['Image']) && file_exists($_FILES['Image']['tmp_name'])){
        $sQuery =
          " INSERT INTO Device.dbo.Item_Image(Item, Image, Image_Type)
            VALUES(?, ?, ?);";
        $params = array(
          $_POST['Item'],
          base64_encode(file_get_contents($_FILES['Image']['tmp_name'])),
          $_FILES['Image']['type']
        );
        $r = $database->query($database_Device, $sQuery, $params);
      }
    }
  }
}
