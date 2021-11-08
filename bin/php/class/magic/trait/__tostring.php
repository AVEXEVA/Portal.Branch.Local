<?php
namespace magic\trait;
trait __tostring {
  public function __tostring(){
    $strings = [];
    foreach( get_object_vars( $this ) as $key=>$value ){
      if( is_array( $value ) || is_object( $value ) ){ continue; }
      $strings[ ] = "{$key}='{$value}'";
    }
    return implode( $delimter, $strings );
  }
}
?>
