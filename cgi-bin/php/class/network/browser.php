<?php
namespace network;
if(!trait_exists('traits\magic_methods')){require('cgi-bin/php/traits/magic_methods.php');}
class browser {
  //traits
  use traits\magic_methods;
  //variables
  protected $id = null;
  protected $user_agent = null;
}
?>
