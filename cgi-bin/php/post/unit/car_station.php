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
      //INSERT BLANK RECORD INTO Item, Product & Car_Station
      //Insert into Product
      $sQuery = "INSERT INTO Device.dbo.Product(Name) VALUES(?);";
      $params = array($_POST['Product']);
      $r = sqlsrv_query($database_Device, $sQuery, $params);
      //Select ID from Product
      $sQuery = "SELECT Max(Product.ID) AS ID FROM Device.dbo.Product;";
      $r = sqlsrv_query($database_Device, $sQuery);
      if($r){$Product_ID = sqlsrv_fetch_array($r)['ID'];}
      else {return;}
      //Insert into Item
      if(isset($_FILES['Image'])){
        $sQuery = "INSERT INTO Device.dbo.Item(Type, Product, Device, [Serial], [Condition], Notes, Image, Image_Type, Length, Width, Height) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);";
        $params = array('Car_Station', $Product_ID, $_POST['ID'], $_POST['Serial'], $_POST['Condition'], $_POST['Notes'], base64_encode(file_get_contents($_FILES['Image']['tmp_name'])), $_FILES['Image']['type'], $_POST['Length'], $_POST['Width'], $_POST['Height']);
      } else {
        $sQuery = "INSERT INTO Device.dbo.Item(Type, Product, Device, [Serial], [Condition], Notes) VALUES(?, ?, ?, ?, ?, ?, ?, ? ,?);";
        $params = array('Car_Station', $Product_ID, $_POST['ID'], $_POST['Serial'], $_POST['Condition'], $_POST['Notes'], $_POST['Length'], $_POST['Width'], $_POST['Height']);
      }
      $r = sqlsrv_query($database_Device, $sQuery, $params);
      //SELECT ID from Item
      $sQuery = "SELECT Max(Item.ID) AS ID FROM Device.dbo.Item;";
      $r = sqlsrv_query($database_Device, $sQuery);
      if($r){$Item_ID = sqlsrv_fetch_array($r)['ID'];}
      else {return;}
      //Insert into Car_Station
      $sQuery = "INSERT INTO Device.dbo.[Car_Station](Item, No_Smoking, Gong, Handicap_Chimes, Electrical_Outlet, Service_Cabinet, Intercom, Composition) VALUES(?, ?, ?, ?, ?, ?, ?, ?);";
      $params = array($Item_ID, $_POST['No_Smoking'], $_POST['Gong'], $_POST['Handicap_Chimes'], $_POST['Electrical_Outlet'], $_POST['Service_Cabinet'], $_POST['Intercom'], $_POST['Composition']);
      $r = sqlsrv_query($database_Device, $sQuery, $params);
    } elseif(isset($_POST['ID'],$_POST['Item'])){
      //SELECT Product BY Item ID
      $sQuery = "SELECT Item.Product AS Product_ID FROM Device.dbo.Item WHERE Item.ID = ?";
      $params = array($_POST['Item']);
      $r = sqlsrv_query($database_Device, $sQuery, $params);
      if($r){$Item = sqlsrv_fetch_array($r);}
      else{return;}
      //UPDATE Product
      //echo $Item['Product_ID'];
      $sQuery = "UPDATE Device.dbo.Product SET Product.Name = ? WHERE Product.ID = ?;";
      $params = array($_POST['Product'], $Item['Product_ID']);
      $r = sqlsrv_query($database_Device, $sQuery, $params);
      //Update Item
      if(isset($_FILES['Image'])){
        $sQuery = "UPDATE Device.dbo.Item SET Item.Type = ?, Item.Product = ?, Item.Device = ?, Item.Serial = ?, Item.Condition = ?, Item.Notes = ?, Item.Image = ?, Item.Image_Type = ?, Item.Length = ?, Item.Width = ?, Item.Height = ? WHERE Item.ID = ?";
        $params = array('Car_Station', $Item['Product_ID'], $_POST['ID'], $_POST['Serial'], $_POST['Condition'], $_POST['Notes'], base64_encode(file_get_contents($_FILES['Image']['tmp_name'])), $_FILES['Image']['type'], $_POST['Length'], $_POST['Width'], $_POST['Height'], $_POST['Item']);
      } else {
        $sQuery = "UPDATE Device.dbo.Item SET Item.Type = ?, Item.Product = ?, Item.Device = ?, Item.Serial = ?, Item.Condition = ?, Item.Notes = ?, Item.Length = ?, Item.Width = ?, Item.Height = ? WHERE Item.ID = ?";
        $params = array('Car_Station', $Item['Product_ID'], $_POST['ID'], $_POST['Serial'], $_POST['Condition'], $_POST['Notes'], $_POST['Length'], $_POST['Width'], $_POST['Height'], $_POST['Item']);
      }
      $r = sqlsrv_query($database_Device, $sQuery, $params);
      //Update Car_Station
      $sQuery = "UPDATE Device.dbo.[Car_Station] SET Car_Station.No_Smoking = ?, Car_Station.Gong = ?, Car_Station.Handicap_Chimes = ?, Car_Station.Electrical_Outlet = ?, Car_Station.Service_Cabinet = ?, Car_Station.Intercom = ?, Car_Station.Composition = ? WHERE Car_Station.Item = ?";
      $params = array($_POST['No_Smoking'], $_POST['Gong'], $_POST['Handicap_Chimes'], $_POST['Electrical_Outlet'], $_POST['Service_Cabinet'], $_POST['Intercom'], $_POST['Composition'], $_POST['Item'] );
      $r = sqlsrv_Query($database_Device, $sQuery, $params);
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
