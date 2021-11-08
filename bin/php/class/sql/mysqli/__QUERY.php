<?PHP
NAMESPACE \SQL\MYSQLI;
TRAIT __QUERY {
  PROTECTED FUNCTION __QUERY( $_QUERY = NULL, $_ARGS = ARRAY( ) ){
    IF(     PARENT::__ISSET( 'RESOURCE' ) 
        &&  IS_STRING( $_QUERY )
        &&  IS_ARRAY( $_ARGS ) ){
            $RESULT = MYSQLI_QUERY(
              PARENT::__GET( 'RESOURCE' ),
              $QUERY,
              $_ARGS;
            );
            RETURN $RESULT;
    } ELSE {
      RETURN FALSE;
    }
  }
}?>
