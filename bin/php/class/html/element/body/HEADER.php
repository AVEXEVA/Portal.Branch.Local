<?php 
NAMESPACE \HTML\ELEMENT\BODY;
CLASS HEADER EXTENDS \HTML\ELEMENT\BODY\INDEX {
  PUBLIC FUNCTION __construct(){
    ?><HEADER <?PHP PARENT::__toString(' ');?>><?PHP ECHO PARENT::__get('NAME');?></HEADER><?PHP 
  }
}?>
