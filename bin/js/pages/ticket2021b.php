<script>

</script>
<script>
function distance(lat1, lon1, lat2, lon2, unit) {
  if ((lat1 == lat2) && (lon1 == lon2)) {
    return 0;
  } else {
    var radlat1 = Math.PI * lat1/180;
    var radlat2 = Math.PI * lat2/180;
    var theta = lon1-lon2;
    var radtheta = Math.PI * theta/180;
    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);
    if (dist > 1) {
      dist = 1;
    }
    dist = Math.acos(dist);
    dist = dist * 180/Math.PI;
    dist = dist * 60 * 1.1515;
    if (unit=="K") { dist = dist * 1.609344 }
    if (unit=="N") { dist = dist * 0.8684 }
    return dist;
  }
}
<?php if($Ticket['TimeSite'] >= '1899-12-30 00:00:00.000'){?>
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

  function split( val ) {
      return val.split( /,\s*/ );
    }
    function extractLast( term ) {
      return split( term ).pop();
    }

$(document).ready(function(){
  if($('#slider-regular').length > 0){
    noUiSlider.create(sliderRegular, {
      start: 0,
      step:.25,
      range:{
        'min':0,
        'max':8
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
        'max':8
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
  }
});<?php }?>
function time_on_site(/*position*/){
  $("button#en_route").attr('disabled','disabled');
  $("button#on_site").attr('disabled','disabled');
  //var loc_lat = <?php echo isset($Ticket['Lattitude']) && strlen($Ticket['Lattitude']) > 0 ? $Ticket['Lattitude'] : 0;?>;
  //var loc_lng = <?php echo isset($Ticket['Longitude']) && strlen($Ticket['Longitude']) > 0  ? $Ticket['Longitude'] : 0;?>;
  //if((loc_lat == 0) || (loc_lng == 0) || distance(position.coords.latitude, position.coords.longitude, loc_lat, loc_lng, 'M') <= .42){
    $.ajax({
      url:"bin/php/post/ticket_time_on_site.php",
      data: {
        ID : '<?php echo $_GET['ID'];?>'/*,
        Latitude  : position.coords.latitude,
        Longitude : position.coords.longitude*/
      },
      cache:false,
      method:"POST",
      timeout:15000,
      error:function(XMLHttpRequest, textStatus, errorThrown){
        alert('Your ticket did not update. Please check your internet.');
        $("button#en_route").prop('disabled',false);
        $("button#on_site").prop('disabled',false);
        $("button#on_site").html("At Work");
      },
      success:function(code){
        document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
      }
    });
  /*} else {
    $("button#en_route").prop('disabled',false);
    $("button#on_site").prop('disabled',false);
    $("button#on_site").html("At Work");
    alert("You are not within the GeoFence. Please get closer to the location.");
  }*/
}
function showError(error) {
  switch(error.code) {
    case error.PERMISSION_DENIED:
      alert("You denied the request for Geolocation. You will not be able to start, edit or complete tickets until you enable it. Please reset your history to enable location services.");
      break;
    case error.POSITION_UNAVAILABLE:
      alert("Position information is unavailable. ");
      break;
    case error.TIMEOUT:
      alert("Geolocation has timed out.");
      break;
    case error.UNKNOWN_ERROR:
      alert("An unknown error occurred. Please contact the ITHelpDesk@NouveauElevator.com");
      break;
    default:
      alert('GEOLOCATION ERROR');
      break;
  }
  $("button#en_route").prop('disabled',false);
  $("button#on_site").prop('disabled',false);
  $("button#on_site").html("At Work");
}
function post_time_on_site(link){
  $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
  if (navigator.geolocation || true) {
      time_on_site();
  } else {
      alert("Please enable your GPS. If you did not get the prompt or declined it earlier, please clear your history to get the prompt again.")
  }
}
function post_time_en_route(link){
  $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
  if (navigator.geolocation || true) {
    time_en_route();
      //navigator.geolocation.getCurrentPosition(time_en_route, showError, {enableHighAccuracy:true, timeout:10000});
  } else {
      alert("Please enable your GPS. If you did not get the prompt or declined it earlier, please clear your history to get the prompt again.")
      $(link).html("At Work");
  }
}


function time_en_route(position){
  $("button#en_route").attr('disabled','disabled');
  $("button#on_site").attr('disabled','disabled');
  $.ajax({
    url:"bin/php/post/ticket_time_en_route.php",
    cache:false,
    data: {
      ID        : '<?php echo $_GET['ID'];?>',
      Latitude  : position.coords.latitude,
      Longitude : position.coords.longitude
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
function post_time_completed(link){
  $.ajax({
    url:"bin/php/post/ticket_time_completed.php",
    data: {ID : <?php echo $Ticket['ID'];?>},
    method:"POST",
    success:function(comp){
      var completed;
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
      var total = 0;
      total = calculate_Total();
      if(total <= 8){
        sliderRegular.noUiSlider.set(total);
      } else if(total <= 16) {
        var difference = total - 8;
        sliderRegular.noUiSlider.set(8);
        sliderOvertime.noUiSlider.set(difference);
      } else {
        var difference = total - 16;
        sliderRegular.noUiSlider.set(8);
        sliderOvertime.noUiSlider.set(8);
        sliderDoubletime.noUiSlider.set(difference);
      }
      tempReg = sliderRegular.noUiSlider.get();
      tempOvertime = sliderOvertime.noUiSlider.get();
      tempCompleted = 1;
    }
  });
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
  return canvas[0].toDataURL() == blank.toDataURL();
}
var tempLink = null;
function post_time_allocation(link){
  $(link).html("Saving <img src='media/images/spinner.gif' height='25px' width='auto' />");
  $(link).attr('disabled','disabled');
  tempLink = link;
  var message = "";
  if($("div#completed button").length != 0){message = message + "You need to click the completed button. ";}
  if(isCanvasBlank($("#signature"))){message = message + "A Signature needs to be filled out. ";}
  if($("textarea[name='Resolution']").val() == ''){message = message + "A Resolution needs to be filled out. "; }
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
      if (navigator.geolocation || true) {
        saveTicket();
        //navigator.geolocation.getCurrentPosition(saveTicket, showError, {enableHighAccuracy:true, timeout:10000});
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
function saveTicket(position){
  var ticketData = new FormData($('form#Receipt')[0]);
  ticketData.append("ID",'<?php echo $_GET['ID'];?>');
  ticketData.append("Regular",$("#time-regular").val());
  ticketData.append("Overtime",$("#time-overtime").val());
  ticketData.append("Doubletime",$("#time-doubletime").val());
  ticketData.append("NightDiff",$("#time-nightdiff").val());
  ticketData.append("CarExpenses",$("input[Name='CarExpenses']").val());
  ticketData.append("OtherExpenses",$("input[Name='OtherExpenses']").val());
  ticketData.append("Chargeable",$("input[name='Chargeable']").prop('checked'));
  ticketData.append("Follow_Up",$("input[name='Follow_Up']").prop('checked'));
  //ticketData.append("Latitude",position.coords.latitude,);
  //ticketData.append("Longitude",position.coords.longitude);
  ticketData.append("Longitude",0);
  ticketData.append("Latitude",0);
  ticketData.append("Resolution",$("textarea[name='Resolution']").val());
  ticketData.append("Signature_Text",$("input[name='Signature_Name']").val());
  ticketData.append("Signature_Canvas", $("#signature")[0].toDataURL("image/jpeg"));
  ticketData.append("Signature_Work_Order",ApplySignature);
  ticketData.append("Email",$("input[name='Email']").val());
  $.ajax({
    url:"bin/php/post/save_ticket.php",
    cache: false,
    processData: false,
    contentType: false,
    data: ticketData,
    timeout:15000,
    error:function(XMLHttpRequest, textStatus, errorThrown){
      alert('Your ticket did not save. Please check your internet.')
      $(tempLink).html("Save");
      $(tempLink).prop('disabled',false);
    },
    method:"POST",
    success:function(code){
      <?php if($_SESSION['User'] == 895){?>if(confirm("Can you please add information pertaining to the controller?")){
        document.location.href='unit.php?ID=<?php echo $Ticket['Unit_ID'];?>';
      } else {
        document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
      }<?php } else {?>
        document.location.href='ticket.php?ID=<?php echo $_GET['ID'];?>';
      <?php }?>
    }
  });
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
    success:function(code){document.location.href='work.php';}
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
  if(position.coords){
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
function hideError(error) {
  switch(error.code) {
    case error.PERMISSION_DENIED:
      console.log("You denied the request for Geolocation. You will not be able to start, edit or complete tickets until you enable it. Please reset your history to enable location services.");
      break;
    case error.POSITION_UNAVAILABLE:
      console.log("Location information is unavailable. You will not be able to start, edit or complete tickets until your location information is enabled.");
      break;
    case error.TIMEOUT:
      console.log("The location permission has timed out. Please click again and click enable.");
      break;
    case error.UNKNOWN_ERROR:
      console.log("An unknown error occurred. Please contact the ITHelpDesk@NouveauElevator.com");
      break;
    default:
      console.log('Ignore. Beta Test 5')
  }
}
navigator.geolocation.watchPosition(watchSuccess, hideError, {enableHighAccuracy:true, timeout:15000, maximumAge:0});
</script>
