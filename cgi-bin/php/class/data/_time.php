<?php
namespace data;
class _time extends \data\_string {
  //traits
  //variables
  protected $hour = null;
  protected $minute = null;
  protected $second = null;
  //functions
  public function __construct( $_args = null ){
    parent::__construct( $_args );
    self::__constructor( );
  }
  public function __constructor( ){
    if( parent::__check( ) ){
      preg_match(
        '/(\d{2}):(\d{2}):(\d{2})/',
        parent::__get( 'string' ),
        $matches
      );
      parent::__set( 'hour', $matches[ 1 ];
      parent::__set( 'minute', $matches[ 2 ];
      parent::__set( 'second', $matches[ 3 ];
    }
  }
  public function __strtotime( $string = null ){
    if( is_string( $string ) ){
      self::__construct(
        array(
          'string' => date(
            parent::__get( 'string' ),
            strtotime( $string )
          )
        )
      );
    }
  }
}?>
