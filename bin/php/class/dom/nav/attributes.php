<?php 
namespace dom\nav;
class attributes {
	public function __construct( $themes = array( ) ){
		//Initialize Attributes
		$attributes = array();
		$attributes[ 'class' ] = array( 
			'navbar', 
			'navbar-expand-lg' 
		);
		$attributes[ 'style' ] = array( );

		//Iterate across themes
		$themes = is_array( $themes ) ? $themes : array( $themes );
		while ( $theme = array_pop( $themes ) ){
			switch( $theme ){
				case 'dark' :
					$attributes[ 'class' ][ ] = 'navbar-dark';
					$attributes[ 'class' ][ ] = 'bg-dark';
					break;
				case 'light':
					$attributes[ 'class' ][ ] = 'navbar-light';
					$attributes[ 'class' ][ ] = 'bg-light';
					break;
				case 'h75px' :
					$attributes [ 'class'][ ] = 'h75px';
					break;
			}
		}

		//Return Attributes
		echo implode( ' ', 
			array( 
				"class='" . implode(' ', $attributes[ 'class' ] ) . "'",
			)
		); 
	}
}
?>