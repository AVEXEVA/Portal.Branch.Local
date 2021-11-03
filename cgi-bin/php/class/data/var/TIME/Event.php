<?php
namespace Time;
if(!trait_exists('Traits\Magic_Methods')){require('cgi-bin/PHP/Traits/Magic_Methods.php');}
Class Event {
  //Traits
  use Traits\Magic_Methods;
  //Variables
  protected $ID = NULL;
  protected $Name = NULL;
  protected $Description = NULL;
  protected $Time_Lapse = NULL;
  //Functions
  public function __toString(){return $this->__get('Name');}
}
?>
