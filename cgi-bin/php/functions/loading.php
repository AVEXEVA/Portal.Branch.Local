<?php function loading ( $message = '', $output = 'console' ){
  if( is_string( $message ) ){
    switch( $output ){
      case 'html':
        echo "<li class='error' style='background-color:#00eaff;color:black;padding:5px;margin:5px;'>{$message}</li>";
        break;
      case 'console':
        echo "<script>console.log('{$message}');</script>";
        break;
      default:
        break;
    }
  }
}?>
