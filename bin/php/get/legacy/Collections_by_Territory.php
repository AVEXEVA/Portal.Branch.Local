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
    if( isset($My_Privileges['Territory'], $My_Privileges['Invoice']) 
        && $My_Privileges['Territory']['Other'] >= 4
	   	&& $My_Privileges['Invoice']['Other'] >= 4){
			$Privileged = True;}
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){print json_encode(array('data'=>array()));}
    else {
        $r = $database->query(null,"
            SELECT OpenAR.Ref      AS Invoice,
                   OpenAR.fDate     AS Dated,
                   OpenAR.Due      AS Due,
                   OpenAR.fDesc    AS Description,
                   OpenAR.Original AS Original,
                   OpenAR.Balance  AS Balance,
				   Loc.Balance AS Total_Balance,
				           Invoice.PO      AS Purchase_Order,
				           Loc.Tag         AS Location
            FROM   nei.dbo.OpenAR
				   LEFT JOIN nei.dbo.Invoice ON OpenAR.Ref = Invoice.Ref
				   LEFT JOIN nei.dbo.Loc     ON Loc.Loc    = OpenAR.Loc
            WHERE  Loc.Terr = ?
        ;",array($_GET['ID']));
        $data = array();
		function makeInterest($Date, $Rate, $Original, $Balance){
			$thirtyDayDateInterval = new DateInterval('P30D');
			$Date = $Date->add($thirtyDayDateInterval);
			$yearDateInterval = new DateInterval('P365D');
			$Interest = 0;
			while(date('Y-m-d H:i:s') > $Date->format("Y-m-d H:i:s")){
				$Interest += $Original * $Rate;
				$Date = $Date->add($yearDateInterval);
			}
			return $Interest;
		}
    function sum($Location, $Balance, $Due){
  
    }
        if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){
			$array['Interest'] = makeInterest(new DateTime($array['Dated']),.09,floatval($array['Balance']),floatval($array['Balance']));
			$data[] = $array;
		}}
        print json_encode(array('data'=>$data));   
    }
}?>