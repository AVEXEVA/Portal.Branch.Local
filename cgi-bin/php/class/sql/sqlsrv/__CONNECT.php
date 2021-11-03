<?PHP
NAMESPACE SQL\SQLSRV;
TRAIT __CONNECT {
  PROTECTED FUNCTION __CONNECT( $SERVER, $DATABASE, $USER, $OPTIONS ){
    RETURN SQLSRV_CONNECT(
      $SERVER->__GET( 'IP' )->__GET( 'STRING' ),
      ARRAY( 
        'Database' => $DATABASE->__GET( 'NAME' ),
        'UID'      => $USER->__GET( 'NAME' ),
        'PWD'      => $USER->__GET( 'PASSWORD' )
      )
    ); 
  }
}?>
