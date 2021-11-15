<?php 
if( session_id( ) == '' || !isset($_SESSION)) { exit; }
$_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) 
		?	$_SESSION[ 'Tables' ]
		:	array( 
				'Customers' => array( )
			);
?>