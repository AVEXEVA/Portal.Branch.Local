<?php class TBODY extends Element {
	//Variables
	//Elements
	public $TRS = array();
	//Functions
	public function TBODY(){?><TBODY><?php 
		for($i=0;$i<count($this->TRS);$i++){$this->TRS[$i]->TR();}
	?></TBODY><?php
}?>