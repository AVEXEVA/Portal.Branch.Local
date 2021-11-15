<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require( '/var/www/html/Portal.Branch.Local/bin/php/index.php' );
}
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
        $Route = sqlsrv_fetch_array($r);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">    <title>Nouveau Elevator Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>

<body onload='finishLoadingPage();' style='overflow-y:scroll;height:100%;background-color:#1d1d1d !important;color:white !important;'>
    <div id="wrapper" style='height:100%;' class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content' style='height:100%;overflow-y:scroll;'>
            <h4 style='margin:0px;padding:10px;background-color:whitesmoke;border-bottom:1px solid darkgray;'><a href='route.php?ID=<?php echo $_GET['ID'];?>'><?php \singleton\fontawesome::getInstance( )->Route();?> Route : <?php echo $Route['Route_Name'];?> : <?php echo $Route['Employee_First_Name'] . " " . $Route['Employee_Last_Name'];?></a></h4>
            <style>
            .nav-text{
                font-weight: bold;
                text-align: center;
            }
            .nav-icon{
                text-align: center;
            }
            </style>
            <style>
                * { margin: 0 }

                .Screen-Tabs { overflow-x: hidden }

                .Screen-Tabs>div {
                    --n: 1;
                    display: flex;
                    align-items: center;
                    overflow-y: hidden;
                    width: 100%; // fallback
                    width: calc(var(--n)*100%);
                    /*height: 50vw;*/ max-height: 100vh;
                    transform: translate(calc(var(--tx, 0px) + var(--i, 0)/var(--n)*-100%));

                    div {
                        /*width: 100%; // fallback
                        width: calc(100%/var(--n));*/
                        user-select: none;
                        pointer-events: none
                    }

                }

                .smooth { transition: transform  calc(var(--f, 1)*.5s) ease-out }
                div.Home-Screen-Option.active {
                    background-color:#3d3d3d !important;
                    color:white !important;
                }
            </style>
            <div class='Screen-Tabs shadower'>
                <div class='row'>
                    <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'route-information.php?ID=<?php echo $_GET['ID'];?>');">
                            <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Information(3);?></div>
                            <div class ='nav-text'>Information</div>
                    </div>
                    <?php
                    $r = $database->query(null,"SELECT Elev.ID FROM Elev LEFT JOIN Loc ON Elev.Loc = Loc.Loc WHERE Loc.Route = ?;",array($_GET['ID']));
                    if($r){
                      $Units = array();
                      while($row = sqlsrv_fetch_array($r)){$Units[] = $row['ID'];}
                      if(count($Units) > 0){
                        $Units = "WHERE (CM_Unit.Elev_ID = " . implode(" OR CM_Unit.Elev_ID = ",$Units) . ")";
                        $r = $database->query($database_Device,"SELECT CM_Unit.* FROM Device.dbo.CM_Unit {$Units}");
                        if($r && is_array(sqlsrv_fetch_array($r))){
                        ?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'route-faults.php?ID=<?php echo $_GET['ID'];?>');">
                                <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Information(3);?></div>
                                <div class ='nav-text'>Faults</div>
                        </div><?php }
                      }
                    }?>
                    <?php if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4){
                    ?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'route-locations.php?ID=<?php echo $_GET['ID'];?>');">
                            <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Location(3);?></div>
                            <div class ='nav-text'>Locations</div>
                    </div><?php }?>
                    <?php if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4){
                    ?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'route-units.php?ID=<?php echo $_GET['ID'];?>');">
                            <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Unit(3);?></div>
                            <div class ='nav-text'>Units</div>
                    </div><?php }?>
                    <?php if(isset($My_Privileges['Violation']) && $My_Privileges['Violation']['User_Privilege'] >= 4){
                    ?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="someFunction(this,'route-violations.php?ID=<?php echo $_GET['ID'];?>');">
                            <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->Violation(3);?></div>
                            <div class ='nav-text'>Violations</div>
                    </div><?php }?>
                    <?php if(isset($My_Privileges['User']) && $My_Privileges['User']['Other_Privilege'] >= 4){
                    ?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onClick="document.location.href='user.php?ID=<?php echo $_GET['ID'];?>';">
                            <div class='nav-icon'><?php \singleton\fontawesome::getInstance( )->User(3);?></div>
                            <div class ='nav-text'>User</div>
                    </div><?php }?>
                </div>
            </div>
            <div class='container-content'></div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <?php require('bin/js/datatables.php');?>
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
    

    <!-- Custom Date Filters-->
    
    <?php if( !isMobile() && false ){?>
    <script>
        function hrefLocations(){hrefRow("Table_Locations","location");}
        function hrefUnits(){hrefRow("Table_Units","unit");}
        function hrefViolations(){hrefRow("Table_Violations","violation");}
        $(document).ready(function(){
            var Table_Locations = $('#Table_Locations').DataTable( {
                "ajax": "bin/php/get/Locations_by_Route.php?ID=<?php echo $_GET['ID'];?>",
                "columns": [
                    { "data": "ID" },
                    { "data": "Name"},
                    { "data": "Tag"},
                    { "data": "Street"},
                    { "data": "City"},
                    { "data": "State"},
                    { "data": "Zip"},
                    { "data": "Route"},
                    { "data": "Zone"},
                    { "data": "Units"}
                ],
                "order": [[1, 'asc']],
                "language":{
                    "loadingRecords":""
                },
                "initComplete":function(){}

            } );
            $("Table#Table_Locations").on("draw.dt",function(){hrefLocations();});

            var Table_Units = $('#Table_Units').DataTable( {
                "ajax": "bin/php/get/Units_by_Route.php?ID=<?php echo $_GET['ID'];?>",
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
            var Table_Maintenances = $('#Table_Maintenances').DataTable( {
                "ajax": "bin/php/reports/Maintenances_by_Route.php?ID=<?php echo $_GET['ID'];?>",
                "columns": [
                    {
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
                "initComplete":function(){}

            } );
            $("Table#Table_Maintenances").on("draw.dt",function(){hrefLocations();});
            var Table_Violations = $('#Table_Violations').DataTable( {
                "ajax": {
                    "url":"bin/php/reports/Due_Violations_by_Route.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;}
                },
                "columns": [
                    { "data": "ID" },
                    { "data": "Name"},
                    { "data": "Location"},
                    { "data": "Unit"},
                    { "data": "Date",
                      render: function(data){
                        if(data === null || typeof data === 'undefined'){return '';}
                        else {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}}
                    },
                    { "data": "Due_Date"
                    },
                    { "data": "Status"},
                    { "data": "Maintenance",
                        render:function(data){
                            if(data == '1'){return 'In Scope';}
                            else{return 'N/A';}
                        }
                    }
                ],
                <?php require('bin/js/datatableOptions.php');?>
            } );
            finishLoadingPage();
            $("Table#Table_Violations").on("draw.dt",function(){hrefViolations();});
        });
    </script><?php } else {?>
    <script>
        function someFunction(link,URL){
            $(link).siblings().removeClass('active');
            $(link).addClass('active');
            $.ajax({
                url:"bin/php/element/route/" + URL,
                success:function(code){
                    $("div.container-content").html(code);
                }
            });
        }
        $(document).ready(function(){
            $("div.Screen-Tabs>div>div:first-child").click();
        });
    </script>
    <?php }?>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=route<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
