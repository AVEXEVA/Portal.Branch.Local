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
          SELECT Access, Owner, Group, Other
          FROM   Portal.dbo.Privilege
          WHERE  User_ID = ?
      ;",array($_SESSION['User']));
      $My_Privileges = array();
      while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access']] = $My_Privilege;}
      $Privileged = FALSE;
      if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['Owner'] >= 6 && $My_Privileges['Unit']['Group'] >= 4){$Privileged = TRUE;}
  }
  if(!isset($array['ID'])  || !$Privileged){?><html><head>
    <script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
  else {
    if(isset($_POST['ID']) && !isset($_POST['Item'])){
      //INSERT BLANK RECORD INTO Item, Product & Governor
      //Insert into Product
      $sQuery = "INSERT INTO Device.dbo.Product(Name, Manufacturer) VALUES(?, ?);";
      $params = array($_POST['Product'], $_POST['Manufacturer']);
      $r = $database->query($database_Device, $sQuery, $params);
      //Select ID from Product
      $sQuery = "SELECT Max(Product.ID) AS ID FROM Device.dbo.Product;";
      $r = $database->query($database_Device, $sQuery);
      if($r){$Product_ID = sqlsrv_fetch_array($r)['ID'];}
      else {return;}
      //Insert into Item
      if(isset($_FILES['Image']) && file_exists($_FILES['Image']['tmp_name'])){
        $sQuery = "INSERT INTO Device.dbo.Item(Type, Product, Device, [Serial], [Condition], Notes, Image, Image_Type, Vendor_Purchase_Order, Blueprint) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $params = array('Governor', $Product_ID, $_POST['ID'], $_POST['Serial'], $_POST['Condition'], $_POST['Notes'], base64_encode(file_get_contents($_FILES['Image']['tmp_name'])), $_FILES['Image']['type'], $_POST['Vendor_Purchase_Order'], $_POST['Blueprint']);
      } else {
        $sQuery = "INSERT INTO Device.dbo.Item(Type, Product, Device, [Serial], [Condition], Notes, Vendor_Purchase_Order, Blueprint) VALUES(?, ?, ?, ?, ?, ?, ?, ?);";
        $params = array('Governor', $Product_ID, $_POST['ID'], $_POST['Serial'], $_POST['Condition'], $_POST['Notes'], $_POST['Vendor_Purchase_Order'], $_POST['Blueprint']);
      }
      $r = $database->query($database_Device, $sQuery, $params);
      //SELECT ID from Item
      $sQuery = "SELECT Max(Item.ID) AS ID FROM Device.dbo.Item;";
      $r = $database->query($database_Device, $sQuery);

      if($r){$Item_ID = sqlsrv_fetch_array($r)['ID'];}
      else {return;}
      //Insert into Governor
      $sQuery =
        " INSERT INTO Device.dbo.[Governor](Item, Type, Cable_Size, Cable_Length, Cable_Condition, Hand, Rated_Speed, Trip_Speed, WaterTight, DustTight, Guard, Pull_Through_Minimum, Pull_Through_Maximum, Tripper, Link, Spring, Adjuster)
          VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
      $params = array(
        $Item_ID,
        $_POST['Type'],
        $_POST['Cable_Size'],
        $_POST['Cable_Length'],
        $_POST['Cable_Condition'],
        $_POST['Hand'],
        $_POST['Rated_Speed'],
        $_POST['Trip_Speed'],
        $_POST['WaterTight'],
        $_POST['DustTight'],
        $_POST['Guard'],
        $_POST['Pull_Through_Minimum'],
        $_POST['Pull_Through_Maximum'],
        $_POST['Tripper'],
        $_POST['Link'],
        $_POST['Spring'],
        $_POST['Adjuster']
      );
      $r = $database->query($database_Device, $sQuery, $params);

      //Insert into Image
      if(isset($_FILES['Image']) && file_exists($_FILES['Image']['tmp_name'])){
        $sQuery = "INSERT INTO Device.dbo.Item_Image(Item, Image, Image_Type) VALUES(?, ?, ?);";
        $params = array(
          $Item_ID,
          base64_encode(file_get_contents($_FILES['Image']['tmp_name'])),
          $_FILES['Image']['type']
        );
        $r = $database->query($database_Device, $sQuery, $params);
      }
    } elseif(isset($_POST['ID'],$_POST['Item'])){
      //SELECT Product BY Item ID
      $sQuery = "SELECT Item.Product AS Product_ID FROM Device.dbo.Item WHERE Item.ID = ?";
      $params = array($_POST['Item']);
      $r = $database->query($database_Device, $sQuery, $params);
      if($r){$Item = sqlsrv_fetch_array($r);}
      else{return;}
      //UPDATE Product
      //echo $Item['Product_ID'];
      $sQuery = "UPDATE Device.dbo.Product SET Product.Name = ?, Product.Manufacturer = ? WHERE Product.ID = ?;";
      $params = array($_POST['Product'], $_POST['Manufacturer'], $Item['Product_ID']);
      $r = $database->query($database_Device, $sQuery, $params);
      //Update Item
      if(isset($_FILES['Image']) && file_exists($_FILES['Image']['tmp_name'])){
        $sQuery = "UPDATE Device.dbo.Item SET Item.Type = ?, Item.Product = ?, Item.Device = ?, Item.Serial = ?, Item.Condition = ?, Item.Notes = ?, Item.Image = ?, Item.Image_Type = ?, Item.Vendor_Purchase_Order = ?, Item.Blueprint = ? WHERE Item.ID = ?";
        $params = array('Governor', $Item['Product_ID'], $_POST['ID'], $_POST['Serial'], $_POST['Condition'], $_POST['Notes'], base64_encode(file_get_contents($_FILES['Image']['tmp_name'])), $_FILES['Image']['type'], $_POST['Vendor_Purchase_Order'], $_POST['Blueprint'], $_POST['Item']);
      } else {
        $sQuery = "UPDATE Device.dbo.Item SET Item.Type = ?, Item.Product = ?, Item.Device = ?, Item.Serial = ?, Item.Condition = ?, Item.Notes = ?, Item.Vendor_Purchase_Order = ?, Item.Blueprint = ? WHERE Item.ID = ?";
        $params = array('Governor', $Item['Product_ID'], $_POST['ID'], $_POST['Serial'], $_POST['Condition'], $_POST['Notes'], $_POST['Vendor_Purchase_Order'], $_POST['Blueprint'], $_POST['Item']);
      }
      $r = $database->query($database_Device, $sQuery, $params);
      //Update Governor
      $sQuery =
        " UPDATE  Device.dbo.[Governor]
          SET     Governor.Type = ?,
                  Governor.Cable_Size = ?,
                  Governor.Cable_Length = ?,
                  Governor.Cable_Condition = ?,
                  Governor.Hand = ?,
                  Governor.Rated_Speed = ?,
                  Governor.Trip_Speed = ?,
                  Governor.WaterTight = ?,
                  Governor.DustTight = ?,
                  Governor.Guard = ?,
                  Governor.Pull_Through_Minimum = ?,
                  Governor.Pull_Through_Maximum = ?,
                  Governor.Tripper = ?,
                  Governor.Link =?,
                  Governor.Spring = ?,
                  Governor.Adjuster = ?
          WHERE   Governor.Item = ?";
      $params = array(
        $_POST['Type'],
        $_POST['Cable_Size'],
        $_POST['Cable_Length'],
        $_POST['Cable_Condition'],
        $_POST['Hand'],
        $_POST['Rated_Speed'],
        $_POST['Trip_Speed'],
        $_POST['WaterTight'],
        $_POST['DustTight'],
        $_POST['Guard'],
        $_POST['Pull_Through_Minimum'],
        $_POST['Pull_Through_Maximum'],
        $_POST['Tripper'],
        $_POST['Link'],
        $_POST['Spring'],
        $_POST['Adjuster'],
        $_POST['Item']
      );
      $r = sqlsrv_Query($database_Device, $sQuery, $params);

      //Insert into Image
      if(isset($_FILES['Image']) && file_exists($_FILES['Image']['tmp_name'])){
        $sQuery = "INSERT INTO Device.dbo.Item_Image(Item, Image, Image_Type) VALUES(?, ?, ?);";
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
