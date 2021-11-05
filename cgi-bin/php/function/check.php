<?php 
function check($requestedPerm, $level, $permInt){
    return ($requestedPerm << $level) & $permInt != 0;
}
?>