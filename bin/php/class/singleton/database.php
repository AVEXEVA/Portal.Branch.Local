<?php
namespace singleton;
class database extends \singleton\index {
	private $default = 'Demo';
	private $resources = array( );
	private $databases = array(
		'Portal',
		'Demo'
	);
	private $host = '52.191.64.69';
	private $user = 'development';
	private $password = '484138thstreetLongislandcity';
	private $options = array(
		'Database' 				=> 	null,
	    'Uid' 					=> 	'development',
	    'PWD' 					=> 	'484138thstreetLongislandcity',
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
}?>