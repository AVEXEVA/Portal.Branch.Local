<?php
namespace singleton;
class database extends \singleton\index {
	private $default = 'Dev01';
	private $resources = array( );
	private $databases = array(
		'Portal',
		'Nei',
		'Dev01'
	);
	private $host = '172.16.12.44';
	private $user = 'sa';
	private $password = 'SQLABC!23456';
	private $options = array(
		'Database' 				=> 	null,
	    'Uid' 					=> 	'sa',
	    'PWD' 					=> 	'SQLABC!23456',
	    'ReturnDatesAsStrings'	=>	true,
	    'CharacterSet' 			=> 	SQLSRV_ENC_CHAR,
	    'TraceOn' 				=> 	false
	);
	protected function __construct( ){
		if( is_array( $this->databases ) && count( $this->databases ) > 0 ){
			foreach( $this->databases as $database ){
				if( is_string( $database ) && strlen( $database ) > 0 ){
					$options = $this->options;
					$options[ 'Database' ] = $database;
					$this->resources[ $database ] = sqlsrv_connect( $this->host, $options );
				}
			}
		}
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
	public function changeDefault( $database = 'Dev01' ){
		if( in_array( $database, $this->databases ) ){
			$this->default = $database;
		}
	}
}?>
