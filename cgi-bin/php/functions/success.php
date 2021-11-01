<?php 
function success ( $message = '', $output = 'console' ){
  if( is_string( $message ) ){
     switch( $output ){
       case 'html':
         echo "<li class='success' style='background-color:#90ee90;color:black;margin:5px;padding:5px;'>{$message}</li>";
         break;
       case 'console':
         echo "<script>console.log('{$message}');</script>";
         break;
       default:
         break;
     }
  }
}
?>
