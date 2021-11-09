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
               AND Connection.Hash = ?
    ;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
    $User    = \singleton\database::getInstance( )->query(
        null,
      " SELECT Emp.*,
               Emp.fFirst AS First_Name,
               Emp.Last   AS Last_Name
        FROM   Emp
        WHERE  Emp.ID = ?;",
    array($_SESSION['User'] ) );
    $User = sqlsrv_fetch_arra y($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = \singleton\database::getInstance( )->query(
        null,
      " SELECT Privilege.Access_Table,
               Privilege.User_Privilege,
               Privilege.Group_Privilege,
               Privilege.Other_Privilege
        FROM   Privilege
        WHERE  Privilege.User_ID = ?;",
    array($_SESSION['User']));
    $Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = False;
    if( isset($Privileges['Accounts_Payable'])
        && (
		    $Privileges['Accounts_Payable']['User_Privilege'] >= 4
    &&  $Privileges['Accounts_Payable']['Group_Privilege'] >= 4
    &&  $Privileges['Accounts_Payable']['Other_Privilege'] >= 4
		)
	 ){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $r = \singleton\database::getInstance( )->query(
            null,
        "  SELECT  Paid.PITR     AS PITR,
        				   Paid.Type     AS Type,
        				   Paid.Line     AS Line,
                   Paid.fDate    AS Date,
        				   Paid.fDesc    AS Description,
        				   Paid.Original AS Original,
        				   Paid.Balance  AS Balance,
        				   Paid.Disc     AS Discount,
        				   Paid.Paid     AS Paid,
        				   Paid.TRID     AS TRID
                   FROM   Paid
                   LEFT JOIN Trans ON Paid.TRID = Trans.ID;"
        );
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}?>
