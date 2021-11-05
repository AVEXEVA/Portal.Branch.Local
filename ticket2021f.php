<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "ticket.php?ID={$_GET['ID']}"));
    $r = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($NEI,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($My_Privileges['Ticket']['User_Privilege'] >= 6 && !isset($_GET['ID'])){$Privileged = TRUE;}
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
                Loc.Loc             AS Location_ID,
                Loc.Address         AS Address,
                Loc.City            AS City,
                Loc.State           AS State,
                Loc.Zip             AS Zip,
                Loc.Latt            AS Lattitude,
                Loc.fLong           AS Longitude,
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
                Emp.Title           AS Role
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
                Loc.Latt            AS Lattitude,
                Loc.fLong           AS Longitude,
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
                Loc.Latt            AS Lattitude,
                Loc.fLong           AS Longitude,
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
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Nouveau Texas Portal</title>
    <?php require( bin_meta . 'index.php');?>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
    <script src="cgi-bin/js/slider-noui.js"></script>
    <link href='cgi-bin/css/slider-noui.css' rel='stylesheet' />
    <style type="text/css" media="print">
        .no-print {
            display:block !important;
            height:0px !important;
            margin:0px;
            padding:0px;
        }
        .print {
            display:none !important;
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
            display:block;
        }
        .no-print {
            display:none;
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
<body onload="finishLoadingPage();" style='background-color:#2d2d2d !important;color:white !important;/*overscroll-behavior: contain;*/'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>;">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content' style='overflow-x:hidden;<?php if(isset($_SESSION['Branch']) && $_SESSION['Branch'] == 'Customer'){?>margin:0px !important;<?php }?>'>
            <?php if(!isMobile() && false){?>
            <div class='print'>
				<div class="panel panel-primary" style='margin-bottom:0px;'>
					<div class="panel-heading">
						<div style='float:left;'>
							<h3><?php $Icons->Ticket();?> <?php echo $Location['Tag'];  ?> Ticket #<?php echo $Ticket['ID'];?></h3>
						</div>
						<div style='clear:both;'></div>
					</div>
					<div class="panel-body print" style='background-color:rgba(255,255,255,.9) !important;'>
						<div class="row">
							<div class='col-md-6' style=''>
								<div class="panel panel-primary">
									<div class="panel-heading">Basic Information</div>
									<div class='panel-body'>
										<div style='font-size:24px;text-decoration:underline;'><b>
											<?php /*Need to make one big row and multiple cols*/?>
											<div class='row'><div class='col-xs-12'><a href='ticket.php?ID=<?php echo $Ticket['ID'];?>'><?php $Icons->Ticket();?> Ticket #<?php echo $Ticket['ID'];?></a></div></div>
											<div class='row'><div class='col-xs-12'><a href='location.php?ID=<?php echo $Ticket['Location_ID'];?>'><?php $Icons->Location();?> <?php echo $Ticket['Tag'];?></a></div></div>
											<div class='row'><div class='col-xs-12'><a href='job.php?ID=<?php echo $Ticket['Job_ID'];?>'><?php $Icons->Job();?> <?php echo $Ticket['Job_Description'];?></a></div></div>
											<div class='row'><div class='col-xs-12'><?php $Icons->User();?> <?php echo proper($Ticket['First_Name'] . " " . $Ticket['Last_Name']);?></div></div>
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
													<div class='col-xs-8'><pre><?php
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
													}?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Status:</b></div>
													<div class='col-xs-8'><pre><?php echo strlen($Ticket["Status"]) > 1 ? $Ticket["Status"] : "None";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Description:</b></div>
													<div class='col-xs-8'><pre style='max-height:300px;overflow:scroll;'><?php echo strlen($Ticket['fDesc']) > 1 ? $Ticket["fDesc"] : "None";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Resolution:</b></div>
													<div class='col-xs-8'><pre style='max-height:300px;overflow:scroll;'><?php echo strlen($Ticket['DescRes']) > 1 ? $Ticket['DescRes'] : "None";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Internal Comments:</b></div>
													<div class='col-xs-8'><pre style='max-height:300px;overflow:scroll;'><?php echo strlen($Ticket['Comments']) > 1 ? $Ticket['Comments'] : "None";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Zone Expenses:</b></div>
													<div class='col-xs-8'><pre><?php echo strlen($Ticket['Zone']) > 0 ? $Ticket['Zone'] : "0.00";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Other Expenses:</b></div>
													<div class='col-xs-8'><pre>$<?php echo strlen($Ticket['OtherE']) > 1 ? $Ticket['OtherE'] : "0.00";?></pre></div>
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
													<div class='col-xs-8'><?php if(!$Field){?><a href="<?php echo (strlen($Ticket['Owner_ID']) > 0) ? 'customer.php?ID=' . $Ticket['Owner_ID'] : '#';?>"><pre><?php echo (strlen($Ticket['Customer']) > 0) ? $Ticket["Customer"] : 'Unlisted';?></pre></a><?php } else {?><pre><?php echo (strlen($Ticket['Customer']) > 0) ? $Ticket["Customer"] : 'Unlisted';?><?php }?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Name:</b></div>
													<div class='col-xs-8'><a href="<?php echo (strlen($Ticket['Loc']) > 0) ? 'location.php?ID=' . $Ticket['Loc'] : '#';?>"><pre><?php echo (strlen($Ticket['Tag']) > 0) ? $Ticket["Tag"] : 'Unlisted';?></pre></a></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Street:</b></div>
													<div class='col-xs-8'><pre><?php echo (strlen($Ticket['Address']) > 0) ? proper($Ticket["Address"]) : 'Unlisted';?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>City:</b></div>
													<div class='col-xs-8'><pre><?php echo (strlen($Ticket['City']) > 0) ? proper($Ticket["City"]) : 'Unlisted';?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>State:</b></div>
													<div class='col-xs-8'><pre><?php echo (strlen($Ticket['State']) > 0) ? $Ticket["State"] : 'Unlisted';?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Zip:</b></div>
													<div class='col-xs-8'><pre><?php echo (strlen($Ticket['Zip']) > 0) ? $Ticket["Zip"] : 'Unlisted';?></pre></div>

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
														<div class='col-xs-8'><a href="<?php echo (strlen($Ticket['Job_ID']) > 0) ? 'job.php?ID=' . $Ticket['Job_ID'] : '#';?>"><pre><?php echo strlen($Ticket['Job_ID']) ? $Ticket['Job_ID'] : "Unlisted";?></pre></a></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>Type:</b></div>
														<div class='col-xs-8'><pre><?php echo strlen($Ticket["Job_Type"]) ? proper($Ticket['Job_Type']) : "Unlisted";?></pre></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>Division:</b></div>
														<div class='col-xs-8'><pre><?php echo strlen($Ticket['Division']) > 0 ? proper($Ticket["Division"]) : "Unlisted";?></pre></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>Description:</b></div>
														<div class='col-xs-8'><pre><?php echo strlen($Ticket['Job_Description']) > 0 ? $Ticket['Job_Description'] : "Unlisted";?></pre></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>Unit:</b></div>
														<div class='col-xs-8'><a href="<?php echo (strlen($Ticket['Unit_ID']) > 0) ? 'unit.php?ID=' . $Ticket['Unit_ID'] : '#';?>"><pre><?php echo strlen($Ticket["Unit_Label"]) > 0 ? $Ticket['Unit_Label'] : "Unlisted";?></pre></a></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>State:</b></div>
														<div class='col-xs-8'><a href="<?php echo (strlen($Ticket['Unit_ID']) > 0) ? 'unit.php?ID=' . $Ticket['Unit_ID'] : '#';?>"><pre><?php echo strlen($Ticket["Unit_State"]) > 0 ? $Ticket['Unit_State'] : "Unlisted";?></pre></a></div>
													</div>
													<div class='row'>
														<div class='col-xs-4'><b>Type:</b></div>
														<div class='col-xs-8'><a href="<?php echo (strlen($Ticket['Unit_ID']) > 0) ? 'unit.php?ID=' . $Ticket['Unit_ID'] : '#';?>"><pre><?php echo strlen($Ticket["Unit_Type"]) > 0 ? proper($Ticket['Unit_Type']) : "Unlisted";?></pre></a></div>
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
													<div class='col-xs-8'><pre><?php echo strlen($Ticket["CDate"]) > 0 ? date("m/d/Y h:i:s a", strtotime($Ticket["CDate"])) : "Unlisted";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Dispatched:</b></div>
													<div class='col-xs-8'><pre><?php echo strlen($Ticket["DDate"]) > 0 ? date("m/d/Y h:i:s a", strtotime($Ticket['DDate'])) : "Unlisted";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Worked:</b></div>
													<div class='col-xs-8'><pre><?php echo strlen($Ticket["EDate"]) > 0 ? date("m/d/Y h:i:s a", strtotime($Ticket['EDate'])) : "Unlisted";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>En Route:</b></div>
													<div class='col-xs-8'><pre><?php echo strlen($Ticket["TimeRoute"]) > 0 && date("h:i:s a",strtotime(substr($Ticket['TimeRoute'],11,8))) != '12:00:00 am' ? date("h:i:s a",strtotime(substr($Ticket['TimeRoute'],11,8))) : "None";?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>On Site:</b></div>
													<div class='col-xs-8'><pre><?php
                            if(strlen($Ticket['TimeSite']) > 0){
                              if(date("h:i:s a",strtotime($Ticket['TimeSite'])) == '12:00 AM'){?><button onClick='post_on_site();'>Work Accepted</button><?php }
                              else {echo date("h:i:s a",strtotime($Ticket['TimeSite']));}
                            } else {
                              echo 'N/A';
                            }
                            //echo strlen($Ticket["TimeSite"]) > 0 ? date("h:i:s a",strtotime(substr($Ticket['TimeSite'],11,8))) : "Unlisted";
                          ?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-4'><b>Completed:</b></div>
													<div class='col-xs-8'><pre><?php echo strlen($Ticket["TimeComp"]) > 0 ? date("h:i:s a",strtotime(substr($Ticket['TimeComp'],11,8))) : "Unlisted";?></pre></div>
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
									<div class='panel-body' style='height:200px;overflow-y:scroll;'>
										<?php
											$r = sqlsrv_query($NEI,"
												SELECT TOP 100 TechLocation.*
												FROM TechLocation
												WHERE TicketID = '" . $Ticket['ID'] . "'
												ORDER BY TechLocation.DateTimeRecorded ASC;");
											$GPS_Locations = array();
											while($array = sqlsrv_fetch_array($r)){$GPS_Locations[] = $array;}
											foreach($GPS_Locations as $GPS_Location){?>
												<h4 style='background-color:#9e9e9e !important;;color:blackgin:0px;padding:5px;'><?php echo $GPS_Location['ActionGroup'];?></h4>
												<div class='row'>
													<div class='col-xs-2'>Timestamp:</div>
													<div class='col-xs-8'><pre><?php echo $GPS_Location['DateTimeRecorded'];?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-2'>Latitude:</div>
													<div class='col-xs-8'><pre><?php echo substr($GPS_Location['Latitude'],0,7);?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-2'>Longitude:</div>
													<div class='col-xs-8'><pre><?php echo substr($GPS_Location['Longitude'],0,8);?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-2'>Accuracy:</div>
													<div class='col-xs-8'><pre><?php echo $GPS_Location['Accuracy'];?></pre></div>
												</div>
												<div class='row'>
													<div class='col-xs-2'>Action:</div>
													<div class='col-xs-8'><pre><?php echo $GPS_Location['Action'];?></pre></div>
												</div>
											<?php }?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
            </div>
            <div class='no-print'>
                <div class='row shadower' style='text-align:center;'>
                    <div><b>Nouveau Elevator Industries Inc.</b></div>
                    <div>47-55 37th Street</div>
                    <div>Tel:(718) 349-4700 | Fax:(718)383-3218</div>
                    <div>Email:Operations@NouveauElevator.com</div>
                </div>
                <hr />
                <h3 style='text-align:center;'><b><?php echo $Ticket['Status'];?> Service Ticket #<?php echo $_GET['ID'];?></b></h3>
                <hr />
                <div class='row shadower'>
                    <div class='col-xs-2' style='text-align:right;'><b>Customer</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Customer'];?></div>
                </div>
                <div class='row shadwer'>
                    <!--<div class='col-xs-2' style='text-align:right;'><b>ID#</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Location_ID'];?></div>-->
                    <div class='col-xs-2' style='text-align:right;'><b>Location</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Tag'];?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Job</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Job_Description'];?></div>
                </div>
                <div class='row shadower'>
                    <div class='col-xs-2'>&nbsp;</div>
                    <div class='col-xs-2'><?php echo $Ticket['Address'];?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Unit ID</b></div>
                    <div class='col-xs-2'><?php echo strlen($Ticket['Unit_State'] > 0) ? $Ticket['Unit_State'] : $Ticket['Unit_Label'];?></div>
                </div>
                <div class='row shadower'>
                    <div class='col-xs-2'>&nbsp;</div>
                    <div class='col-xs-2'><?php echo $Ticket['City'];?>, <?php echo $Ticket['State'];?> <?php echo $Ticket['Zip'];?></div>
                </div>

                <div class='row shadower'>
                    <div class='col-xs-2' style='text-align:right;'><b>Customer Signature</b></div>
                    <div class='col-xs-2'><img id='Ticket_Signature' width='100%' src='data:image/jpeg;base64,<?php echo base64_encode($Ticket['Signature']);?>' /></div>
					<script>
					$(document).ready(function(){
						//$("img#Ticket_Signature").src = 'data:image/bmp;base64,' + "";
					});
					</script>
                </div>
                <hr />
                <div class='row shadower'>
                    <div class='col-xs-2' style='text-align:right;'><b>Serviced</b></div>
                    <div class='col-xs-2'><?php echo substr($Ticket['EDate'],0,10);?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Regular</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Reg'] == '' ? '0.00' : $Ticket['Reg'];?> hrs</div>
                    <div class='col-xs-2' style='text-align:right;'><b>Worker</b></div>
                    <div class='col-xs-2'><?php echo strlen($Ticket['First_Name']) > 0 ? proper($Ticket["First_Name"] . " " . $Ticket['Last_Name']) : "None";;?></div>
                </div>
                <div class='row shadower'>
                    <div class='col-xs-2' style='text-align:right;'><b>Dispatched</b></div>
                    <div class='col-xs-2'><?php echo substr($Ticket['TimeRoute'],11,99);?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>O.T.</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['OT'] == '' ? '0.00' : $Ticket['OT']?> hrs</div>
                    <div class='col-xs-2' style='text-align:right;'><b>Role</b></div>
                    <div class='col-xs-2'><?php echo proper($Ticket['Role']);?></div>
                </div>
                <div class='row shadower'>
                    <div class='col-xs-2' style='text-align:right;'><b>On Site</b></div>
                    <div class='col-xs-2'><?php echo substr($Ticket['TimeSite'],11,99);?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>D.T.</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['DT'] == '' ? '0.00' : $Ticket['DT'];?> hrs</div>
                </div>
                <div class='row shaodwer'>
                    <div class='col-xs-2' style='text-align:right;'><b>Completed</b></div>
                    <div class='col-xs-2'><?php echo substr($Ticket['TimeComp'],11,99);?></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Total</b></div>
                    <div class='col-xs-2'><?php echo $Ticket['Total'] == '' ? '0.00' : $Ticket['Total'];?> hrs</div>
                </div>
                <hr />
                <div class='row shadower'>
                    <div class='col-xs-2' style='text-align:right;'><b>Scope of Work</b></div>
                    <div class='col-xs-10'><pre><?php echo $Ticket['fDesc'];?></pre></div>
                    <div class='col-xs-2' style='text-align:right;'><b>Resolution of Work</b></div>
                    <div class='col-xs-10'><pre><?php echo $Ticket['DescRes'];?></pre></div>
                </div>
            </div>
			<?php } else {?>
                    <h4 style='margin:0px;padding:10px;<?php if($Ticket['Assigned'] >= 2 && $Ticket['Assigned'] <= 3){?>background-color:gold;<?php } else {?>background-color:whitesmoke;<?php }?>border-bottom:1px solid darkgray;'><a href='work.php?ID=<?php echo $_GET['ID'];?>'><?php $Icons->Ticket();?> Ticket: <?php echo $_GET['ID'];?> - <?php echo $Ticket['Tag'];?> - <?php echo $Ticket['Unit_State'];?></a></h4>
                    <style>
                    .nav-text{
                        font-weight: bold;
                        text-align: center;
                    }
                    .nav-icon{
                        text-align: center;
                    }
                </style>
                <style>
                    * { margin: 0 }

                    .Screen-Tabs { overflow-x: hidden }

                    .Screen-Tabs>div {
                        --n: 1;
                        display: flex;
                        align-items: center;
                        overflow-y: hidden;
                        width: 100%; // fallback
                        width: calc(var(--n)*100%);
                        /*height: 50vw;*/ max-height: 100vh;
                        transform: translate(calc(var(--tx, 0px) + var(--i, 0)/var(--n)*-100%));

                        div {
                            /*width: 100%; // fallback
                            width: calc(100%/var(--n));*/
                            user-select: none;
                            pointer-events: none
                        }

                    }

                    .smooth { transition: transform  calc(var(--f, 1)*.5s) ease-out }
                    div.Home-Screen-Option {
                        background-color:#3d3d3d !important;
                        color:white !important;
                    }
                    div.Home-Screen-Option.active {
                        background-color:#1d1d1d !important;
                        color:white !important;
                    }
                    .Screen-Tabs {
                        border-bottom:3px solid black;
                    }
                </style>
            <div class='Screen-Tabs shadower'>
                <div class='row'>
                    <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'ticket-information2021f.php?ID=<?php echo $_GET['ID'];?><?php if(isset($_GET['Edit'])){?>&Edit=True<?php }?><?php if(isset($_GET['Ticket_Update'])){?>&Ticket_Update=1<?php }?>');">
                            <div class='nav-icon'><?php $Icons->Information(3);?></div>
                            <div class ='nav-text'>Information</div>
                    </div>
                    <?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >= 4){
                    ?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='customer.php?ID=<?php echo $Ticket['Customer_ID'];?>';">
                            <div class='nav-icon'><?php $Icons->Customer(3);?></div>
                            <div class ='nav-text'>Customer</div>
                    </div><?php }?>
                    <?php if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4){
                    ?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='location.php?ID=<?php echo $Ticket['Location_ID'];?>';">
                            <div class='nav-icon'><?php $Icons->Location(3);?></div>
                            <div class ='nav-text'>Location</div>
                    </div><?php }?>
                    <?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4){
                    ?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='job.php?ID=<?php echo $Ticket['Job_ID'];?>';">
                            <div class='nav-icon'><?php $Icons->Job(3);?></div>
                            <div class ='nav-text'>Job</div>
                    </div><?php }?>
                    <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'ticket-map.php?ID=<?php echo $_GET['ID'];?>');">
                            <div class='nav-icon'><?php $Icons->Map(3);?></div>
                            <div class ='nav-text'>Map</div>
                    </div>
                    <?php if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4){
                    ?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='unit.php?ID=<?php echo $Ticket['Unit_ID'];?>';">
                            <div class='nav-icon'><?php $Icons->Unit(3);?></div>
                            <div class ='nav-text'>Unit</div>
                    </div><?php }?>
                    <?php if(isset($My_Privileges['User']) && $My_Privileges['User']['User_Privilege'] >= 4){
                    ?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='user.php?ID=<?php echo $Ticket['Employee_ID'];?>';">
                            <div class='nav-icon'><?php $Icons->User(3);?></div>
                            <div class ='nav-text'>User</div>
                    </div><?php }?>

                </div>
            </div>
            <div class='container-content'></div>
            <?php }?>
        </div>
        <!-- /#page-wrapper -->

    </div>


    <!-- Bootstrap Core JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<?php if(!isMobile()){?>
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
   <?php }?><style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    div#map * {overflow:invisible;}
    </style>
    <script>
        function clickTab(Tab,Subtab){
            $("a[tab='" + Tab + "']").click();
            setTimeout(function(){
                $("a[tab='" + Subtab + "']").click();
            },2500);
        }
        $(document).ready(function(){
            $("a[tab='overview-pills']").click();
        });
    </script>
    <script>
        function someFunction(link,URL){
            $(link).siblings().removeClass('active');
            $(link).addClass('active');
            $.ajax({
                url:"cgi-bin/php/element/ticket/" + URL,
                success:function(code){
                    $("div.container-content").html(code);
                }
            });
        }
        $(document).ready(function(){
            $("div.Screen-Tabs>div>div:first-child").click();
        });
    </script>
</body>
</html>
<?php
		} else {require('new-ticket.php');}
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=ticket2<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
