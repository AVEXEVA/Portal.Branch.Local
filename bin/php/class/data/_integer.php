<?php
namespace data;
class _integer extends \data\_index {
  protected $integer = null;
  public function __validate(){ 
  	return is_int( parent::__get( 'integer') );
  }
}
?>
