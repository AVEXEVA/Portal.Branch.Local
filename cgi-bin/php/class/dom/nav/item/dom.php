<?php
namespace dom\nav\item;
class dom {
	public function __construct( ){
		?><li class="nav-item dropdown">
			<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<?php new \icon\filter( );?> Other
			</a>
			<div class="dropdown-menu" aria-labelledby="navbarDropdown">
				<a class="dropdown-item" href="#">All</a>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item" href="#"><input type='checkbox' /> Graphs</a>
				<a class="dropdown-item" href="#"><input type='checkbox' /> Tables</a>
				<div class="dropdown-divider"></div>
				<a class="dropdown-item" href="#">None</a>
			</div>
        </li><?php
	}
}