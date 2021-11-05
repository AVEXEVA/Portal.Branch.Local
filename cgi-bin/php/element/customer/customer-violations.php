<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
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
?><div class="panel panel-primary" style='margin-bottom:0px;'>
	<div class="panel-body">			
		<div class="panel-body">
			<table id='Table_Violations' class='display' cellspacing='0' width='100%'>
				<thead>
					<th title='ID'>ID</th>
					<th title='Name'>Name</th>
					<th title="Date">Date</th>
					<th title='Status'>Status</th>
					<th title='Description'>Description</th>
				</thead>
			</table>
		</div>
		<script>
			var Table_Violations = $('#Table_Violations').DataTable( {
				"ajax": {
					"url":"cgi-bin/php/get/Violations_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
					"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
				},
				"columns": [
					{ "data": "ID" },
					{ "data": "Name"},
					{ "data": "Date"},
					{ "data": "Status"},
					{ "data": "Description"}
				],
				<?php require('../../../js/datatableOptions.php');?>
			} );
			function hrefViolations(){hrefRow("Table_Violations","job");}
			$("Table#Table_Violations").on("draw.dt",function(){hrefViolations();});
		</script>
	</div>
</div><?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>