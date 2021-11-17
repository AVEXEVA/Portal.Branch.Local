<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
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
        "   SELECT  Privilege.Access, 
                    Privilege.Owner, 
                    Privilege.Group, 
                    Privilege.Other
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    while($Privilege = sqlsrv_fetch_array($r)){$Privileges[$Privilege['Access']] = $Privilege;}
    $Privileged = FALSE;
    if( isset($Privileges['Location']) 
        && $Privileges['Location']['Owner'] >= 4 
        && $Privileges['Location']['Group'] >= 4 
        && $Privileges['Location']['Other'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Location']['Owner'] >= 4 && is_numeric($_GET['ID'])){
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
        ||  !is_numeric( $_GET[ 'ID' ] ) ){
            ?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $database->query(
            null,
            "   INSERT INTO Activity([User], [Date], [Page]) 
                VALUES(?,?,?);",
            array(
                $_SESSION['User'],
                date('Y-m-d H:i:s'), 
                'location-feed.php?ID=' . $_GET[ 'ID' ]
            )
        );
        $ID = $_GET['ID'];
        $r = $database->query(null,
            "SELECT TOP 1
                    Loc.Loc              AS Location_ID,
                    Loc.ID               AS Name,
                    Loc.Tag              AS Tag,
                    Loc.Address          AS Street,
                    Loc.City             AS City,
                    Loc.State            AS State,
                    Loc.Zip              AS Zip,
                    Loc.Balance          as Location_Balance,
                    Zone.Name            AS Zone,
                    Loc.Route            AS Route_ID,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Rol.Phone            AS Route_Mechanic_Phone_Number,
                    Portal.Email         AS Route_Mechanic_Email,
                    Loc.Owner            AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Balance AS Customer_Balance,
                    OwnerWithROl.Address AS Customer_Street,
                    OwnerWithRol.City    AS Customer_City,
                    OwnerWithRol.State   AS Customer_State,
                    OwnerWithRol.Zip     AS Customer_Zip,
                    OwnerWithRol.Contact AS Customer_Contact,
                    Terr.Name            AS Territory_Domain/*,
                    Sum(SELECT Location.ID FROM Loc AS Location WHERE Location.Owner='Loc.Owner') AS Customer_Locations*/
            FROM    Loc
                    LEFT JOIN Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN Route        ON Loc.Route  = Route.ID
                    LEFT JOIN Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN Terr         ON Terr.ID    = Loc.Terr
                    LEFT JOIN Rol          ON Emp.Rol    = Rol.ID
                    LEFT JOIN Portal    ON Emp.ID     = Portal.Branch_ID AND Portal.Branch = 'Nouveau Elevator'
            WHERE
                    Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;
        $job_result = $database->query(null,"
            SELECT Job.ID AS ID
            FROM   Job
            WHERE  Job.Loc = ?
        ;",array($_GET['ID']));
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
			<div class="panel panel-primary">
				<!--<div class="panel-heading"><h4><?php \singleton\fontawesome::getInstance( )->Maintenance();?> Maintenance</h4></div>-->
				<div class='panel-body '>
					<div class='row' style='font-size:16px;padding:5px;'>
						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Route();?> Route:</div>
                        <div class='col-xs-8'><?php if($My_Privileges['Route']['Other'] >= 4 || $My_User['ID'] == $Location['Route_Mechanic_ID']){?><a href="route.php?ID=<?php echo $Location['Route_ID'];?>"><?php }?><?php echo proper($Location["Route_Mechanic_First_Name"] . " " . $Location["Route_Mechanic_Last_Name"]);?><?php if($My_Privileges['Route']['Other'] >= 4 || $My_User['ID'] == $Location['Route_Mechanic_ID']){?></a><?php }?>
						</div>
                        <?php if(isset($Location['Route_Mechanic_Phone_Number']) && strlen($Location['Route_Mechanic_Phone_Number']) > 0){?><div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Phone();?> Phone:</div>
						<div class='col-xs-8'><?php echo $Location['Route_Mechanic_Phone_Number'];?></div><?php }?>
						<?php if(strlen($Location['Route_Mechanic_Email']) > 0){?><div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Email();?> Email:</div>
                        <div class='col-xs-8'><?php echo $Location['Route_Mechanic_Email'];?></div><?php }?>
						<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Division();?> Division:</div>
                        <div class='col-xs-8'><?php if($My_Privileges['Ticket']['Other'] >= 4){?><a href="dispatch.php?Supervisors=Division%201&Mechanics=undefined&Start_Date=07/13/2017&End_Date=07/13/2017"><?php }?><?php echo proper($Location["Zone"]);?><?php if($My_Privileges['Ticket']['Other'] >= 4){?></a><?php }?></div>
					</div>
				</div>
                <!--<div class='panel-heading'><h4>Monthly Maintenance Due</h4></div>-->
                <div class="panel-body" style='border-top:3px dashed black;'>
                    <table id='Table_Maintenances' class='display' cellspacing='0' width='100%' style=''>
                        <thead>
                            <th>ID</th>
                            <th title='Location'>Location</th>
                            <th title='Unit'>Unit Name</th>
                            <th title='State'>Unit State</th>
                            <th title="Last Maintenance">Last Maintenance</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
			</div>
	<script>
        var Table_Maintenances = $('#Table_Maintenances').DataTable( {
                "ajax": "bin/php/reports/Maintenances_by_Location.php?ID=<?php echo $_GET['ID'];?>",
                "columns": [
                    {
                        "data": "ID",
                        "className":"hidden"
                    },{
                        "data": "Location"
                    },{
                        "data": "Unit"
                    },{
                        "data": "State"
                    },{
                        "data": "Last_Date",
                        "render": function(data){
                            if(data === null || typeof data === 'undefined'){return '';}
                            else {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
                    }
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "initComplete":function(){},
                "searching":false,
                "paging":false,
                "scrollY" : "300px",
            } );
            function hrefUnits(){hrefRow("Table_Maintenances","unit");}
            $("Table#Table_Maintenances").on("draw.dt",function(){hrefUnits();});
	</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
