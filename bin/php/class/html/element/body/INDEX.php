<?PHP
NAMESPACE \HTML\ELEMENT\BODY;
CLASS INDEX EXTENDS \HTML\ELEMENT\INDEX {
  //TRAITS
  use \TRAIT\CSS\INDEX;
  //VARIABLES
  ///ARGUMENTS
  PROTECTED $ID      = NULL;
  PROTECTED $NAME    = NULL;
  PROTECTED $CLASS   = NULL;
  PROTECTED $REL     = NULL;
  PROTECTED $CONTENT = NULL;
  ///COMPUTED
  PROTECTED $HTML    = NULL;
  //FUNCTIONS
  ///MAGIC
  PUBLIC FUNCTION __construct( $_ARGS = NULL ){
    PARENT::__construct( $_ARGS );
  }
  ///HTML
  PROTECTED FUNCTION __CONTENT( ){ ECHO PARENT::__get( 'CONTENT' ); }
  PROTECTED FUNCTION __HTML( ){ECHO $this->HTML;}
  PROTECTED FUNCTION __HTML_ATTRIBUTES( ){
    $STRINGS = [ ];
    FOREACH( get_object_vars( $this ) as $KEY => $VALUE ){
      IF( is_array( $VALUE ) || is_object( $VALUE ) ){ CONTINUE; }
      ELSEIF( in_array( $value, ARRAY( 'HTML' )){ CONTINUE; }
      $STRINGS[ ] = "{$KEY}='{$VALUE}'";
    }
    echo implode( ' ', $STRINGS );
  }
}?>
