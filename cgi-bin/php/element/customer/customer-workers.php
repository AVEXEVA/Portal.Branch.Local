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
	<div class="panel-heading"><?php \singleton\fontawesome::getInstance( )->Users();?> Workers</h4></div>
	<div class="panel-body">
		<table id='Table_People' class='display' cellspacing='0' width='100%'>
			<thead>
				<th>ID</th>
				<th>First Name</th>
				<th>Last Name</th>
			</thead>
		</table>
	</div>
	<script>
		var Table_People = $('#Table_People').DataTable( {
			"ajax": "cgi-bin/php/get/Workers_by_Customer.php?ID=<?php echo $_GET['ID'];?>",
			"columns": [
				{ "data" : "ID"      
				},{
					"data" : "First_Name"
				},{
					"data" : "Last_Name"   
				},{ 
					"data" : "Regular",
					"visible" : false
				},{ 
					"data" : "Overtime",
					"visible": false
				},{ 
					"data" : "Doubletime",
					"visible": false
				},{ 
					"data" : "Total",
					"visible" : false
				  }
			],
			"language":{
				"loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
			},
			"paging":true,
			"select":true,
			"initComplete":function(){},
			"scrollY" : "600px",
			"scrollCollapse":true,
			"lengthChange": false
		} );
	</script>
</div>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>