<?php 
session_start( [ 'read_and_close' => true ] );
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
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "permits.php"));
		if(count($_POST) > 0){
			if(isset($_POST['Location']) && is_numeric($_POST['Location']) && isset($_POST['Name']) && strlen($_POST['Name']) > 0){
				sqlsrv_query($NEI,"
					INSERT INTO Permit(Name, Description, Location, Type, Expiration, Link)
					VALUES(?,?,?,?,?,?)
				;",array($POST['Name'],$_POST['Description'],$_POST['Location'],$_POST['Type'],$_POST['Expiration'],$_POST['Link']));
			}
		}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Peter D. Speranza">    <title>Nouveau Texas | Portal</title>    
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require( bin_php . 'element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class='panel-heading'><h4>
                            <div style='display:inline-block;'>
                                <span onClick="document.location.href='purchasing.php'" style='cursor:pointer;'><?php \singleton\fontawesome::getInstance( )->Unit();?>Tracked Permits</span>
                                <span class='hidden' onClick="modernizationTracker('modernization_equipment');" style='cursor:pointer;'><span id='modernization_equipment'> > Equipment Entity</span></span>
                            </div>
                            <div style='clear:both;'></div>
                        </h4></div>
                        <div class="panel-body" id='content'>
							<div class='row'>
								<div id='addPermit' class="col-lg-3 col-md-3" onClick="addPermit();" style='cursor:pointer;'>
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3"><i class="fa fa-cogs fa-3x"></i></div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
														<div class="medium">Add Permit</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div id='editPermit' class="col-lg-3 col-md-3" onClick="editPermit();" style='cursor:pointer;'>
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3"><i class="fa fa-cogs fa-3x"></i></div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
														<div class="medium">Edit Permit</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
								<div id='deletePermit' class="col-lg-3 col-md-3" onClick="deletePermit();" style='cursor:pointer;'>
									<div class="panel panel-primary">
										<div class="panel-heading">
											<div class="row">
												<div class="col-xs-3"><i class="fa fa-cogs fa-3x"></i></div>
												<div class="col-xs-9 text-right">
													<div class="col-xs-9 text-right">
														<div class="medium">Delete Permit</div>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
                            <table id='Table_Permits' class='display' cellspacing='0' width='100%' style='border-collapse:collapse !important;'>
                                <thead>
									<th>Internal ID</th>
                                    <th>Permit Name</th>
                                    <th>Type</th>
									<th>Location</th>
									<th>Expiration</th>
									<th>Description</th>
									<th>Link</th>
                                    <th></th>
									<th></th>
                                </thead>
                                <tbody>
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
    

    <!-- Metis Menu Plugin JavaScript -->
        

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    
    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
    
    <script>
        $(document).ready(function(){
            
        });
    </script>
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
	var Table_Permits = $('#Table_Permits').DataTable( {
		"ajax": {
			"url":"cgi-bin/php/get/Permits.php",
			"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
		},
		"columns": [
			{ "data": "ID", "className" : "hidden" },
			{ "data": "Name"},
			{ "data": "Type"},
			{ "data": "Location"},
			{ "data": "Expiration",render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}},
			{ "data": "Description"},
			{ "data": "Link"},
			{ "data": "Location_ID", "className" : "hidden"}

		],
		"orderFixed": [[3, 'asc']],
		"language":{"loadingRecords":""},
		"lengthMenu":[[-1,10,25,50,100,500,0],["All",10,25,50,100,500,"None"]],
		"drawCallback": function ( settings ) {
			var api = this.api();
			var rows = api.rows( {page:'current'} ).nodes();
			var last=null;

			api.column(3, {page:'current'} ).data().each( function ( group, i ) {
				if ( last !== group ) {
					$(rows).eq( i ).before(
						'<tr class="group" style="background-color:#151515;color:white;font-weight:bold;text-align:center;"><td colspan="8">'+group+'</td></tr>'
					);

					last = group;
				}
			} );
		},
		"initComplete":function(){
		}
	});
	var Locations = [<?php 
		$r = sqlsrv_query($NEI,"
			SELECT Loc.Loc AS Loc,
				   Loc.Tag AS Tag
			FROM   nei.dbo.Loc
			WHERE  Loc.Maint = 1
		;");
		$Locations = array();
		if($r){while($Location = sqlsrv_fetch_array($r)){
			$Location['Tag'] = str_replace("'","`",$Location['Tag']);
			$Locations[] = "{value:'{$Location['Loc']}', label:'{$Location['Tag']}'}";
		}}
		echo implode(",",$Locations);
		
	?>];
	function addPermit(){
		$("#Table_Permits").prepend("<tr id='addPermit' style='border-bottom:3px solid #333333;'><td class='hidden'></td><td><input type='text' name='Name' placeholder='Name' /></td><td><select name='Type'><option value='EA'>EA</option><option value='EBN'>EBN</option></select></td><td><input name='Location_Name' type='text' size='30' /><input type='hidden' name='Location' /></td><td><input type='text' name='Expiration' /></td><td><textarea name='Description' cols='50' rows='3'></textarea></td><td><input type='text' name='Link' /></td></tr>");
		$("div#addPermit").attr("onClick","savePermit();")
		$("div#addPermit div.medium").html("Save Permit");
		$(document).ready(function(){
			$("input[name='Location_Name']").autocomplete({
				source: Locations,
				minLength: 3,
				select: function (event, ui) {
					event.preventDefault();
					$("input[name='Location_Name']").val(ui.item.label); // display the selected text
					$("input[name='Location']").val(ui.item.value); // save selected id to hidden input
				}
			});
		});
	}
	function savePermit(){
		if($("tr#addPermit").length == 1){
			var tr = $("tr#addPermit");
			var dName = $("tr#addPermit input[name='Name']").val();
			var dType = $("tr#addPermit select[name='Type']").val();
			var dExpiration = $("tr#addPermit input[name='Expiration']").val();
			var dDescription = $("tr#addPermit textarea[name='Description']").val();
			var dLocation = $("tr#addPermit input[name='Location']").val();
			var dLink = $("tr#addPermit input[name='Link']").val();
			$.ajax({
				url:"cgi-bin/php/post/addPermit.php",
				data: {Name : dName, Type : dType, Expiration: dExpiration, Description: dDescription, Location: dLocation, Link: dLink},
				method:"POST",
				success:function(code){
					Table_Permits.ajax.reload();
					$("div#addPermit div.medium").html("Add Permit");
					$("div#addPermit").attr("onClick","addPermit();")
				}
			});
		} else {
			var tr = $("tr#editPermit");
			var dID = $("tr#editPermit td:first-child").html();
			var dName = $("tr#editPermit input[name='Name']").val();
			var dType = $("tr#editPermit select[name='Type']").val();
			var dExpiration = $("tr#editPermit input[name='Expiration']").val();
			var dDescription = $("tr#editPermit textarea[name='Description']").val();
			var dLocation = $("tr#editPermit input[name='Location']").val();
			var dLink = $("tr#editPermit input[name='Link']").val();
			$.ajax({
				url:"cgi-bin/php/post/updatePermit.php",
				data: {ID: dID, Name : dName, Type : dType, Expiration: dExpiration, Description: dDescription, Location: dLocation, Link: dLink},
				method:"POST",
				success:function(code){
					Table_Permits.ajax.reload();
					$("div#addPermit div.medium").html("Add Permit");
					$("div#addPermit").attr("onClick","addPermit();")
				}
			});
			$("div#addPermit div.medium").html("Add Permit");
			$("div#addPermit").attr("onClick","addPermit();")
		}
	}
	function editPermit(){
		var tr = $(".selected");
		var dID = $("tr.selected td:first-child").html();
		var dName = $("tr.selected td:nth-child(2)").html();
		var dType = $("tr.selected td:nth-child(3)").html();
		var dLocation = $("tr.selected td:nth-child(4)").html();
		var dExpiration = $("tr.selected td:nth-child(5)").html();
		var dDescription = $("tr.selected td:nth-child(6)").html();
		var dLink = $("tr.selected td:nth-child(7)").html();
		var dLocationID = $("tr.selected td:last-child").html();
		var htmlType;
		if(dType == "EA"){htmlType = "<option value='EA' selected='selected'>EA</option><option value='EBN'>EBN</option>";}
		else {htmlType = "<option value='EA' selected='selected'>EA</option><option value='EBN' selected='selected'>EBN</option>";}
		$(tr).replaceWith("<tr id='editPermit' style='border-bottom:3px solid #333333;'><td class='hidden'>" + dID + "</td><td><input type='text' name='Name' placeholder='Name' value='" + dName + "' /></td><td><select name='Type'>" + htmlType + "</select></td><td><input name='Location_Name' type='text' size='30' value='" + dLocation + "' /><input type='hidden' name='Location' value='" + dLocationID + "' /></td><td><input type='text' name='Expiration' value='" + dExpiration + "' /></td><td><textarea name='Description' cols='50' rows='3'>" + dDescription + "</textarea></td><td><input type='text' name='Link' value='" + dLink + "' /></td></tr>");
		$("div#addPermit").attr("onClick","savePermit();");
		$("div#addPermit div.medium").html("Save Permit");
		$(document).ready(function(){
			$("input[name='Location_Name']").autocomplete({
				source: Locations,
				minLength: 3,
				select: function (event, ui) {
					event.preventDefault();
					$("input[name='Location_Name']").val(ui.item.label); // display the selected text
					$("input[name='Location']").val(ui.item.value); // save selected id to hidden input
				}
			});
		});
	}
	function deletePermit(){
		var Permit_ID = $(".selected").children("td:first-child").html();
		$.ajax({
			url:"cgi-bin/php/post/deletePermit.php",
			method:"POST",
			data:{ID:Permit_ID},
			success:function(code){
				Table_Permits.ajax.reload();
			}
		})
	}
    $(document).ready(function(){
        finishLoadingPage();
    });
    function hyperlinkInput(link){
        if($(link).val().includes("http://") || $(link).val().includes("https://")){
            $(link).parent().append("<div class='miniPopup' style='position:absolute;top:-25px;width:100%;height:25px;;background-color:white;padding-left:10px;'><a href='" + $(link).val() + "' target='_blank' style='text-decoration:underline;color:blue;'>" + $(link).val() + "</a></div>");
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
        $(document).on("click",function(){
            $(".miniPopup").remove();
        });
		$('#Table_Permits tbody').on( 'click', 'tr', function () {
			if ( $(this).hasClass('selected') ) {
				$(this).removeClass('selected');
			}
			else {
				Table_Permits.$('tr.selected').removeClass('selected');
				$(this).addClass('selected');
			}
		} );
    });
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=modernizations.php';</script></head></html><?php }?>