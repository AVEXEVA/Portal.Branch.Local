<?php
namespace magic\trait;
trait __get {
  public function __get( $_args = null ){
    if( is_array( $_args ) ){
      $array = array();
      if(count( $_args ) > 0){
        foreach( $_args as $index => $key ){
          $array[ ] = self::__isset( $key ) 
            ? self::__get( $key ) 
            : '';
        }
      }
      return $array; } 
    elseif(self::__isset( $_args ) ) {
      return $this->$_args;
    }
  }
}
?>
