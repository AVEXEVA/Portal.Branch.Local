<?PHP
NAMESPACE \HTML\ELEMENT\BODY;
CLASS LI EXTENDS \HTML\ELEMENT\BODY\INDEX {
  //VARIABLES
  PROTECTED $ELEMENTS = ARRAY( );
  //FUNCTIONS
  PUBLIC FUNCTION HTML( ){
    ?><LI <?PHP ECHO PARENT::__ATTRIBUTES( );?>>
      <?PHP 
        IF(is_array( PARENT::__get( 'ELEMENTS' ) ) &&  count( PARENT->__get( 'ELEMENTS' ) ) ){
          FOREACH( parent::__get( 'ELEMENTS' ) AS $ELEMENT ){
            $ELEMENT->__HTML( );
          }
        }
      ?>
    </LI><?php
  }
}?>
