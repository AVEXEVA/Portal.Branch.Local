<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
	//Connection
    $result = $database->query(
    	null,
    	"	SELECT 	* 
    		FROM 	Connection 
    		WHERE 		Connector = ? 
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
		"	SELECT 	*, 
					fFirst AS First_Name, 
					Last as Last_Name 
			FROM 	Emp 
			WHERE 	ID= ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$User   = sqlsrv_fetch_array( $result );
	//Privileges
	$result = $database->query(null,
		" 	SELECT 	Privilege.*
			FROM   	Privilege
			WHERE  	Privilege.User_ID = ?;",
		array(
			$_SESSION[ 'User' ]
		)
	);
	$Privileges = array();
	$Privileged = false;
	while( $Privilege = sqlsrv_fetch_array( $result ) ){ $Privileges[ $Privilege[ 'Access_Table' ] ] = $Privilege; }
	if(		isset($Privileges['Customer']) 
		&& 	$Privileges[ 'Customer' ][ 'User_Privilege' ]  >= 4 
		&& 	$Privileges[ 'Customer' ][ 'Group_Privilege' ] >= 4 
		&& 	$Privileges[ 'Customer' ][ 'Other_Privilege' ] >= 4){
				$Privileged = true;}
    if(		!isset($Connection['ID'])  
    	|| 	!is_numeric($_GET['ID']) 
    	|| !$Privileged 
    ){ ?><html><head><script>document.location.href="../login.php?Forward=customer<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
    	$database->query(
    		null,
    		"	INSERT INTO Activity( [User], [Date], [Page] ) VALUES( ?, ?, ? );",
    		array(
    			$_SESSION['User'],
    			date("Y-m-d H:i:s"), 
    			"customer.php"
    		)
    	);
        $result = $database->query(
        	null,
            "	SELECT 	Customer.*                    
            	FROM    (
            				SELECT 	Owner.ID    AS ID,
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
	<div class="panel-heading"><?php \singleton\fontawesome::getInstance( )->Location( 1 );?> Locations</div>
	<div class="panel-body">
		<table id='Table_Locations' class='display' cellspacing='0' width='100%'>
			<thead>
				<th title="Location's ID"></th>
				<th title="Location's Name State ID"></th>
				<th title="Location's Tag">Name</th>
				<th title="Location's Street"></th>
				<th title="Location's City"></th>
				<th title="Location's State"></th>
				<th title="Location's Zip"></th>
				<th title="Location's Route">Route</th>
				<th title="Location's Zone">Division</th>
				<th title="Location's Mainteniance"></th>
			</thead>
		</table>
	</div>
	<script>
		var Table_Locations = $('#Table_Locations').DataTable( {
			dom 	   : 'tlp',
		    responsive : true,
		    autoWidth : false,
			paging    : false,
			searching : false,
			"ajax": "bin/php/get/Locations_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
			"columns": [
				{ 
					"data": "ID",
					"className":"hidden"
				},{ 
					"data": "Name",
					"visible":false
				},{ 
					"data": "Tag"
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
					"data": "Route"
				},{ 
					"data": "Division"
				},{ 
					"data": "Maintenance",
				  	"render":function(data){
					  	if(data == '1'){return "Maintained";}
					  	else {return "Not Maintained";}
				  	},
					"visible":false
				}
			]
		} );
		function hrefLocations(){hrefRow("Table_Locations","location");}
		$("Table#Table_Locations").on("draw.dt",function(){hrefLocations();});
	</script>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>