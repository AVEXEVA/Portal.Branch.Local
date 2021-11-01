<?php
function exists( $path = null, $dir = '/bin/php/' ){
  return file_exists( $dir . '/' . $path );
}?>
