<?php
namespace magic\trait;
trait __construct {
  public function __construct( $_args = null ){
    try {
      //success('success ' . get_class($this) . '->__construct( $_args );');
      self::__set( $_args );
    } catch( exception $exception ){
      //error('error ' . get_class($this) . '->__construct( $_args);');
    }
  }
}
?>
