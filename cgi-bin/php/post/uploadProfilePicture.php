<?php
if(session_id() == '' || !isset($_SESSION)) { 
    session_start( [ 'read_and_close' => true ] ); 
    require('/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/cgi-bin/php/index.php');
}
if(     !isset( $Databases[ 'Default' ], $_SESSION[ 'User' ], $_SESSION[ 'Connection' ] )
    ||  !connection_privileged( $Databases[ 'Default' ], $_SESSION[ 'User' ], $_SESSION[ 'Connection' ] ) ){
        header( 'Location: https://beta.nouveauelevator.com/login.php' );
        exit; } 
if(     isset($_FILES[ 'Profile' ] ) 
    &&  isset( $_FILES[ 'Profile' ][ 'tmp_name' ] ) 
    &&  strlen( $_FILES[ 'Profile' ][ 'tmp_name' ] ) > 0 )
{
  ob_start( );
  $image = imagecreatefromstring( file_get_contents( $_FILES[ 'Profile' ][ 'tmp_name' ] ) );
  imagejpeg( $image, null, 50 );
  $image = ob_get_clean( );
  $image = base64_encode( $image );
  sqlsrv_query( 
    $Portal,
    "UPDATE Portal.dbo.Portal SET [Picture] = ?, Picture_Type = ? WHERE Branch = ? AND Branch_ID = ?;",
    array( 
      array(
        $image, 
        SQLSRV_PARAM_IN,
        SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),
        SQLSRV_SQLTYPE_VARBINARY('max')
      ),
      $_FILES[ 'Profile' ][ 'type' ],
      $_SESSION[ 'Connection' ][ 'Branch' ],
      $_SESSION[ 'User' ]
    )
  );
  echo 'success';
}
?>