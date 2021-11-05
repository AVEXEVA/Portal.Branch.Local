<?php 
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
	$My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User); 
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($NEI,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($My_Privileges['Job']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
		$a = sqlsrv_query($NEI,"
			SELECT Job.Loc
			FROM Job
			WHERE Job.ID = ?
		;",array($_GET['ID']));
		$loc = sqlsrv_fetch_array($a)['Loc'];
        $r = sqlsrv_query(  $NEI,"
			SELECT *
			FROM 		Job
			LEFT JOIN 	TicketO ON Job.ID = TicketO.Job
			WHERE 		TicketO.LID= ?
				AND 	TicketO.fWork= ?
		;",array($loc,$My_User['fWork']));
        $r2 = sqlsrv_query( $NEI,"
			SELECT *
			FROM 		Job
			LEFT JOIN 	TicketD ON Job.ID = TicketD.Job
			WHERE 		TicketD.Loc= ?
						AND TicketD.fWork= ? 
		;",array($loc,$My_User['fWork']));
		$r3 = sqlsrv_query( $NEI,"
			SELECT *
			FROM 		Job
			LEFT JOIN 	TicketDArchive ON Job.ID = TicketDArchive.Loc
			WHERE 		TicketDArchive.Loc= ?
						AND TicketDArchive.fWork= ?
		;",array($loc,$My_User['fWork']));
        $r = sqlsrv_fetch_array($r);
        $r2 = sqlsrv_fetch_array($r2);
		$r3 = sqlsrv_fetch_array($r3);
        $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
	}
    //
    if(!isset($array['ID'])  || !is_numeric($_GET['ID']) || !$Privileged ){require("401.html");}
    else {
		$r = sqlsrv_query(
			$NEI,
			"	SELECT 	TOP 1
			    		Job.ID                AS Job_ID,
			    		Job.fDesc             AS Job_Name,
			    		Job.fDate             AS Job_Start_Date,
			            Job.BHour             AS Job_Budgeted_Hours,
					        JobType.Type          AS Job_Type,
						Job.Remarks 		  AS Job_Remarks,
				          	Loc.Loc               AS Location_ID,
					      	Loc.ID                AS Location_Name,
			    		Loc.Tag               AS Location_Tag,
			    		Loc.Address           AS Location_Street,
			    		Loc.City              AS Location_City,
			    		Loc.State             AS Location_State,
			    		Loc.Zip               AS Location_Zip,
			    		Loc.Route             AS Route,
			    		Zone.Name             AS Division,
			    		Owner.ID              AS Customer_ID,
			    		OwnerRol.Name     	  AS Customer_Name,
			   	 		Owner.Status       	  AS Customer_Status,
			    		Owner.Elevs    		  AS Customer_Elevators,
			    		OwnerRol.Address      AS Customer_Street,
			    		OwnerRol.City         AS Customer_City,
			    		OwnerRol.State        AS Customer_State,
			    		OwnerRol.Zip          AS Customer_Zip,
			    		OwnerRol.Contact      AS Customer_Contact,
			    		OwnerRol.Remarks      AS Customer_Remarks,
			    		OwnerRol.Email        AS Customer_Email,
			    		OwnerRol.Cellular     AS Customer_Cellular,
			    		Elev.ID               AS Unit_ID,
			    		Elev.Unit             AS Unit_Label,
			    		Elev.State            AS Unit_State,
			    		Elev.Cat              AS Unit_Category,
			    		Elev.Type             AS Unit_Type,
			    		Emp.fFirst            AS Mechanic_First_Name,
			    		Emp.Last              AS Mechanic_Last_Name,
			    		Route.ID              AS Route_ID,
						Violation.ID          AS Violation_ID,
						Violation.fdate       AS Violation_Date,
						Violation.Status      AS Violation_Status,
						Violation.Remarks     AS Violation_Remarks
				FROM 	Job
			    		LEFT JOIN Loc           	ON Job.Loc      = Loc.Loc
			    		LEFT JOIN Zone          	ON Loc.Zone     = Zone.ID
			    		LEFT JOIN JobType       	ON Job.Type     = JobType.ID
			    		LEFT JOIN OwnerWithRol  	ON Job.Owner    = OwnerWithRol.ID
			    		LEFT JOIN Elev          	ON Job.Elev     = Elev.ID
			    		LEFT JOIN Route         	ON Loc.Route    = Route.ID
			    		LEFT JOIN Emp           	ON Emp.fWork    = Route.Mech
						LEFT JOIN Violation     	ON Job.ID       = Violation.Job
						LEFT JOIN Owner 			ON Owner.ID 	= Loc.Owner 
						LEFT JOIN Rol AS OwnerRol 	ON OwnerRol.ID  = Owner.Rol
				WHERE 	Job.ID = ?;",
			array(
				$_GET[ 'ID' ]
			)
		);
        $Job = sqlsrv_fetch_array($r);?>
<div class="panel panel-primary">
    <div class="panel-heading"><?php \singleton\fontawesome::getInstance( )->Job( 1 );?> Job</div>
    <div class='panel-body' style='padding:10px;'>			
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> ID</div>
			<div class='col-xs-8'><?php echo $Job['Job_ID'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Name</div>
			<div class='col-xs-8'><?php echo $Job['Job_Name'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Start Date</div>
			<div class='col-xs-8'><?php echo date("m/d/Y",strtotime($Job['Job_Start_Date']));?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Timetable</div>
			<div class='col-xs-8'><?php echo strlen($Job['Job_Budgeted_Hours']) > 0 ? $Job['Job_Budgeted_Hours'] : "Null";?> hrs</div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Type</div>
			<div class='col-xs-8'><?php echo $Job['Job_Type'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Notes</div>
			<div class='col-xs-8'><pre><?php echo $Job['Job_Remarks'];?></pre></div>
		</div>
	</div>
	<div class='panel-heading'><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Location</div>
	<div class='panel-body' style='padding:10px;'>
		<div class='row' style='border-bottom:3px ;padding-top:10px;padding-bottom:10px;'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Name:</div>
			<div class='col-xs-8'><?php echo $Job['Location_Name'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Street:</div>
			<div class='col-xs-8'><?php echo $Job['Location_Street'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
			<div class='col-xs-8'><?php echo $Job['Location_City'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State:</div>
			<div class='col-xs-8'><?php echo $Job['Location_State'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
			<div class='col-xs-8'><?php echo $Job['Location_Zip'];?></div>
		</div>
	</div>
	<div class='panel-heading'><?php \singleton\fontawesome::getInstance( )->Customer( 1 );?> Customer</div>
	<div class='panel-body' style='padding:10px;'>
		<div class='row' style='border-bottom:3px ;padding-top:10px;padding-bottom:10px;'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Customer </div>
			<div class='col-xs-8'><?php echo $Job['Customer_Street'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Address</div>
			<div class='col-xs-8'><?php echo $Job['Customer_Name'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City</div>
			<div class='col-xs-8'><?php echo $Job['Customer_City'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State</div>
			<div class='col-xs-8'><?php echo $Job['Customer_State'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip</div>
			<div class='col-xs-8'><?php echo $Job['Customer_Zip'];?></div>
		</div>
		<div class='row'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status</div>
			<div class='col-xs-8'><?php echo $Job['Customer_Status'] == 0 ? "Active" : "Unactive";?></div>
		</div>
		<div class='row'>
			<?php if(isset($Job['Customer_Website']) && strlen($Job['Customer_Website']) > 0){?><div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Website</div>
			<div class='col-xs-8'><?php echo $Job['Customer_Website'];?></div><?php }?>
		</div>
    </div>
</div>

<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>