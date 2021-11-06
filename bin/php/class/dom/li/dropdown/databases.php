<?php 
namespace dom\li\dropdown;
class databases {
	public function __construct( ){
		?><li class="nav-item dropdown">
			<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<?php new \icon\database( );?> Databases
			</a>
			<div class="dropdown-menu" aria-labelledby="navbarDropdown">
				<a class="dropdown-item" href="#"><input type='checkbox' /> All</a>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item" href="#"><input type='checkbox' /> Connecticut</a>
				<a class="dropdown-item" href="#"><input type='checkbox' /> Florida</a>
				<a class="dropdown-item" href="#"><input type='checkbox' /> Illinois</a>
				<a class="dropdown-item" href="#"><input type='checkbox' /> New Jersey</a>
				<a class="dropdown-item" href="#"><input type='checkbox' /> New York</a>
				<a class="dropdown-item" href="#"><input type='checkbox' /> Texas</a>
			</div>
		</li><?php
	}
}
?>