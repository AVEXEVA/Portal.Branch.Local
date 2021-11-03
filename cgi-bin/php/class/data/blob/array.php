<?php
namespace \Data\Type;
Class Array extends \Data\Type\index {
  //Variables
  protected $Array = NULL;
  //Functions
  protected function __check( ){return is_array( parent::__get( 'Array' )); }
}
?>
