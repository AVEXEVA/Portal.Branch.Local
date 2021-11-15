<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset($_SESSION[ 'User' ], $_SESSION[ 'Hash' ] ) ){
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
            "   SELECT  Count( Ticket.Count ) AS Count 
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
        ||  !$Privileged 
        ||  !is_numeric( $_GET[ 'ID' ] ) ){
            ?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $r = $database->query(null,
          " SELECT  TOP 1
                    Elev.ID,
                    Elev.Unit           AS Building_ID,
                    Elev.State          AS City_ID,
                    Elev.Cat            AS Category,
                    Elev.Type           AS Type,
                    Elev.Building       AS Building,
                    Elev.Since          AS Since,
                    Elev.Last           AS Last,
                    Elev.Price          AS Price,
                    Elev.fDesc          AS Description,
                    Loc.Loc             AS Location_ID,
                    Loc.ID              AS Name,
                    Loc.Tag             AS Tag,
                    Loc.Tag             AS Location_Tag,
                    Loc.Address         AS Street,
                    Loc.City            AS City,
                    Loc.State           AS Location_State,
                    Loc.Zip             AS Zip,
                    Loc.Route           AS Route,
                    Zone.Name           AS Zone,
                    Owner.ID            AS Customer_ID,
                    OwnerRol.Name       AS Customer_Name,
                    OwnerRol.Contact    AS Customer_Contact,
                    OwnerRol.Address    AS Customer_Street,
                    OwnerRol.City       AS Customer_City,
                    OwnerRol.State      AS Customer_State,
                    Emp.ID              AS Route_Mechanic_ID,
                    Emp.fFirst          AS Route_Mechanic_First_Name,
                    Emp.Last            AS Route_Mechanic_Last_Name
            FROM    Elev
                    LEFT JOIN Loc               ON Elev.Loc     = Loc.Loc
                    LEFT JOIN Zone              ON Loc.Zone     = Zone.ID
                    LEFT JOIN OwnerWithRol      ON Loc.Owner    = OwnerWithRol.ID
                    LEFT JOIN Route             ON Loc.Route    = Route.ID
                    LEFT JOIN Emp               ON Route.Mech   = Emp.fWork
                    LEFT JOIN Owner             ON Loc.Owner    = Owner.ID 
                    LEFT JOIN Rol AS OwnerRol   ON OwnerRol.ID  = Owner.Rol
            WHERE   Elev.ID = ?;",
          array(
            $_GET[ 'ID' ]
          )
        );
        $Unit = sqlsrv_fetch_array($r);
        $unit = $Unit;
        $data = $Unit;
        $r2 = $database->query(null,"
            SELECT *
            FROM   ElevTItem
            WHERE  ElevTItem.ElevT    = 1
                   AND ElevTItem.Elev = ?
        ;",array($_GET['ID']));
        if($r2){while($array = sqlsrv_fetch_array($r2)){$Unit[$array['fDesc']] = $array['Value'];}}
?>

		<div class='panel-body white-background shadower'>
			<table id='Table_Open_Violations' class='display' cellspacing='0' width='100%'>
				<thead>
					<th>Violation</th>
					<th>Status</th>
					<th>Due Date</th>
				</thead>
			</table>	
		</div>
		<script>
		var Table_Open_Violations = $('#Table_Open_Violations').DataTable( {
			"ajax": {
				"url":"bin/php/reports/Due_Violations_by_Unit.php?ID=<?php echo $_GET['ID'];?>",
				"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
			},
			"columns": [
				{ "data": "Name"},
				{ "data": "Status"},
				{ "data": "Due_Date"}
			],
			"order": [[1, 'asc']],
			"language":{"loadingRecords":""},
			//"paging":false,
			"searching":false,
			"info":false,
			"lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
			"initComplete":function(){
			}
		} );
		function hrefViolation(){hrefRow("Table_Open_Violations","violation");}
		$("Table#Table_Open_Violations").on("draw.dt",function(){hrefViolation();});
		
		</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>