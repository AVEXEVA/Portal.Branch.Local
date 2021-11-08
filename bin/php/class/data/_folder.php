<?php
namespace \type;
class _folder extends \data\index {
  //variables
  protected $id = null;
  protected $name = null;
  protected $parent = null;
  //arguments
  protected $path = null;
  //contents
  protected $files = null;
  //functions
  ///magic
  public function __construct( $_args = null ){
    parent::__construct( $_args );
  }
  public function __scan( ){
    $files = array( );
    if( self::__check( ) ){
      foreach( scandir( parent::__get( 'path' ) ) as $index=>$file ){
        if( $file == '.' || $file == '..'){ continue; }
        $files[ ] = new \data\type\file( $file );
      }      
    }
    parent::__get( 'files', $files );
  }
  public function __check( ){ return file_exists( parent::__get( 'path' ) ); }
}
?>
