<?php
/*********APP**********/
if( session_id( ) == '' || !isset($_SESSION)) {
    session_start( [ 'read_and_close' => true ] );
    require( __DIR__ . '/../../bin/php/index.php' );
}
if( isset( $_POST[ 'Databases' ] ) ){
	$result = \singleton\database::getInstance( )->query( 
		'Portal',
		"SELECT [Name] FROM [Database];"
	);
	if( $result ){ while( $row = sqlsrv_fetch_array( $result ) ){
		if( in_array( $row[ 'Name' ], $_POST[ 'Databases' ] ) ){
			\singleton\database::getInstance( )->query( 
				'Portal',
				"	UPDATE 	[Database]
					SET 	[Database].[Status] = 1
					WHERE 	[Database].[Name]   = ?;",
				array( $row[ 'Name' ] )
			);
		} else {
			\singleton\database::getInstance( )->query( 
				'Portal',
				"	UPDATE 	[Database]
					SET 	[Database].[Status] = 0
					WHERE 	[Database].[Name]   = ?;",
				array( $row[ 'Name' ] )
			);
		}
	}}
}?>