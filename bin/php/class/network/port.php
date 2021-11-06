<?php
namespace network;
if(!trait_exists('traits\magic_methods')){require('bin/php/traits/magic_methods.php');}
class port {
  //traits
  use traits\magic_methods;
  //variables
  protected $id = null;
  protected $number = null;
  //functions
  public function __tostring(){return $this->__get('port');}
}
?>
