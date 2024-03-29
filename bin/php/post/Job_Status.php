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
        if(isset($My_Privileges['Job']) && $My_Privileges['Job']['Owner'] >= 6 && $My_Privileges['Job']['Group'] >= 6 && $My_Privileges['Job']['Other'] >= 6){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Job){
					$resource = $database->query(null,"
						SELECT Job_Status.ID
						FROM   nei.dbo.Job_Status
						WHERE  Job_Status.Status = ?
					;",array($Job['Status']));
					if($r){$Job_Status_ID = sqlsrv_fetch_array($resource)['ID'];}
					$Job_Status_ID--;
					$database->query(null,"
						UPDATE nei.dbo.Job
						SET    Job.Status = ?
						WHERE  Job.ID     = ?
					;", array($Job_Status_ID ,$ID));
					$Job['ID'] = intval($ID);
					$data[] = $Job;
				}
				print json_encode(array("data"=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create' && FALSE){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				foreach($_POST['data'] as $ID=>$Job){
					$resource = $database->query(null,"
						SELECT Loc.Loc AS Location_ID
						FROM   nei.dbo.Loc
						WHERE  Loc.Tag = ?
					;",array($Job['Location']));
					if($r){$Location_ID = sqlsrv_fetch_array($resource)['Location_ID'];}
					$resource = $database->query(null,"SELECT Max(Elev.ID) AS ID FROM nei.dbo.Elev;");
					$Job_Primary_Key = sqlsrv_fetch_array($resource)['ID'];
					$Job_Primary_Key++;
					$resource = $database->query(null,"
						INSERT INTO Elev(ID, Job, State, Loc, Owner, Cat, Type, Building, Manuf, Remarks, Install, InstallBy, Since, Last, Price, fGroup, fDesc, Serial, Template, Status, Week, AID)
						VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
					;",array($Job_Primary_Key, $Job['Job'], $Job['State'], $Location_ID, $_GET['ID'], 'Public', $Job['Type'], $Job['Building'], $Job['Manufacturer'], $Job['Remarks'], $Job['Install'], $Job['InstallBy'], $Job['Since'], $Job['Last'], $Job['Price'], $Job['fGroup'], $Job['fDesc'], $Job['Serial'], $Job['Template'], $Job['Status'], $Job['Week'], ""));
					if( ($errors = sqlsrv_errors() ) != null) {
						foreach( $errors as $error ) {
							echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
							echo "code: ".$error[ 'code']."<br />";
							echo "message: ".$error[ 'message']."<br />";
						}
					}
					$Job['ID'] = $Job_Primary_Key;
					print json_encode(array('data'=>$Location));
				}
			}
		}
    }
}?>