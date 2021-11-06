<?php
namespace Time;
if(!trait_exists('Traits\Magic_Methods')){require('bin/PHP/Traits/Magic_Methods.php');}
Class Calendar {
  //Traits
  use Traits\Magic_Methods;
  //Variables
  protected $ID = NULL;
  protected $Name = NULL;
  protected $Description = NULL;
  //Arrays
  protected $Users = array();
}
?>
