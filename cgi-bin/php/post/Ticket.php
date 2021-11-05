<?php 
session_start( [ 'read_and_close' => true ] );
require('../get/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $Privileged = FALSE;
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        $r = sqlsrv_query($NEI,"SELECT * FROM Emp WHERE ID = ?",array($_GET['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 6 && $My_Privileges['Unit']['Group_Privilege'] >= 6 && $My_Privileges['Unit']['Other_Privilege'] >= 6){$Privileged = TRUE;}
    }
    if(!$Privileged || count($_POST) == 0){?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
    else {
		if(isset($_POST['action']) && $_POST['action'] == 'edit'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				$data = array();
				foreach($_POST['data'] as $ID=>$Ticket){
					$data[] = $Ticket;
					continue;
					//GET Location ID
					$resource = sqlsrv_query($NEI,"
						SELECT Loc.Loc AS Location_ID
						FROM   nei.dbo.Loc
						WHERE  Loc.Tag = ?
					;",array($Ticket['Location']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
					if($resource && sqlsrv_num_rows( $resource ) > 0){$Location_ID = sqlsrv_fetch_array($resource)['Location_ID'];}
					else {continue;}
					//GET Unit ID
					$resource = sqlsrv_query($NEI,"
						SELECT Elev.ID AS Unit_ID
						FROM   nei.dbo.Elev
						WHERE  Unit.State = ?
					;",array($Ticket['Unit_State']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
					if($resource && sqlsrv_num_rows( $resource ) > 0){$Unit_ID = sqlsrv_fetch_array($resource)['Unit_ID'];}
					else {$Unit_ID = NULL;}
					//Check if TicketO
					$resource = sqlsrv_query($NEI,"
						SELECT TicketO.ID AS ID
						FROM   nei.dbo.TicketO
						WHERE  TicketO.ID = ?
					;",array($ID),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
					//What to do if TicketO
					if($resource && sqlsrv_num_rows( $resource ) > 0){
						$resource = sqlsrv_query($NEI,"
							SELECT TickOStatus.Ref AS Status_ID
							FROM   nei.dbo.TickOStatus
							WHERE  TickOStatus.Type = ?
						;",array($Ticket['Status']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
						if($resource && sqlsrv_num_rows( $resource ) > 0){$Status_ID = sqlsrv_fetch_array($resource)['Status_ID'];}
						else {continue;}
						sqlsrv_query($NEI,"
							UPDATE nei.dbo.TicketO
							SET    TicketO.EDate  = ?,
							       TicketO.Assigned = ?,
								   TicketO.LID    = ?,
								   TicketO.fDesc = ?
							WHERE  TicketO.ID = ?
								   
						;",array($Ticket['Worked'],$Status_ID,$Location_ID,$Ticket['Description'],$ID));
						$data[] = $Ticket;
					} else {
						
					}
				}
				print json_encode(array('data'=>$data));
			}
		} elseif(isset($_POST['action']) && $_POST['action'] == 'create'){
			if(isset($_POST['data']) && count($_POST['data']) > 0){
				foreach($_POST['data'] as $ID=>$Ticket){
					$resource = sqlsrv_query($NEI,"
						SELECT Loc.Loc AS Location_ID
						FROM   nei.dbo.Loc
						WHERE  Loc.Tag = ?
					;",array($Ticket['Location']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
					if($resource && sqlsrv_num_rows( $resource ) > 0){$Location_ID = sqlsrv_fetch_array($resource)['Location_ID'];}
					else {continue;}
					//GET Unit ID
					$resource = sqlsrv_query($NEI,"
						SELECT Elev.ID AS Unit_ID
						FROM   nei.dbo.Elev
						WHERE  Unit.State = ?
					;",array($Ticket['Unit_State']),array("Scrollable"=>SQLSRV_CURSOR_KEYSET));
					if($resource && sqlsrv_num_rows( $resource ) > 0){$Unit_ID = sqlsrv_fetch_array($resource)['Unit_ID'];}
					else {continue;}
					$Creation_Date = date("Y-m-d H:i:s");
				}
			}
		}
    }
}?>