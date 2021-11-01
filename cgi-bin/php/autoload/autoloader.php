<?php
function autoloader( $string ){
  $string = str_replace( '\\', '/', $string );
  if( file_exists( '/var/www/portal.live.local/html/cgi-bin/php/class/' . $string . '.php' ) ){
    load('class/' . $string . '.php', '/var/www/portal.live.local/html/cgi-bin/php' );
  }
}
spl_autoload_register('autoloader');
?>
