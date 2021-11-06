<?php Class SERVER {
	//Variables
	private $REMOTE_ADDR;
	private $UserAgent;
	private $SCRIPT;
	//Traits
	use Magic\Methods;
	//Functions
	public function __construct($array = array()){
		self::__set($array);
	}
}?>