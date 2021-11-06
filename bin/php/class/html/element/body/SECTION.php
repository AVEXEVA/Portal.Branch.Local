<?php 
NAMESPACE \HTML\ELEMENT\BODY\SECTION;
CLASS SECTION EXTENDS \HTML\ELEMENT\BODY\INDEX {
  //VARIABLES
  PUBLIC $ELEMENTS = array();
  //FUNCTIONS
  PUBLIC FUNCTION HTML(){?><SECTION <?php parent::ATTRIBUTES();?>><?php
    if(is_object($this->ELEMENTS[$i])){
      $this->Elements[$i]->get_class($this->ELEMENTS[$i])();
    } else {echo $this->ELEMENTS[$i];}
  ?></SECTION><?php }
}?>
