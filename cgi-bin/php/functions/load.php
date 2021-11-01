<?php
function load( $path = null, $dir = '/var/www/portal.live.local/html/cgi-bin' ){
  $dir = substr( $dir, 0, 7 ) == 'cgi-bin' ? '/var/www/portal.live.local/html/' . $dir : $dir;
  try {
    if( file_exists( $dir . '/' . $path ) ){
      require( $dir . '/' . $path );
    }
  } catch( exception $e ){ }
}?>
