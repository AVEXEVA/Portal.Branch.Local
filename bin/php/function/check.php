<?php 
function check($requestedPerm, $level, $permInt){
    return ( hexdec( dechex( $requestedPerm << $level ) ) & hexdec( $permInt ) ) != 0;
}
?>