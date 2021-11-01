<?php 
session_start();
require('../../index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM nei.dbo.Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User = sqlsrv_fetch_array($User);
    $Field = ($User['Field'] == 1 && $User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($Portal,"
            SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
            FROM   Portal.dbo.Privilege
            WHERE  User_ID = ?
        ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >= 4 && $My_Privileges['Job']['Group_Privilege'] >= 4 && $My_Privileges['Job']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }
    else {
        //var_dump($_POST);
        if(count($_POST) > 0 && isset($_POST['Job']) && is_numeric($_POST['Job']) && $_POST['Job'] > 0 && isset($_POST['Unit']) && is_numeric($_POST['Unit']) && $_POST['Unit'] > 0){
            //var_dump($_POST);
            function lastId($queryID) {
                sqlsrv_next_result($queryID);
                sqlsrv_fetch($queryID);
                return sqlsrv_get_field($queryID, 0);
            }
            $r = sqlsrv_query($Portal,"
                SELECT *
                FROM Modernization 
                WHERE 
                    Modernization.Job = ?
                    AND Modernization.Unit = ?
            ",array($_POST['Job'],$_POST['Unit']));
            $Modernization = sqlsrv_fetch_array($r);
            if(strlen($_POST['Removed']) > 0){$_POST['Removed'] = substr($_POST['Removed'],6,4) . '-' . substr($_POST['Removed'],0,2) . '-' . substr($_POST['Removed'],3,2);}
            else {$_POST['Removed'] = "1900-01-01";}
            if(strlen($_POST['Returned']) > 0){$_POST['Returned'] = substr($_POST['Returned'],6,4) . '-' . substr($_POST['Returned'],0,2) . '-' . substr($_POST['Returned'],3,2);}
            else {$_POST['Returned'] = "1900-01-01";}
            if(!is_null($Modernization)){
                $Modernization = $Modernization['ID'];
                $r = sqlsrv_query($Portal,"
                    UPDATE Modernization 
                    SET 
                        Modernization.Supervisor = ?,
                        Modernization.Date_Removed = ?,
                        Modernization.Actual_Return = ?,
                        Modernization.EBN = ?,
                        Modernization.Budget_Hours = ?
                    WHERE 
                        Modernization.ID = ?
                ",array($_POST['Supervisor'],$_POST['Removed'],$_POST['Returned'],$_POST['EBN'],$_POST['Budget_Hours'],$Modernization));
            } else {
                $r = sqlsrv_query($Portal,"
                    INSERT INTO Modernization(Supervisor, Date_Removed, Actual_Return, EBN, Budget_Hours, Job, Unit)
                    VALUES('{$_POST['Supervisor']}','{$_POST['Removed']}','{$_POST['Returned']}','{$_POST['EBN']}','{$_POST['Budget_Hours']}','{$_POST['Job']}','{$_POST['Unit']}');
                     SELECT SCOPE_IDENTITY();
                ");
                $Modernization = lastId($r);
            }
            //echo 'here';
            $Timestamp = date("Y-m-d H:i:s");
            $r = sqlsrv_query($Portal,"
                    INSERT INTO Mod_Tracker(Modernization,Status,Author,Time_Stamp) 
                    VALUES('{$Modernization}','{$_POST['Status']}','{$_SESSION['User']}','{$Timestamp}')
                ;");
        }?>
<table>
    <tr><td style='text-align:right;'>Ongoing Mods:</td>
        <td>&nbsp;</td>
        <td><?php
        $r = sqlsrv_query($NEI,"
            SELECT Count(Job.ID) AS Counter
            FROM Job
            WHERE 
                Job.Status = '0'
                AND Job.Type = '2'

        ;");
        $Counter = sqlsrv_fetch_array($r)['Counter'];
        echo $Counter;
        ?></td>
    </tr>
    <tr><td style='text-align:right;'>Mods Started This Year:</td>
        <td>&nbsp;</td>
        <td><?php
        $r = sqlsrv_query($NEI,"
            SELECT Count(Job.ID) AS Counter
            FROM Job
            WHERE 
                Job.fDate >= '2017-01-01 00:00:00.000'
                AND Job.Type = '2'
        ;");
        $Counter = sqlsrv_fetch_array($r)['Counter'];
        echo $Counter;
        ?></td>
    </tr>
</table>
<tr><td style='text-align:right;'>Mods Returned To Service This Year:</td>
        <td>&nbsp;</td>
        <td><?php
        /*$r = sqlsrv_query($Portal,"
            SELECT Count(Modernization.ID) AS Counter
            FROM Job
            WHERE 
                Job.fDate >= '2017-01-01 00:00:00.000'
                AND Job.Type = '2'
        ;");
        $Counter = sqlsrv_fetch_array($r)['Counter'];*/
        $Counter = 0;
        $r = sqlsrv_query($Portal,"
            SELECT
               MAX(Mod_Tracker.Time_Stamp) AS Time_Stamp
            FROM
               Mod_Tracker 
            GROUP BY
               Mod_Tracker.Modernization
        ;");
        if($r){
            while($array = sqlsrv_fetch_array($r)){
                $r2 = sqlsrv_query($Portal,"
                    SELECT Mod_Tracker.Status AS Status
                    FROM Mod_Tracker 
                    WHERE Mod_Tracker.Time_Stamp = '{$array['Time_Stamp']}'
                ;");
                if($r2){
                    $array2 = sqlsrv_fetch_array($r2);
                    if($array2['Status'] == '12'){
                        $Counter++;
                    }
                }
            }
        }
        echo $Counter;
        ?></td>
    </tr>
</table>
<br />
<hr />
<table id='Table_Modernizations' class='display' cellspacing='0' width='100%'>
    <thead>
        <th style='display:none;'>Modernization</th>
        <th>Location</th>
        <th>Job</th>
        <th>Name</th>
        <th>Unit</th>
        <th>Status</th>
        <th>Supervisor</th>
        <th>EBN</th>
        <th>Removed</th>
        <th>Returned</th>
        <th>Budgeted Hours</th>
        <th>Projected</th>
        <th>Total Hours</th>
        <th>Balance</th>
    </thead>
    <tbody></tbody>
    <tfoot>
        <tr>
            <th style='display:none;'></th>
            <th></th>
            <th></th>
            <th>Page Sum</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </tfoot>
</table>
    <script>
        $(document).ready(function() {
            var Table_Modernizations = $('#Table_Modernizations').DataTable( {
                "ajax": {
                    "url":"php/get/Tracked_Modernizations.php",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;
                    } 
                },
                "columns": [
                    {
                        "data": "ID",
                        "className":"hide_column"
                    },
                    { "data": "Location"},
                    { "data": "Job" },
                    { "data": "Name"},
                    { "data": "Unit"},
                    { "data": "Status"},
                    
                    { "data": "Supervisor"},
                    { "data": "EBN"},
                    { 
                        "data": "Removed",
                        render: function(data){
                            if(data != null && data.length == 10){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
                            else {return '';}
                        }
                    },
                    { 
                        "data": "Returned",
                        render: function(data){
                            if(data != null && data.length == 10){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
                            else {return '';}
                        }
                    },
                    { "data": "Budgeted_Hours", className:"sum"},
                    { "data": "Projection"},
                    { "data": "Total_Hours", className:"sum"},
                    { "data": "Balance",className:"sum",
                        render:function(data){if(data != null){return data;}else {return '';}}}
                ],
                "rowGroup": {
                    dataSrc: 'Location',
                    startRender: null,
                    endRender: function ( rows, group ) {
                        return $('<tr/>');
                    },
                },
                "order": [[1, 'asc']],
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "language":{"loadingRecords":""},
                "footerCallback": function(row, data, start, end, display) {
                    var api = this.api();

                    api.columns('.sum', { page: 'current' }).every(function () {
                        var sum = api
                            .cells( null, this.index(), { page: 'current'} )
                            .render('display')
                            .reduce(function (a, b) {
                                var x = parseFloat(a) || 0;
                                var y = parseFloat(b) || 0;
                                return x + y;
                            }, 0);
                        $(this.footer()).html(sum);
                    });
                },
                drawCallback: function (settings) {
                    var api = this.api();
                    var rows = api.rows({ page: 'current' }).nodes();
                    var last = null;
                    api.column(1, { page: 'current' }).data().each(function (group, i) {
                        if (last !== group) {
                            $(rows).eq(i).before(
                                '<tr class="group"><td colspan="14" style="BACKGROUND-COLOR:#337ab7;font-weight:700;color:white;">' + group  + '</td></tr>'
                            );
                            last = group;
                        }
                    });
                },
                "initComplete":function(){
                    //setTimeout(function(){hrefModernizations();},100);
                    finishLoadingPage();
                }
            } );
            $("Table#Table_Modernizations").on("draw.dt",function(){
                //$("#Table_Modernizations tbody").prepend("<tr class='new'><td colspan='14' style='background-color:#4482cd;color:white;'>Add New Modernization</td></tr>");
                $("Table#Table_Modernizations tr[role='row']").on("dblclick",function(){
                    $.ajax({
                        url:"cgi-bin/php/element/modernization/modernization_tracker.php?ID=" + $(this).children("td:first-child").html(),
                        method:"GET",
                        success:function(code){
                            modernizationTracker(null,code);
                        }
                    })
                });
            });

            $("#Table_Modernizations").DataTable().draw();
            $("Table#Table_Modernizations").on("click","tr",function(){
                $(".selected").toggleClass("selected");
                $(this).toggleClass("selected");
            });
            //$("Table#Table_Modernizations").on("draw.dt",function(){hrefModernizations();});
            <?php if(!$Mobile){?>
            yadcf.init(Table_Modernizations,[
                {   column_number:4},
                {   column_number:5}
            ]);
            stylizeYADCF();
            <?php }?>
        } );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }?>