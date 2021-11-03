<?php
namespace data;
class _bit extends \data\index {
  //traits
  //variables
  protected $bit = null;
  //functions
  public function __construct( $_args = null ){
    parent::__construct( $_args );
    self::__check( );
  }
  private function __check( ){
    return in_array( 
      parent::__get( 'bit' ),
      array( 0, 1 )
    );
  }
}?>
