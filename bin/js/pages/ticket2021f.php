<script>
<?php if($Ticket['TimeSite'] >= '1899-12-30 00:00:00.000'){?>
  var maxRegularHours = 8;
  var sliderRegular = document.getElementById('slider-regular');
  var sliderOvertime = document.getElementById('slider-overtime');
  var sliderDoubletime = document.getElementById('slider-doubletime');
  var sliderNightDiff = document.getElementById('slider-nightdiff');
  var timeRegular = document.getElementById('time-regular');
  var timeOvertime = document.getElementById('time-overtime');
  var timeDoubletime = document.getElementById('time-doubletime');
  var timeNightDiff = document.getElementById('time-nightdiff');
  var tempReg = 0;
  var tempOvertime = 0;
  var tempCompleted = 0;
  function toggleRegularHours(){
    maxRegularHours = maxRegularHours == 8 ? 10 : 8;
    sliderRegular.noUiSlider.updateOptions({
      range:{
        'min' : 0,
        'max' : maxRegularHours
      }
    }, true);
    if(completed != null) { changeSliders(); }
  }
  function split( val ) {
      return val.split( /,\s*/ );
    }
    function extractLast( term ) {
      return split( term ).pop();
    }
$(document).ready(function(){
  noUiSlider.create(sliderRegular, {
    start: 0,
    step:.25,
    range:{
      'min':0,
      'max':maxRegularHours
    }
  });
  sliderRegular.noUiSlider.on('update',function(values, handle){
    timeRegular.value = values[handle];
    if($("input.lunch:checked").length == 0){
      tempReg = timeRegular.value;
    }
    timeTotal.value = parseFloat(timeNightDiff.value) + parseFloat(timeOvertime.value) + parseFloat(timeDoubletime.value) + parseFloat(values[handle]);
  });
  noUiSlider.create(sliderOvertime, {
    start: 0,
    step:.25,
    range:{
      'min':0,
      'max':12
    }
  });
  sliderOvertime.noUiSlider.on('update',function(values, handle){
    timeOvertime.value = values[handle];
    if($("input.lunch:checked").length == 0){
      tempOvertime = timeOvertime.value;
    }
    timeTotal.value = parseFloat(timeRegular.value) + parseFloat(timeNightDiff.value) + parseFloat(timeDoubletime.value) + parseFloat(values[handle]);
  });
  noUiSlider.create(sliderDoubletime, {
    start: 0,
    step:.25,
    range:{
      'min':0,
      'max':12
    }
  });
  sliderDoubletime.noUiSlider.on('update',function(values, handle){
    timeDoubletime.value = values[handle];
    timeTotal.value = parseFloat(timeRegular.value) + parseFloat(timeOvertime.value) + parseFloat(timeNightDiff.value) + parseFloat(values[handle]);
  });
  noUiSlider.create(sliderNightDiff, {
    start: 0,
    step:.25,
    range:{
      'min':0,
      'max':8
    }
  });
  sliderNightDiff.noUiSlider.on('update',function(values, handle){
    timeNightDiff.value = values[handle];
    timeTotal.value = parseFloat(timeRegular.value) + parseFloat(timeOvertime.value) + parseFloat(timeDoubletime.value) + parseFloat(values[handle]);
  });
  <?php if(isset($_GET['Edit'])){?>
    sliderRegular.noUiSlider.set(<?php echo $Ticket2['Reg'];?>);
    sliderOvertime.noUiSlider.set(<?php echo $Ticket2['OT'];?>);
    sliderDoubletime.noUiSlider.set(<?php echo $Ticket2['DT'];?>);
    sliderNightDiff.noUiSlider.set(<?php echo $Ticket2['TT'];?>);
  <?php }?>
});<?php }?>
/*function time_on_site(){
  if(!check_Distance(<?php echo $Ticket['Lattitude'];?>, <?php echo $Ticket['Longitude'];?>)){
    $("button#on_site").html("At Work");
    alert("Warning. You are not in the geofence.");
  } else {
  }
  $("button#en_route").attr('disabled','disabled');
    $("button#on_site").attr('disabled','disabled');
    $.ajax({
      url:"bin/php/post/ticket_time_on_site.php",
      data: {
        ID : '<?php echo $_GET['ID'];?>
      },
      cache:false,
      method:"POST",
      timeout:20000,
      error:function(XMLHttpRequest, textStatus, errorThrown){
        alert('Your ticket did not update. Please check your internet.')
        $("button#en_route").prop('disabled',false);
        $("button#on_site").prop('disabled',false);
        $("button#on_site").html("At Work");
      },
      success:function(code){
        document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
      }
    });
}*/
function positionDistance(position){
  if(verify_Distance(<?php echo $Ticket['Lattitude'];?>, <?php echo $Ticket['Longitude'];?>, position.coords.latitude, position.coords.longitude, <?php echo is_numeric($Ticket['Distance']) ? $Ticket['Distance'] : 1;?>)){
    $("button#en_route").attr('disabled','disabled');
    $("button#on_site").attr('disabled','disabled');
    $.ajax({
      url:"bin/php/post/ticket_time_on_site.php",
      data: {
        ID : '<?php echo $_GET['ID'];?>'
      },
      cache:false,
      method:"POST",
      timeout:20000,
      error:function(XMLHttpRequest, textStatus, errorThrown){
        alert('Your ticket did not update. Please check your internet or your GPS Services.')
        $("button#en_route").prop('disabled',false);
        $("button#on_site").prop('disabled',false);
        $("button#on_site").html("At Work");
      },
      success:function(code){
        if(code == 'Error: No Current Active GPS Timestamp.'){
          $("button#en_route").prop('disabled',false);
          $("button#on_site").prop('disabled',false);
          $("button#on_site").html("At Work");
          alert(code);
        } else {
          document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
        }
      }
    });
  } else {
    $("button#on_site").html("At Work");
    alert("Warning: Location Geofence On. You are out of the Geofence. You were not clocked in.");
  }
}
var FaceMask = false;
function time_on_site(/*position*/){
  if(<?php echo in_array($_SESSION['User'], array(683, 1159, 1412, 1534, 1245)) ? 'true' : 'false';?> && <?php echo $Ticket['Geofence'] == 1 ? 'true' : 'false';?>){
    navigator.geolocation.getCurrentPosition(positionDistance, function(){
      $("button#on_site").html("At Work");
      alert("Warning: GPS is Required. Your GPS is disabled. Please enable your Location Services. You were not clocked in");
    });
  } else {
    <?php if($Ticket['Location_ID'] == 9615){?>
      $( "#dialog-confirm" ).dialog({
        resizable: false,
        height: "auto",
        width: 400,
        modal: true,
        buttons: {
          "Confirm": function() {
            $( this ).dialog( "close" );
            $("button#en_route").attr('disabled','disabled');
            $("button#on_site").attr('disabled','disabled');
            $.ajax({
              url:"bin/php/post/ticket_time_on_site.php",
              data: {
                ID : '<?php echo $_GET['ID'];?>'
              },
              cache:false,
              method:"POST",
              timeout:20000,
              error:function(XMLHttpRequest, textStatus, errorThrown){
                alert('Your ticket did not update. Please check your internet or your GPS Services.')
                $("button#en_route").prop('disabled',false);
                $("button#on_site").prop('disabled',false);
                $("button#on_site").html("At Work");
              },
              success:function(code){
                if(code == 'Error: No Current Active GPS Timestamp.'){
                  $("button#en_route").prop('disabled',false);
                  $("button#on_site").prop('disabled',false);
                  $("button#on_site").html("At Work");
                  alert(code);
                } else {
                  document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
                }
              }
            });
          },
          "Cancel": function() {
            $( this ).dialog( "close" );
            $("button#en_route").prop('disabled',false);
            $("button#on_site").prop('disabled',false);
            $("button#on_site").html("At Work");
          }
        }
      });
    <?php } else {?>
      $("button#en_route").attr('disabled','disabled');
      $("button#on_site").attr('disabled','disabled');
      $.ajax({
        url:"bin/php/post/ticket_time_on_site.php",
        data: {
          ID : '<?php echo $_GET['ID'];?>'
        },
        cache:false,
        method:"POST",
        timeout:20000,
        error:function(XMLHttpRequest, textStatus, errorThrown){
          alert('Your ticket did not update. Please check your internet or your GPS Services.')
          $("button#en_route").prop('disabled',false);
          $("button#on_site").prop('disabled',false);
          $("button#on_site").html("At Work");
        },
        success:function(code){
          if(code == 'Error: No Current Active GPS Timestamp.'){
            $("button#en_route").prop('disabled',false);
            $("button#on_site").prop('disabled',false);
            $("button#on_site").html("At Work");
            alert(code);
          } else {
            document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
          }
        }
      });
    <?php }?>
  }
}
function post_time_on_site(link){
  $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
  if (navigator.geolocation) {
      //navigator.geolocation.getCurrentPosition(time_on_site, showError, {enableHighAccuracy:true, timeout:20000});
      time_on_site();
  } else {
      alert("Please enable your GPS. If you did not get the prompt or declined it earlier, please clear your history to get the prompt again.")
  }
}
function post_time_en_route(link){
  $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
  if (navigator.geolocation) {
      //navigator.geolocation.getCurrentPosition(time_en_route, showError, {enableHighAccuracy:true, timeout:20000});
      time_en_route();
  } else {
      alert("Please enable your GPS. If you did not get the prompt or declined it earlier, please clear your history to get the prompt again.")
      $(link).html("At Work");
  }
}
function showError(error) {
  switch(error.code) {
    case error.PERMISSION_DENIED:
      alert("You denied the request for Geolocation. You will not be able to start, edit or complete tickets until you enable it. Please reset your history to enable location services.");
      break;
    case error.POSITION_UNAVAILABLE:
      alert("Location information is unavailable. You will not be able to start, edit or complete tickets until your location information is enabled.");
      break;
    case error.TIMEOUT:
      alert("The location permission has timed out. Please click again and click enable.");
      break;
    case error.UNKNOWN_ERROR:
      alert("An unknown error occurred. Please contact the ITHelpDesk@NouveauElevator.com");
      break;
    default:
      alert('Default Error: Unknown')
  }
  $("button#en_route").prop('disabled',false);
  $("button#on_site").prop('disabled',false);
  $("button#on_site").html("At Work");
  $("button[onclick='post_time_allocation(this);']").prop('disabled',false);
  $("button[onclick='post_time_allocation(this);']").html("Save");
}
function time_en_route(/*position*/){
  $("button#en_route").attr('disabled','disabled');
  $("button#on_site").attr('disabled','disabled');
  $.ajax({
    url:"bin/php/post/ticket_time_en_route.php",
    cache:false,
    data: {
      ID        : '<?php echo $_GET['ID'];?>'/*,
      Latitude  : position.coords.latitude,
      Longitude : position.coords.longitude*/
    },
    method:"POST",
    timeout:15000,
    error:function(XMLHttpRequest, textStatus, errorThrown){
      alert('Your ticket did not update. Please check your internet.')
      $("button#en_route").prop('disabled',false);
      $("button#en_route").html("Accept Work");
      $("button#on_site").prop('disabled',false);
    },
    success:function(code){
      //alert(code);
      document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
    }
  });
}
Number.prototype.round = function(p) {
  p = p || 10;
  return parseFloat( this.toFixed(p) );
};
function reset_time(link){
  if (confirm('Are you sure you want to reset the ticket? All times will be reset to null.')) {
    $.ajax({
      url:"bin/php/post/ticket_time_reset.php",
      data:{ID : <?php echo $_GET['ID'];?>},
      method:"POST",
      success:function(code){
        document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
      }
    });
  } else {
  }
}
var completed = null;
function post_time_completed(link){
  $.ajax({
    url:"bin/php/post/ticket_time_completed.php",
    data: {ID : <?php echo $Ticket['ID'];?>},
    method:"POST",
    success:function(comp){
      completed = comp;
      var d = new Date();
      var hours = completed.substr(11,2);
      var minutes = completed.substr(14,2);
      var flip = '';
      if(parseInt(hours) > 12){
        flip = 'PM';
        hours = parseInt(hours) - 12;
        hours = "0" + hours;
      } else if(parseInt(hours) == 12) {
        flip = 'PM';
      } else {
        flip = 'AM';
      }
      $(link).replaceWith(hours + ":" + minutes + " " + flip);
      changeSliders();
    }
  });
}
function changeSliders(){
  var total = 0;
  total = calculate_Total();
  if(total <= maxRegularHours){
    sliderRegular.noUiSlider.set(total);
    sliderOvertime.noUiSlider.set(0);
  } else if(total <= maxRegularHours + 8 ) {
    var difference = total - maxRegularHours;
    sliderRegular.noUiSlider.set(maxRegularHours);
    sliderOvertime.noUiSlider.set(difference);
  } else {
    var difference = total - (maxRegularHours + 8);
    sliderRegular.noUiSlider.set(maxRegularHours);
    sliderOvertime.noUiSlider.set(8);
    sliderDoubletime.noUiSlider.set(difference);
  }
  tempReg = sliderRegular.noUiSlider.get();
  tempOvertime = sliderOvertime.noUiSlider.get();
  tempCompleted = 1;
}
function time_lunch(link, type){
  if(tempCompleted == 0){
    $("input.lunch").prop('checked',false);
    $("input.lunch").prop('checked',false);
    alert("You must click completed work first.");
  } else {
    if($(link).prop('checked')){
      $("input.lunch").prop('checked',false);
      $("input.lunch").prop('checked',false);
      $(link).prop('checked',true);
    }
    sliderRegular.noUiSlider.set(tempReg);
    sliderOvertime.noUiSlider.set(tempOvertime);
    if($(link).prop('checked')){
      if(sliderOvertime.noUiSlider.get() >= type){
        sliderOvertime.noUiSlider.set(sliderOvertime.noUiSlider.get() - type);
      } else if(sliderOvertime.noUiSlider.get() > 0){
        sliderOvertime.noUiSlider.set(0);
        sliderRegular.noUiSlider.set(sliderRegular.noUiSlider.get() - (type - sliderOvertime.noUiSlider.get()));
      } else {
        sliderRegular.noUiSlider.set(sliderRegular.noUiSlider.get() - type);
      }
    }
  }
}
function isCanvasBlank(canvas) {
  var blank = document.createElement('canvas');
  blank.width = canvas[0].width;
  blank.height = canvas[0].height;
  var asdf = blank.getContext("2d");
  asdf.clearRect(0, 0, blank.width, blank.height);
  asdf.beginPath();
  asdf.fillStyle = "white";
  asdf.fillRect(0, 0, blank.width, blank.height);
  //alert(blank.toDataURL());
  return canvas[0].toDataURL() == blank.toDataURL();
}
var tempLink = null;
function post_time_allocation(link){
  $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
  $(link).attr('disabled','disabled');
  tempLink = link;
  var message = "";
  if('<?php
    echo $Annual_Test_Check;
  ?>' == 'false'){message = message + "You need to complete the annual test. ";}
  if($("div#completed button").length != 0){message = message + "You need to click the completed button. ";}
  if($("textarea[name='Resolution']").val() == ''){message = message + "Resolution code needs to be filled out. "; }  
  if($("textarea[name='Resolution2']").val() == ''){message = message + "Resolution description needs to be filled out. "; }
  if(isCanvasBlank($("#signature")) || $("input[name='Signature_Name']").val() == ''){message = message + "A Signature and signee needs to be filled out. ";}
  if(     $("input[name='time-regular']").val() % .25 != 0
      ||  $("input[name='time-overtime']").val() % .25 != 0
      ||  $("input[name='time-doubletime']").val() % .25 != 0
      ||  $("input[name='time-nightdiff']").val() % .25 != 0){message = message + "You must round your time to the quarter. "}
  if(message != ''){alert(message);}
  if(message == ""){
    var total = parseFloat($("#time-regular").val()) + parseFloat($("#time-overtime").val()) + parseFloat($("#time-doubletime").val()) + parseFloat($("#time-nightdiff").val());
    var totalt = parseFloat($("span#total-hours").html());
    var pass = true;
    if(parseFloat(total) != parseFloat(totalt)){
      pass = confirm("You hours do not match the calculated total time. Do you want to continue?");
    }
    if(pass){
      if (navigator.geolocation) {
        //navigator.geolocation.getCurrentPosition(saveTicket, showError, {enableHighAccuracy:true, timeout:20000});
        saveTicket();
        return;
      } else {
        alert("Please enable your GPS. If you did not get the prompt or declined it earlier, please clear your history to get the prompt again.")
      }
    }
  }
  $(link).html("Save");
  $(link).prop('disabled',false);
}
var ApplySignature = false;
function applySignature(link){
  ApplySignature = !ApplySignature;
  if(ApplySignature){
    $(link).css('background-color','gold');
    $(link).html("Ready to Apply");
  } else {
    $(link).css('background-color','rgb(211, 211, 211)');
    $(link).html("Apply Signature to Work Order");
  }
}
function saveTicket(/*position*/){

  var ticketData = new FormData($('form#Receipt')[0]);
  ticketData.delete('ID')
  ticketData.append("ID",'<?php echo $_GET['ID'];?>');
  ticketData.append("Regular",$("#time-regular").val());
  ticketData.append("Overtime",$("#time-overtime").val());
  ticketData.append("Doubletime",$("#time-doubletime").val());
  ticketData.append("NightDiff",$("#time-nightdiff").val());
  ticketData.append("CarExpenses",$("input[Name='CarExpenses']").val());
  ticketData.append("OtherExpenses",$("input[Name='OtherExpenses']").val());
  ticketData.append("Chargeable",$("input[name='Chargeable']").prop('checked'));
  ticketData.append("Continue_Work",$("input[name='Continue_Work']").prop('checked'));
  ticketData.append("Resolution","-----Codes-----\n" + $("textarea[name='Resolution']").val() + "\n-----Notes-----\n" + $("textarea[name='Resolution2']").val() ); 
  ticketData.append("Signature_Text",$("input[name='Signature_Name']").val());
  ticketData.append("Signature_Canvas", $("#signature")[0].toDataURL("image/jpeg"));
  ticketData.append("Signature_Work_Order",ApplySignature);
  ticketData.append("Email",$("input[name='Email']").val());
  <?php 
  if(is_array($_GET['Deficiencies']) && count($_GET['Deficiencies']) > 0){?>
  var Deficiencies = [];
  $("input.Deficiency_Checkbox").each(function(){
    if($(this).prop("checked")){
      Deficiencies.push($(this).attr('rel'));
    }
  });
  ticketData.append("Deficiencies", Deficiencies);
  <?php }?>
  var Tasks_Statuses = [];
  var asdfing = [];
  var count = 1;
  $("input.Task_Checkbox").each(function(){
    if($(this).prop("checked")){
      asdfing.push(count);
      Tasks_Statuses.push(1);
    } else {
      asdfing.push(count);
      Tasks_Statuses.push(0);
    }
    count++;
  });
  ticketData.append("Tasks_Statuses", Tasks_Statuses);
  var Tasks_Descriptions = "";
  count = 1;
  $("div.Task_Description").each(function(){
    if(Tasks_Descriptions != ""){
      Tasks_Descriptions = Tasks_Descriptions + ";";
    }
    Tasks_Descriptions = Tasks_Descriptions + $(this).html();
    count++;
  });

  ticketData.append("Tasks_Descriptions", Tasks_Descriptions);

  if( $("input[Name='CarExpenses']").val() > 500 || $("input[Name='OtherExpenses']").val() > 500 ){
    alert("Your expenses have exceeded the digital limit. Please submit your expenses through Payroll.");
    $("button[onclick='post_time_allocation(this);']").html("Save");
    $("button[onclick='post_time_allocation(this);']").prop('disabled',false);
  }
  else {
    $.ajax({
      url:"bin/php/post/save_ticket.php",
      cache: false,
      processData: false,
      contentType: false,
      data: ticketData,
      timeout:25000,
      error:function(XMLHttpRequest, textStatus, errorThrown){
        alert('Your ticket did not save. Please check your internet.');
        $(tempLink).html("Save");
        $(tempLink).prop('disabled',false);
      },
      method:"POST",
      success:function(code){
        <?php
        if($_SESSION['User'] == 895 && FALSE){?>if(confirm("Can you please add information pertaining to the controller?")){
          document.location.href='unit.php?ID=<?php echo $Ticket['Unit_ID'];?>&Ticket_Update=1';
        } else {
          document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
        }<?php } else {?>
          document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
        <?php }?>
      }
    });
  }
}
function save_internal_comments(link){
  $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
  $(link).attr('disabled','disabled');
  $.ajax({
    url:"bin/php/post/ticket_internal_comments.php",
    data: {
      ID:  '<?php echo $_GET['ID'];?>',
      Internal_Comments: $("textarea[name='Internal_Comments']").val()
    },
    timeout:15000,
    error:function(XMLHttpRequest, textStatus, errorThrown){
      alert('Your ticket did not save. Please check your internet.')
      $(link).html("Save");
      $(link).prop('disabled',false);
    },
    method:"POST",
    success:function(code){
      <?php
      if($Ticket['Level'] == 10){
        $r = $database->query(null,
          " SELECT  TicketO.ID
            FROM    nei.dbo.TicketO
            WHERE   TicketO.LElev = ?
                    AND TicketO.fWork = ?
                    AND TicketO.Level = 4
          ;", array($Ticket['Unit_ID'], $Ticket['fWork']));
        if($r){
          $next = sqlsrv_fetch_array($r);
          if(is_array($next) && isset($next['ID']) && is_numeric($next['ID'])){
            ?>document.location.href = 'ticket.php?ID=<?php echo $next['ID'];?>';<?php
          } else {
            ?>document.location.href = 'work.php';<?php
          }
        } else {
          ?>document.location.href = 'work.php';<?php
        }
      } else {
        ?>document.location.href = 'work.php';<?php
      }?>
    }
  })
}
var Metro = true;
function toggleMetro(link){
  if($("input[name='OtherExpenses']").val() == ''){$("input[name='OtherExpenses']").val(0);}
  if(Metro){$("input[name='OtherExpenses']").val(parseFloat($("input[name='OtherExpenses']").val()) + 2.75 );}
  else {$("input[name='OtherExpenses']").val(parseFloat($("input[name='OtherExpenses']").val()) - 2.75 );}
  Metro = !Metro;
}
function toggle_email_person(){$("div.email-person").toggleClass('active');}
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else {
        //alert("Geolocation is not supported by this browser.");
    }
}
function showPosition(position) {
    $.ajax({
      data:{
        latitude  : position.coords.latitude,
        longitude : position.coords.longitude,
        ID        : <?php echo $_GET['ID'];?>
      },
      url:"bin/php/post/ticket-gps.php",
      method:"POST",
      success:function(code){}
    });
}
function toggle_Resolution_Items(link){
  if($(link).val().includes('Item')){
    $(".Resolution-Items").show();
  } else {
    $(".Resolution-Items").hide();
  }
}
function add_Resolution(link){
  if($("select[name='Resolutions']").val().includes('Item')){
    if($("textarea[name='Resolution']").val() == ""){
      $("textarea[name='Resolution']").val($("select[name='Resolutions']").val() + ": " + String($("select[name='Item_Type']").val()).replace(/,/g, ', ') + ".");
    } else {
      $("textarea[name='Resolution']").val($("textarea[name='Resolution']").val() + "\n" + $("select[name='Resolutions']").val() + ": " + String($("select[name='Item_Type']").val()).replace(/,/g, ', ') + ".");
    }
  } else {
    if($("select[name='Resolutions']").val().includes('Maintenance')){
      if($("textarea[name='Resolution']").val() == ""){
        $("textarea[name='Resolution']").val("Performed Maintenance on Unit, checked operation of elevator, door operation, hall calls, car calls, motor room, car top and pit.");
      } else {
        $("textarea[name='Resolution']").val($("textarea[name='Resolution']").val() + "\nPerformed Maintenance on Unit, checked operation of elevator, door operation, hall calls, car calls, motor room, car top and pit.");
      }
    } else {
      if($("textarea[name='Resolution']").val() == ""){
        $("textarea[name='Resolution']").val($("select[name='Resolutions']").val() + ".");
      } else {
        $("textarea[name='Resolution']").val($("textarea[name='Resolution']").val() + "\n" + $("select[name='Resolutions']").val() + ".");
      }
    }
  }

}
function calculate_Total(){
  var on_site = $('#en-route').html();
  var completed = $('#completed').html();
  return calculateTotal(on_site, completed);
}
function calculateTotal(on_site, completed){
  var total = 0;
  var on_site_hours = parseFloat(on_site.substr(0,2));
  var on_site_minutes = parseFloat(on_site.substr(3,2));
  var on_site_ext = on_site.substr(6,2);
  var completed_hours = parseFloat(completed.substr(0,2));
  var completed_minutes = parseFloat(completed.substr(3,2));
  var completed_ext = completed.substr(6,2);

  if(on_site_ext == 'PM' && on_site_hours != 12){on_site_hours += 12;}
  else if(on_site_ext == 'AM' && on_site_hours == 12){on_site_hours = 0;}

  if(completed_ext == 'PM' && completed_hours != 12){completed_hours += 12;}
  else if(completed_ext == 'AM' && completed_hours == 12){completed_hours = 0;}

  if(completed_hours < on_site_hours){
    total = (24 - on_site_hours) +  completed_hours + ((completed_minutes - on_site_minutes) / 60);
  } else if(completed_hours == on_site_hours && completed_minutes < on_site_minutes){
    total = 24 - ((on_site_minutes - completed_minutes) / 60);
  } else {
    total = (completed_hours - on_site_hours) + ((completed_minutes - on_site_minutes) / 60);
  }

  total = Math.ceil(4 * total) / 4;
  $("#permaTotal").html("&nbsp;&nbsp;out of <span id='total-hours'>" + total.round(2) + "</span> hours ");
  return total;
}
$(document).ready(function(){
  $("input[name='time-regular']").on("blur",function(){
    if($(this).val() % .25 == 0){
      sliderRegular.noUiSlider.set($(this).val());
    } else {
      alert('You must round to the quarter')
    }
  });
  $("input[name='time-overtime']").on("blur",function(){
    if($(this).val() % .25 == 0){
      sliderOvertime.noUiSlider.set($(this).val());
    } else {
      alert('You must round to the quarter')
    }
  });
  $("input[name='time-doubletime']").on("blur",function(){
    if($(this).val() % .25 == 0){
      sliderDoubletime.noUiSlider.set($(this).val());
    } else {
      alert('You must round to the quarter')
    }
  });
  $("input[name='time-nightdiff']").on("blur",function(){
    if($(this).val() % .25 == 0){
      sliderNightDiff.noUiSlider.set($(this).val());
    } else {
      alert('You must round to the quarter')
    }
  });
});
var uploadField = document.getElementById("Receipt_Input");
/*
uploadField.onchange = function() {
    if(this.files[0].size > 8388608){
       alert("File is too big!");
       this.value = "";
    };
};
*/
</script>
<div id="dialog-confirm" title="Are you wearing a face mask?" style='display:none;'>
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:12px 12px 20px 0;"></span>Please confirm you are wearing a face mask.</p>
</div>
