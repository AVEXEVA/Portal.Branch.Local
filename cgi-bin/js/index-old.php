<!-- jQuery -->
<script data-pagespeed-no-defer src="https://www.nouveauelevator.com/vendor/jquery/jquery.min.js"></script>

<!-- jQuery UI  -->


<!-- Functions -->
<script src="cgi-bin/js/functions.js"></script>

<!-- On Load -->
<script src="cgi-bin/js/onload.js"></script>

<script>
/* When the user clicks on the button,
toggle between hiding and showing the dropdown content */
function dropDown() {
    document.getElementById("myDropdown").classList.toggle("show");
}

// Close the dropdown if the user clicks outside of it
window.onclick = function(event) {
  if (!event.target.matches('.dropbtn')) {

    var dropdowns = document.getElementsByClassName("dropdown-content");
    var i;
    for (i = 0; i < dropdowns.length; i++) {
      var openDropdown = dropdowns[i];
      if (openDropdown.classList.contains('show')) {
        openDropdown.classList.remove('show');
      }
    }
  }
}
</script>
<script type="text/javascript">
    $(document).ready(function(){setTimeout(function(){$("div#wrapper.toggled ul.collapse.in:not([aria-expanded])").removeClass("in");},250);});
    $(document).ready(function() {
        $("#menu-toggle").click(function(e) {
            e.preventDefault();

            $("#wrapper").toggleClass("toggled");

            $('#wrapper.toggled').find("#sidebar-wrapper").find(".collapse").collapse('hide');
            $.ajax({
              url:"php/post/toggleMenu.php"
            });
        });
    });
</script>
<script>
Notification.requestPermission().then(function(result) {
  console.log(result);
});
  // Finally, if the user has denied notifications and you
  // want to be respectful there is no need to bother them any more.
var title = "What's Up?";
var img = '/to-do-notifications/img/icon-128.png';
var text = 'HEY! Your task "' + title + '" is now overdue.';
//var notification = new Notification('To do list', { body: text, icon: img });
</script>
<script>
var updated = 0;
function watchSuccess(position){
  if(updated == 0){
    updated = 1;
    var gpsData = new FormData();
    gpsData.append("Latitude",position.coords.latitude,);
    gpsData.append("Longitude",position.coords.longitude);
    gpsData.append("Time_Stamp",position.timestamp);
    $.ajax({
      url:"cgi-bin/php/post/updateGPS.php",
      method:"POST",
      data:gpsData,
      cache:false,
      processData: false,
      contentType: false,
      success:function(code){
        updated = 0;
      },
      error:function(XMLHttpRequest, textStatus, errorThrown){
        updated = 0;
      }
    });
  }
}
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
