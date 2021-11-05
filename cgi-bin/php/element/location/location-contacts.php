<?php
session_start( [ 'read_and_close' => true ] );
require('../../../../cgi-bin/php/index.php');
setlocale(LC_MONETARY, 'en_US');
if(isset($_SESSION['User'],$_SESSION['Hash'])){
    $r = sqlsrv_query($NEI,"SELECT * FROM Connection WHERE Connector = ? AND Hash = ?;",array($_SESSION['User'],$_SESSION['Hash']));
    $array = sqlsrv_fetch_array($r);
    $My_User = sqlsrv_query($NEI,"SELECT *, fFirst AS First_Name, Last as Last_Name FROM Emp WHERE ID = ?",array($_SESSION['User']));
    $My_User = sqlsrv_fetch_array($My_User);
    $Field = ($My_User['Field'] == 1 && $My_User['Title'] != "OFFICE") ? True : False;
    $r = sqlsrv_query($NEI,"
        SELECT Access_Table, User_Privilege, Group_Privilege, Other_Privilege
        FROM   Privilege
        WHERE  User_ID = ?
    ;",array($_SESSION['User']));
    $My_Privileges = array();
    while($array2 = sqlsrv_fetch_array($r)){$My_Privileges[$array2['Access_Table']] = $array2;}
    $Privileged = FALSE;
    if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >= 4 && $My_Privileges['Location']['Group_Privilege'] >= 4 && $My_Privileges['Location']['Other_Privilege'] >= 4){$Privileged = TRUE;}
    elseif($My_Privileges['Location']['User_Privilege'] >= 4 && is_numeric($_GET['ID'])){
        $r = sqlsrv_query(  $NEI,"
		SELECT 	*
		FROM 	TicketO
		WHERE 	TicketO.LID='{$_GET['ID']}'
				AND fWork='{$My_User['fWork']}'");
        $r2 = sqlsrv_query( $NEI,"
		SELECT 	*
		FROM 	TicketD
		WHERE 	TicketD.Loc='{$_GET['ID']}'
				AND fWork='{$My_User['fWork']}'");
        $r3 = sqlsrv_query( $NEI,"
		SELECT 	*
		FROM 	TicketDArchive
		WHERE 	TicketDArchive.Loc='{$_GET['ID']}'
				AND fWork='{$My_User['fWork']}'");
        $r = sqlsrv_fetch_array($r);
        $r2 = sqlsrv_fetch_array($r2);
		$r3 = sqlsrv_fetch_array($r3);
        $Privileged = (is_array($r) || is_array($r2) || is_array($r3)) ? TRUE : FALSE;
    }
    sqlsrv_query($Portal,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "location.php"));
    if(!isset($array['ID'])  || !$Privileged || !is_numeric($_GET['ID'])){?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }
    else {
        $ID = $_GET['ID'];
        $r = sqlsrv_query($NEI,
            "SELECT TOP 1
                    Loc.Loc              AS Location_ID,
                    Loc.ID               AS Name,
                    Loc.Tag              AS Tag,
                    Loc.Address          AS Street,
                    Loc.City             AS City,
                    Loc.State            AS State,
                    Loc.Zip              AS Zip,
                    Loc.Balance          as Location_Balance,
                    Zone.Name            AS Zone,
                    Loc.Route            AS Route_ID,
                    Emp.ID               AS Route_Mechanic_ID,
                    Emp.fFirst           AS Route_Mechanic_First_Name,
                    Emp.Last             AS Route_Mechanic_Last_Name,
                    Loc.Owner            AS Customer_ID,
                    OwnerWithRol.Name    AS Customer_Name,
                    OwnerWithRol.Balance AS Customer_Balance,
                    Terr.Name            AS Territory_Domain/*,
                    Sum(SELECT Location.ID FROM Loc AS Location WHERE Location.Owner='Loc.Owner') AS Customer_Locations*/
            FROM    Loc
                    LEFT JOIN Zone         ON Loc.Zone   = Zone.ID
                    LEFT JOIN Route        ON Loc.Route  = Route.ID
                    LEFT JOIN Emp          ON Route.Mech = Emp.fWork
                    LEFT JOIN OwnerWithRol ON Loc.Owner  = OwnerWithRol.ID
                    LEFT JOIN Terr         		   ON Terr.ID    = Loc.Terr
            WHERE
                    Loc.Loc = ?
        ;",array($_GET['ID']));
        $Location = sqlsrv_fetch_array($r);
        $data = $Location;?>
							<div class="panel panel-primary">
								<!--<div class="panel-heading"><h4><?php $Icons->Users();?> Workers Table</h4></div>-->
								<div class="panel-body  BankGothic shadow">
									<table id='Table_Contacts' class='display' cellspacing='0' width='100%' style='font-size:12px;'>
										<thead>
											<th>ID</th>
											<th>Contact</th>
											<th>Phone</th>
											<th>Email</th>
											<th>Fax</th>
											<th>Street</th>
											<th>City</th>
											<th>Zip</th>
											<th>State</th>
										</thead>
									</table>
								</div>
							</div>

		<script>
	var Table_People = $('#Table_Contacts').DataTable( {
		"ajax": "cgi-bin/php/get/Contacts_by_Location.php?ID=<?php echo $_GET['ID'];?>",
		"columns": [
			{ "data" : "ID","classname":"hidden"      },
			{ "data" : "Contact" },
			{ "data" : "Phone"   },
			{ "data" : "EMail"   },
			{ "data" : "Fax"     },
			{ "data" : "Address"  },
			{ "data" : "City"    },
			{ "data" : "Zip"     },
			{ "data" : "State"   }
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
			{ text:"View",
			  action:function(e,dt,node,config){
				  document.location.href = 'contact.php?ID=' + $("#Table_Units tbody tr.selected td:first-child").html();
			  }
			}
		],
		<?php if(!isMobile()){?>"buttons":[
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
			/*{ extend: "create", editor: Editor_Locations },
			{ extend: "edit",   editor: Editor_Locations },
			{
				extend: "remove",
				editor: Editor_Locations,
				formButtons: [
					'Delete',
					{ text: 'Cancel', action: function () { this.close(); } }
				]
			},*/
			{ text:"View",
			  action:function(e,dt,node,config){
				  var data = Table_Locations.rows({selected:true}).data()[0];
				  document.location.href = 'location.php?ID=' + data.ID;
			  }
			}
		],<?php }?>
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
	function hrefContacts(){hrefRow("Table_Contacts","contact");}
	$("Table#Table_Contacts").on("draw.dt",function(){hrefContacts();});
	</script>
<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
