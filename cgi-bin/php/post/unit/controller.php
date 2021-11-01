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
  if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html>
  <?php } else {
    if(isset($_POST['ID'])){
      $r = sqlsrv_query($NEI,
        " SELECT  ElevTItem.*
          FROM    ElevTItem
          WHERE   ElevTItem.Elev = ?
                  AND ElevTItem.ElevT = 4
        ;", array($_POST['ID']));
      $rows = array();
      if($r){
        while($row = sqlsrv_fetch_array($r)){$rows[] = $row;}
      }
      if(count($rows) == 0){
        $ids = array('Manufacturer'=>1648621, 'Model #'=>1648622, 'Software Version'=>1648623, 'Serial #'=>1648624, 'PO # of Maunfacture'=>1648625, 'Install Date'=>1648626, 'User Name'=>1648627, 'Password'=>1648628, 'Type'=>1648629, 'Grouping'=>1648630);
        $keys = array('Manufacturer'=>'Manufacturer', 'Model #'=>'Product', 'Software Version'=>'Software', 'Serial #'=>'Serial', 'PO # of Maunfacture'=>'Vendor_Purchase_Order', 'Install Date'=>'Installed', 'User Name'=>'User_Name', 'Password'=>'Password', 'Type'=>'Type', 'Grouping'=>'Grouping');
        $i = 1;
        foreach($keys as $c=>$v){
          $r = sqlsrv_query($NEI, "SELECT Max(ID) as Next_ID FROM nei.dbo.ElevTItem;");
          if($r){
            $Next_ID = sqlsrv_fetch_array($r)['Next_ID'] + 1;
            sqlsrv_query($NEI, "INSERT INTO nei.dbo.ElevTItem(ID, ElevT, Elev, CustomID, fDesc, [Line], Value, Format, fExists) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?);", array($Next_ID, 4, $_POST['ID'], $ids[$c], $c, $i, $_POST[$v], 'text', NULL));
            if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
            $i++;
          }
        }
      } else {
        $keys = array('Manufacturer'=>'Manufacturer', 'Model #'=>'Product', 'Software Version'=>'Software', 'Serial #'=>'Serial', 'PO # of Maunfacture'=>'Vendor_Purchase_Order', 'Install Date'=>'Installed', 'User Name'=>'User_Name', 'Password'=>'Password', 'Type'=>'Type', 'Grouping'=>'Grouping');
        foreach($keys as $c=>$v){
          sqlsrv_query($NEI, "UPDATE nei.dbo.ElevTItem SET ElevTItem.Value = ? WHERE ElevTItem.ElevT = 4 AND ElevTItem.Elev = ? AND ElevTItem.fDesc = ?;", array($_POST[$v], $_POST['ID'], $c));
          if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
        }
      }
    }
    if(isset($_POST['ID']) && !isset($_POST['Item'])){
      //INSERT BLANK RECORD INTO Item, Product & Controller
      //Insert into Product
      $sQuery =
        " INSERT INTO Device.dbo.Product(Name, Manufacturer)
          VALUES(?);";
      $params = array(
        $_POST['Product'],
        $_POST['Manufacturer']
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
          " INSERT INTO Device.dbo.Item(Type, Product, Device, [Serial], [Condition], Notes, Image, Image_Type, Length, Width, Height, Vendor_Purchase_Order, Blueprint)
            VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $params = array(
          'Controller',
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
          'Controller',
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
      $r = sqlsrv_query($database_Device, $sQuery, $params);

      //SELECT ID from Item
      $sQuery =
        " SELECT  Max(Item.ID) AS ID
          FROM    Device.dbo.Item;";
      $r = sqlsrv_query($database_Device, $sQuery);
      if($r){$Item_ID = sqlsrv_fetch_array($r)['ID'];}
      else {return;}

      //Insert into Controller
      $sQuery =
        " INSERT INTO Device.dbo.[Controller](Item, MRL, Motor_Overload, [Type], Mounting_Pad, Landings, Maximum_Car_Speed, [User_Name], [Password], [Grouping], [Software], [Installed])
          VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
      $params = array(
        $Item_ID,
        $_POST['MRL'],
        $_POST['Motor_Overload'],
        $_POST['Type'],
        $_POST['Mounting_Pad'],
        $_POST['Landings'],
        $_POST['Maximum_Car_Speed'],
        $_POST['User_Name'],
        $_POST['Password'],
        $_POST['Grouping'],
        $_POST['Software'],
        $_POST['Installed']
      );
      $r = sqlsrv_query($database_Device, $sQuery, $params);

      //Insert into Image
      if(isset($_FILES['Image']) && file_exists($_FILES['Image']['tmp_name'])){
        $sQuery = "INSERT INTO Device.dbo.Item_Image(Item, Image, Image_Type) VALUES(?, ?, ?);";
        $params = array(
          $Item_ID,
          base64_encode(file_get_contents($_FILES['Image']['tmp_name'])),
          $_FILES['Image']['type']
        );
        $r = sqlsrv_query($database_Device, $sQuery, $params);
      }
    } elseif(isset($_POST['ID'],$_POST['Item'])){
      //SELECT Product BY Item ID
      $sQuery =
        " SELECT  Item.Product AS Product_ID
          FROM    Device.dbo.Item
          WHERE   Item.ID = ?";
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
          SET     Product.Name = ?,
                  Product.Manufacturer = ?
          WHERE   Product.ID = ?;";
      $params = array(
        $_POST['Product'],
        $_POST['Manufacturer'],
        $Item['Product_ID']
      );
      $r = sqlsrv_query($database_Device, $sQuery, $params);

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
          'Controller',
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
          'Controller',
          $Item['Product_ID'],
          $_POST['ID'],
          $_POST['Serial'],
          $_POST['Condition'],
          $_POST['Notes'],
          $_POST['Length'],
          $_POST['Width'],
          $_POST['Height'],
          $_POST['Vendor_Purchase_Order'],
          $_POST['Blueprint'],
          $_POST['Item']
        );
      }
      $r = sqlsrv_query($database_Device, $sQuery, $params);

      //Update Controller
      $sQuery =
        " UPDATE  Device.dbo.Controller
          SET     Controller.MRL = ?,
                  Controller.Motor_Overload = ?,
                  Controller.[Type] = ?,
                  Controller.Mounting_Pad = ?,
                  Controller.Landings = ?,
                  Controller.Maximum_Car_Speed = ?,
                  Controller.[User_Name] = ?,
                  Controller.[Password] = ?,
                  Controller.[Grouping] = ?,
                  Controller.[Software] = ?,
                  Controller.[Installed] = ?
          WHERE   Controller.Item = ?;";
      $params = array(
        $_POST['MRL'],
        $_POST['Motor_Overload'],
        $_POST['Type'],
        $_POST['Mounting_Pad'],
        $_POST['Landings'],
        $_POST['Maximum_Car_Speed'],
        $_POST['User_Name'],
        $_POST['Password'],
        $_POST['Grouping'],
        $_POST['Software'],
        $_POST['Installed'],
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
        $r = sqlsrv_query($database_Device, $sQuery, $params);
      }
    }
  }
}
