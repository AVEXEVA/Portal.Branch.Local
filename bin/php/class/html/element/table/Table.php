<?php Class Table extends Element {
	//Variables
	//Elements
	public $THEAD;
	public $TBODY;
	public $TFOOT;
	//Functions
	public function Table(){?><Table <?php parent::Attributes();?>><?php
		if(is_object($this->THEAD)){$this->THEAD->THEAD();}
		if(is_object($this->TBODY)){$this->TBODY->TBODY();}
		if(is_object($this->TFOOT)){$this->TFOOT->TFOOT();}
	?></Table><?php
}?>