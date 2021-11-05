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
    if(isset($My_Privileges['Route']) && $My_Privileges['Route']['User_Privilege'] >= 4 && $My_Privileges['Route']['Group_Privilege'] >= 4 && $My_Privileges['Route']['Other_Privilege'] >= 4){$Privileged = TRUE;}
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
    $database->query(null,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "route.php"));
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
                LEFT JOIN Emp          ON  Route.Mech = Emp.fWork
                LEFT JOIN Rol          ON Emp.Rol    = Rol.ID 
            WHERE
                Route.ID        =   ?
        ;",array($_GET['ID']));
        $Route = sqlsrv_fetch_array($r);
?>
    <div class='panel panel-primary'>
        <!--<div class="panel-heading"><h4>Units</h4></div-->
        <div class="panel-body">
            <table id='Table_Units' class='display' cellspacing='0' width='100%'>
                <thead><tr>
                    <th title="Unit's ID">ID</th>
                    <th title='Unit State ID'>State</th>
                    <th title="Unit's Label">Unit</th>
                    <th title="Type of Unit">Type</th>
                    <th>Location</th>
                </tr></thead>
				<tbody></tbody>
            </table>
        </div>
    </div>
    <script>
        function hrefUnits(){hrefRow("Table_Units","unit");}
        var Table_Units = $('#Table_Units').DataTable( {
            "ajax": "cgi-bin/php/get/Units_by_Route.php?ID=<?php echo $_GET['ID'];?>",
            "lengthChange":false,
            "columns": [
                { "data": "ID" },
                { "data": "State"},
                { "data": "Unit"},
                { "data": "Type"},
                { "data": "Location"}
            ],
            "order": [[1, 'asc']],
            "language":{"loadingRecords":""},
            "initComplete":function(){}
        } );
		$("Table#Table_Units").on("draw.dt",function(){hrefUnits();});
	</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=route<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>