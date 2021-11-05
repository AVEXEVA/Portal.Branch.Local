<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    //Connection
    $result = sqlsrv_query(
        $NEI,
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
    $result = sqlsrv_query(
        $NEI,
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
    $result = sqlsrv_query($NEI,
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
        sqlsrv_query(
            $NEI,
            "   INSERT INTO Activity( [User], [Date], [Page] ) VALUES( ?, ?, ? );",
            array(
                $_SESSION['User'],
                date("Y-m-d H:i:s"), 
                "customer/tickets.php"
            )
        );
        $result = sqlsrv_query(
            $NEI,
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
	<div class='panel-heading'><?php $Icons->Unit( 1 );?> Units</div>
	<div class="panel-body">
		<table id='Table_Units' class='display' cellspacing='0' width='100%' overflow-x: scroll style="font-size: 12px";>
			<thead>
				<th title="Unit's ID">ID</th>
				<th title='Unit State ID'>State</th>
				<th title="Unit's Label">Label</th>
				<th title="Type of Unit">Type</th>
				<th title="Unit's Location">Location</th>
				<th>Status</th>
			</thead>
		</table>
	</div>
	<script>
		var Table_Units = $('#Table_Units').DataTable( {
		"ajax": "cgi-bin/php/get/Units_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
		"autoWidth":false,
		"columns": [
			{ 
				"data": "ID",
				"className":"hidden"
			},{ 
				"data": "State"
			},{ 
				"data": "Unit"
			},{ 
				"data": "Type"
			},{ 
				"data": "Location",
				"visible":false
			},{ 
				"data": "Status",
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
			<?php if(count($Values) > 0){foreach($Values as $Field=>$Value){?>,{"data" :"<?php echo $Field;?>", "visible":false}<?php }}?>
		],
		"paging":false,
		"searching":false
		} );
		function hrefUnits(){hrefRow("Table_Units","unit");}
		$("Table#Table_Units").on("draw.dt",function(){hrefUnits();});
	</script>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>