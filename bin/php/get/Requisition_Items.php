<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT *
        FROM Connection
        WHERE Connector = ?
        AND Hash = ?;",
    array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $User = \singleton\database::getInstance( )->query(
        null,
      " SELECT  *
        FROM Emp
        WHERE ID = ?",
    array($_GET['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && "OFFICE" != $User['Title']) ? True : False;
    if(!isset($array['ID']) ){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		$data = array();
        $r = \singleton\database::getInstance( )->query(
            null,
          " SELECT Item.ID              AS ID,
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
			FROM   Item
			       LEFT JOIN Product      ON Item.Product = Product.ID
				   LEFT JOIN Product_Type ON Product.Type = Product_Type.ID
			WHERE  Item.Requisition = ?;",
      array($_GET['ID']));
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));
	}
}?>
