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
    if( isset($My_Privileges['Contract'], $My_Privleges['Job']) 
        && $My_Privileges['Contract']['Other_Privilege'] >= 4
	  	&& $My_Privileges['Job']['Other_Privilege'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $r = sqlsrv_query($NEI,"
            SELECT Contract.Job      AS Contract_Job,
				   Contract.BAmt     AS Contract_Amount,
                   Contract.BLenght  AS Contract_Length,
                   Contract.BCycle   AS Contract_Billing_Cycle,
                   Contract.BStart   AS Contract_Start,
				   Contract.Review   AS Contract_Review,
				   Contract.BFinish  AS Contract_End,
				   Contract.BEscType AS Contract_Escalation_Type,
                   Loc.Tag           AS Location,
				   Job.Custom15      AS Link
            FROM   nei.dbo.Contract 
                   LEFT JOIN nei.dbo.Loc ON Contract.Loc = Loc.Loc
				   LEFT JOIN nei.dbo.Job ON Contract.Job = Job.ID
            WHERE  Contract.Job = ?
        ;",array($_GET['ID']));
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}?>