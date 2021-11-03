<?php Class TR extends Element {
	//Variables
	public $TS = array();
	//Functions
	public function TR(){?><TR <?php parent::Attributes();?>><?php 
		for($i=0;$i<count($this->TS);$i++){
			if(is_object($this->TS[$i])){
				if(is_a($this->TS[$i], 'TH'){$this->TS[$i]->TH();}
				elseif(is_a($this->TS[$i], 'TD'){$this->TS[$i]->TD();}
			}
		}
	?></TR><?php
}?>