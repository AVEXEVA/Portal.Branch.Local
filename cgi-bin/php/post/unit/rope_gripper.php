<?php
session_start();
require('../../index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
  $array = sqlsrv_fetch_array($r);
  if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
      sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "unit.php"));
      $r= sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
      $My_User = sqlsrv_fetch_array($r);
      $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
      $r = sqlsrv_query($Portal,"
          SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
          FROM   Portal.dbo.Privilege
          WHERE  User_ID = ?
      ;",array($_SESSION['User']));
      $My_Privileges = array();
      while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}
      $Privileged = FALSE;
      if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 6 && $My_Privileges['Unit']['Group_Privilege'] >= 4){$Privileged = TRUE;}
  }
  if(!isset($array['ID'])  || !$Privileged){?><html><head>
    <script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
  else {
    if(isset($_POST['ID']) && !isset($_POST['Item'])){
      //INSERT BLANK RECORD INTO Item, Product & Rope_Gripper
      //Insert into Product
      $sQuery =
        " INSERT INTO Device.dbo.Product(Name)
          VALUES(?);";
      $params = array(
        $_POST['Product']
      );
      $r = sqlsrv_query($database_Device, $sQuery, $params);

      //Select ID from Product
      $sQuery =
        " SELECT  Max(Product.ID) AS ID
          FROM    Device.dbo.Product;";
      $r = sqlsrv_query($database_Device, $sQuery);
      if($r){$Product_ID = sqlsrv_fetch_array($r)['ID'];}
      else {return;}

      //Insert into Item
      if(isset($_FILES['Image']) && file_exists($_FILES['Image']['tmp_name'])){
        $sQuery =
          " INSERT INTO Device.dbo.Item(Type, Product, Device, [Serial], [Condition], Notes, Image, Image_Type)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?);";
        $params = array(
          'Rope_Gripper',
          $Product_ID,
          $_POST['ID'],
          $_POST['Serial'],
          $_POST['Condition'],
          $_POST['Notes'],
          base64_encode(file_get_contents($_FILES['Image']['tmp_name'])),
          $_FILES['Image']['type']
        );
      } else {
        $sQuery =
          " INSERT INTO Device.dbo.Item(Type, Product, Device, [Serial], [Condition], Notes)
            VALUES(?, ?, ?, ?, ?, ?);";
        $params = array(
          'Rope_Gripper',
          $Product_ID,
          $_POST['ID'],
          $_POST['Serial'],
          $_POST['Condition'],
          $_POST['Notes']
        );
      }
      $r = sqlsrv_query($database_Device, $sQuery, $params);

      //SELECT ID from Item
      $sQuery =
        " SELECT  Max(Item.ID) AS ID
          FROM    Device.dbo.Item;";
      $r = sqlsrv_query($database_Device, $sQuery);
      if($r){$Item_ID = sqlsrv_fetch_array($r)['ID'];}
      else {return;}

      //Insert into Rope_Gripper
      $sQuery =
        " INSERT INTO Device.dbo.[Rope_Gripper](Item, Rated_Speed, Tripping_Speed, Rated_Load, System_Load, Contact_Ratings, Door_Zone, Roping)
          VALUES(?, ?, ?, ?, ?, ?, ?, ?);";
      $params = array(
        $Item_ID,
        $_POST['Rated_Speed'],
        $_POST['Tripping_Speed'],
        $_POST['Rated_Load'],
        $_POST['System_Load'],
        $_POST['Contact_Ratings'],
        $_POST['Door_Zone'],
        $_POST['Roping']
      );
      $r = sqlsrv_query($database_Device, $sQuery, $params);
    } elseif(isset($_POST['ID'],$_POST['Item'])){
      //SELECT Product BY Item ID
      $sQuery =
        " SELECT  Item.Product AS Product_ID
          FROM    Device.dbo.Item
          WHERE   Item.ID = ?;";
      $params = array(
        $_POST['Item']
      );
      $r = sqlsrv_query($database_Device, $sQuery, $params);
      if($r){$Item = sqlsrv_fetch_array($r);}
      else{return;}

      //UPDATE Product
      //echo $Item['Product_ID'];
      $sQuery =
        " UPDATE  Device.dbo.Product
          SET     Product.Name = ?
          WHERE   Product.ID = ?;";
      $params = array(
        $_POST['Product'],
        $Item['Product_ID']
      );
      $r = sqlsrv_query($database_Device, $sQuery, $params);

      //Update Item
      if(isset($_FILES['Image']) && file_exists($_FILES['Image']['tmp_name'])){
        $sQuery =
          " UPDATE  Device.dbo.Item
            SET     Item.Type = ?,
                    Item.Product = ?,
                    Item.Device = ?,
                    Item.Serial = ?,
                    Item.Condition = ?,
                    Item.Notes = ?,
                    Item.Image = ?,
                    Item.Image_Type = ?
            WHERE   Item.ID = ?;";
        $params = array(
          'Rope_Gripper',
          $Item['Product_ID'],
          $_POST['ID'],
          $_POST['Serial'],
          $_POST['Condition'],
          $_POST['Notes'],
          base64_encode(file_get_contents($_FILES['Image']['tmp_name'])),
          $_FILES['Image']['type'],
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
                    Item.Notes = ?
            WHERE   Item.ID = ?;";
        $params = array(
          'Rope_Gripper',
          $Item['Product_ID'],
          $_POST['ID'],
          $_POST['Serial'],
          $_POST['Condition'],
          $_POST['Notes'],
          $_POST['Item']
        );
      }
      $r = sqlsrv_query($database_Device, $sQuery, $params);

      //Update Rope_Gripper
      $sQuery =
        " UPDATE  Device.dbo.[Rope_Gripper]
          SET     Rope_Gripper.Rated_Speed = ?,
                  Rope_Gripper.Tripping_Speed = ?,
                  Rope_Gripper.Rated_Load = ?,
                  Rope_Gripper.System_Load = ?,
                  Rope_Gripper.Contact_Ratings = ?,
                  Rope_Gripper.Door_Zone = ?,
                  Rope_Gripper.Roping = ?
          WHERE   Rope_Gripper.Item = ?;";
      $params = array(
        $_POST['Rated_Speed'],
        $_POST['Tripping_Speed'],
        $_POST['Rated_Load'],
        $_POST['System_Load'],
        $_POST['Contact_Ratings'],
        $_POST['Door_Zone'],
        $_POST['Roping'],
        $_POST['Item']
      );
      $r = sqlsrv_query($database_Device, $sQuery, $params);
    }
  }
}
