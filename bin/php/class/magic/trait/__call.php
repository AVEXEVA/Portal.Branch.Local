<?php
namespace magic\trait;
trait __call {
  public function __call( $function, $_args){
    if( method_exists( $this, $function ) ){ 
      $this->$function($_args);
    }
  }
}?>
