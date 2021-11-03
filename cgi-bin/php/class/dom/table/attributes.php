<?php 
namespace dom\table;
class attributes {
	public function __construct($themes = array( ) ){
		//Initialize Attributes
		$attributes = array( );
		$attributes[ 'class' ] = array( 'display' );
		$attributes[ 'cellspacing' ] = array( 0 );

		//Iterate across themes
		$themes = is_array( $themes ) ? $themes : array( $themes );
		while ( $theme = array_pop( $themes ) ){
			switch( $theme ){
				default : break;
			}
		}

		//Return Attributes
		echo implode( ' ', 
			array( 
				"class='" . implode(' ', $attributes[ 'class' ] ) . "'",
				"cellspacing='" . implode( ' ', $attributes[ 'cellspacing' ] ) . "'"
			)
		); 
	}
}
?>