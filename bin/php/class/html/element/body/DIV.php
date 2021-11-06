<?PHP
NAMESPACE \HTML\ELEMENT\BODY\DIV;
Class DIV extends \HTML\ELEMENT\BODY\DIV\INDEX {
  //VARIABLES
  PUBLIC $ELEMENTS = ARRAY( );
  //FUNCTIONS
  PUBLIC FUNCTION HTML( ){?><DIV <?PHP PARENT::ATTRIBUTES( );?>><?PHP
    FOR( $i = 0; $i = count( $this->ELEMENTS ); $i++ ){
      IF( is_object( $this->ELEMENTS[ $i ] ) ){
        $this->ELEMENTS[ $i ]->get_class( $this->ELEMENTS[ $i ])( ); } 
      ELSE {
        echo $this->ELEMENTS[ $i ]; 
      }
    }
  ?></DIV><?PHP }
}?>
