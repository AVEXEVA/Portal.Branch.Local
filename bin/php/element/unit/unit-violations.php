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
?><div class="panel panel-primary" style='margin-bottom:0px;'>
	<div class="panel-body">
		<div class="row">
			<div class='col-md-12' >
				<div class="panel panel-primary">
					<!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Violations Table</h3></div>-->
					<div class="panel-body white-background BankGothic shadow">
						<table id='Table_Violations' class='display' cellspacing='0' width='100%' style="font-size: 8px">
							<thead>
								<th title='ID of the Violation'>ID</th>
								<th title='Name of the Violation'>Name</th>
								<th title="Date of the Violation">Date</th>
								<th title='Status of the Violation'>Status</th>
								<th title='Description of the Violation'>Description</th>
							</thead>
						</table>
					</div>
				</div>
			</div>
			<script>
			var Editor_Violations = new $.fn.dataTable.Editor({
				ajax: "php/get/Locations_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
				table: "#Table_Violations",
				idSrc: "ID",
				fields : [{
					label: "ID",
					name: "ID"
				},{
					label: "Name",
					name: "Name"
				},{
					label: "Date",
					name: "Date",
					type: "datetime",
					def:function(){return new Date();}
				},{
					label: "Status",
					name: "Status",
					type: "select",
					options: [<?php
						$r = $database->query(null,"
							SELECT   VioStatus.Type,
								     VioStatus.ID
							FROM     VioStatus
							ORDER BY VioStatus.Type ASC
						;");
						$Types = array();
						if($r){while($Type = sqlsrv_fetch_array($r)){$Types[] = '{' . "label: '{$Type['Type']}', value:'{$Type['ID']}'" . '}';}}
						echo implode(",",$Types);
					?>]
				},{
					label: "Description",
					name: "Description",
					type:"textarea"
				}]
			});
			Editor_Violations.field('ID').disable();
			var Table_Violations = $('#Table_Violations').DataTable( {
				"ajax": {
					"url":"bin/php/get/Violations_by_Location.php?ID=<?php echo $_GET['ID'];?>",
					"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
				},
				"columns": [
					{ 
						"data": "ID" 
					},{ 
						"data": "Name"
					},{ 
						"data": "Date",
					 	render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
					},{ 
						"data": "Status"
					},{ 
						"data": "Description"
					}
				],
				"buttons":[/*
					'copy',
					'csv',
					'excel',
					'pdf',
					'print',
					{ extend: "create", editor: Editor_Violations },
					{ extend: "edit",   editor: Editor_Violations },
					{ extend: "remove", editor: Editor_Violations },
					{ text:"View",
					  action:function(e,dt,node,config){
						  document.location.href = 'job.php?ID=' + $("#Table_Violations tbody tr.selected td:first-child").html();
					  }
					}
				*/],
				<?php require('../../../js/datatableOptions.php');?>
			} );
				function hrefViolations(){hrefRow("Table_Violations","violation");}
				$("Table#Table_Violations").on("draw.dt",function(){hrefViolations();});
				</script>

			</script>
			<?php /*<div class='col-md-12'>
				<div class="panel panel-primary">
					<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> </h3></div>
					<div class="panel-body white-background BankGothic shadow">
					</div>
				</div>
			</div>*/?>
		</div>
	</div>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>