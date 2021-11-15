<?php
function autoloader( $string ){
  $string = str_replace( '\\', '/', $string );
  $folderPath = '/var/www/html/Portal.Branch.Local/bin/php/class/' . $string;
  $filePath = $folderPath . '.php';
  if( file_exists( $filePath ) ){ require( $filePath ); } 
  elseif( file_exists( $folderPath ) ){ require( $folderPath . '/index.php' ); }
}
spl_autoload_register('autoloader');
?>
