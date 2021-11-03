<?php
namespace magic\trait;
trait __isset {
  public function __isset( $key ){
    return property_exists( $this, $key );
  }
}
?>
