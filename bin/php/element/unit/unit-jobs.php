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
    if( isset($Privileges['Unit']) 
        && $Privileges['Unit']['Owner'] >= 4 
        && $Privileges['Unit']['Group'] >= 4 
        && $Privileges['Unit']['Other'] >= 4){$Privileged = TRUE;}
    elseif($Privileges['Unit']['Owner'] >= 4 && is_numeric($_GET['ID'])){
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
        ||  !$Privileged 
        ||  !is_numeric( $_GET[ 'ID' ] ) ){
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
                    Loc.ID              AS Name,
                    Loc.Tag             AS Tag,
                    Loc.Tag             AS Location_Tag,
                    Loc.Address         AS Street,
                    Loc.City            AS City,
                    Loc.State           AS Location_State,
                    Loc.Zip             AS Zip,
                    Loc.Route           AS Route,
                    Zone.Name           AS Zone,
                    OwnerWithRol.Name   AS Customer_Name,
                    OwnerWithRol.ID     AS Customer_ID,
            				OwnerWithRol.Contact AS Customer_Contact,
            				OwnerWithRol.Address AS Customer_Street,
            				OwnerWithRol.City 	AS Customer_City,
            				OwnerWithRol.State 	AS Customer_State,
                    Emp.ID AS Route_Mechanic_ID,
                    Emp.fFirst AS Route_Mechanic_First_Name,
                    Emp.Last AS Route_Mechanic_Last_Name
            FROM    Elev
                    LEFT JOIN Loc           ON Elev.Loc = Loc.Loc
                    LEFT JOIN Zone          ON Loc.Zone = Zone.ID
                    LEFT JOIN OwnerWithRol  ON Loc.Owner = OwnerWithRol.ID
                    LEFT JOIN Route ON Loc.Route = Route.ID
                    LEFT JOIN Emp ON Route.Mech = Emp.fWork
            WHERE   Elev.ID = ?;",
          array(
            $_GET[ 'ID' ]
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
?><div class='panel panel-primary'>
	<div class='panel-heading'><?php \singleton\fontawesome::getInstance( )->Job( 1 );?> Jobs</div>
	<div class='panel-body'>
		<table id='Table_Jobs' class='display' cellspacing='0' width='100%'>
			<thead>
				<th>ID</th>
				<th>Name</th>
				<th>Type</th>
				<th>Status</th>
			</thead>
		</table>	
	</div>
	<script>
		var Table_Jobs = $('#Table_Jobs').DataTable( {
			scrollY        : '600px',
			scrollCollapse : true,
			lengthChange   : false,
			paging         : true,
			dom            : 'tp',
			select         : true,
			ajax           : 'bin/php/get/Jobs_by_Unit.php?ID=<?php echo $_GET['ID'];?>',
			columns: [
				{ 
					data : 'ID'
				},{ 
					data : 'Name' 
				},{ 
					data : 'Type'
				},{ 
					data: 'Status'
				}
			],
			order : [ [3, 'desc'] ],
			language : {
				loadingRecords : ''
			},
			initComplete : function( ){ }
		} );
		function hrefJobs(){hrefRow('Table_Jobs','job');}
		$('Table#Table_Jobs').on('draw.dt',function(){hrefJobs();});
	</script>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>