<?php
namespace singleton;
class database extends \singleton\index {
	private $default = null;
	private $databases = array( );
	private $host = '20.124.200.54';
	private $options = array(
		'Database' 							=> 	'Portal',
		'Uid' 									=> 	'sa',
		'PWD' 									=> 	'007!Youknowwhattodo!',
		'ReturnDatesAsStrings'	=>	true,
		'CharacterSet' 					=> 	SQLSRV_ENC_CHAR,
		'TraceOn' 							=> 	false
	);
	protected function __construct( ){
		$this->default = 'Portal';
		$this->databases[ 'Portal' ] = sqlsrv_connect(
			$this->host,
			$this->options
		);
		$result = sqlsrv_query(
			$this->databases[ 'Portal' ],
			"	SELECT 	[Database].[Name],
						[Database].[Default]
				FROM 	[Database]
				WHERE 	[Database].[Status] = 1;"
		);
		if( $result ){ while( $row = sqlsrv_fetch_array( $result ) ){
			$this->databases[ ] = $row[ 'Name' ];
			$options = $this->options;
			$options[ 'Database' ] = $row[ 'Name' ];
			$this->databases[ $row[ 'Name' ] ] = sqlsrv_connect( $this->host, $options );
			if( $row[ 'Default' ] == 1 ){ $this->default = $row[ 'Name' ]; }
		} }
	}
	public function query( $database, $query, $parameters = array( ) ){
		return is_null ( $database ) || !in_array( $database, array_keys( $this->databases ) )
			?	sqlsrv_query(
					$this->databases[ $this->default ],
					$query,
					$parameters
				)
			: 	sqlsrv_query(
					$this->databases[ $database ],
					$query,
					$parameters
				);
	}
	public function changeDefault( $database = null ){
		$database = is_null( $database ) ? $this->default : $database;
		if( in_array( $database, $this->databases ) ){
			$this->default = $database;
		}
	}
}?>
