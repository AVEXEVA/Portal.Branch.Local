<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $Connection = $database->query(
        null,
        "   SELECT  Connection.* 
            FROM    Connection 
            WHERE   Connection.Connector = ? 
                    AND Connection.Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($Connection);

    //User
    $User = $database->query(
        null,
        "   SELECT  Emp.*, 
                    Emp.fFirst  AS First_Name, 
                    Emp.Last    AS Last_Name 
            FROM    Emp 
            WHERE   Emp.ID = ?;",
        array(
            $_SESSION['User']
        )
    );
    $User = sqlsrv_fetch_array($User);

    //Privileges
    $r = $database->query(
        null,
        "   SELECT  Privilege.Access_Table, 
                    Privilege.User_Privilege, 
                    Privilege.Group_Privilege, 
                    Privilege.Other_Privilege
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access_Table']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Unit']) 
        && $Privileges['Unit']['User_Privilege'] >= 4 
        && $Privileges['Unit']['Group_Privilege'] >= 4 
        && $Privileges['Unit']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Unit']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = $database->query(  
            null,
            "   SELECT  Sum( Ticket.Count ) AS Count 
                FROM    (
                            SELECT  Ticket.Unit,
                                    Ticket.Field,
                                    Sum( Ticket.Count ) AS Count
                            FROM (
                                (
                                    SELECT      TicketO.LElev AS Unit,
                                                TicketO.fWork AS Field,
                                                Count( TicketO.ID ) AS Count
                                    FROM        TicketO
                                    GROUP BY    TicketO.LElev,
                                                TicketO.fWork
                                ) UNION ALL (
                                    SELECT      TicketD.Elev AS Unit,
                                                TicketD.fWork AS Field, 
                                                Count( TicketD.ID ) AS Count
                                    FROM        TicketD
                                    GROUP BY    TicketD.Elev,
                                                TicketD.fWork
                                )
                            ) AS Ticket
                            GROUP BY    Ticket.Unit,
                                        Ticket.Field
                        ) AS Ticket
                        LEFT JOIN Emp AS Employee ON Ticket.Field = Employee.fWork
                WHERE   Employee.ID = ?
                        AND Ticket.Unit = ?;",
            array( 
                $_SESSION[ 'User' ],
                $_GET[ 'ID' ]
            )
        );
        $Tickets = 0;
        if ( $r ){ $Tickets = sqlsrv_fetch_array( $r )[ 'Count' ]; }
        $Privileged =  $Tickets > 0 ? true : false;
    }
    if(     !isset( $Connection[ 'ID' ] )  
        ||  !$Privileged ){
            ?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
          " SELECT  TOP 1
                    Elev.ID,
                    Elev.Unit           AS Unit,
                    Elev.State          AS State,
                    Elev.Cat            AS Category,
                    Elev.Type           AS Type,
                    Elev.Building       AS Building,
                    Elev.Since          AS Since,
                    Elev.Last           AS Last,
                    Elev.Price          AS Price,
                    Elev.fDesc          AS Description,
                    Loc.Loc             AS Location_ID,
                    Loc.Tag             AS Location_Name,
                    Loc.Address         AS Location_Street,
                    Loc.City            AS Location_City,
                    Loc.State           AS Location_State,
                    Loc.Zip             AS Location_Zip,
                    Loc.Route           AS Location_Route,
                    Zone.Name           AS Location_Division,
                    Customer.ID         AS Customer_ID,
                    Customer.Name       AS Customer_Name,
    				Customer.Contact    AS Customer_Contact,
    				Customer.Street     AS Customer_Street,
    				Customer.City 	    AS Customer_City,
    				Customer.State 	    AS Customer_State,
                    Emp.ID              AS Route_Mechanic_ID,
                    Emp.fFirst          AS Route_Mechanic_First_Name,
                    Emp.Last            AS Route_Mechanic_Last_Name
            FROM    Elev
                    LEFT JOIN Loc           ON Elev.Loc = Loc.Loc
                    LEFT JOIN Zone          ON Loc.Zone = Zone.ID
                    LEFT JOIN (
                            SELECT  Owner.ID        AS ID,
                                    Rol.Name        AS Name,
                                    Rol.Address     AS Street,
                                    Rol.City        AS City,
                                    Rol.State       AS State,
                                    Rol.Zip         AS Zip,
                                    Owner.Status    AS Status,
                                    Rol.Website     AS Website
                            FROM    Owner
                            LEFT JOIN Rol ON Owner.Rol          = Rol.ID
                    ) AS Customer ON Location.Owner             = Customer.ID
                    LEFT JOIN Route ON Loc.Route = Route.ID
                    LEFT JOIN Emp ON Route.Mech = Emp.fWork
            WHERE      Elev.ID = ?
                    OR Elev.State = ?;",
          array(
            isset( $_GET[ 'ID' ] ) ? $_GET[ 'ID' ] : null,
            isset( $_GET[ 'City_ID' ] ) ? $_GET[ 'City_ID' ] : null,
          )
        );
        $Unit = sqlsrv_fetch_array($r);
        $r = $database->query(
          null,
          " SELECT  *
            FROM    ElevTItem
            WHERE   ElevTItem.ElevT    = 1
                    AND ElevTItem.Elev = ?;",
          array(
            $_GET[ 'ID' ]
          )
        );
        if( $r ){while( $array = sqlsrv_fetch_array( $r ) ){ $Unit[ $array[ 'fDesc' ] ] = $array[ 'Value' ]; } }
