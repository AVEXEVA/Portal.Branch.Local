<?php 
session_start( [ 'read_and_close' => true ] );
require('index.php');
setlocale(LC_MONETARY, 'en_US');
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
    $r = $database->query( null,"
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
    if( isset($My_Privileges['Invoice']) 
        && $My_Privileges['Invoice']['Other_Privilege'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID'])  || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $r = $database->query(null,"
            SELECT OpenAR.Ref        AS  Invoice,
                   OpenAR.fDate      AS  Dated,
                   OpenAR.Due        AS  Due,
                   OpenAR.fDesc      AS  Description,
                   OpenAR.Original   AS  Original,
                   OpenAR.Balance    AS  Balance,
                   OwnerWithRol.Name AS  Customer,
                   Loc.Tag           AS  Location,
				   CASE WHEN OpenAR.Original = OpenAR.Balance THEN 'Unpaid' ELSE 'Partial' END AS Partial,
				   JobType.Type      AS Job_Type,
				   Job.fDesc         AS Job_Name
            FROM   OpenAR
                   LEFT JOIN Loc 		  ON OpenAR.Loc  = Loc.Loc
                   LEFT JOIN OwnerWithRol ON Loc.Owner 	 = OwnerWithRol.ID
				   LEFT JOIN Invoice      ON OpenAR.Ref  = Invoice.Ref
				   LEFT JOIN Job          ON Invoice.Job = Job.ID
				   LEFT JOIN JobType      ON Job.Type    = JobType.ID
        ;");
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   
	}
}?>