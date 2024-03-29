<?php 
session_start( [ 'read_and_close' => true ] );
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $Privileged = FALSE;
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r = $database->query(null,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = $database->query($Portal,"
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['Owner'] >= 4 && $My_Privileges['Job']['Group'] >= 4 && $My_Privileges['Job']['Other'] >= 4){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0 && isset($_POST['ID']) && is_numeric($_POST['ID'])){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
        $_POST['Expiration'] = $_POST['Expiration'] != "" ? date_format(date_create_from_format('m/d/Y',$_POST['Expiration']), 'Y-m-d 00:00:00.000') : "1900-01-01 00:00:00.000";
        var_dump($_POST);
        $database->query($Portal,"
			UPDATE Portal.dbo.Permit 
			SET    Name        = ?, 
				   Type        = ?, 
				   Location    = ?, 
				   Expiration  = ?, 
				   Description = ?, 
				   Link        = ? 
			WHERE  Permit.ID   = ?
			;", array($_POST['Name'], $_POST['Type'], $_POST['Location'], $_POST['Expiration'], $_POST['Description'], $_POST['Link'], $_POST['ID']));
		if( ($errors = sqlsrv_errors() ) != null) {
			foreach( $errors as $error ) {
				echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
				echo "code: ".$error[ 'code']."<br />";
				echo "message: ".$error[ 'message']."<br />";
			}
		}
    }
}?>