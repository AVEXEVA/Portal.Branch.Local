<?php
namespace data;
class _object extends \data\index {
  //variables
  protected $object = null;
  //functions
  public function __validate( $object = null ){ return is_object( parent::__get( 'object' ) ); }
}?>
