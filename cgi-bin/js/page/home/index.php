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