<?php
namespace dom\li;
class home {
	public function __construct( ){
		?><li class="nav-item active">
			<a class="nav-link" href="index.php"><?php new \icon\home( );?> Home <span class="sr-only">(current)</span></a>
		</li><?php
	}
}?>