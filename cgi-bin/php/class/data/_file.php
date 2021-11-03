<?php
namespace data;
class _file extends \data\_index {
  //variables
  protected $id        = null;
  protected $name      = null;
  protected $extension = null;
  protected $size      = null;
  protected $folder    = null;
  protected $link      = null;
  ///arguments
  protected $path      = null;
  ///information
  protected $content   = null;
  //functions
  ///magic 
  public function __construct( $_args = null {
    parent::__construct( $_args );
    self::__open( );
  }
  public function __size( ){ parent::__set( 'size', filesize( parent::__get( 'path' ) ) ); }
  public function __destroy( ){ self::__close( ); }
  public function __open( $mode = 'a+' ){
    if( file_exists( parent::__get( 'path' ) ) ){
      parent::__set(
        'link',
        fopen( parent::__get( 'path' ), $mode )
      );
    }
  }
  public function __close( ){ fclose( parent::__get( 'link' ) ); }
  public function __read( ){ parent::__set( 'content', fread( parent::__get( 'link' ), filesize( parent::__get( 'path' ) ) ) ); }
}
?>
