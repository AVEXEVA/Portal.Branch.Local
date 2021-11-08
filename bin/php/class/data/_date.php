<?php
namespace data;
class _date extends \data\_string {
  //traits
  //variables
  ///computed
  protected $year  = null;
  protected $month = null;
  protected $day   = null;
  //functions
  public function __construct( $_args = null ){
    parent::__construct( $_args );
    self::__constructor( );
  }
  public function __constructor( ){
    if( parent::__check( ) ){
      preg_match(
        '/(\d{4})-(\d{2})-(\d{2})/',
        parent::__get( 'string' ),
        $matches
      );
      parent::__set( 'year', $matches[ 1 ] );
      parent::__set( 'month', $matches[ 2 ] );
      parent::__set( 'day', $matches[ 3 ] );
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
