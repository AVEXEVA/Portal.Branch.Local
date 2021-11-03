<?php
namespace dom\header;
class nouveau {
	public function __construct( ){
		?><header>
		    <nav <?php new \dom\nav\attributes( 'dark', 'h75px' );?>>
		    	<?php new \icon\nouveau( );?>
				<a class="navbar-brand bankgothic" href="#" style='font-size:32px;'>Nouveau Elevator</a>
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav mr-auto">
						<?php new \dom\li\home( );?>
						<?php new \dom\li\settings( );?>
						<?php new \dom\li\dropdown\databases( );?>
						<?php new \dom\li\logout( );?>
					</ul>
					<?php new \dom\form\search( );?>
				</div>
			</nav>
		</header><?php
	}
}
?>