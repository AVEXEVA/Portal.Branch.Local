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

const firebase = initializeApp(firebaseConfig);

const messaging = firebase.messaging();
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