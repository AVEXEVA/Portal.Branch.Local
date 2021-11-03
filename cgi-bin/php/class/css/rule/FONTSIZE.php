<?php
namespace CSS;
class FontSize extends Magic {
  public $Value;
  //Functions
  public function __construct( $Array = array()){parent::__construct( $Array );}
	public function __constructor(){}
	public function __construction(){}
  public function __toCSS(){return 'font-size:' . parent::__get('Value') . ';';}
}
?>
