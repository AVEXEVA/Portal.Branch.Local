<?php
namespace network;
if(!trait_exists('traits\magic_methods')){require('cgi-bin/php/traits/magic_methods.php');}
class modem {
  //traits
  use traits\magic_methods;
  //variables
  public $id = null;
  public $name = null;
  public $ip = null;
}
?>
