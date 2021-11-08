<?php Class Browser extends DIV {
	public function __construct($Folder){parent::__construct(array(
		new \UL\Menu(array(
			'ID'    => 'Browser',
			'Class' => 'Browser'
		)),
		new \DIV\Container(array(
			'ID'    => 'Browser_Container',
			'Class' => 'Container'
		))
	));}
}
?>