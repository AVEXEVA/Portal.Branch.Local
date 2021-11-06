<?php
namespace data;
class _datetime extends \data\_string {
  //variables
  protected $date = null;
  protected $time = null;
  //functions
  ///magic
  public function __construct( $_args = null ){
    parent::__construct( $_args );
    self::__constructor( );
  }
  public function __constructor( ){
    if( self::__validate( ) ){
      preg_match(
        '/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/',
        parent::__get( 'string' ),
        $matches
      );
      parent::__set(
        'date',
        new \data\type\date( array(
          'string' => $matches[ 1 ] . '-' . $matches[ 2 ] . '-' . $matches[ 3 ],
          'year'   => $matches[ 1 ],
          'month'  => $matches[ 2 ],
          'day'    => $matches[ 3 ],
        ) )
      );
      parent::__set(
        'time',
        new \data\type\time( array(
          'string' => $matches[ 4 ] . ':' . $matches[ 5 ] . ':' . $matches[ 6 ],
          'hour'   => $matches[ 4 ],
          'minute' => $matches[ 5 ],
          'second' => $matches[ 6 ]
        ) )
      );
    }
  }
  public function __validate( ){
    return    parent::__check( )
           && preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", parent::__get( 'string' ) );
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
}
?>
