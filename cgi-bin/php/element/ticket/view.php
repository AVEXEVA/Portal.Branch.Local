<?php
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    if(!isset($_SESSION['Branch']) || $_SESSION['Branch'] == 'Nouveau Elevator'){
        sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php"));
        $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
        $My_User = sqlsrv_fetch_array($r);
        $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
        $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
        $My_Privileges = array();
        while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
        $Privileged = FALSE;
        if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
        elseif($My_Privileges['Ticket']['Group_Privilege'] >= 4){
            $r = sqlsrv_query(  $NEI,"SELECT LID FROM nei.dbo.TicketO WHERE TicketO.ID='{$_GET['ID']}'");
            $r2 = sqlsrv_query( $NEI,"SELECT Loc FROM nei.dbo.TicketD WHERE TicketD.ID='{$_GET['ID']}'");
            $r3 = sqlsrv_query( $NEI,"SELECT Loc FROM nei.dbo.TicketDArchive WHERE TicketDArchive.ID='{$_GET['ID']}'");
            $r = sqlsrv_fetch_array($r);
            $r2 = sqlsrv_fetch_array($r2);
            $r3 = sqlsrv_fetch_array($r3);
            $Location = NULL;
            if(is_array($r)){$Location = $r['LID'];}
            elseif(is_array($r2)){$Location = $r2['Loc'];}
            elseif(is_array($r3)){$Location = $r3['Loc'];}
            if(!is_null($Location)){
                $r = sqlsrv_query(  $NEI,"SELECT ID FROM nei.dbo.TicketO WHERE TicketO.LID='{$Location}' AND fWork='{$My_User['fWork']}'");
                $r2 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketD WHERE TicketD.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
                $r3 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketDArchive WHERE TicketDArchive.Loc='{$Location}' AND fWork='{$My_User['fWork']}'");
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
                    $r = sqlsrv_query(  $NEI,"SELECT ID FROM nei.dbo.TicketO WHERE TicketO.ID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
                    $r2 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketD WHERE TicketD.ID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
                    $r3 = sqlsrv_query( $NEI,"SELECT ID FROM nei.dbo.TicketDArchive WHERE TicketDArchive.ID='{$_GET['ID']}' AND fWork='{$User['fWork']}'");
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
    } elseif($_SESSION['Branch'] == 'Customer' && is_numeric($_GET['ID'])){
            $r  = sqlsrv_query( $NEI,"SELECT Loc.Loc FROM nei.dbo.TicketO        LEFT JOIN nei.dbo.Loc ON TicketO.LID        = Loc.Loc WHERE TicketO.ID=?        AND Loc.Owner = ?;",array($_GET['ID'],$_SESSION['Branch_ID']));
            $r2 = sqlsrv_query( $NEI,"SELECT Loc.Loc FROM nei.dbo.TicketD        LEFT JOIN nei.dbo.Loc ON TicketD.Loc        = Loc.Loc WHERE TicketD.ID=?        AND Loc.Owner = ?;",array($_GET['ID'],$_SESSION['Branch_ID']));
            $r3 = sqlsrv_query( $NEI,"SELECT Loc.Loc FROM nei.dbo.TicketDArchive LEFT JOIN nei.dbo.Loc ON TicketDArchive.Loc = Loc.Loc WHERE TicketDArchive.ID=? AND Loc.Owner = ?;",array($_GET['ID'],$_SESSION['Branch_ID']));
            if($r || $r2 || $r3){
                if($r){$a = sqlsrv_fetch_array($r);}else{$a = false;}
                if($r2){$a2 = sqlsrv_fetch_array($r2);}else{$a2 = false;}
                if($r3){$a3 = sqlsrv_fetch_array($r3);}else{$a3 = false;}
                if($a || $a2 || $a3){
                    $Privileged = true;
                }
            }
    }
    
    if(!isset($array['ID'],$_GET['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head></head></html><?php }
    else {
//CONNECT TO SERVER
//GET OPEN TICKETS
if(is_numeric($_GET['ID'])){
    $r = sqlsrv_query($NEI,"
            SELECT 
                TicketO.*, 
                Loc.Tag             AS Tag, 
                Loc.ID              AS Location_ID,
                Loc.Address         AS Address, 
                Loc.City            AS City, 
                Loc.State           AS State, 
                Loc.Zip             AS Zip, 
                Job.ID              AS Job_ID, 
                Job.fDesc           AS Job_Description, 
                OwnerWithRol.ID     AS Owner_ID, 
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
                Emp.Title           AS Role
            FROM
                nei.dbo.TicketO
                LEFT JOIN nei.dbo.Loc           ON TicketO.LID      = Loc.Loc
                LEFT JOIN nei.dbo.Job           ON TicketO.Job      = Job.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON TicketO.Owner    = OwnerWithRol.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type         = JobType.ID
                LEFT JOIN nei.dbo.Elev          ON TicketO.LElev    = Elev.ID
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone         = Zone.ID
                LEFT JOIN nei.dbo.TickOStatus   ON TicketO.Assigned = TickOStatus.Ref
                LEFT JOIN nei.dbo.Emp           ON TicketO.fWork    = Emp.fWork
                LEFT JOIN nei.dbo.TicketPic     ON TicketO.ID       = TicketPic.TicketID 
            WHERE
                TicketO.ID=?;",array($_GET['ID']));
    $Ticket = sqlsrv_fetch_array($r);
    while($temp = sqlsrv_fetch_array($r)){}
    $Ticket['Loc'] = $Ticket['LID'];
    $Ticket['Status'] = ($Ticket['Status'] == 'Completed') ? "Reviewing" : $Ticket['Status'];
    if($Ticket['ID'] == "" || $Ticket['ID'] == 0 || !isset($Ticket['ID'])){
        $r = sqlsrv_query($NEI,"
            SELECT
                TicketD.*, 
                Loc.Tag             AS Tag, 
                Loc.ID              AS Location_ID,
                Loc.Address         AS Address, 
                Loc.City            AS City, 
                Loc.State           AS State, 
                Loc.Zip             AS Zip, 
                Job.ID              AS Job_ID, 
                Job.fDesc           AS Job_Description, 
                OwnerWithRol.ID     AS Owner_ID, 
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
                nei.dbo.TicketD
                LEFT JOIN nei.dbo.Loc           ON TicketD.Loc      = Loc.Loc
                LEFT JOIN nei.dbo.Job           ON TicketD.Job      = Job.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner        = OwnerWithRol.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type         = JobType.ID
                LEFT JOIN nei.dbo.Elev          ON TicketD.Elev     = Elev.ID
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone         = Zone.ID
                LEFT JOIN nei.dbo.Emp           ON TicketD.fWork    = Emp.fWork
                LEFT JOIN nei.dbo.TicketPic     ON TicketD.ID       = TicketPic.TicketID
            WHERE
                TicketD.ID = ?;",array($_GET['ID']));
        $Ticket = sqlsrv_fetch_array($r);
    }
    if($Ticket['ID'] == "" || $Ticket['ID'] == 0 || !isset($Ticket['ID'])){
        $r = sqlsrv_query($NEI,"
            SELECT
                TicketDArchive.*, 
                Loc.Tag             AS Tag,
                Loc.ID              AS Location_ID, 
                Loc.Address         AS Address, 
                Loc.City            AS City, 
                Loc.State           AS State, 
                Loc.Zip             AS Zip, 
                Job.ID              AS Job_ID, 
                Job.fDesc           AS Job_Description, 
                OwnerWithRol.ID     AS Owner_ID, 
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
                nei.dbo.TicketDArchive 
                LEFT JOIN nei.dbo.Loc           ON TicketDArchive.Loc = Loc.Loc
                LEFT JOIN nei.dbo.Job           ON TicketDArchive.Job = Job.ID
                LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                LEFT JOIN nei.dbo.JobType       ON Job.Type = JobType.ID
                LEFT JOIN nei.dbo.Elev          ON TicketDArchive.Elev = Elev.ID 
                LEFT JOIN nei.dbo.Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN nei.dbo.Emp           ON TicketDArchive.fWork = Emp.fWork
                LEFT JOIN nei.dbo.TicketPic     ON TicketDArchive.ID = TicketPic.TicketID
            WHERE
                TicketDArchive.ID = ?;",array($_GET['ID']));
        $Ticket = sqlsrv_fetch_array($r);
    }
}?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>    <title>Nouveau Elevator Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
    <style type="text/css" media="print">
        .no-print {
            display:none !important;
            height:0px !important;
            margin:0px;
            padding:0px;
        }
        .print {
            display:block !important;
            page-break-before:avoid;
        }
        hr {
            margin-bottom:10px;
            margin-top:10px;
        }
        pre {
white-space: normal !important;
}
    </style>
    <style type='text/css'>
        .print {
            display:none;
        }
        .no-print {
            display:block;
        }
        pre {
            white-space: -moz-pre-wrap; /* Mozilla, supported since 1999 */
            white-space: -pre-wrap; /* Opera */
            white-space: -o-pre-wrap; /* Opera */
            white-space: pre-wrap; /* CSS3 - Text module (Candidate Recommendation) http://www.w3.org/TR/css3-text/#white-space */
            word-wrap: break-word; /* IE 5.5+ */
        }

    </style>

</head>
<body onload="finishLoadingPage();">
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'html/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content' style='<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
            <div class='no-print'>
				<div class="panel panel-primary" style='margin-bottom:0px;'>
					<div class="panel-heading">
						<div style='float:left;'>
							<h3><?php \singleton\fontawesome::getInstance( )->Ticket();?> <?php echo $Location['Tag'];  ?> Ticket #<?php echo $Ticket['ID'];?></h3>
						</div>
						<?php if(isset($My_Privileges['Admin'])){?>
						<div style='float:right;'>
							<div style='float:left;font-size:22px;border:1px solid white;background-color:#f5f5f5;color:#252525;padding:5px;box-shadow:0px 5px 5px 0px #555555;border-radius:5px;cursor:pointer;margin-left:10px;'>
								<?php \singleton\fontawesome::getInstance( )->Edit();?> Edit
							</div>
							<div onClick="document.location.href='ticket.php';" style='float:left;font-size:22px;border:1px solid white;background-color:#f5f5f5;color:#252525;padding:5px;box-shadow:0px 5px 5px 0px #555555;border-radius:5px;cursor:pointer;margin-left:10px;'>
								<?php \singleton\fontawesome::getInstance( )->Add();?> Add
							</div>
							<div style='float:left;font-size:22px;border:1px solid white;background-color:#f5f5f5;color:#252525;padding:5px;box-shadow:0px 5px 5px 0px #555555;border-radius:5px;cursor:pointer;margin-left:10px;'>
								<?php \singleton\fontawesome::getInstance( )->Delete();?> Delete
							</div>
						</div><?php }?>
						<div style='clear:both;'></div>
					</div>
					<div class="panel-body no-print">
						<div class="row">
							<div class='col-md-6' style=''>
								<div class="panel panel-primary">
									<div class="panel-heading">Basic Information</div>
									<div class='panel-body'>
										<div style='font-size:24px;text-decoration:underline;'><b>
											<div class='row'><div class='col-xs-12'><a href='ticket.php?ID=<?php echo $Ticket['ID'];?>'><?php \singleton\fontawesome::getInstance( )->Ticket();?> Ticket #<?php echo $Ticket['ID'];?></a></div></div>
											<div class='row'><div class='col-xs-12'><a href='location.php?ID=<?php echo $Ticket['Location_ID'];?>'><?php \singleton\fontawesome::getInstance( )->Location();?> <?php echo $Ticket['Tag'];?></a></div></div>
											<div class='row'><div class='col-xs-12'><a href='job.php?ID=<?php echo $Ticket['Job_ID'];?>'><?php \singleton\fontawesome::getInstance( )->Job();?> <?php echo $Ticket['Job_Description'];?></a></div></div>
											<div class='row'><div class='col-xs-12'><a href='user.php?ID=<?php echo $Ticket['User_ID'];?>'><?php \singleton\fontawesome::getInstance( )->User();?> <?php echo proper($Ticket['First_Name'] . " " . $Ticket['Last_Name']);?></a></div></div>
										</b></div>
									</div>
								</div>
								<div class='row'>
									<div class='col-md-12' style=''>
										<div class="panel panel-primary">
											<div class="panel-heading">Ticket Information</div>
											<div class='panel-body'>
												<div class='row'>
													<div class='col-xs-4'><b>Total Hours</b></div>
													<div class='col-xs-8'><?php
													if(isset($Ticket['Total']) && strlen($Ticket['Total']) > 0){
														echo $Ticket['Total'];
													} else {
														if($Ticket['Status'] != 'Assigned'){
															if($Ticket['TimeRoute'] == "1899-12-30 00:00:00.000" || $Ticket['TimeRoute'] == ""){ $Start_Time = intval(substr($Ticket['TimeSite'],11,2)) + (intval(substr($Ticket['TimeSite'],14,2)) / 60); } 
															else { $Start_Time = intval(substr($Ticket['TimeRoute'],11,2)) + (intval(substr($Ticket['TimeRoute'],14,2)) / 60); }
															if($Ticket['TimeComp'] ==  "" || $Ticket['TimeComp'] == "1899-12-30 00:00:00.000"){$End_Time=intval(substr(date("Y-m-d H:i:s", strtotime('+0 hours')),11,2)) + (intval(substr(date("Y-m-d H:i:s", strtotime('+3 hours')),14,2)) / 60);}
															else {$End_Time = intval(substr($Ticket['TimeComp'],11,2)) + (intval(substr($Ticket['TimeComp'],14,2)) / 60);}

															echo $End_Time - $Start_Time;?> hours<?php
														} else {
															echo "Unlisted";
														}
													}?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Status:</b></div>
													<div class='col-xs-8'><?php echo strlen($Ticket["Status"]) > 1 ? $Ticket["Status"] : "Unlisted";?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Description:</b></div>
													<div class='col-xs-8'><pre style='max-height:300px;overflow:scroll;'><?php echo strlen($Ticket['fDesc']) > 1 ? $Ticket["fDesc"] : "Unlisted";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Resolution:</b></div>
													<div class='col-xs-8'><pre style='max-height:300px;overflow:scroll;'><?php echo strlen($Ticket['DescRes']) > 1 ? $Ticket['DescRes'] : "Unlisted";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Zone Expenses:</b></div>
													<div class='col-xs-8'>$<?php echo strlen($Ticket['Zone']) > 0 ? $Ticket['Zone'] : "0.00";?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Other Expenses:</b></div>
													<div class='col-xs-8'>$<?php echo strlen($Ticket['OtherE']) > 1 ? $Ticket['OtherE'] : "0.00";?></div>
												</div>
											</div>
										</div>
									</div>
									
								</div>
							</div>	
							<div class='col-md-6'>
								<div class='row' >
									<div class='col-md-6' >
										<div class="panel panel-primary">
											<div class="panel-heading">
												<i class="fa fa-map fa-fw"></i> Location Details
											</div>
											<div class="panel-body">
												<div class='row'>
													<div class='col-xs-4'><b>Customer:</b></div>
													<div class='col-xs-8'><?php if(!$Field){?><a href="<?php echo (strlen($Ticket['Owner_ID']) > 0) ? 'customer.php?ID=' . $Ticket['Owner_ID'] : '#';?>"><?php echo (strlen($Ticket['Customer']) > 0) ? $Ticket["Customer"] : 'Unlisted';?></a><?php } else {?><?php echo (strlen($Ticket['Customer']) > 0) ? $Ticket["Customer"] : 'Unlisted';?><?php }?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Name:</b></div>
													<div class='col-xs-8'><a href="<?php echo (strlen($Ticket['Loc']) > 0) ? 'location.php?ID=' . $Ticket['Loc'] : '#';?>"><?php echo (strlen($Ticket['Tag']) > 0) ? $Ticket["Tag"] : 'Unlisted';?></a></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Street:</b></div>
													<div class='col-xs-8'><?php echo (strlen($Ticket['Address']) > 0) ? proper($Ticket["Address"]) : 'Unlisted';?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>City:</b></div>
													<div class='col-xs-8'><?php echo (strlen($Ticket['City']) > 0) ? proper($Ticket["City"]) : 'Unlisted';?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>State:</b></div>
													<div class='col-xs-8'><?php echo (strlen($Ticket['State']) > 0) ? $Ticket["State"] : 'Unlisted';?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Zip:</b></div>
													<div class='col-xs-8'><?php echo (strlen($Ticket['Zip']) > 0) ? $Ticket["Zip"] : 'Unlisted';?></div>

												</div>
											</div>
										</div>
									</div>
									<div class='col-md-6' >
										<div class="panel panel-primary">
											<div class="panel-heading"><i class="fa fa-map fa-fw"></i> Map</div>
											<div class="panel-body">
												<style>#map {height:100%;}</style>
                               					<div id="map" style='height:300px;overflow:visible;width:100%;'></div>
											</div>
										</div>
									</div>
									<div class='col-md-6' style=''>
										<div class="panel panel-primary">
											<div class="panel-heading">Job Information</div>
											<div class='panel-body'>
													<div class='row'>
														<div class='col-xs-4'><b>Job:</b></div>
														<div class='col-xs-8'><a href="<?php echo (strlen($Ticket['Job_ID']) > 0) ? 'job.php?ID=' . $Ticket['Job_ID'] : '#';?>"><?php echo strlen($Ticket['Job_ID']) ? $Ticket['Job_ID'] : "Unlisted";?></a></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>Type:</b></div>
														<div class='col-xs-8'><?php echo strlen($Ticket["Job_Type"]) ? proper($Ticket['Job_Type']) : "Unlisted";?></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>Division:</b></div>
														<div class='col-xs-8'><?php echo strlen($Ticket['Division']) > 0 ? proper($Ticket["Division"]) : "Unlisted";?></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>Description:</b></div>
														<div class='col-xs-8'><?php echo strlen($Ticket['Job_Description']) > 0 ? $Ticket['Job_Description'] : "Unlisted";?></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>Unit:</b></div>
														<div class='col-xs-8'><a href="<?php echo (strlen($Ticket['Unit_ID']) > 0) ? 'unit.php?ID=' . $Ticket['Unit_ID'] : '#';?>"><?php echo strlen($Ticket["Unit_Label"]) > 0 ? $Ticket['Unit_Label'] : "Unlisted";?></a></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>State:</b></div>
														<div class='col-xs-8'><a href="<?php echo (strlen($Ticket['Unit_ID']) > 0) ? 'unit.php?ID=' . $Ticket['Unit_ID'] : '#';?>"><?php echo strlen($Ticket["Unit_State"]) > 0 ? $Ticket['Unit_State'] : "Unlisted";?></a></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>Type:</b></div>
														<div class='col-xs-8'><a href="<?php echo (strlen($Ticket['Unit_ID']) > 0) ? 'unit.php?ID=' . $Ticket['Unit_ID'] : '#';?>"><?php echo strlen($Ticket["Unit_Type"]) > 0 ? proper($Ticket['Unit_Type']) : "Unlisted";?></a></div>
													</div>
											</div>
										</div>
									</div>
									<div class='col-md-6' style=''>
										<div class="panel panel-primary">
											<div class="panel-heading">Clock Information</div>
											<div class='panel-body'>
												<div class='row'>
													<div class='col-xs-4'><b>Creation:</b></div>
													<div class='col-xs-8'><?php echo strlen($Ticket["CDate"]) > 0 ? date("m/d/Y H:i:s", strtotime($Ticket["CDate"])) : "Unlisted";?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Dispatched:</b></div>
													<div class='col-xs-8'><?php echo strlen($Ticket["DDate"]) > 0 ? date("m/d/Y H:i:s", strtotime($Ticket['DDate'])) : "Unlisted";?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Worked:</b></div>
													<div class='col-xs-8'><?php echo strlen($Ticket["EDate"]) > 0 ? date("m/d/Y H:i:s", strtotime($Ticket['EDate'])) : "Unlisted";?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>En Route:</b></div>
													<div class='col-xs-8'><?php echo strlen($Ticket["TimeRoute"]) > 0 ? substr($Ticket['TimeRoute'],11,8) : "Unlisted";?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>On Site:</b></div>
													<div class='col-xs-8'><?php echo strlen($Ticket["TimeSite"]) > 0 ? substr($Ticket['TimeSite'],11,8) : "Unlisted";?></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Completed:</b></div>
													<div class='col-xs-8'><?php echo strlen($Ticket["TimeComp"]) > 0 ? substr($Ticket['TimeComp'],11,8) : "Unlisted";?></div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class='row'>
							<div class='col-lg-12'>
								<div class='panel panel-primary'>
									<div class='panel-heading'>
										<i class='fa fa-map fa-fw'></i> GPS Details
									</div>
									<div class='panel-body'>
										<?php
											$r = sqlsrv_query($NEI,"
												SELECT TOP 100 TechLocation.* 
												FROM TechLocation
												WHERE TicketID = '" . $Ticket['ID'] . "';");
											$GPS_Locations = array();
											while($array = sqlsrv_fetch_array($r)){$GPS_Locations[] = $array;}
											foreach($GPS_Locations as $GPS_Location){?>
												<div class='row'>
													<div class='col-xs-2'>Action Group:</div>
													<div class='col-xs-8'><?php echo $GPS_Location['ActionGroup'];?></div>
												</div>
												<div class='row'>
													<div class='col-xs-2'>Timestamp:</div>
													<div class='col-xs-8'><?php echo $GPS_Location['DateTimeRecorded'];?></div>
												</div>
												<div class='row'>
													<div class='col-xs-2'>Latitude:</div>
													<div class='col-xs-8'><?php echo substr($GPS_Location['Latitude'],0,7);?></div>
												</div>
												<div class='row'>
													<div class='col-xs-2'>Longitude:</div>
													<div class='col-xs-8'><?php echo substr($GPS_Location['Longitude'],0,8);?></div>
												</div>
												<div class='row'>
													<div class='col-xs-2'>Accuracy:</div>
													<div class='col-xs-8'><?php echo $GPS_Location['Accuracy'];?></div>
												</div>
												<div class='row'>
													<div class='col-xs-2'>Action:</div>
													<div class='col-xs-8'><?php echo $GPS_Location['Action'];?></div>
												</div>
											<?php }?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
            </div>
            <div class='print'>
                <div class='row' style='text-align:center;'>
                    <div><b>Nouveau Elevator Industries Inc.</b></div>
                    <div>47-55 37th Street</div>
                    <div>Tel:(718) 349-4700 | Fax:(718)383-3218</div>
                    <div>Email:Operations@NouveauElevator.com</div>
                </div>
                <hr />
                <h3 style='text-align:center;'><b><?php echo $Ticket['Status'];?> Service Ticket #<?php echo $_GET['ID'];?></b></h3>
                <hr />
                <div class='row'>
                    <div class='col-xs-2' style='text-align:right;'><b>Customer</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Customer'];?></div>
                </div>
                <div class='row'>
                    <!--<div class='col-xs-2' style='text-align:right;'><b>ID#</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Location_ID'];?></div>-->
                    <div class='col-xs-2' style='text-align:right;'><b>Location</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Tag'];?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Job</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Job_Description'];?></div>
                </div>
                <div class='row'>
                    <div class='col-xs-2'>&nbsp;</div>
                    <div class='col-xs-2'><?php echo $Ticket['Address'];?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Unit ID</b></div>
                    <div class='col-xs-2'><?php echo strlen($Ticket['Unit_State'] > 0) ? $Ticket['Unit_State'] : $Ticket['Unit_Label'];?></div>
                </div>
                <div class='row'>
                    <div class='col-xs-2'>&nbsp;</div>
                    <div class='col-xs-2'><?php echo $Ticket['City'];?>, <?php echo $Ticket['State'];?> <?php echo $Ticket['Zip'];?></div>
                </div>
                
                <div class='row'>
                    <div class='col-xs-2' style='text-align:right;'><b>Customer Signature</b></div>
                    <div class='col-xs-2'></div>
                </div>
                <hr />
                <div class='row'>
                    <div class='col-xs-2' style='text-align:right;'><b>Serviced</b></div>
                    <div class='col-xs-2'><?php echo substr($Ticket['EDate'],0,10);?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Regular</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Reg'] == '' ? '0.00' : $Ticket['Reg'];?> hrs</div>
                    <div class='col-xs-2' style='text-align:right;'><b>Worker</b></div>
                    <div class='col-xs-2'><?php echo strlen($Ticket['First_Name']) > 0 ? proper($Ticket["First_Name"] . " " . $Ticket['Last_Name']) : "None";;?></div>
                </div>
                <div class='row'>
                    <div class='col-xs-2' style='text-align:right;'><b>Dispatched</b></div>
                    <div class='col-xs-2'><?php echo substr($Ticket['TimeRoute'],11,99);?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>O.T.</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['OT'] == '' ? '0.00' : $Ticket['OT']?> hrs</div>
                    <div class='col-xs-2' style='text-align:right;'><b>Role</b></div>
                    <div class='col-xs-2'><?php echo proper($Ticket['Role']);?></div>
                </div>
                <div class='row'>
                    <div class='col-xs-2' style='text-align:right;'><b>On Site</b></div>
                    <div class='col-xs-2'><?php echo substr($Ticket['TimeSite'],11,99);?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>D.T.</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['DT'] == '' ? '0.00' : $Ticket['DT'];?> hrs</div>
                </div>
                <div class='row'>
                    <div class='col-xs-2' style='text-align:right;'><b>Completed</b></div>
                    <div class='col-xs-2'><?php echo substr($Ticket['TimeComp'],11,99);?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Total</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Total'] == '' ? '0.00' : $Ticket['Total'];?> hrs</div>
                </div>
                <hr />
                <div class='row'>
                    <div class='col-xs-2' style='text-align:right;'><b>Scope of Work</b></div>
                    <div class='col-xs-10'><pre><?php echo $Ticket['fDesc'];?></pre></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Resolution of Work</b></div>
                    <div class='col-xs-10'><pre><?php echo $Ticket['DescRes'];?></pre></div>
                </div>
            </div>
        </div>
        <!-- /#page-wrapper -->

    </div>
    

    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <!-- Morris Charts JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/raphael/raphael.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.php"></script>

    <!-- Custom Theme JavaScript -->
    

    <!-- JQUERY UI Javascript -->
    
    
    <script type="text/javascript">
  function initialize() {
    var latlng = new google.maps.LatLng(40.7831, -73.9712);
    var myOptions = {
      zoom: 10,
      center: latlng,
      mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map"),
        myOptions);
    var marker = new Array();
<?php 
$r = sqlsrv_query($NEI,"
    SELECT TOP 100 TechLocation.* 
    FROM TechLocation
    WHERE TicketID = '" . $Ticket['ID'] . "';");
$GPS_Locations = array();
while($array = sqlsrv_fetch_array($r)){$GPS_Locations[] = $array;}
foreach($GPS_Locations as $key=>$GPS_Location){?>
    marker[<?php echo $key;?>] = new google.maps.Marker({
      position: {lat:<?php echo substr($GPS_Location['Latitude'],0,7);?>,lng:<?php echo substr($GPS_Location['Longitude'],0,8);?>},
      map: map,
      title: '<?php echo $GPS_Location['ActionGroup'];?>'
    });
  <?php }?>}</script>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=ticket<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>