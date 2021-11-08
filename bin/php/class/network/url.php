<?php
namespace network;
class url extends \index {
  //variables
  public $id = null;
  public $address = null;
  //functions
  protected __tostring(){return $this->__get('url');}
}
?>
