<?php
session_start();
if(isset($_SESSION['User'],$_SESSION['Hash'])){
  if( in_array( $_SESSION['User'], array( 250, 895 ) ) ){?>
  	<html>
  	<head>
  		<style>
  			html, body { 
  			margin : 0px;
	  		}
	  	</style>
  	</head>
  	<body>
	  	<div style='width:100%;text-align:center;height:100%;font-size:32px;'>
	  		<div  style='height:100%;width:49%;float:left;background-color:black;color:white;' onClick="document.location.href='home.php?Version=Beta';"><div style='position:relative;top:40%;'>Beta</div></div>
	  		<div style='height:100%;width:49%;float:right;background-color:white;color:black;' onClick="document.location.href='home.php?Version=Live';"><div style='position:relative;top:40%;'>Live</div></div>
	  	</div>
	</body>
	</html>
  	<?php  }
} else {?><html><head><script>document.location.href='../login.php';</script></head></html><?php }
?>
