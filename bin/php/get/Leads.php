<?php
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT *
        FROM   Connection
        WHERE  Connection.Connector = ?
               AND Connection.Hash = ?;",
    array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $User    = $database->query(null,
      " SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?
    ;", array($_SESSION[ 'User' ] ) );
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?;",
      array($_SESSION[ 'User' ] ) );
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2[ 'Access_Table' ]] = $array2;}
    $Privileged = False;
    if( isset($Privileges[ 'Lead' ])
        && (
				$Privileges[ 'Lead' ][ 'Other_Privilege' ] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {

        if( isset($_GET[ 'ID' ] ) && !in_array( $_GET[ 'ID' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['ID'];
			$conditions[] = "Lead.ID LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'Customer' ] ) && !in_array( $_GET[ 'Customer' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Customer'];
			$conditions[] = "Customer.Name LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'Description' ] ) && !in_array( $_GET[ 'Description' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Description'];
			$conditions[] = "Lead.fDesc LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'Street' ] ) && !in_array( $_GET[ 'Street' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Street'];
			$conditions[] = "Lead.Address LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'City' ] ) && !in_array( $_GET[ 'City' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['City'];
			$conditions[] = "Lead.City LIKE '%' + ? + '%'";
		}
		if( isset($_GET[ 'State' ] ) && !in_array( $_GET[ 'State' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['State'];
			$conditions[] = "Lead.State LIKE '%' + ? + '%'";
		}
        if( isset($_GET[ 'Zip' ] ) && !in_array( $_GET[ 'Zip' ], array( '', ' ', null ) ) ){
			$parameters[] = $_GET['Zip'];
			$conditions[] = "Lead.Zip LIKE '%' + ? + '%'";
		}
      $r = \singleton\database::getInstance( )->query(
            null,
          "  SELECT   Lead.ID           AS ID,
				     Lead.fDesc        AS Name,
				     Lead.Address      AS Street,
				     Lead.City         AS City,
				     Lead.State        AS State,
				     Lead.Zip          AS Zip,
				     OwnerWithRol.Name AS Customer
			FROM     Lead
					 LEFT JOIN OwnerWithRol ON OwnerWithRol.ID = Lead.Owner
			ORDER BY Lead.fDesc ASC",
      array(),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
		$data = array();
		$row_count = sqlsrv_num_rows( $r );
		if($r){
			while($i < $row_count){
				$Lead = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
				if(is_array($Lead) && $Lead != array()){
					$data[] = $Lead;
				}
				$i++;
			}
		}
		print json_encode(array('data'=>$data));
	}
}?>
