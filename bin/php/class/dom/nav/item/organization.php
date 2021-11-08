<?php
namespace dom\nav\item;
class organization {
	public function __construct( ){
		?><li class="nav-item dropdown">
			<a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
				<?php new \icon\organization( );?> Organization
			</a>
			<ul class="dropdown-menu" aria-labelledby="navbarDropdown">
				<li class="dropdown-item" href="#">All</li>
				<li class="dropdown-divider"></li>
				<li class="dropdown-submenu" href="#">
					<a><input type='checkbox' /> Accounting</a>
					<ul>
						<li class='dropdown-item'><input type='checkbox' /> Collection</li>
						<li class='dropdown-item'><input type='checkbox' /> Payable</li>
						<li class='dropdown-item'><input type='checkbox' /> Billing</li>
						<li class='dropdown-item'><input type='checkbox' /> Payroll</li>
					</ul>
				</li>
				<li class="dropdown-item" href="#"><input type='checkbox' /> Operations</li>
				<li class="dropdown-item" href="#"><input type='checkbox' /> Dispatching</li>
				<li class="dropdown-divider"></li>
			<li class="dropdown-item" href="#">None</li>
			</ul>
        </li><?php
	}
}