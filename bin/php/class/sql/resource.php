<?php
namespace sql;
class resource extends \magic {
   //variables
   protected $database = null;
   //sqlsrv
   protected $link = null;
   protected $type = null;
   //functions
   public function __construct( $_args = array( ) ){
     parent::__construct( $_args );
     self::__connect();
   }
   private function __connect(){
     switch( parent::__get( 'type' ) ){
       case 'sqlsrv' : self::__sqlsrv( ); break;
       case 'mysqli' : self::__mysqli( ); break;
       default       : self::__mysqli( ); break;
     }
   }
   private function __sqlsrv( ){
     parent::__set( 
       'link',
       sqlsrv_connect(
         parent::__get( 'database' )->__get( 'ip' ),
         parent::__get( 'database' )->__get( 'username' ),
         parent::__get( 'database' )->__get( 'password' ),
         parent::__get( 'database' )->__get( 'name' )
       )
     )
   }
   private function __mysqli( ){ 
     parent::__set(
       'link',
       mysqli_connect(
          parent::__get( 'database' )->__get( 'ip' ),
          parent::__get( 'database' )->__get( 'username' ),
          parent::__get( 'database' )->__get( 'password' ),
          parent::__get( 'database' )->__get( 'name' )
       )
     );
   }
}
?>
