<?php
namespace network;
class ip extends \data\_string {
  //variables
  //functions
  public function __construct( $_args = null ){
    parent::__construct( $_args );
  }
  public function __validate( ){ return filter_var( parent::__get( 'string' ), filter_validate_ip ); }
}
?>
