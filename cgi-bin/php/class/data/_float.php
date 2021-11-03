<?php
namespace data;
class _float extends \data\index {
  //variables
  ///arguments
  protected $float = null;
  //functions
  ///magic
  public function __validate(){ return is_numeric( parent::__get( 'float' ) );}
}
?>
