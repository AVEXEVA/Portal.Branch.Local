<?php
namespace network;
class server extends \index {
  //variables
  protected $id          = null;
  protected $name        = null;
  protected $description = null;
  protected $ip          = null;
  protected $hostname    = null;
  //functions
  public function __construct( $_args = null ){
    parent::__construct( $_args );
    self::__constructor( );
  }
  private function __constructor( ){
    parent::__set( 'ip', new \network\ip( array( 'string' => $_server['server_addr'] ) ) );
    parent::__set( 'hostname', new \network\hostname( array( 'string' => $_server['server_name'] ) ) );
  }
}?>
