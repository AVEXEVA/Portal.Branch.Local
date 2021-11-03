<?php
namespace CSS;
class FontWeight extends Magic {
  public $Value;
  //Functions
  public function __construct( $Array = array()){parent::__construct( $Array );}
	public function __constructor(){}
	public function __construction(){}
  public function __toCSS(){return 'font-weight:' . parent::__get('Value') . ';';}
}
?>
