<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
			   AND Connection.Hash = ?
	;", array($_SESSION['User'],$_SESSION['Hash']));
    $Connection = sqlsrv_fetch_array($r);
	$My_User    = sqlsrv_query($NEI,"
		SELECT Emp.*, 
			   Emp.fFirst AS First_Name, 
			   Emp.Last   AS Last_Name 
		FROM   Emp
		WHERE  Emp.ID = ?
	;", array($_SESSION['User']));
	$My_User = sqlsrv_fetch_array($My_User); 
	$My_Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
	$r = sqlsrv_query($Portal,"
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
	 if( isset($My_Privileges['RMA']) 
        && $My_Privileges['RMA']['Other_Privilege'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
	else {
        $r = sqlsrv_query($NEI,"
            SELECT RMA.ID          AS ID,
				   RMA.Name        AS Name,
				   RMA.Date        AS Date,
				   RMA.Address     AS Address,
				   RMA.RMA         AS RMA,
				   RMA.Received    AS Received,
				   RMA.Returned    AS Returned,
				   RMA.Tracking    AS Tracking,
				   RMA.PO          AS PO,
				   RMA.Link        AS Link,
				   RMA.Status      AS Status,
				   RMA.Description AS Description,
				   Loc.Tag         AS Location
			FROM   Portal.dbo.RMA
			       LEFT JOIN nei.dbo.Loc ON Loc.Loc = RMA.Location
		;");
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));	}
}?>