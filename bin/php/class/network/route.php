<?php
namespace network;
if(!trait_exists('traits\magic_methods')){require('bin/php/traits/magic_methods.php');}
class router {
  //traits
  use traits\magic_methods;
  //variables
  public $id = null;
  public $name = null;
  public $ip = null;
}
?>
