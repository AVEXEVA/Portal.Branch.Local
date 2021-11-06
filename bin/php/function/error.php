<?php 
function error ( $message = '', $output = 'console' ){
  if( is_string( $message ) ){
    switch( $output ){
      case 'html':
        echo "<li class='error' style='background-color:#ff3333;color:black;padding:5px;margin:5px;'>{$message}</li>";
        break;
      case 'console':
        echo "<script>console.log('{$message}')</script>";
        break;
      default:
        break;
    }
  }
}?>
