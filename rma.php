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
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "rma.php"));
        if(count($_POST) > 0){
        	if(isset($_POST['Name']) && !isset($_POST['Address'])){
            	sqlsrv_query($Portal,"INSERT INTO RMA(Name, Date, Address, RMA, Recieved, Returned, Tracking, PO, Link, Description, Status) VALUES(?,?,?,?,?,?,?)",array($_POST['Name'],$_POST['Date'],$_POST['Address'],$_POST['RMA'],$_POST['Recieved'],$_POST['Returned'],$_POST['Tracking'],$_POST['PO'],$_POST['Link'],$_POST['Description'],$_POST['Status']));
            }
        }?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>    
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
    <style>.hidden {display:none;}</style>
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
                            <span onClick="document.location.href='purchasing.php'" style='cursor:pointer;float:left;'><?php $Icons->Unit();?>Tracking RMAs</span>
                            <div style='float:right;margin-left:25px;margin-right:25px;' onClick="saveRMA();"><button style='color:black;'>Save</button></div>
                            <div style='float:right;margin-left:25px;margin-right:25px;' onClick="newRMA();"><button style='color:black;'>New</button></div>
                            <div style='clear:both;'></div>
                        </h4></div>
                        <style>
                        table thead tr th, table tbody tr td {
                            padding:10px !important;
                            border:1px solid black;
                            vertical-align:top;
                            overflow:hidden;
                        }
                        table {
                            width:100%;
                            table-layout:fixed;
                        }
                        </style>
                        <div class="panel-body" id='content'>
                            <table id='Table_Insured_Companies' class='display' cellspacing='0' width='100%'>
                                <thead style='background-color:#252525;color:white;'><tr>
                                	<th class='hidden'>ID</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
                                    <th>Address</th>
                                    <th>PO</th>
                                    <th>RMA #</th>
                                    <th>NEI Received</th>
                                    <th>NEI Returned</th>
                                    <th>Tracking #</th>
                                    <th>Link</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                </tr></thead>
                                <script>
                                    function newRMA(){
                                        var Company = prompt("What is the RMA's Name?");
                                        if(Company == null){return;}
                                        var string = "Name=" + encodeURIComponent(Company);
                                        $.ajax({
                                            url:"cgi-bin/php/post/addRMA.php",
                                            data:string,
                                            method:"POST",
                                            success:function(code){document.location.href='rma.php';}
                                        });
                                    }
                                    function deleteRow(link){
                                        var RMA = decodeURIComponent($(link).parent().parent().children("td:nth-child(2)").html());
                                        var response = confirm("Would you like to delete the RMA '" + RMA + "'?");
                                        var ID = $(link).parent().parent().children("td:first-child").html();
                                        if(response){
                                            $.ajax({
                                                url:"cgi-bin/php/post/deleteRMA.php",
                                                method:"POST",
                                                data:"ID=" + ID,
                                                success:function(code){$(link).parent().parent().remove();}
                                            });
                                        }
                                    }
                                </script>
                                <style>
                                tbody tr td.Buttons button {width:100px;}
                                </style>
                                <script>
                                function editRMA(link){
                                	var tr = $(link);
                                    $(tr).addClass("editing");
                                	$(tr).children(".data").each(function(){
                                        var small = "";
                                        if($(this).hasClass('small')){
                                            small = " style='width:100px;height:22px;' ";
                                        } 
                                        if($(this).hasClass("med")){
                                            small = " style='width:200px;height:22px;position:relative;top:-0px;font-size:14px;' ";
                                        }
                                        if($(this).hasClass("textarea")){
                                            $(this).html("<textarea cols='50' rows='5' name='" + $(this).attr('rel') + "'>" + $(this).html() + "</textarea>");
                                        } else if($(this).hasClass("dd")){
                                            if($(this).html() == "Open"){
                                                $(this).html("<select name='Status'><option value=''>Unselected</option><option value='Open' selected='selected'>Open</option><option value='Complete'>Complete</option></select");
                                            } else if($(this).html() == "Complete") {
                                                $(this).html("<select name='Status'><option value=''>Unselected</option><option value='Open' >Open</option><option value='Complete' selected='selected'>Complete</option></select");
                                            } else {
                                                $(this).html("<select name='Status'><option value='' selected='selected'>Unselected</option><option value='Open' >Open</option><option value='Complete'>Complete</option></select");
                                            }
                                        } else if($(this).hasClass('Link')) {
                                            $(this).html("<input type='text' name='" + $(this).attr('rel') + "' value='" + $(this).children("a").html() + "'" + " " + small + " />")
                                        } else {
                                            $(this).html("<input type='text' name='" + $(this).attr('rel') + "' value='" + $(this).html() + "'" + " " + small + " />")
                                        }
                                    ;});
                                	$("input[name='Date']").datepicker({onSelect:function(dateText, inst){}});
                                	$("input[name='Received']").datepicker({onSelect:function(dateText, inst){}});
                                	$("input[name='Returned']").datepicker({onSelect:function(dateText, inst){}});
                                }
                                function saveRMA(link){
                                	$("tr.editing").each(function(){
                                        var tr = this;
                                    	var ID = $(tr).children(".ID").html();
                                    	var Name = encodeURIComponent($(tr).children(".Name").children("input").val());
                                    	var xDate = $(tr).children(".Date").children("input").val();
                                    	var Address = $(tr).children(".Address").children("input").val();
                                    	var RMA = $(tr).children(".RMA").children("input").val();
                                    	var Received = $(tr).children(".Received").children("input").val();
                                    	var Returned = $(tr).children(".Returned").children("input").val();
                                    	var Tracking = $(tr).children(".Tracking").children("input").val();
                                        var PO = $(tr).children(".PO").children("input").val();
                                        var Link = encodeURIComponent($(tr).children(".Link").children("input").val());
                                        var Description = encodeURIComponent($(tr).children(".Description").children("textarea").val());
                                        var Status = $(tr).children(".Status").children("select").val();
                                    	var string = "ID=" + ID + "&Name=" + Name + "&Date=" + xDate + "&Address=" + Address + "&RMA=" + RMA + "&Received=" + Received + "&Returned=" + Returned + "&Tracking=" + Tracking + "&Link=" + Link + "&Description=" + Description + "&Status=" + Status + "&PO=" + PO;
                                    	$.ajax({
                                    		url:"cgi-bin/php/post/updateRMA.php",
                                    		data:string,
                                    		method:"POST",
                                    		success:function(code){revertRMA(tr);}
                                    	});
                                    });
                                }
                                function revertRMA(link){
                                	var tr = $(link);
                                	$(tr).children(".data").children("input").each(function(){if(!$(this).parent().hasClass('Link')){$(this).parent().html($(this).val());}});
                                    $(tr).children(".data").children("input").each(function(){if($(this).parent().hasClass('Link')){$(this).parent().html("<a target='_blank' href='" + $(this).val() + "'>" + $(this).val() + "</a>");}});
                                    $(tr).children(".data").children("textarea").each(function(){$(this).parent().html($(this).val());});
                                    $(tr).children(".data").children("select").each(function(){$(this).parent().html($(this).val());});
                                	$(tr).removeClass("editing");
                                }
                                </script>
                                <tbody>
                                    <?php
                                    	$r = sqlsrv_query($Portal,"SELECT * FROM RMA");
                                    	if($r){while($RMA = sqlsrv_fetch_array($r)){
                                    		?><tr class='RMA'>
                                    		<td class='ID hidden' rel='ID'><?php echo $RMA['ID'];?></td>
                                    		<td class='Name data med' rel='Name'><?php echo $RMA['Name'];?></td>
                                    		<td class='Date data small' rel='Date'><?php echo ($RMA['Date'] != "1900-01-01") ? substr($RMA['Date'],5,2) . '/' . substr($RMA['Date'],8,2) . '/' . substr($RMA['Date'],0,4) : null;?></td>
                                    		<td class='Address data small' rel='Address'><?php echo $RMA['Address'];?></td>
                                            <td class='PO data small' rel='PO'><?php echo $RMA['PO'];?></td>
                                    		<td class='RMA data small' rel='RMA'><?php echo ($RMA['RMA'] != 0) ? $RMA['RMA'] : null; ?></td>
                                    		<td class='Received data small' rel='Received'><?php echo ($RMA['Received'] != "1900-01-01 00:00:00.000") ? substr($RMA['Received'],5,2) . '/' . substr($RMA['Received'],8,2) . '/' . substr($RMA['Received'],0,4) : null;?></td>
                                    		<td class='Returned data small' rel='Returned'><?php echo ($RMA['Returned'] != "1900-01-01 00:00:00.000") ? substr($RMA['Returned'],5,2) . '/' . substr($RMA['Returned'],8,2) . '/' . substr($RMA['Returned'],0,4) : null;?></td>
                                    		<td class='Tracking data small' rel='Tracking'><?php echo $RMA['Tracking'];?></td>
                                            <td class='Link data med link' rel='Link'><a href='<?php echo $RMA['Link'];?>' target="_blank"><?php echo $RMA['Link'];?></a></td>
                                            <td class='Description data textarea' rel='Description'><?php echo $RMA['Description'];?></td>
                                            <td class='Status data dd' rel='Status'><?php echo $RMA['Status'];?></td>
                                    		</tr><?php
                                    	}}
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- /#wrapper -->


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
    <script>
        $(document).ready(function(){
            var Table_Insured_Companies = $("#Table_Insured_Companies").DataTable({"paging":false});
        });
    </script>
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
    $(document).ready(function(){
        finishLoadingPage();
    });
    function hyperlinkInput(link){
        if($(link).val().includes("http://") || $(link).val().includes("https://")){
            $(link).parent().append("<div class='miniPopup' style='position:absolute;top:-25px;width:100%;height:25px;;background-color:white;padding-left:10px;'><a href='" + $(link).val() + "' target='_blank'  style='text-decoration:underline;color:blue;'>" + $(link).val() + "</a></div>");
        }
    }
    function updateHyperlink(link){
        var ID = $(link).attr('rel');
        var hyperlink = $(link).val();
        var string = "ID=" + ID + "&Hyperlink=" + encodeURIComponent(hyperlink);
        $.ajax({
            url:"cgi-bin/php/post/updateHyperlink.php",
            data:string,
            method:"POST",
            success:function(code){}
        });
    }
    function updateActive(link){
        var form_data = $(link).parent().serialize() + "&ID=" + $(link).attr('rel');
        $.ajax({
            url:"cgi-bin/php/post/updateActive.php",
            data:form_data,
            method:"POST",
            success:function(code){}
        });
    }
    $(document).ready(function(){
        $(document).on("click",function(){$(".miniPopup").remove();});
        $("table#Table_Insured_Companies tr.RMA").on("dblclick",function(){
            if($(this).hasClass("editing")){} 
            else {editRMA(this);}
        });
    });
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }?>