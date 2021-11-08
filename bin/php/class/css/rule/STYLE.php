<?php
namespace CSS;
class Style extends Magic {
  public $CSS_Rules = array();
  //Functions
  public function __construct( $Array = array()){parent::__construct( $Array );}
	public function __constructor(){}
	public function __construction(){}
  //Handlers
  public function __toCSS(){
    $Rules = array();
    if(parent::validate(parent::__get('Styles'), 'array+')){foreach(parent::__get('Styles') as $Rule){$Rules[] = $Rule->__toCSS();}}
    return implode('', $Rules);
  }
  public function __toStyle(){return '<style>' . self::__toCSS() . '</style>';}
}
?>
