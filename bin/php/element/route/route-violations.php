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
            SELECT Access, Owner, Group, Other
            FROM   Privilege
            WHERE  User_ID = ? 
        ;",array($_SESSION['User']));
    $My_Privileges = array(); 
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Route']) 
        && $My_Privileges['Route']['Owner'] >= 4 
        && $My_Privileges['Route']['Group'] >= 4 
        && $My_Privileges['Route']['Other'] >= 4){
            $Privileged = TRUE;}
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
                    Route.ID        =   ?",array($_GET['ID']));
            $Route = sqlsrv_fetch_array($r);
            if($My_Privileges['Route']['Owner'] >= 4 && $_SESSION['User'] == $Route['Employee_ID']){$Privileged = TRUE;}
        }
    }
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="401.html";</script></head></html><?php }
    else {
        $database->query(null,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "route.php"));
        $r = $database->query(null,"
        	SELECT Route.ID             AS ID,
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
            FROM   Route
                   LEFT JOIN Emp          ON  Route.Mech = Emp.fWork
                   LEFT JOIN Rol          ON Emp.Rol    = Rol.ID
            WHERE  Route.ID = ?
        ;",array($_GET['ID']));
        $Route = sqlsrv_fetch_array($r);
?>
<div class='panel panel-primary'>
	<!--<div class="panel-heading"><h4>Violations</h4></div>-->
    <div class="panel-body">
        <table id='Table_Violations' class='display' cellspacing='0' width='100%' style='font-size:10px;'>
            <thead>
                <th title='ID of the Violation'>ID</th>
                <th title='Name of the Violation'>Name</th>
                <th title='Location of the Violation'>Location</th>
                <th title="Violation's Unit">Unit</th>
                <th title="Date of the Violation">Date</th>
				<th tilte='Due Date of the Violation'>Due Date</th>
                <th title='Status of the Violation'>Status</th>
				<th tilte='Maintenance'>Maintenance</th>
            </thead>
			<tbody></tbody>
        </table>
    </div>
</div>
    <script>
		function hrefViolations(){hrefRow("Table_Violations","violation");}
		var Table_Violations = $('#Table_Violations').DataTable( {
            "ajax": {
                "url":"bin/php/reports/Due_Violations_by_Route.php?ID=<?php echo $_GET['ID'];?>",
                "dataSrc":function(json){
                    if(!json.data){json.data = [];}
                    return json.data;}
            },
            "columns": [
                { 
					"data": "ID",
					"className":"hidden"
				},{ 
					"data": "Name"
				},{ 
					"data": "Location"
				},{ 
					"data": "Unit"
				},{ 
					"data": "Date",
				  	render: function(data){
						if(data === null || typeof data === 'undefined'){return '';} 
						else {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
					}
				},{ 
					"data": "Due_Date"
				},{ 
					"data": "Status",
					"visible":false
				},{ 
					"data": "Maintenance",
					render:function(data){
						if(data == '1'){return 'In Scope';}
						else{return 'N/A';} 
					},
					"className":"hidden"
				}
            ],
            "language":{
                "loadingRecords":"<div style='text-align:center;'><div class='sk-cube-grid' style='display:inline-block;position:relative;';><div class='sk-cube sk-cube1' style='background-color:#cc0000'></div><div class='sk-cube sk-cube2' style='background-color:#cc0000'></div><div class='sk-cube sk-cube3' style='background-color:#cc0000'></div><div class='sk-cube sk-cube4' style='background-color:#cc0000'></div><div class='sk-cube sk-cube5' style='background-color:#cc0000'></div><div class='sk-cube sk-cube6' style='background-color:#cc0000'></div><div class='sk-cube sk-cube7' style='background-color:#cc0000'></div><div class='sk-cube sk-cube8' style='background-color:#cc0000'></div><div class='sk-cube sk-cube9' style='background-color:#cc0000'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-45px;'><div class='sk-cube sk-cube1' style='background-color:#00007f'></div><div class='sk-cube sk-cube2' style='background-color:#00007f'></div><div class='sk-cube sk-cube3' style='background-color:#00007f'></div><div class='sk-cube sk-cube4' style='background-color:#00007f'></div><div class='sk-cube sk-cube5' style='background-color:#00007f'></div><div class='sk-cube sk-cube6' style='background-color:#00007f'></div><div class='sk-cube sk-cube7' style='background-color:#00007f'></div><div class='sk-cube sk-cube8' style='background-color:#00007f'></div><div class='sk-cube sk-cube9' style='background-color:#00007f'></div></div><div class='sk-cube-grid' style='display:inline-block;position:relative;top:-84px;'><div class='sk-cube sk-cube1' style='background-color:gold'></div><div class='sk-cube sk-cube2' style='background-color:gold'></div><div class='sk-cube sk-cube3' style='background-color:gold'></div><div class='sk-cube sk-cube4' style='background-color:gold'></div><div class='sk-cube sk-cube5' style='background-color:gold'></div><div class='sk-cube sk-cube6' style='background-color:gold'></div><div class='sk-cube sk-cube7' style='background-color:gold'></div><div class='sk-cube sk-cube8' style='background-color:gold'></div><div class='sk-cube sk-cube9' style='background-color:gold'></div></div></div><div style='font-size:72px;text-align:center;' class='BankGothic'>Nouveau Elevator</div><div style='font-size:42px;text-align:center;'><i>Raising Your Life</i></div>"
            },
            "paging":true,
            <?php if(!isMobile()){?>"dom":"Bfrtip",<?php }?>
            "select":true,
            "initComplete":function(){
            },
            "scrollY" : "600px",
            "scrollCollapse":true,
            "lengthChange": false
        } );
		function hrefViolations(){hrefRow("Table_Violations","violation");}
        $("Table#Table_Violations").on("draw.dt",function(){hrefViolations();});
    </script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=route<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>