<?php
namespace sql;
class dataserver extends \network\server {
  //varaibles
  ///arguments
  protected $id   = null;
  protected $name = null;
  protected $description = null;
  protected $type = null;
  //functions
  ///magic 
  public function __construct( $_args = null ) {
    parent::__construct( $_args );
  }
  private function __constructors( ){
    //self::__databases( );
  }
  private function __databases( ){
    switch( parent::__get( 'type' ) ){
      case 'mysql'      : self::__databases_mysql( );      break;
      case 'mssql'      : self::__databases_mssql( );      break;
      case 'mariadb'    : self::__databsaes_mariadb( );    break;
      case 'mongodb'    : self::__databases_mongodb( );    break;
      case 'postgresql' : self::__databases_postgresql( ); break;
      default           : self::__databases_mssql( );
    }
  }
}?>
