<?php
namespace data;
class _integer extends \data\_index {
  //variables
  ///arguments
  protected $integer = null;
  //functions
  ///magic
  public function __validate(){ return is_int( parent::__get( 'integer') );}
}
?>
