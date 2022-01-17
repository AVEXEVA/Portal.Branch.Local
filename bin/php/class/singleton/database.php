<?php
namespace singleton;
class database extends \singleton\index {
	private $default = 'Demo';
	private $resources = array( );
	private $databases = array( );
	private $host = '20.124.200.54';
	private $user = 'sa';
	private $password = '007!Youknowwhattodo!';
	private $options = array(
		'Database' 				=> 	'Portal',
		'Uid' 					=> 	'sa',
		'PWD' 					=> 	'007!Youknowwhattodo!',
		'ReturnDatesAsStrings'	=>	true,
		'CharacterSet' 			=> 	SQLSRV_ENC_CHAR,
		'TraceOn' 				=> 	false
	);
	protected function __construct( ){
		$this->databases[ 'Portal' ] = sqlsrv_connect( 
			$this->host,
			$this->options
		);
		$result = sqlsrv_query(
			$this->databases[ 'Portal' ],
			"	SELECT 	Database.Name
				FROM 	Portal.dbo.Database
				WHERE 	Database.Status = 1;"
		);
		if( $result ){ while( $row = sqlsrv_fetch_array( $result ) ){
			$this->databases[ ] = $row[ 'Name' ];	
			$options = $this->options;
			$options[ 'Database' ] = $database;
			$this->resources[ $database ] = sqlsrv_connect( $this->host, $options );
		} }
	}
	public function query( $database, $query, $parameters = array( ) ){
		return is_null ( $database ) || !in_array( $database, array_keys( $this->resources ) )
			?	sqlsrv_query(
					$this->resources[ $this->default ],
					$query,
					$parameters
				)
			: 	sqlsrv_query(
					$this->resources[ $database ],
					$query,
					$parameters
				);
	}
	public function changeDefault( $database = 'Demo' ){
		if( in_array( $database, $this->databases ) ){
			$this->default = $database;
		}
	}
}?>
