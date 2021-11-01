<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $result = sqlsrv_query(
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
    $Connection = sqlsrv_fetch_array( $result );

    //User
    $result = sqlsrv_query(
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
    $User = sqlsrv_fetch_array( $result );

    //Privileges
    $result = sqlsrv_query(
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
    while($Privilege = sqlsrv_fetch_array($result)){$Privileges[$Privilege['Access_Table']] = $Privilege;}
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
                'location-information.php?ID=' . $_GET[ 'ID' ]
            )
        );
        $r = sqlsrv_query(
            $NEI,
            "SELECT TOP 1
                    Loc.Loc              AS Location_ID,
                    Loc.ID               AS Location_Name,
                    Loc.Tag              AS Location_Tag,
                    Loc.Address          AS Location_Street,
                    Loc.City             AS Location_City,
                    Loc.State            AS Location_State,
                    Loc.Zip              AS Location_Zip,
                    Loc.Balance          AS Location_Balance,
                    Loc.Latt             AS Latitude,
                    Loc.fLong            AS Longitude,
                    Zone.Name            AS Division,
                    Zone.ID              AS Division_ID,
                    Loc.Route            AS Route_ID,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Loc.Owner            AS Customer_ID,
                    OwnerRol.Name        AS Customer_Name,
                    OWner.Balance        AS Customer_Balance,
                    OwnerRol.Address     AS Customer_Street,
                    OwnerRol.City        AS Customer_City,
                    OwnerRol.State       AS Customer_State,
                    OwnerRol.Zip         AS Customer_Zip,
                    OwnerRol.Contact     AS Customer_Contact,
                    Terr.Name            AS Territory_Domain,
                    Terr.Name            AS Territory_Name,
                    Loc.Custom8          AS Resident_Mechanic,
                    Units.Count          AS Units
            FROM    Loc
                    LEFT JOIN Zone              ON Loc.Zone    = Zone.ID
                    LEFT JOIN Route             ON Loc.Route   = Route.ID
                    LEFT JOIN Emp               ON Route.Mech  = Emp.fWork
                    LEFT JOIN Owner             ON Owner.ID    = Loc.Owner
                    LEFT JOIN Rol AS OwnerRol   ON OwnerRol.ID = Owner.Rol
                    LEFT JOIN Terr              ON Terr.ID     = Loc.Terr
                    LEFT JOIN Rol               ON Emp.Rol     = Rol.ID
                    LEFT JOIN (
                        SELECT      Elev.Loc AS Location,
                                    Count( Elev.ID ) AS Count
                        FROM        Elev
                        GROUP BY    Elev.Loc
                    ) AS Units ON Units.Location = Loc.Loc
            WHERE   Loc.Loc = ?;",
            array(
                $_GET[ 'ID' ]
            )
        );
        $Location = sqlsrv_fetch_array($r);
?><style>table#Table_Tickets { font-size:12px; }</style>
<div class='panel panel-primary'>
	<div class='panel-heading'><h4><?php $Icons->Ticket( 1 );?> Tickets</h4></div>
	<div class='panel-body'>
		<table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
			<thead>
				<th>ID</th>
				<th>Date</th>
				<th>Worker</th>
				<th>Type</th>
			</thead>
		</table>
	</div>
</div>
<script>
var Table_Tickets = $('#Table_Tickets').DataTable( {
	ajax : {
		url     : 'cgi-bin/php/get/Tickets_by_Location-fork.php?ID=<?php echo $_GET['ID'];?>',
		dataSrc : function( json ){ 
			if( !json.data ){ json.data = [ ]; }
			return json.data;
		}
	},
	columns : [
		{
			data : 'ID'
		},{
			data : 'Worked'
		},{
			data : 'Mechanic_Name'
		},{
			data : 'Job_Type'
		}
	],
	order     : [[0, 'desc']],
    autoWidth : false,
    searching : false
} );
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
