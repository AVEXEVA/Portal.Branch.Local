<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
	//Connection
    $result = $database->query(
    	null,
    	"	SELECT 	* 
    		FROM 	Connection 
    		WHERE 		Connector = ? 
    				AND Hash = ?;",
    	array(
    		$_SESSION['User'],
    		$_SESSION['Hash']
    	)
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
	$result = $database->query(
		null,
		"	SELECT 	*, 
					fFirst AS First_Name, 
					Last as Last_Name 
			FROM 	Emp 
			WHERE 	ID= ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$result = $database->query(null,
		" 	SELECT 	Privilege.*
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$Privileges = array();
	$Privileged = false;
	while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access' ] ] = $Privilege; }
	if(		isset($Privileges['Customer']) 
		&& 	$Privileges[ 'Customer' ][ 'Owner' ]  >= 4 
		&& 	$Privileges[ 'Customer' ][ 'Group' ] >= 4 
		&& 	$Privileges[ 'Customer' ][ 'Other' ] >= 4){
				$Privileged = true;}
    if(		!isset($Connection['ID'])  
    	|| 	!is_numeric($_GET['ID']) 
    	|| !$Privileged 
    ){ ?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
    	$database->query(
    		null,
    		"	INSERT INTO Activity( [User], [Date], [Page] ) VALUES( ?, ?, ? );",
    		array(
    			$_SESSION['User'],
    			date("Y-m-d H:i:s"), 
    			"customer.php"
    		)
    	);
        $result = $database->query(
        	null,
            "	SELECT 	Customer.*                    
            	FROM    (
            				SELECT 	Owner.ID    AS ID,
		                    		Rol.Name    AS Name,
		                    		Rol.Address AS Street,
				                    Rol.City    AS City,
				                    Rol.State   AS State,
				                    Rol.Zip     AS Zip,
				                    Owner.Status  AS Status,
									Rol.Website AS Website
							FROM    Owner 
									LEFT JOIN Rol ON Owner.Rol = Rol.ID
            		) AS Customer
            	WHERE   Customer.ID = ?;",
            array(
            	$_GET['ID']
            )
        );
        $Customer = sqlsrv_fetch_array($result);
?><div class='row'>
	<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Customer(1);?> Name:</div>
	<div class='col-8'><?php echo $Customer['Name'];?></div>
</div>
<div class='row'>
	<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Status:</div>
	<div class='col-8'><?php echo isset($Customer['Status']) && $Customer['Status'] == 0? "Active" : "Inactive";?></div>
</div>
<div class='row'>
	<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Street:</div>
	<div class='col-8'><?php echo $Customer['Street'];?></div>
</div>
<div class='row'>
	<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
	<div class='col-8'><?php echo $Customer['City'];?></div>
</div>
<div class='row'>
	<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State:</div>
	<div class='col-8'><?php echo $Customer['State'];?></div>
</div>
<div class='row'>
	<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
	<div class='col-8'><?php echo $Customer['Zip'];?></div>
</div>
<div class='row'>
	<div class='col-4'><?php \singleton\fontawesome::getInstance( )->Web(1);?> Website:</div>
	<div class='col-8'><?php echo strlen($Customer['Website']) > 0 ?  $Customer['Website'] : "&nbsp;";?></div>
</div>
<div class='row'>
    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Units</div>
    <div class='col-8'><?php
		$r = $database->query(null,"
			SELECT Count(Elev.ID) AS Count_of_Elevators
			FROM   Elev
				   LEFT JOIN Loc ON Elev.Loc = Loc.Loc
			WHERE  Loc.Owner = ?
		;",array($_GET['ID']));
		echo $r ? sqlsrv_fetch_array($r)['Count_of_Elevators'] : 0;
	?></div>
</div>
<div class='row'>
    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Jobs</div>
    <div class='col-8'>&nbsp;
		<?php
		$r = $database->query(null,"
			SELECT Count(Job.ID) AS Count_of_Jobs
			FROM   Job
				   LEFT JOIN Loc ON Job.Loc = Loc.Loc
			WHERE  Loc.Owner = ? AND Job.Status = 1
		;",array($_GET['ID']));
	echo $r ? sqlsrv_fetch_array($r)['Count_of_Jobs'] : 0;?>
	</div>
</div>
<div class='row'>
    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Violation(1);?> Violations</div>
    <div class='col-8'>&nbsp;
	<?php
		$r = $database->query(null,"
			SELECT Count(Violation.ID) AS Count_of_Violations
			FROM   Violation
				   LEFT JOIN Loc ON Violation.Loc = Loc.Loc
			WHERE  Loc.Owner = ?
		;",array($_GET['ID']));
		echo $r ? sqlsrv_fetch_array($r)['Count_of_Violations'] : 0;?>
	</div>
</div>
<div class='row'>
    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Tickets</div>
    <div class='col-8'>
	<?php
		$r = $database->query(null,"
			SELECT Count(Tickets.ID) AS Count_of_Tickets
			FROM   (
						(
							SELECT TicketO.ID AS ID
							FROM   TicketO
								   LEFT JOIN Loc ON TicketO.LID = Loc.Loc
							WHERE  Loc.Owner = ?
						)
						UNION ALL
						(
							SELECT TicketD.ID AS ID
							FROM   TicketD
								   LEFT JOIN Loc ON TicketD.Loc = Loc.Loc
							WHERE  Loc.Owner = ?
						)
						UNION ALL
						(
							SELECT TicketDArchive.ID AS ID
							FROM   TicketDArchive
								   LEFT JOIN Loc ON TicketDArchive.Loc = Loc.Loc
							WHERE  Loc.Owner = ?
						)
					) AS Tickets
		;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
		echo $r ? sqlsrv_fetch_array($r)['Count_of_Tickets'] : 0;?>
	</div>
</div>
<div class='row'>
    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Proposals</div>
    <div class='col-8'>
		<?php
		$r = $database->query(null,"
			SELECT Count(Estimate.ID) AS Count_of_Estimates
			FROM   Estimate
				   LEFT JOIN Loc ON Estimate.LocID = Loc.Loc
			WHERE  Loc.Owner = ?
		;",array($_GET['ID']));
		echo $r ? sqlsrv_fetch_array($r)['Count_of_Estimates'] : 0;?>
	</div>
</div>
<div class='row'>
    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?> Invoices</div>
    <div class='col-8'>
		<?php
		$r = $database->query(null,"
			SELECT Count(Invoice.Ref) AS Count_of_Invoices
			FROM   Invoice
				   LEFT JOIN Loc ON Invoice.Loc = Loc.Loc
			WHERE  Loc.Owner = ? AND Invoice.Status = 1;
		;",array($_GET['ID']));
		echo $r ? sqlsrv_fetch_array($r)['Count_of_Invoices'] : 0;?>
	</div>
</div>
<div class='row'>
    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Legal(1);?> Lawsuits</div>
    <div class='col-8'>
		<?php
		$r = $database->query(null,"
			SELECT Count(Job.ID) AS Count_of_Lawsuits
			FROM   Job
				   LEFT JOIN Loc ON Job.Loc = Loc.Loc
			WHERE  Loc.Owner = ?
				   AND (Job.Type = 9
					 OR Job.Type = 12)
		;",array($_GET['ID']));
		echo $r ? sqlsrv_fetch_array($r)['Count_of_Lawsuits'] : 0;?>
	</div>
</div>
<?php if(isset($Privileges['Finances']) && $Privileges['Finances']['Owner'] >= 4) {?>
<div class='row' style='padding-top:10px;padding-bottom:10px;'>
    <div class='col-4'><?php \singleton\fontawesome::getInstance( )->Payroll(1);?> Balance</div>
    <div class='col-8'><?php
		$r = $database->query(null,"
			SELECT Sum(OpenAR.Balance) AS Balance
			FROM   OpenAR
				   LEFT JOIN Loc ON OpenAR.Loc = Loc.Loc
			WHERE  Loc.Owner = ?
		;",array($_GET['ID']));
		$Balance = $r ? sqlsrv_fetch_array($r)['Balance'] : 0;
		echo money_format('%(n',$Balance);
	?></div>
</div> <?php } ?>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
