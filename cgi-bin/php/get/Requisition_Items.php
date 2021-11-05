<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
        SELECT * 
        FROM   Connection 
        WHERE  Connection.Connector = ? 
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $My_User    = $database->query(null,"
        SELECT Emp.*, 
               Emp.fFirst AS First_Name, 
               Emp.Last   AS Last_Name 
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query($Portal,"
        SELECT Privilege.Access_Table, 
               Privilege.User_Privilege, 
               Privilege.Group_Privilege, 
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Requistion']) 
        && (
			$My_Privileges['Requistion']['Other_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
		$data = array();
        $r = $database->query(null,"
            SELECT Item.ID              AS ID,
			       Product.ID           AS Product,
				   Item.Quantity        AS Quantity,
				   Item.Requisition     AS Item_Requisition,
				   Product_Type.Name    AS Type,
				   Product.Name         AS Name,
				   Product.Description  AS Description,
				   Product.Manufacturer AS Manufacturer,
				   Product.Model        AS Model,
				   Product.Model_Number AS Model_Number,
				   Product.Notes        AS Notes,
				   ''                   AS Image
			FROM   Portal.dbo.Item
			       LEFT JOIN Portal.dbo.Product      ON Item.Product = Product.ID
				   LEFT JOIN Portal.dbo.Product_Type ON Product.Type = Product_Type.ID
			WHERE  Item.Requisition = ?
		;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));	
	}
}?>