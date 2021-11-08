<?php
namespace CSS;
class Height extends Magic {
  public $Value;
  //Functions
  public function __construct( $Array = array()){parent::__construct( $Array );}
	public function __constructor(){}
	public function __construction(){}
  public function __toCSS(){return 'height:' . parent::__get('Value') . ';';}
}
?>
