<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $result = $database->query(
        null,
        "   SELECT  * 
            FROM    Connection 
            WHERE       Connector = ? 
                    AND Hash = ?;",
        array(
            $_SESSION['User'],
            $_SESSION['Hash']
        )
    );
    $Connection = sqlsrv_fetch_array($result);
    //User
    $result = $database->query(
        null,
        "   SELECT  *, 
                    fFirst AS First_Name, 
                    Last as Last_Name 
            FROM    Emp 
            WHERE   ID= ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $User   = sqlsrv_fetch_array( $result );
    //Privileges
    $result = $database->query(null,
        "   SELECT  Privilege.*
            FROM    Privilege
            WHERE   Privilege.User_ID = ?;",
        array(
            $_SESSION[ 'User' ]
        )
    );
    $Privileges = array();
    $Privileged = false;
    while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
    if(     isset($Privileges['Customer']) 
        &&  $Privileges[ 'Customer' ][ 'User_Privilege' ]  >= 4 
        &&  $Privileges[ 'Customer' ][ 'Group_Privilege' ] >= 4 
        &&  $Privileges[ 'Customer' ][ 'Other_Privilege' ] >= 4){
                $Privileged = true;}
    if(     !isset($Connection['ID'])  
        ||  !is_numeric($_GET['ID']) 
        || 	!$Privileged 
    ){ ?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $database->query(
            null,
            "   INSERT INTO Activity( [User], [Date], [Page] ) VALUES( ?, ?, ? );",
            array(
                $_SESSION['User'],
                date("Y-m-d H:i:s"), 
                "customer/tickets.php"
            )
        );
        $result = $database->query(
            null,
            "   SELECT  Customer.*                    
                FROM    (
                            SELECT  Owner.ID    AS ID,
                                    Rol.Name    AS Name,
                                    Rol.Address AS Street,
                                    Rol.City    AS City,
                                    Rol.State   AS State, 
                                    Rol.Zip     AS Zip,
                                    Owner.Status  AS Status,
                                    Rol.Website AS Website
                            FROM    Owner 
                                    LEFT JOIN Rol ON Owner.Rol = Rol.ID
                    ) AS Customer
                WHERE   Customer.ID = ?;",
            array(
                $_GET['ID']
            )
        );
        $Customer = sqlsrv_fetch_array($result);
?><div class="panel panel-primary">
	<div class='panel-heading'><?php \singleton\fontawesome::getInstance( )->Ticket( 1 );?> Tickets</div>
	<div class="panel-body">
		<table id='Table_Tickets' class='display' cellspacing='0' width='100%'>
			<thead>
				<th>ID</th>
				<th>Location</th>
				<th>Job</th>
				<th>Mechanic</th>
				<th>From</th>
				<th>To</th>                                            
				<th>Hours</th>
			</thead>
		</table>
	</div>
	<script>
		var Table_Tickets = $('#Table_Tickets').DataTable( {
			"order": [[1, 'asc']],
            "language":{"loadingRecords":""},
            "searching":false,
            "info":false,
            "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
            "initComplete":function(){},
            "paging":false,
            "searching":false,
			"ajax": {
				"url":"bin/php/get/Tickets_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
				"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
			},
			"columns": [
				{ 
					"data": "ID" 
				},{ 
					"data": "Location"
				},{
					"data": "Job_Description"
				},{ 
					"data": "Mechanic"
				},{ 
					"data": "Worked",
					render: function(data){if(data != null){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}else{return null;}}
				},{ 
					"data": "Status"
				},{ 
					"data": "Total",
					"defaultContent":"0"
				},{
					"data":"Unit_State",
					"visible":false,
					"searchable":true
				},{
					"data":"Unit_Label",
					"visible":false,
					"searchable":true
				},{ 
					"data": "Street",
					"visible":false
				},{ 
					"data": "City",
					"visible":false
				},{ 
					"data": "State",
					"visible":false
				},{ 
					"data": "Zip",
					"visible":false
				},{ 
					"data": "Route",
					"visible":false
				},{ 
					"data": "Division",
					"visible":false
				},{ 
					"data": "Maintenance",
					"visible":false,
					"render":function(data){
					  if(data == '1'){return "Maintained";}
					  else {return "Not Maintained";}
				  	}
				},{
					"data":"Tags",
					"visible":false,
					"searchable":true
				}
			]
		} );
		function hrefTickets(){hrefRow("Table_Tickets","ticket");}
		$("Table#Table_Tickets").on("draw.dt",function(){hrefTickets();});
		</script>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>