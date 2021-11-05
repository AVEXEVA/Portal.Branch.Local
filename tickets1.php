<?php
session_start( [ 'read_and_close' => true ] );
require('cgi-bin/php/index.php');
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
	   	|| !isset($My_Privileges['Ticket'])
	  		|| $My_Privileges['Ticket']['User_Privilege']  < 4
	  		|| $My_Privileges['Ticket']['Group_Privilege'] < 4){
				?><?php require('../404.html');?><?php }
    else {
		$database->query(null,"
			INSERT INTO Portal.dbo.Activity([User], [Date], [Page])
			VALUES(?,?,?)
		;",array($_SESSION['User'],date("Y-m-d H:i:s"), "tickets.php"));

if(isMobile()){?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>

</head>

<body style='background-color:#1d1d1d;'>
    <div id="wrapper" class="<?php echo isset($_SESSION['Toggle_Menu']) ? $_SESSION['Toggle_Menu'] : null;?>">
        <?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
        <?php require( bin_php . 'element/loading.php');?>
        <div id="page-wrapper" class='content'>
			<div class="panel panel-primary" style='margin-bottom:0px;'>
				<div class="panel-heading"><?php
				$_GET['Mechanic'] = isset($_GET['Mechanic']) ? $_GET['Mechanic'] : $_SESSION['User'];
				if(is_numeric($_GET['Mechanic'])){$r = $database->query(null,"SELECT Emp.* FROM Emp WHERE Emp.ID='" . $_GET['Mechanic']. "';");$r = sqlsrv_fetch_array($r);$Mechanic = $r;}
						else {$Mechanic = $User;}?><?php \singleton\fontawesome::getInstance( )->Ticket();?><?php echo proper($Mechanic['fFirst'] . " " . $Mechanic['Last']);?>'s Tickets</div>

				<div class="panel-body no-print">
          <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
					<div class="row">
						<div class='col-md-3'>
							<div class='row'>
								<div class='col-xs-4'>
									<label class='date' for="filter_start_date">Start Date:</label>
								</div>
								<div class='col-xs-8 input-group'>
									<input class='start_date form-control' size='10' name='filter_start_date' value='<?php echo isset($_GET['Start_Date']) && strlen($_GET['Start_Date']) > 1 ? $_GET['Start_Date'] : date("m/01/Y",strtotime('now'));?>' />
								</div>
							</div>
							<div class='row'>
								<div class='col-xs-4'>
									<label class='date' for="filter_end_date">End Date:</label>
								</div>
								<div class='col-xs-8 input-group'>
									<input class='end_date form-control' size='10' name='filter_end_date'  value='<?php echo isset($_GET['End_Date']) && strlen($_GET['End_Date']) > 1 ? $_GET['End_Date'] : '12/31/2018';?>'/><br />
								</div>
							</div>
              <div class='row'>
								<div class='col-xs-4'>
									<label class='tag' for='filter_today'>Date Filters:</label>
								</div>
                <div class='col-xs-8' onClick=""><button onClick="toggle_display('filter_dates');" style='width:100%;height:50px;'>Show</button></div>
								<div id='filter_dates' class='toggle_display col-xs-12 input-group'>
									<button class='form-control' onClick="today();">Today</button>
									<button class='form-control' onClick="yesterday();">Yesterday</button>
									<button class='form-control' onClick="this_week();">This Week</button>
									<button class='form-control' onClick="this_month();">This Month</button>
									<button class='form-control' onClick="last_week();">Last Week</button>
									<button class='form-control' onClick="last_month();">Last Month</button>
									<button class='form-control' onClick="this_year();">This Year</button>
									<button class='form-control' onClick="all_time();">All Time</button>
								</div>
							</div>
              <style>.toggle_display {display:none;}.toggle_display.active{display:block;}</style>
              <script>
              function toggle_display(id){
                $("#" + id).toggleClass('active');
              }
              </script>
						</div>
						<?php if(!isMobile()){?><div class='col-md-9'>
							<div id="map" style='height:350px;overflow:visible;width:100%;'></div>
						</div>
						<script type="text/javascript">
						  function initialize() {
							var latlng = new google.maps.LatLng(40.7831, -73.9712);
							var myOptions = {
							  zoom: 10,
							  center: latlng,
							  mapTypeId: google.maps.MapTypeId.ROADMAP
							};
							var map = new google.maps.Map(document.getElementById("map"),
								myOptions);
							var marker = new Array();
						<?php
						if(isset($_GET['Start_Date'])){$Start_Date = DateTime::createFromFormat('m/d/Y', $_GET['Start_Date'])->format("Y-m-d 00:00:00.000");}
						else{$Start_Date = DateTime::createFromFormat('m/d/Y',"01/01/2018")->format("Y-m-d 00:00:00.000");}

						if(isset($_GET['End_Date'])){$End_Date = DateTime::createFromFormat('m/d/Y', $_GET['End_Date'])->format("Y-m-d 23:59:59.999");}
						else{$End_Date = DateTime::createFromFormat('m/d/Y',"12/30/2050")->format("Y-m-d 23:59:59.999");}
						$r = $database->query(null,"
							SELECT
								TechLocation.*
							FROM
								TechLocation
							WHERE
								TechLocation.DateTimeRecorded >= '{$Start_Date}'
								AND TechLocation.DateTimeRecorded <= '{$End_Date}'
								AND TechLocation.TechID = '{$Mechanic['fWork']}'
						;");
						$GPS_Locations = array();
						while($array = sqlsrv_fetch_array($r)){$GPS_Locations[$array['ID']] = $array;}
						foreach($GPS_Locations as $ID=>$GPS_Location){?>
							marker[<?php echo $ID;?>] = new google.maps.Marker({
							  position: {lat:<?php echo $GPS_Location['Latitude'];?>,lng:<?php echo $GPS_Location['Longitude'];?>},
							  map: map,
							  title: '<?php echo $GPS_Location['DateTimeRecorded'];?>',
							  Icon: 'http://vps9073.inmotionhosting.com/~skeera6/portal/images/GoogleMapsMarkers/<?php if($GPS_Location['ActionGroup'] == 'Completed time'){?>green_MarkerC<?php }
							  elseif($GPS_Location['ActionGroup'] == 'On site time'){?>yellow_MarkerO<?php }
							  elseif($GPS_Location['ActionGroup'] == 'General'){?>paleblue_MarkerG<?php }?>.png'
							})
							<?php if($GPS_Location['ActionGroup'] != 'General' || $GPS_Location['Ticket_ID'] > 0){?>marker[<?php echo $ID?>].addListener('click',function(){
								document.location.href='ticket.php?ID=<?php echo $GPS_Location['TicketID'];?>';
							});<?php }?>
						  <?php }?>}</script>
						<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>
						<?php }?>
            <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
				</div>
			</div>
			<div class='panel-heading no-print'>&nbsp;</div>
			<div class="panel-body">
				<table id='Table_Tickets' class='display' cellspacing='0' width='100%' <?php if(isMobile()){?>style='font-size:10px;'<?php }?>>
					<thead>
						<?php if(!$Mobile){?><th></th><?php }?>
						<th title='ID of the Ticket'><?php if($Mobile){?>ID<?php }?></th>
						<th title='Location of the Ticket'><?php if($Mobile){?>Location<?php }?></th>
            <th title='Unit of the Ticket'><?php if($Mobile){?>Unit<?php }?></th>
						<th title='Status of the Ticket'><?php if($Mobile){?>Status<?php }?></th>
						<th title='Scheduled Work Time'>Scheduled</th>
						<th title='Total Hours'>Hours</th>
						<th title='Only Valid Past 3/8/17'><?php if($Mobile){?>Paid <?php }?></th>
					</thead>
				</table>
			</div>
		</div>
    </div>
    <Style>
    tr:not([class]) {
      background-color:#1d1d1d !important;
      color:white !important;
    }
    </style>
    <!-- Bootstrap Core JavaScript -->
    

    <!-- Metis Menu Plugin JavaScript -->
    

    <!-- Morris Charts JavaScript -->
    <!--<script src="https://www.nouveauelevator.com/vendor/raphael/raphael.min.js"></script>
    <script src="https://www.nouveauelevator.com/vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.php"></script>-->

    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    

    <!-- Custom Theme JavaScript -->
    

    <!--Moment JS Date Formatter-->
    

    <!-- JQUERY UI Javascript -->
    

    <!-- Custom Date Filters-->
    
    <style>
    div.column {display:inline-block;vertical-align:top;}
    div.label1 {display:inline-block;font-weight:bold;width:150px;vertical-align:top;}
    div.data {display:inline-block;width:300px;vertical-align:top;}
    </style>
	<script src="cgi-bin/js/function/formatTicket.js"></script>
    <script>
        var reset_loc = 0;
        $(document).ready(function(){
            $("input.start_date").datepicker({
                onSelect:function(dateText, inst){
                    document.location.href="tickets.php?Dashboard=Mechanic&Mechanic=<?php echo (isset($_GET['Mechanic'])) ? $_GET['Mechanic'] : $_SESSION['User'];?>&Start_Date=" + dateText + "&End_Date=" + $("input.end_date").val() + "&Location_Tag=" + $("select[name='filter_location_tag']").val() + "" + "&Show_Hours=" + $("input#show_hours[type='radio']:checked").val() + "&Show_Tickets=" + $("input#show_tickets[type='radio']:checked").val();
                }
            });
            $("input.end_date").datepicker({
                onSelect:function(dateText, inst){
                    document.location.href="tickets.php?Dashboard=Mechanic&Mechanic=<?php echo (isset($_GET['Mechanic'])) ? $_GET['Mechanic'] : $_SESSION['User'];?>&Start_Date=" + $("input.start_date").val() + "&End_Date=" + dateText + "&Location_Tag=" + $("select[name='filter_location_tag']").val() + "" + "&Show_Hours=" + $("input#show_hours[type='radio']:checked").val() + "&Show_Tickets=" + $("input#show_tickets[type='radio']:checked").val();
                }
            });
        });
        function refresh_get(){
            var Location_Tags = $("select[name='filter_location_tag']").val();
            $(".location_tag").each(function(){
                Location_Tags += "," + $(this).html();
            });

            document.location.href="tickets.php?Dashboard=Mechanic&Mechanic=<?php echo (isset($_GET['Mechanic'])) ? $_GET['Mechanic'] : $_SESSION['User'];?>&Start_Date=" + $("input.start_date").val() + "&End_Date=" + $("input.end_date").val() + "&Location_Tag=" + Location_Tags + "&Show_Hours=" + $("input#show_hours[type='radio']:checked").val() + "&Show_Tickets=" + $("input#show_tickets[type='radio']:checked").val();
        }
        function filter_location(){refresh_get();}
        function toggle_hours(){refresh_get();}
        function toggle_tickets(){refresh_get();}
        function format ( d ) {
            var description = d.Description;
            if(description == null || description === false){description = '';}
            var resolution = d.Resolution;
            if(resolution == null || resolution === false){resolution = '';}
            return '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;background-color:#1d1d1d;width:100%;"><tbody style="background-color:#1d1d1d;">'+
                '<tr>'+
                    '<td style="background-color:#1d1d1d;">ID:</td>'+
                    '<td style="background-color:#1d1d1d;">'+d.ID+'</td>'+
                '</tr>'+
                '<tr>'+
                    '<td style="background-color:#1d1d1d;">Description:</td>'+
                    '<td style="background-color:#1d1d1d;">'+description+'</td>'+
                '</tr>'+
                '<tr>'+
                    '<td style="background-color:#1d1d1d;">Resolution:</td>'+
                    '<td style="background-color:#1d1d1d;">'+resolution+'</td>'+
                '</tr>'+
                '<tr>'+
                    '<td colspan="2"><button style="width:100%;height:42px;" onClick="document.location.href=\'ticket2.php?ID='+d.ID+'\';"><?php \singleton\fontawesome::getInstance( )->Ticket();?>View Ticket</a></td>'+
                '</tr>'+
            '</tbody></table>';
        }
        $(document).ready(function() {
            var Location_Tags = ""
            $(".location_tag").each(function(){
                Location_Tags += "," + $(this).html();
            });
            Location_Tags = Location_Tags.substring(1);
            var Table_Tickets = $('#Table_Tickets').DataTable( {
                'ajax': {
                        "url": "cgi-bin/php/get/Tickets.php?Dashboard=Mechanic&Mechanic=<?php echo (isset($_GET['Mechanic'])) ? $_GET['Mechanic'] : $_SESSION['User'];?>&Start_Date=" + $("input.start_date").val() + "&End_Date=" + $("input.end_date").val() + "&Location_Tag=" + Location_Tags + "" + "&Show_Hours=" + $("input#show_hours[type='radio']:checked").val() + "&Show_Tickets=" + $("input#show_tickets[type='radio']:checked").val(),
                        "dataSrc":function(json){
                            if(!json.data){json.data = [];}
                            return json.data;}
                },
                "columns": [
                    <?php if(!isMobile()){?>{
                        "className":      'details-control',
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": ''
                    },<?php }?>
                    {
          						"data": "ID",
                      "visible":false
          					},{
          						"data": "Tag"
          					},{
                      "data": "Unit_State"
                    },{
          						"data": "Status"
                      <?php if(isMobile()){?>,"visible":false<?php }?>
          					},{
                        "data": "Date",
                        render: function(data) {return data.substr(5,2) + "/" + data.substr(8,2) + "/" + data.substr(0,4);}
                    },{
                        "data": "Total",
                        "defaultContent":"0.00"
                    },{
                        "data": "ClearPR",
                        "defaultContent":"Unpaid",
                        render: function(data) {return data == '0' || data == 'Unknown' ? "N" : "Y";}
                    }
                ],
                "lengthMenu":[[10,25,50,100,500,-1],[10,25,50,100,500,"All"]],
                "order": [[1, 'asc']],
                "language":{"loadingRecords":""},
                "initComplete":function(){finishLoadingPage();},
				        "paging":false//,
                /*"drawCallback": function ( settings ) {
                  //hrefTickets(this.api());
                }*/

            } );
            <?php if(!isMobile()){?>$('#Table_Tickets tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = Table_Tickets.row( tr );

                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    row.child( formatTicket(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );<?php } else {?>
			      $('#Table_Tickets tbody').on('click', 'td', function () {
                var tr = $(this).closest('tr');
                var row = Table_Tickets.row( tr );

                if ( row.child.isShown() ) {
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            } );
			<?php }?>
            <?php if(!$Mobile){?>
            yadcf.init(Table_Tickets,[
                {   column_number:1,
                    filter_type:"auto_complete",
					filter_default_label:"Search ID"},
                {   column_number:2,
					filter_default_label:"Select Location"},
                {   column_number:3,
					filter_default_label:"Select Status"},
                {   column_number:4,
                    filter_type: "range_date",
                    date_format: "mm/dd/yyyy",
                    filter_delay: 500},
                {   column_number:5,
                    filter_type: "range_number_slider",
                    filter_delay: 500},
                {   column_number:6,
					filter_default_label:"Select Payroll"}/*,
                {   column_number:7 }*/
            ]);
            $("div.yadcf-filter-wrapper").addClass("input-group");
            $("select.yadcf-filter").addClass("form-control");
            $("input.yadcf-filter").addClass("form-control");
            <?php }?>
        } );
        function hrefTickets(tbl){
          $("table#Table_Tickets tbody tr").each(function(){
            $(this).on('click',function(){
              document.location.href='ticket2.php?ID=' + tbl.row(this).data().ID;
            });
          });
        }
    </script>
</body>
</html>
<?php
} else {
  $_GET['processing'] = 1;
  require('../beta/tickets.php');
}
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=tickets.php';</script></head></html><?php }?>
