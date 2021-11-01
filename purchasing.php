<?php 
session_start();
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"
		SELECT * 
		FROM   Connection 
		WHERE  Connection.Connector = ? 
		       AND Connection.Hash  = ?
	;",array($_SESSION['User'],$_SESSION['Hash']));
    $My_Connection = sqlsrv_fetch_array($r,SQLSRV_FETCH_ASSOC);
    $r = sqlsrv_query($NEI,"
		SELECT *,
		       Emp.fFirst AS First_Name,
			   Emp.Last   AS Last_Name
		FROM   Emp 
		WHERE  Emp.ID = ?
	;",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($r);
	$r = sqlsrv_query($NEI,"
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
		sqlsrv_query($NEI,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page]) 
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "purchasing.php"));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>    
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index2.php');?>
        <?php require(PROJECT_ROOT.'php/element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class='panel-heading'><h4>
                            <div style='display:inline-block;'>
                                <span onClick="document.location.href='purchasing.php'" style='cursor:pointer;'><?php $Icons->Unit();?>Equipment List</span>
                                <span class='hidden' onClick="modernizationTracker('modernization_equipment');" style='cursor:pointer;'><span id='modernization_equipment'> > Equipment Entity</span></span>
                            </div>
                            <?php if(isset($My_Privileges['Admin']['User_Privilege']) && $My_Privileges['Admin']['User_Privilege'] > 4){?>
                            <div class='delete' style='cursor:pointer;float:right;margin-left:25px;' onClick="deleteRow();"><?php $Icons->Edit();?> Delete</div><?php }?>
                            <div class='add' style='float:right;margin-left:25px;cursor:pointer;' onClick="popupAddEquipment();"><?php $Icons->Add();?> Add</div>
                            <div style='clear:both;'></div>
                        </h4></div>
                        <div class="panel-body" id='content'>
                            <table id='Table_Modernization_Equipment' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th></th>
                                    <th>ID</th>
                                    <th>Location</th>
                                    <th>In Care Of</th>
                                    <th>Subcontractor</th>
                                    <th>Equipment</th>
                                    <th>Quantity</th>
                                    <th>Description</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap Core JavaScript -->
    <script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.js"></script>    

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="cgi-bin/js/jquery.dataTables.yadcf.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>

    <!--Moment JS Date Formatter-->
    <script src="../dist/js/moment.js"></script>

    <!-- JQUERY UI Javascript -->
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <style>
    Table#Table_Modernizations td.hide_column { display:none; }
    </style>
    <!-- Custom Date Filters-->
    <script src="../dist/js/filters.js"></script>
    <style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    </style>
    <script>
        function popupAddEquipment(link){
            $.ajax({
                url:"cgi-bin/php/element/purchasing/Add_Modernization_Equipment.php?ID=" + $("input[name='ID']").val(),
                method:"GET",
                success:function(code){
                    $("body").append(code);
                }
            });
        }
        function modernizationTracker(protocol,code){
                if(protocol == null){
                    $("div#content").html(code);
                    var title = $("input[name='job_name']").val();
                    if($("input[name='Version']").length == 0) {
                        $("span#tracked_modernization").parent().attr("class","").children().html(" > " + title);
                        $("span#modernization_equipment").parent().attr("class","hidden");
                        $("div.add").attr("onclick","popupAddEquipment();");
                    } else {
                        var equipment = $("input[name='Equipment']").val();
                        $("span#modernization_equipment").parent().attr("class","").children().html(" > " + equipment);
                        $("div.add").attr("onclick","popupAddCorrespondence();");
                    }
                    
                } else if(protocol == 'tracked_modernization'){
                    document.location.href='purchasing.php';
                } else if(protocol == 'modernization_equipment'){
                    $.ajax({
                        url:"cgi-bin/php/element/modernization/modernization_equipment.php?ID=" + $("form#modernization_equipment input[name='ID']").val(),
                        method:"GET",
                        success:function(code){
                            modernizationTracker(null,code);
                            
                        }
                    });

                }
            }
        function removePopup(string){$("div.popup[rel='" + string + "']").remove();}
        function lookupJobs(link){
            $.ajax({
                method:"GET",
                url:"cgi-bin/php/element/select_Job_by_Location.php?ID=" + $(link).val(),
                success:function(code){$("td#tdJob").html(code);}
            });
            $.ajax({
                method:"GET",
                url:"cgi-bin/php/element/select_Unit_by_Location.php?ID=" + $(link).val(),
                success:function(code){$("td#tdUnit").html(code);}
            });
        }
        function deleteRow(){
            if($(".selected").length){
                if($("form#modernization_equipment").length == 0) {
                    var string = "ID=" + $(".selected td:nth-child(2)").html();
                    $.ajax({
                        url:"cgi-bin/php/post/deleteModernizationEquipment.php",
                        method:"POST",
                        data:string,
                        success:function(){
                            modernizationTracker('tracked_modernization',null);
                        }
                    });
                } else {
                    var string = "ID=" + $(".selected td:nth-child(1)").html();
                    $.ajax({
                        url:"cgi-bin/php/post/deleteModernizationCorrespondence.php",
                        method:"POST",
                        data:string,
                        success:function(){
                            modernizationTracker('modernization_equipment',null);
                        }
                    });
                }
            }
        }
        $(document).ready(function() {
            var Table_Modernization_Equipment = $('#Table_Modernization_Equipment').DataTable( {
                "ajax": {
                    "url":"cgi-bin/php/get/Purchasing.php?ID=<?php echo $_GET['ID'];?>",
                    "dataSrc":function(json){
                        if(!json.data){json.data = [];}
                        return json.data;
                    } 
                },
                "columns": [
                    {
                        "className":      'details-control',
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": ''
                    },
                    {
                        "data": "ID",
                        "className":"hidden"
                    },
                    { 
                        "data":"Location",
                        "className":"hidden"
                    },
                    { "data": "In_Care_Of"},
                    { "data": "Subcontractor" },
                    { "data": "Equipment"},
                    { "data": "Quantity"},
                    { "data": "Description"},
                    
                    { "data": "Version"},
                    { "data": "Status"}
                ],
                "order": [[1, 'asc']],
                "lengthMenu":[[10,25,50,100,500,-1,0],[10,25,50,100,500,"All","None"]],
                "language":{"loadingRecords":""}, 
                "rowGroup": {
                    dataSrc: 'Location',
                    startRender: null,
                    endRender: function ( rows, group ) {
                        return $('<tr/>');
                    },
                },
                drawCallback: function (settings) {
                    var api = this.api();
                    var rows = api.rows({ page: 'current' }).nodes();
                    var last = null;

                    api.column(2, { page: 'current' }).data().each(function (group, i) {

                        if (last !== group) {

                            $(rows).eq(i).before(
                                '<tr class="group"><td colspan="14" style="BACKGROUND-COLOR:#337ab7;font-weight:700;color:white;">' + group  + '</td></tr>'
                            );

                            last = group;
                        }
                    });
                },
                "initComplete":function(){finishLoadingPage();}
            } );
            $("Table#Table_Modernization_Equipment").on("draw.dt",function(){
                $("Table#Table_Modernization_Equipment tr[role='row']").on("dblclick",function(){
                    $.ajax({
                        url:"cgi-bin/php/element/modernization/modernization_equipment.php?ID=" + $(this).children("td:nth-child(2)").html(),
                        method:"GET",
                        success:function(code){modernizationTracker(null,code);}
                    });
                });
            });
            $("Table#Table_Modernization_Equipment").on("click","tr",function(){
                $(".selected").toggleClass("selected");
                $(this).toggleClass("selected");
            });
            $('#Table_Modernization_Equipment tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = Table_Modernization_Equipment.row( tr );
         
                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    row.child( formatEquipment(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );
            <?php if(!$Mobile){?>
            yadcf.init(Table_Modernization_Equipment,[
                {   column_number:2},
                {   column_number:3},
                {   column_number:8}
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