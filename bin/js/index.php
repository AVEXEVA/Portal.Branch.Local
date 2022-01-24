<script data-pagespeed-no-defer src="https://www.nouveauelevator.com/vendor/jquery/jquery.min.js"></script>
<?php
  $_GET[ 'Bootstrap' ] = isset( $_GET[ 'Bootstrap' ] ) ? $_GET[ 'Bootstrap' ] : null;
  switch( $_GET[ 'Bootstrap' ] ){
    case '5.1':?><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-U1DAWAznBHeqEIlVSCgzq+c9gqGAJn5c/t99JyeKa9xxaYpSvHU5awsuZVVFIhvj" crossorigin="anonymous"></script><script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script><?php break;
    default:?><script src="https://www.nouveauelevator.com/vendor/bootstrap/js/bootstrap.min.js"></script><?php break;
    break;
  }
?>
<?php if( !isset( $_GET[ 'JQUERY_UI' ]) || $_GET[ 'JQUERY_UI' ]  == 1 ){?><script data-pagespeed-no-defer src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script><?php }?>
<script src="bin/js/functions.js"></script>
<script src="bin/js/onload.js"></script>
<script src="https://kit.fontawesome.com/46bc044748.js" crossorigin="anonymous"></script>
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-56288874-1"></script>
<script src="bin/js/index.js?<?php echo rand( 1000, 9999999 );?>"></script>
<script src='bin/js/function/columnVisibility.js?<?php echo rand( 1000, 99999 );?>'></script>
<?php 
if( file_exists( bin_js . 'page/' . substr( basename( $_SERVER['SCRIPT_NAME'] ), 0, strlen( basename( $_SERVER['SCRIPT_NAME'] ) ) - 4 ) . '/index.php') ){
  require( bin_js . 'page/' .  substr( basename( $_SERVER['SCRIPT_NAME'] ), 0, strlen( basename( $_SERVER['SCRIPT_NAME'] ) ) - 4 ) . '/index.php' );
}
?>
<?php require( bin_js . 'datatables.php');?>
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
      url:"bin/php/post/GPS.php",
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
$( document ).ready(function (){
  $( 'form[method="get"]' ).on( 'submit', function( ){
    document.location.href = this.action + '?' + $( this ).find( 'input:visible, select:visible, textarea:visible' ).fieldSerialize( );
  });
});
</script>
<!--<script type="module">
    import { initializeApp } from 'https://www.gstatic.com/firebasejs/9.1.2/firebase-app.js'
    import { } from 'https://www.gstatic.com/firebasejs/9.1.2/firebase-messaging.js'
    //import { auth } from 'https://www.gstatic.com/firebasejs/9.1.2/firebase-auth.js'
    //import { database } from 'https://www.gstatic.com/firebasejs/9.1.2/firebase-database.js'
  </script>-->
<script src="https://www.gstatic.com/firebasejs/8.2.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.2.1/firebase-messaging.js"></script>
<script type='module'>
  Notification.requestPermission(function(status) {
    console.log('Notification permission status:', status);
  });
self.addEventListener('notificationclick', function(e) {
  var notification = e.notification;
  var primaryKey = notification.data.primaryKey;
  var action = e.action;

  if (action === 'close') {
    notification.close();
  } else {
    clients.openWindow('http://www.nouveauelevator.com/portal/index.php');
    notification.close();
  }
});
self.addEventListener('push', function(e) {
  var options = {
    body: 'This notification was generated from a push!',
    icon: 'images/example.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: '2'
    },
    actions: [
      {action: 'explore', title: 'Explore this new world',
        icon: 'images/checkmark.png'},
      {action: 'close', title: 'Close',
        icon: 'images/xmark.png'},
    ]
  };
  e.waitUntil(
    self.registration.showNotification('Hello world!', options)
  );
});
const firebaseConfig = {
  apiKey: "AIzaSyC8EwR2xPTdKfM2rVY8xwgEhUwvS1NV68s",
  authDomain: "nouveau-elevator-portal.firebaseapp.com",
  projectId: "nouveau-elevator-portal",
  storageBucket: "nouveau-elevator-portal.appspot.com",
  messagingSenderId: "902592626299",
  appId: "1:902592626299:web:34668e30fa98d49a18ecde"
};

const app = firebase.initializeApp(firebaseConfig);

const messaging = app.messaging();
messaging.getToken({ vapidKey: 'BG7NS9MFx7Hb286vC3dAnW9dMRLGib-Xo737VVDFBlG-3dplGfJT_4ToV8o0yRka9bTCrhqiMGWw6Hp21nmjGIo' }).then((currentToken) => {
  if (currentToken) {
    $.ajax({
      url : 'bin/php/post/service_worker.php',
      method : 'POST',
      data : {
        token : currentToken
      }
    })
  } else {
    // Show permission request UI
    console.log('No registration token available. Request permission to generate one.');
    // ...
  }
}).catch((err) => {
  console.log('An error occurred while retrieving token. ', err);
  // ...
});
</script>