<?php
session_start();
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($My_Privileges['Ticket']['Group_Privilege'] >= 4){
        $r = sqlsrv_query(  $NEI,"SELECT LID FROM TicketO WHERE TicketO.ID='{$_GET['ID']}'");
        $r2 = sqlsrv_query( $NEI,"SELECT Loc FROM TicketD WHERE TicketD.ID='{$_GET['ID']}'");
        $r3 = sqlsrv_query( $NEI,"SELECT Loc FROM TicketDArchive WHERE TicketDArchive.ID='{$_GET['ID']}'");
        $r = sqlsrv_fetch_array($r);
        $r2 = sqlsrv_fetch_array($r2);
        $r3 = sqlsrv_fetch_array($r3);
        $Location = NULL;
        if(is_array($r)){$Location = $r['LID'];}
        elseif(is_array($r2)){$Location = $r2['Loc'];}
        elseif(is_array($r3)){$Location = $r3['Loc'];}
        if(!is_null($Location)){
            $r = sqlsrv_query(  $NEI,"SELECT ID FROM TicketO WHERE TicketO.LID='{$Location}' AND fWork='{$My_User['fWork']}'");
            $r2 = sqlsrv_query( $NEI,"SELECT ID FROM TicketD WHERE TicketD.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
            $r3 = sqlsrv_query( $NEI,"SELECT ID FROM TicketDArchive WHERE TicketDArchive.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
            if($r || $r2 || $r3){
                if($r){$a = sqlsrv_fetch_array($r);}
                if($r2){$a2 = sqlsrv_fetch_array($r2);}
                if($r3){$a3 = sqlsrv_fetch_array($r3);}
                if($a || $a2 || $a3){
                    $Privileged = true;
                }
            }
        }
        if(!$Privileged){
            if($My_Privileges['Ticket']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
                $r = sqlsrv_query(  $NEI,"SELECT ID FROM TicketO WHERE TicketO.ID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
                $r2 = sqlsrv_query( $NEI,"SELECT ID FROM TicketD WHERE TicketD.ID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
                $r3 = sqlsrv_query( $NEI,"SELECT ID FROM TicketDArchive WHERE TicketDArchive.ID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
                if($r || $r2 || $r3){
                    if($r){$a = sqlsrv_fetch_array($r);}
                    if($r2){$a2 = sqlsrv_fetch_array($r2);}
                    if($r3){$a3 = sqlsrv_fetch_array($r3);}
                    if($a || $a2 || $a3){
                        $Privileged = true;
                    }
                }
            }
        }
    }
    if(!isset($array['ID'])  || !$Privileged){?><html><head></head></html><?php }
    else {
$Ticket = null;
if(isset($_GET['ID']) && is_numeric($_GET['ID'])){
    $r = sqlsrv_query($NEI,"
            SELECT
                TicketO.*,
                Loc.Tag             AS Tag,
                Loc.Loc              AS Location_ID,
                Loc.Address         AS Address,
                Loc.City            AS City,
                Loc.State           AS State,
                Loc.Zip             AS Zip,
                Job.ID              AS Job_ID,
                Job.fDesc           AS Job_Description,
                OwnerWithRol.ID     AS Owner_ID,
                OwnerWithRol.ID     AS Customer_ID,
                OwnerWithRol.Name   AS Customer,
                JobType.Type        AS Job_Type,
                Elev.ID             AS Unit_ID,
                Elev.Unit           AS Unit_Label,
                Elev.State          AS Unit_State,
                Elev.Type           AS Unit_Type,
                Zone.Name           AS Division,
                TicketPic.PicData   AS PicData,
                TickOStatus.Type    AS Status,
                Emp.ID              AS Employee_ID,
                Emp.fFirst          AS First_Name,
                Emp.Last            AS Last_Name,
                Emp.Title           AS Role,
				TicketO.fDesc		AS Description
				FROM
                TicketO
                LEFT JOIN Loc           ON TicketO.LID      = Loc.Loc
                LEFT JOIN Job           ON TicketO.Job      = Job.ID
                LEFT JOIN OwnerWithRol  ON Loc.Owner    = OwnerWithRol.ID
                LEFT JOIN JobType       ON Job.Type         = JobType.ID
                LEFT JOIN Elev          ON TicketO.LElev    = Elev.ID
                LEFT JOIN Zone          ON Loc.Zone         = Zone.ID
                LEFT JOIN TickOStatus   ON TicketO.Assigned = TickOStatus.Ref
                LEFT JOIN Emp           ON TicketO.fWork    = Emp.fWork
                LEFT JOIN TicketPic     ON TicketO.ID       = TicketPic.TicketID
            WHERE
                TicketO.ID=?;",array($_GET['ID']));
    $Ticket = sqlsrv_fetch_array($r);
    $Ticket['Loc'] = $Ticket['LID'];
    $Ticket['Status'] = ($Ticket['Status'] == 'Completed') ? "Reviewing" : $Ticket['Status'];
    if($Ticket['ID'] == "" || $Ticket['ID'] == 0 || !isset($Ticket['ID'])){
        $r = sqlsrv_query($NEI,"
            SELECT
                TicketD.*,
                Loc.Tag             AS Tag,
                Loc.Loc              AS Location_ID,
                Loc.Address         AS Address,
                Loc.City            AS City,
                Loc.State           AS State,
                Loc.Zip             AS Zip,
                Job.ID              AS Job_ID,
                Job.fDesc           AS Job_Description,
                OwnerWithRol.ID     AS Owner_ID,
                OwnerWithRol.ID     AS Customer_ID,
                OwnerWithRol.Name   AS Customer,
                JobType.Type        AS Job_Type,
                Elev.ID             AS Unit_ID,
                Elev.Unit           AS Unit_Label,
                Elev.State          AS Unit_State,
                Elev.Type           AS Unit_Type,
                Zone.Name           AS Division,
                TicketPic.PicData   AS PicData,
                Emp.ID              AS Employee_ID,
                Emp.fFirst          AS First_Name,
                Emp.Last            AS Last_Name,
                Emp.Title           AS Role,
				'Completed'         AS Status
            FROM
                TicketD
                LEFT JOIN Loc           ON TicketD.Loc      = Loc.Loc
                LEFT JOIN Job           ON TicketD.Job      = Job.ID
                LEFT JOIN OwnerWithRol  ON Loc.Owner        = OwnerWithRol.ID
                LEFT JOIN JobType       ON Job.Type         = JobType.ID
                LEFT JOIN Elev          ON TicketD.Elev     = Elev.ID
                LEFT JOIN Zone          ON Loc.Zone         = Zone.ID
                LEFT JOIN Emp           ON TicketD.fWork    = Emp.fWork
                LEFT JOIN TicketPic     ON TicketD.ID       = TicketPic.TicketID
            WHERE
                TicketD.ID = ?;",array($_GET['ID']));
        $Ticket = sqlsrv_fetch_array($r);
    }
    if($Ticket['ID'] == "" || $Ticket['ID'] == 0 || !isset($Ticket['ID'])){
        $r = sqlsrv_query($NEI,"
            SELECT
                TicketDArchive.*,
                Loc.Tag             AS Tag,
                Loc.Loc              AS Location_ID,
                Loc.Address         AS Address,
				Loc.Loc             AS Location_Loc,
                Loc.City            AS City,
                Loc.State           AS State,
                Loc.Zip             AS Zip,
                Job.ID              AS Job_ID,
                Job.fDesc           AS Job_Description,
                OwnerWithRol.ID     AS Owner_ID,
                OwnerWithRol.ID     AS Customer_ID,
                OwnerWithRol.Name   AS Customer,
                JobType.Type        AS Job_Type,
                Elev.ID             AS Unit_ID,
                Elev.Unit           AS Unit_Label,
                Elev.State          AS Unit_State,
                Elev.Type           AS Unit_Type,
                Zone.Name           AS Division,
                TicketPic.PicData   AS PicData,
                Emp.ID              AS Employee_ID,
				Emp.ID              AS User_ID,
                Emp.fFirst          AS First_Name,
                Emp.Last            AS Last_Name,
                Emp.Title           AS Role,
				'Completed'         AS Status
            FROM
                TicketDArchive
                LEFT JOIN Loc           ON TicketDArchive.Loc = Loc.Loc
                LEFT JOIN Job           ON TicketDArchive.Job = Job.ID
                LEFT JOIN OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN JobType       ON Job.Type = JobType.ID
                LEFT JOIN Elev          ON TicketDArchive.Elev = Elev.ID
                LEFT JOIN Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN Emp           ON TicketDArchive.fWork = Emp.fWork
                LEFT JOIN TicketPic     ON TicketDArchive.ID = TicketPic.TicketID
            WHERE
                TicketDArchive.ID = ?;",array($_GET['ID']));
        $Ticket = sqlsrv_fetch_array($r);
    }
$r = sqlsrv_query($NEI,"SELECT PDATicketSignature.Signature AS Signature FROM PDATicketSignature WHERE PDATicketSignature.PDATicketID = ?",array($_GET['ID']));
if($r){while($array = sqlsrv_fetch_array($r)){$Ticket['Signature'] = $array['Signature'];}}?>
<div class="panel panel-primary">
	<div class='panel-body white-background' style='font-size:16px;padding:5px;'>
		<div class='row' style='border-bottom:3px dashed black;padding-top:10px;padding-bottom:10px;'>
			<div class='col-xs-4'><?php $Icons->User(1);?> Worker:</div>
			<div class='col-xs-8'><?php echo proper($Ticket['First_Name'] . " " . $Ticket["Last_Name"]);?></div>
			<!--<div class='col-xs-4'><?php $Icons->Calendar(1);?> Created:</div>
			<div class='col-xs-8'><?php echo date("m/d/Y h:i A",strtotime($Ticket['CDate']));?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> Dispatched:</div>
			<div class='col-xs-8'><?php echo date("m/d/Y h:i A",strtotime($Ticket['DDate']));?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> Scheduled:</div>
			<div class='col-xs-8'><?php echo date("m/d/Y h:i A",strtotime($Ticket['EDate']));?></div>-->
      <div class='col-xs-4'><?php $Icons->Description(1);?> Description:</div>
			<div class='col-xs-8'><?php echo proper($Ticket['fDesc']);?></div>
      <div class='col-xs-4'><?php $Icons->Blank(1);?> Status:</div>
      <div class='col-xs-8'><?php echo strlen($Ticket['Status']) > 0 ? $Ticket['Status'] : 'Completed';?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> On Site:</div>
			<div class='col-xs-8'><?php echo date("h:i A",strtotime($Ticket['TimeSite']));?></div>
      <div class='col-xs-4'><?php $Icons->Blank(1);?> Done:</div>
      <div class='col-xs-8'><?php echo date("h:i A",strtotime($Ticket['TimeComp']));?></div>
		</div>
		<div class='row' style='border-bottom:3px dashed black;padding-top:10px;padding-bottom:10px;'>
			<div class='col-xs-4'><?php $Icons->Customer(1);?> Customer:</div>
			<div class='col-xs-8'><?php echo proper($Ticket['Customer']);?></div>
			<div class='col-xs-4'><?php $Icons->Location(1);?> Location:</div>
			<div class='col-xs-8'><?php echo proper($Ticket['Tag']);?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> Street:</div>
			<div class='col-xs-8'><?php echo proper($Ticket['Address']);?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> City:</div>
			<div class='col-xs-8'><?php echo proper($Ticket['City']);?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> State:</div>
			<div class='col-xs-8'><?php echo $Ticket['State'];?></div>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> Zip:</div>
			<div class='col-xs-8'><?php echo $Ticket['Zip'];?></div>
		</div>
		<div class='row' style='border-bottom:3px dashed black;padding-top:10px;padding-bottom:10px;'>
			<div class='col-xs-4'><?php $Icons->Blank(1);?> Resolution:</div>
			<div class='col-xs-8'><?php echo proper($Ticket['DescRes']);?></div>
		</div>
	</div>
</div>
<?php
		} else{echo "hello world";}
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=ticket<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
