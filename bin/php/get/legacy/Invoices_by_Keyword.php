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
        SELECT Privilege.Access, 
               Privilege.Owner, 
               Privilege.Group, 
               Privilege.Other
        FROM   Privilege
        WHERE  Privilege.User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
    $Privileged = False;
    if( isset($My_Privileges['Invoice']) 
        && $My_Privileges['Invoice']['Other'] >= 4){
            $Privileged = True;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $Keyword = addslashes($_GET['Keyword']);
        $r = $database->query(null,"
            SELECT Invoice.Ref     	    AS ID,
                   Invoice.fDesc   	    AS Description,
                   Invoice.Total   	    AS Total,
                   Job.fDesc       	    AS Job,
                   Loc.Tag         	    AS Location,
                   Invoice.fDate   	    AS fDate,
                   OwnerWithRol.Name 	AS Customer
            FROM   nei.dbo.Invoice
                   LEFT JOIN nei.dbo.Loc   		    ON Invoice.Loc     = Loc.Loc
                   LEFT JOIN nei.dbo.Job   		    ON Invoice.Job     = Job.ID
                   LEFT JOIN nei.dbo.OwnerWithRol 	ON OwnerWithRol.ID = Loc.Owner
            WHERE  Invoice.Ref             LIKE '%{$Keyword}%'
                   OR Invoice.fDesc        LIKE '%{$Keyword}%'
                   OR Invoice.Total        LIKE '%{$Keyword}%'
                   OR Loc.Tag              LIKE '%{$Keyword}%'
                   OR OwnerWithRol.Name    LIKE '%{$Keyword}%'
        ;");
        $data = array();
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
        print json_encode(array('data'=>$data));   }
}?>