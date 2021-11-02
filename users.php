<?php 
session_start();
require('cgi-bin/php/index.php');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array        = sqlsrv_fetch_array($r);
    $User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID= ?",array($_SESSION['User']));
    $User         = sqlsrv_fetch_array($User);
    $Field        = ($User['Field'] == 1 && $User['Title'] != 'OFFICE') ? True : False;
    $r            = sqlsrv_query($Portal,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE User_ID = '{$_SESSION['User']}'
    ;");
    $My_Privileges   = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged   = FALSE;
    if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4 && $My_Privileges['Ticket']['Group_Privilege'] >= 4 && $My_Privileges['Ticket']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "users.php"));
    if(!isset($array['ID'])  || !$Privileged){?><html><head><script>document.location.href='../login.php?Forward=users.php';</script></head></html><?php }
    else {

?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require(PROJECT_ROOT.'php/meta.php');?>    
    <title>Nouveau Texas | Portal</title>    
    <?php require(PROJECT_ROOT."css/index.php");?>
    <?php require(PROJECT_ROOT.'js/index.php');?>
</head>
<body onload='finishLoadingPage();'>

    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
    	<?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php //require(PROJECT_ROOT.'php/element/navigation/users.php');
        		//file is missing?    ?>

        <?php require(PROJECT_ROOT.'php/element/loading.php');?>

        <div id="page-wrapper" class='content'>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h3><?php $Icons->Users();?> Users</h3></div>
                        <div class="panel-body">
							<div id='Form_User'>
								<div class="panel panel-primary">
									<div class="panel-heading" style='position:fixed;width:750px;z-index:999;'><h2 style='display:block;'>User Details</h2></div>
									<div class="panel-body white-background BankGothic shadow" style='padding-top:100px;'>
										<div style='display:block !important;'>
											<fieldset >
												<legend>User</legend>
												<editor-field name='ID'></editor-field>
												<editor-field name='Email'></editor-field>
												<editor-field name='Password'></editor-field>
												<editor-field name='First_Name'></editor-field>
												<editor-field name='Last_Name'></editor-field>
												<editor-field name='Phone'></editor-field>
												<editor-field name='Roles'></editor-field>
											</fieldset>
										</div>
									</div>
								</div>
							</div>
                            <table id='Table_Users' class='display' cellspacing='0' width='100%'>
                                <thead>
                                    <th title="ID">ID</th>
                                    <th title="First Name">First Name</th>
                                    <th title="Last Name">Last Name</th>
									<th title='Email'>Email</th>
									<th title='Phone'>Phone</th>
                                    <th title="Roles">Roles</th>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/metisMenu/metisMenu.js"></script>
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    <script src="../dist/js/sb-admin-2.js"></script>
    <script src="../dist/js/moment.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script>
		var Editor_Users = new $.fn.dataTable.Editor({
			ajax: "php/post/User.php?ID=<?php echo $_GET['ID'];?>",
			table: "#Table_Users",
			template: '#Form_User',
			formOptions: {
				inline: {
					submit: "allIfChanged"
				}
			},
			idSrc: "ID",
			fields : [{
				label: "ID",
				name: "ID"
			},{
				label: "First Name",
				name: "First_Name"
			},{
				label: "Last Name",
				name: "Last_Name"
			},{
				label: "Email",
				name: "Email"
			},{
				label:"Phone",
				name:"Phone"
			},{
				label: "Roles",
				name: "Roles"
			}]
		});
		Editor_Users.field('ID').disable();
		Editor_Users.field('ID').hide();
		/*$('#Table_Users').on( 'click', 'tbody td:not(:first-child)', function (e) {
			Editor_Users.inline( this );
		} );*/
		var Table_Users = $('#Table_Users').DataTable( {
			"ajax": {
				"url":"cgi-bin/php/get/Users.php",
				"dataSrc":function(json){if(!json.data){json.data = [];}return json.data;}
			},
			"columns": [
				{ 
					"data": "ID",
					"className":"hidden"
				},{ 
					"data": "First_Name",
					render:function(data){
						return toProperCase(data);
					}
				},{ 
					"data": "Last_Name",
					render:function(data){
						return toProperCase(data);
					}
				},{ 
					"data": "Email"
				},{ 
					"data": "Phone"
				},{ 
					"data": "Roles"
				}
			],
			"buttons":[
				{
					extend: 'collection',
					text: 'Export',
					buttons: [
						'copy',
						'excel',
						'csv',
						'pdf',
						'print'
					]
				},
				/*{ extend: "create", editor: Editor_Users },
				{ extend: "edit",   editor: Editor_Users },
				{ 
					extend: "remove", 
					editor: Editor_Users, 
					formButtons: [
						'Delete',
						{ text: 'Cancel', action: function () { this.close(); } }
					]
				},*/
				{ text:"View",
				  action:function(e,dt,node,config){
					  document.location.href = 'user.php?ID=' + $("#Table_Users tbody tr.selected td:first-child").html();
				  }
				}
			],
			"order": [[2, 'asc'],[1, 'asc']],
			<?php require('cgi-bin/js/datatableOptions.php');?>
		} );
    </script>
</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='login.php?Forward=directory.php';</script></head></html><?php }?>