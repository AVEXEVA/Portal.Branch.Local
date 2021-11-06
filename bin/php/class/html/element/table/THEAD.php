<?php class THEAD extends Element {
	//Variables
	//Elements
	public $TRS = array();
	//Functions
	public function THEAD(){?><THEAD><?php 
		for($i=0;$i<count($this->TRS);$i++){$this->TRS[$i]->TR();}
	?></HEAD><?php
}?>