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
    if( isset($Privileges['Location']) 
        && $Privileges['Location']['User_Privilege'] >= 4 
        && $Privileges['Location']['Group_Privilege'] >= 4 
        && $Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = $database->query(  
            null,
            "   SELECT  Count( Ticket.ID ) AS Count 
                FROM    (
                            SELECT  Ticket.ID,
                                    Ticket.Location,
                                    Ticket.Field,
                                    Sum( Ticket.Count ) AS Count
                            FROM (
                                (
                                    SELECT      TicketO.ID,
                                                TicketO.LID AS Location,
                                                TicketO.fWork AS Field,
                                                Count( TicketO.ID ) AS Count
                                    FROM        TicketO
                                    GROUP BY    TicketO.ID,
                                                TicketO.LID,
                                                TicketO.fWork
                                ) UNION ALL (
                                    SELECT      TicketD.ID,
                                                TicketD.Loc AS Location,
                                                TicketD.fWork AS Field, 
                                                Count( TicketD.ID ) AS Count
                                    FROM        TicketD
                                    GROUP BY    TicketD.ID,
                                                TicketD.Loc,
                                                TicketD.fWork
                                )
                            ) AS Ticket
                            GROUP BY    Ticket.ID,
                                        Ticket.Location,
                                        Ticket.Field
                        ) AS Ticket
                        LEFT JOIN Emp AS Employee ON Ticket.Field = Employee.fWork
                WHERE   Employee.ID = ?
                        AND Ticket.Location = ?;",
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
        ||  !$Privileged 
        ||  !isset( $_GET[ 'ID' ] )
        ||  !is_int( $_GET[ 'ID' ] ) ){
            ?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
            "SELECT TOP 1
                    Loc.Loc              AS ID,
                    Loc.ID               AS Name,
                    Loc.Tag              AS Tag,
                    Loc.Address          AS Street,
                    Loc.City             AS City,
                    Loc.State            AS State,
                    Loc.Zip              AS Zip,
                    Loc.Balance          as Balance,
                    Zone.Name            AS Zone,
                    Loc.Route            AS Route_ID,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Rol.Phone            AS Route_Mechanic_Phone_Number,
                    Loc.Owner            AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Balance AS Customer_Balance,
                    OwnerWithROl.Address AS Customer_Street,
                    OwnerWithRol.City    AS Customer_City,
                    OwnerWithRol.State   AS Customer_State,
                    OwnerWithRol.Zip     AS Customer_Zip,
                    OwnerWithRol.Contact AS Customer_Contact,
                    Terr.Name            AS Territory_Domain,
                    Terr.Name            AS Territory_Name,
                    Loc.Custom8          AS Resident_Mechanic
            FROM    Loc
                    LEFT JOIN Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN Route        ON Loc.Route  = Route.ID
                    LEFT JOIN Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN Terr         ON Terr.ID    = Loc.Terr
                    LEFT JOIN Rol          ON Emp.Rol    = Rol.ID
            WHERE	Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;
?>      <div>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Location(1);?> Name:</div>
			<div class='col-xs-8'><?php echo $Location['Location_Name'];?></div>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Tag:</div>
			<div class='col-xs-8'><?php echo $Location['Location_Tag'];?></div>
        </div>
        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Address(1);?> Street:</div>
			<div class='col-xs-8'><?php echo strlen($Location['Location_Street']) ? $Location['Location_Street'] : "&nbsp;";?></div>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> City:</div>
			<div class='col-xs-8'><?php echo strlen($Location['Location_City']) ? $Location['Location_City'] : "&nbsp;";?></div>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> State:</div>
			<div class='col-xs-8'><?php echo strlen($Location['Location_State']) ? $Location['Location_State'] : "&nbsp;";?></div>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Blank(1);?> Zip:</div>
			<div class='col-xs-8'><?php echo strlen($Location['Location_Zip']) ? $Location['Location_Zip'] : "&nbsp;";?></div>
        </div>
        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Territory(1);?> Territory:</div>
            <div class='col-xs-8'><?php echo isset($Location['Territory_Name']) && $Location['Territory_Name'] != '' ? $Location['Territory_Name'] : "&nbsp;";?></div>
			<?php if(isset($My_Privileges['Invoice']) && $My_Privileges['Invoice']['Other_Privilege'] >= 4){?><div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Collection(1);?> Balance:</div>
            <div class='col-xs-8'><?php echo isset($Location['Location_Balance']) && $Location['Location_Balance'] != '' ? money_format('%.2n',$Location['Location_Balance']) : "&nbsp;";?></div><?php }?>
        </div>
        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Route();?> Route:</div>
            <div class='col-xs-8'><?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Location['Route_Mechanic_ID']){?><a href="route.php?ID=<?php echo $Location['Route_ID'];?>"><?php }?><?php echo proper($Location["Route_Mechanic_First_Name"] . " " . $Location["Route_Mechanic_Last_Name"]);?><?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Location['Route_Mechanic_ID']){?></a><?php }?>
			</div>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Resident(1);?> Resident:</div>
            <div class='col-xs-8'><?php echo isset($Location['Resident_Mechanic']) && $Location['Resident_Mechanic'] != '' ? proper($Location['Resident_Mechanic']) : "No";?></div>
            <?php if(isset($Location['Route_Mechanic_Phone_Number']) && strlen($Location['Route_Mechanic_Phone_Number']) > 0){?>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Phone();?> Phone:</div>
			<?php $number = $Location['Route_Mechanic_Phone_Number'];?>
			<div class='col-xs-8'><a href="tel:<?php echo $number;?>"><?php echo $number;?></a></div><?php }?>
			<?php /*<?php if(strlen($Location['Route_Mechanic_Email']) > 0){?><div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Email(1);?> Email:</div>
            <div class='col-xs-8'><a href="mailto:<?php echo $Location['Route_Mechanic_Email'];?>"><?php echo $Location['Route_Mechanic_Email'];?></a></div><?php }?>*/?>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Division(1);?> Division:</div>
            <div class='col-xs-8'><?php if($My_Privileges['Ticket']['Other_Privilege'] >= 4){?><a href="dispatch.php?Supervisors=Division%201&Mechanics=undefined&Start_Date=07/13/2017&End_Date=07/13/2017"><?php }?><?php echo proper($Location["Zone"]);?><?php if($My_Privileges['Ticket']['Other_Privilege'] >= 4){?></a><?php }?></div>
		</div>
        <div class='row shadower' style='padding-top:10px;padding-bottom:10px;'>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Unit(1);?> Units</div>
            <div class='col-xs-8'><?php
                $r = $database->query(null,"SELECT Count(ID) AS Count_of_Elevators FROM Elev WHERE Loc='{$_GET['ID']}';");
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Elevators']) : 0;
            ?></div>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Job(1);?> Jobs</div>
            <div class='col-xs-8'><?php
                $r = $database->query(null,"SELECT Count(ID) AS Count_of_Jobs FROM Job WHERE Loc='{$_GET['ID']}' ;");
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;
            ?></div>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Violation(1);?> Violations</div>
            <div class='col-xs-8'><?php
                $r = $database->query(null,"SELECT Count(ID) AS Count_of_Jobs FROM Violation WHERE Loc='{$_GET['ID']}';");
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Jobs']) : 0;
            ?></div>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Ticket(1);?> Tickets</div>
            <div class='col-xs-8'><?php
                $r = $database->query(null,"
                    SELECT Count(Tickets.ID) AS Count_of_Tickets
                    FROM   (
                                (SELECT ID FROM TicketO WHERE TicketO.LID = ?)
                                UNION ALL
                                (SELECT ID FROM TicketD WHERE TicketD.Loc = ?)
                                UNION ALL
                                (SELECT ID FROM TicketDArchive WHERE TicketDArchive.Loc = ?)
                            ) AS Tickets
                ;",array($_GET['ID'],$_GET['ID'],$_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;
            ?></div>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Proposal(1);?> Proposals</div>
            <div class='col-xs-8'><?php
                $r = $database->query(null,"
                    SELECT Count(Estimate.ID) AS Count_of_Tickets
                    FROM   Estimate
                    WHERE  Estimate.LocID = ?
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Tickets']) : 0;
            ?></div>
            <?php if(isset($My_Privileges['Invoice']) && $My_Privileges['Invoice']['Other'] >= 4){?>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Invoice(1);?>Collections</div>
            <div class='col-xs-8'><?php
                $r = $database->query(null,"
                    SELECT Count(Ref) AS Count_of_Invoices
                    FROM   OpenAR
                    WHERE  Loc='{$_GET['ID']}' AND Invoice.Status = 1;
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Invoices']) : 0;
            ?></div><?php }?>
			<?php if(isset($My_Privileges['Legal']) && $My_Privileges['Legal'] >=4 ) {?>
            <div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Legal(1);?> Lawsuits</div>
            <div class='col-xs-8'><?php
                $r = $database->query(null,"
                    SELECT Count(ID) AS Count_of_Legal_Jobs
                    FROM   Job
                    WHERE  Job.Loc = ?
                           AND (Job.Type = 9
                             OR Job.Type = 12)
                ;",array($_GET['ID']));
                echo $r ? number_format(sqlsrv_fetch_array($r)['Count_of_Legal_Jobs']) : 0;

            ?></div>


			<?php }?>
        </div>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
