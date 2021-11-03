<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php' );
}
if(     !isset( $Databases[ 'Default' ], $_SESSION[ 'User' ], $_SESSION[ 'Connection' ] )
    ||  !connection_privileged( $Databases[ 'Default' ], $_SESSION[ 'User' ], $_SESSION[ 'Connection' ] ) ){
        header( 'Location: https://beta.nouveauelevator.com/login.php' );
        exit; }
$result = sqlsrv_query(
  $Databases[ 'Default' ],
  " SELECT *,
          Emp.fFirst AS First_Name,
          Emp.Last   AS Last_Name,
          Emp.Field  AS Field
    FROM  Emp
    WHERE Emp.ID = ?;",
  array(
    $_SESSION[ 'Connection' ][ 'Branch_ID' ]
  )
);
$User = sqlsrv_fetch_array( $result );

$Privileges = privileges( $Databases[ 'Default' ], $_SESSION[ 'Connection' ][ 'Branch_ID' ] );
?><!DOCTYPE html>
<html lang='en'>
<head>
  <title><?php echo $_SESSION[ 'Connection' ][ 'Branch' ];?> | Portal</title>
  <?php require( bin_meta . 'index.php' );?>
  <?php require( bin_css  . 'index.php' );?>
  <?php require( bin_js   . 'index.php' );?>
  <style>
      button.slim {
        background: none;
        color: inherit;
        border: none;
        padding: 0;
        font: inherit;
        cursor: pointer;
        outline: inherit;
      }
      #main-menu .panel {
        margin:25px; 
        padding:0px;
        -webkit-box-shadow: 0px 1px 25px rgba(237, 237, 237, 0.50);
        -moz-box-shadow:    0px 1px 25px rgba(237, 237, 237, 0.50);
        box-shadow:         0px 1px 25px rgba(237, 237, 237, 0.50);
      }
      #main-menu .panel-heading {
        background-color:rgba( 250, 250, 250, .4 );
        -webkit-box-shadow: 0px 1px 25px rgba(19, 19, 19, 0.50);
        -moz-box-shadow:    0px 1px 25px rgba(19, 19, 19, 0.50);
        box-shadow:         0px 1px 25px rgba(19, 19, 19, 0.50);
      }
      #main-menu .panel-footer {
        -webkit-box-shadow: 0px 1px 20px rgba(19, 19, 19, 0.40);
        -moz-box-shadow:    0px 1px 20px rgba(19, 19, 19, 0.40);
        box-shadow:         0px 1px 20px rgba(19, 19, 19, 0.40);
      }
      button {
        border:0px;
        width:100%;
        height:100%;
        color:black;
        text-align:left;
        background-color:transparent;
      }
      #main-menu div.panel-body {
        max-height:0px;
        overflow:hidden;
      }
      div.toggle-panel.open div.panel-body {
        max-height:750px;
        transition:max-height 1s;
      }
      ul.panel-links {
        margin:20px;
        padding:0px;
        list-style-type:none;
      }
      .Home-Screen-Option {
        border:1px solid gold;
      }
      #clock-menu>.row.Dashboard {
        padding-bottom:20px;
      }
      @media screen and (min-width:1980px) {
        .Home-Screen-Option {
          /*order-radius:100px;*/
          padding:25px;
          border:1px solid gold;
          margin:auto;
          margin-top: 25px;
        }
        #page-wrapper {
          padding-left:16.666%;
          padding-right:16.666%;
        }
        #clock-menu {
          border-bottom-left-radius:25px;
          border-bottom-right-radius:25px;
        }
        #clock-menu>.row.Dashboard {
          padding:20px;
        }
      }
      .Home-Screen-Option:hover {
        background-color:gold !important;
        color:black !important;
      }
      .nav-text{ text-align: center; }
      .nav-icon{ text-align: center; }
  </style>
