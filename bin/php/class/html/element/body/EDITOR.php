<?php 
NAMESPACE \HTML\ELEMENT\BODY;
CLASS EDITOR EXTENDS \HTML\ELEMENT\BODY\INDEX {
  public $Path;
  public $PRE;
  public function Write(){}
  public function Read(){
    $Path = cleanPath($Path);
    if(is_string($Path)){
      $f = fopen($Path, 'r');
      $this->PRE = new PRE(array(
        'HTML' => fread($f, filesize($Path))
      ));
      fclose($f);
    } else {?>&nbsp;<?php }
  }
  public static function cleanPath($Path){
    return     strpos($Path, '.') 
           &&  strpos($Path, '.') < strrpos($Path, '/') 
               ? $Path 
               : False;
  }
}
?>
