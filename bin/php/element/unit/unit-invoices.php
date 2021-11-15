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
        && 	$Privileges['Unit']['User_Privilege'] >= 4 
        && 	$Privileges['Unit']['Group_Privilege'] >= 4 
        && 	$Privileges['Unit']['Other_Privilege'] >= 4){
        		$Privileged = TRUE;
    }
    if(     !isset( $Connection[ 'ID' ] )  
        ||  !$Privileged 
        ||  !is_numeric( $_GET[ 'ID' ] ) ){
            /*?><html><head><script>document.location.href="../login.php?Forward=unit<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php*/ }
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
?>
	<div class="panel panel-primary" style='margin-bottom:0px;'>
		<div class="panel-body">
			<div class="row">
				<div class='col-md-12' >
					<div class="panel panel-primary">
						<!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Invoices Table</h3></div>-->
						<div class="panel-body white-background BankGothic shadow">
							<table id='Table_Invoices' class='display' cellspacing='0' width='100%' style="font-size: 8px">
								<thead>
									<th title='ID of the Invoice'>ID</th>
									<th title='Job of the Invoice'>Job</th>
									<th title='Location of the Invoice'>Location</th>
									<th title='Date of the Invoice'>Date</th>
									<th title='Description of the Invoice'>Description</th>
									<th title='Total Amount of Invoice'>Amount</th>
								</thead>
							</table>
						</div>
					</div>
				</div>
				<script>
				var Editor_Invoices = new $.fn.dataTable.Editor({
					ajax: "php/get/Locations_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
					table: "#Table_Invoices",
					idSrc: "ID",
					fields : [{
						label: "ID",
						name: "ID"
					},{
						label: "Job",
						name: "Job",
						visible: false
						
					},{
						label: "Location",
						name: "Location"
					},{
						label:"Date",
						name: "fDate",
						type: "datetime",
						def:  function(){return new Date();}
					},{
						label:"Description",
						name:"Description",
						type:"textarea"
					},{
						label: "Amount",
						name: "Total"
					}]
				});
				var Table_Invoices = $('#Table_Invoices').DataTable( {
					"ajax": "bin/php/get/Invoices_by_Location.php?ID=<?php echo $_GET['ID'];?>",
					"columns": [
						{ 
							"data" : "ID" 
						},{ 
							"data" : "Job",
							"visible": false
						},{ 
							"data" : "Location"
						},{ 
							"data" : "fDate",
						 	render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
						},{ 
							"data" : "Description"
						},{ 
							"data" : "Total",
							render:function(data){return "$" + parseFloat(data).toLocaleString();}
						}
					],
					"buttons":[/*
						'copy',
						'csv',
						'excel',
						'pdf',
						'print',
						{ extend: "create", editor: Editor_Invoices },
						{ extend: "edit",   editor: Editor_Invoices },
						{ extend: "remove", editor: Editor_Invoices },
						{ text:"View",
						  action:function(e,dt,node,config){
							  if($("#Table_Invoices tbody tr.selected").length > 0){
							  	document.location.href = 'invoice.php?ID=' + $("#Table_Invoices tbody tr.selected td:first-child").html();
							  }
						  }
						}
					*/],
					"searching": false,
					<?php require('../../../js/datatableOptions.php');?>
				} );
				</script>
			</div>
		</div>
	</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>