<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $User    = \singleton\database::getInstance( )->query(
        null,
      " SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT Privilege.Access,
               Privilege.Owner,
               Privilege.Group,
               Privilege.Other
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Product'])
        && (
			$Privileges['Product']['Owner'] >= 4
  &&  $Privileges['Product']['Group'] >= 4
  &&  $Privileges['Product']['Other'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['ID'];
			$conditions[] = "Product.ID LIKE '%' + ? + '%'";
		}

        if( isset($_GET[ 'Name' ] ) && !in_array( $_GET[ 'Name' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Name'];
			$conditions[] = "Product.Name LIKE '%' + ? + '%'";
		}


        $data = array();
		$r = \singleton\database::getInstance( )->query(
        null,
    " SELECT Product.*
			FROM   Portal.dbo.Product
		;");
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));	}
}?>
