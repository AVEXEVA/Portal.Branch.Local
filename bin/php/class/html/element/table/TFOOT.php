<?php class TFOOT extends Element {
	//Variables
	//Elements
	public $TRS = array();
	//Functions
	public function TFOOT(){?><TFOOT><?php 
		for($i=0;$i<count($this->TRS);$i++){$this->TRS[$i]->TR();}
	?></TFOOT><?php
}?>