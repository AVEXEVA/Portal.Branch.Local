<?php
if(session_id() == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require('cgi-bin/php/index.php');
}

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
  			     Emp.Last   AS Last_Name,
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
  if(	!isset($My_Connection['ID']) ){?><?php require('../404.html');?><?php }
  else {
?><!DOCTYPE html>
<html lang="en">
<head>
    <?php require( bin_meta . 'index.php');?>
    <title>Nouveau Texas | Portal</title>
    <?php require( bin_css . 'index.php');?>
    <?php require( bin_js . 'index.php');?>
</head>
<body onload='finishLoadingPage();'>
	<?php require(PROJECT_ROOT.'php/element/navigation/index.php');?>
    <div id="page-wrapper" class='content'>
    	<link rel='stylesheet' href='cgi-bin/css/home.css' />
		<div class='row'><?php
		$Start_Date = date('Y-m-01 00:00:00.000');
		$end = date('m') == 2 ? 28 : 30;//if(date('m')==2) {$end = 28;}  else { $end = 30;}
		$End_Date = date('Y-m-t 23:59:59.999');
		if(isset($_SESSION['User'])) { $fWork = $_SESSION['User'];}
		$query = sqlsrv_query(
			$NEI,
			" SELECT Count(*) AS Count
			  FROM TicketO LEFT JOIN Emp ON TicketO.fWork = Emp.fWork
			  WHERE (
			    TicketO.Assigned = 1
			    OR TicketO.Assigned = 2
			    OR TicketO.Assigned = 3
			    OR TicketO.Assigned = 5
			  ) AND Emp.ID = ?;",  
			array(
				$_SESSION['User']
			)
		);
		$open_tickets_count = sqlsrv_fetch_array($query)['Count'];
		$data = array();
		$query = sqlsrv_query(
            $NEI,
            " SELECT Count(Tickets.ID) AS Count
              FROM  (
                    ( 
                      SELECT Emp.ID AS Emp,
                             TicketO.EDate AS EDate
                      FROM   TicketO 
                             LEFT JOIN Emp ON TicketO.fWork = Emp.fWork 
	                  ) UNION ALL (
                      SELECT Emp.ID AS Emp,
                             TicketD.EDate AS EDate
                      FROM   TicketD LEFT JOIN Emp ON TicketD.fWork = Emp.fWork 
	                  )
            ) AS Tickets
            WHERE     Tickets.Emp = ?
                  AND Tickets.EDate >= ?
                  AND Tickets.EDate <= ?;",
        	array(
          		$_SESSION['User'],
          		$Start_Date,
          		$End_Date
        	)
      	);
		$total_tickets_count = intval(sqlsrv_fetch_array($query)['Count']);
    if($total_tickets_count != 0){
		  $ticket_percentage = round(100 - (($open_tickets_count / ($total_tickets_count)) * 100));
    } else {
      $ticket_percentage = 1;
    }
		$Year_Start = date("Y-01-01 00:00:00.000");
		$Next_Year_Start = date("Y-01-01 00:00:00.000", strtotime("+1 year"));
		$service_count = "
			SELECT Count(*) AS Count
			FROM   (
						(SELECT TicketO.Level, TicketO.EDate, TicketO.fWork, TicketO.Assigned FROM TicketO)
						UNION ALL
						(SELECT TicketD.Level, TicketD.EDate, TicketD.fWork, '4' as Assigned FROM TicketD)
				   ) AS Tickets
			       LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
			WHERE  (
					Tickets.Assigned = 1
					OR Tickets.Assigned = 2
					OR Tickets.Assigned = 3
					OR Tickets.Assigned = 5
				   )
				   AND Tickets.Level = 1
				   AND Tickets.EDate >= ?
				   AND Tickets.EDate < ?
				   AND Emp.ID = ?";
		$query = sqlsrv_query($NEI,$service_count,array($Year_Start, $Next_Year_Start, $_SESSION['User']));
		$service_tickets_count = sqlsrv_fetch_array($query)['Count'];
		$total_service_count = "
			SELECT Count(*) AS Count
			FROM   (
						(SELECT TicketO.Level, TicketO.EDate, TicketO.fWork, TicketO.Assigned FROM TicketO)
						UNION ALL
						(SELECT TicketD.Level, TicketD.EDate, TicketD.fWork, '4' as Assigned FROM TicketD)
				   ) AS Tickets
			       LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
			WHERE  (Tickets.Level = 1
				   AND Tickets.EDate >= ?
				   AND Tickets.EDate < ?
				   AND Emp.ID = ?)";
		$query = sqlsrv_query($NEI,$total_service_count,array($Year_Start, $Next_Year_Start, $_SESSION['User']));
		$total_service_tickets_count = sqlsrv_fetch_array($query)['Count'];
    if($total_service_tickets_count != 0){
  		$service_ticket_percentage = round(100 - (($service_tickets_count / ($total_service_tickets_count)) * 100));
    } else {
      $service_ticket_percentage = 1;
    }
		$violation_count = "
			SELECT Count(*) AS Count
			FROM   (
						(SELECT TicketO.Level, TicketO.EDate, TicketO.fWork, TicketO.Assigned FROM TicketO)
						UNION ALL
						(SELECT TicketD.Level, TicketD.Edate, TicketD.fWork, '4' as Assigned FROM TicketD)
				   ) AS Tickets
				   LEFT JOIN Emp ON Tickets.fWork = Emp.fWork
			WHERE  (
					Tickets.Assigned = 1
					OR Tickets.Assigned = 2
					OR Tickets.Assigned = 3
					OR Tickets.Assigned = 5
				   )
				   AND Tickets.Level = 4
			       AND Tickets.EDate >= ?
			       AND Tickets.EDate < ?
			       AND Emp.ID = ?";
		$query = sqlsrv_query($NEI,$violation_count,array($Year_Start, $Next_Year_Start, $_SESSION['User']));
		$violation_tickets_count = sqlsrv_fetch_array($query)['Count'];
    if($total_service_tickets_count != 0){
		    $violation_ticket_percentage = round(100 - (($violation_tickets_count / ($total_service_tickets_count)) * 100));
    } else {
      $violation_ticket_percentage = 1;
    }
		if ($ticket_percentage <= 50) {$open_color = "danger";}
			else if ($ticket_percentage >= 60) {$open_color = "warning";}
			else if ($ticket_percentage >= 80) {$open_color = "info";}
			else if ($ticket_percentage >= 90) {$open_color = "success";}
		if ($service_tickets_count >= 3) {$service_color = "danger";}
			else if ($service_tickets_count >= 2) {$service_color = "warning";}
			else if ($service_tickets_count >= 1) {$service_color = "info";}
			else if ($service_tickets_count >= 0) {$service_color = "success";}
		if ($violation_tickets_count >= 3) {$violation_color = "danger";}
			else if ($violation_tickets_count >= 2) {$violation_color = "warning";}
			else if ($violation_tickets_count >= 1) {$violation_color = "info";}
			else if ($violation_tickets_count >= 0) {$violation_color = "success";}
		?>
		<section class="container" style='background-color:#2d2d2d !important;color:white !important;<?php if(!isset($_SESSION['toggleActivity']) || $_SESSION['toggleActivity'] == 1){?>display:block !important;<?php }else{?>display:none !important;<?php }?>'>
      <script>
      var checked = true;
      function changeSelectAllSafety(){
        $(".popup input.safety").each(function(){
          $(this).prop('checked',checked);
        });
        checked = !checked;
      }
      var safety_acknowledgement_popup = "<div class='popup' style='background-color:#1d1d1d;color:black;top:0;position:absolute;left:0;width:100%;height:100%;'><div class='panel panel-primary'><div class='panel-heading' style='padding-top:25px;'> Acknowledgement of Safety <h2></div><div class='panel-body' style='padding:25px;'> <div class='row'> <div class='col-xs-6'>Hardhat</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='Hardhat' /></div> </div> <div class='row'> <div class='col-xs-6'>Safety Book</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='Safety_Book' /></div> </div> <div class='row'> <div class='col-xs-6'>First Aid Kit</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='First_Aid_Kit' /></div> </div> <div class='row'> <div class='col-xs-6'>Dust Masks</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='Dusk_Masks' /></div> </div> <div class='row'> <div class='col-xs-6'>Lock Out Kit</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='Lock_Out_Kit' /></div> </div> <div class='row'> <div class='col-xs-6'>Safety Glasses</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='Safety_Glasses' /></div> </div> <div class='row'> <div class='col-xs-6'>GFCI</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='GFCI' /></div> </div> <div class='row'> <div class='col-xs-6'>Select All</div> <div class='col-xs-6'><input class='safety' value='checked' name='Select_All_Safety' onChange='changeSelectAllSafety();' type='checkbox' /></div> </div> <div class='row'> <div class='col-xs-12'>&nbsp;</div> </div> <div class='row'> <div class='col-xs-12'>&nbsp;</div> </div> <div class='row'> <div class='col-xs-6'>Safety Harness Set</div> <div class='col-xs-6'><input value='checked' type='checkbox' name='Safety_Harness_Set' /></div> </div> <div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'><button onClick='submit_Acknowledgement_of_Safety(this);' style='width:100%;'>Acknowledgement of Safety</button></div></div></div>";
      var dt_notes = '';
      function attendance_clock(link){
        //Notes
        if($("textarea[name='notes_in']").length > 0){dt_notes = $("textarea[name='notes_in']").val();}
        if($("textarea[name='notes_out']").length > 0){dt_notes = $("textarea[name='notes_out']").val();}
        //IF Tree
        if($(link).attr('rel').includes('in')){
          $("body").css("overflow","hidden");
          $('.popup').remove();
          $('body').append(safety_acknowledgement_popup);
        } else if($(link).attr('rel').includes('out')) {
          $('.popup').remove();
            $.ajax({
              url:"review-tickets.php",
              success:function(code){
                $("body").append(code);
              }
            });
        }
      }
      function complete_clockout(){
        if('<?php
          $r = sqlsrv_query($NEI,"SELECT * FROM TicketO WHERE TicketO.Assigned >= 2 AND TicketO.Assigned <= 3 AND TicketO.fWork = ?;",array($My_User['fWork']));
          if($r && is_array(sqlsrv_fetch_array($r))){echo 'True';}
          else{echo 'False';}
        ?>' == 'True'){alert('You must finish your tickets before you clock out.');}
        else {
          $(".popup").remove();
          var link = $("button[rel='out']");
          var d = new Date();
          var hours = d.getHours();
          var minutes = d.getMinutes();
          var flip = '';
          if(hours >= 12){
            hours = hours - 12;
            if(hours == 0){
              hours = 12;
            }
            flip = 'PM';
          } else {
            flip = 'AM';
          }
          if(hours < 10){
            hours = "0" + hours.toString();
          }
          if(minutes < 10){
            minutes = "0" + minutes.toString();
          }
          var year = d.getFullYear();
          var month = parseFloat(d.getMonth()) + 1;
          var day = d.getDate();
          $.ajax({
            method:"POST",
            data: {Notes : dt_notes},
            url:"cgi-bin/php/post/clock_out.php",
            success:function(code){
              $(link).replaceWith(code);
            }
          });

        }
      }
      function submit_Acknowledgement_of_Safety(link){
        //if(!$("input[name='Safety_Harness']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        //if(!$("input[name='Backpack']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        if(!$("input[name='Hardhat']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        if(!$("input[name='Safety_Book']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        //if(!$("input[name='Palm_Gloves']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        if(!$("input[name='First_Aid_Kit']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        if(!$("input[name='Dusk_Masks']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        if(!$("input[name='Lock_Out_Kit']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        //if(!$("input[name='Goggles']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        if(!$("input[name='Safety_Glasses']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        if(!$("input[name='GFCI']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        //if(!$("input[name='Safety_Harness_Set']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
        $("body").css("overflow","visible");
        $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
        $(link).attr('disabled','disabled')
        $.ajax({method:"POST",data:{Notes : dt_notes}, url:"cgi-bin/php/post/clock_in.php",success:function(code){
            document.location.href='home3.php';
        }});

      }
      function clock_in_menu(){
        $("body").append("<div class='popup' style='background-color:#1d1d1d;color:white !important;top:0;position:absolute;left:0;width:100%;height:100%;'><div class='panel panel-primary'><div class='panel-heading'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'>&nbsp;</div></div></div><div class='panel-heading'>Clock In Notes</div></div class='panel-bodwy'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'><textarea name='notes_in' style='width:100%;color:black;' rows='10'></textarea></div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'><input style='color:black;width:100%;height:50px;' rel='in_notes' type='button' onClick='attendance_clock(this);' value='Save' /></div></div></div></div>");
      }
      function clock_out_menu(){
        $("body").append("<div class='popup' style='background-color:#1d1d1d;color:white !important;top:0;position:absolute;left:0;width:100%;height:100%;'><div class='panel panel-primary'><div class='panel-heading'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'>&nbsp;</div></div></div><div class='panel-heading'>Clock In Notes</div></div class='panel-bodwy'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'><textarea name='notes_out' style='width:100%;color:black;' rows='10'></textarea></div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'><input style='color:black;width:100%;height:50px;' rel='out_notes' type='button' onClick='attendance_clock(this);' value='Save' /></div></div></div></div>");
      }
      </script>
      <div class='panel-body' style='padding-top:15px;padding-bottom:15px;'>
        <?php
        $r = sqlsrv_query($NEI, "SELECT * FROM Attendance WHERE Attendance.[User] = ? AND Attendance.[End] IS NULL",array($_SESSION['User']));
        if($r){$row = sqlsrv_fetch_array($r);}
        ?>
        <div class='row'>
          <div class='col-xs-6' style='text-align:center;'><?php if(is_array($row) && isset($row['Start'])){echo date("m/d/Y h:i A",strtotime($row['Start'])); } else {?><button rel='in' style='color:black !important;' onClick='attendance_clock(this);'>Clock In</button><button style='color:black !important;' onClick='clock_in_menu();'>+</button><?php }?></div>
          <div class='col-xs-6' style='text-align:center;'><button rel='out' style='color:black !important;' onClick='attendance_clock(this);'>Clock Out</button><!--<button style='color:black !important;' onClick='clock_out_menu(link);'>+</button>--></div>
        </div>
      </div>
		<div class="row">
			<div class="col-md-12 col-sm-12">
				<div class="projects">
					<strong>Monthly Maintenance Tickets Progress</strong>
						<div class="progress">
							<div class="progress-bar progress-bar-<?php echo $open_color;?> progress-bar-striped active" role="progressbar"
							aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $ticket_percentage . "%";?>;">
				/			<strong><?php echo $total_tickets_count-$open_tickets_count;?></strong>
							</div>
							<div class="progress-bar progess-bar-<?php echo $open_color;?> progress-bar-striped active" role="progressbar" aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo 100 - $ticket_percentage ."%";?>">
							<strong><?php echo $open_tickets_count . " out of " . $total_tickets_count . " tickets remaining"; ?></strong>
							</div>

						</div>
						<?php if($service_tickets_count > 0){?><strong>Service Tickets Progress</strong>
						<div class="progress">
							<div class="progress-bar progress-bar-<?php echo $service_color;?> progress-bar-striped active" role="progressbar"
							aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $service_ticket_percentage . "%";?>;">
							<strong><?php echo $service_tickets_count . " tickets remaining"; ?></strong>
							</div>
						</div><?php }?>
            <?php if($violation_tickets_count > 0){?>
						<strong>Violation Tickets Progress</strong>
						<div class="progress">
							<div class="progress-bar progress-bar-<?php echo $violation_color;?> progress-bar-striped active" role="progressbar"
							aria-valuenow="30" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $violation_ticket_percentage . "%";?>;">
							<strong><?php echo $violation_tickets_count . " tickets remaining"; ?></strong>
							</div>
						</div>
          <?php }?>
					</div>
				</div>
			</div>
		</div>

		</section>
    <script>
    function toggle_activities(link){
      $('section.container').toggle();
      $.ajax({url:"cgi-bin/php/post/toggleActivity.php"});
    }
    </script>
    <div class='row'><div class='col-xs-12'><button style='width:100%;color:black !important;' onClick='toggle_activities(this);'>Hide/Show</button></div></div>
		<?php if(isset($My_Privileges['Collection']) && $My_Privileges['Invoice']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='collections.php'">
			<div class='nav-icon'><?php $Icons->Collection(3);?></div>
			<div class ='nav-text'>Collections</div>
		</div><?php } ?>
    <?php if(isset($My_Privileges['Time']) && $My_Privileges['Time']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='scheduler.php'">
      <div class='nav-icon'><i class="fa fa-question-circle fa-3x fa-fw" aria-hidden="true"></i></div>
      <div class ='nav-text'>Attendance</div>
    </div><?php }?>
    <?php if(isset($My_Privileges['Time']) && $My_Privileges['Time']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='attendance_report.php'">
      <div class='nav-icon'><i class="fa fa-clock-o fa-3x fa-fw" aria-hidden="true"></i></div>
      <div class ='nav-text'>Clocker</div>
    </div><?php }?>
			<?php if(isset($My_Privileges['Customer']) && $My_Privileges['Customer']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='customers.php'">
				<div class='nav-icon'><?php $Icons->Customer(3);?></div>
				<div class ='nav-text'>Customers</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['Dispatch']) && $My_Privileges['Dispatch']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='dispatch.php'">
				<div class='nav-icon'><?php $Icons->Dispatch(3);?></div>
				<div class ='nav-text'>Dispatch</div>
			</div><?php } ?>
      <?php if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='tickets.php'">
				<div class='nav-icon'><?php $Icons->History(3);?></div>
				<div class ='nav-text'>History</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['Invoice']) && $My_Privileges['Invoice']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='invoices.php'">
				<div class='nav-icon'><?php $Icons->Invoice(3);?></div>
				<div class ='nav-text'>Invoices</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['Job']) && $My_Privileges['Job']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='jobs.php'">
				<div class='nav-icon'><?php $Icons->Job(3);?></div>
				<div class ='nav-text'>Jobs</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['Lead']) && $My_Privileges['Lead']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='leads.php'">
					<div class='nav-icon'><?php $Icons->Customer(3);?></div>
					<div class ='nav-text'>Leads</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['Location']) && $My_Privileges['Location']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='locations.php'">
					<div class='nav-icon'><?php $Icons->Location(3);?></div>
					<div class ='nav-text'>Locations</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['Privilege']) && $My_Privileges['Privilege']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='privileges.php'">
				<div class='nav-icon'><?php $Icons->Privilege(3);?></div>
				<div class ='nav-text'>Privileges</div>
			</div><?php } ?>
			<div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='user.php'">
					<div class='nav-icon'><?php $Icons->User(3);?></div>
					<div class ='nav-text'>Profile</div>
			</div>
			<?php if(isset($My_Privileges['Proposal']) && $My_Privileges['Proposal']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='proposals.php'">
				<div class='nav-icon'><?php $Icons->Proposal(3);?></div>
				<div class ='nav-text'>Proposals</div>
			</div><?php } ?>
      <?php if(isset($My_Privileges['Requisition']) && $My_Privileges['Requisition']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='requisitions.php'">
				<div class='nav-icon'><?php $Icons->Requisition(3);?></div>
				<div class ='nav-text'>Requisitions</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['Route']) && $My_Privileges['Route']['Other_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='routes.php'">
				<div class='nav-icon'><?php $Icons->Route(3);?></div>
				<div class ='nav-text'>Routes</div>
			</div><?php } ?>
			<?php
			$result = sqlsrv_query($NEI,"
				SELECT Route.ID
				FROM   Route
					   LEFT JOIN Emp ON Route.Mech = Emp.fWork
				WHERE  Emp.ID = ?
			;",array($_SESSION['User']));
			$RouteNav = sqlsrv_fetch_array($result);
			if(isset($My_Privileges['Route']) && $My_Privileges['Route']['User_Privilege'] >=4 && is_array($RouteNav) && isset($RouteNav['ID']) && $RouteNav['ID'] > 0 ){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='route.php?ID=<?php echo $RouteNav['ID'];?>'">
				<div class='nav-icon'><?php $Icons->Route(3);?></div>
				<div class ='nav-text'>Route</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['Safety_Report']) && $My_Privileges['Safety_Report']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='reports.php'">
				<div class='nav-icon'><?php $Icons->Report(3);?></div>
				<div class ='nav-text'>Reports</div>
			</div><?php } ?>
      <?php if(isset($My_Privileges['Admin']) && $My_Privileges['Admin']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='supervisors.php'">
				<div class='nav-icon'><?php $Icons->Customer(3);?></div>
				<div class ='nav-text'>Supervisors</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['Territory']) && $My_Privileges['Territory']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='territories.php'">
					<div class='nav-icon'><?php $Icons->Territory(3);?></div>
					<div class ='nav-text'>Territories</div>
			</div><?php }?>
      <?php if((isset($My_Privileges['Admin']) && $My_Privileges['Admin']['Other_Privilege'] >= 4) || (isset($My_Privileges['Testing']) && $My_Privileges['Testing']['User_Privilege'] >= 4)){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='category-test.php'">
        <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Test</div>
      </div><?php }?>
			<?php if(isset($My_Privileges['Time']) && $My_Privileges['Time']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='timesheet.php'">
					<div class='nav-icon'><?php $Icons->Timesheet(3);?></div>
					<div class ='nav-text'>Timesheet</div>
			</div><?php }?>
			<?php if(isset($My_Privileges['Unit']) && $My_Privileges['Unit']['User_Privilege'] >= 4 || $My_Privileges['Unit']['Group_Privilege'] >= 4 || $My_Privileges['Unit']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='units.php'">
				<div class='nav-icon'><?php $Icons->Unit(3);?></div>
				<div class ='nav-text'>Units</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['User']) && $My_Privileges['User']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='users.php'">
				<div class='nav-icon'><?php $Icons->Users(3);?></div>
				<div class ='nav-text'>Users</div>
			</div><?php } ?>
			<?php if(isset($My_Privileges['Violation']) && $My_Privileges['Violation']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='violations.php'">
				<div class='nav-icon'><?php $Icons->Violation(3);?></div>
				<div class ='nav-text'>Violations</div>
			</div><?php } ?>
		<div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='https://beta.nouveauelevator.com/';">
			<div class='nav-icon'><?php $Icons->Web(3);?></div>
			<div class ='nav-text'>Website</div>
		</div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='work.php'">
        <div class='nav-icon'><?php $Icons->Ticket(3);?></div>
        <div class ='nav-text'>Work</div>
      </div>
      <div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='../login.php?Logout=TRUE'">
		<div class='nav-icon'><?php $Icons->Logout(3);?></div>
		<div class ='nav-text'>Logout</div>
	</div>
      <?php if(isset($My_Privileges['Ticket']) && $My_Privileges['Ticket']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-lg-1 col-md-2 col-xs-3' onclick="document.location.href='support.php'">
				<div class='nav-icon'><i class="fa fa-question-circle fa-3x fa-fw" aria-hidden="true"></i></div>
				<div class ='nav-text'>Support</div>
			</div><?php }?>
		</div>
    </div>
    
    <?php require(PROJECT_ROOT.'js/datatables.php');?>
    

</body>
</html>
<?php
    }
} else {?><html><head><script>document.location.href='../login.php?Forward=home2.php';</script></head></html><?php }
?>
