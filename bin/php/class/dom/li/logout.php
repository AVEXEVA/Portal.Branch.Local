<?php
namespace dom\li;
class logout {
	public function __construct( ){
		?><li class="nav-item">
			<a class="nav-link" href="login.php?Logout"><?php new \icon\logout( );?> Log out</a>
		</li><?php
	}
}