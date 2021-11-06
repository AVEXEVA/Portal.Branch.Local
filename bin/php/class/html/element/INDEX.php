<?PHP
NAMESPACE \HTML\ELEMENT;
CLASS INDEX EXTENDS \HTML\INDEX {
  //TRAITS
  use \TRAIT\CSS\INDEX; 
  //VARIABLES
  ///ARGUMENTS
  PROTECTED $ID    = NULL;
  PROTECTED $NAME  = NULL;
  PROTECTED $CLASS = NULL;
  PROTECTED $REL   = NULL;
  ///COMPUTED
  PROTECTED $HTML  = NULL;
 //FUNCTIONS
  //MAGIC
  PUBLIC FUNCTION __construct( $_ARGS = NULL ){
    PARENT::__construct( $_ARGS );
  }
  ///CONSTRUCTORS
  PROTECTED FUNCTION HTML( ){ ECHO $this->HTML; }
  PROTECTED FUNCTION ATTRIBUTES( $DELIMETER = ARRAY( ) ){
    $STRINGS = [ ];
    FOREACH( get_object_vars( $this ) as $KEY => $VALUE ){
      IF( is_array( $VALUE ) || is_object( $VALUE ) ){ continue; }
      ELSEIF( in_array( $value, ARRAY( 'HTML' )){ continue; }
      $STRINGS[] = "{$KEY}='{$VALUE}'";
    }
    echo implode( $DELIMETER, $STRINGS );
  }
}
?>