</head>
<body style='background-color:#0a0a0a !important;'>
  <?php require( bin_php .'element/navigation/index.php');?>
  <div id='page-wrapper' class='content' style='display:block;'>
    <script>
    var checked = true;
    function changeSelectAllSafety(){
      $(".popup input.safety").each(function(){
        $(this).prop('checked',checked);
      });
      checked = !checked;
    }
    var safety_acknowledgement_popup = "<div class='popup' style='background-color:#1d1d1d;color:black;top:50px;position:absolute;left:0;width:100%;height:100%;'><div class='panel panel-primary'><div class='panel-heading' style='padding-top:25px;'> Acknowledgement of Safety <h2></div><div class='panel-body' style='padding:25px;'> <div class='row'> <div class='col-xs-6'>Hardhat</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='Hardhat' /></div> </div> <div class='row'> <div class='col-xs-6'>Safety Book</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='Safety_Book' /></div> </div> <div class='row'> <div class='col-xs-6'>First Aid Kit</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='First_Aid_Kit' /></div> </div> <div class='row'> <div class='col-xs-6'>Dust Masks</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='Dusk_Masks' /></div> </div> <div class='row'> <div class='col-xs-6'>Lock Out Kit</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='Lock_Out_Kit' /></div> </div> <div class='row'> <div class='col-xs-6'>Safety Glasses</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='Safety_Glasses' /></div> </div> <div class='row'> <div class='col-xs-6'>GFCI</div> <div class='col-xs-6'><input class='safety' value='checked' type='checkbox' name='GFCI' /></div> </div> <div class='row'> <div class='col-xs-6'>Select All</div> <div class='col-xs-6'><input class='safety' value='checked' name='Select_All_Safety' onChange='changeSelectAllSafety();' type='checkbox' /></div> </div> <div class='row'> <div class='col-xs-12'>&nbsp;</div> </div> <div class='row'> <div class='col-xs-12'>&nbsp;</div> </div> <div class='row'> <div class='col-xs-6'>Safety Harness Set</div> <div class='col-xs-6'><input value='checked' type='checkbox' name='Safety_Harness_Set' /></div> </div> <div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'><button onClick='submit_Acknowledgement_of_Safety(this);' style='width:90%;margin-left:5%;margin-right:5%;border:1px solid black;height:50px;color:black !important;background-color:lightgray;text-align:center;'>Acknowledgement of Safety</button></div></div></div>";
    var dt_notes = '';
    var covid_19_questionaire_popup = "<div class='popup' style='background-color:#1d1d1d;color:black;top:50px;position:absolute;left:0;width:100%;height:100%;'><div class='panel panel-primary'><div class='panel-heading' style='padding-top:25px;'> COVID 19 Questionaire<h2></div><div class='panel-body' style='padding:25px;'><form id='COVID_19_Questionaire'><div class='row'><div class='col-xs-12'>Have you experienced a fever of 100.4 degrees F or greater, a new cough, new loss of taste or smell or shortness of breath within the past 10 days?</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-6'><input type='radio' name='COVID_19_Questionaire_1' value='No' /> No</div><div class='col-xs-6'><input type='radio' name='COVID_19_Questionaire_1' value='Yes' /> Yes</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'>In the past 10 days, have you gotten a positive result from a COVID-19 test that tested saliva or used a nose or throat swab? (not a blood test)</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-6'><input type='radio' name='COVID_19_Questionaire_2' value='No' /> No</div><div class='col-xs-6'><input type='radio' name='COVID_19_Questionaire_2' value='Yes' /> Yes</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'>To the best of your knowledge, in the past 14 days, have you been in close contact (within 6 feet for at least 10 minutes) with anyone while they had Covid-19?</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-6'><input type='radio' name='COVID_19_Questionaire_3' value='No' /> No</div><div class='col-xs-6'><input type='radio' name='COVID_19_Questionaire_3' value='Yes' /> Yes</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'>In the past 14 days, have you traveled internationally or returned from a state identified by New York State as having widespread community transmission of COVID-19 (other than just passing through the restricted state for less than 24 hours)? Visit https://coronavirus.health.ny.gov/covid-19-travel-advisory for applicable states.</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-6'><input type='radio' name='COVID_19_Questionaire_4' value='No' /> No</div><div class='col-xs-6'><input type='radio' name='COVID_19_Questionaire_4' value='Yes' /> Yes</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'><button name='submitCOVID19Questionaire' type='button' onClick='submit_COVID_19_Questionaire(this);' style='width:90%;margin-left:5%;margin-right:5%;border:1px solid black;height:50px;color:black !important;background-color:lightgray;text-align:center;'>Submit Questionaire</button></div></form></div></div>";
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
        $r = sqlsrv_query($NEI,"SELECT * FROM TicketO LEFT JOIN TicketDPDA ON TicketO.ID = TicketDPDA.ID WHERE ((TicketO.Assigned >= 2 AND TicketO.Assigned <= 3)  OR (TicketDPDA.Comments = '' OR TicketDPDA.Comments IS NULL ) AND 0 = 1 ) AND TicketO.fWork = ? ;",array($User['fWork']));
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
        $.ajax({method:"POST",data: {Notes : dt_notes}, url:"cgi-bin/php/post/clock_out.php"});
        $(link).replaceWith("<div style='border:1px solid black;box-shadow:1px 2px black;'>" + month + "/" + day + "/" + year + " " + hours + ":" + minutes + " " + flip + "</div>");
      }
    }
    function submit_Acknowledgement_of_Safety(link){
      if(!$("input[name='Hardhat']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
      if(!$("input[name='Safety_Book']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
      if(!$("input[name='First_Aid_Kit']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
      if(!$("input[name='Dusk_Masks']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
      if(!$("input[name='Lock_Out_Kit']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
      if(!$("input[name='Safety_Glasses']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
      if(!$("input[name='GFCI']").is(':checked')){alert('You must have all of your safety equipment in order to clock in');return;}
      $("body").css("overflow","visible");
      $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
      $(link).attr('disabled','disabled')
      $("div.popup").replaceWith(covid_19_questionaire_popup);
    }
    function submit_COVID_19_Questionaire(link){
      if($("input[name='COVID_19_Questionaire_1']:checked").length == 0){ alert('Please answer all questions'); return; }
      else if($("input[name='COVID_19_Questionaire_2']:checked").length == 0){ alert('Please answer all questions'); return; }
      else if($("input[name='COVID_19_Questionaire_3']:checked").length == 0){ alert('Please answer all questions'); return; }
      else if($("input[name='COVID_19_Questionaire_4']:checked").length == 0){ alert('Please answer all questions'); return; }

      $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
      $(link).attr('disabled','disabled')

      $.ajax({
        url:'cgi-bin/php/post/COVID19Questionaire.php',
        data:{
          1 : $("input[name='COVID_19_Questionaire_1']:checked").val(),
          2 : $("input[name='COVID_19_Questionaire_2']:checked").val(),
          3 : $("input[name='COVID_19_Questionaire_3']:checked").val(),
          4 : $("input[name='COVID_19_Questionaire_4']:checked").val()
        },
        method:'POST',
        success:function(code){
          if(code == 'No'){
            alert('An employee who does not pass the screening questions must not report to work and should report to their supervisor.');
              document.location.href='home.php';
          } else {
            $.ajax({method:"POST",data:{Notes : dt_notes}, url:"cgi-bin/php/post/clock_in.php",success:function(code){ document.location.href='home.php'; }});
          }
        }
      });
    }
    function clock_in_menu(){
      $("body").append("<div class='popup' style='background-color:#1d1d1d;color:white !important;top:0;position:absolute;left:0;width:100%;height:100%;'><div class='panel panel-primary'><div class='panel-heading'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'>&nbsp;</div></div></div><div class='panel-heading'>Clock In Notes</div></div class='panel-bodwy'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'><textarea name='notes_in' style='width:100%;color:black;' rows='10'></textarea></div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'><input style='color:black;width:100%;height:50px;' rel='in_notes' type='button' onClick='attendance_clock(this);' value='Save' /></div></div></div></div>");
    }
    function clock_out_menu(){
      $("body").append("<div class='popup' style='background-color:#1d1d1d;color:white !important;top:0;position:absolute;left:0;width:100%;height:100%;'><div class='panel panel-primary'><div class='panel-heading'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'>&nbsp;</div></div></div><div class='panel-heading'>Clock In Notes</div><div class='panel-body'><div class='row'><div class='col-xs-12'>&nbsp;</div></div><div class='row'><div class='col-xs-12'><textarea name='notes_out' style='width:100%;color:black;' rows='10'></textarea></div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'>&nbsp;</div><div class='col-xs-12'><input style='color:black;width:100%;height:50px;' rel='out_notes' type='button' onClick='attendance_clock(this);' value='Save' /></div></div></div></div>");
    }
    </script>
    <section id='account-menu' style='padding:50px;background-color:#0f0f0f;'>
      <div class='row'>
        <div class='col-xs-4'>&nbsp;</div>
        <?php 
          $result = sqlsrv_query( 
            $Portal,
            " SELECT  Picture,
                      Picture_Type AS Type 
              FROM    Portal.dbo.Portal 
              WHERE   Portal.Branch = ?
                      AND Portal.Branch_ID = ?;",
            array( 
              $_SESSION[ 'Connection' ][ 'Branch' ],
              $_SESSION[ 'User' ]
            )
          );
          if( $result ){
            $row = sqlsrv_fetch_array( $result );
            if( is_null( $row[ 'Picture' ] ) ){
              ?><div class='col-xs-4'><button class='slim' style='text-align:center;' onClick="browseProfilePicture( );"><img src='media/images/icons/avatar.png'  style='max-width:100%;max-height:200px;' /></button></div><?php
            } else {
              ?><div class='col-xs-4'><button class='slim' style='text-align:center;' onClick="browseProfilePicture( );"><img src='<?php print "data:" . $row['Type'] . ";base64, " . $row['Picture'];?>'  style='max-width:100%;max-height:200px;border:1px solid red;border-radius:100px;' /></button></div><?php
            }
          } else {?><div class='col-xs-4'><button class='slim' style='text-align:center;' onClick="browseProfilePicture( );"><img src='media/images/icons/avatar.png'  style='max-width:100%;max-height:200px;' /></button></div><?php }?>
        <script>
          function browseProfilePicture( ){
            $("body").append( "<div id='UploadProfilePicture' class='hidden' style='background-color:#1d1d1d;color:white !important;top:0;position:absolute;left:0;width:100%;height:100%;'><form><div class='panel panel-primary'><div class='panel-heading'><h3>Upload User Picture</h3></div><div class='panel-body'><div class='row'><div class='col-xs-12'><input onChange='uploadProfilePicture( );' type='file' name='Profile' /></div></div></div></div></form></div>");
            $("#UploadProfilePicture input").click( );
          }
          function uploadProfilePicture( ){
            var formData = new FormData( $( '#UploadProfilePicture form' ) [ 0 ] );
            $.ajax({
              url : 'cgi-bin/php/post/uploadProfilePicture.php',
              method : 'POST',
              cache: false,
              processData: false,
              contentType: false,
              data: formData,
              timeout:10000,
              success: function( ){ document.location.href = 'home.php'; }
            });
            $("#UploadProfilePiocture").remove( );
          }
        </script>
        <div class='col-xs-4'>
          <div class='row'>
            <div class='col-xs-12'><button class='slim' onClick="document.location.href='settings.php';" style='text-align:right;'><i class="fas fa-user-cog fa-2x"></i></button></div>
          </div>
        </div>
      </div>
      <div style='height:5px;'>&nbsp;</div>
      <div class='row'>
        <div class='col-xs-2'>&nbsp;</div>
        <div class='col-xs-8' style='text-align:center;'><?php echo $User[ 'Name' ];?></div>
        <div class='col-xs-2'>&nbsp;</div>
      </div>
      <div class='row'>
        <div class='col-xs-2'>&nbsp;</div>
        <div class='col-xs-8' style='text-align:center;font-size:12px;'><?php echo $User[ 'Title' ];?></div>
        <div class='col-xs-2'>&nbsp;</div>
      </div>
    </section>
    <?php if( $User[ 'Field' ] == 1 ){?>
    <section id='clock-menu' style='background-color:#3d3d3d;'>
      <?php
      $r = sqlsrv_query($NEI, "SELECT * FROM Attendance WHERE Attendance.[User] = ? AND Attendance.[End] IS NULL",array($_SESSION['User']));
      if($r){$Attendance_Activity = sqlsrv_fetch_array($r);}
      ?>
      <div class='row' style='/*-webkit-box-shadow: 0px 1px 20px rgba(19, 19, 19, 0.8);-moz-box-shadow:0px 1px 20px rgba(19, 19, 19, 0.8);box-shadow:0px 1px 20px rgba(19, 19, 19, 0.8);*/';>
        <div class='col-xs-12' style='background-color:#1e1e1e;color:white;padding:20px;text-align:center;'>Field Work</div>
      </div>
      <div class='row' style='padding:20px;'>
        <div class='col-xs-6' style='text-align:center;'>
          <?php if(is_array($Attendance_Activity) && isset($Attendance_Activity['Start'])){
            ?><div style='border:1px solid black;box-shadow:1px 0px black;padding:5px;'><i class="fas fa-business-time fa-fw fa-1x"></i> Clock In</div><?php
            ?><div style='border:1px solid black;box-shadow:1px 2px black;background-color:rgb(170, 170, 170);color:black;padding:5px;'><?php
              echo date("m/d/Y h:i A",strtotime($Attendance_Activity['Start']));
            ?></div><?php
          } else {
            ?><div style='border:1px solid black;box-shadow:1px 0px black;padding:5px;'><i class="fas fa-business-time fa-fw fa-1x"></i> Clock In</div><?php
            ?><button rel='in' style='background-color:rgba(250,250,250,.9);border:1px solid black;box-shadow:1px 2px black;text-align:center;padding:5px;' onClick='attendance_clock(this);'>Start Work</button><?php
          }?>
        </div>
        <div class='col-xs-6' style='text-align:center;'>
          <div style='border:1px solid black;box-shadow:1px 0px black;padding:5px;'><i class="fas fa-clipboard-list"></i> Clock Out</div><?php
          if(is_array($Attendance_Activity) && isset($Attendance_Activity['Start'])){
            ?><button rel='out' onClick='attendance_clock(this);' style='background-color:rgba(250,250,250,.9);border:1px solid black;box-shadow:1px 2px black;text-align:center;padding:5px;'>Finish Work</button><?php
          } else {
            ?><button disabled rel='out' onClick='attendance_clock(this);' style='background-color:rgba(250,250,250,.9);border:1px solid black;box-shadow:1px 2px black;text-align:center;padding:5px;'>Finish Work</button><?php
          }?>
        </div>
      </div>
      
      <style>
        #Table_Tickets th {
          border : 1px solid black;
        }
        #Table_Tickets td {
          border-top : 1px solid gray !important;
        }
        .Dashboard.row .panel-heading {
          padding : 0px;
          margin  : 0px;
        }
        .Dashboard.row .panel-heading .row>div {
          
          border : 1px solid #3d3d3d;
          cursor : pointer;
        }
        .Dashboard.row .panel-body {
          display : none;
        }
        .Dashboard.row .panel-body.active {
          display : block; 
        }
        .Dashboard ul {
        	margin:0px;
        	padding:0px;
        	list-style-type:none;
        	position:relative;
        }
        .Dashboard ul li {
        	display:none;
        	padding : 15px;
        }
        .Dashboard ul li.active, .Dashboard ul li.show {
        	display:block;
        }
        .Dashboard ul li.active:first-child {
        	top:0px;
        }
        .Dashboard ul li.active:nth-child( 2 ){
        	top:50px;
        }
        .Dashboard ul li.active:nth-child( 3 ){
        	top:100px;
        }
        .Dashboard ul li:nth-child( even ) {
        	background-color:#1d1d1d;
        }
        .Dashboard ul li:nth-child( odd ) {
        	background-color:#2d2d2d;
        }
        /*Fix end of row*/
      </style>
      <script>
        function changePanel( link ){
          	changePanelHeading( $( link ).hasClass( 'active' ) ? null : $( link).attr( 'panel' ) );
          	changePanelBody( $( link ).attr( 'panel' ) );
        }
        function changePanelHeading( panel ){
          	$(".Dashboard .panel-heading ul li ").each(function(){ 
          		if( panel === null ){
          			$( this ).addClass( 'show' ); 
          			$( this ).removeClass( 'active' );
      			} else if ( panel != $( this ).attr( 'panel ') ) {
      				$( this ).removeClass( 'show' );
      			} else {
      				$( this ).addClass( 'active' );
      			}
      		});
      		if( panel != null ){
      			$( ".Dashboard .panel-heading ul li[panel='" + panel + "']").addClass( 'active' );
      		}
        } 
        function changePanelBody( panel ){
          $(".Dashboard .panel-body").each(function(){ $( this ).removeClass( 'active' ); });
          $(".Dashboard .panel-body[panel='" + panel + "']").addClass( 'active' );
        }
      </script>
      <style>

      </style>
      <div class='Dashboard row'>
        <div class='col-xs-12'>
          <div class='panel panel-primary'>
            <div class='panel-heading'>
              <div class='row'>
	            <div class='col-xs-12'>
	            	<ul>
	            		<li class='active' onClick="changePanel( this );" panel='Tickets'><?php $Icons->Ticket( 1 );?> Tickets</li>
	            		<li class='' onClick="changePanel( this );" panel='Locations'><?php $Icons->Location( 1 );?> Locations</li>
	            	</ul>
	            </div>
	          </div>
              <?php /*<div class='row'>
                <div class='col-xs-4 active' onClick="changePanel( 'Tickets' );" panelheading='Tickets'><?php $Icons->Ticket( 1 );?> Tickets</div>
                <div class='col-xs-4' onClick="changePanel( 'Locations' );" panelheading='Locations'><?php $Icons->Location( 1 );?> Locations</div>
                <div class='col-xs-2'>&nbsp;</div>
              </div>*/?>
            </div>
            <div class='panel-body active' panel='Tickets'>
              <table id='Table_Tickets' class='display' cellspacing='0' width='100%' style='<?php if(isMobile()){?>font-size:10px;<?php }?>'>
                <thead><tr>
                  <th title='Location'></th>
                  <th title='ID'>ID</th>
                  <th title='Status'>Status</th>
                  <th title='Date'>Date</th>
                  <th title='Unit'>Unit</th>
                  <th title='Type'>Type</th>
                  <th title='Priority'>Priority</th>
                </tr></thead>
                <tfoot><tr>
                  <th colspan='5' onClick="document.location.href='tickets.php';" style='cursor:pointer;'><?php $Icons->Ticket( 1 );?> Go to All Tickets</th>
                  <th colspan='2' onClick='document.location.href="ticket.php";'><i class='fa fa-plus fa-fw fa-1x'></i> New</th>
                </tr></tfoot>
              </table>
            </div>
            <div class="panel-body no-print filters" panel='Locations' style='border-bottom:1px solid #1d1d1d;'>
              <div class='row'><div class='col-xs-12'>&nbsp;</div></div>
              <div class='row'>
                  <div class='col-xs-4'>Search:</div>
                  <div class='col-xs-8'><input type='text' name='Search' placeholder='Search' onChange='redrawLocations( );' /></div>
              </div>
            </div>
            <div class='panel-body' panel='Locations'>
              <table id='Table_Locations' class='display' cellspacing='0' width='100%' style='<?php if(isMobile()){?>font-size:9px;<?php }?>;'>
                <thead><tr>
                  <th title='ID'>ID</th>
                  <th title='Name'>Name</th>
                  <th title='Customer'>Customer</th>
                  <th title='City'>City</th>
                  <th title='Street'>Street</th>
                  <th title='Maintained'>Maintained</th>
                  <th title='Status'>Status</th>
                </tr></thead>
              </table>
            </div>
          </div>
        </div>
        <script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
        <?php $_GET[ 'Datatables_Simple' ] = 1; ?>
        <?php require('cgi-bin/js/datatables.php');?>
        <style></style>
        <script src='https://cdn.datatables.net/rowgroup/1.1.2/js/dataTables.rowGroup.min.js'></script>
        <script src='https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js'></script>
        <script>
          var grouping_id = 5;
          var grouping_name = 'Level';
          var collapsedGroups = [];
          var groupParent = [];
          var Table_Tickets = $('#Table_Tickets').DataTable( {
            dom: 'tp',
            ajax: {
              url: 'cgi-bin/php/get/Work2.php',
              dataSrc:function(json){
                if( !json.data ){ json.data = []; }
                return json.data;}
            },
            columns: [
              {
                className: 'indent',
                data : 'Tag',
                render : function(data, type, row, meta){
                  if(type === 'display'){return '<?php $Icons->Ticket(1);?>';}
                  return data;
                },
                sortable : true,
                visible : false
              },{
                data : 'ID'
              },{
                data : 'Status'
              },{
                data : 'Date',
                render: function(data) {
                  if(data === null){return data;}
                  else {return data.substr(5,2) + '/' + data.substr(8,2) + '/' + data.substr(0,4);}}
              },{
                data : 'Unit_State',
                render:function(data, type, row, meta){
                  if(type === 'display'){
                    if(row.Unit_State === null){return '';}
                    return row.Unit_State + ', </br>' + row.Unit_Label;
                  }
                  return row.Unit_State;
                }
              },{
                data : 'Level',
                render: function(data, type, row, meta){
                  return data;
                }
              },{
                data : 'Priority',
                render: function(data, type, row, meta){
                  return data == 1 ? 'Yes' : 'No';
                },
                visible : false
              }
            ],
            order: [ [ 5, 'asc' ], [0, 'asc' ] ],
            initComplete : function(){ },
            paging : false,
            createdRow : function( row, data, dataIndex ) {
              if ( data['Status'] == 'On Site' || data['Status'] == 'En Route') { $(row).addClass('gold'); } 
              else if( data['Priority'] == 1 && data['Status'] != 'Reviewing' && data['Status'] != 'Completed'){ $(row).addClass('red'); } 
              else if ( data['Level'] == 'Service Call' && data['Status'] != 'Reviewing' && data['Status'] != 'Completed' && data['Status'] != 'Signed' ){ $(row).addClass('blue'); } 
              else if( data['Status'] == 'Signed' ){ $(row).addClass('green'); } 
              else if (data['Status'] != 'Reviewing' && data['Status'] != 'Completed' ){ $(row).addClass('light'); }
            },
            rowGroup: { 
              // Uses the 'row group' plugin
              dataSrc: [
                'Level',
                'Location'
              ],
              startRender: function(rows, group, level) {
                groupParent[level] = group;

                var groupAll = '';
                for (var i = 0; i < level; i++) {groupAll += groupParent[i]; if (collapsedGroups[groupAll]) {return;}}
                groupAll += group;

                if ((typeof(collapsedGroups[groupAll]) == 'undefined') || (collapsedGroups[groupAll] === null)) {collapsedGroups[groupAll] = true;} //True = Start collapsed. False = Start expanded.

                var collapsed = collapsedGroups[groupAll];
                var newTickets = 0;
                rows.nodes().each(function(r) {
                  if(( $(r).children(':nth-child(2)').html() != 'On Site' && $(r).children(':nth-child(2)').html() != 'En Route'  && $(r).children(':nth-child(6)').html() != 'Yes') || $(r).children(':nth-child(2)').html() == 'Reviewing' || $(r).children(':nth-child(2)').html() == 'Signed' || $(r).children(':nth-child(2)').html() == 'Completed'){
                    r.style.display = (collapsed ? 'none' : '');
                  }
                  var start = new Date();
                  start.setHours(0,0,0,0);
                  var end = new Date();
                  end.setHours(23,59,59,999);
                  if( new Date($(r).children(':nth-child(3)').html()) >= start && new Date($(r).children(':nth-child(3)').html()) < end && $(r).children(':nth-child(2)').html() != 'Reviewing' && $(r).children(':nth-child(2)').html() != 'Signed' && $(r).children(':nth-child(2)').html() != 'Completed'){ newTickets++; }
                });
                var newString = newTickets > 0 ? ', ' + newTickets + ' new' : '';
                return $('<tr/>').append('<td colspan="5">' + group  + ' ( ' + rows.count() + ' total' + newString + ' ) </td>').attr('data-name', groupAll).toggleClass('collapsed', collapsed);
              }
            },
            drawCallback : function ( settings ) { 
              hrefTickets( ); 
            }
          } );
          $('tbody').on('click', 'tr.dtrg-start', function () {
              var name = $(this).data('name');
              collapsedGroups[name] = !collapsedGroups[name];
              Table_Tickets.draw( );
          });
          function hrefTickets( ){ hrefRow( 'Table_Tickets', 'ticket'); }
          function redrawTickets( ) { Table_Tickets.order( [ [ grouping_id, 'asc' ] ] ).draw( ); }
        </script>
        <script>
        var isChromium = window.chrome,
          winNav = window.navigator,
          vendorName = winNav.vendor,
          isOpera = winNav.userAgent.indexOf("OPR") > -1,
          isIEedge = winNav.userAgent.indexOf("Edge") > -1,
          isIOSChrome = winNav.userAgent.match("CriOS");
        var Table_Locations = $('#Table_Locations').DataTable( {
          dom      : 'tlp',
          processing : true,
          serverSide : true,
          responsive : true,
          ajax      : {
                  url : 'cgi-bin/php/get/Locations2.php',
                  data : function( d ){
                      d = {
                          start : d.start,
                          length : d.length,
                          order : {
                              column : d.order[0].column,
                              dir : d.order[0].dir
                          }
                      };
                      d.Search = $('input[name="Search"]').val( );
                      d.ID = $('input[name="ID"]').val( );
                      d.Name = $('input[name="Name"]').val( );
                      d.Customer = $('input[name="Customer"]').val( );
                      d.City = $('input[name="City"]').val( );
                      d.Street = $('input[name="Street"]').val( );
                      d.Maintained = $('select[name="Maintained"]').val( );
                      d.Status = $('select[name="Status"]').val( );
                      return d; 
                  }
              },
          columns   : [
            {
              data    : 'ID',
              className : 'hidden'
            },{ 
              data : 'Name'
            },{
              data : 'Customer'
            },{
              data : 'City'
            },{
              data : 'Street'
            },{
              className : 'hidden',
              data   : 'Maintained',
              render : function ( data ){
                return data == 1
                  ? 'Yes'
                  : 'No';
              }
              
            },{
              className : 'hidden',
              data   : 'Status', 
              render : function ( data ){
                return data == 0 
                  ? 'Yes'
                  : 'No';
              }
            }
          ],
          autoWidth : false,
          paging    : true,
          searching : false
        } );
        function redrawLocations( ){ Table_Locations.draw( ); }
        function hrefLocations(){hrefRow("Table_Locations","location");}
        $("Table#Table_Locations").on("draw.dt",function(){hrefLocations();});
        </script>
      </div>
    </section>
    <?php } ?>
    <secton id='main-menu' style=''>
      <script>
        function togglePanel( link ){ link.parentNode.parentNode.classList.toggle('open'); }
      </script>
      <style>
        .Home-Screen-Option {
          background-color:#1f1f1f;
          font-size:16px;
          /*border-radius:100px;*/
          position:relative;
          /*left:4%;*/
          padding:10px
        }
        @media screen and ( min-width:1400px ){
          .col-xl-1 {
            width:7.333333%;
            margin-left:.5%;
            margin-right:.5%;
          }
        }
      </style>
      <?php if(isset($Ticket) && is_array($Ticket)){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='ticket.php?ID=<?php echo $Ticket['ID'];?>';">
        <div class='nav-icon'><?php $Icons->Ticket(3);?></div>
        <div class ='nav-text'>Active Ticket</div>
      </div><?php }?>
      <?php if(isset($Privileges['Time']) && $Privileges['Time']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-6' onclick="document.location.href='scheduler.php'">
        <div class='nav-icon'><i class="fa fa-question-circle fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Attendance</div>
      </div><?php }?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='collections.php'">
        <div class='nav-icon'><?php $Icons->Collection(3);?></div>
        <div class ='nav-text'>Collections</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Contract']) && $Privileges['Contract']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='contracts.php'">
        <div class='nav-icon'><?php $Icons->Contract(3);?></div>
        <div class ='nav-text'>Contracts</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Customer']) && $Privileges['Customer']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='customers.php'">
        <div class='nav-icon'><?php $Icons->Customer(3);?></div>
        <div class ='nav-text'>Customers</div>
      </div><?php } ?>
      <?php /*if(isset($Privileges['Dispatch']) && $Privileges['Ticket']['Other_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='dispatch.php'">
        <div class='nav-icon'><?php $Icons->Dispatch(3);?></div>
        <div class ='nav-text'>Dispatch</div>
      </div><?php } */?>
      <?php /*if(isset($Privileges['Ticket']) && $Privileges['Ticket']['Other_Privilege'] >=7){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='gps_locations.php'">
        <div class='nav-icon'><i class="fa fa-tencent-weibo fa-3x" aria-hidden="true"></i></div>
        <div class ='nav-text'>Geofence</div>
      </div><?php }*/ ?>
      <?php if(isset($Privileges['Ticket']) && $Privileges['Ticket']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='tickets.php'">
        <div class='nav-icon'><?php $Icons->History(3);?></div>
        <div class ='nav-text'>History</div>
      </div><?php } ?>
      <?php if(isset($User['Title']) && strpos($User['Title'], 'SUPER') === false && ($User['Title'] != 'OFFICE' || in_array($User['ID'],array(895,250)))){?><div class='Home-Screen-Option col-xl-2 col-lg-2 col-md-2 col-xs-6' onclick="window.open('https://docs.google.com/forms/d/1kqijgH7gnxEVwYaobgCn8nbjNFG-vXXpecXMHkqy0GA/viewform?edit_requested=true');">
        <div class='nav-icon'><?php $Icons->Safety_Report(3);?></div>
        <div class ='nav-text'>Incident Report</div>
      </div><?php } ?>
      <?php if(isset($User['Title']) && strpos($User['Title'], 'SUPER') !== false && ($User['Title'] != 'OFFICE' || in_array($User['ID'],array(895,250)))){?><div class='Home-Screen-Option col-xl-2 col-lg-2 col-md-2 col-xs-6' onclick="window.open('https://docs.google.com/a/nouveauelevator.com/forms/d/1yeaJSLEJMkt8HYnx_fzGHJtBjU_iOlXCNtQT6r5pXTE/edit?usp=drive_web');">
        <div class='nav-icon'><?php $Icons->Safety_Report(3);?></div>
        <div class ='nav-text'>Incident Report</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='invoices.php'">
        <div class='nav-icon'><?php $Icons->Invoice(3);?></div>
        <div class ='nav-text'>Invoices</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Job']) && $Privileges['Job']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='jobs.php'">
        <div class='nav-icon'><?php $Icons->Job(3);?></div>
        <div class ='nav-text'>Jobs</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Lead']) && $Privileges['Lead']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='leads.php'">
          <div class='nav-icon'><?php $Icons->Customer(3);?></div>
          <div class ='nav-text'>Leads</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Location']) && $Privileges['Location']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='locations.php'">
          <div class='nav-icon'><?php $Icons->Location(3);?></div>
          <div class ='nav-text'>Locations</div>
      </div><?php } ?>
      <div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='../login.php?Logout=TRUE'">
        <div class='nav-icon'><?php $Icons->Logout(3);?></div>
        <div class ='nav-text'>Logout</div>
      </div>
      <?php if(isset($Privileges['Map']) && $Privileges['Map']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='map.php'">
        <div class='nav-icon'><?php $Icons->Map(3);?></div>
        <div class ='nav-text'>Map</div>
      </div><?php }?>
      <?php if(isset( $Privileges['Admin'] ) && $Privileges['Admin']['Other_Privilege'] >= 7 ){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='payroll.php'">
        <div class='nav-icon'><?php $Icons->Invoice(3);?></div>
        <div class ='nav-text'>Payroll</div> 
      </div><?php }?>
      <?php if(isset($Privileges['Privilege']) && $Privileges['Privilege']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='privileges.php'">
        <div class='nav-icon'><?php $Icons->Privilege(3);?></div>
        <div class ='nav-text'>Privileges</div> 
      </div><?php } ?>
      <?php if((isset($Privileges['Code']) && $Privileges['Code']['Other_Privilege'] >= 4) && $_SESSION['User'] != 975){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='batch_process_deficiencies2.php'">
        <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Processing</div>
      </div><?php }?>
      <div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='user.php'">
          <div class='nav-icon'><?php $Icons->User(3);?></div>
          <div class ='nav-text'>Profile</div>
      </div>
      <?php if(isset($Privileges['Sales_Admin']) && $Privileges['Sales_Admin']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-6' onclick="document.location.href='accounts_v2019.php'">
        <div class='nav-icon'><i class="fa fa-dollar fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Profitability</div>
      </div><?php }?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['Other_Privilege'] >= 4 ){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='proposals.php'">
        <div class='nav-icon'><?php $Icons->Proposal(3);?></div>
        <div class ='nav-text'>Proposals</div>
      </div><?php } ?>
      <?php /*<div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='pto.php'">
        <div class='nav-icon'><?php $Icons->Calendar(3);?></div>
        <div class ='nav-text'>PTO</div>
      </div>*/?>
      <?php if(isset( $Privileges['Admin'] ) && $Privileges['Admin']['Other_Privilege'] >= 7 ){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='elevt_report.php'">
        <div class='nav-icon'><?php $Icons->Customer(3);?></div>
        <div class ='nav-text'>Questions</div>
      </div><?php }?>
      <?php if(isset($Privileges['Requisition']) && $Privileges['Requisition']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-6' onclick="document.location.href='requisitions.php'">
        <div class='nav-icon'><?php $Icons->Requisition(3);?></div>
        <div class ='nav-text'>Requisitions</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Time']) && $Privileges['Time']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-6' onclick="document.location.href='review.php'">
        <div class='nav-icon'><?php $Icons->Customer( 3 );?></div>
        <div class ='nav-text'>Review</div>
      </div><?php }?>
      <?php if(isset($Privileges['Invoice']) && $Privileges['Invoice']['User_Privilege'] >= 7){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='invoice-registrar-1.php'">
        <div class='nav-icon'><?php $Icons->Invoice(3);?></div>
        <div class ='nav-text'>Registrar</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Route']) && $Privileges['Route']['Other_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='routes.php'">
        <div class='nav-icon'><?php $Icons->Route(3);?></div>
        <div class ='nav-text'>Routes</div>
      </div><?php } ?>
      <?php
      $result = sqlsrv_query(
        $NEI,
        " SELECT Route.ID
          FROM   Route
                 LEFT JOIN Emp ON Route.Mech = Emp.fWork
          WHERE  Emp.ID = ?;",
        array( $_SESSION['User'] )
      );
      $RouteNav = sqlsrv_fetch_array($result);
      if(isset($Privileges['Route']) && $Privileges['Route']['User_Privilege'] >= 4 && is_array($RouteNav) && isset($RouteNav['ID']) && $RouteNav['ID'] > 0 ){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='route.php?ID=<?php echo $RouteNav['ID'];?>'">
        <div class='nav-icon'><?php $Icons->Route(3);?></div>
        <div class ='nav-text'>Route</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Safety_Report']) && $Privileges['Safety_Report']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='reports.php'">
        <div class='nav-icon'><?php $Icons->Report(3);?></div>
        <div class ='nav-text'>Reports</div>
      </div><?php } ?>
      <?php if(False){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='settings.php'">
        <div class='nav-icon'><?php $Icons->Unit(3);?></div>
        <div class ='nav-text'>Settings</div>
      </div><?php }?>
      <?php if(isset($Privileges['Admin']) && $Privileges['Admin']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='supervising.php'">
        <div class='nav-icon'><?php $Icons->Customer(3);?></div>
        <div class ='nav-text'>Supervising</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Ticket']) && $Privileges['Ticket']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='support.php'">
        <div class='nav-icon'><i class="fa fa-question-circle fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Support</div>
      </div><?php }?>
      <?php if(isset($Privileges['Territory']) && $Privileges['Territory']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='territories.php'">
          <div class='nav-icon'><?php $Icons->Territory(3);?></div>
          <div class ='nav-text'>Territories</div>
      </div><?php }?>
      <?php if((isset($Privileges['Code']) && $Privileges['Code']['Other_Privilege'] >= 4)){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='category_tests.php'">
        <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Tests</div>
      </div><?php }?>
      <?php if((isset($Privileges['Testing_Admin']) && $Privileges['Testing_Admin']['Other_Privilege'] >= 4)){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='draft_category_tests.php'">
        <div class='nav-icon'><i class="fa fa-clipboard fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Testing</div>
      </div><?php }?>
      <?php if(isset($Privileges['Time']) && $Privileges['Time']['User_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='timesheet.php'">
          <div class='nav-icon'><?php $Icons->Timesheet(3);?></div>
          <div class ='nav-text'>Timesheet</div>
      </div><?php }?>
      <?php if(isset($Privileges['Unit']) && $Privileges['Unit']['User_Privilege'] >= 4 || $Privileges['Unit']['Group_Privilege'] >= 4 || $Privileges['Unit']['Other_Privilege'] >= 4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='units.php'">
        <div class='nav-icon'><?php $Icons->Unit(3);?></div>
        <div class ='nav-text'>Units</div>
      </div><?php } ?>
      <?php if(isset($Privileges['User']) && $Privileges['User']['Other_Privilege'] >= 7){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='users.php'">
        <div class='nav-icon'><?php $Icons->Users(3);?></div>
        <div class ='nav-text'>Users</div>
      </div><?php } ?>
      <?php if(isset($Privileges['Violation']) && $Privileges['Violation']['User_Privilege'] >=4){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='violations.php'">
        <div class='nav-icon'><?php $Icons->Violation(3);?></div>
        <div class ='nav-text'>Violations</div>
      </div><?php } ?>
      <div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='https://www.nouveauelevator.com/';">
        <div class='nav-icon'><?php $Icons->Web(3);?></div>
        <div class ='nav-text'>Website</div>
      </div>
      <div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='work.php'">
        <div class='nav-icon'><?php $Icons->Ticket(3);?></div>
        <div class ='nav-text'>Work</div>
      </div>
      <?php if(isset($Privileges['Admin']) && $Privileges['Admin']['Other_Privilege'] >= 7){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='interface.php'">
        <div class='nav-icon'><i class="fa fa-user-secret fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Beta</div>
      </div><?php }?>
      <?php if(isset($Privileges['Admin']) && $Privileges['Admin']['Other_Privilege'] >= 7){?><div class='Home-Screen-Option col-xl-1 col-lg-2 col-md-2 col-xs-3' onclick="document.location.href='../portal2/'">
        <div class='nav-icon'><i class="fa fa-user-secret fa-3x fa-fw" aria-hidden="true"></i></div>
        <div class ='nav-text'>Legacy</div>
      </div><?php }?>
    </section>
  </div>
</div>
<script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
</body>
</html>