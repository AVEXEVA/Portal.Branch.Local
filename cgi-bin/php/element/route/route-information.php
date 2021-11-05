<?php 
session_start( [ 'read_and_close' => true ] );
require('../../../php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $r = $database->query(null,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = $database->query(null,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($array['ID'],$My_Privileges['Route']) && $My_Privileges['Route']['User_Privilege'] >= 4 && $My_Privileges['Route']['Group_Privilege'] >= 4 && $My_Privileges['Route']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    else {
        if(is_numeric($_GET['ID'])){
                $r = $database->query(null,
                "SELECT 
                    Route.ID        AS  ID,
                    Route.Name      AS  Route, 
                    Emp.fFirst      AS  First_Name,
                    Emp.Last        AS  Last_Name,
                    Emp.ID          AS  Employee_ID,
                    Emp.fWork       AS  fWork
                FROM 
                    Route
                    LEFT JOIN Emp   ON  Route.Mech = Emp.fWork
                WHERE
                    Route.ID        =   '{$_GET['ID']}'");
            $Route = sqlsrv_fetch_array($r);
            if($My_Privileges['Route']['User_Privilege'] >= 4 && $_SESSION['User'] == $Route['Employee_ID']){$Privileged = TRUE;}
        }
    }
    $database->query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "route.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=route<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
    	$r = $database->query(null,
            "SELECT 
                Route.ID             AS ID,
                Route.Name           AS Route, 
                Route.Name           AS Route_Name,
                Route.ID             AS Route_ID,
                Emp.fFirst           AS First_Name,
                Emp.Last             AS Last_Name,
                Emp.ID               AS Employee_ID,
                Emp.fFirst           AS Employee_First_Name,
                Emp.Last             AS Employee_Last_Name,
                Emp.fWork            AS fWork,
                Emp.ID               AS Route_Mechanic_ID,
                Emp.fFirst           AS Route_Mechanic_First_Name,
                Emp.Last             AS Route_Mechanic_Last_Name,
                Rol.Phone            AS Route_Mechanic_Phone_Number
            FROM 
                Route
                LEFT JOIN Emp   ON  Route.Mech = Emp.fWork
                LEFT JOIN Rol          ON Emp.Rol    = Rol.ID
            WHERE
                Route.ID        =   ?
        ;",array($_GET['ID']));
        if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }
        $Route = sqlsrv_fetch_array($r);
?><div class="panel panel-primary">
	<!--<div class="panel-heading"><h4><?php \singleton\fontawesome::getInstance( )->Maintenance();?> Maintenance</h4></div>-->
	<div class='panel-body white-background'>
		<div class='row shadower ' style='font-size:16px;padding:5px;'>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Route();?> Route:</div>
            <div class='col-xs-8'><?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Route['Route_Mechanic_ID']){?><a href="route.php?ID=<?php echo $Route['Route_ID'];?>"><?php }?><?php echo proper($Route["Route_Mechanic_First_Name"] . " " . $Route["Route_Mechanic_Last_Name"]);?><?php if($My_Privileges['Route']['Other_Privilege'] >= 4 || $My_User['ID'] == $Route['Route_Mechanic_ID']){?></a><?php }?>
			</div>
            <?php if(isset($Route['Route_Mechanic_Phone_Number']) && strlen($Route['Route_Mechanic_Phone_Number']) > 0){?>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Phone();?> Phone:</div>
				
			<div class='col-xs-8'><a href="tel:<?php echo $Route['Route_Mechanic_Phone_Number'];?>"><?php echo $Route['Route_Mechanic_Phone_Number'];?></a></div><?php }?>
			<?php /*if(strlen($Route['Route_Mechanic_Email']) > 0){?>
			<div class='col-xs-4'><?php \singleton\fontawesome::getInstance( )->Email();?> Email:</div>
            <div class='col-xs-8'><a href="mailto:<?php echo $Route['Route_Mechanic_Email'];?>"><?php echo $Route['Route_Mechanic_Email'];?></a></div><?php }*/?>
		</div>
	</div>
    <!--<div class='panel-heading'><h4>Monthly Maintenance Due</h4></div>-->
    <div class="panel-body shadower">
        <table id='Table_Maintenances' class='display' cellspacing='0' width='100%' style=''>
            <thead>
                <th>ID</th>
                <th title='Location'>Location</th>
                <th title='Unit'>Unit Name</th>
                <th title='State'>Unit State</th>
                <th title="Last Maintenance">Last Maintenance</th>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>		
<script>
	function hrefUnits(){hrefRow("Table_Maintenances","unit");}
	$("Table#Table_Units").on("draw.dt",function(){hrefUnits();});
		var Table_Maintenances = $('#Table_Maintenances').DataTable( {
            "ajax": "cgi-bin/php/reports/Maintenances_by_Route.php?ID=<?php echo $_GET['ID'];?>",
            "columns": [
            	{
            		"data":"ID",
            		"className":"hidden"
            	},{ 
					"data": "Location" 
				},{ 
					"data": "Unit"
				},{
					"data": "State"
				},{ 
					"data": "Last_Date",
					"render": function(data){
						if(data === null || typeof data === 'undefined'){return '';} 
						else {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
				}
            ],
            "order": [[1, 'asc']],
            "language":{
                "loadingRecords":""
            },
            "paging":false,
            "searching":false,
            "initComplete":function(){}

        } );
		$("Table#Table_Maintenances").on("draw.dt",function(){hrefUnits();});
</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=route<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?> 