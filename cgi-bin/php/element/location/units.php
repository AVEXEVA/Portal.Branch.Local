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
?><div class='panel panel-primary'>
	<div class='panel-heading'><h4><?php $Icons->Unit( 1 );?> Units</h4></div>
	<div class='panel-body'>
		<table id='Table_Units' class='display' cellspacing='0' width='100%' style='font-size:12px'>
			<thead><tr>
				<th title='ID'>ID</th>
				<th title='City ID'>City ID</th>
				<th title='Building ID'>Building ID</th>
				<th title='Type'>Type</th>
				<th title='Status'>Status</th>
			</tr></thead>
            <tfoot><tr>
                <th title='ID'><input type='text' name='ID' onChange='redraw();' /></th>
                <th title='City ID'><input type='text' name='City_ID' onChange='redraw();' /></th>
                <th title='Building ID'><input type='text' name='Building_ID' onChange='redraw();' /></th>
                <th title='Type'><input type='text' name='Type' onChange='redraw();' /></th>
                <th title='Status'><input type='text' name='Status' onChange='redraw();' /></th>
            </tr></tfoot>
		</table>
	</div>
</div>
<script>
	var Table_Units = $('#Table_Units').DataTable( {
        processing : true,
        serverSide : true,
        responsive : true,
		ajax      : {
            url : 'cgi-bin/php/get/Units_by_Location.php',
            data : function( d ){
                d = {
                    start : d.start,
                    length : d.length,
                    order : {
                        column : d.order[0].column,
                        dir : d.order[0].dir
                    }
                };
                d.Location = <?php echo $_GET[ 'ID' ];?>;
                d.ID = $('input[name="ID"]').val();
                d.City_ID = $('input[name="City_ID"]').val();
                d.Building_ID = $('input[name="Building_ID"]').val();
                d.Type = $('input[name="Type"]').val();
                d.Status = $('input[name="Status"]').val();
                return d; 
            }
        },
		columns   : [
			{
				data      : 'ID',
				className : 'hidden'
			},{
				data : 'State'
			},{
				data : 'Unit'
			},{
				data : 'Type'
			},{
				data : 'Status',
				render:function(data){
					switch(data){
						case 0:return 'Active';
						case 1:return 'Inactive';
						case 2:return 'Demolished';
						case 3:return 'XXX';
						case 4:return 'YYY';
						case 5:return 'ZZZ';
						case 6:return 'AAA';
						default:return 'Error';
					}
				}
			}
		],
        autoWidth : false,
		paging    :false,
		searching :false
	} );
	function hrefUnits( ) { hrefRow( 'Table_Units', 'unit' ); }
	$( 'Table#Table_Units' ).on( 'draw.dt', function( ){ hrefUnits( ); } );
    function redraw( ){ Table_Units.draw( { } ); }

</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
