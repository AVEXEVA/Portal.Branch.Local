<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $Connection = sqlsrv_query(
        $NEI,
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
    $User = sqlsrv_query(
        $NEI,
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
    $r = sqlsrv_query(
        $NEI,
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
        $r = sqlsrv_query(  
            $NEI,
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
        sqlsrv_query(
            $NEI,
            "   INSERT INTO Activity([User], [Date], [Page]) 
                VALUES(?,?,?);",
            array(
                $_SESSION['User'],
                date('Y-m-d H:i:s'), 
                'location-feed.php?ID=' . $_GET[ 'ID' ]
            )
        );

        $r = sqlsrv_query($NEI,
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
                    Loc.Owner            AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Balance AS Customer_Balance,
                    Terr.Name            AS Territory_Domain/*,
                    Sum(SELECT Location.ID FROM Loc AS Location WHERE Location.Owner='Loc.Owner') AS Customer_Locations*/
            FROM    Loc
                    LEFT JOIN Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN Route        ON Loc.Route  = Route.ID
                    LEFT JOIN Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN Terr         		   ON Terr.ID    = Loc.Terr
            WHERE
                    Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;?>
<div class="panel panel-primary" style='margin-bottom:0px;'>
	<div class="panel-body">
		<div class="panel panel-primary">
			<div class="panel-heading"><h4><?php \singleton\fontawesome::getInstance( )->Modernization();?>Active Modernizations</h4></div>
			<div class='panel-body  BankGothic shadow'>
				<table id='Table_Active_Modernizations' class='display' cellspacing='0' width='100%'>
					<thead>
						<th>ID</th>
						<th>Name</th>
						<th>Date</th>
					</thead>
				</table>
			</div>
		</div>
		<div class="panel panel-primary">
			<div class="panel-heading"><h4><?php \singleton\fontawesome::getInstance( )->Modernization();?>Modernization Job Items</h4></div>
			<div class='panel-body  BankGothic shadow'>
				<table id='Table_Modernization_Job_Items' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
					<thead>
						<th>Job</th>
						<th>Item</th>
						<th>Date</th>
					</thead>
				</table>
			</div>
		</div>
	</div>
</div>
	<style>
		.border-seperate {
			border-bottom:3px solid #333333;
		}
	</style>

	<script>
		var Table_Active_Modernizations = $('#Table_Active_Modernizations').DataTable( {
			"ajax": "cgi-bin/php/reports/Active_Modernizations_by_Location.php?ID=<?php echo $_GET['ID'];?>",
			"columns": [
				{ "data": "ID"},
				{ "data": "Name" },
				{ "data": "Date",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
			],
			"order": [[1, 'asc']],
			"language":{
				"loadingRecords":""
			},
			"initComplete":function(){},
            "paging":false,
            "searching":false
		} );
        function hrefJobs(){hrefRow("Table_Active_Modernizations","job");}
        $(document).ready(function(){
            $("Table#Table_Active_Modernizations").on("draw.dt",function(){hrefJobs();});
        });
		</script>
		<script>
		var Table_Modernization_Job_Items = $('#Table_Modernization_Job_Items').DataTable( {
			"ajax": "cgi-bin/php/reports/Modernization_Job_Items_by_Location.php?ID=<?php echo $_GET['ID'];?>",
			"columns": [
				{ "data": "Job"},
				{ "data": "Item" },
				{ "data": "Date",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
			],
			"order": [[1, 'asc']],
			"language":{
				"loadingRecords":""
			},
			"initComplete":function(){},
            "paging":false,
            "searching":false
		} );
		</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
