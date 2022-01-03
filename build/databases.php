<?php 
$result = \singleton\database::getInstance( )->query( null, "SELECT name, database_id FROM sys.databases WHERE name NOT IN ( 'master', 'tempdb', 'model', 'msdb' );" );
if( $result ){while( $row = sqlsrv_fetch_array( $result ) ){
	\singleton\database::getInstance( )->query(
		Portal,
		"INSERT INTO [Database]( Name, Object ) VALUES( ?, ? );",
		array(
			$row[ 'name' ],
			$row[ 'database_id' ]
		)
	);
}}?>