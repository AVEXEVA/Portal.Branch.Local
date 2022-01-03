<?php 
if( session_id( ) == '' || !isset($_SESSION)) { exit; }

$_SESSION[ 'Database' ] = isset( $_POST[ 'Database' ] ) 
		? $_POST[ 'Database' ] 
		: ( isset( $_SESSION[ 'Database' ] ) 
			?	$_SESSION[ 'Database' ]
			:	'Demo'
		);
\singleton\database::getInstance( )->changeDefault( isset( $_SESSION[ 'Database' ] ) ? $_SESSION[ 'Database' ] : 'Demo' );

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
