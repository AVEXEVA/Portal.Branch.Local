<?php
namespace http\session;
class token extends \index {
  //variables
  private $alphanumeric = null;
  //functions
  public function __construct( $_args = array( ) ){
    parent::__construct( 
      array(
        'alphanumeric' => new \data\_alphanumeric( $_args )
      )
    );
  }
}
?>
