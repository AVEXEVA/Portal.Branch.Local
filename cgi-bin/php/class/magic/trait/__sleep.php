<?php
namespace magic\trait;
trait __sleep {
  public function __sleep( $_args = null ){
    if( is_null( $_args ) ){ return self::__sleep( get_class_vars( get_class( $this  ) ) ); } 
    elseif( is_array( $_args ) ){
      if( count( $_args ) > 0 ){
        foreach( $_args as $key=>$value ){
          if( self::__isset( $key ) ){ $_args[ $key ] = self::__get( $key ); }
        }
      }
      return $_args; } 
    elseif(self::__isset( $_args )){
      return array( $_args => self::__get( $_args ) );
    }
  }
}?>