?><!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
    <?php 
        $_SESSION[ 'Bootstrap' ] = '5.1';
        require( bin_meta . 'index.php');
        require( bin_css  . 'index.php');
        require( bin_js   . 'index.php');
    ?>
</head>
<body onload='finishLoadingPage();'>
    <div id="wrapper">
        <?php require( bin_php . 'element/navigation.php' ); ?>
        <?php require( bin_php . 'element/loading.php' ); ?>
		<div id="page-wrapper" class='content'>
            <div class='card-deck row'>
    			<div class='card col-12'>
                    <div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Unit();?> Unit: <?php echo $Unit['Unit'];?></div>
                    <div class='card-body'>
            			<div class='Screen-Tabs shadower'>
            				<div class='row'>
            					<div class='nav-tab col-lg-1 col-md-2 col-3' onClick="">
        							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Info(3);?></div>
        							<div class='nav-text'>Information</div>
            					</div>
                                <?php if($Unit['Type'] == 'Elevator' && isset($Privileges['Unit']) && ($Privileges['Unit']['User_Privilege'] >= 4 || $Privileges['Unit']['Group_Privilege'] >= 4)){
            					?><div tab='cod' class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-items.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><img src='media/images/icons/elevator.png' width='auto' height='35px' /></div>
            							<div class ='nav-text'>Elevator</div>
            					</div><?php }
                                $r = $database->query($database_Device,"SELECT CM_Fault.* FROM Device.dbo.CM_Unit LEFT JOIN Device.dbo.CM_Fault ON CM_Unit.Location = CM_Fault.Location AND CM_Unit.Unit = CM_Fault.Unit WHERE CM_Unit.Elev_ID = ?",array($_GET['ID']));
                                if($r && is_array(sqlsrv_fetch_array($r)) && ($Privileges['Unit']['User_Privilege'] >= 4 || $Privileges['Unit']['Group_Privilege'] >= 4)){
            					?><div tab='cod' class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-faults.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><img src='media/images/icons/fault.png' width='auto' height='35px' /></div>
            							<div class ='nav-text'>Faults</div>
            					</div><?php }
                                if($Unit['Type'] == 'Elevator' && isset($Privileges['Unit']) && $Privileges['Unit']['User_Privilege'] >= 4 || $Privileges['Unit']['Group_Privilege'] >= 4){
            					?><div tab='cod' class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-survey-sheet.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Sitemap(3);?></div>
            							<div class ='nav-text'>Survey</div>
            					</div><?php }
                                if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4 || $Privileges['Job']['Group_Privilege'] >= 4){
            					?><div tab='cod' class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-code.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
            							<div class ='nav-text'>Code</div>
            					</div><?php }
            					if(isset($Privileges['Customer']) && $Privileges['Customer']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-customer.php?ID=<?php echo $Unit['Customer_ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Customer(3);?></div>
            							<div class ='nav-text'>Customer</div>
            					</div><?php }
            					if(isset($Privileges['Collection']) && $Privileges['Collection']['User_Privilege'] >= 4 && FALSE){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-collection.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Collection(3);?></div>
            							<div class ='nav-text'>Collections</div>
            					</div><?php }
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-feed.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Activities(3);?></div>
            							<div class ='nav-text'>Feed</div>
            					</div><?php 
                                if(isset($Privileges['Time']) && $Privileges['Time']['Group_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-hours.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Hours(3);?></div>
            							<div class ='nav-text'>Hours</div>
            					</div><?php }
                                if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-invoices.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Invoice(3);?></div>
            							<div class ='nav-text'>Invoices</div>
            					</div><?php }
                                if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-jobs.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
            							<div class ='nav-text'>Jobs</div>
            					</div><?php }
                                if(isset($Privileges['Log']) && $Privileges['Log']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-log.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Job(3);?></div>
            							<div class ='nav-text'>Log</div>
            					</div><?php }
                                if(isset($Privileges['Legal']) && $Privileges['Legal']['User_Privilege'] >= 4 && false){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-legal.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Legal(3);?></div>
            							<div class ='nav-text'>Legal</div>
            					</div><?php }
                                if(isset($Privileges['Location']) && $Privileges['Location']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="document.location.href='location.php?ID=<?php echo $Unit['Location_ID'];?>';">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location(3);?></div>
            							<div class ='nav-text'>Location</div>
            					</div><?php }
                                /*if(isset($Privileges['Maintenance']) && $Privileges['Maintenance']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-maintenance.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Maintenance(3);?></div>
            							<div class ='nav-text'>Maintenance</div>
            					</div><?php }*/
                                /*if(isset($Privileges['Map']) && $Privileges['Map']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-map.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Map(3);?></div>
            							<div class ='nav-text'>Map</div>
            					</div><?php }*/
                                /*if(isset($Privileges['Modernization']) && $Privileges['Modernization']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-modernization.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Modernization(3);?></div>
            							<div class ='nav-text'>Modernization</div>
            					</div><?php }*/
                                if(isset($Privileges['Finances']) && $Privileges['Finances']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-pnl.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Financial(3);?></div>
            							<div class ='nav-text'>P&L</div>
            					</div><?php }
                                /*if(isset($Privileges['Repair']) && $Privileges['Repair']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-repair.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Repair(3);?></div>
            							<div class ='nav-text'>Repair</div>
            					</div><?php }*/?>
            					<?php if(isset($Privileges['Route']) && $Privileges['Route']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick=document.location.href="route.php?ID=<?php echo $Unit['Route'];?>">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Route(3);?></div>
            							<div class ='nav-text'>Route</div>
            					</div><?php }?>
            					<?php if(isset($Privileges['Service']) && $Privileges['Service']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-service.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Phone(3);?></div>
            							<div class ='nav-text'>Service</div>
            					</div><?php }?>
            					<?php /*if(isset($Privileges['Testing']) && $Privileges['Testing']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-testing.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Testing(3);?></div>
            							<div class ='nav-text'>Testing</div>
            					</div><?php }*/
                                if(isset($Privileges['Ticket']) && $Privileges['Ticket']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-tickets.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Ticket(3);?></div>
            							<div class ='nav-text'>Tickets</div>
            					</div><?php }
                                if(isset($Privileges['Time']) && $Privileges['Time']['Group_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-timeline.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->History(3);?></div>
            							<div class ='nav-text'>Timeline</div>
            					</div><?php }
                                if(isset($Privileges['Violation']) && $Privileges['Violation']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-violations.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation(3);?></div>
            							<div class ='nav-text'>Violations</div>
            					</div><?php }
                                if(isset($Privileges['User']) && $Privileges['User']['User_Privilege'] >= 4){
            					?><div class='nav-tab col-lg-1 col-md-2 col-3' onClick="someFunction(this,'unit-workers.php?ID=<?php echo $_GET['ID'];?>');">
            							<div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Users(3);?></div>
            							<div class ='nav-text'>Workers</div>
            					</div><?php }?>
            				</div>
            			</div>
                    </div>
                </div>
                <div class='card card-primary col-4'>
                    <div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Info( 1 );?> Information</div>
                    <div class='card-body bg-dark'>
                        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
                            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> ID:</div>
                            <div class='col-xs-8'><?php echo strlen($Unit['State'])>0 ? $Unit['State'] : "&nbsp;";?></div>
                        </div>
                        <div class='row'>
                            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Name:</div>
                            <div class='col-xs-8'><?php echo strlen($Unit['Unit'])>0 ? $Unit['Unit'] : "&nbsp;";?></div>
                        </div>
                        <div class='row'>
                            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Type:</div>
                            <div class='col-xs-8'><?php echo strlen($Unit['Type'])>0 ? $Unit['Type'] : "&nbsp;";?></div>
                        </div>
                        <div class='row'>
                            <?php if(isset($My_Privileges['Invoice']) && $My_Privileges['Invoice']['Other_Privilege'] >= 4){?><div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Collection(1);?> Price:</div>
                            <div class='col-xs-8'><?php echo strlen($Unit['Price'])>0 ? money_format('%.2n',$Unit['Price']): "&nbsp;";?></div><?php }?>
                        </div>
                        <div class='row'>
                            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Note(1);?> Notes:</div>
                            <div class='col-xs-8'><?php echo strlen($Unit['Description'])>0 ? $Unit['Description'] : "&nbsp;";?></div>
                        </div>
                    </div>
                </div>
                <div class='card card-primary col-4'>
                    <div class='card-heading'><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Location</div>
                    <div class='card-body'>
                        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
                            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Location:</div>
                            <div class='col-xs-8'><?php echo strlen($Unit['Location_Tag'])>0 ? $Unit['Location_Tag'] : "&nbsp;";?></div>
                        </div>
                        <div class='row'>
                            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Street:</div>
                            <div class='col-xs-8'><?php echo strlen($Unit['Street'])>0 ? $Unit['Street'] : "&nbsp;";?></div>
                        </div>
                        <div class='row'>
                            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
                            <div class='col-xs-8'><?php echo strlen($Unit['City'])>0 ? $Unit['City'] : "&nbsp;";?></div>
                        </div>
                        <div class='row'>
                            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State:</div>
                            <div class='col-xs-8'><?php echo strlen($Unit['Location_State'])>0 ? $Unit['Location_State'] : "&nbsp;";?></div>
                        </div>
                        <div class='row'>
                            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
                            <div class='col-xs-8'><?php echo strlen($Unit['Zip'])>0 ? $Unit['Zip'] : "&nbsp;";?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	</div>
</body>
</html>
<?php
	}
} else {?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
