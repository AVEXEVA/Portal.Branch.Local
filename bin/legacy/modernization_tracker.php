<?php 
session_start( [ 'read_and_close' => true ] );
require('bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = $database->query(null,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = $database->query(null,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = $database->query(null,"
		SELECT * 
		FROM   Privilege 
		WHERE  Privilege.User_ID = ?
	;",array($_SESSION['User']));
	$My_Privileges = array();
	if($r){while($My_Privilege = sqlsrv_fetch_array($r)){$My_Privileges[$My_Privilege['Access_Table']] = $My_Privilege;}}
    if(	!isset($My_Connection['ID']) 
	   	|| !isset($My_Privileges['Job']) 
	  		|| $My_Privileges['Job']['User_Privilege']  < 4
	  		|| $My_Privileges['Job']['Group_Privilege'] < 4
	  	    || $My_Privileges['Job']['Other_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "modernization.php"));
        if(count($_POST) > 0 && isset($_POST['Job']) && is_numeric($_POST['Job']) && $_POST['Job'] > 0 && isset($_POST['Unit']) && is_numeric($_POST['Unit']) && $_POST['Unit'] > 0){
            function lastId($queryID) {
                sqlsrv_next_result($queryID);
                sqlsrv_fetch($queryID);
                return sqlsrv_get_field($queryID, 0);
            }
            $r = $database->query($Portal,"
                SELECT *
                FROM   Modernization 
                WHERE  Modernization.Job      = ?
                       AND Modernization.Unit = ?
            ",array($_POST['Job'],$_POST['Unit']));
            $Modernization = sqlsrv_fetch_array($r);
            if(strlen($_POST['Removed']) > 0){$_POST['Removed'] = substr($_POST['Removed'],6,4) . '-' . substr($_POST['Removed'],0,2) . '-' . substr($_POST['Removed'],3,2);}
            else {$_POST['Removed'] = "1900-01-01";}
            if(strlen($_POST['Returned']) > 0){$_POST['Returned'] = substr($_POST['Returned'],6,4) . '-' . substr($_POST['Returned'],0,2) . '-' . substr($_POST['Returned'],3,2);}
            else {$_POST['Returned'] = "1900-01-01";}
            if(!is_null($Modernization)){
                $Modernization = $Modernization['ID'];
                $r = $database->query($Portal,"
                    UPDATE Modernization 
                    SET    Modernization.Supervisor    = ?,
                           Modernization.Date_Removed  = ?,
                           Modernization.Actual_Return = ?,
                           Modernization.EBN           = ?,
                           Modernization.Budget_Hours  = ?
                    WHERE  Modernization.ID = ?
                ",array($_POST['Supervisor'],$_POST['Removed'],$_POST['Returned'],$_POST['EBN'],$_POST['Budget_Hours'],$Modernization));
            } else {
                foreach($_POST as $key=>$value){
                    $_POST[$key] = trim($value);
                    if($value = ' '){$value = '';}
                }
                $r = $database->query($Portal,"
                    INSERT INTO Modernization(Supervisor, Date_Removed, Actual_Return, EBN, Budget_Hours, Job, Unit)
                    VALUES('{$_POST['Supervisor']}','{$_POST['Removed']}','{$_POST['Returned']}','{$_POST['EBN']}','{$_POST['Budget_Hours']}','{$_POST['Job']}','{$_POST['Unit']}');
                     SELECT SCOPE_IDENTITY();
                ");
                $Modernization = lastId($r);
				$r = $database->query(null,"SELECT * FROM Portal.dbo.Tasks;");
				if($r){while($Task = sqlsrv_fetch_array($r)){$database->query(null,"INSERT INTO Portal.dbo.Mod_Tasks(Modernization, Task, Status) VALUES(?,?,?);",array($Modernization,$Task['ID'],"0%"));if( ($errors = sqlsrv_errors() ) != null) {
        foreach( $errors as $error ) {
            echo "SQLSTATE: ".$error[ 'SQLSTATE']."<br />";
            echo "code: ".$error[ 'code']."<br />";
            echo "message: ".$error[ 'message']."<br />";
        }
    }}}
            }
            //echo 'here';
            $Timestamp = date("Y-m-d H:i:s");
            $r = $database->query($Portal,"
                    INSERT INTO Mod_Tracker(Modernization,Status,Author,Time_Stamp) 
                    VALUES('{$Modernization}','{$_POST['Status']}','{$_SESSION['User']}','{$Timestamp}')
                ;");
        }?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    
	<title>Nouveau Texas | Portal</title>    
    <?php require( bin_php . 'element/navigation.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'html/navigation.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <script>
                        function updateModernization(){
                            var formdata = $("form#modernization").serialize();
                            $.ajax({
                                url:"bin/php/post/updateModernization.php",
                                method:"POST",
                                data:formdata,
                                success:function(code){
                                }
                            });
                            var formdata = $("form#Survey_Sheet").serialize();
                            $.ajax({
                                url:"bin/php/post/updateModernization.php?ID=" + $("input[name='ID']").val(),
                                method:"POST",
                                data:formdata + "&Unit=" + $("input[name='Unit']").val(),
                                success:function(code){
                                    $.ajax({
                                        url:"bin/php/element/modernization/modernization_tracker.php?ID=" + $("input[name='ID']").val(),
                                        method:"GET",
                                        success:function(code){
                                            modernizationTracker(null,code);;
                                        }
                                    });
                                }
                            });
                        }
                        function updateEquipment(){
                            var formdata = $("form#modernization_equipment").serialize();
                            $.ajax({
                                url:"bin/php/post/updateModernizationEquipment.php",
                                method:"POST",
                                data:formdata,
                                success:function(code){
                                    $.ajax({
                                        url:"bin/php/element/modernization/modernization_equipment.php?ID=" + $("form#modernization_equipment input[name='ID']").val(),
                                        method:"GET",
                                        success:function(code){
                                            modernizationTracker(null,code);
                                        }
                                    });
                                }
                            });   
                        }
                        function modernizationTracker(protocol,code){
                            if(protocol == null){
                                $("div#content").html(code);
                                var title = $("input[name='job_name']").val();
                                if($("input[name='Version']").length == 0 && $("input[name='Hyperlink']").length == 0){
                                    $("div.add").attr("onclick","popupAdd();");
                                }
                                else if($("input[name='Version']").length == 0) {
                                    $("span#tracked_modernization").parent().attr("class","").children().html(" > " + title);
                                    $("span#modernization_equipment").parent().attr("class","hidden");
                                    $("div.add").attr("onclick","popupAddEquipment();");
                                } else {
                                    var equipment = $("input[name='Equipment']").val();
                                    $("span#modernization_equipment").parent().attr("class","").children().html(" > " + equipment);
                                    $("div.add").attr("onclick","popupAddCorrespondence();");
                                }
                                
                            } else if(protocol == 'tracked_modernizations') {
                                $.ajax({
                                    url:"bin/php/element/modernization/tracked_modernizations.php?ID=" + $(this).children("td:first-child").html(),
                                    method:"GET",
                                    success:function(code){
                                        modernizationTracker(null,code);
                                        $("span#tracked_modernization").parent().attr("class","hidden").children().html("");
                                    }
                                });
                                
                                
                            } else if(protocol == 'tracked_modernization'){
                                $.ajax({
                                    url:"bin/php/element/modernization/modernization_tracker.php?ID=" + $("form#modernization input[name='ID']").val(),
                                    method:"GET",
                                    success:function(code){
                                        modernizationTracker(null,code);;
                                    }
                                });
                            } else if(protocol == 'modernization_equipment'){
                                $.ajax({
                                    url:"bin/php/element/modernization/modernization_equipment.php?ID=" + $("form#modernization_equipment input[name='ID']").val(),
                                    method:"GET",
                                    success:function(code){
                                        modernizationTracker(null,code);
                                        
                                    }
                                });

                            }
                        }
                        function popupAdd(){
                            $.ajax({
                                url:"bin/php/element/modernization/Add_Tracked_Modernization.php",
                                method:"GET",
                                success:function(code){
                                    $("div#popup").remove();
                                    $("body").append(code);
                                }
                            });
                        }
                        function popupEdit(){
                            if($(".selected").length){
                                var string = "Job=" + $(".selected td:nth-child(3)").html();
                                var string = string + "&Unit=" + $(".selected td:nth-child(5)").html();
                                $.ajax({
                                    url:"bin/php/element/modernization/Edit_Tracked_Modernization.php?" + string,
                                    method:"GET",
                                    success:function(code){
                                        $("div#popup").remove();
                                        $("body").append(code);
                                    }
                                });
                            }
                        }
                        function deleteRow(){
                            if($(".selected").length){
                                if($("form#modernization").length == 0){
                                    var string = "ID=" + $(".selected td:nth-child(1)").html();
                                    $.ajax({
                                        url:"bin/php/post/deleteTrackedModernization.php",
                                        method:"POST",
                                        data:string,
                                        success:function(code){
                                            document.location.href='modernization_tracker.php';

                                        }
                                    });
                                } else if($("form#modernization_equipment").length == 0) {
                                    var string = "ID=" + $(".selected td:nth-child(2)").html();
                                    $.ajax({
                                        url:"bin/php/post/deleteModernizationEquipment.php",
                                        method:"POST",
                                        data:string,
                                        success:function(){
                                            modernizationTracker('tracked_modernization',null);
                                        }
                                    });
                                } else {
                                    var string = "ID=" + $(".selected td:nth-child(1)").html();
                                    $.ajax({
                                        url:"bin/php/post/deleteModernizationCorrespondence.php",
                                        method:"POST",
                                        data:string,
                                        success:function(){
                                            modernizationTracker('modernization_equipment',null);
                                        }
                                    });
                                }
                            }
                            
                        }
                        function selectRow(){
                            $.ajax({
                                url:"bin/php/element/modernization/modernization_tracker.php?ID=" + $(".selected").children("td:first-child").html(),
                                method:"GET",
                                success:function(code){
                                    modernizationTracker(null,code);;
                                }
                            });
                        }
                        function editRow(link){
                            $.ajax({
                                url:"bin/php/element/modernization/Edit_Modernization_Correspondence.php?ID=" + $(link).children("td:first-child").html() + "&Ref=" + $("form#modernization_equipment input[name='ID']").val(),
                                method:"GET",
                                success:function(code){
                                    $("body").append(code);
                                }
                            });
                        }
                        function popupAddEquipment(link){
                            $.ajax({
                                url:"bin/php/element/modernization/Add_Modernization_Equipment.php?ID=" + $("input[name='ID']").val(),
                                method:"GET",
                                success:function(code){
                                    $("body").append(code);
                                }
                            });
                        }
                        function popupAddCorrespondence(link){
                            $.ajax({
                                url:"bin/php/element/modernization/Add_Modernization_Correspondence.php?Modernization=" + $("form#modernization input[name='ID']").val() + "&ID=" + $("form#modernization_equipment input[name='ID']").val(),
                                method:"GET",
                                success:function(code){
                                    $("body").append(code);
                                }
                            });
                        }
                        function popupEditEquipment(){
                            if($(".selected").length){
                                var string = "Job=" + $(".selected td:nth-child(3)").html();
                                var string = string + "&Unit=" + $(".selected td:nth-child(5)").html();
                                var string = string + "&ID=" + $(".selected td:nth-child(2)").html();
                                $.ajax({
                                    url:"bin/php/element/modernization/Edit_Modernization_Equipment.php?" + string,
                                    method:"GET",
                                    success:function(code){
                                        $("div#popup").remove();
                                        $("body").append(code);
                                    }
                                });
                            }
                        }
                        function deleteEquipmentRow(){
                            if($(".selected").length){
                                var string = "Job=" + $(".selected td:nth-child(3)").html();
                                var string = string + "&Unit=" + $(".selected td:nth-child(5)").html();
                                $.ajax({
                                    url:"bin/php/post/deleteTrackedModernization.php",
                                    method:"POST",
                                    data:string,
                                    success:function(code){
                                        $(".selected").remove();
                                    }
                                });
                            }
                            
                        }
                        function removePopup(string){

                            $("div.popup[rel='" + string + "']").remove();
                        }
                        </script>
                        <script>
                        function cloneEquipment(){
                            var formdata = $("form#modernization_equipment").serialize();
                            $.ajax({
                                url:"bin/php/post/cloneModernizationEquipment.php?ID=" + $("form#modernization input[name='ID']").val(),
                                method:"POST",
                                data:formdata,
                                success:function(code){
                                    $(".popup").remove();
                                    $.ajax({
                                        url:"bin/php/element/modernization/modernization_tracker.php?ID=" + $("form#modernization input[name='ID']").val(),
                                        method:"GET",
                                        success:function(code){
                                            modernizationTracker(null,code);;
                                        }
                                    });
                                }
                            });
                        }
                        </script>
                        <script>
                        function hyperlinkInput(link){
                            if($(link).val().includes("http://") || $(link).val().includes("https://")){
                                $(link).parent().append("<div class='miniPopup' style='position:absolute;top:-25px;width:100%;height:25px;;background-color:white;padding-left:10px;'><a href='" + $(link).val() + "' target='_blank' style='text-decoration:underline;color:blue;'>" + $(link).val() + "</a></div>");
                            }
                        }
                        $(document).ready(function(){
                            $(document).on("click",function(){
                                $(".miniPopup").remove();
                            });
                        })
                        </script>
                        <style>
                        tr td[role='row'] { cursor:pointer; }
                        </style>
                        <div class='panel-heading'><h4>
                            <div style='display:inline-block;'>
                                <span onClick="modernizationTracker('tracked_modernizations');" style='cursor:pointer;'><?php \singleton\fontawesome::getInstance( )->Unit();?>Modernization List</span>
                                <span class='hidden' onClick="modernizationTracker('tracked_modernization');" style='cursor:pointer;'><span id='tracked_modernization'> > Modernization Entity</span></span>
                                <span class='hidden' onClick="modernizationTracker('modernization_equipment');" style='cursor:pointer;'><span id='modernization_equipment'> > Equipment Entity</span></span>
                            </div>
                            <?php if(isset($My_Privileges['Admin']['User_Privilege']) && $My_Privileges['Admin']['User_Privilege'] > 4){?>
                            <div class='delete' style='cursor:pointer;float:right;margin-left:25px;' onClick="deleteRow();"><?php \singleton\fontawesome::getInstance( )->Edit();?> Delete</div><?php }?>
                            <div class='add' style='float:right;margin-left:25px;cursor:pointer;' onClick="popupAdd();"><?php \singleton\fontawesome::getInstance( )->Add();?> Add</div>
                            <div style='clear:both;'></div>
                        </h4></div>
                        <div class="panel-body" id='content'>
                            <table>
                                <tr><td style='text-align:right;'>Ongoing Mods:</td>
                                    <td>&nbsp;</td>
                                    <td><?php
                                    $r = $database->query(null,"
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
                                    $r = $database->query(null,"
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
                                    /*$r = $database->query($Portal,"
                                        SELECT Count(Modernization.ID) AS Counter
                                        FROM Job
                                        WHERE 
                                            Job.fDate >= '2017-01-01 00:00:00.000'
                                            AND Job.Type = '2'
                                    ;");
                                    $Counter = sqlsrv_fetch_array($r)['Counter'];*/
                                    $Counter = 0;
                                    $r = $database->query($Portal,"
                                        SELECT
                                           MAX(Mod_Tracker.Time_Stamp) AS Time_Stamp
                                        FROM
                                           Mod_Tracker 
                                        GROUP BY
                                           Mod_Tracker.Modernization
                                    ;");
                                    if($r){
                                        while($array = sqlsrv_fetch_array($r)){
                                            $r2 = $database->query($Portal,"
                                                SELECT Mod_Tracker.Status AS Status
                                                FROM Mod_Tracker 
                                                WHERE Mod_Tracker.Time_Stamp = '{$array['Time_Stamp']}'
                                            ;");
                                            if($r2){
                                                $array2 = sqlsrv_fetch_array($r2);
                                                if($array2['Status'] == '15'){
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
                            <style>
                            .hidden {display:none;}
                            </style>
                            <hr />
                            <table id='Table_Modernizations' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th style='display:none;'>Modernization</th>
                                    <th class='hidden' title="">Location</th>
                                    <th>Name</th>
                                    <th>Job</th>
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
										<th class='hidden'></th>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    
    <!-- Metis Menu Plugin JavaScript -->
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
	
	
    <!-- Custom Theme JavaScript -->
    
    <!-- JQUERY UI Javascript -->
    
    <style>
    Table#Table_Modernizations td.hide_column { display:none; }
    </style>
    <!-- Custom Date Filters-->
    
    <style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    </style>
    <script>
        function formatEquipment ( d ) {
            return "<div>"+
                "<div>"+
                    "<div class='column'>"+
                        "<div class='Account'><div class='label1'>Submitted:</div><div class='data'>"+d.Submitted+"</div></div>"+
                        "<div class='Location'><div class='label1'>Purchased:</div><div class='data'>"+d.Purchased+"</div></div>"+
                        "<div class='Address'><div class='label1'>PO:</div><div class='data'>"+d.PO+"</div></div>"+
                        "<div class='Address'><div class='label1'>Drawings_Received:</div><div class='data'>"+d.Drawings_Received+"</div></div>"+
                        "<div class='Caller'><div class='label1'>Drawings_Reviewed:</div><div class='data'>"+d.Drawings_Reviewed+"</div></div>"+
                    "</div>"+
                "</div>"+
                "<div>"+
                    "<div class='column' style='width:45%;vertical-align:top;'>"+
                        "<div><b>Equipment Description</b></div>"+
                        "<div><pre>"+d.Description+"</div>"+
                    "</div>"+
                    "<div class='column' style='width:45%;vertical-align:top;'>"+
                        "<div><b>Tracker Notes</b></div>"+
                        "<div><pre>"+d.Notes+"</div>"+
                    "</div>"+
                "</div>"+
            '</div>'
        }
        function hrefModernizations(){hrefRow("Table_Modernizations","job");}
        $(document).ready(function() {
            var Table_Modernizations = $('#Table_Modernizations').DataTable( {
				
                "ajax": {
                    "url":"bin/php/get/Tracked_Modernizations.php",
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
                    { "data": "Location",
                        "className":"hidden"},
                    { "data": "Name"},
                    { "data": "Job" },
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
                                '<tr class="group"><td colspan="14" style="BACKGROUND-COLOR:#151515;font-weight:700;color:white;">' + group  + '</td></tr>'
                            );
                            last = group;
                        }
                    });
                },
                "initComplete":function(){finishLoadingPage();}
            } );
            $("Table#Table_Modernizations").on("draw.dt",function(){
                $("Table#Table_Modernizations tr[role='row']").on("dblclick",function(){
                    $.ajax({
                        url:"bin/php/element/modernization/modernization_tracker.php?ID=" + $(this).children("td:first-child").html(),
                        method:"GET",
                        success:function(code){
                            modernizationTracker(null,code);;
                        }
                    });
                });
            });
            $("#Table_Modernizations").DataTable().draw();
            $("Table#Table_Modernizations").on("click","tr",function(){
                $(".selected").toggleClass("selected");
                $(this).toggleClass("selected");
            });
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