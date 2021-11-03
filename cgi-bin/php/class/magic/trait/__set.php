<?php
namespace magic\trait;
trait __set {
  public function __set( $key, $value = null ){
    if( is_array( $key ) ){
      if( count( $key ) > 0){
        foreach( $key as $k=>$v){
          if( self::__isset($k)){
            $this->$k = $v;
          }}}} 
    elseif( self::__isset( $key ) ){
      $this->$key = $value;
    }
  }
}
?>
