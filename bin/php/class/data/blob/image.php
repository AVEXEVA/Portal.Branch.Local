<?php
namespace Multimedia;
if(!trait_exists('Traits\Magic_Methods')){require('bin/PHP/Traits/Magic_Methods.php');}
Class Image {
  //Traits
  use Traits\Magic_Methods;
  //Variables
  protected $ID = NULL;
  protected $Multimedia = NULL;
}
?>
