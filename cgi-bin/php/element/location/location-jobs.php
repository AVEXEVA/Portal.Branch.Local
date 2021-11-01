<?php
if(session_id() == '' || !isset($_SESSION) ){
  session_start();
  require('/var/www/nouveautexas.com/html/portal/cgi-bin/php/index.php');
}
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
    sqlsrv_query($NEI,"INSERT INTO Activity([User], [Date], [Page]) VALUES(?,?,?);",array($_SESSION['User'],date("Y-m-d H:i:s"), "location.php"));
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
                    Terr.Name            AS Territory_Domain
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
        $data = $Location;
        $job_result = sqlsrv_query($NEI,"
            SELECT Job.ID AS ID
            FROM   Job
            WHERE  Job.Loc = ?
        ;",array($_GET['ID']));
        if($job_result){
            $Jobs = array();
            $dates = array();
            $totals = array();
            while($array = sqlsrv_fetch_array($job_result)){$Jobs[] = "[JOBLABOR].[JOB #]='{$array['ID']}'";}
            $SQL_Jobs = implode(" OR ",$Jobs);
        }?>
				<div class="panel panel-primary" style='margin-bottom:0px;'>
					<div class="panel-body">
						<div class="row">
							<div class='col-md-12' >
								<div class="panel panel-primary">
									<!--<div class="panel-heading"><h3><i class="fa fa-bell fa-fw"></i> Jobs Table</h3></div>-->
									<div class="panel-body  BankGothic shadow">
										<table id='Table_Jobs' class='display' cellspacing='0' width='100%' style='font-size:12px'>
											<thead>
												<th>ID</th>
												<th>Name</th>
												<th>Type</th>
												<th>Date</th>
											</thead>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!-- /.panel -->
			</div>
        </div>
    </div>
	<style>
		.border-seperate {
			border-bottom:3px solid #333333;
		}
	</style>
	<script>
		var Editor_Jobs = new $.fn.dataTable.Editor({
			ajax: "cgi-bin/php/get/Active_Jobs_by_Location.php?ID=<?php echo $_GET['ID'];?>",
			table: "#Table_Jobs",
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
				label: "Name",
				name: "Name"
			},{
				label: "Location",
				name: "Location",
				type: "select",
				options: [<?php
					$r = sqlsrv_query($NEI,"
						SELECT   Loc.Tag
						FROM     Loc
						WHERE    Loc.Owner = ?
						ORDER BY Loc.Tag ASC
					;",array($_GET['ID']));
					$Tags = array();
					if($r){while($Tag = sqlsrv_fetch_array($r)){
						$Tag['Tag'] = str_replace("'","",$Tag['Tag']);
						$Tags[] = '{' . "label: '{$Tag['Tag']}', value:'{$Tag['Tag']}'" . '}';
					}}
					echo implode(",",$Tags);
				?>]
			},{
				label: "Type",
				name: "Type",
				type: "select",
				options: [<?php
					$r = sqlsrv_query($NEI,"
						SELECT   JobType.ID,
								 JobType.Type
						FROM     JobType
						ORDER BY JobType.Type ASC
					;");
					$Types = array();
					if($r){while($Type = sqlsrv_fetch_array($r)){$Types[] = '{' . "label: '{$Type['Type']}', value:'{$Type['Type']}'" . '}';}}
					echo implode(",",$Types);
				?>]
			},{
				label: "Date",
				name: "Date",
				type:"datetime"
			},{
				label: "Status",
				name: "Status",
				type: "select",
				options: [<?php
					$r = sqlsrv_query($NEI,"
						SELECT   Job_Status.ID,
								 Job_Status.Status
						FROM     Job_Status
						ORDER BY Job_Status.ID ASC
					;");
					$Statuses = array();
					if($r){while($Status = sqlsrv_fetch_array($r)){
						$Statuses[] = '{' . "label: '{$Status['Status']}', value:'{$Status['Status']}'" . '}';
					}}
					echo implode(",",$Statuses);
				?>]
			}]
		});
		Editor_Jobs.field('ID').disable();
		/*$('#Table_Jobs').on( 'click', 'tbody td:not(:first-child)', function (e) {
			Editor_Jobs.inline( this );
		} );*/
		var Table_Jobs = $('#Table_Jobs').DataTable( {
			"ajax": {
				"url":"cgi-bin/php/get/Jobs_by_Location.php?ID=<?php echo $_GET['ID'];?>",
				"dataSrc":function(json){
					if(!json.data){json.data = [];}
					return json.data;}
			},
			"columns": [
				{
					"data": "ID"
				},{
					"data": "Name"
				},{
					"data": "Type"
				},{
					"data": "Date",
					render: function(data){return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
				}
			],
			"buttons":[/*
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
				{ extend: "create", editor: Editor_Jobs },
				{ extend: "edit",   editor: Editor_Jobs },
				{
					extend: "remove",
					editor: Editor_Jobs,
					formButtons: [
						'Delete',
						{ text: 'Cancel', action: function () { this.close(); } }
					]
				},
				{ text:"View",
				  action:function(e,dt,node,config){
					  if($("#Table_Jobs tbody tr.selected").length > 0){
						document.location.href = 'job.php?ID=' + $("#Table_Jobs tbody tr.selected td:first-child").html();
					  }
				  }
				}
			*/],
			<?php require('../../../js/datatableOptions.php');?>
		} );
		//$("Table#Table_Jobs").on("draw.dt",function(){hrefJobs();});
		<?php if(!$Mobile){?>
			yadcf.init(Table_Jobs,[
				{   column_number:0,
					filter_type:"auto_complete",
					filter_default_label:"ID"},
				{   column_number:1,
					filter_type:"auto_complete",
					filter_default_label:"Name"},
				{   column_number:2,
					filter_default_label:"Location"},
				{   column_number:3,
					filter_default_label:"Type"},
				{   column_number:4,
					filter_type: "range_date",
					date_format: "mm/dd/yyyy",
					filter_delay: 500},
				{   column_number:5,
					filter_default_label:"Status"}
			]);
			stylizeYADCF();
		<?php }?>
		function hrefTickets(){hrefRow("Table_Jobs","job");}
	$("Table#Table_Jobs").on("draw.dt",function(){hrefTickets();});
	</script>

<?php
    }
} else {?><html><head><script>document.location.href="../login.php?Forward=location<?php echo (!isset($_GET['ID']) || !is_numeric($_GET['ID'])) ? "s.php" : ".php?ID={$_GET['ID']}";?>";</script></head></html><?php }?>
