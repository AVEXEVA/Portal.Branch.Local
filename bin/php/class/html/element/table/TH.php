<?php Class TH extends Element {
	//Variables
	public $COLSPAN;
	//Functions
	public function TH(){?><TD <?php parent::Attributes();?>><?php echo parent::__get('Rel');?></TH><?php
}?>