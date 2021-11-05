<?php
namespace http\session;
class index extends \index {
  //traits
  use \magic\methods\__constructors;
  //variables
  ///arguments
  protected $session    = null;
  protected $get        = null;
  protected $post       = null;
  protected $args       = null;
  ///security
  protected $user       = null;
  protected $connection = null;
  ///sql
  protected $dataservers     = null;
  //functions
  ///magic
  public function __construct( $_args  = array( ) ){
    if( session_id( ) == '' || !isset( $_SESSION )) { session_start( [ 'read_and_close' => true ] ); }
    parent::__construct( 
      array(
        'session'    => array( 
          'name'  => 'session',
          'type'  => '\data\_session',
          'value' => $_SESSION
        ),
        'post'    => array( 
          'name'  => 'post',
          'type'  => '\data\_post',
          'value' => $_POST
        ),
        'get'    => array( 
          'name'  => 'get',
          'type'  => '\data\_get',
          'value' => $_GET
        ),
        'args'    => array( 
          'name'  => 'post',
          'type'  => '\data\_args',
          'value' => $_args
        )
      )
    );
    self::__constructors( );
  }
}?>

