<?PHP 
NAMESPACE \HTML\ELEMENT\BODY;
CLASS FOOTER ETENDS \HTML\ELEMENT\BODY\INDEX {
  //TRAITS
  /*NONE*/
  //VARIABLES
  PROTECTED $ELEMENTS = NULL;
  //FUNCTIONS
  ///CONSTRUCTORS
  PUBLIC FUNCTION __HTML( ){
    ?><FOOTER <?PHP PARENT::__HTML_ATTRIBUTES( );?>>
      <?php 
        IF( is_array( parent::__get( 'ELEMENTS' ) ) && count( parent::__get( 'ELEMENTS' ) ) > 0 ){
          FOREACH( parent::__get( 'ELEMENTS' ) AS $INDEX=>$ELEMENT ){
            IF(is_a( $ELEMENT, '\HTML\INDEX' ) ){ $ELEMENT->__HTML( ); }
            ELSEIF( is_string( $ELEMENT ) ){ ECHO $ELEMENT; }
          }
        }
      ?>
    </FOOTER><?PHP
  }
}?>
