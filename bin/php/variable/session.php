<?php 
if( session_id( ) == '' || !isset($_SESSION)) { exit; }
$_SESSION[ 'Tables' ] = isset( $_SESSION[ 'Tables' ] ) 
		?	$_SESSION[ 'Tables' ]
		:	array( 
				'Customers' => array( ),
				'Location' => array( ),
				'Jobs' => array( ),
				'Units' => array( ),
				'Routes' => array( ),
				'Employees' => array( ),
				'Contacts' => array( ),
				'Leads' => array( ),
				'Users' => array( ),
				'Proposals' => array( )
			);
?>