<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [
        'read_and_close' => true
    ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
    $result = $database->query(
        null,
        "   SELECT  *
                FROM    Connection
                WHERE       Connection.Connector = ?
                    AND Connection.Hash  = ?;",
        array(
            $_SESSION[ 'User' ],
            $_SESSION[ 'Hash' ]
        )
    );
    $Connection = sqlsrv_fetch_array( $result );
    //User
    $result = $database->query(
        null,
        "   SELECT  *,
                    Emp.fFirst AS First_Name,
                    Emp.Last   AS Last_Name
            FROM    Emp
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User = sqlsrv_fetch_array( $result );
    //Privileges
    $result = $database->query(
        null,
        "   SELECT  *
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION['User']
        )
    );
    $Privileges = array();
    if( $result ){while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; } }
    if( !isset( $Connection[ 'ID' ] )
        || !isset($Privileges[ 'Ticket' ])
            || $Privileges[ 'Ticket' ][ 'User_Privilege' ]  < 4
            || $Privileges[ 'Ticket' ][ 'Group_Privilege' ] < 4
            || $Privileges[ 'Ticket' ][ 'Other_Privilege' ] < 4
    ){      
        ?><?php require( '../404.html' );?><?php 
    } else {
//CONNECT TO SERVER
//GET OPEN TICKETS
if(is_numeric($_GET['ID'])){
    $r = $database->query(null,"
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
                Customer.ID     AS Owner_ID,
                Customer.Name   AS Customer,
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
                'TicketO' AS Table2
            FROM
                TicketO
                LEFT JOIN Loc           ON TicketO.LID      = Loc.Loc
                LEFT JOIN Job           ON TicketO.Job      = Job.ID
                LEFT JOIN (
                    SELECT  Owner.ID,
                            Rol.Name 
                    FROM    Owner 
                            LEFT JOIN Rol ON Rol.ID = Owner.Rol
                ) AS Customer ON Job.Owner = Customer.ID
                LEFT JOIN JobType       ON Job.Type         = JobType.ID
                LEFT JOIN Elev          ON TicketO.LElev    = Elev.ID
                LEFT JOIN Zone          ON Loc.Zone         = Zone.ID
                LEFT JOIN TickOStatus   ON TicketO.Assigned = TickOStatus.Ref
                LEFT JOIN Emp           ON TicketO.fWork    = Emp.fWork
                LEFT JOIN TicketPic     ON TicketO.ID       = TicketPic.TicketID
            WHERE
                TicketO.ID=?;",array($_GET['ID']));
    $Ticket = sqlsrv_fetch_array($r);
    while($temp = sqlsrv_fetch_array($r)){}
    $Ticket['Loc'] = $Ticket['LID'];
    $Ticket['Status'] = ($Ticket['Status'] == 'Completed') ? "Reviewing" : $Ticket['Status'];
    if($Ticket['ID'] == "" || $Ticket['ID'] == 0 || !isset($Ticket['ID'])){
        $r = $database->query(null,"
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
                Customer.ID     AS Owner_ID,
                Customer.Name   AS Customer,
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
				'Completed'         AS Status,
        'TicketD' AS Table2
            FROM
                TicketD
                LEFT JOIN Loc           ON TicketD.Loc      = Loc.Loc
                LEFT JOIN Job           ON TicketD.Job      = Job.ID
                LEFT JOIN (
                    SELECT  Owner.ID,
                            Rol.Name 
                    FROM    Owner 
                            LEFT JOIN Rol ON Rol.ID = Owner.Rol
                ) AS Customer ON Job.Owner = Customer.ID
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
        $r = $database->query(null,"
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
                Customer.ID     AS Owner_ID,
                Customer.Name   AS Customer,
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
				'Completed'         AS Status,
        'TicketDArchive' AS Table2
            FROM
                TicketDArchive
                LEFT JOIN Loc           ON TicketDArchive.Loc = Loc.Loc
                LEFT JOIN Job           ON TicketDArchive.Job = Job.ID
                LEFT JOIN (
                    SELECT  Owner.ID,
                            Rol.Name 
                    FROM    Owner 
                            LEFT JOIN Rol ON Rol.ID = Owner.Rol
                ) AS Customer ON Job.Owner = Customer.ID
                LEFT JOIN JobType       ON Job.Type = JobType.ID
                LEFT JOIN Elev          ON TicketDArchive.Elev = Elev.ID
                LEFT JOIN Zone          ON Loc.Zone = Zone.ID
                LEFT JOIN Emp           ON TicketDArchive.fWork = Emp.fWork
                LEFT JOIN TicketPic     ON TicketDArchive.ID = TicketPic.TicketID
            WHERE
                TicketDArchive.ID = ?;",array($_GET['ID']));
        $Ticket = sqlsrv_fetch_array($r);
    }
}
$r = $database->query(null,"SELECT PDATicketSignature.Signature AS Signature FROM PDATicketSignature WHERE PDATicketSignature.PDATicketID = ? AND PDATicketSignature.SignatureType = 'C';",array($_GET['ID']));
if($r){while($array = sqlsrv_fetch_array($r)){$Ticket['Signature'] = $array['Signature'];}}
if($Ticket['Table2'] == 'TicketO'){
  $r = $database->query(null,"SELECT * FROM TicketDPDA WHERE ID = ?;",array($_GET['ID']));
  $Ticket2 = sqlsrv_fetch_array($r);
} elseif($Ticket['Table2'] == 'TicketD'){
  $r = $database->query(null,"SELECT * FROM TicketD WHERE ID = ?;",array($_GET['ID']));
  $Ticket2 = sqlsrv_fetch_array($r);
} elseif($Ticket['Table2'] == 'TicketDArchive'){
  $r = $database->query(null,"SELECT * FROM TicketDArchive WHERE ID = ?;",array($_GET['ID']));
  $Ticket2 = sqlsrv_fetch_array($r);
}
?>
<style>.pagebreak { page-break-before: always; } /* page-break-after works, as well */</style>
<div class='pagebreak'> </div>
<div class='' style='background-color:white !important;color:black !important;font-size:12px !important;'>
	<div class='row g-0' style='text-align:center;'>
		<div><b>Nouveau Elevator Industries Inc.</b></div>
		<div>47-55 37th Street</div>
		<div>Tel:(718) 349-4700 | Fax:(718)383-3218</div>
		<div>Email:Operations@NouveauElevator.com</div>
	</div>
	<hr style='border-bottom:1px solid black;' />
	<h3 style='text-align:center;'><b><?php echo $Ticket['Status'];?> Service Ticket #<?php echo $_GET['ID'];?></b></h3>
	<hr style='border-bottom:1px solid black;' />
	<div class='row g-0'>
		<div class='p-1 col-2' style='text-align:right;'><b>Customer</b></div>
		<div class='p-1 col-2'><?php echo $Ticket['Customer'];?></div>
	</div>
	<div class='row g-0'>
		<!--<div class='p-1 col-2' style='text-align:right;'><b>ID#</b></div>
		<div class='p-1 col-2'><?php echo $Ticket['Location_ID'];?></div>-->
		<div class='p-1 col-2' style='text-align:right;'><b>Location</b></div>
		<div class='p-1 col-2'><?php echo $Ticket['Tag'];?></div>
		<div class='p-1 col-2' style='text-align:right;'><b>Job</b></div>
		<div class='p-1 col-2'><?php echo $Ticket['Job_Description'];?></div>
	</div>
	<div class='row g-0'>
		<div class='p-1 col-2'>&nbsp;</div>
		<div class='p-1 col-2'><?php echo $Ticket['Address'];?></div>
		<div class='p-1 col-2' style='text-align:right;'><b>Unit ID</b></div>
		<div class='p-1 col-2'><?php echo strlen($Ticket['Unit_State'] > 0) ? $Ticket['Unit_State'] : $Ticket['Unit_Label'];?></div>
	</div>
	<div class='row g-0'>
		<div class='p-1 col-2'>&nbsp;</div>
		<div class='p-1 col-2'><?php echo $Ticket['City'];?>, <?php echo $Ticket['State'];?> <?php echo $Ticket['Zip'];?></div>
	</div>

	<div class='row g-0'>
		<div class='p-1 col-2' style='text-align:right;'><b>Customer Signature</b></div>
		<div class='p-1 col-2'><img id='Ticket_Signature' height='100px' src='data:image/jpeg;base64,<?php echo base64_encode($Ticket['Signature']);?>' /></div>
	</div>
	<hr style='border-bottom:1px solid black;' />
	<div class='row g-0'>
		<div class='p-1 col-2' style='text-align:right;'><b>Serviced</b></div>
		<div class='p-1 col-2'><?php echo date('m/d/Y', strtotime(substr($Ticket['EDate'],0,10)));?></div>
		<div class='p-1 col-2' style='text-align:right;'><b>Regular</b></div>
		<div class='p-1 col-2'><?php echo $Ticket['Reg'] == '' ? '0.00' : $Ticket['Reg'];?> hrs</div>
		<div class='p-1 col-2' style='text-align:right;'><b>Worker</b></div>
		<div class='p-1 col-2'><?php echo strlen($Ticket['First_Name']) > 0 ? proper($Ticket["First_Name"] . " " . $Ticket['Last_Name']) : "None";;?></div>
	</div>
	<div class='row g-0'>
		<div class='p-1 col-2' style='text-align:right;'><b>Dispatched</b></div>
		<div class='p-1 col-2'><?php echo substr($Ticket['TimeRoute'],11,99);?></div>
		<div class='p-1 col-2' style='text-align:right;'><b>O.T.</b></div>
		<div class='p-1 col-2'><?php echo $Ticket['OT'] == '' ? '0.00' : $Ticket['OT']?> hrs</div>
		<div class='p-1 col-2' style='text-align:right;'><b>Role</b></div>
		<div class='p-1 col-2'><?php echo proper($Ticket['Role']);?></div>
	</div>
	<div class='row g-0'>
		<div class='p-1 col-2' style='text-align:right;'><b>On Site</b></div>
		<div class='p-1 col-2'><?php echo substr($Ticket['TimeSite'],11,99);?></div>
		<div class='p-1 col-2' style='text-align:right;'><b>D.T.</b></div>
		<div class='p-1 col-2'><?php echo $Ticket['DT'] == '' ? '0.00' : $Ticket['DT'];?> hrs</div>
	</div>
	<div class='row g-0'>
		<div class='p-1 col-2' style='text-align:right;'><b>Completed</b></div>
		<div class='p-1 col-2'><?php echo substr($Ticket['TimeComp'],11,99);?></div>
    <div class='p-1 col-2' style='text-align:right;'><b>T.T.</b></div>
		<div class='p-1 col-2'><?php echo $Ticket['TT'] == '' ? '0.00' : $Ticket['TT'];?> hrs</div>
	</div>
    <div class='row g-0'>
        <div class='p-1 col-4'>&nbsp;</div>
        <div class='p-1 col-2' style='text-align:right;'><b>Total</b></div>
		<div class='p-1 col-2'><?php echo $Ticket['Total'] == '' ? '0.00' : $Ticket['Total'];?> hrs</div>
    </div>
	<hr style='border-bottom:1px solid black;' />
	<div class='row g-0'>
		<div class='p-1 col-2' style='text-align:right;'><b>Scope of Work</b></div>
		<div class='p-1 col-10'><pre><?php echo $Ticket['fDesc'];?></pre></div>
		<div class='p-1 col-2' style='text-align:right;'><b>Resolution of Work</b></div>
		<div class='p-1 col-10'><pre><?php echo $Ticket2['DescRes'];?></pre></div>
	</div>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=ticket<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
