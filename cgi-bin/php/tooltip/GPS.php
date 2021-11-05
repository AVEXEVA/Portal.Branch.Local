<?php
session_start( [ 'read_and_close' => true ] );
require('../../../cgi-bin/php/index.php');
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

    if((!isset($array['ID'])  || !$Privileged) && isset($_GET['ID'])){?><html><head></head></html><?php }
    elseif(isset($array['ID']) && $Privileged && isset($_GET['ID']) && is_numeric($_GET['ID'])) {
      $Employee_ID = $_GET['ID'];
      if(isset($Employee_ID)){
        $r = sqlsrv_query($NEI,
          " SELECT  TicketO.*
            FROM    nei.dbo.TicketO
                    LEFT JOIN nei.dbo.Emp ON TicketO.fWork = Emp.fWork
            WHERE   Emp.ID = ?
                    AND TicketO.Assigned > 1
                    AND TicketO.Assigned < 4
          ;",array($Employee_ID));
        if($r){
          $row = sqlsrv_fetch_array($r);
          if(is_array($row)){
            $Ticket_ID = $row['ID'];
          }
        }
      }
      $_GET['ID'] = isset($Ticket_ID) ? $Ticket_ID : -1;
      $Ticket = null;
      $r = sqlsrv_query($NEI,"
              SELECT
                  TicketO.*,
                  TicketDPDA.DescRes AS DescRes,
                  TicketDPDA.OT,
                  TicketDPDA.Reg,
                  TicketDPDA.DT,
                  TicketDPDA.TT,
                  TicketDPDA.NT,
                  TicketDPDA.Total,
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
                  Emp.Title           AS Role
              FROM
                  nei.dbo.TicketO
                  LEFT JOIN nei.dbo.TicketDPDA    ON TicketDPDA.ID = TicketO.ID
                  LEFT JOIN nei.dbo.Loc           ON TicketO.LID      = Loc.Loc
                  LEFT JOIN nei.dbo.Job           ON TicketO.Job      = Job.ID
                  LEFT JOIN nei.dbo.OwnerWithRol  ON Loc.Owner    = OwnerWithRol.ID
                  LEFT JOIN nei.dbo.JobType       ON Job.Type         = JobType.ID
                  LEFT JOIN nei.dbo.Elev          ON TicketO.LElev    = Elev.ID
                  LEFT JOIN nei.dbo.Zone          ON Loc.Zone         = Zone.ID
                  LEFT JOIN nei.dbo.TickOStatus   ON TicketO.Assigned = TickOStatus.Ref
                  LEFT JOIN nei.dbo.Emp           ON TicketO.fWork    = Emp.fWork
                  LEFT JOIN nei.dbo.TicketPic     ON TicketO.ID       = TicketPic.TicketID
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
  $r = sqlsrv_query($NEI,"SELECT PDATicketSignature.Signature AS Signature FROM nei.dbo.PDATicketSignature WHERE PDATicketSignature.PDATicketID = ?",array($_GET['ID']));
  if($r){while($array = sqlsrv_fetch_array($r)){$Ticket['Signature'] = $array['Signature'];}}?>
<div class='popup' class='' style='width:650px;font-size:12px !important;background-color:white;height: auto !important;'>
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
      <div class='col-xs-2'><img id='Ticket_Signature' width='100%' src='data:image/jpeg;base64,<?php echo base64_encode($Ticket['Signature']);?>' /></div>
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
      <div class='col-xs-2' style='text-align:right;'><b>Accepted Work</b></div>
      <div class='col-xs-2'><?php echo date("h:i A",strtotime($Ticket['TimeRoute'])) == '12:00 AM' || $Ticket['TimeRoute'] == NULL ? '' : date("h:i A",strtotime($Ticket['TimeRoute']));?></div>
      <div class='col-xs-2' style='text-align:right;'><b>O.T.</b></div>
      <div class='col-xs-2'><?php echo $Ticket['OT'] == '' ? '0.00' : $Ticket['OT']?> hrs</div>
      <div class='col-xs-2' style='text-align:right;'><b>Role</b></div>
      <div class='col-xs-2'><?php echo proper($Ticket['Role']);?></div>
  </div>
  <div class='row'>
      <div class='col-xs-2' style='text-align:right;'><b>At Work</b></div>
      <div class='col-xs-2'><?php echo date("h:i A",strtotime($Ticket['TimeSite'])) == '12:00 AM' || $Ticket['TimeSite'] == NULL ? '' : date("h:i A",strtotime($Ticket['TimeSite']));?></div>
      <div class='col-xs-2' style='text-align:right;'><b>D.T.</b></div>
      <div class='col-xs-2'><?php echo $Ticket['DT'] == '' ? '0.00' : $Ticket['DT'];?> hrs</div>
  </div>
  <div class='row'>
      <div class='col-xs-2' style='text-align:right;'><b>Completed Work</b></div>
      <div class='col-xs-2'><?php echo date("h:i A",strtotime($Ticket['TimeComp'])) == '12:00 AM' || $Ticket['TimeComp'] == NULL ? '' : date("h:i A",strtotime($Ticket['TimeComp']));;?></div>
      <div class='col-xs-2' style='text-align:right;'><b>Total</b></div>
      <div class='col-xs-2'><?php echo $Ticket['Total'] == '' ? '0.00' : $Ticket['Total'];?> hrs</div>
  </div>
  <hr />
  <div class='row'>
      <div class='col-xs-2' style='text-align:right;'><b>Scope of Work</b></div>
      <div class='col-xs-10'><pre style='font-size:12px !important;'><?php echo $Ticket['fDesc'];?></pre></div>
      <div class='col-xs-2' style='text-align:right;'><b>Resolution of Work</b></div>
      <div class='col-xs-10'><pre style='font-size:12px !important;'><?php echo $Ticket['DescRes'];?></pre></div>
  </div>
</div>
<?php
  }
}
?>
