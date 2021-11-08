<?php
namespace magic\trait;
trait __echo {
  public function __echo( $key = null ){
    if( self::__isset( $key ) ){ echo self::__get( $key ); }
    else { error( "error \magic\echo : {$key}" ); } 
  }
}
?>
