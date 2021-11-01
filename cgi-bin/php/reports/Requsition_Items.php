<?php 
session_start();
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $My_User = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Emp WHERE ID = ?",array($_GET['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $Field = ($User['Field'] == 1 && "OFFICE" != $My_User['Title']) ? True : False;
    if(!isset($array['ID']) ){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		$data = array();
        $r = sqlsrv_query($NEI,"
            SELECT Item.ID              AS ID,
			       Item.Product         AS Product_ID,
				   Item.Quantity        AS Item_Quantity,
				   Item.Requisition     AS Item_Requisition,
				   Product_Type.Name    AS Product_Type,
				   Product.Name         AS Product_Name,
				   Product.Description  AS Product_Description,
				   Product.Manufacturer AS Manufacturer,
				   Product.Model        AS Model,
				   Product.Model_Number AS Model_Number,
				   Product.Notes        AS Product_Notes,
				   ''                   AS Product_Image
			FROM   Portal.dbo.Item
			       LEFT JOIN Portal.dbo.Product      ON Item.Product = Product.ID
				   LEFT JOIN Portal.dbo.Product_Type ON Product.Type = Product_Type.ID
			WHERE  Item.Requisition = ?
		;",array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));	
	}
}?>