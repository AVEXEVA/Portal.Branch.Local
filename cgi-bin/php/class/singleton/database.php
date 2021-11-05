<?php
namespace singleton;
class database extends \singleton\index {
	use \magic\method\index;
	private $resource = null;
	private $host = null;
	private $user = null;
	private $password = null;
	private $database = null;
	private $databases = array(
		'Portal',
		'Demo',
		'NEI',
		'N-FL',
		'N-IL',
		'N-CT',
		'N-TX',
		'Nustar',
		'Paradox',
		'Attendance'
	);
	private $options = array(
		'Database' 				=> 	null,
	    'Uid' 					=> 	'sa',
	    'PWD' 					=> 	'SQLABC!23456',
	    'ReturnDatesAsStrings'	=>	true,
	    'CharacterSet' 			=> 	SQLSRV_ENC_CHAR,
	    'TraceOn' 				=> 	false
	)
	public function __construct( $database = null ){
		if( in_array( $Database, $this->databases ) ){
			$options = $this->options;
			$options[ 'Database' ] = $database;
			$this->resource = sqlsrv_connect(
				$this->host,
				$options
			);
		}
	}
	public function __query( $query, $parameters ){
		return sqlsrv_query(
			$this->resource,
			$query,
			$parameters
		);
	}
}?>