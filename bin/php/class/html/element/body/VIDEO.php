<?php 
NAMESPACE \HTML\ELEMENT\BODY;
Class VIDEO extends \HTML\ELEMENT\BODY\INDEX {
  //VARIABLES
  ///ATTRIBUTES
  PUBLIC $WIDTH    = '100%';
  PUBLIC $HEIGHT   = '100%';
  PUBLIC $CONTROLS = False;
  PUBLIC $AUTOPLAY = False;
  ///Elements
  PUBLIC $SOURCES  = ARRAY();
  //FUNCTIONS
  ///HTML
  PUBLIC FUNCTION HTML(){?><VIDEO <?PHP PARENT::ATTRIBUTES( );?>><?PHP
    FOR( $i=0; $i < count( $this->SOURCES ); $i++ ){ $this->SOURCES[ $i ]->SOURCE();}
  ?></VIDEO><?php }
}?>
