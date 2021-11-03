<?php
namespace Time;
if(!trait_exists('Traits\Magic_Methods')){require('cgi-bin/PHP/Traits/Magic_Methods.php');}
Class Lapse {
  //Traits
  use Traits\Magic_Methods;
  //Variables
  protected $ID = NULL;
  protected $Start = NULL;
  protected $End = NULL;
  //Functions
  public function __toString(){return $this->__get('Start') . ' to ' . $this->__get('End');}
}
?>
