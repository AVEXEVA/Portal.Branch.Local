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
	if( isset($My_Privileges['Location']) 
	   	&& $My_Privileges['Location']['Other'] >= 4
	  	&& $My_Privileges['Ticket']['Other_Privlege'] >= 4){
			$Privileged = True;} 
    if(!isset($Connection['ID'])  || !is_numeric($_GET['ID']) || !$Privileged){?><html><head><script>document.location.href="../login.php?Forward=job<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
		$r = $database->query(null,"
			SELECT Elev.ID    AS ID,
				   Elev.State AS State, 
				   Elev.Unit  AS Unit,
				   Elev.Type  AS Type,
				   Loc.Tag    AS Location
			FROM   nei.dbo.Elev
				   LEFT JOIN nei.dbo.Loc               ON Loc.Loc                 = Elev.Loc
				   LEFT JOIN Portal.dbo.Master_Account ON Master_Account.Customer = Loc.Owner
			WHERE  Master_Account.Master = ?;
		",array($_GET['ID']));
		$data = array();
		if($r){while($array = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC)){$data[] = $array;}}
		print json_encode(array('data'=>$data));   
	}
}