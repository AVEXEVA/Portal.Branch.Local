<?php
if(session_id() == '' || !isset($_SESSION)) {
    session_start( );
    require('/var/www/html/Portal.Branch.Local/bin/php/index.php');
}

if(     isset($_FILES[ 'Profile' ] )
    &&  isset( $_FILES[ 'Profile' ][ 'tmp_name' ] )
    &&  strlen( $_FILES[ 'Profile' ][ 'tmp_name' ] ) > 0 )
{
  ob_start( );
  $image = imagecreatefromstring( file_get_contents( $_FILES[ 'Profile' ][ 'tmp_name' ] ) );
  imagejpeg( $image, null, 50 );
  $image = ob_get_clean( );
  $image = base64_encode( $image );
  $query="UPDATE [User] SET [Picture] = ?, [Picture_Type] = ? WHERE [ID] = ?;";
  $database->query(
    'Portal',
    $query,
    array(
      array(
        $image,
        SQLSRV_PARAM_IN,
        SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY),
        SQLSRV_SQLTYPE_VARBINARY('max')
      ),
      $_FILES[ 'Profile' ][ 'type' ],
      $_SESSION[ 'Connection' ][ 'User' ],
    )
  );
  echo 'success';
}
?>
