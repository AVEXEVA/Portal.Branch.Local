<?php 
class Form extends Element {
	//Variables
	///Attributes
	public $Method;
	public $Entype;
	public $Action;
	///Elements
	public $Fieldsets= array();
	public $Buttons = array();
	//Functions
	public function __construct($array = array()){
		parent::__construct($array);
	}
	public function Form(){
		?><form <?php parent::Attributes();?>><?php 
			for($i=0;$i<count($this->$Fieldsets);$i++){$this->$Fieldsets[$i]->Fieldset();}	
			?><DIV Class='Buttonset'><?php for($i=0;$i<count($this->Buttons);$i++){$this->Buttons[$i]->Button();}?></DIV><?php
		?></form><?php
	}
}
?>