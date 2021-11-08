<?php
if( session_id( ) == '' || !isset($_SESSION)) { 
    session_start( [
    'read_and_close' => true
  ] ); 
    require( '/var/www/beta.nouveauelevator.com/html/Portal.Branch.Local/bin/php/index.php' );
}
if( isset( $_SESSION[ 'User' ] ) ){
  require('work4.php');
} else {
  ?><script>document.location.href='../login.php?Forward=work.php';</script><?php
}?>